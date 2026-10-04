<?php

namespace App\Http\Controllers\Api\Transport\Rental;

use App\Classes\AdminClass;
use App\Classes\NotificationClass;
use App\Classes\UserClassApi;
use App\model\GeneralSettings;
use App\model\PromocodeDetails;
use App\model\Provider;
use App\model\ProviderUserRunningService;
use App\model\RestrictedArea;
use App\model\ServiceCategory;
use App\model\ServiceSettings;
use App\model\TempUserBooking;
use App\model\TransportDriverDetails;
use App\model\TransportRatings;
use App\model\TransportRentalRideBooking;
use App\model\TransportRideDriverToken;
use App\model\TransportVehicleType;
use App\model\UsedPromocodeDetails;
use App\model\UserCardDetails;
use App\model\UserWalletTransaction;
use App\model\WorldCurrency;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    private $userClassapi;
    private $notificationClass;
    private $bikeRental;
    private $bikeService;
    private $taxiService;
    private $taxiRental;
    private $adminClassApi;
    private $user_type = 0;

    public function __construct(AdminClass $adminClassApi ,UserClassApi $userClassapi, NotificationClass $notificationClass)
    {
        $this->userClassapi = $userClassapi;
        $this->notificationClass = $notificationClass;
        $this->adminClassApi = $adminClassApi;
        $this->bikeRental = 31;
        $this->taxiRental = 32;
        $this->bikeService = 1;
        $this->taxiService = 2;
    }

    public function postGetNearestDriverPosition(Request $request)
    {
        $validator = Validator::make($request->all(), [
                "user_id" => "required|numeric",
                "access_token" => "required|numeric",
                "service_category_id" => "required|numeric|in:31,32",
                "current_lat" => "required|numeric",
                "current_long" => "required|numeric"
            ]
        );
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }
        $user_check = $this->userClassapi->checkUserAllow($request->get('user_id'), $request->get('access_token'));
        if ($decoded['status'] = json_decode($user_check) == false) {
            return $user_check;
        }
        $expire_date_time = date('Y-m-d H:i:s', strtotime("-10 minutes"));
        $lat = $request->get('current_lat');
        $long = $request->get('current_long');
        $service_cat_id = $request->get('service_category_id');
        $service_setting = ServiceSettings::query()->where('service_cat_id', $service_cat_id)->first();
        if ($service_setting != Null) {
            $radius = $service_setting->provider_search_radius;
        } else {
            $radius = 5;
        }

        if (in_array($service_cat_id, [5, 6, 7, 8, 9, 10])) {
            $skip_ids = [1,2,4,31,32];
        } else {
            $skip_ids = [1, 2, 4, 5, 6, 7, 8, 9, 10, 31, 32];
        }

        $not_multi_delivery_provider_id = ProviderUserRunningService::query()->whereIn('service_cat_id',$skip_ids)->get()->pluck('provider_id');

        $find_service_cat_id = $service_cat_id == 31 ? $this->bikeService : $this->taxiService;

        $provider_lat_long = Provider::query()->select(DB::raw("CONCAT(COALESCE(providers.first_name,''),' ',COALESCE(providers.last_name,'')) as name"), 'transport_driver_details.current_lat', 'transport_driver_details.current_long', 'service_category.id as service_id')
            ->whereRaw(DB::raw("(6371 * acos( cos( radians(" . $lat . ") ) * cos( radians( current_lat ) )  * cos( radians( current_long ) - radians(" . $long . ") ) + sin( radians(" . $lat . ") ) * sin(radians( current_lat ) ) ) ) < " . $radius))
            ->join('provider_services', 'provider_services.provider_id', '=', 'providers.id')
            ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
            ->join('transport_driver_details', 'transport_driver_details.provider_service_id', '=', 'provider_services.id')
            ->where('service_category.id', $find_service_cat_id)
            ->where('providers.status', 1)
            ->where('provider_services.status', 1)
            ->where('provider_services.current_status', 1)
            ->where('transport_driver_details.driver_service', 1)
            ->where('transport_driver_details.rental_service', 1)
            ->where('transport_driver_details.last_online_date_time', '>=', $expire_date_time)
            ->whereNotNull('transport_driver_details.current_lat')
            ->whereNotNull('transport_driver_details.current_long')
            ->whereNotNull('providers.access_token')
            ->whereNotNull('providers.device_token');

        if ($not_multi_delivery_provider_id != Null) {
            $provider_lat_long = $provider_lat_long->whereNotIn('providers.id', $not_multi_delivery_provider_id);
        }

        $provider_lat_long = $provider_lat_long->get();

        return response()->json([
            "status" => 1,
//            "message" => "success",
            "message" => __('user_messages.1'),
            "message_code" => 1,
            "active_driver_lat_long" => $provider_lat_long,
        ]);
    }

    public function postTransportServiceType(Request $request)
    {
        $validator = Validator::make($request->all(), [
                "user_id" => "required|numeric",
                "access_token" => "required|numeric",
                "service_category_id" => "required|numeric|in:31,32",
                "current_lat" => "required",
                "current_long" => "required",
            ]
        );
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }

        $user_check = $this->userClassapi->checkUserAllow($request->get('user_id'), $request->get('access_token'));
        if ($decoded['status'] = json_decode($user_check) == false) {
            return $user_check;
        }
        $user_currency = WorldCurrency::query()->where('symbol', $user_check->currency)->first();
        if ($user_currency == Null) {
            $user_currency = WorldCurrency::query()->where('default_currency', 1)->first();
        }

        $service_cat_id = $request->get('service_category_id');
        $currency = $user_currency->ratio;
        $find_service_cat_id = $service_cat_id == 31 ? $this->bikeService : $this->taxiService;
        $service_types = TransportVehicleType::query()->select('id',
            'name',
            'icon_name',
            DB::raw('ROUND(rental_amount_for_1hour * ' . $currency . ',2) As rental_amount'))
            ->where('service_cat_id', $find_service_cat_id)
            ->where('status', '=', 1)
            ->orderBy('rental_amount', 'asc')->get();

        $expire_date_time = date('Y-m-d H:i:s', strtotime("-10 minutes"));
        $lat = $request->get('current_lat');
        $long = $request->get('current_long');
        $service_setting = ServiceSettings::query()->where('service_cat_id', $service_cat_id)->first();
        if ($service_setting != Null) {
            $radius = $service_setting->provider_search_radius;
        } else {
            $radius = 5;
        }

        if (in_array($service_cat_id, [5, 6, 7, 8, 9, 10])) {
            $skip_ids = [1,2,4,31,32];
        } else {
            $skip_ids = [1, 2, 4, 5, 6, 7, 8, 9, 10, 31, 32];
        }

        $not_multi_delivery_provider_id = ProviderUserRunningService::query()->whereIn('service_cat_id',$skip_ids)->get()->pluck('provider_id');

        $provider_lat_long = Provider::query()->select('transport_driver_details.vehicle_type_id')
            ->whereRaw(DB::raw("(6371 * acos( cos( radians(" . $lat . ") ) * cos( radians( current_lat ) )  * cos( radians( current_long ) - radians(" . $long . ") ) + sin( radians(" . $lat . ") ) * sin(radians( current_lat ) ) ) ) < " . $radius))
            ->join('provider_services', 'provider_services.provider_id', '=', 'providers.id')
