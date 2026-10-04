<?php

namespace App\Http\Controllers\Api\Transport\Rental;

use App\Classes\DriverClassApi;
use App\Classes\NotificationClass;
use App\model\PromocodeDetails;
use App\model\ProviderServices;
use App\model\ProviderUserRunningService;
use App\model\ServiceSettings;
use App\model\TransportDriverDetails;
use App\model\TransportRentalRideBooking;
use App\model\TransportVehicleType;
use App\model\UsedPromocodeDetails;
use App\model\User;
use App\model\UserRatings;
use App\model\WorldCurrency;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DriverController extends Controller
{
    private $driverClassApi;
    private $notificationClass;
    private $bikeRental;
    private $bikeService;
    private $taxiService;
    private $taxiRental;

    public function __construct(DriverClassApi $driverClassApi, NotificationClass $notificationClass)
    {
        $this->driverClassApi = $driverClassApi;
        $this->notificationClass = $notificationClass;
        $this->bikeRental = 31;
        $this->taxiRental = 32;
        $this->bikeService = 1;
        $this->taxiService = 2;
    }

    public function postTransportRentalRideDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
                "driver_id" => "required|integer",
                "access_token" => "required|numeric",
                "driver_service_id" => "required|numeric",
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

        $driver_id = $request->get('driver_id');
        $driver_check = $this->driverClassApi->checkDriverRegisterAllow($driver_id, $request->get('access_token'), $request->get('driver_service_id'));
        if ($decoded['status'] = json_decode($driver_check) == false) {
            return $driver_check;
        }

        $user_currency = WorldCurrency::query()->where('symbol', $driver_check->currency)->first();
        if ($user_currency == Null) {
            $user_currency = WorldCurrency::query()->where('default_currency', 1)->first();
        }
        $currency = $user_currency->ratio;

        $ride_id = $request->get('ride_id');
        $ride_details = TransportRentalRideBooking::query()->where('id', $ride_id)->first();
        if ($ride_details != Null) {
            $user_profile_url = url('/assets/images/profile-images/customer');
            $vehicle_icon_url = url('/assets/images/service-category/transport-service-type');
            $user_details = User::query()->select('id', 'rating',
                DB::raw("(CASE WHEN avatar != '' THEN concat('$user_profile_url','/',avatar) ELSE '' END) as user_profile_url"))
                ->where('id', $ride_details->user_id)->first();
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
            $promocode_details = UsedPromocodeDetails::query()->where('id', $ride_details->promo_code)->first();

            return response()->json([
                "status" => 1,
//                "message" => "success",
                "message" => __('driver_messages.1'),
                "message_code" => 1,
                "ride_id" => $ride_details->id,
                "ride_no" => $ride_details->ride_no,
                "user_name" => $ride_details->user_name,
                "user_profile_image" => $user_details != Null ? $user_details->user_profile_url != Null ? $user_details->user_profile_url : "" : "",
                "user_rating" => $user_details->rating,
                "vehicle_type_name" => $vehicle_details != Null ? $vehicle_details->name : "",
                "vehicle_type_icon" => $vehicle_details != Null ? $vehicle_details->icon_url != Null ? $vehicle_details->icon_url : "" : "",
                "pickup_location" => $ride_details->pickup_address,
                "pickup_lat_long" => $ride_details->pickup_lat_long,
                "pickup_date_time" => $ride_details->pickup_date_time,
                "drop_location" => $ride_details->drop_address,
                "drop_lat_long" => $ride_details->drop_lat_long,
                "drop_date_time" => $ride_details->drop_date_time,
                "free_travel_km" => $count_km_limit,
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
                'service_date_time' => date('Y-m-d H:i:s',strtotime($ride_details->created_at)),
                'schedule_order_date_time' => $ride_details->pickup_date_time,
                "driver_pay_settle_status"=>$ride_details->driver_pay_settle_status,
                "promocode_discount" => $promocode_details != Null ? round($promocode_details->discount_amount * $currency, 2) : 0,
                "promocode_name" => $promocode_details != Null ? $promocode_details->promocode_name : '',
                "start_odometer_value" => $ride_details->start_km,
                "otp" => $ride_details->otp,
            ]);

        } else {
            return response()->json([
                "status" => 0,
//                "message" => "something went to wrong",
                "message" => __('driver_messages.9'),
                "message_code" => 9,
            ]);
        }
    }

    public function postTransportRentalRideUpdateStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "driver_id" => "required|integer",
            "access_token" => "required|numeric",
            "driver_service_id" => "required|numeric",
            "service_category_id" => "required|integer|in:31,32",
            "ride_id" => "required|integer",
            "ride_status" => "required|in:1,2,3,6,7,8,9",
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }

        $driver_id = $request->get('driver_id');
        $driver_check = $this->driverClassApi->checkDriverRegisterAllow($driver_id, $request->get('access_token'), $request->get('driver_service_id'));
        if ($decoded['status'] = json_decode($driver_check) == false) {
            return $driver_check;
        }

        $driver_details_id = TransportDriverDetails::query()->select('provider_services.id as provider_services_id', 'transport_driver_details.id as driver_id', DB::raw("CONCAT(COALESCE(providers.first_name,''),' ') as driver_name"),'provider_services.current_status')
            ->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
            ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
            ->where('providers.id', $driver_id)
            ->first();
        if ($driver_details_id == Null) {
            return response()->json([
                "status" => 0,
//                "message" => "something went to wrong!",
                "message" => __('driver_messages.9'),
                "message_code" => 9,
            ]);
        }

        $ride_id = $request->get('ride_id');
        $ride_details = TransportRentalRideBooking::query()->where('id', $ride_id)->first();

        if ($ride_details != Null) {
            if ($ride_details->driver_id != Null) {
                if ($ride_details->driver_id != $driver_details_id->driver_id) {
                    return response()->json([
                        "status" => 0,
//                        "message" => "Sorry, You are too late to the accept ride",
                        "message" => __('driver_messages.23'),
                        "message_code" => 23,
                    ]);
                }
            }
            if ($ride_details->status == 2) {
                return response()->json([
                    "status" => 0,
//                    "message" => "Sorry, Your ride is cancelled",
                    "message" => __('driver_messages.65'),
                    "message_code" => 65,
                ]);
            }
            if ($ride_details->status == 9) {
                return response()->json([
                    "status" => 0,
//                    "message" => "Your ride is completed",
                    "message" => __('driver_messages.21'),
                    "message_code" => 21,
                ]);
            }
            if ($ride_details->status == 10) {
                return response()->json([
                    "status" => 0,
//                    "message" => "Sorry, Your ride is failed!",
                    "message" => __('driver_messages.65'),
                    "message_code" => 65,
                ]);
            }

            $update_status = $request->get('ride_status');
            switch ($update_status) {
                case $update_status == 1:
                    if ($ride_details->status == 0) {
                        $provider_running_service = ProviderUserRunningService::query()
                            ->where('provider_id', $driver_id)
                            ->where('user_id', $ride_details->user_id)
                            ->where('service_cat_id', $ride_details->service_cat_id)
                            ->where('booking_id', $ride_details->id)
                            ->first();
                        if($provider_running_service == Null){
                            $ride_details->driver_id = $driver_details_id->driver_id;
                            $ride_details->driver_name = $driver_details_id->driver_name;
                            $ride_details->status = $update_status;
                            $ride_details->save();

                            $this->notificationClass->userTransportRentalNotification($ride_details->id);
                        } else{
                            return response()->json([
                                'status' => 0,
                                'message_code' => 275,
//                                'message' => 'You already have running ride!',
                                'message' => __('driver_messages.275'),
                            ]);
                        }

                    } elseif ($ride_details->status == 4) {
                        return response()->json([
                            "status" => 0,
//                            "message" => "Sorry, Your ride request is cancelled by user",
                            "message" => __('driver_messages.24'),
                            "message_code" => 24,
                        ]);
                    } else {
                        return response()->json([
                            "status" => 0,
//                            "message" => "Sorry, You are too late to the accept ride",
                            "message" => __('driver_messages.23'),
                            "message_code" => 23,
                        ]);
                    }
                    break;
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
                        $ride_details->cancel_by = "driver";
                        $ride_details->save();

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
                        $this->notificationClass->userTransportRentalNotification($ride_details->id);
                    } else {
                        return response()->json([
                            "status" => 0,
//                            "message" => "you have not allowed to cancel this ride",
                            "message" => __('driver_messages.123'),
                            "message_code" => 123,
                        ]);
                    }
                    break;
                case $update_status == 3:
                    if ($ride_details->status == 0 || $ride_details->status == 1) {
                        $validator = Validator::make($request->all(), [
                            "start_km" => "required|numeric",
                        ]);
                        if ($validator->fails()) {
                            return response()->json([
                                "status" => 0,
                                "message" => $validator->errors()->first(),
                                "message_code" => 9,
                            ]);
                        }
                        $ride_details->status = $update_status;
                        $ride_details->start_km = $request->get('start_km');
                        $ride_details->pickup_date_time = date('Y-m-d H:i:s');
                        $ride_details->save();

                        $driver_status = ProviderServices::query()->where('provider_id',$driver_id)->first();
                        $driver_status->current_status = 0;
                        $driver_status->save();

                        $provider_running_service = new ProviderUserRunningService();
                        $provider_running_service->provider_id = $driver_id;
                        $provider_running_service->user_id = $ride_details->user_id;
                        $provider_running_service->service_cat_id = $ride_details->service_cat_id;
                        $provider_running_service->booking_id = $ride_details->id;
                        $provider_running_service->save();

                        $this->notificationClass->userTransportRentalNotification($ride_details->id);
                    }
                    break;
                case $update_status == 6:
                    if ($ride_details->status == 5) {
                        $validator = Validator::make($request->all(), [
                            "end_km" => "required|numeric",
                        ]);
                        if ($validator->fails()) {
                            return response()->json([
                                "status" => 0,
                                "message" => $validator->errors()->first(),
                                "message_code" => 9,
                            ]);
                        }
                        $ride_details->status = $update_status;
                        $ride_details->end_km = $request->get('end_km');
                        $ride_details->drop_date_time = date('Y-m-d H:i:s');
                        $ride_details->save();

                        ProviderUserRunningService::query()
                            ->where('provider_id', $driver_id)
                            ->where('user_id', $ride_details->user_id)
                            ->where('service_cat_id', $ride_details->service_cat_id)
                            ->where('booking_id', $ride_details->id)
                            ->delete();

                        $driver_status = ProviderServices::query()->where('provider_id',$driver_id)->first();
                        $driver_status->current_status = 1;
                        $driver_status->save();

                        $service_category_id = 0;
                        if ($ride_details->service_cat_id == $this->bikeRental) {
                            $service_category_id = $this->bikeService;
                        }
                        if ($ride_details->service_cat_id == $this->taxiRental) {
                            $service_category_id = $this->taxiService;
                        }
                        //$vehicle_type_id = $request->get('vehicle_type_id');
                        $vehicle_type_id = $ride_details->vehicle_type_id;
                        $vehicle_type = TransportVehicleType::query()->where('service_cat_id', $service_category_id)->where('id', $vehicle_type_id)->where('status', 1)->first();
                        if ($vehicle_type == Null) {
                            return response()->json([
                                "status" => 0,
//                                "message" => "something went to wrong",
                                "message" => __('driver_messages.9'),
                                "message_code" => 9,
                            ]);
                        }

                        $start_date_time = strtotime($ride_details->pickup_date_time);
                        $end_date_time = strtotime($ride_details->drop_date_time);
                        $diff = abs($end_date_time - $start_date_time);
                        $years = floor($diff / (365 * 60 * 60 * 24));
                        $hours = floor(($diff - $years * 365 * 60 * 60 * 24) / (60 * 60));
                        $minutes = floor(($diff - $years * 365 * 60 * 60 * 24 - $hours * 60 * 60) / 60);

                        $total_km = $ride_details->end_km - $ride_details->start_km;

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
                        }
                        $count_km_limit = round($count_km_limit_for_hours + $count_km_limit_for_minutes);

                        $extra_amount = 0;

                        $extra_km = $total_km - $count_km_limit;
                        if ($extra_km > 1) {
                            $extra_amount = $extra_km * $rental_cost_for_km;
                        } else {
                            $extra_km = 0;
                        }

                        $service_settings = ServiceSettings::query()->where('service_cat_id', $ride_details->service_cat_id)->first();
                        if ($service_settings != Null) {
                            $get_tax = $service_settings->tax;
                            $admin_commission = $service_settings->admin_commission;
                        } else {
                            $get_tax = 0;
                            $admin_commission = 0;
                        }
                        $discount_cost = 0;
                        $discount_promo = 0;
                        if ($ride_details->promo_code != 0) {
                            $used_promocode_details = UsedPromocodeDetails::query()->where('id', $ride_details->promo_code)->first();
                            if ($used_promocode_details != Null) {
                                $get_promocode = PromocodeDetails::query()->where('id', $used_promocode_details->promocode_id)->first();
                                if ($get_promocode != Null) {
                                    $discount_on_amount = ($count_amount + $extra_amount);
                                    if ($get_promocode->discount_type == 1) {
                                        $discount_promo = round($get_promocode->discount_amount, 2);
                                    } elseif ($get_promocode->discount_type == 2) {
                                        $discount_promo = round( (($discount_on_amount * $get_promocode->discount_amount) / 100), 2);
                                    } else {
                                        $discount_promo = 0;
                                    }
                                    $used_promocode_details->discount_amount = $discount_promo;
                                    $used_promocode_details->status = 1;
                                    $used_promocode_details->save();
                                }
                            }
                        }


                        $tax_cost = round(((($count_amount + $extra_amount - $discount_cost - $discount_promo) * $get_tax) / 100), 2);
                        $total_pay = ((($count_amount + $extra_amount) - $discount_cost) - $discount_promo) + $tax_cost;

                        $driver_amount = $count_amount + $extra_amount;
                        $commission = round((($driver_amount * $admin_commission) / 100), 2);
                        $driver_new_amount = round($driver_amount - $commission, 2);
                        $admin_commission = round($commission - $discount_cost, 2);

                        $ride_details->total_km = $total_km;
                        $ride_details->ride_fare = $count_amount;
                        $ride_details->additional_fare = $extra_amount;
                        $ride_details->additional_km = $extra_km;
                        $ride_details->discount = $discount_cost;
                        $ride_details->tax = $tax_cost;
                        $ride_details->total_pay = $total_pay;
                        $ride_details->driver_amount = $driver_new_amount;
                        $ride_details->admin_commission = $admin_commission;
                        $ride_details->km_limit_per_hour = $rental_km_limit_for_1hour;
                        $ride_details->rental_amount_per_hour = $rental_amount_for_1hour;
                        $ride_details->extra_km_amount_per_hour = $rental_cost_for_km;
                        $ride_details->save();

                        $this->notificationClass->userTransportRentalNotification($ride_details->id);
                    }
                    break;
                case $update_status == 7:
                    $validator = Validator::make($request->all(), [
                        "payment_type" => "required|integer|in:1,2,3",
                    ]);
                    if ($validator->fails()) {
                        return response()->json([
                            "status" => 0,
                            "message" => $validator->errors()->first(),
                            "message_code" => 9,
                        ]);
                    }
                    if ($ride_details->payment_status != 1){
                        return response()->json([
                            "status" => 0,
//                            "message" => "Payment Process Pending",
                            "message" => __('driver_messages.83'),
                            "message_code" => 83,
                        ]);
                    }
                    if ($ride_details->status == 6) {
                        $ride_details->status = $update_status;
                        $ride_details->payment_type = $request->get('payment_type');
                        $ride_details->payment_status = 1;
                        $ride_details->save();

                        $this->notificationClass->userTransportRentalNotification($ride_details->id);
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
                    if ($request->get('rating') != Null || $request->get('comment') != Null) {
                        if ($request->get('rating') != Null) {
                            $user_rating = UserRatings::query()->where('user_id', $ride_details->user_id)->where('rental_book_id', $ride_details->id)->first();
                            if ($user_rating == Null) {
                                $user_rating = new UserRatings();
                                $user_rating->user_id = $ride_details->user_id;
                                $user_rating->provider_id = $driver_id;
                                $user_rating->rental_book_id = $ride_details->id;
                            }
                            $user_rating->rating = round($request->get('rating'), 2);
                            $user_rating->comment = $request->get('comment') != Null ? trim($request->get('comment')) : Null;
                            $user_rating->save();

                            $user_details = User::query()->where('id', $ride_details->user_id)->first();
                            if ($user_details != Null) {
                                $ratings = UserRatings::query()->select(DB::raw('avg(rating) as ratings'))
                                    ->groupBy('user_id')
                                    ->where('user_id', $user_details->id)
                                    ->first();
                                $user_details->rating = $ratings->ratings;
                                $user_details->save();
                            }
                        }
                    }
                    if ($ride_details->status == 7) {
                        $ride_details->status = $update_status;
                        $ride_details->save();

                        $this->notificationClass->userTransportRentalNotification($ride_details->id);
                    }
                    break;
                case $update_status == 9:
                    if ($ride_details->status == 8) {
                        $ride_details->status = $update_status;
                        $ride_details->save();

                        $this->notificationClass->userTransportRentalNotification($ride_details->id);
                    }
                    break;
                default:
                    return response()->json([
                        "status" => 0,
//                        "message" => "something went to wrong!",
                        "message" => __('driver_messages.9'),
                        "message_code" => 9,
                    ]);
            }
            return response()->json([
                "status" => 1,
//                "message" => "success",
                "message" => __('driver_messages.1'),
                "message_code" => 1,
                "ride_status" => $ride_details->status
            ]);
        } else {
            return response()->json([
                "status" => 0,
//                "message" => "something went to wrong!",
                "message" => __('driver_messages.9'),
                "message_code" => 9,
            ]);
        }
    }
}
