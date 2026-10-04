<?php

namespace App\Http\Controllers\Api\Transport;

use App\Classes\AdminClass;
use App\Classes\NotificationClass;
use App\Classes\UserClassApi;
use App\model\GeneralSettings;
use App\model\PromocodeDetails;
use App\model\Provider;
use App\model\ProviderServices;
use App\model\ProviderUserRunningService;
use App\model\RestrictedArea;
use App\model\ServiceCategory;
use App\model\ServiceSettings;
use App\model\SurchargeTimings;
use App\model\TempUserBooking;
use App\model\TransportCategoryList;
use App\model\TransportDriverDetails;
use App\model\TransportHomePage;
use App\model\TransportRentalRideBooking;
use App\model\TransportVehicleType;
use App\model\TransportRatings;
use App\model\TransportRideBook;
use App\model\TransportWeightList;
use App\model\User;
use App\model\UserCardDetails;
use App\model\UserReferHistory;
use App\model\UserRideWayPoint;
use App\model\UserRunningRide;
use App\model\UserWalletTransaction;
use App\model\WorldCurrency;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    //json response status [
    //    0 => false,
    //    1 => true,
    //    2 => registration pending,
    //    3 => app user blocked,
    //    4 => app user access token not match,
    //    5 => app user not found
    //  ]

    //ApiLogDetail logger type => 0:user,1:store,2:driver,3:provider
    private $userClassapi;
    private $notificationClass;
    private $bikeService;
    private $taxiService;
    private $courierService;
    private $adminClass;
    private $user_type = 0;

    public function __construct(UserClassApi $userClassapi, NotificationClass $notificationClass, AdminClass $adminClass)
    {
        $this->adminClass = $adminClass;
        $this->userClassapi = $userClassapi;
        $this->notificationClass = $notificationClass;
        $this->bikeService = 1;
        $this->taxiService = 2;
        $this->courierService = 4;
    }

    public function postTransportServiceType(Request $request)
    {
        $this->notificationClass->ApiLogDetail($logger_type =0, $request->get('user_id'), "postTransportServiceType", $request->all());

        $validator = Validator::make($request->all(), [
                "user_id" => "required|numeric",
                "access_token" => "required|numeric",
                "service_category_id" => "required|numeric|in:1,2,4",
                "current_lat" => "required",
                "current_long" => "required",
                "handicap" => "nullable|in:0,1",
                "child_seat" => "nullable|in:0,1",
                "gender" => "nullable|in:0,1,2",
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
        $currency = $user_currency->ratio;

        $service_types = TransportVehicleType::query()->select('id', 'name', 'icon_name',
            DB::raw('ROUND(cost_for_km * ' . $currency . ',2) As cost_for_km'),
            DB::raw('ROUND(time_fare * ' . $currency . ',2) As time_fare'),
            DB::raw('ROUND(base_fare * ' . $currency . ',2) As base_fare'),
            DB::raw('ROUND(min_fare_amount * ' . $currency . ',2) As min_fare_amount'),
            'weight_limit', 'dimension_limit'
//            , 'base_fare', 'min_fare_amount'
            ,'weight_limit','width_limit','height_limit','service_cat_id'
        )
            ->where('status', '=', 1)
            ->orderBy('cost_for_km', 'asc');

        /*if($request->get('service_category_id') == 4){
            $service_types->whereIn('service_cat_id', [1,2]);
        } else{
            $service_types->where('service_cat_id', $request->get('service_category_id'));
        }*/
        $service_types->where('service_cat_id', $request->get('service_category_id'));
        $service_types = $service_types->get();

        $expire_date_time = date('Y-m-d H:i:s', strtotime("-120 minutes"));
        $lat = $request->get('current_lat');
        $long = $request->get('current_long');
        $service_cat_id = $request->get('service_category_id');
        $service_setting = ServiceSettings::where('service_cat_id', $service_cat_id)->first();
        if ($service_setting != Null && $service_setting->provider_search_radius != null) {
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

        if ($service_cat_id == $this->courierService) {
            $provider_lat_long = Provider::select('transport_driver_details.vehicle_type_id')
                ->whereRaw(DB::raw("(6371 * acos( cos( radians(" . $lat . ") ) * cos( radians( current_lat ) )  * cos( radians( current_long ) - radians(" . $long . ") ) + sin( radians(" . $lat . ") ) * sin(radians( current_lat ) ) ) ) < " . $radius))
                ->join('provider_services', 'provider_services.provider_id', '=', 'providers.id')
//                        ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
                ->join('transport_driver_details', 'transport_driver_details.provider_service_id', '=', 'provider_services.id')
//                ->join('transport_vehicle_type', 'transport_vehicle_type.id', '=', 'transport_driver_details.vehicle_type_id')
//                ->where('service_category.id', $service_cat_id)
//                ->whereIn('service_category.id', [$service_cat_id, $this->bikeService])
                ->where('providers.status', 1)
                ->where('provider_services.status', 1)
                ->where('provider_services.current_status', 1)
                ->where('transport_driver_details.courier_boy', 1)
                ->where('transport_driver_details.last_online_date_time', '>=', $expire_date_time)
                ->whereNotNull('transport_driver_details.current_lat')
                ->whereNotNull('transport_driver_details.current_long')
                ->whereNotNull('providers.access_token')
                ->whereNotNull('providers.device_token');
//                ->groupBy('transport_driver_details.vehicle_type_id')->GET();
        }
        elseif ($service_cat_id == $this->bikeService){
            $provider_lat_long = Provider::select('transport_driver_details.vehicle_type_id',
                DB::raw("(6371 * acos( cos( radians(" . $lat . ") ) * cos( radians( current_lat ) )  * cos( radians( current_long ) - radians(" . $long . ") ) + sin( radians(" . $lat . ") ) * sin(radians( current_lat ) ) ) ) as distance "))
                //->whereRaw(DB::raw("(6371 * acos( cos( radians(" . $lat . ") ) * cos( radians( current_lat ) )  * cos( radians( current_long ) - radians(" . $long . ") ) + sin( radians(" . $lat . ") ) * sin(radians( current_lat ) ) ) ) < " . $radius))
                ->join('provider_services', 'provider_services.provider_id', '=', 'providers.id')
                //->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
                ->join('transport_driver_details', 'transport_driver_details.provider_service_id', '=', 'provider_services.id')
                //->where('service_category.id', $service_cat_id)
                ->where('providers.status', 1)
                ->where('provider_services.status', 1)
                ->where('provider_services.current_status', 1)
                ->where('transport_driver_details.driver_service', 1)
                ->where('transport_driver_details.last_online_date_time', '>=', $expire_date_time)
                ->whereNotNull('transport_driver_details.current_lat')
                ->whereNotNull('transport_driver_details.current_long')
                ->whereNotNull('providers.access_token')
                ->whereNotNull('providers.device_token');
            if (in_array($request->get('gender'),[1,2])) {
                $provider_lat_long->where('providers.gender', $request->get('gender'));
            }
        }
        else {
            $provider_lat_long = Provider::select('transport_driver_details.vehicle_type_id')
                ->whereRaw(DB::raw("(6371 * acos( cos( radians(" . $lat . ") ) * cos( radians( current_lat ) )  * cos( radians( current_long ) - radians(" . $long . ") ) + sin( radians(" . $lat . ") ) * sin(radians( current_lat ) ) ) ) < " . $radius))
                ->join('provider_services', 'provider_services.provider_id', '=', 'providers.id')
//                        ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
                ->join('transport_driver_details', 'transport_driver_details.provider_service_id', '=', 'provider_services.id')
//                ->where('service_category.id', $service_cat_id)
                ->where('providers.status', 1)
                ->where('provider_services.status', 1)
                ->where('provider_services.current_status', 1)
//                ->where('transport_driver_details.handicap', $request->get('handicap'))
//                ->where('transport_driver_details.child_seat', $request->get('child_seat'))
                ->where('transport_driver_details.driver_service', 1)
                ->where('transport_driver_details.last_online_date_time', '>=', $expire_date_time)
                ->whereNotNull('transport_driver_details.current_lat')
                ->whereNotNull('transport_driver_details.current_long')
                ->whereNotNull('providers.access_token')
                ->whereNotNull('providers.device_token');
//                ->groupBy('transport_driver_details.vehicle_type_id');
            if ($request->get('handicap') == 1) {
                $provider_lat_long->where('transport_driver_details.handicap', 1);
            }
            if ($request->get('child_seat') == 1) {
                $provider_lat_long->where('transport_driver_details.child_seat', 1);
            }
            if (in_array($request->get('gender'),[1,2])) {
                $provider_lat_long->where('providers.gender', $request->get('gender'));
            }
        }

        if ($not_multi_delivery_provider_id != Null) {
            $provider_lat_long = $provider_lat_long->whereNotIn('providers.id', $not_multi_delivery_provider_id);
        }

        $provider_lat_long = $provider_lat_long->get()->pluck('vehicle_type_id')->toArray();

        $service_type = [];
        $check_service_id_bike = TransportVehicleType::query()
            ->whereIn('service_cat_id',[1])
            ->get()->pluck('id')->toArray();

        $check_service_id_car = TransportVehicleType::query()
            ->whereIn('service_cat_id',[2])
            ->get()->pluck('id')->toArray();
        if (!$service_types->isEmpty()) {
            foreach ($service_types as $key => $type) {
                $service_status = 0;
                if($this->courierService == $service_cat_id){
                    if(!in_array($type->id,[10,14])){
                        if (in_array($type->id, $provider_lat_long)) {
                            $service_status = 1;
                        }
                    } else{
                        if ($type->id == 10){
                            $result = array_intersect($provider_lat_long,$check_service_id_bike);
                            if(count($result) > 0){
                                $service_status = 1;
                            }
                        } else{
                            $result = array_intersect($provider_lat_long,$check_service_id_car);
                            if(count($result) > 0){
                                $service_status = 1;
                            }
                        }
                    }
                } else{
                    if (in_array($type->id, $provider_lat_long)) {
                        $service_status = 1;
                    }
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
                    "cost_per_km" => $type->cost_for_km,
                    "weight_limit" => $type->weight_limit != Null ? $type->weight_limit : 0,
                    "dimension_limit" => $type->dimension_limit != Null ? $type->dimension_limit : '',
                    "base_fare" => $type->base_fare,
                    "cost_per_min" => $type->time_fare,//,'weight_limit','width_limit','height_limit'
                    "width_limit" => $type->width_limit != Null ? round($type->width_limit, 1) : 0,
                    "height_limit" => $type->height_limit != Null ? round($type->height_limit, 1) : 0,
                    "min_fare_amount" => $type->min_fare_amount,
                    "currency_ratio" => $currency - 0,
                    "service_status" => $service_status,
                ];
            }
            return response()->json([
                "status" => 1,
//                "message" => "Success",
                'message' => __('user_messages.1'),
                "message_code" => 1,
                "service_type" => $service_type,
            ]);
        } else {
            return response()->json([
                "status" => 0,
//                "message" => "vehicle type not Found!",
                'message' => __('user_messages.9'),
                "message_code" => 9,
            ]);
        }
    }

    public function postTransportRideBooking(Request $request)
    {
        $this->notificationClass->ApiLogDetail($logger_type =0, $request->get('user_id'), "postTransportRideBooking", $request->all());
        $validator = Validator::make($request->all(), [
            "user_id" => "required",
            "access_token" => "required",
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

        $ride_book = [];
        if ($request->get('ride_id') != Null) {
            $ride = TransportRideBook::query()->where('id', '=', $request->get('ride_id'))->first();
            if ($ride == Null) {
                $ride_book = $this->userClassapi->TransportRideBook($request, $user_check);
            } else {
                $service_setting = ServiceSettings::query()->where('service_cat_id', $request->get('service_category_id'))->first();
                if ($service_setting == Null) {
                    return response()->json([
                        "status" => 0,
//                        "message" => "something went to wrong!",
                        'message' => __('user_messages.9'),
                        "message_code" => 9,
                    ]);
                }
                $general_setting =  GeneralSettings::query()->first();
                if ($general_setting != Null) {
                    $timeout = ($general_setting->user_timeout > 0) ? $general_setting->user_timeout : 60;
                } else {
                    $timeout = 60;
                }
                $ride->no_of_retry = $ride->no_of_retry + 1;
                $ride->save();
                $this->notificationClass->driverRequestNotification($request->get('service_category_id'), $ride->id,$ride->driver_gender);
                $pickup = array_map('trim', explode(",", $ride->pickup_latlong));
                $destination = array_map('trim', explode(",", $ride->destination_latlong));
                $way_points[] = [
                    "address" => $ride->pickup_address,
                    "address_lat" => trim($pickup[0]),
                    "address_long" => trim($pickup[1])
                ];
                if ($ride->is_way_point == 1) {
                    $ride_way_point = UserRideWayPoint::query()->where('ride_id', $ride->id)->first();
                    if ($ride_way_point != Null) {
                        if ($ride_way_point->way_point_1 != Null && $ride_way_point->lat_long_1 != Null) {
                            $lat_long_1 = array_map('trim', explode(",", $ride_way_point->lat_long_1));
                            $way_points[] = [
                                "address" => $ride_way_point->way_point_1,
                                "address_lat" => trim($lat_long_1[0]),
                                "address_long" => trim($lat_long_1[1])
                            ];
                        }
                        if ($ride_way_point->way_point_2 != Null && $ride_way_point->lat_long_2 != Null) {
                            $lat_long_2 = explode(",", $ride_way_point->lat_long_2);
                            $way_points[] = [
                                "address" => $ride_way_point->way_point_2,
                                "address_lat" => trim($lat_long_2[0]),
                                "address_long" => trim($lat_long_2[1])
                            ];
                        }
                        if ($ride_way_point->way_point_3 != Null && $ride_way_point->lat_long_3 != Null) {
                            $lat_long_3 = explode(",", $ride_way_point->lat_long_3);
                            $way_points[] = [
                                "address" => $ride_way_point->way_point_3,
                                "address_lat" => trim($lat_long_3[0]),
                                "address_long" => trim($lat_long_3[1])
                            ];
                        }

                    }
                }
                $way_points[] = [
                    "address" => $ride->destination_address,
                    "address_lat" => trim($destination[0]),
                    "address_long" => trim($destination[1])
                ];
                return response()->json([
                    "status" => 1,
//                    "message" => "Ride Booking Successfully!",
                    'message' => __('user_messages.1'),
                    "message_code" => 1,
                    "ride_id" => $ride->id,
                    "booking_no" => $ride->ride_no,
                    "pickup_date_time" => $this->notificationClass->dateLangConvert(Date('d M, Y', strtotime($ride->pickup_datetime)),$user_check->language),
                    //"pickup_address" => $ride->pickup_address,
                    //"destination_address" => $ride->destination_address,
                    "address_list" => $way_points,
                    "ride_status" => $ride->status,
                    "accept_time_out" => round($timeout)
                ]);
            }
        } else {
            $ride_book = $this->userClassapi->TransportRideBook($request, $user_check);
        }
        return $ride_book;
    }

    public function postTransportRidePreview(Request $request)
    {
        $this->notificationClass->ApiLogDetail($logger_type =0, $request->get('user_id'), "postTransportRidePreview", $request->all());

        $validator = Validator::make($request->all(), [
            "user_id" => "required",
            "access_token" => "required",
            "service_category_id" => "required|numeric|in:4",
            "estimated_time" => "required",
            "total_distance" => "required",
            "promo_code" => "nullable",
            "pickup_date_time" => "nullable",
            "payment_type" => "nullable|in:1,2,3",
            "tip" => "nullable|numeric",
            "service_type_id" => "nullable",//transport_vehicle_id
            "address_list" => "required",//address,address_lat,address_long,name,contact_number
            "weight_id" => "required|numeric",//transport_weight_id
//            "sender_shop_name" => "nullable",
//            "sender_landmark" => "nullable",
//            "recipient_landmark" => "nullable",
//            "category_id" => "required|numeric",//transport_category_id
//            "estimate_price" => "nullable|numeric",
//            "delivery_instruction" => "nullable",
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

        if ($user_check != Null) {
            $user_currency = WorldCurrency::query()->where('symbol', $user_check->currency)->first();
            if ($user_currency == Null) {
                $user_currency = WorldCurrency::query()->where('default_currency', 1)->first();
            }
        } else {
            $user_currency = WorldCurrency::query()->where('default_currency', 1)->first();
        }
        $currency = $user_currency->ratio;

        try {
            $address_list = json_decode($request->get('address_list'), true);
            $new_address_list = [];
            foreach ($address_list as $key => $address) {
//                if ($address['address'] == Null || $address['address_lat'] == Null || $address['address_long'] == Null|| $address['name'] == Null|| $address['contact_number'] == Null) {
                if ($address['address'] == Null || $address['address_lat'] == Null || $address['address_long'] == Null ) {
                    unset($address_list[$key]);
                }
                $new_address_list[] = $address;
            }
        }catch (\Exception $e) {
            return response()->json([
                "status" => 0,
//                "message" => "Invalid Json",
                "message" => __('user_messages.9'),
                "message_code" => 9,
            ]);
        }
//        if ($request->get('service_category_id') != 4){
//            $address_lat = trim($new_address_list[0]['address_lat']);
//            $address_long = trim($new_address_list[0]['address_long']);
//            $area_details = RestrictedArea::query()->where('status',1)->get();
//            $latitudes = $longitudes = '';
//            foreach ($area_details as $area){
//                $latitudes = $latitudes . $area->latitude . ',';
//                $longitudes = $longitudes . $area->longitude . ',';
//            }
//            $restricted_lat = explode(',',substr($latitudes, 0, -1));
//            $restricted_long = explode(',',substr($longitudes, 0, -1));
//            $points_polygon = count($restricted_lat);
//            if($this->adminClass->is_in_restricted_area($points_polygon,$restricted_lat,$restricted_long,$address_lat,$address_long)){
//                return response()->json([
//                    "status" => 0,
////                "message" => "Your pickup address exist in restricted area, please try with other location!",
//                    "message" => __('user_messages.194'),
//                    "message_code" => 194,
//                ]);
//            }
//        }

        $address_count = count($new_address_list);
        if ($address_count < 2) {
            return response()->json([
                "status" => 0,
//                "message" => "something went to wrong!",
                "message" => __('user_messages.9'),
                "message_code" => 9,
            ]);
        }

        $transport_weight_list = TransportWeightList::query()->where('id',$request->get('weight_id'))->first();
        if ($transport_weight_list == Null) {
            return response()->json([
                "status" => 0,
//                "message" => "Vehicle Type Not Found!",
                "message" => __('user_messages.9'),
                "message_code" => 9,
            ]);
        }

        //find price service_type
        $get_vehicle_price = TransportVehicleType::query()
            ->where('service_cat_id','=', $request->get('service_category_id'))
            ->where('weight_limit', '!=',null)
            ->orderBy('weight_limit', 'asc');
//            if ($transport_weight_list->close_limit != 0){
//                $get_vehicle_price->where('weight_limit','<',$transport_weight_list->close_limit)
//                    ->where('weight_limit','>=',$transport_weight_list->start_limit);
//            }else{
                $get_vehicle_price->where('weight_limit','=',$transport_weight_list->id);
//            }
        $get_vehicle_price = $get_vehicle_price->first();
        if ($get_vehicle_price == Null) {
            return response()->json([
                "status" => 0,
//                "message" => "Vehicle Type Not Found!",
                "message" => __('user_messages.9'),
                "message_code" => 9,
            ]);
        }

        $service_settings = ServiceSettings::query()->where('service_cat_id', $request->get('service_category_id'))->first();
        if ($service_settings != Null) {
            $get_tax = $service_settings->tax;
        } else {
            $get_tax = 0;
        }

        $total_distance = $request->get('total_distance');

//        $user_address = urlencode($address_lat.','.$address_long);
//        $delivery_lat = trim($new_address_list[1]['address_lat']);
//        $delivery_long = trim($new_address_list[1]['address_long']);
//        $origin = urlencode($delivery_lat . "," . $delivery_long);
//        $get_coordinates = $this->userClassapi->get_coordinates($origin, $user_address);
//        $coordinates_check = json_decode($get_coordinates, TRUE);
//        if ($coordinates_check == 0) {
//            return response()->json([
//                "status" => 0,
//                "message" => "something went to wrong!",
//                "message_code" => 9,
//            ]);
//        }
//        if (count($coordinates_check['routes']) > 0) {
//            $total_distance = preg_replace("/[^0-9.]/", "", $coordinates_check['routes'][0]['legs'][0]['distance']['value']);
//            $total_distance = ($total_distance * 0.001);
//        }

        try{
            $km = explode(' ', $total_distance);
            $km_count = floatval($km[0]);
            $distance_km = $km_count;

            $eta = explode(' ', $request->get('estimated_time'));
            $eta_count = intval($eta[0]);

        } catch (\Exception $e) {
            $distance_km = 0;
            $eta_count = 0;
        }
        $base_fare = round(($get_vehicle_price->base_fare), 2);
        $total_distance_amount = round(($distance_km * $get_vehicle_price->cost_for_km), 2);
        $time_fare_amount = round(($eta_count * $get_vehicle_price->time_fare), 2);

        /*$promo_code_discount = 0;
        $promo_code_name = '';
        $promo_code_id = 0;
        if ($request->get('promo_code') != 0) {
            $get_discount_on_amount = ($base_fare + $total_distance_amount + $time_fare_amount);
            $min_fare_amount = round($get_vehicle_price->min_fare_amount,2);
            $adjustment_amount = 0;
            if ($get_discount_on_amount < $min_fare_amount) {
                $adjustment_amount = round(($min_fare_amount - $get_discount_on_amount), 2); //adjust amount
            }
            $discount_on_amount = ($get_discount_on_amount + $adjustment_amount);
            $promo_code = $this->userClassapi->checkPromoCodeValid($request->get('promo_code'), $discount_on_amount, $user_check->id, $request->get('service_category_id'));
            if ($promo_code != 0) {
                if ($promo_code != 1) {
                    $get_prmocode = PromocodeDetails::query()->where('id', $request->get('promo_code'))->where('status', 1)->first();
                    if ($get_prmocode != Null) {
                        $promo_code_discount = $promo_code;
                        $promo_code_name = $get_prmocode->promo_code;
                        $promo_code_id = $get_prmocode->id;
                    }
                }
            }
        }*/

        $promocode_name = '';
        $promo_code_apply = 0;
        $promo_code_status = 0;
        $promo_message_code = 0;
        $promo_code_message = "";
        $min_order_amount = 0;
        $promo_discount_cost = 0;
        $get_promo_code = $request->get('promo_code');
        $get_promo_code_id = 0;
        if ($get_promo_code != "" &&  $get_promo_code!= Null ) {

            $promo_code_apply = 1;
            if (!is_numeric($get_promo_code)) {
                $get_promocode = PromocodeDetails::query()->where('promo_code', $get_promo_code)->where('service_cat_id', $request->get('service_category_id'))->where('status','1')->first();
                $get_promo_code_id = ($get_promocode != Null) ? $get_promocode->id : 0;
            }

            if ($get_promo_code_id != 0){
                $get_discount_on_amount = ($base_fare + $total_distance_amount + $time_fare_amount);
                $min_fare_amount = round($get_vehicle_price->min_fare_amount,2);
                $adjustment_amount = 0;
                if ($get_discount_on_amount < $min_fare_amount) {
                    $adjustment_amount = round(($min_fare_amount - $get_discount_on_amount), 2); //adjust amount
                }
                $discount_on_amount = ($get_discount_on_amount + $adjustment_amount);

//                    return $this->userClassapi->checkPromoCodeValid($get_promo_code_id, $discount_on_amount, $user_details->id, $request->get('service_category_id'));
                list($promo_code_status,$promo_message_code,$promo_code_message,$min_order_amount,$promo_code_amount,$promo_code_name) = $this->userClassapi->checkPromoCodeValid($get_promo_code_id, $discount_on_amount,  $user_check->id, $request->get('service_category_id'));
                $promo_code_status = $promo_code_status;
                $promo_message_code = $promo_message_code;
                $promo_code_message = $promo_code_message;
                $min_order_amount = $min_order_amount;
                $promo_discount_cost = $promo_code_amount;
                $promocode_name = $promo_code_name != Null ? $promo_code_name : "";

            }else{

                $promo_code_status = 0;
                $promo_message_code = 232;
                $promo_code_message = __('user_messages.232');
                $min_order_amount = 0;
            }
        }

        $tip = $request->get('tip') != null ? $request->get('tip') : 0;
        $step_1 = $base_fare + $total_distance_amount + $time_fare_amount; //ride amount
        $adjustment_amount = 0;
        if ($step_1 < $get_vehicle_price->min_fare_amount) {
            $adjustment_amount = round(($get_vehicle_price->min_fare_amount - $step_1), 2); //adjust amount
        }
        $step_2 = round(($step_1 + $adjustment_amount), 2); //add adjust amount
        $step_3 = $step_2 - $promo_discount_cost; //promo code discount
        $step_4 = round((($step_3 * $get_tax) / 100), 2); //tax count
        $step_5 = $step_3 + $step_4 + $tip; //count ride amount

        $tax = $step_4;
        $total_pay = $step_5;

        $refer_discount = 0;
        if ($user_check->pending_refer_discount > 0) {
            $user_refer_history = UserReferHistory::query()->where('user_id', $user_check->id)->where('user_status', 0)->first();
            $total_price = $total_pay;
            if ($user_refer_history != Null) {
                if ($user_refer_history->user_discount_type == 1) {
                    $refer_discount_price = round($total_price - $user_refer_history->user_discount, 2);
                    $get_refer_discount_price = $user_refer_history->user_discount;
                } else {
                    $refer_discount_price = round((($total_price * $user_refer_history->user_discount) / 100), 2);
                    $refer_discount_price = round($total_price - $refer_discount_price, 2);
                    $get_refer_discount_price = $refer_discount_price;
                }
                if ($refer_discount_price < 0) {
                    $refer_discount_price = 0;
                }
                $total_pay = $refer_discount_price;
                $refer_discount = round($get_refer_discount_price, 2);
            } else {
                $user_refer_history = UserReferHistory::query()->where('refer_id', $user_check->id)->where('refer_status', 0)->first();
                if ($user_refer_history != Null) {
                    if ($user_refer_history->refer_discount_type == 1) {
                        $refer_discount_price = round($total_price - $user_refer_history->refer_discount, 2);
                        $get_refer_discount_price = $user_refer_history->user_discount;
                    } else {
                        $refer_discount_price = round((($total_price * $user_refer_history->refer_discount) / 100), 2);
                        $refer_discount_price = round($total_price - $refer_discount_price, 2);
                        $get_refer_discount_price = $refer_discount_price;
                    }
                    if ($refer_discount_price < 0) {
                        $refer_discount_price = 0;
                    }
                    $total_pay = $refer_discount_price;
                    $refer_discount = round($get_refer_discount_price, 2);
                }
            }
        }

        return response()->json([
            "status" => 1,
//            "message" => "success!",
            "message" => __('user_messages.1'),
            "message_code" => 1,
            "service_type_id" => $get_vehicle_price->id,
            "cost_per_km" => round( $get_vehicle_price->cost_for_km * $currency, 2),
            "base_fare" => round($base_fare * $currency, 2),
            "cost_per_min" => round($get_vehicle_price->time_fare * $currency, 2),
            "time_fare_amount" => round($time_fare_amount * $currency, 2),
            "total_distance" => $total_distance,
            "estimated_time" => $request->get('estimated_time'),
//            "ride_fare" => round($step_1 * $currency, 2),
            "ride_fare" => round($total_distance_amount * $currency, 2),
            "refer_discount" => round($refer_discount * $currency, 2),
            "min_adjustment_amount" => round($adjustment_amount * $currency, 2),
            "promocode_id" => $get_promo_code_id,
            'promo_code_apply' => $promo_code_apply,
            'promo_code_status' => $promo_code_status,
            'promo_message_code' => $promo_message_code,
            'promo_code_message' => $promo_code_message,
            'min_order_amount' =>round($min_order_amount * $currency, 2),
            "promocode_discount" => round($promo_discount_cost * $currency, 2),
            "promocode_name" => $promocode_name,
            "tax" => round($tax * $currency, 2),
            "tip" => round($tip * $currency, 2),
            "subtotal" => round(($total_pay - $tax) * $currency, 2),
            "total_pay" => round($total_pay * $currency, 2),
        ]);


    }

    public function postTransportAddRideTip(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "user_id" => "required|numeric",
            "access_token" => "required",
            "ride_id" => "required|numeric",
            "tip" => "required|numeric"
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

        $ride_details = TransportRideBook::query()->where('id', $request->get('ride_id'))->first();
        if ($ride_details == Null) {
            return response()->json([
                "status" => 0,
//                "message" => "ride details not found",
                "message" => __('user_messages.26'),
                "message_code" => 26,
            ]);
        }
        $user_currency = WorldCurrency::query()->where('symbol', $user_check->currency)->first();
        if ($user_currency == Null) {
            $user_currency = WorldCurrency::query()->where('default_currency', 1)->first();
        }
        $currency = $user_currency->ratio;
        if ($ride_details->tip < 1) {
//            $val = round($request->get('tip') / $currency, 2);
////            $reminder = $val % 10;
//            $reminder = fmod($val * 100,10);
//            if ($reminder >= 5) {
//                $val = round($val + 0.01, 2);
//            }
            $tip = round($request->get('tip') / $currency, 2);
            $ride_details->tip = $tip;
            $ride_details->total_pay = round(($ride_details->total_pay + $ride_details->tip), 2);
            $ride_details->driver_amount = round(($ride_details->driver_amount + $ride_details->tip), 2);
            $ride_details->save();
        }
        return $this->userClassapi->findRideDetailsforTADorTCIorTRD($ride_details->id, false, false, true);
    }

    public function postTransportCancelRide(Request $request)
    {
        $this->notificationClass->ApiLogDetail($logger_type =0, $request->get('user_id'), "postTransportCancelRide", $request->all());

        $validator = Validator::make($request->all(), [
                "user_id" => "required|numeric",
                "access_token" => "required|numeric",
                "ride_id" => "required|numeric",
                "cancel_reason" => "nullable"
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

        $ride_cancel = $this->userClassapi->transportRideBookingCancel($request->get('ride_id'), "user", $request->get('cancel_reason'));
        return $ride_cancel;
    }

    public function postTransportAssignDriverDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
                "user_id" => "required|numeric",
                "access_token" => "required|numeric",
                "ride_id" => "required|numeric",
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
        $assign_driver_details = $this->userClassapi->findRideDetailsforTADorTCIorTRD($request->get('ride_id'), true, false, false);
        return $assign_driver_details;
    }

    public function postTransportRideHistory(Request $request)
    {
        $validator = Validator::make($request->all(), [
                "user_id" => "required|numeric",
                "access_token" => "required|numeric",
                "filter_type" => "nullable|in:0,1,2,3,4,5",
                "timezone" => "required"
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
        $currency = $user_currency->ratio;

        $lang_prefix = $this->adminClass->get_langugae_fields($user_check->language);

        date_default_timezone_set($request->get('timezone'));

        $filter_type = $request->get('filter_type');
        $date = date('Y-m-d');

        if ($filter_type == 1) {
            //today
            $start_date = $date . " 00:00:01";
            $end_date = $date . " 23:59:59";
        } elseif ($filter_type == 2) {
            //last 7 day
            $start_date = date('Y-m-d', strtotime('-7 days', strtotime($date)));
            // $start_date = date('Y-m-d', strtotime($date . ' - 7 days'));
            $end_date = $date;
            $start_date = $start_date . " 00:00:01";
            $end_date = $end_date . " 23:59:59";

        } elseif ($filter_type == 4) {
            //this year
            $start_date = date("Y-01-01", strtotime($date));
            $end_date = date("Y-m-d", strtotime($date));
            $start_date = $start_date . " 00:00:01";
            $end_date = $end_date . " 23:59:59";
        } else {
            //last 30 day
            $start_date = date('Y-m-d', strtotime('-30 days', strtotime($date)));
            $end_date = $date;
            $start_date = $start_date . " 00:00:01";
            $end_date = $end_date . " 23:59:59";
            //$current_month = date('m');
            //$current_year = date('Y');
            //$last_month = $current_month - 1;
            //$start_date = date("Y-m-d", strtotime($current_year . '-' . $last_month . '01'));
            //$end_date = date("Y-m-d", strtotime($start_date));
        }

        $per_page = 10;
        if ($request->get('per_page') != Null) {
            $per_page = $request->get('per_page');
        }
        $page = $request->get('page') != Null ? $request->get('page') : 1;
        $offset = ($page - 1) * $per_page;

        /*$start = new \DateTime($start_date, new \DateTimeZone(config('app.timezone')));
        $start->setTimezone(new \DateTimeZone($request->get('timezone')));

        $end = new \DateTime($end_date, new \DateTimeZone(config('app.timezone')));
        $end->setTimezone(new \DateTimeZone($request->get('timezone')));*/


        if ($filter_type == 1 || $filter_type == 2 || $filter_type == 3 || $filter_type == 4) {


            $rides_1 = TransportRideBook::query()->select('user_ride_booking.id',
                'user_ride_booking.ride_no',
                'user_ride_booking.pickup_datetime',
                'user_ride_booking.total_pay',
                'user_ride_booking.status',
                'user_ride_booking.pickup_address',
                'user_ride_booking.destination_address',
                //'service_category.' . $lang_prefix . 'name as category_name',
                DB::raw("( CASE WHEN service_category." . $lang_prefix . "name !='' THEN service_category." . $lang_prefix . "name ELSE service_category.name END) as category_name"),
                DB::raw("DATE_FORMAT(user_ride_booking.created_at, '%Y-%m-%d %H:%i:%s') as service_date_time"),
                DB::raw("DATE_FORMAT(user_ride_booking.pickup_datetime, '%Y-%m-%d %H:%i:%s') as schedule_order_date_time"),
                'service_category.id as category_id',
                'service_category.icon_name as category_icon')
                ->join('service_category', 'service_category.id', '=', 'user_ride_booking.service_cat_id')
                ->where('user_ride_booking.user_id', $request->get('user_id'))
                //->where('user_ride_booking.driver_id', '!=', Null)
//                ->whereNotNull('user_ride_booking.driver_id')
                ->where(function ($query) use ($request) {
                    $query->where('user_ride_booking.driver_id', '!=', Null);
                    $query->orWhere('user_ride_booking.service_cat_id', "=", $this->courierService);
                    $query->where('user_ride_booking.service_cat_id', "!=", NULL);
                })
                ->where('user_ride_booking.pickup_datetime', '>=', $start_date)
                ->where('user_ride_booking.pickup_datetime', '<=', $end_date)
                ->orderBy('user_ride_booking.pickup_datetime', 'desc');


            $rides_2 = TransportRentalRideBooking::query()->select('user_rental_ride_booking.id',
                'user_rental_ride_booking.ride_no',
                'user_rental_ride_booking.pickup_date_time as pickup_datetime',
                'user_rental_ride_booking.total_pay',
                'user_rental_ride_booking.status',
                'user_rental_ride_booking.pickup_address',
                'user_rental_ride_booking.drop_address as destination_address',
                //'service_category.' . $lang_prefix . 'name as category_name',
                DB::raw("( CASE WHEN service_category." . $lang_prefix . "name !='' THEN service_category." . $lang_prefix . "name ELSE service_category.name END) as category_name"),
                DB::raw("DATE_FORMAT(user_rental_ride_booking.created_at, '%Y-%m-%d %H:%i:%s') as service_date_time"),
                DB::raw("DATE_FORMAT(user_rental_ride_booking.pickup_date_time, '%Y-%m-%d %H:%i:%s') as schedule_order_date_time"),
                'service_category.id as category_id',
                'service_category.icon_name as category_icon')
                ->join('service_category', 'service_category.id', '=', 'user_rental_ride_booking.service_cat_id')
                ->where('user_rental_ride_booking.user_id', $request->get('user_id'))
//                ->whereNotNull('user_rental_ride_booking.driver_id')
                ->where(function ($query) use ($request) {
                    $query->where('user_rental_ride_booking.driver_id', '!=', Null);
                    $query->orWhere('user_rental_ride_booking.service_cat_id', "=", $this->courierService);
                    $query->where('user_rental_ride_booking.service_cat_id', "!=", NULL);
                })
                ->where('user_rental_ride_booking.pickup_date_time', '>=', $start_date)
                ->where('user_rental_ride_booking.pickup_date_time', '<=', $end_date)
                ->orderBy('user_rental_ride_booking.pickup_date_time', 'desc');
        } else {
            $rides_1 = TransportRideBook::query()->select('user_ride_booking.id',
                'user_ride_booking.ride_no',
                'user_ride_booking.pickup_datetime',
                'user_ride_booking.total_pay',
                'user_ride_booking.status',
                'user_ride_booking.pickup_address',
                'user_ride_booking.destination_address',
                //'service_category.' . $lang_prefix . 'name as category_name',
                DB::raw("( CASE WHEN service_category." . $lang_prefix . "name != '' THEN service_category." . $lang_prefix . "name ELSE service_category.name END) as category_name"),
                DB::raw("DATE_FORMAT(user_ride_booking.created_at, '%Y-%m-%d %H:%i:%s') as service_date_time"),
                DB::raw("DATE_FORMAT(user_ride_booking.pickup_datetime, '%Y-%m-%d %H:%i:%s') as schedule_order_date_time"),
                'service_category.id as category_id',
                'service_category.icon_name as category_icon')
                ->join('service_category', 'service_category.id', '=', 'user_ride_booking.service_cat_id')
                ->where('user_ride_booking.user_id', $request->get('user_id'))
                //->where('user_ride_booking.driver_id', '!=', Null)
//                ->whereNotNull('user_ride_booking.driver_id')
                ->where(function ($query) use ($request) {
                    $query->where('user_ride_booking.driver_id', '!=', Null);
                    $query->orWhere('user_ride_booking.service_cat_id', "=", $this->courierService);
                    $query->where('user_ride_booking.service_cat_id', "!=", NULL);
                })
                ->orderBy('user_ride_booking.pickup_datetime', 'desc');
            $rides_2 = TransportRentalRideBooking::query()->select('user_rental_ride_booking.id',
                'user_rental_ride_booking.ride_no',
                'user_rental_ride_booking.pickup_date_time as pickup_datetime',
                'user_rental_ride_booking.total_pay',
                'user_rental_ride_booking.status',
                'user_rental_ride_booking.pickup_address',
                'user_rental_ride_booking.drop_address as destination_address',
                //'service_category.' . $lang_prefix . 'name as category_name',
                DB::raw("( CASE WHEN service_category." . $lang_prefix . "name != '' THEN service_category." . $lang_prefix . "name ELSE service_category.name END) as category_name"),
                DB::raw("DATE_FORMAT(user_rental_ride_booking.created_at, '%Y-%m-%d %H:%i:%s') as service_date_time"),
                DB::raw("DATE_FORMAT(user_rental_ride_booking.pickup_date_time, '%Y-%m-%d %H:%i:%s') as schedule_order_date_time"),
                'service_category.id as category_id',
                'service_category.icon_name as category_icon')
                ->join('service_category', 'service_category.id', '=', 'user_rental_ride_booking.service_cat_id')
                ->where('user_rental_ride_booking.user_id', $request->get('user_id'))
//                ->whereNotNull('user_rental_ride_booking.driver_id')
                ->where(function ($query) use ($request) {
                    $query->where('user_rental_ride_booking.driver_id', '!=', Null);
                    $query->orWhere('user_rental_ride_booking.service_cat_id', "=", $this->courierService);
                    $query->where('user_rental_ride_booking.service_cat_id', "!=", NULL);
                })
                ->orderBy('user_rental_ride_booking.pickup_date_time', 'desc');
        }

        if($filter_type == 5){
            $rides_1->where('user_ride_booking.pickup_datetime', '>', date('Y-m-d H:i:s'));
            $rides_2->where('user_rental_ride_booking.pickup_date_time', '>', date('Y-m-d H:i:s'));
        }

        $total_count = $rides_1->count() + $rides_2->count();
        $rides_1 = $rides_1->get()->toArray();
        $rides_2 = $rides_2->get()->toArray();

        $ride_history = [];
        $rides = array_merge($rides_1, $rides_2);
        $rides = array_slice($rides,$offset,$per_page);
        usort($rides, function ($a, $b) {
            if ($a['pickup_datetime'] == $b['pickup_datetime']) return 0;
            return $a['pickup_datetime'] < $b['pickup_datetime'] ? 1 : -1;
        });
        foreach ($rides as $key => $ride) {
            $ride_history[] = [
                "ride_id" => $ride['id'],
                "booking_no" => $ride['ride_no'],
                'category_icon' => $ride['category_icon'] != Null ? url('/assets/images/service-category/' . $ride['category_icon']) : '',
                "category_name" => ucwords(strtolower($ride['category_name'])),
                "service_category_id" => $ride['category_id'],
                "ride_status" => $ride['status'],
                "total_pay" => round($ride['total_pay'] * $currency, 2),
                "pickup_date" => $this->notificationClass->dateLangConvert(Date('d M, Y', strtotime($ride['pickup_datetime'])),$user_check->language),
                "pickup_time" => Date('h:i A', strtotime($ride['pickup_datetime'])),
                'service_date_time' => $ride['service_date_time'],
                'schedule_order_date_time' => $ride['schedule_order_date_time'],
                "pickup_address" => $ride['pickup_address'],
                "destination_address" => $ride['destination_address']
            ];
        }

        $current_page = $page;
        $divider = round($total_count / $per_page);
        if($divider > ($total_count / $per_page)){
            $last_page = $divider;
        } else{
            $last_page = $divider + 1;
        }

        return response()->json([
            "status" => 1,
//            "message" => "Success",
            "message" => __('user_messages.1'),
            "message_code" => 1,
            'current_page' => $current_page - 0,
            'last_page' => $last_page - 0,
            'total' => $total_count - 0,
            "rides" => $ride_history,
        ]);

    }

    public function postTransportRideCompletedInvoiceDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
                "user_id" => "required|numeric",
                "access_token" => "required|numeric",
                "ride_id" => "required|numeric"
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
        $ride_invoice_details = $this->userClassapi->findRideDetailsforTADorTCIorTRD($request->get('ride_id'), false, true, false);
        return $ride_invoice_details;
    }

    public function postTransportRideReceiptDetails(Request $request)
    {
        $this->notificationClass->ApiLogDetail($logger_type =0, $request->get('user_id'), "postTransportRideReceiptDetails", $request->all());
        $validator = Validator::make($request->all(), [
            "user_id" => "required|numeric",
            "access_token" => "required|numeric",
            "ride_id" => "required|numeric"
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
        $ride_invoice_details = $this->userClassapi->findRideDetailsforTADorTCIorTRD($request->get('ride_id'), false, false, true);
        return $ride_invoice_details;
    }

    public function postTransportRideUserRating(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "user_id" => "required|numeric",
            "access_token" => "required|numeric",
            "ride_id" => "required|numeric",
            "driver_id" => "required",
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
        $user_check = $this->userClassapi->checkUserAllow($request->get('user_id'), $request->get('access_token'));
        if ($decoded['status'] = json_decode($user_check) == false) {
            return $user_check;
        }
        $driver_rating = TransportRatings::query()->where('ride_id', $request->get('ride_id'))->where('user_id', $request->get('user_id'))->first();
        if ($driver_rating == Null) {
            $check_ride = TransportRideBook::query()->where('id', $request->get('ride_id'))->first();
            if ($check_ride == Null) {
                return response()->json([
                    "status" => 0,
//                    "message" => "ride details not found",
                    "message" => __('user_messages.26'),
                    "message_code" => 26,
                ]);
            }
            if ($check_ride->payment_status != 1) {
                return response()->json([
                    "status" => 0,
//                    "message" => "your ride payment is pending!",
                    "message" => __('user_messages.83'),
                    "message_code" => 83,
                ]);
            }
            //if ($check_ride->status != 9) {
            //    return response()->json([
            //        "status" => 0,
            //        "message" => "driver not completed your ride",
            //        "message_code" => 92,
            //    ]);
            //}

            $driver_details_id = TransportDriverDetails::query()->select('provider_services.id as provider_services_id')
                ->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
                ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
                ->where('providers.id', $request->get('driver_id'))
                ->first();
            if ($driver_details_id == Null) {
                return response()->json([
                    "status" => 0,
//                    "message" => "driver details not found!",
                    "message" => __('user_messages.15'),
                    "message_code" => 15,
                ]);
            }
            $check_driver = TransportDriverDetails::query()->where('provider_service_id', $driver_details_id->provider_services_id)->first();
            if ($check_driver != Null) {
                $check_ride->user_rating_status = 1;
                $check_ride->save();

                $driver_rating = TransportRatings::query()->where('driver_id', $check_driver->id)->where('ride_id', $request->get('ride_id'))->where('user_id', $request->get('user_id'))->first();
                if ($driver_rating == Null) {
                    $driver_rating = new TransportRatings();
                }
                $driver_rating->driver_id = $check_driver->id;
                $driver_rating->user_id = $request->get('user_id');
                $driver_rating->ride_id = $request->get('ride_id');
                $driver_rating->rating = $request->get('rating');
                if ($request->get('comment') != Null) {
                    $driver_rating->comment = $request->get('comment');
                }
                $driver_rating->save();

                $ratings = TransportRatings::query()->select(DB::raw('avg(rating) as ratings'))->groupBy('driver_id')->where('driver_id', $check_driver->id)->first();
                $check_driver->rating = $ratings->ratings;
                $check_driver->save();
                UserRunningRide::query()->where('user_id', $check_ride->user_id)->where('service_cat_id', $check_ride->service_cat_id)->where('booking_id', $check_ride->id)->delete();
            } else {
                return response()->json([
                    "status" => 0,
//                    "message" => "driver details not found!",
                    "message" => __('user_messages.15'),
                    "message_code" => 15,
                ]);
            }
        }
        return response()->json([
            "status" => 1,
//            "message" => "success!",
            "message" => __('user_messages.1'),
            "message_code" => 1,
        ]);
    }

    public function postGetNearestDriverPosition(Request $request)
    {
        $validator = Validator::make($request->all(), [
                "user_id" => "required|numeric",
                "access_token" => "required|numeric",
                "service_category_id" => "required|numeric|in:1,2,4",
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
        $service_setting = ServiceSettings::where('service_cat_id', $service_cat_id)->first();
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

        if ($service_cat_id == $this->courierService) {
            $provider_lat_long = Provider::query()->select(DB::raw("CONCAT(COALESCE(providers.first_name,''),' ',COALESCE(providers.last_name,'')) as name"), 'transport_driver_details.current_lat', 'transport_driver_details.current_long', 'service_category.id as service_id')
                ->whereRaw(DB::raw("(6371 * acos( cos( radians(" . $lat . ") ) * cos( radians( current_lat ) )  * cos( radians( current_long ) - radians(" . $long . ") ) + sin( radians(" . $lat . ") ) * sin(radians( current_lat ) ) ) ) < " . $radius))
                ->join('provider_services', 'provider_services.provider_id', '=', 'providers.id')
                ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
                ->join('transport_driver_details', 'transport_driver_details.provider_service_id', '=', 'provider_services.id')
//                ->where('service_category.id', $service_cat_id)
                ->whereIn('service_category.id', [$service_cat_id, $this->bikeService])
                ->where('providers.status', 1)
                ->where('provider_services.status', 1)
                ->where('provider_services.current_status', 1)
                ->where('transport_driver_details.courier_boy', 1)
                ->where('transport_driver_details.last_online_date_time', '>=', $expire_date_time)
                ->whereNotNull('transport_driver_details.current_lat')
                ->whereNotNull('transport_driver_details.current_long')
                ->whereNotNull('providers.access_token')
                ->whereNotNull('providers.device_token');
        }
        else {
            $provider_lat_long = Provider::query()->select(DB::raw("CONCAT(COALESCE(providers.first_name,''),' ',COALESCE(providers.last_name,'')) as name"), 'transport_driver_details.current_lat', 'transport_driver_details.current_long', 'service_category.id as service_id')
                ->whereRaw(DB::raw("(6371 * acos( cos( radians(" . $lat . ") ) * cos( radians( current_lat ) )  * cos( radians( current_long ) - radians(" . $long . ") ) + sin( radians(" . $lat . ") ) * sin(radians( current_lat ) ) ) ) < " . $radius))
                ->join('provider_services', 'provider_services.provider_id', '=', 'providers.id')
                ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
                ->join('transport_driver_details', 'transport_driver_details.provider_service_id', '=', 'provider_services.id')
                ->where('service_category.id', $service_cat_id)
                ->where('providers.status', 1)
                ->where('provider_services.status', 1)
                ->where('provider_services.current_status', 1)
                ->where('transport_driver_details.driver_service', 1)
                ->where('transport_driver_details.last_online_date_time', '>=', $expire_date_time)
                ->whereNotNull('transport_driver_details.current_lat')
                ->whereNotNull('transport_driver_details.current_long')
                ->whereNotNull('providers.access_token')
                ->whereNotNull('providers.device_token');
        }

        if ($not_multi_delivery_provider_id != Null) {
            $provider_lat_long = $provider_lat_long->whereNotIn('providers.id', $not_multi_delivery_provider_id);
        }

        $provider_lat_long = $provider_lat_long->get();

        return response()->json([
            "status" => 1,
//            "message" => "success",
            'message' => __('user_messages.1'),
            "message_code" => 1,
            "active_driver_lat_long" => $provider_lat_long,
        ]);
    }

    public function postTransportOrderPayment(Request $request) {
        //payment_method 1 = omise, 0= paypal
        $validator = Validator::make($request->all(), [
            "user_id" => "required|numeric",
            "access_token" => "required|numeric",
            "ride_id" => "required|numeric",
            "payment_type" => "required|numeric|in:1,2,3",
            //"card_id" => "required",
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }

        $user_details = $this->userClassapi->checkUserAllow($request->get('user_id'), $request->get('access_token'));
        if ($decoded['status'] = json_decode($user_details) == false) {
            return $user_details;
        }

        $ride_details = TransportRideBook::query()->where('id', $request->get('ride_id'))->first();
        if ($ride_details == Null) {
            return response()->json([
                "status" => 0,
//                "message" => "ride details not found",
                "message" => __('user_messages.26'),
                "message_code" => 26,
            ]);
        }

        $redirect_url = "";
        $success_url = "";
        $failed_url = "";
        if ($request->get('payment_type') == 1) {
            $ride_details->payment_type = $request->get('payment_type');
            $ride_details->payment_status = 1;
            $ride_details->save();
            UserRunningRide::query()->where('user_id', $ride_details->user_id)->where('service_cat_id', $ride_details->service_cat_id)->where('booking_id', $ride_details->id)->delete();
        }
        elseif ($request->get('payment_type') == 2) {
            if ($ride_details->payment_status == 0) {
                $validator = Validator::make($request->all(), [
                    "card_id" => "required|numeric",
                ]);
                if ($validator->fails()) {
                    return response()->json([
                        "status" => 0,
                        "message" => $validator->errors()->first(),
                        "message_code" => 9,
                    ]);
                }
                $card_details = UserCardDetails::query()->where('id', $request->get('card_id'))->first();
                if ($card_details == Null) {
                    return response()->json([
                        "status" => 0,
//                        "message" => "card details not found",
                        "message" => __('user_messages.120'),
                        "message_code" => 120,
                    ]);
                }
                $ride_details->payment_type = $request->get('payment_type');
                $ride_details->payment_status = 1;
                $ride_details->save();
               //$validator = Validator::make($request->all(), [
               //    "payment_method_type" => "nullable|in:1,0",
               //]);
               //if ($validator->fails()) {
               //    return response()->json([
               //        "status" => 0,
               //        "message" => $validator->errors()->first(),
               //        "message_code" => 9,
               //    ]);
               //}
               //if ($request->get('payment_method_type') == 1) {
               //    $amount_to_php = round($ride_details->total_pay, 2);
               //    if ($amount_to_php < 100 || $amount_to_php > 99999999) {
               //        return response()->json([
               //            "status" => 0,
               //            "message" => "With paymongo min amount must be ₱100 and max amount must be ₱99999999",
               //            "message_code" => 158,
               //        ]);
               //    }
               //    $success_url = \URL::route('paypmongo.success');
               //    $failed_url = \URL::route('paypmongo.failed');
               //    $error_url = \URL::route('paypmongo.failed');
               //    //$error_url = \URL::route('mongo_pay.error');
               //    try {
               //        $user_id = $request->get('user_id');
               //            $service_category_id = $ride_details->service_cat_id;
               //            $order_id = $ride_details->id;
               //            $mongo_success_url = \URL::route('mongo_pay.success', ["id" => $user_id, "order_id" => $order_id]);
               //            $mongo_failed_url = \URL::route('mongo_pay.failed', ["id" => $user_id, "order_id" => $order_id]);
               //            $gcashSource = Paymongo::source()->create([
               //                'type' => 'gcash',
               //                'amount' => $amount_to_php,
               //                'currency' => 'PHP',
               //                'user_id' => $user_id,
               //                'redirect' => [
               //                    'success' => $mongo_success_url,
               //                    'failed' => $mongo_failed_url
               //                ]
               //            ]);
               //            $get_gcashSource_data = $gcashSource->getData();
               //            $mongo_success_url = $get_gcashSource_data['redirect']['checkout_url'];
               //            $gcashSource_status = $get_gcashSource_data['status'];
               //            $source_type = $get_gcashSource_data['source_type'];
               //            $gcashSource_id = $get_gcashSource_data['id'];
               //            if ($mongo_success_url != "" && $gcashSource_status == "pending" && $source_type == "gcash") {
               //                $ride_details->payment_type = $request->get('payment_type');
               //                $ride_details->save();
               //                $tempUserBooking = New TempUserBooking();
               //                $tempUserBooking->user_id = $user_id;
               //                $tempUserBooking->service_cat_id = $service_category_id;
               //              $tempUserBooking->booking_id = $order_id;
               //              $tempUserBooking->transaction_id = $gcashSource_id;
               //              $tempUserBooking->payment_method_type = 1;
               //              $tempUserBooking->coupon_ids = 0;
               //              $tempUserBooking->amount = round($amount_to_php, 2);
               //              $tempUserBooking->payment_status = 0;
               //              $tempUserBooking->save();
               //              $redirect_url = $mongo_success_url;
               //          } else {
               //              return response()->json([
               //                  "status" => 0,
               //                  "message" => "Payment transaction failed",
               //                  "message_code" => 108,
               //              ]);
               //          }
               //      } catch (\Exception $e) {
               //          return response()->json([
               //              "status" => 0,
               //              "message" => $e->getMessage(),
               //              "message_code" => 108,
               //          ]);
               //      }
               //  }
               //  elseif ($request->get('payment_method_type') == 0) {
               //      $convert_to_currency = WorldCurrency::query()->where('symbol', "$")->first();
               //      if ($convert_to_currency == Null) {
               //          return response()->json([
               //              'status' => 0,
               //              'message' => "Something went to wrong!",
               //              "message_code" => 9,
               //          ]);
               //      }
               //      $success_url = \URL::route('paypal.success');
               //      $failed_url = \URL::route('paypal.failed');
               //      $settings = GeneralSettings::query()->first();
               //      if ($settings == Null && $settings->paypal_sandbox == Null && $settings->paypal_client_id == Null && $settings->paypal_client_secret_key == Null) {
               //          return response()->json([
               //              'status' => 0,
               //              'message' => "Something went to wrong!",
               //              "message_code" => 9,
               //          ]);
               //      }
               //     $amount_to_usd = round($ride_details->total_pay * $convert_to_currency->ratio, 2);
               //     try {
               //         $user_id = $request->get('user_id');
               //         $service_category_id = $ride_details->service_cat_id;
               //         $order_id = $ride_details->id;
               //         $paypal_sandbox = $settings->paypal_sandbox . "";
               //         $paypal_client_id = $settings->paypal_client_id . "";
               //         $paypal_client_secret_key = $settings->paypal_client_secret_key . "";
               //         $api_context = new ApiContext(new OAuthTokenCredential($paypal_client_id, $paypal_client_secret_key));
               //         $settings = array(
               //             'mode' => $paypal_sandbox,
               //             'http.ConnectionTimeOut' => 30,
               //             'log.LogEnabled' => true,
               //             'log.FileName' => storage_path() . '/logs/paypal.log',
               //             'log.LogLevel' => 'ERROR'
               //         );
               //         $api_context->setConfig($settings);
               //         $payer = new Payer();
               //         $payer->setPaymentMethod('paypal');
               //         $item_1 = new Item();
               //         $item_1->setName('Item 1')
               //             ->setCurrency('USD')
               //             ->setQuantity(1)
               //             ->setPrice($amount_to_usd);
               //         $item_list = new ItemList();
               //         $item_list->setItems(array($item_1));
               //         $amount = new Amount();
               //         $amount->setCurrency('USD')
               //             ->setTotal($amount_to_usd);
               //         $transaction = new Transaction();
               //         $transaction->setAmount($amount)
               //             ->setItemList($item_list)
               //             ->setCustom($user_id . ',' . $service_category_id . ',' . $order_id)
               //             ->setDescription('Your transaction description');
               //         $redirect_urls = new RedirectUrls();
               //         $redirect_urls->setReturnUrl(\URL::route('payment.status'))/** Specify return URL **/
               //         ->setCancelUrl(\URL::route('payment.status'));
               //         $payment = new Payment();
               //         $payment->setIntent('Sale')
               //             ->setPayer($payer)
               //             ->setRedirectUrls($redirect_urls)
               //             ->setTransactions(array($transaction));
               //         try {
               //             $payment->create($api_context);
               //         } catch (\PayPal\Exception\PPConnectionException $ex) {
               //             if (\Config::get('app.debug')) {
               //                 return response()->json([
               //                     "status" => 0,
               //                     "message" => "Connection timeout",
               //                     "message_code" => 9,
               //                 ]);
               //             } else {
               //                 return response()->json([
               //                     "status" => 0,
               //                     "message" => "your payment transaction failed",
               //                     "message_code" => 9,
               //                 ]);
               //             }
               //         }
               //         foreach ($payment->getLinks() as $link) {
               //             if ($link->getRel() == 'approval_url') {
               //                 $redirect_url = $link->getHref();
               //                 break;
               //             }
               //         }
               //         \Session::put('paypal_payment_id', $payment->getId());
               //         if (isset($redirect_url)) {
               //             $ride_details->payment_type = $request->get('payment_type');
               //             //$ride_details->payment_status = 1;
               //             $ride_details->transaction_id = $payment->getId();
               //             $ride_details->save();
               //        } else {
               //            return response()->json([
               //                "status" => 0,
               //                "message" => "your payment transaction failed",
               //                "message_code" => 108,
               //            ]);
               //        }
               //    } catch (\Exception $e) {
               //        return response()->json([
               //            "status" => 0,
               //            "message" => $e->getMessage(),
               //            "message_code" => 108,
               //        ]);
               //    }
               //} else {
               //    return response()->json([
               //        "status" => 0,
               //        "message" => "something went to wrong",
               //        "message_code" => 9,
               //    ]);
               //}
            }
            UserRunningRide::query()->where('user_id', $ride_details->user_id)->where('service_cat_id', $ride_details->service_cat_id)->where('booking_id', $ride_details->id)->delete();
        }
        elseif ($request->get('payment_type') == 3) {
            if ($ride_details->payment_status == 0) {
                $service_category = ServiceCategory::query()->where('id', $ride_details->service_cat_id)->first();
                if ($service_category == Null) {
                    return response()->json([
                        "status" => 0,
//                        "message" => "something want to wrong!",
                        "message" => __('user_messages.9'),
                        "message_code" => 9,
                    ]);
                }
                $wallet_balance = UserWalletTransaction::query()->where('user_id', $request->get('user_id'))->orderBy('id', 'desc')->first();
                if ($wallet_balance != Null) {
                    $balance = $wallet_balance->remaining_balance;
                } else {
                    $balance = 0;
                }
                if ($ride_details->total_pay > $balance) {
                    return response()->json([
                        "status" => 0,
//                        "message" => "You can't pay ride amount through Wallet because your wallet balance is insufficient.",
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
                $ride_details->payment_type = $request->get('payment_type');
                $ride_details->payment_status = 1;
                $ride_details->save();
            }
            UserRunningRide::query()->where('user_id', $ride_details->user_id)->where('service_cat_id', $ride_details->service_cat_id)->where('booking_id', $ride_details->id)->delete();
        } else {
            return response()->json([
                "status" => 0,
//                "message" => "something went to wrong",
                "message" => __('user_messages.9'),
                "message_code" => 9,
            ]);
        }
        if ($request->get('payment_type') != 2) {
            if ($ride_details->completed_by == 1) {
                if ($ride_details->driver_id != Null) {
                    $driver_details = TransportDriverDetails::query()->where('id', $ride_details->driver_id)->first();
                    if ($driver_details != Null) {
                        $update_driver_status = ProviderServices::query()->where('id', $driver_details->provider_service_id)->first();
                        if ($update_driver_status != Null) {
                            //$update_driver_status->current_status = 1;
                            //$update_driver_status->save();
                            ProviderUserRunningService::query()->where('service_cat_id', $ride_details->service_cat_id)->where('user_id', $ride_details->user_id)->where('booking_id', $ride_details->id)->delete();
                        }
                    }
                }
                $ride_details->status = 9;
                $ride_details->save();
            }
        }

        return response()->json([
            "status" => 1,
//            "message" => "success!",
            "message" => __('user_messages.1'),
            "message_code" => 1,
            "ride_id" => $ride_details->id,
            "ride_no" => $ride_details->ride_no,
            "redirect_url" => $redirect_url,
            "success_url" => $success_url,
            "failed_url" => $failed_url
        ]);
    }

    public function postTransportServiceHome(Request $request)
    {
        $this->notificationClass->ApiLogDetail($logger_type =0, $request->get('user_id'), "postTransportServiceHome", $request->all());
        $validator = Validator::make($request->all(), [
                "user_id" => "required|numeric",
                "access_token" => "required|numeric",
                "service_category_id" => "required|numeric|in:4",
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

        $language = $user_check->language;
        if($language !="en" && $language != "" && $language != "Null" ){
            $lang_prefix = $language."_";
        }else{
            $lang_prefix ="";
        }

        $service_cat_id = $request->get('service_category_id');

        $homepage_slider_url = url('/assets/images/service-slider-banner/');

        $transport_home_page = TransportHomePage::query()->select(
            DB::raw("(CASE WHEN transport_home_page.banner_image != '' THEN (concat('$homepage_slider_url','/',transport_home_page.banner_image,'?v=0.3')) ELSE '' END) as banner_image"),
            'transport_home_page.'.$lang_prefix . 'description as description',
            'transport_home_page.'.$lang_prefix . 'instruction as instruction'
        )
            ->where('service_cat_id',$service_cat_id)
            ->first();

        $transport_weight_list = TransportWeightList::query()->select('transport_weight_list.id', 'transport_weight_list.start_limit', 'transport_weight_list.close_limit')
            ->join('transport_vehicle_type','transport_vehicle_type.weight_limit','=','transport_weight_list.id')
            ->where('transport_weight_list.service_cat_id',$service_cat_id)
            ->where('transport_weight_list.status',1)
            ->orderBy('transport_weight_list.id','asc')
            ->get();

        $transport_category_list = TransportCategoryList::query()->select('id', 'category_name')
            ->where('service_cat_id',$service_cat_id)
            ->where('status',1)
            ->get();

        return response()->json([
            "status" => 1,
//            "message" => "success",
            "message" => __('user_messages.1'),
            "message_code" => 1,
            "banner_image" => isset($transport_home_page) ? $transport_home_page->banner_image : '',
            "description" => isset($transport_home_page) ? $transport_home_page->description : '',
            "instruction" => isset($transport_home_page) ? $transport_home_page->instruction : '',
            "transport_weight_list" => isset($transport_weight_list) ? $transport_weight_list : [],
            "transport_category_list" => isset($transport_category_list) ? $transport_category_list : [],
        ]);
    }

    public function postTransportSurchargeCharge(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "user_id" => "required",
            "access_token" => "required",
            "service_type_id" => "required",
            "pickup_date_time" => "nullable",
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

        if ($request->get('service_type_id') == Null) {
            return response()->json([
                "status" => 0,
//                "message" => "something went to wrong!",
                "message" => __('user_messages.9'),
                "message_code" => 9,
            ]);
        }else {
            $vehicle_type = TransportVehicleType::query()->where('id', $request->get('service_type_id'))->first();
            if ($vehicle_type == Null) {
                return response()->json([
                    "status" => 0,
//                    "message" => "something went to wrong!",
                    "message" => __('user_messages.9'),
                    "message_code" => 9,
                ]);
            }else{
                if ($vehicle_type->surcharge_timings_status != null && $vehicle_type->surcharge_timings_status == 1){

                    if ($request->get('pickup_date_time') != null){
                        $day = date('D', strtotime(date($request->get('pickup_date_time'))));
                    }else{
                        $day = date('D');
                    }
                    $surcharge_timings = SurchargeTimings::query()->where('transport_vehicle_type_id', $vehicle_type->id)
                        ->where('day', $day)
                        ->first();
                    if ($surcharge_timings == null){
                        return response()->json([
                            "status" => 1,
//                            "message" => "success",
                            "message" => __('user_messages.1'),
                            "surcharge_charge_status" => 0,
                            "message_code" => 1,
                        ]);
                    }else{
                        if ($request->get('pickup_date_time') != null) {
                            $time =  date('H:i:s', strtotime(date($request->get('pickup_date_time'))));
                        }else{
                            $time = date('H:i:s');
                        }
                        if ( $time >= $surcharge_timings->start_time && $time <= $surcharge_timings->end_time){
                            return response()->json([
                                "status" => 1,
//                                "message" => "success",
                                "message" => __('user_messages.1'),
                                "surcharge_charge_status" => 1,
                                "surcharge_cost" => $surcharge_timings->price,
                                "message_code" => 1,
                            ]);
                        }else{
                            return response()->json([
                                "status" => 1,
//                                "message" => "success",
                                "message" => __('user_messages.1'),
                                "surcharge_charge_status" => 0,
                                "message_code" => 1,
                            ]);
                        }
                    }
                }else{
                    return response()->json([
                        "status" => 1,
//                        "message" => "success",
                        "message" => __('user_messages.1'),
                        "surcharge_charge_status" => 0,
                        "message_code" => 1,
                    ]);
                }
            }
        }
    }
}