//            ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
            ->join('transport_driver_details', 'transport_driver_details.provider_service_id', '=', 'provider_services.id')
//            ->where('service_category.id', $find_service_cat_id)
            ->where('providers.status', 1)
            ->where('provider_services.status', 1)
            ->where('provider_services.current_status', 1)
            //->where('transport_driver_details.driver_service', 1)
            ->where('transport_driver_details.rental_service', 1)
            ->where('transport_driver_details.last_online_date_time', '>=', $expire_date_time)
            ->whereNotNull('transport_driver_details.current_lat')
            ->whereNotNull('transport_driver_details.current_long')
            ->whereNotNull('providers.access_token')
            ->whereNotNull('providers.device_token');

        if ($not_multi_delivery_provider_id != Null) {
            $provider_lat_long = $provider_lat_long->whereNotIn('providers.id', $not_multi_delivery_provider_id);
        }

        $provider_lat_long = $provider_lat_long->get()->pluck('vehicle_type_id')->toArray();

        $service_type = [];
        if (!$service_types->isEmpty()) {
            foreach ($service_types as $key => $type) {
                $service_status = 0;
                if (in_array($type->id, $provider_lat_long)){
                    $service_status = 1;
                }
                if ($type->icon_name != Null) {
                    $image_path = asset('assets/images/service-category/transport-service-type/' . $type->icon_name);
                } else {
                    $image_path = null;
                }
                $service_type[] = [
                    "id" => $type->id,
                    "service_name" => $type->name,
                    "image" => $image_path,
                    "rental_amount" => $type->rental_amount,
                    "service_status" => $service_status,
                ];
            }
            return response()->json([
                "status" => 1,
//                "message" => "Success",
                "message" => __('user_messages.1'),
                "message_code" => 1,
                "service_type" => $service_type,
            ]);
        } else {
            return response()->json([
                "status" => 0,
//                "message" => "vehicle type not Found!",
                "message" => __('user_messages.9'),
                "message_code" => 9,
            ]);
        }
    }

    public function postTransportRentalRidePreview(Request $request) {
        $validator = Validator::make($request->all(), [
            "user_id" => "required|numeric",
            "access_token" => "required|numeric",
            "service_category_id" => "required|numeric|in:31,32",
            "vehicle_type_id" => "required|integer",
            "start_date_time" => "required",
            "end_date_time" => "required",
            "total_km" => "required|numeric",
            "promo_code" => "nullable",
            /*"current_lat" => "required",
            "current_long" => "required",*/
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }

        $user_check = $this->userClassapi->checkUserAllow($request->get('user_id'), $request->get('access_token'));
        if ($decoded['status'] = json_decode($user_check) == false) {
            return $user_check;
        }
        $service_category_id = $request->get('service_category_id');
        if ($service_category_id == 31) {
            $service_category_id = $this->bikeService;
        } elseif ($service_category_id == 32) {
            $service_category_id = $this->taxiService;
        } else {
            return response()->json([
                "status" => 0,
//                "message" => "something went to wrong",
                "message" => __('user_messages.9'),
                "message_code" => 9,
            ]);
        }
        $user_currency = WorldCurrency::query()->where('symbol', $user_check->currency)->first();
        if ($user_currency == Null) {
            $user_currency = WorldCurrency::query()->where('default_currency', 1)->first();
        }
        $currency = $user_currency->ratio;
        $vehicle_type_id = $request->get('vehicle_type_id');
        $vehicle_type = TransportVehicleType::query()->where('service_cat_id', $service_category_id)->where('id', $vehicle_type_id)->where('status', 1)->first();
        if ($vehicle_type == Null) {
            return response()->json([
                "status" => 0,
//                "message" => "something went to wrong",
                "message" => __('user_messages.9'),
                "message_code" => 9,
            ]);
        }
        $total_km = $request->get('total_km');

        $start_date_time = strtotime(str_replace('/', '-', $request->get('start_date_time')));
        $end_date_time = strtotime(str_replace('/', '-', $request->get('end_date_time')));
        // Formulate the Difference between two dates
        $diff = abs($end_date_time - $start_date_time);

        // To get the year divide the resultant date into
        // total seconds in a year (365*60*60*24)
        $years = floor($diff / (365 * 60 * 60 * 24));

        // To get the month, subtract it with years and
        // divide the resultant date into
        // total seconds in a month (30*60*60*24)
        //$months = floor(($diff - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));

        // To get the day, subtract it with years and
        // months and divide the resultant date into
        // total seconds in a days (60*60*24)
        //$days = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24) / (60 * 60 * 24));

        // To get the hour, subtract it with years,
        // months & seconds and divide the resultant
        // date into total seconds in a hours (60*60)
        //$hours = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24) / (60 * 60));
        $hours = floor(($diff - $years * 365 * 60 * 60 * 24) / (60 * 60));

        // To get the minutes, subtract it with years,
        // months, seconds and hours and divide the
        // resultant date into total seconds i.e. 60
        //$minutes = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24 - $hours * 60 * 60) / 60);
        $minutes = floor(($diff - $years * 365 * 60 * 60 * 24 - $hours * 60 * 60) / 60);

        // To get the minutes, subtract it with years,
        // months, seconds, hours and minutes
        //$seconds = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24 - $days * 60 * 60 * 24 - $hours * 60 * 60 - $minutes * 60));

        //return "diff-" . $diff . " years-" . $years . " months-" . $months . " days-" . $days . " hours-" . $hours . " minutes-" . $minutes . " seconds-" . $seconds;
        //return " hours - " . $hours . " minutes - " . $minutes;

        $rental_cost_for_km = $vehicle_type->rental_cost_for_km;
        $rental_amount_for_1hour = $vehicle_type->rental_amount_for_1hour;
        $rental_km_limit_for_1hour = $vehicle_type->rental_km_limit_for_1hour;

        $count_amount_for_hours = $hours * $rental_amount_for_1hour;
        $count_amount_for_minutes = 0;
        if ($minutes > 0) {
            if ($minutes > 30) {
                $count_amount_for_minutes = ($rental_amount_for_1hour);
            } else {
                $count_amount_for_minutes = ($rental_amount_for_1hour / 2);
            }
        } else{
            $count_amount_for_minutes = ($rental_amount_for_1hour / 2);
        }
        $count_amount = round($count_amount_for_hours + $count_amount_for_minutes, 2);
        $count_km_limit_for_hours = $hours * $rental_km_limit_for_1hour;
        $count_km_limit_for_minutes = 0;
        if ($minutes > 0) {
            if ($minutes > 30) {
                $count_km_limit_for_minutes = ($rental_km_limit_for_1hour);
            } else {
                $count_km_limit_for_minutes = ($rental_km_limit_for_1hour / 2);
            }
        } else{
            $count_km_limit_for_minutes = ($rental_km_limit_for_1hour / 2);
        }
        $count_km_limit = round($count_km_limit_for_hours + $count_km_limit_for_minutes);

        $extra_amount = 0;

        $extra_km = $total_km - $count_km_limit;
        if ($extra_km > 1) {
            $extra_amount = $extra_km * $rental_cost_for_km;
            //$total_amount = round($count_amount + $extra_amount, 2);
        }

        $service_settings = ServiceSettings::query()->where('service_cat_id', $request->get('service_category_id'))->first();
        if ($service_settings != Null) {
            $get_tax = $service_settings->tax;
        } else {
            $get_tax = 0;
        }

        $discount_cost = 0;
        $promocode_discount = 0;
        $promo_code_name = "";
        $get_promo_code = $request->get('promo_code');
        if ($get_promo_code != 0) {
            if (!is_numeric($get_promo_code)) {
                $get_promocode = PromocodeDetails::query()->where('promo_code', $get_promo_code)->where('service_cat_id', $request->get('service_category_id'))->where('status','1')->first();
                $get_promo_code_id = ($get_promocode != Null) ? $get_promocode->id : 0;
            }
            //$discount_on_amount = (($total_price - $discount_cost) + $place_order->packaging_cost);
            if($get_promo_code_id > 0)
            {
                $discount_on_amount = ($count_amount + $extra_amount);
                list($promo_code_status,$promo_message_code,$promo_code_message,$min_order_amount,$promo_code,$promo_code_name) = $this->userClassapi->checkPromoCodeValid($get_promo_code_id, $discount_on_amount, $user_check->id, $request->get('service_category_id'));
                if ($promo_code_status == 1) {
                    $get_prmocode = PromocodeDetails::query()->where('id', $get_promo_code_id)->where('status', 1)->first();
                    if ($get_prmocode != Null) {
                        $discount_cost = $promo_code;
                        $promocode_discount = $promo_code;
                    }
                }
            }
        }



        $tax_cost = round(((($count_amount + $extra_amount - $discount_cost) * $get_tax) / 100), 2);
        $total_pay = $count_amount + $extra_amount - $discount_cost + $tax_cost;
        $sub_total = $count_amount + $extra_amount;

        return response()->json([
            "status" => 1,
//            "message" => "success",
            "message" => __('user_messages.1'),
            "message_code" => 1,
            "travel_hour" => $hours . " Hours" . ($minutes != 0 ? " " . $minutes . " Min" : ""),
            "free_travel_km" => $count_km_limit,
//            "amount_per_hour" => round($vehicle_type->rental_amount_for_1hour * $currency, 2),
            "amount_per_hour" => round($vehicle_type->rental_amount_for_1hour * $currency, 2),
            "subtotal" => round($sub_total * $currency, 2),
            "ride_fare" => round($count_amount * $currency, 2),
            "additional_fare" => round($extra_amount * $currency, 2),
            "discount_cost" => round($discount_cost * $currency, 2),
            "promocode_discount" => round($promocode_discount * $currency, 2),
            "promocode_name" => ($promo_code_name !="")?$promo_code_name:"",
            "tax_cost" => round($tax_cost * $currency, 2),
            "total_pay" => round($total_pay * $currency, 2)
        ]);
    }

    public function postTransportRentalRideBooking(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "user_id" => "required|numeric",
            "access_token" => "required|numeric",
            "service_category_id" => "required|numeric|in:31,32",
            "ride_id" => "nullable|numeric"
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }

        $user_check = $this->userClassapi->checkUserAllow($request->get('user_id'), $request->get('access_token'));
        if ($decoded['status'] = json_decode($user_check) == false) {
            return $user_check;
        }
        $ride_id = $request->get('ride_id');
        $request_service_category_id = $request->get('service_category_id');
        $service_settings = ServiceSettings::query()->where('service_cat_id', $request_service_category_id)->first();
        if ($service_settings != Null) {
            $get_tax = $service_settings->tax;
            $timeout = $service_settings->provider_accept_timeout;
            $admin_commission = $service_settings->admin_commission;
        } else {
            $get_tax = 0;
            $timeout = 60;
            $admin_commission = 0;
        }
        if ($ride_id != Null && $ride_id > 0) {
            $ride_book = TransportRentalRideBooking::query()->where('id', $ride_id)->first();
            if ($ride_book == Null) {
                return response()->json([
                    "status" => 0,
//                    "message" => "something went to wrong",
                    "message" => __('user_messages.9'),
                    "message_code" => 9,
                ]);
            }
        } else {
            $validator = Validator::make($request->all(), [
                "vehicle_type_id" => "required|integer",
                "start_date_time" => "required",
                "end_date_time" => "required",
                "total_km" => "required|numeric",
                "payment_type" => "required|in:1,2,3",
                "pickup_location" => "required",
                "pickup_lat" => "required",
                "pickup_long" => "required",
                "drop_location" => "required",
                "drop_lat" => "required",
                "drop_long" => "required",
                "promo_code" => "nullable",

            ]);
            if ($validator->fails()) {
                return response()->json([
                    "status" => 0,
                    "message" => $validator->errors()->first(),
                    "message_code" => 9,
                ]);
            }
            $service_category_id = $request->get('service_category_id');
            if ($service_category_id == 31) {
                $service_category_id = $this->bikeService;
            } elseif ($service_category_id == 32) {
                $service_category_id = $this->taxiService;
            } else {
                return response()->json([
                    "status" => 0,
//                    "message" => "something went to wrong",
                    "message" => __('user_messages.9'),
                    "message_code" => 9,
                ]);
            }
            $vehicle_type_id = $request->get('vehicle_type_id');
            $vehicle_type = TransportVehicleType::query()->where('service_cat_id', $service_category_id)->where('id', $vehicle_type_id)->where('status', 1)->first();
            if ($vehicle_type == Null) {
                return response()->json([
                    "status" => 0,
//                    "message" => "something went to wrong",
                    "message" => __('user_messages.9'),
                    "message_code" => 9,
                ]);
            }

            $address_lat = trim($request->get('pickup_lat'));
            $address_long = trim($request->get('pickup_long'));
            $area_details = RestrictedArea::query()->where('status',1)->get();
            $latitudes = $longitudes = '';
            foreach ($area_details as $area){
                $latitudes = $latitudes . $area->latitude . ',';
                $longitudes = $longitudes . $area->longitude . ',';
            }
            $restricted_lat = explode(',',substr($latitudes, 0, -1));
            $restricted_long = explode(',',substr($longitudes, 0, -1));
            $points_polygon = count($restricted_lat);
            if($this->adminClassApi->is_in_restricted_area($points_polygon,$restricted_lat,$restricted_long,$address_lat,$address_long)){
                return response()->json([
                    "status" => 0,
//                    "message" => "Your pickup address exist in restricted area, please try with other location!",
                    "message" => __('user_messages.194'),
                    "message_code" => 194,
                ]);
            }

            $total_km = $request->get('total_km');

            $start_date_time = strtotime(str_replace('/', '-', $request->get('start_date_time')));
            $end_date_time = strtotime(str_replace('/', '-', $request->get('end_date_time')));
            // Formulate the Difference between two dates
            $diff = abs($end_date_time - $start_date_time);
            $years = floor($diff / (365 * 60 * 60 * 24));
            $hours = floor(($diff - $years * 365 * 60 * 60 * 24) / (60 * 60));
            $minutes = floor(($diff - $years * 365 * 60 * 60 * 24 - $hours * 60 * 60) / 60);

            $rental_cost_for_km = $vehicle_type->rental_cost_for_km;
            $rental_amount_for_1hour = $vehicle_type->rental_amount_for_1hour;
            $rental_km_limit_for_1hour = $vehicle_type->rental_km_limit_for_1hour;

            $count_amount_for_hours = $hours * $rental_amount_for_1hour;
            $count_amount_for_minutes = 0;
            if ($minutes > 0) {
                if ($minutes > 30) {
                    $count_amount_for_minutes = ($rental_amount_for_1hour);
                } else {
                    $count_amount_for_minutes = ($rental_amount_for_1hour / 2);
                }
            } else{
                $count_amount_for_minutes = ($rental_amount_for_1hour / 2);
            }

            $count_amount = round($count_amount_for_hours + $count_amount_for_minutes, 2);
            $count_km_limit_for_hours = $hours * $rental_km_limit_for_1hour;
            $count_km_limit_for_minutes = 0;
            if ($minutes > 0) {
                if ($minutes > 30) {
                    $count_km_limit_for_minutes = ($rental_km_limit_for_1hour);
                } else {
                    $count_km_limit_for_minutes = ($rental_km_limit_for_1hour / 2);
                }
            } else{
                $count_km_limit_for_minutes = ($rental_km_limit_for_1hour / 2);
            }
            $count_km_limit = round($count_km_limit_for_hours + $count_km_limit_for_minutes);

            $extra_amount = 0;

            $extra_km = $total_km - $count_km_limit;
            if ($extra_km > 1) {
                $extra_amount = $extra_km * $rental_cost_for_km;
            } else {
                $extra_km = 0;
            }

            $promo_code_discount = 0;
            $promo_code_id = 0;
            if ($request['promo_code'] != 0) {
                $discount_on_amount = ($count_amount + $extra_amount);
                //$discount_on_amount = ($ride->base_fare + $ride->total_distance_amount);
                $promo_code = $this->userClassapi->checkPromoCodeValid($request['promo_code'], $discount_on_amount, $user_check->id, $request['service_category_id']);
                if ($promo_code != 0) {
                    if ($promo_code != 1) {
                        $get_prmocode = PromocodeDetails::query()->where('id', $request['promo_code'])->where('status', 1)->first();
                        if ($get_prmocode != Null) {
                            $promo_code_discount = $promo_code;
                            //$place_order->total_pay = round($place_order->total_pay - $promo_code, 2);
                            $add_promocode_details = new UsedPromocodeDetails();
                            $add_promocode_details->service_cat_id = $request['service_category_id'];
                            $add_promocode_details->user_id = $user_check->id;
                            $add_promocode_details->promocode_id = $get_prmocode->id;
                            $add_promocode_details->promocode_name = $get_prmocode->promo_code;
                            $add_promocode_details->discount_amount = $promo_code;
                            $add_promocode_details->status = 0;
                            $add_promocode_details->save();

                            //$ride->promo_code = $add_promocode_details->id;
                            $promo_code_id = $add_promocode_details->id;
                            //$ride->save();
                        }
                    }
                }
            }


            $discount_cost = 0;
            $promo_code_discount = 0;
            $get_promo_code_id = 0;
            $get_promo_code = $request->get('promo_code');
            if ($get_promo_code != "") {
                if (!is_numeric($get_promo_code)) {
                    $get_promocode = PromocodeDetails::query()->where('promo_code', $get_promo_code)->where('service_cat_id', $request->get('service_category_id'))->where('status','1')->first();
                    $get_promo_code_id = ($get_promocode != Null) ? $get_promocode->id : 0;
                }
                //$discount_on_amount = (($total_price - $discount_cost) + $place_order->packaging_cost);

                if($get_promo_code_id > 0)
                {

                    $discount_on_amount = ($count_amount + $extra_amount);
//                        $promo_code = $this->userClassapi->checkPromoCodeValid($get_promo_code_id, $discount_on_amount, $user_details->id, $request->get('service_category_id'));
                    list($promo_code_status,$promo_message_code,$promo_code_message,$min_order_amount,$promo_code,$promo_code_name) = $this->userClassapi->checkPromoCodeValid($get_promo_code_id, $discount_on_amount, $user_check->id, $request->get('service_category_id'));

                    if ($promo_code_status == 1) {
                        $get_prmocode = PromocodeDetails::query()->where('id', $get_promo_code_id)->where('status', 1)->first();
                        if ($get_prmocode != Null) {
                            $promo_code_discount = $promo_code;

                            $add_promocode_details = new UsedPromocodeDetails();
                            $add_promocode_details->service_cat_id = $request->get('service_category_id');
                            $add_promocode_details->user_id = $request->get('user_id');
                            $add_promocode_details->promocode_id = $get_promo_code_id;
                            $add_promocode_details->promocode_name = $get_prmocode->promo_code;
                            $add_promocode_details->discount_amount = $promo_code;
//                                $add_promocode_details->status = 1;
                            $add_promocode_details->save();

                            $get_prmocode->total_usage = $get_prmocode->total_usage + 1;
                            $get_prmocode->save();

                            $promo_code_id = $add_promocode_details->id;
                        }
                    }
                }

            }




            $tax_cost = round(((($count_amount + $extra_amount - $discount_cost- $promo_code_discount) * $get_tax) / 100), 2);
            $total_pay = (($count_amount + $extra_amount) - $promo_code_discount) - $discount_cost + $tax_cost;


            $driver_amount = $count_amount + $extra_amount;
            $commission = round((($driver_amount * $admin_commission) / 100), 2);
            $driver_new_amount = round($driver_amount - $commission, 2);
            $admin_commission = round($commission - $discount_cost, 2);
            //"vehicle_type_id" => "required|integer",
            //"start_date_time" => "required",
            //"end_date_time" => "required",
            //"total_km" => "required|numeric",
            //"payment_type" => "required|in:1,2,3",
            //"pickup_location" => "required",
            //"pickup_lat" => "required",
            //"pickup_long" => "required",
            //"drop_location" => "required",
            //"drop_lat" => "required",
            //"drop_long" => "required"

            //"amount_per_hour" => $vehicle_type->rental_amount_for_1hour,
            //"ride_fare" => $count_amount,
            //"additional_fare" => $extra_amount,
            //"discount_cost" => $discount_cost,
            //"tax_cost" => $tax_cost,
            //"total_pay" => $total_pay
            $timeout = 60;
            $general_settings = GeneralSettings::query()->select('user_timeout', 'driver_algorithm')->first();
            $driver_algorithm = 0;
            if ($general_settings != Null){
                $driver_algorithm = $general_settings->driver_algorithm - 0;
                $timeout = $general_settings->user_timeout != NUll ? round($general_settings->user_timeout) : 60;
            }

            $ride_book = new TransportRentalRideBooking();
            $ride_book->user_id = $user_check->id;
            $ride_book->service_cat_id = $request_service_category_id;
            $ride_book->vehicle_type_id = $vehicle_type->id;
            $ride_book->ride_no = $ride_book->generateRideNo();
            $ride_book->pickup_address = $request->get('pickup_location');
            $ride_book->pickup_lat_long = $request->get('pickup_lat') . "," . $request->get('pickup_long');
            $ride_book->drop_address = $request->get('drop_location');
            $ride_book->drop_lat_long = $request->get('drop_lat') . "," . $request->get('drop_long');
            $ride_book->pickup_date_time = date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $request->get('start_date_time'))));
            $ride_book->drop_date_time = date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $request->get('end_date_time'))));
            $ride_book->total_km = $request->get('total_km');
            $ride_book->ride_fare = $count_amount;
            $ride_book->additional_fare = $extra_amount;
            $ride_book->additional_km = $extra_km;
            $ride_book->discount = $discount_cost;
            $ride_book->payment_type = $request->get('payment_type');
            $ride_book->payment_status = 0;
            $ride_book->tax = $tax_cost;
            $ride_book->total_pay = $total_pay;
            $ride_book->driver_amount = $driver_new_amount;
            $ride_book->admin_commission = $admin_commission;
            $ride_book->user_name = $user_check->first_name . ($user_check->last_name != Null ? " " . $user_check->last_name : "");
            $ride_book->vehicle_type_name = $vehicle_type->name;
            $ride_book->km_limit_per_hour = $rental_km_limit_for_1hour;
            $ride_book->rental_amount_per_hour = $rental_amount_for_1hour;
            $ride_book->extra_km_amount_per_hour = $rental_cost_for_km;
            $ride_book->status = 0;
            $ride_book->driver_algorithm =$driver_algorithm;
            $ride_book->promo_code = $promo_code_id;
            $ride_book->save();
            $get_prmocode = PromocodeDetails::query()->where('id', $request['promo_code'])->where('status', 1)->first();
            if ($get_prmocode != Null) {
                $get_prmocode->total_usage = $get_prmocode->total_usage + 1;
                $get_prmocode->save();
            }
            $ride_id = $ride_book->id;
        }

        $ddd = $this->notificationClass->driverRequestNotification($ride_book->service_cat_id, $ride_id);

        return response()->json([
            "status" => 1,
//            "message" => "Ride Booking Successfully!",
            "message" => __('user_messages.1'),
            "message_code" => 1,
            "ride_id" => $ride_book->id,
            "booking_no" => $ride_book->ride_no,
            "pickup_date_time" => $this->notificationClass->dateLangConvert(Date('d M, Y', strtotime($ride_book->pickup_date_time)),$user_check->language),
            "pickup_address" => $ride_book->pickup_address,
            "destination_address" => $ride_book->drop_address,
            "ride_status" => $ride_book->status,
            "accept_time_out" => round($timeout),
            "notify" => $ddd,
        ]);
    }

    public function postTransportRentalRideDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
                "user_id" => "required|integer",
                "access_token" => "required|numeric",
                "service_category_id" => "required|integer|in:31,32",
                "ride_id" => "required|integer"
            ]
        );
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }

        $user_check = $this->userClassapi->checkUserAllow($request->get('user_id'), $request->get('access_token'));
        if ($decoded['status'] = json_decode($user_check) == false) {
            return $user_check;
        }

        $ride_id = $request->get('ride_id');
        $ride_details = TransportRentalRideBooking::query()->where('id', $ride_id)->where('user_id', $user_check->id)->first();
        if ($ride_details != Null) {
            $user_currency = WorldCurrency::query()->where('symbol', $user_check->currency)->first();
            if ($user_currency == Null) {
                $user_currency = WorldCurrency::query()->where('default_currency', 1)->first();
            }
            $currency = $user_currency->ratio;

            $user_profile_url = url('/assets/images/profile-images/provider');
            $vehicle_icon_url = url('/assets/images/service-category/transport-service-type');
            $driver_details = TransportDriverDetails::query()->select('providers.id',
                DB::raw("providers.first_name as name"),
                'transport_driver_details.rating',
                'transport_driver_details.vehicle_company',
                'transport_driver_details.plat_no',
                'transport_driver_details.model_year',
                'transport_driver_details.model_name',
                'transport_driver_details.vehicle_color',
                DB::raw("(CASE WHEN providers.avatar != '' THEN concat('$user_profile_url','/',providers.avatar) ELSE '' END) as driver_profile_url"))
                ->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
                ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
                ->where('transport_driver_details.id', $ride_details->driver_id)->first();
            $vehicle_details = TransportVehicleType::query()->select('name',
                DB::raw("(CASE WHEN icon_name != '' THEN concat('$vehicle_icon_url','/',icon_name) ELSE '' END) as icon_url"))
                ->where('id', $ride_details->vehicle_type_id)->first();

            $start_date_time = strtotime($ride_details->pickup_date_time);
            $end_date_time = strtotime($ride_details->drop_date_time);
            // Formulate the Difference between two dates
            $diff = abs($end_date_time - $start_date_time);
            $years = floor($diff / (365 * 60 * 60 * 24));
            $hours = floor(($diff - $years * 365 * 60 * 60 * 24) / (60 * 60));
            $minutes = floor(($diff - $years * 365 * 60 * 60 * 24 - $hours * 60 * 60) / 60);
            $count_km_limit_for_hours = $hours * $ride_details->km_limit_per_hour;
            $count_km_limit_for_minutes = 0;
            if ($minutes > 0) {
                if ($minutes > 30) {
                    $count_km_limit_for_minutes = ($ride_details->km_limit_per_hour);
                } else {
                    $count_km_limit_for_minutes = ($ride_details->km_limit_per_hour / 2);
                }
            }
            $count_km_limit = round($count_km_limit_for_hours + $count_km_limit_for_minutes);
//            return $ride_details->promo_code;
            $promocode_details = UsedPromocodeDetails::query()->where('id', $ride_details->promo_code)->first();

            return response()->json([
                "status" => 1,
//                "message" => "success",
                "message" => __('user_messages.1'),
                "message_code" => 1,
                "ride_id" => $ride_details->id,
                "ride_no" => $ride_details->ride_no,
                "driver_name" => $ride_details->driver_name != Null ? $ride_details->driver_name : "",
                "driver_profile_image" => $driver_details != Null ? $driver_details->driver_profile_url != Null ? $driver_details->driver_profile_url : "" : "",
                "driver_rating" => $driver_details != Null ? $driver_details->rating : 0.0,
                "vehicle_manufacture_name" => $driver_details != Null ? $driver_details->vehicle_company : "",
                "vehicle_plat_no" =>  $driver_details != Null ? $driver_details->plat_no : "",
                "vehicle_model_year" => $driver_details != Null ? intval($driver_details->model_year) : "",
                "vehicle_model_name" => $driver_details != Null ? $driver_details->model_name : 0,
                "vehicle_color" => $driver_details != Null ? $driver_details->vehicle_color : "",
                "vehicle_type_name" => $vehicle_details != Null ? $vehicle_details->name : "",
                "vehicle_type_icon" => $vehicle_details != Null ? $vehicle_details->icon_url != Null ? $vehicle_details->icon_url : "" : "",
                "pickup_location" => $ride_details->pickup_address,
                "pickup_lat_long" => $ride_details->pickup_lat_long,
                "pickup_date_time" => $ride_details->pickup_date_time,
                "drop_location" => $ride_details->drop_address,
                "drop_lat_long" => $ride_details->drop_lat_long,
                "drop_date_time" => $ride_details->drop_date_time,
                "free_travel_km" => $count_km_limit,
                'service_date_time' => date('Y-m-d H:i:s',strtotime($ride_details->created_at)),
                'schedule_order_date_time' => $ride_details->pickup_date_time,
                "travel_km" => (($ride_details->start_km != 0 && $ride_details->end_km != 0) ? $ride_details->end_km - $ride_details->start_km : 0),
                "amount_per_hour" => round($ride_details->rental_amount_per_hour * $currency, 2),
                "travel_hour" => ($hours != 0 ? ($hours . " Hours ") : "") . ($minutes != 0 ? ($minutes . " Min") : "1 Min"),
                "ride_fare" => round($ride_details->ride_fare * $currency, 2),
                "additional_fare" => round($ride_details->additional_fare * $currency, 2),
                "subtotal" => round(($ride_details->ride_fare + $ride_details->additional_fare) * $currency, 2),
                "discount_cost" => round($ride_details->discount * $currency, 2),
                "tax_cost" => round($ride_details->tax * $currency, 2),
                "total_pay" => round($ride_details->total_pay * $currency, 2),
                "payment_type" => $ride_details->payment_type,
                "ride_status" => $ride_details->status,
                "promocode_discount" => $promocode_details != Null ? round($promocode_details->discount_amount * $currency, 2) : 0,
                "promocode_name" => $promocode_details != Null ? $promocode_details->promocode_name : '',
            ]);

        } else {
            return response()->json([
                "status" => 0,
//                "message" => "something went to wrong",
                "message" => __('user_messages.9'),
                "message_code" => 9,
            ]);
        }
    }

    public function postTransportRentalRideUpdateStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "user_id" => "required|numeric",
            "access_token" => "required|numeric",
            "service_category_id" => "required|numeric|in:31,32",
            "ride_id" => "required|numeric",
            "ride_status" => "required|in:2,4,5,7,8,9",
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }

        $user_check = $this->userClassapi->checkUserAllow($request->get('user_id'), $request->get('access_token'));
        if ($decoded['status'] = json_decode($user_check) == false) {
            return $user_check;
        }

        $ride_id = $request->get('ride_id');
        $ride_details = TransportRentalRideBooking::query()->where('id', $ride_id)->where('user_id', $user_check->id)->first();
        if ($ride_details != Null) {
            if ($ride_details->status == 2) {
                return response()->json([
                    "status" => 0,
//                    "message" => "Sorry, Your ride is cancelled",
                    "message" => __('user_messages.65'),
                    "message_code" => 65,
                ]);
            }
            if ($ride_details->status == 9) {
                return response()->json([
                    "status" => 0,
//                    "message" => "Your ride is completed",
                    "message" => __('user_messages.21'),
                    "message_code" => 21,
                ]);
            }
            if ($ride_details->status == 10) {
                return response()->json([
                    "status" => 0,
//                    "message" => "Sorry, Your ride is failed!",
                    "message" => __('user_messages.65'),
                    "message_code" => 65,
                ]);
            }

            $update_status = $request->get('ride_status');
            $redirect_url = "";
            $success_url = "";
            $failed_url = "";
            switch ($update_status) {
                case $update_status == 2:
                    if ($ride_details->status == 0 || $ride_details->status == 1) {
                        $validator = Validator::make($request->all(), [
                            "reason" => "required",
                        ]);
                        if ($validator->fails()) {
                            return response()->json([
                                "status" => 0,
                                "message" => $validator->errors()->first(),
                                "message_code" => 9,
                            ]);
                        }
                        $ride_status = $ride_details->status;
                        $ride_details->otp = null;
                        $ride_details->status = $update_status;
                        $ride_details->cancel_reason = $request->get('reason');
                        $ride_details->cancel_by = "user";
                        $ride_details->save();

                        try {
                            TransportRideDriverToken::query()->where('ride_id', $ride_details->id)->where('service_cat_id', $ride_details->service_cat_id)->delete();
                        }catch (\Exception $e){}

                        if ($ride_status < 5){
                            if ($ride_details->promo_code > 0){
                                $used_promocode_details = UsedPromocodeDetails::query()->where('id', $ride_details->promo_code)->first();
                                if ($used_promocode_details != Null) {
                                    $get_promocode = PromocodeDetails::query()->where('id', $used_promocode_details->promocode_id)->first();
                                    if ($get_promocode != Null) {
                                        $count_promocode = $get_promocode->total_usage - 1;
                                        $get_promocode->total_usage = ($count_promocode > 0)? $count_promocode : 0;
                                        $get_promocode->save();
                                    }
                                    $used_promocode_details->status = 2;
                                    $used_promocode_details->save();
                                }
                            }
                        }

                        if ($ride_details->driver_id != Null) {
                            $this->notificationClass->driverTransportRentalNotification($ride_details->id);
                        }
                    } else {
                        return response()->json([
                            "status" => 0,
//                            "message" => "you have not allowed to cancel this ride",
                            "message" => __('user_messages.123'),
                            "message_code" => 123,
                        ]);
                    }
                    break;
                case $update_status == 4:
                    if ($ride_details->status == 3) {
                        $ride_details->status = $update_status;
                        $ride_details->save();

                        $this->notificationClass->driverTransportRentalNotification($ride_details->id);
                    }
                    break;
                case $update_status == 5:
                    if ($ride_details->status == 4) {
                        $ride_details->status = $update_status;
                        $ride_details->save();

                        $this->notificationClass->driverTransportRentalNotification($ride_details->id);
                    }
                    break;
                case $update_status == 7:
                    $validator = Validator::make($request->all(), [
                        "payment_type" => "required|integer|in:1,2,3",
                        //"card_id" => "required_if:payment_type,==,2",
                    ]);
                    if ($validator->fails()) {
                        return response()->json([
                            "status" => 0,
                            "message" => $validator->errors()->first(),
                            "message_code" => 9,
                        ]);
                    }
                    if ($ride_details->status == 6) {
                        if ($request->get('payment_type') == 1) {
                            $ride_details->status = $update_status;
                            $ride_details->payment_type = $request->get('payment_type');
                            $ride_details->payment_status = 1;
                            $ride_details->save();

                            $this->notificationClass->driverTransportRentalNotification($ride_details->id);
                        }
                        elseif ($request->get('payment_type') == 3) {
                            $service_category = ServiceCategory::query()->where('id', $ride_details->service_cat_id)->first();
                            if ($service_category == Null) {
                                return response()->json([
                                    "status" => 0,
//                                    "message" => "something want to wrong!",
                                    "message" => __('user_messages.9'),
                                    "message_code" => 9,
                                ]);
                            }
                            if ($ride_details->payment_status == 0) {
                                $wallet_balance = UserWalletTransaction::query()->where('user_id', $request->get('user_id'))->orderBy('id', 'desc')->first();
                                if ($wallet_balance != Null) {
                                    $balance = $wallet_balance->remaining_balance;
                                } else {
                                    $balance = 0;
                                }
                                if ($ride_details->total_pay > $balance) {
                                    return response()->json([
                                        "status" => 0,
//                                        "message" => "You can't pay ride amount through Wallet because your wallet balance is insufficient.",
                                        "message" => __('user_messages.109'),
                                        "message_code" => 109
                                    ]);
                                }
                                $add_balance = new UserWalletTransaction();
                                $add_balance->user_id = $request->get('user_id');
                                $add_balance->wallet_provider_type = $this->user_type;
                                $add_balance->transaction_type = 2;
                                $add_balance->amount = $ride_details->total_pay;
                                //$add_balance->subject = "Paid To Ride - Order #" . $ride_details->ride_no;
                                $add_balance->subject = "Paid to " . $ride_details->driver_name . " - " . $service_category->name . " Booking # " . $ride_details->ride_no;
                                $add_balance->remaining_balance = round($balance - $ride_details->total_pay, 2);
                                $add_balance->subject_code = 4;
                                $add_balance->order_no = $ride_details->ride_no;
                                $add_balance->save();

                                $ride_details->status = $update_status;
                                $ride_details->payment_type = $request->get('payment_type');
                                $ride_details->payment_status = 1;
                                $ride_details->save();

                                $this->notificationClass->driverTransportRentalNotification($ride_details->id);
                            }
                        }
                        elseif ($request->get('payment_type') == 2) {
                            if ($ride_details->payment_status == 0) {
                                $user_currency = WorldCurrency::query()->where('symbol', $user_check['currency'])->first();
                                if ($user_currency == Null) {
                                    $user_currency = WorldCurrency::query()->where('default_currency', 1)->first();
                                }
                                $currency = $user_currency != Null ? $user_currency->ratio : 1;
                                $currency_code = $user_currency != Null ? $user_currency->currency_code : '';
                                $ride_details->status = $update_status;
                                $ride_details->payment_type = $request->get('payment_type');
                                $ride_details->payment_status = 1;
                                $ride_details->save();

                                $this->notificationClass->driverTransportRentalNotification($ride_details->id);
                            }
//                            if ($ride_details->payment_status == 0) {
//
//                                $validator = Validator::make($request->all(), [
//                                    "payment_method_type" => "nullable|in:1,0",
//                                ]);
//                                if ($validator->fails()) {
//                                    return response()->json([
//                                        "status" => 0,
//                                        "message" => $validator->errors()->first(),
//                                        "message_code" => 9,
//                                    ]);
//                                }
//                                if ($request->get('payment_method_type') == 1) {
//
//                                    $amount_to_php = round($ride_details->total_pay, 2);
//                                    if ($amount_to_php < 100 || $amount_to_php > 99999999) {
//                                        return response()->json([
//                                            "status" => 0,
//                                            "message" => "With paymongo min amount must be ₱100 and max amount must be ₱99999999",
//                                            "message_code" => 158,
//                                        ]);
//                                    }
//
//                                    $success_url = \URL::route('paypmongo.success');
//                                    $failed_url = \URL::route('paypmongo.failed');
//                                    $error_url = \URL::route('paypmongo.failed');
//                                    //$error_url = \URL::route('mongo_pay.error');
//
//                                    try {
//                                        $user_id = $request->get('user_id');
//                                        $service_category_id = $ride_details->service_cat_id;
//                                        $order_id = $ride_details->id;
//
//
//                                        $mongo_success_url = \URL::route('mongo_pay.success', ["id" => $user_id, "order_id" => $order_id]);
//                                        $mongo_failed_url = \URL::route('mongo_pay.failed', ["id" => $user_id, "order_id" => $order_id]);
//
//                                        $gcashSource = Paymongo::source()->create([
//                                            'type' => 'gcash',
//                                            'amount' => $amount_to_php,
//                                            'currency' => 'PHP',
//                                            'user_id' => $user_id,
//                                            'redirect' => [
//                                                'success' => $mongo_success_url,
//                                                'failed' => $mongo_failed_url
//                                            ]
//
//                                        ]);
//
//                                        $get_gcashSource_data = $gcashSource->getData();
//                                        $mongo_success_url = $get_gcashSource_data['redirect']['checkout_url'];
//                                        $gcashSource_status = $get_gcashSource_data['status'];
//                                        $source_type = $get_gcashSource_data['source_type'];
//                                        $gcashSource_id = $get_gcashSource_data['id'];
//                                        if ($mongo_success_url != "" && $gcashSource_status == "pending" && $source_type == "gcash") {
//
//                                            $ride_details->payment_type = $request->get('payment_type');
//                                            $ride_details->save();
//
//                                            $tempUserBooking = New TempUserBooking();
//                                            $tempUserBooking->user_id = $user_id;
//                                            $tempUserBooking->service_cat_id = $service_category_id;
//                                            $tempUserBooking->booking_id = $order_id;
//                                            $tempUserBooking->transaction_id = $gcashSource_id;
//                                            $tempUserBooking->payment_method_type = 1;
//                                            $tempUserBooking->coupon_ids = 0;
//                                            $tempUserBooking->amount = round($amount_to_php, 2);
//                                            $tempUserBooking->payment_status = 0;
//                                            $tempUserBooking->save();
//
//                                            $redirect_url = $mongo_success_url;
//
//                                        } else {
//                                            return response()->json([
//                                                "status" => 0,
//                                                "message" => "Payment transaction failed",
//                                                "message_code" => 108,
//                                            ]);
//                                        }
//                                    } catch (\Exception $e) {
//                                        return response()->json([
//                                            "status" => 0,
//                                            "message" => $e->getMessage(),
//                                            "message_code" => 108,
//                                        ]);
//                                    }
//
//                                }
//                                elseif ($request->get('payment_method_type') == 0) {
//
//                                    $success_url = \URL::route('paypal.success');
//                                    $failed_url = \URL::route('paypal.failed');
//                                    $convert_to_currency = WorldCurrency::query()->where('symbol', "$")->first();
//                                    if ($convert_to_currency == Null) {
//                                        return response()->json([
//                                            'status' => 0,
//                                            'message' => "Something went to wrong!",
//                                            "message_code" => 9,
//                                        ]);
//                                    }
//                                    $settings = GeneralSettings::query()->first();
//                                    if ($settings == Null && $settings->paypal_sandbox == Null && $settings->paypal_client_id == Null && $settings->paypal_client_secret_key == Null) {
//                                        return response()->json([
//                                            'status' => 0,
//                                            'message' => "Something went to wrong!",
//                                            "message_code" => 9,
//                                        ]);
//                                    }
//                                    $amount_to_usd = round($ride_details->total_pay * $convert_to_currency->ratio, 2);
//
//                                    try {
//                                        $user_id = $request->get('user_id');
//                                        $service_category_id = $ride_details->service_cat_id;
//                                        $order_id = $ride_details->id;
//
//                                        $paypal_sandbox = $settings->paypal_sandbox . "";
//                                        $paypal_client_id = $settings->paypal_client_id . "";
//                                        $paypal_client_secret_key = $settings->paypal_client_secret_key . "";
//
//                                        $api_context = new ApiContext(new OAuthTokenCredential($paypal_client_id, $paypal_client_secret_key));
//
//                                        $settings = array(
//                                            'mode' => $paypal_sandbox,
//                                            'http.ConnectionTimeOut' => 30,
//                                            'log.LogEnabled' => true,
//                                            'log.FileName' => storage_path() . '/logs/paypal.log',
//                                            'log.LogLevel' => 'ERROR'
//                                        );
//                                        $api_context->setConfig($settings);
//
//                                        $payer = new Payer();
//                                        $payer->setPaymentMethod('paypal');
//
//                                        $item_1 = new Item();
//                                        $item_1->setName('Item 1')
//                                            ->setCurrency('USD')
//                                            ->setQuantity(1)
//                                            ->setPrice($amount_to_usd);
//
//                                        $item_list = new ItemList();
//                                        $item_list->setItems(array($item_1));
//
//                                        $amount = new Amount();
//                                        $amount->setCurrency('USD')
//                                            ->setTotal($amount_to_usd);
//
//                                        $transaction = new Transaction();
//                                        $transaction->setAmount($amount)
//                                            ->setItemList($item_list)
//                                            ->setCustom($user_id . ',' . $service_category_id . ',' . $order_id)
//                                            ->setDescription('Your transaction description');
//
//                                        $redirect_urls = new RedirectUrls();
//                                        $redirect_urls->setReturnUrl(\URL::route('payment.status'))/** Specify return URL **/
//                                        ->setCancelUrl(\URL::route('payment.status'));
//
//                                        $payment = new Payment();
//                                        $payment->setIntent('Sale')
//                                            ->setPayer($payer)
//                                            ->setRedirectUrls($redirect_urls)
//                                            ->setTransactions(array($transaction));
//
//                                        try {
//                                            $payment->create($api_context);
//                                        } catch (\PayPal\Exception\PPConnectionException $ex) {
//                                            if (\Config::get('app.debug')) {
//                                                return response()->json([
//                                                    "status" => 0,
//                                                    "message" => "Connection timeout",
//                                                    "message_code" => 9,
//                                                ]);
//                                            } else {
//                                                return response()->json([
//                                                    "status" => 0,
//                                                    "message" => "your payment transaction failed",
//                                                    "message_code" => 9,
//                                                ]);
//                                            }
//                                        }
//                                        foreach ($payment->getLinks() as $link) {
//                                            if ($link->getRel() == 'approval_url') {
//                                                $redirect_url = $link->getHref();
//                                                break;
//                                            }
//                                        }
//                                        \Session::put('paypal_payment_id', $payment->getId());
//                                        if (isset($redirect_url)) {
//                                            $ride_details->payment_type = $request->get('payment_type');
//                                            //$ride_details->payment_status = 1;
//                                            $ride_details->transaction_id = $payment->getId();
//                                            $ride_details->save();
//                                        } else {
//                                            return response()->json([
//                                                "status" => 0,
//                                                "message" => "your payment transaction failed",
//                                                "message_code" => 108,
//                                            ]);
//                                        }
//                                    } catch (\Exception $e) {
//                                        return response()->json([
//                                            "status" => 0,
//                                            "message" => $e->getMessage(),
//                                            "message_code" => 108,
//                                        ]);
//                                    }
//                                }
//                                else {
//                                    return response()->json([
//                                        "status" => 0,
//                                        "message" => "something went to wrong",
//                                        "message_code" => 9,
//                                    ]);
//                                }
//
////                                $ride_details->status = $update_status;
////                                $ride_details->payment_type = $request->get('payment_type');
////                                $ride_details->payment_status = 1;
////                                $ride_details->save();
////                                $this->notificationClass->driverTransportRentalNotification($ride_details->id);
//                            }
                        } else {
                            return response()->json([
                                "status" => 0,
//                                "message" => "something went to wrong",
                                "message" => __('user_messages.9'),
                                "message_code" => 9,
                            ]);
                        }
//                        $this->notificationClass->driverTransportRentalNotification($ride_details->id);
                    }
                    break;
                case $update_status == 8:
                    $validator = Validator::make($request->all(), [
                        "rating" => "required",
                        "comment" => "nullable",
                    ]);
                    if ($validator->fails()) {
                        return response()->json([
                            "status" => 0,
                            "message" => $validator->errors()->first(),
                            "message_code" => 9,
                        ]);
                    }
                    $driver_details_id = TransportDriverDetails::query()->select('provider_services.id as provider_services_id')
                        ->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
                        ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
                        ->where('transport_driver_details.id', $ride_details->driver_id)
                        ->first();
                    if ($driver_details_id == Null) {
                        return response()->json([
                            "status" => 0,
//                            "message" => "something went to wrong!",
                            "message" => __('user_messages.9'),
                            "message_code" => 9,
                        ]);
                    }
                    $check_driver = TransportDriverDetails::query()->where('provider_service_id', $driver_details_id->provider_services_id)->first();
                    if ($ride_details != Null && $check_driver != Null) {
                        $driver_rating = TransportRatings::query()->where('driver_id', $check_driver->id)->where('rental_id', $ride_details->id)->where('user_id', $request->get('user_id'))->first();
                        if ($driver_rating == Null) {
                            $driver_rating = new TransportRatings();
                        }
                        $driver_rating->driver_id = $check_driver->id;
                        $driver_rating->user_id = $request->get('user_id');
                        $driver_rating->rental_id = $ride_details->id;
                        $driver_rating->rating = $request->get('rating');
                        if ($request->get('comment') != Null) {
                            $driver_rating->comment = $request->get('comment');
                        }
                        $driver_rating->save();

                        $ratings = TransportRatings::query()->select(DB::raw('avg(rating) as ratings'))
                            ->groupBy('driver_id')
                            ->where('driver_id', $check_driver->id)
                            ->first();
                        $check_driver->rating = $ratings->ratings;
                        $check_driver->save();
                    } else {
                        return response()->json([
                            "status" => 0,
//                            "message" => "something went to wrong!",
                            "message" => __('user_messages.9'),
                            "message_code" => 9,
                        ]);
                    }
                    if ($ride_details->status == 7) {
                        $ride_details->status = $update_status;
                        $ride_details->save();

                        $this->notificationClass->driverTransportRentalNotification($ride_details->id);
                    }
                    break;
                case $update_status == 9:
                    if ($ride_details->status == 8) {
                        $ride_details->status = $update_status;
                        $ride_details->save();

                        $this->notificationClass->driverTransportRentalNotification($ride_details->id);
                    }
                    break;
                default:
                    return response()->json([
                        "status" => 0,
//                        "message" => "something went to wrong!",
                        "message" => __('user_messages.9'),
                        "message_code" => 9,
                    ]);
            }

            return response()->json([
                "status" => 1,
//                "message" => "success",
                "message" => __('user_messages.1'),
                "message_code" => 1,
                "ride_status" => $ride_details->status,
                "redirect_url" => $redirect_url,
                "success_url" => $success_url,
                "failed_url" => $failed_url
            ]);
        } else {
            return response()->json([
                "status" => 0,
//                "message" => "something went to wrong",
                "message" => __('user_messages.9'),
                "message_code" => 9,
            ]);
        }
    }
}
