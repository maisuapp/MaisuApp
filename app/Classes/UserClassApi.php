<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 13-12-2018
 * Time: 03:48 PM
 */

namespace App\Classes;


use App\Http\Controllers\StripeController;
use App\model\AppVersionSetting;
use App\model\DeliveryCharges;
use App\model\GeneralSettings;
use App\model\OtherServiceCategory;
use App\model\OtherServiceProviderDetails;
use App\model\OtherServiceProviderPackages;
use App\model\PromocodeDetails;
use App\model\Provider;
use App\model\ProviderServices;
use App\model\ProviderUserRunningService;
use App\model\RestrictedArea;
use App\model\ServiceCategory;
use App\model\ServiceSettings;
use App\model\ServiceSliderBanner;
use App\model\StoreDetails;
use App\model\StoreProductCategory;
use App\model\StoreProductDetails;
use App\model\StoreProductOptions;
use App\model\StoreProductSize;
use App\model\StoreProductToppings;
use App\model\StoreTimings;
use App\model\SurchargeTimings;
use App\model\TempUserBooking;
use App\model\TempUserWallet;
use App\model\TransportCategoryList;
use App\model\TransportCourierDetails;
use App\model\TransportDriverDetails;
use App\model\TransportRideDriverToken;
use App\model\TransportVehicleType;
use App\model\TransportRideBook;
use App\model\TransportWeightList;
use App\model\UsedPromocodeDetails;
use App\model\User;
use App\model\UserAddress;
use App\model\UserCardDetails;
use App\model\UserFavouriteStore;
use App\model\UserOrderCart;
use App\model\UserPackageBooking;
use App\model\UserPackageBookingQuantity;
use App\model\UserProductBooking;
use App\model\UserProductBookingOptions;
use App\model\UserProductBookingQuantity;
use App\model\UserProductBookingSize;
use App\model\UserProductBookingStatus;
use App\model\UserProductBookingToppings;
use App\model\UserRatings;
use App\model\UserReferHistory;
use App\model\UserRideWayPoint;
use App\model\UserRunningRide;
use App\model\UserWalletTransaction;
use App\model\WorldCurrency;
use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;


class UserClassApi
{
//        json response status [
//            0 => false,
//            1 => true,
//            2 => registration pending,
//            3 => app user blocked,
//            4 => app user access token not match,
//            5 => app user not found
//          ]

    private $notificationClass;
    private $courier_service_id_array =  [4];
    private $adminClassApi;
    private $user_type = 0;
    private $store_type = 1;
    private $driver_type = 2;
    private $provider_type = 3;

    public function __construct(NotificationClass $notificationClass , AdminClass $adminClassApi)
    {
        $this->notificationClass = $notificationClass;

        $this->adminClassApi = $adminClassApi;

    }

    public function generateBookingNo()
    {
        return random_int(1, 9) . date('siHYdm') . random_int(1, 9);
    }

    public function checkUserAllow($user_id, $access_token){
        $user_details = User::query()->select('id', 'first_name', 'last_name', 'email', 'verified_at', 'contact_number', 'login_type', 'login_id', 'password', 'avatar', 'invite_code', 'access_token', 'device_token', 'status as user_status', 'currency', 'pending_refer_discount', 'language','ip_address','time_zone')
            ->where('id',"=", $user_id)->whereNull('users.deleted_at')->first();
        if ($user_details != Null) {
            if ($user_details->access_token != $access_token) {
                return response()->json([
                    'status' => 4,
//                    'message' => "Access Token Not Match!",
                    'message' => __('user_messages.4'),
                    "message_code" => 4,
                ]);
            }
            if ($user_details->verified_at == Null) {
                return response()->json([
                    'status' => 2,
//                    'message' => "User Not Verified!",
                    'message' => __('user_messages.2'),
                    "message_code" => 2,
                ]);
            }
            if ($user_details->user_status == 0) {
                return response()->json([
                    'status' => 3,
//                    'message' => 'Your account is currently blocked, so not authorised to allow any activity!',
                    'message' => __('user_messages.3'),
                    "message_code" => 3,
                ]);
            }
            return $user_details;
        } else {
            return response()->json([
                'status' => 5,
//                'message' => "User not found!",
                'message' => __('user_messages.5'),
                "message_code" => 5,
            ]);
        }
    }

    public function userLoginRegisterUpdateDetails($user_details)
    {
        if ($user_details['avatar'] != Null) {
            if (filter_var($user_details['avatar'], FILTER_VALIDATE_URL) == true) {
                $avatar = $user_details['avatar'];
            } else {
                $avatar = url('/assets/images/profile-images/customer/' . $user_details['avatar']);
            }
        } else {
            $avatar = Null;
        }
        return response()->json([
            'status' => 1,
//            'message' => 'Success!',
            'message' => __('user_messages.1'),
            "message_code" => 1,
            'user_id' => $user_details['id'],
            'user_verified' => $user_details['verified_at'] != Null ? 1 : 0,
            'user_name' => $user_details['first_name'],
            'access_token' => $user_details['access_token'] . '',
            'email' => $user_details['email'] != Null ? $user_details['email'] : "",
            'login_type' => $user_details['login_type'],
            'profile_image' => $avatar,
            'gender' => $user_details['gender'],
            'contact_number' => $user_details['contact_number'] != Null ? $user_details['contact_number'] : "",
            'referral_code' => $user_details['invite_code'] != Null ? $user_details['invite_code'] : "",
            'select_country_code' => $user_details['country_code'] != Null ? $user_details['country_code'] : "",
            'select_currency' => $user_details['currency'] != Null ? $user_details['currency'] : "",
            'select_language' => $user_details['language'] != Null ? $user_details['language'] : "",
            'emergency_contact' => $user_details['emergency_contact'] != Null ? $user_details['emergency_contact'] . '' : "",
            'server_time_zone' => config('app.timezone'),
        ]);
    }

    //transport service start

    // find provider service id
    public function findTransportProviderServiceId($provider_id, $service_cat_id, $find_driver_id)
    {
        $provider_service_id = ProviderServices::where('provider_id', $provider_id)->where('service_cat_id', $service_cat_id)->first();
        if ($provider_service_id != Null) {
            if ($find_driver_id == true) {
                return $this->findTransportDriverId($provider_service_id->id);
            } else {
                return $provider_service_id->id;
            }
        } else {
            return response()->json([
                'status' => 0,
                'message' => "Provider Service not found!",
                "message_code" => 15,
            ]);
        }
    }

    public function findTransportDriverId($provider_service_id)
    {
        $driver_id = TransportDriverDetails::query()->where('provider_service_id', $provider_service_id)->first();
        if ($driver_id != Null) {
            return $driver_id->id;
        } else {
            return response()->json([
                'status' => 0,
                'message' => "Provider Service not found!",
                "message_code" => 15,
            ]);
        }
    }

    public function TransportRideBook($request, $user_details)
    {
        $validator = Validator::make($request->all(), [
            "service_category_id" => "required|numeric|in:1,2,4",
            "payment_type" => "required|in:1,2,3",
            "estimated_time" => "required",
            "total_distance" => "required",
            "service_type_id" => "required",
            //"estimate_price" => "required",
            "gender" => "nullable|in:0,1,2",
            "pickup_date_time" => "nullable",
            "promo_code" => "nullable",
            "address_list" => "required",
            //"handicap" => "required", //additional field
            //"child_seat" => "required", //additional field
            "additional_remarks" => "nullable",
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }
        if ($request['service_category_id'] == 4){
            try {
                $address_list = json_decode($request->get('address_list'), true);
                $new_address_list = [];
                foreach ($address_list as $key => $address) {
                    if ($address['address'] == Null || $address['address_lat'] == Null || $address['address_long'] == Null|| $address['name'] == Null|| $address['contact_number'] == Null) {
                        unset($address_list[$key]);
                    }
                    $new_address_list[] = $address;
                }
            }catch (\Exception $e){
                return response()->json([
                    "status" => 0,
//                    "message" => "Invalid Json",
                    'message' => __('user_messages.9'),
                    "message_code" => 9,
                ]);
            }
        }else{
            try {
                $address_list = json_decode($request->get('address_list'), true);
                $new_address_list = [];
                foreach ($address_list as $key => $address) {
                    if ($address['address'] == Null || $address['address_lat'] == Null || $address['address_long'] == Null) {
                        unset($address_list[$key]);
                    }
                    $new_address_list[] = $address;
                }
            }catch (\Exception $e){
                return response()->json([
                    "status" => 0,
//                    "message" => "Invalid Json",
                    'message' => __('user_messages.9'),
                    "message_code" => 9,
                ]);
            }
        }

        if ($request['payment_type'] == 2 ){
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
            if(in_array($request->get('service_category_id'),$this->courier_service_id_array))
            {
                $card_details = UserCardDetails::query()->where('id', $request->get('card_id'))->first();
                if ($card_details == Null) {
                    return response()->json([
                        "status" => 0,
//                        "message" => "card details not found",
                        'message' => __('user_messages.120'),
                        "message_code" => 120,
                    ]);
                }
            }
        }

        $driver_algorithm = 0;
        $timeout = 60;
        //$general_settings = GeneralSettings::query()->select('user_timeout', 'driver_algorithm')->first();
        $general_settings = request()->get("general_settings");
        if ($general_settings != Null){
            $driver_algorithm = $general_settings->driver_algorithm - 0;
            $timeout = $general_settings->user_timeout != NUll ? round($general_settings->user_timeout) : 60;
        }

        $address_lat = trim($new_address_list[0]['address_lat']);
        $address_long = trim($new_address_list[0]['address_long']);
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
//                "message" => "Your pickup address exist in restricted area, please try with other location!",
                'message' => __('user_messages.194'),
                "message_code" => 194,
            ]);
        }
        $address_count = count($new_address_list);
        if ($address_count < 2) {
            return response()->json([
                "status" => 0,
//                "message" => "something went to wrong!",
                'message' => __('user_messages.9'),
                "message_code" => 9,
            ]);
        }
        if ($request['service_category_id'] == 2) {
            $validator = Validator::make($request->all(), [
                "handicap" => "required|in:0,1",
                "child_seat" => "required|in:0,1"
            ]);
            if ($validator->fails()) {
                return response()->json([
                    "status" => 0,
                    "message" => $validator->errors()->first(),
                    "message_code" => 9,
                ]);
            }
        }

        if ($request['service_category_id'] == 4) {
            $validator = Validator::make($request->all(), [
//                "recipient_name" => "required",
//                "recipient_contact_no" => "required|numeric",
//                "goods_height" => "required|numeric",
//                "goods_width" => "required|numeric",
//                "goods_weight" => "required|numeric",
//                "description" => "required",
                "house_name" => "required",
                "sender_landmark" => "required",
                "recipient_landmark" => "required",
                "estimate_price" => "nullable",
                "category_id" => "required",
                "weight_id" => "required",
                "delivery_instruction" => "nullable",
                "tip" => "nullable|numeric",
            ]);
            if ($validator->fails()) {
                return response()->json([
                    "status" => 0,
                    "message" => $validator->errors()->first(),
                    "message_code" => 9,
                ]);
            }
            if ($address_count != 2) {
                return response()->json([
                    "status" => 0,
//                    "message" => "something went to wrong!",
                    'message' => __('user_messages.9'),
                    "message_code" => 9,
                ]);
            }
        }
        //find price service_type
        if ($request['service_category_id'] == 4){
            $transport_weight_list = TransportWeightList::query()->where('id',$request['weight_id'])->first();
            if ($transport_weight_list == Null) {
                return response()->json([
                    "status" => 0,
//                    "message" => "Vehicle Type Not Found!",
                    'message' => __('user_messages.9'),
                    "message_code" => 9,
                ]);
            }
            $transport_category_list = TransportCategoryList::query()->where('id',$request['category_id'])->first();
            if ($transport_category_list == Null) {
                return response()->json([
                    "status" => 0,
//                    "message" => "Category Type Not Found!",
                    'message' => __('user_messages.9'),
                    "message_code" => 9,
                ]);
            }
            //find price service_type
            $get_vehicle_price = TransportVehicleType::query()
                ->where('service_cat_id','=', $request['service_category_id'])
                ->where('weight_limit', '!=',null)
                ->orderBy('weight_limit', 'asc');
//            if ($transport_weight_list->close_limit != 0){
//                $get_vehicle_price->where('weight_limit','<',$transport_weight_list->close_limit)
//                    ->where('weight_limit','>=',$transport_weight_list->start_limit);
//            }else{
                $get_vehicle_price->where('weight_limit','=',$transport_weight_list->id);
//            }
            $get_vehicle_price = $get_vehicle_price->first();

        }else{
            $get_vehicle_price = TransportVehicleType::query()->where('id', $request['service_type_id'])->first();
        }
        if ($get_vehicle_price == Null) {
            return response()->json([
                "status" => 0,
//                "message" => "Vehicle Type Not Found!",
                'message' => __('user_messages.9'),
                "message_code" => 9,
            ]);
        }
        $ride = new TransportRideBook();
        $ride->user_id = $request['user_id'];
        $ride->service_cat_id = $request['service_category_id'];
        $ride->vehicle_type_id = $get_vehicle_price->id;
        $ride->vehicle_cost_for_km = $get_vehicle_price->cost_for_km;
        $ride->vehicle_cost_for_min = $get_vehicle_price->time_fare;
        $ride->ride_no = $ride->generateRideNo();
        $ride->otp = $ride->generateOtp(4);
        $ride->user_name = $user_details['first_name'];
        if ($request['pickup_date_time'] != Null) {
            $ride->pickup_datetime = date('Y-m-d H:i:s', strtotime($request['pickup_date_time']));
            $ride->ride_type = 1;
        } else {
            $ride->pickup_datetime = date('Y-m-d H:i:s');
        }

        $ride->pickup_address = $new_address_list[0]['address'];
        $ride->pickup_latlong = $new_address_list[0]['address_lat'] . ',' . $new_address_list[0]['address_long'];
        $ride->destination_address = $new_address_list[$address_count - 1]['address'];
        $ride->destination_latlong = $new_address_list[$address_count - 1]['address_lat'] . ',' . $new_address_list[$address_count - 1]['address_long'];

        $ride->total_distance = $request['total_distance'];
        $ride->eta = $request['estimated_time'];
//        $ride->payment_type = strtolower(trim($request['payment_type']));
        $ride->payment_type = $request['payment_type'];
        $ride->payment_status = 0;
        $ride->driver_gender = $request->get('gender') - 0;
        $ride->tip = $request['tip'] != null ? $request['tip'] : 0;
        $ride->additional_request = ($request->get('additional_remarks') != "") ? $request->get('additional_remarks') : "";
        if ($request['service_category_id'] == 2) {
            $ride->handicap = $request['handicap'];
            $ride->child_seat = $request['child_seat'];
        } else {
            $ride->handicap = 0;
            $ride->child_seat = 0;
        }

        $ride->status = 0;
        $ride->driver_algorithm = $driver_algorithm;

        $date = new \DateTime("now", new \DateTimeZone(config('app.timezone')) );
        $ride->retry_time = $date->format('Y-m-d H:i:s');
        $ride->save();
        if ($request['service_category_id'] == 4) {
            $courier_details = new TransportCourierDetails();
            $courier_details->ride_id = $ride->id;
            $courier_details->house_name = $request['house_name'];

            $courier_details->sender_name = $new_address_list[0]['name'];
            $courier_details->sender_contact_number = $new_address_list[0]['contact_number'];
            $courier_details->sender_landmark = $request['sender_landmark'];

            $courier_details->recipient_name = $new_address_list[1]['name'];
            $courier_details->recipient_contact_number = $new_address_list[1]['contact_number'];
            $courier_details->recipient_landmark = $request['recipient_landmark'];
            $courier_details->category_id = $request['category_id'];
            $courier_details->category_name = $transport_category_list->category_name;
            $courier_details->weight_id = $request['weight_id'];
            $courier_details->weight_start_limit = $transport_weight_list->start_limit;
            $courier_details->weight_close_limit = $transport_weight_list->close_limit;
            $courier_details->estimate_price = round($request['estimate_price'],2);
//            $courier_details->goods_height = round($request['goods_height'],2);
//            $courier_details->goods_width = round($request['goods_width'],2);
//            $courier_details->goods_weight = round($request['goods_weight'], 2);
//            $courier_details->description = $request['description'];
//            if ($request['description'] != Null) {
            $courier_details->delivery_instruction = $request['delivery_instruction'];
//            }
            $courier_details->save();
        }
        if ($request['service_category_id'] != 4 && $address_count > 2) {
            $ride->is_way_point = 1;
            $ride->way_point_status = 0;
            $ride->save();

            $ride_way_point = new UserRideWayPoint();
            $ride_way_point->ride_id = $ride->id;
            $ride_way_point->way_point_1 = $new_address_list[1]['address'];
            $ride_way_point->lat_long_1 = $new_address_list[1]['address_lat'] . ',' . $new_address_list[1]['address_long'];
            if ($address_count > 3) {
                $ride_way_point->way_point_2 = $new_address_list[2]['address'];
                $ride_way_point->lat_long_2 = $new_address_list[2]['address_lat'] . ',' . $new_address_list[2]['address_long'];
            }
            if ($address_count > 4) {
                $ride_way_point->way_point_3 = $new_address_list[3]['address'];
                $ride_way_point->lat_long_3 = $new_address_list[3]['address_lat'] . ',' . $new_address_list[3]['address_long'];
            }
            $ride_way_point->save();
        } else {
            $ride->is_way_point = 0;
            $ride->way_point_status = 0;
        }
        $service_settings = ServiceSettings::query()->where('service_cat_id', $ride->service_cat_id)->first();
        if ($service_settings != Null) {
            $admin_commission = $service_settings->admin_commission;
            $get_tax = $service_settings->tax;
        } else {
            $admin_commission = 0;
            $get_tax = 0;
        }

        //code for calcualte surcharge
        if ($get_vehicle_price->surcharge_timings_status == 1){
            $day = date('D', strtotime(date($ride->pickup_datetime)));
            //$day = date('D');
            $surcharge_timings = SurchargeTimings::query()->where('transport_vehicle_type_id', $get_vehicle_price->id)
                ->where('day', $day)
                ->first();
            if ($surcharge_timings != null){

                $time = date('H:i:s', strtotime(date($ride->pickup_datetime)));

                if ( $time >= $surcharge_timings->start_time && $time <= $surcharge_timings->end_time){

                    $km = explode(' ', $ride->total_distance);
                    $km_count = floatval($km[0]);
                    try{
                        $distance_km = $km_count;
                    } catch (\Exception $e) {
                        $distance_km = 0;
                    }

                    $ride->surcharge_start_time = $surcharge_timings->start_time;
                    $ride->surcharge_end_time = $surcharge_timings->end_time;
                    $ride->surcharge_price = $surcharge_timings->price;
                    $ride->surcharge_cost =  round($surcharge_timings->price * $distance_km,2);
                    $ride->save();

                }
            }
        }

        //End surhcage

        $ride->BookingCountDistanceAmount($ride->total_distance, $get_vehicle_price->cost_for_km, $get_vehicle_price->base_fare, $get_vehicle_price->time_fare, $ride->eta);
        /* $promo_code_discount = 0;
         if ($request['promo_code'] != 0) {
             $get_discount_on_amount = ($ride->base_fare + $ride->total_distance_amount + $ride->time_fare_amount);
             $min_fare_amount = ($get_vehicle_price->min_fare_amount);
             $adjustment_amount = 0;
             if ($get_discount_on_amount < $min_fare_amount) {
                 $adjustment_amount = round(($min_fare_amount - $get_discount_on_amount), 2); //adjust amount
             }
             $discount_on_amount = ($get_discount_on_amount + $adjustment_amount);
             $promo_code = $this->checkPromoCodeValid($request['promo_code'], $discount_on_amount, $user_details->id, $request['service_category_id']);

             if ($promo_code != 0) {
                 if ($promo_code != 1) {
                     $get_prmocode = PromocodeDetails::query()->where('id', $request['promo_code'])->where('status', 1)->first();
                     if ($get_prmocode != Null) {
                         $promo_code_discount = $promo_code;
                         $add_promocode_details = new UsedPromocodeDetails();
                         $add_promocode_details->service_cat_id = $request['service_category_id'];
                         $add_promocode_details->user_id = $user_details->id;
                         $add_promocode_details->promocode_id = $get_prmocode->id;
                         $add_promocode_details->promocode_name = $get_prmocode->promo_code;
                         $add_promocode_details->discount_amount = $promo_code;
                         $add_promocode_details->status = 0;
                         $add_promocode_details->save();

                         $get_prmocode->total_usage = $get_prmocode->total_usage + 1;
                         $get_prmocode->save();

                         $ride->promo_code = $add_promocode_details->id;
                         $ride->save();
                     }
                 }
             }
         }*/

        $promo_code_discount = 0;
        $get_promo_code = $request->get('promo_code');
        if ($get_promo_code != 0) {
            if (!is_numeric($get_promo_code)) {
                $get_promocode = PromocodeDetails::query()->where('promo_code', $get_promo_code)->where('service_cat_id', $request->get('service_category_id'))->where('status','1')->first();
                $get_promo_code_id = ($get_promocode != Null) ? $get_promocode->id : 0;
            }
            //$discount_on_amount = (($total_price - $discount_cost) + $place_order->packaging_cost);

            if($get_promo_code_id > 0)
            {
                $get_discount_on_amount = ($ride->base_fare + $ride->total_distance_amount + $ride->time_fare_amount);
                $min_fare_amount = ($get_vehicle_price->min_fare_amount);
                $adjustment_amount = 0;
                if ($get_discount_on_amount < $min_fare_amount) {
                    $adjustment_amount = round(($min_fare_amount - $get_discount_on_amount), 2); //adjust amount
                }
                $discount_on_amount = ($get_discount_on_amount + $adjustment_amount);
//                        $promo_code = $this->userClassapi->checkPromoCodeValid($get_promo_code_id, $discount_on_amount, $user_details->id, $request->get('service_category_id'));
                list($promo_code_status,$promo_message_code,$promo_code_message,$min_order_amount,$promo_code,$promo_code_name) = $this->checkPromoCodeValid($get_promo_code_id, $discount_on_amount, $user_details->id, $request->get('service_category_id'));

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

                        $ride->promo_code = $add_promocode_details->id;
                        $ride->save();
                    }
                }
            }

        }

        $ride->BookingCostCount($ride->base_fare, $ride->total_distance_amount, $promo_code_discount, $get_tax, $admin_commission, $get_vehicle_price->min_fare_amount, $ride->time_fare_amount,$ride->surcharge_cost);

        if ($user_details->pending_refer_discount > 0) {
            $user = User::query()->where('id', $user_details->id)->whereNull('users.deleted_at')->first();
            if ($user != Null) {
                $user_refer_history = UserReferHistory::query()->where('user_id', $user_details->id)->where('user_status', 0)->first();
                $total_price = $ride->total_pay;
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
                    $ride->total_pay = $refer_discount_price;
                    $ride->refer_discount = round($get_refer_discount_price, 2);
                    $ride->save();
                    $user_refer_history->user_status = 1;
                    $user_refer_history->save();
                    $user->pending_refer_discount = $user->pending_refer_discount - 1;
                    $user->save();
                } else {
                    $user_refer_history = UserReferHistory::query()->where('refer_id', $user_details->id)->where('refer_status', 0)->first();
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
                        $ride->total_pay = $refer_discount_price;
                        $ride->refer_discount = round($get_refer_discount_price, 2);
                        $ride->save();
                        $user_refer_history->refer_status = 1;
                        $user_refer_history->save();
                        $user->pending_refer_discount = $user->pending_refer_discount - 1;
                        $user->save();
                    }
                }
            }
        }

        if ($request['service_category_id'] == 4){
            if ($request->get('payment_type') == 1) {
                $ride->payment_type = $request->get('payment_type');
//                $ride_details->payment_status = 1;
                $ride->save();

                $ride_id = $ride->id;
                $service_category_id = $ride->service_cat_id;
                $this->notificationClass->driverRequestNotification($service_category_id, $ride_id,$request->get('gender'));
            }elseif ($request->get('payment_type') == 2) {
                $ride->payment_type = $request->get('payment_type');
                $ride->payment_status = 1;
                $ride->save();
                $ride_id = $ride->id;
                $service_category_id = $ride->service_cat_id;
                $this->notificationClass->driverRequestNotification($service_category_id, $ride_id,$request->get('gender'));
            }elseif ($request->get('payment_type') == 3) {
                $service_category = ServiceCategory::query()->where('id', $ride->service_cat_id)->first();
                if ($service_category == Null) {
                    return response()->json([
                        "status" => 0,
//                        "message" => "something want to wrong!",
                        'message' => __('user_messages.9'),
                        "message_code" => 9,
                    ]);
                }
                $wallet_balance = UserWalletTransaction::query()->where('user_id', $request->get('user_id'))->orderBy('id', 'desc')->first();
                if ($wallet_balance != Null) {
                    $balance = $wallet_balance->remaining_balance;
                } else {
                    $balance = 0;
                }
                if ($ride->total_pay > $balance) {
                    $ride->status = 10;
                    $ride->save();
                    return response()->json([
                        "status" => 0,
//                        "message" => "You can't pay ride amount through Wallet because your wallet balance is insufficient.",
                        'message' => __('user_messages.109'),
                        "message_code" => 109
                    ]);
                }
                $add_balance = new UserWalletTransaction();
                $add_balance->user_id = $request->get('user_id');
                $add_balance->transaction_type = 2;
                $add_balance->amount = $ride->total_pay;
                $add_balance->order_no = $ride->ride_no;
                $add_balance->subject = "Paid To - " . $service_category->name . " Booking #" . $ride->ride_no;
//                $add_balance->subject = "Paid to " . $ride_details->driver_name . " - " . $service_category->name . " Booking # " . $ride_details->ride_no;
                $add_balance->remaining_balance = round($balance - $ride->total_pay, 2);
                $add_balance->subject_code = 4;
                $add_balance->save();

                $ride->payment_type = $request->get('payment_type');
                $ride->payment_status = 1;
                $ride->save();
                $ride_id = $ride->id;
                $service_category_id = $ride->service_cat_id;
                $this->notificationClass->driverRequestNotification($service_category_id, $ride_id,$request->get('gender'));
            }else {
                return response()->json([
                    "status" => 0,
//                    "message" => "something went to wrong",
                    'message' => __('user_messages.9'),
                    "message_code" => 9,
                ]);
            }
        }else{
            $ride_id = $ride->id;
            $service_category_id = $ride->service_cat_id;
            $this->notificationClass->driverRequestNotification($service_category_id, $ride_id,$request->get('gender'));
        }

        if ($general_settings !=  Null) {
            if ($general_settings->send_mail == 1) {
                $service_category = ServiceCategory::query()->where('id', $service_category_id)->first();
                if ($service_category != Null){
                    $email = $user_details['email'];
                    $service_name = ucwords(strtolower($service_category->name));
                    $user_name = $ride->user_name;
                    try{
                        $mail_type = "new_order_placed-transport";
                        $to_mail = $email;
                        $subject = "Your ".$general_settings->mail_site_name." ".$service_name ." Ride Request Placed";
                        $disp_data = array("##user_name##"=>$user_name,"##service_name##"=>$service_name );
                        $mail_return_data = $this->notificationClass->sendMail($subject,$to_mail,$mail_type,$disp_data);
                    } catch (\Exception $e){}
                    try{
                        if ($general_settings->send_receive_email != Null) {
                            $pickup_datetime =  $ride->pickup_datetime;
                            $destination_address = $ride->destination_address;
                            $pickup_address =  $ride->pickup_address;
                            $driver_name = "";
                            $mail_type = "admin_new__ride_request_-_transport";
                            $to_mail = $general_settings->send_receive_email;
                            $subject = $driver_name."  get a new " .$service_name ." ride request" ;
                            $disp_data = array("##driver_name##"=>$driver_name,"##service_name##"=>$service_name,
                                "##pickup_location##"=>$pickup_address,"##destination_location##"=>$destination_address,
                                "##pickup_time_location##"=>$pickup_datetime,"##user_name##"=>$user_name);
                            $mail_return_data = $this->notificationClass->sendMail($subject,$to_mail,$mail_type,$disp_data);
                        }
                    } catch (\Exception $e){}
                }
            }
        }

        return response()->json([
            "status" => 1,
//            "message" => "Ride Booking Successfully!",
            'message' => __('user_messages.1'),
            "message_code" => 1,
            "ride_id" => $ride->id,
            "booking_no" => $ride->ride_no,
            "pickup_date_time" => Date('d M, Y', strtotime($ride->pickup_datetime)),
            "pickup_address" => $ride->pickup_address,
            "destination_address" => $ride->destination_address,
            "ride_status" => $ride->status,
            "accept_time_out" => $timeout,
        ]);
    }

    public function transportRideBookingCancel($ride_id, $cancel_by, $cancel_reason)
    {
        $ride = TransportRideBook::query()->where('id', $ride_id)->first();
        if ($ride != Null) {
            //if ($ride->ride_type == 1 && $ride->pickup_datetime != Null) {
            //    if ($ride->driver_id != Null) {
            //        $pickup_date_time = strtotime(date('Y-m-d H:i:s', strtotime('-1 hour', strtotime($ride->pickup_datetime))));
            //        $current_date_time = strtotime(date('Y-m-d H:i:s'));
            //        if ($current_date_time >= $pickup_date_time) {
            //            return response()->json([
            //                "status" => 0,
            //                "message" => "You cant cancel the ride after 60 minutes of pickup time.",
            //                "message_code" => 146,
            //            ]);
            //        }
            //    }
            //}
            $ride->cancel_by = $cancel_by;
            if ($cancel_reason != Null) {
                $ride->cancel_reason = $cancel_reason;
                if( in_array($ride->service_cat_id,$this->courier_service_id_array) ) {
                    if ($ride->payment_type == 2 || $ride->payment_type == 3) {
                        $service_settings = ServiceSettings::query()->where('service_cat_id', $ride->service_cat_id)->first();
                        if ($service_settings != Null) {
                            $cancel_charge = $service_settings->cancel_charge != Null ? $service_settings->cancel_charge : 0;
                            $count_cancel_charge = round((($ride->total_pay * $cancel_charge) / 100), 2);
                            $charges = round($ride->total_pay - $count_cancel_charge, 2);
                            $ride->cancel_charge = $count_cancel_charge;
                            $ride->refund_amount = $charges;
                        }
                    }
                }
            }
            $ride_status = $ride->status;
            $ride->otp = null;
            $ride->status = 4;
            $ride->save();
            if ($ride_status < 6) {
                if ($ride->promo_code > 0) {
                    $used_promocode_details = UsedPromocodeDetails::query()->where('id', $ride->promo_code)->first();
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
            try {
                TransportRideDriverToken::query()->where('ride_id', $ride->id)->where('service_cat_id', $ride->service_cat_id)->delete();
            }catch (\Exception $e){}
            if ($ride->driver_id != Null) {
                $driver_details = Provider::query()->select('providers.device_token', 'provider_services.id', 'providers.login_device', 'providers.language')
                    ->join('provider_services', 'provider_services.provider_id', '=', 'providers.id')
                    ->join('transport_driver_details', 'transport_driver_details.provider_service_id', '=', 'provider_services.id')
                    ->where('transport_driver_details.id', $ride->driver_id)
                    ->first();
                if ($driver_details != Null) {
                    $this->notificationClass->driverCancelNotification($ride->id,$driver_details->device_token, $ride->service_cat_id, $driver_details->id, $driver_details->login_device,$driver_details->language,null);
                }
            }
            try {
                UserRunningRide::query()->where('service_cat_id', $ride->service_cat_id)->where('user_id', $ride->user_id)->where('booking_id', $ride_id)->delete();
                ProviderUserRunningService::query()->where('service_cat_id', $ride->service_cat_id)->where('user_id', $ride->user_id)->where('booking_id', $ride_id)->delete();
            }catch (\Exception $e){}
            return response()->json([
                "status" => 1,
//                "message" => "Success!",
                "message" => __('user_messages.1'),
                "message_code" => 1,
                "ride_id" => $ride->id,
            ]);
        } else {
            return response()->json([
                'status' => 0,
//                'message' => "Rides Not Found!",
                'message' => __('user_messages.26'),
                "message_code" => 26,
            ]);
        }
    }

    public function findTransportAssignDriverDetails($driver_id, $status)
    {
        if ($status == true) {
            if ($driver_id == Null) {
                return [
                    "status" => 0,
//                    "message" => "Assign Driver Details Not Found!",
                    "message" => __('user_messages.15'),
                    "message_code" => 15,
                ];
            }
        }
        $driver_details = TransportDriverDetails::query()->select(DB::raw("CONCAT(providers.first_name,'') as driver_name"), 'providers.avatar', 'providers.contact_number', 'transport_driver_details.rating', 'transport_vehicle_type.name as vehicle_type_name', 'transport_vehicle_type.icon_name as vehicle_type_icon', 'provider_services.service_cat_id')
            ->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
            ->join('providers', 'providers.id', 'provider_services.provider_id')
            ->join('transport_vehicle_type', 'transport_vehicle_type.id', '=', 'transport_driver_details.vehicle_type_id')
            ->where('transport_driver_details.id', $driver_id)
            ->whereNull('providers.deleted_at')
            ->first();
        if ($driver_details != Null) {
            if ($driver_details->avatar != Null) {
                $image_path = asset('assets/images/profile-images/provider/' . $driver_details->avatar);
            } else {
                $image_path = "";
            }
            if ($driver_details->vehicle_type_icon != Null) {
                $vehicle_type_icon = asset('assets/images/service-category/transport-service-type/' . $driver_details->vehicle_type_icon);
            } else {
                $vehicle_type_icon = "";
            }
            return [
                "status" => 1,
//                "message" => "success!",
                "message" => __('user_messages.1'),
                "message_code" => 1,
                "driver_name" => $driver_details->driver_name,
                "driver_image" => $image_path,
                "driver_rating" => $driver_details->rating,
//                        "driver_address" => $ride_details->driver_address,
                "driver_selected_service_cat_id" => $driver_details->service_cat_id,
//                "service_type" => $driver_details->vehicle_type_name,
//                "service_type_icon" => $vehicle_type_icon,
                "driver_contact_number" => $driver_details->contact_number,
            ];
        } else {
//            return [
//                "status" => 0,
//                "message" => "something went to wrong!",
//                "message_code" => 9,
//            ];
            return [
                "status" => 1,
//                "message" => "success!",
                "message" => __('user_messages.1'),
                "message_code" => 1,
                "driver_name" => '',
                "driver_image" => '',
                "driver_rating" => 0,
//                        "driver_address" => $ride_details->driver_address,
                "driver_selected_service_cat_id" => 0,
                "service_type" => '',
                "service_type_icon" => '',
                "driver_contact_number" => '',
            ];
        }
    }

    //rider details for transport assign driver details (TAD),transport completed invoice (TCI), transport receipt details(TRD)
    public function findRideDetailsforTADorTCIorTRD($ride_id, $forTAD, $forTCI, $forTRD)
    {
        $ride = TransportRideBook::query()->select('user_ride_booking.*','transport_vehicle_type.name as vehicle_type_name', 'transport_vehicle_type.icon_name as vehicle_type_icon')
            ->leftJoin('transport_vehicle_type', 'transport_vehicle_type.id', '=', 'user_ride_booking.vehicle_type_id')
            ->where('user_ride_booking.id', $ride_id)
            ->first();
        if ($ride != Null) {
            $pickup = array_map('trim', explode(",", $ride->pickup_latlong));
            $destination = array_map('trim', explode(",", $ride->destination_latlong));
            $way_points[] = [
                "address" => $ride->pickup_address,
                "address_lat" => trim($pickup[0]),
                "address_long" => trim($pickup[1])
            ];
            $waypoint_msg_code = 0;
            $waypoint_message = "";

            if ($ride->is_way_point == 1) {

                if($ride->way_point_status == 1){

                    $waypoint_msg_code = 272;
//                    $waypoint_message = "Driver Reached at Stop 1";
                    $waypoint_message = __('user_messages.272');
                }elseif($ride->way_point_status == 2){
                    $waypoint_msg_code = 273;
//                    $waypoint_message = "Driver Reached at Stop 2";
                    $waypoint_message = __('user_messages.273');
                }elseif($ride->way_point_status == 3){
                    $waypoint_msg_code = 274;
//                    $waypoint_message = "Driver Reached at Stop 3";
                    $waypoint_message = __('user_messages.274');
                }else{
                    $waypoint_msg_code = 0;
                    $waypoint_message = "";
                }

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
            if ($ride->vehicle_type_icon != Null) {
                $vehicle_type_icon = asset('assets/images/service-category/transport-service-type/' . $ride->vehicle_type_icon);
            } else {
                $vehicle_type_icon = "";
            }

            $ride_details = [
                "status" => 1,
//                "message" => "Success",
                "message" => __('user_messages.1'),
                "message_code" => 1,
                "ride_id" => $ride->id,
                'additional_remark' => "" . $ride->additional_request,
                "ride_service_category_id" => $ride->service_cat_id,
                "booking_type" => $ride->ride_type - 0,
                "booking_no" => $ride->ride_no . "",
                "pickup_date_time" => $ride->pickup_datetime,
                'service_date_time' => date('Y-m-d H:i:s',strtotime($ride->created_at)),
                "address_list" => $way_points,
                "ride_status" => $ride->status,
                "cancel_by" => $ride->cancel_by != Null ? $ride->cancel_by : "",
                "cancel_reason" => $ride->cancel_reason != Null ? $ride->cancel_reason : "",
                "service_type" => $ride->vehicle_type_name,
                "service_type_icon" => $vehicle_type_icon,
                "waypoint_msg_code" => $waypoint_msg_code,
                "waypoint_message" => $waypoint_message,
                'user_rating_status' => $ride->user_rating_status,
                'otp' => $ride->otp.'',
                "surcharge_cost" => $ride->surcharge_cost,
            ];

            if ($forTAD == true) {
                $driver_details = $this->findTransportAssignDriverDetails($ride->driver_id, true);
                if ($driver_details['status'] != 1) {
                    return response()->json($driver_details);
                }
                return response()->json(array_merge($ride_details, $driver_details));
            } elseif ($forTCI == true || $forTRD == true) {
                $driver_details = TransportDriverDetails::query()->select(
                    'transport_vehicle_type.cost_for_km',
                    'transport_vehicle_type.time_fare',
                    'provider_services.service_cat_id',
                    'transport_driver_details.driver_service',
                    'transport_driver_details.delivery_boy',
                    'transport_driver_details.courier_boy',
                    'transport_driver_details.vehicle_company',
                    'transport_driver_details.plat_no',
                    'transport_driver_details.model_year',
                    'transport_driver_details.model_name',
                    'transport_driver_details.vehicle_color',
                    'providers.id as driver_id'
                )
                    ->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
                    ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
                    ->join('transport_vehicle_type', 'transport_vehicle_type.id', '=', 'transport_driver_details.vehicle_type_id')
                    ->where('transport_driver_details.id', $ride->driver_id)
                    ->whereNull('providers.deleted_at')
                    ->first();
                if ($driver_details != Null) {
                    //$time_fare = $driver_details->time_fare;
                    $driver_service = $driver_details->driver_service;
                    $delivery_boy = $driver_details->delivery_boy;
                    $courier_boy = $driver_details->courier_boy;
                    $driver_id = $driver_details->driver_id;
                    if ($driver_details->service_cat_id == 1) {
                        $service = trim(($driver_service == 1 ? "Bike Riding" : '') . ($courier_boy == 1 ? ", Courier Delivery" : '') . ($delivery_boy == 1 ? ", Store Delivery" : ''), ', ');
                    } elseif ($driver_details->service_cat_id == 2) {
                        $service = "Taxi Riding";
                    } elseif ($driver_details->service_cat_id == 4) {
                        $service = "Courier Delivery";
                    } else {
                        $service = '';
                    }
                    $driver_fcm_token = $driver_details->driver_fcm_token != Null ? $driver_details->driver_fcm_token : '';
                    $vehicle_company = $driver_details->vehicle_company != Null ? $driver_details->vehicle_company : '';
                    $plat_no = $driver_details->plat_no != Null ? $driver_details->plat_no : '';
                    $model_year = $driver_details->model_year != Null ? $driver_details->model_year : '';
                    $model_name = $driver_details->model_name != Null ? $driver_details->model_name : '';
                    $vehicle_color = $driver_details->vehicle_color != Null ? $driver_details->vehicle_color : '';
                } else {

                    //$time_fare = 0;
                    $service = '';
                    $driver_id = 0;
                    $driver_fcm_token = '';
                    $vehicle_company = '';
                    $plat_no = '';
                    $model_year = '';
                    $model_name = '';
                    $vehicle_color = '';
                }
                $cost_per_km = $ride->vehicle_cost_for_km - 0;
                $time_fare = $ride->vehicle_cost_for_min - 0;
                $promocode_details = UsedPromocodeDetails::query()->where('id', $ride->promo_code)->first();

                $user_details = User::query()->where('id', $ride->user_id)->whereNull('users.deleted_at')->first();
                if ($user_details != Null) {
                    $user_currency = WorldCurrency::query()->where('symbol', $user_details->currency)->first();
                    if ($user_currency == Null) {
                        $user_currency = WorldCurrency::query()->where('default_currency', 1)->first();
                    }
                } else {
                    $user_currency = WorldCurrency::query()->where('default_currency', 1)->first();
                }

                $currency = $user_currency->ratio;

                $other_details = [
                    "driver_id" => $driver_id,
                    "driver_selected_service_cat_id" => $ride->service_cat_id,
                    "driver_selected_service_string" => $service,
                    "driver_fcm_token" => $driver_fcm_token,
                    "vehicle_manufacture_name" => $vehicle_company,
                    "vehicle_plat_no" => $plat_no,
                    "vehicle_model_year" => intval($model_year),
                    "vehicle_model_name" => $model_name,
                    "vehicle_color" => $vehicle_color,
                    "cost_per_km" => round( $ride->vehicle_cost_for_km * $currency, 2),
                    "base_fare" => round($ride->base_fare * $currency, 2),
                    "cost_per_min" => round($ride->vehicle_cost_for_min * $currency, 2),
                    "time_fare_amount" => round($ride->time_fare_amount * $currency, 2),

                    "total_distance" => $ride->total_distance,
                    "estimated_time" => $ride->eta,
                    "ride_fare" => round($ride->total_distance_amount * $currency, 2),
                    "refer_discount" => round($ride->refer_discount * $currency, 2),
                    "min_adjustment_amount" => round($ride->adjustment_amount * $currency, 2),
                    "promocode_discount" => $promocode_details != Null ? round($promocode_details->discount_amount * $currency, 2) : 0,
                    "promocode_name" => $promocode_details != Null ? $promocode_details->promocode_name : '',
                    "tax" => round($ride->tax * $currency, 2),
                    "tip" => round($ride->tip * $currency, 2),
                    "subtotal" => round(($ride->total_pay - $ride->tax) * $currency, 2),
                    "discount" => $promocode_details != Null ? round($promocode_details->discount_amount * $currency, 2) : 0,
                    "total_pay" => round($ride->total_pay * $currency, 2),
                ];
                if ($forTRD == true) {
                    $courier_details = Null;
                    if (in_array($ride->service_cat_id, $this->courier_service_id_array)) {
                        $courier_details = TransportCourierDetails::query()->select(
                            'house_name',
                            'recipient_name',
                            'recipient_contact_number',
                            'recipient_landmark',
                            'weight_start_limit',
                            'weight_close_limit',
                            'category_name',
                            'sender_name',
                            'sender_contact_number',
                            'sender_landmark',
                            'estimate_price',
                            'goods_height',
                            'goods_width',
                            'goods_weight',
                            'description',
                            DB::raw("(CASE WHEN delivery_instruction!='' THEN delivery_instruction ELSE '' END) as delivery_instruction")
                        )
                            ->where('ride_id', $ride->id)
                            ->first();
                    }
                    $add_other_details = [
                        "payment" => $ride->payment_type,
                        "payment_status" => $ride->payment_status,
                        "courier_details" => $courier_details
                    ];
                    $driver_details = $this->findTransportAssignDriverDetails($ride->driver_id, false);
                    if ($driver_details['status'] != 1) {
                        return response()->json($driver_details);
                    }
                    $other_details = array_merge(array_merge($other_details, $add_other_details));
                    $other_details = array_merge(array_merge($other_details, $driver_details));
                }
                return response()->json(array_merge(array_merge($ride_details, $other_details)));
            } else {
                return response()->json([
                    "status" => 0,
//                    "message" => "something went to wrong!",
                    "message" => __('user_messages.9'),
                    "message_code" => 9,
                ]);
            }
        } else {
            return response()->json([
                "status" => 0,
//                "message" => "Ride not found!",
                "message" => __('user_messages.26'),
                "message_code" => 26,
            ]);
        }
    }
    //transport service end


    //store service start
    public function storeList($store_list, $language, $currency, $currency_symbol){

        $time = Date('H:i:s');
        $get = config('global.lang_constant.GET.'.$language.'value') ." ";
        $off_on_all_orders = " ".config('global.lang_constant.OFF_ON_ALL_ORDERS.'.$language.'value')." " ;
        $off_on_orders_above = " ".config('global.lang_constant.OFF_ON_ORDERS_ABOVE.'.$language.'value')." " ;
        $store_data = [];
        foreach ($store_list as $key => $store_detail) {
            $product_cat_name =
                //array_unique(
                StoreProductDetails::query()
                    ->select('store_product_category.name')
                    ->join('store_product_category', 'store_product_category.id', '=', 'store_product_details.product_cate_id')
                    ->where('store_product_details.store_id', $store_detail->id)
                    ->where('store_product_category.status', 1)
                    ->where('store_product_details.status', 1)
                    ->groupBy("store_product_category.id")
                    ->take(3)
                    ->pluck('name')
                    ->toArray();
            //);
            $count_product_cat_name = count($product_cat_name);
            //$product_cat_count = StoreProductDetails::query()->where('store_id', $store_detail->id)->where('status', 1)->count();
            if ($count_product_cat_name > 0) {
                //if ($count_product_cat_name > 0 && $product_cat_count > 0) {
                $product_cat_name = implode(',', $product_cat_name);
                if ($time >= $store_detail->store_open_time && $time <= $store_detail->store_close_time && $store_detail->current_status == 1) {
                    $store_time_status = 1; //"open"
                    $open_time = "";
                } else {
                    $store_time_status = 0; //"close"
                    $open_time = date('H:i A', strtotime($store_detail->store_open_time));
                }
                $offer_amount = round($store_detail->offer_amount * $currency, 2);
                $offer_min_amount = round($store_detail->offer_min_amount * $currency, 2);

                if ($store_detail->offer_min_amount != 0 && $store_detail->offer_amount != 0) {
                    if ($store_detail->offer_type == 1) {
                        $offer = $get . $currency_symbol . $offer_amount . $off_on_orders_above . $currency_symbol . $offer_min_amount;
                    } else {
                        $offer = $get . $offer_amount . "% " . $off_on_orders_above . "" . $currency_symbol . $offer_min_amount;
                    }
                }
                elseif ($store_detail->offer_amount != 0 && $store_detail->offer_min_amount == 0) {
                    if ($store_detail->offer_type == 1) {
                        $offer = $get . $currency_symbol . $offer_amount . " " . $off_on_all_orders;
                    } else {
                        $offer = $get . $store_detail->offer_amount . "% " . $off_on_all_orders;
                    }
                }
                else {
                    $offer = "";
                }
                if ($store_detail->image != Null) {
                    $banner = url('/assets/images/store-images/' . $store_detail->image);
                } else {
                    $banner = "";
                }
                $store_data[] = [
                    "store_id" => $store_detail->id,
                    "store_name" => $store_detail->name,
                    "store_banner" => $banner,
                    "store_products" => $product_cat_name,
                    "average_ratings" => $store_detail->rating,
                    //"total_reviews" => 100,
                    //"store_open_time" => $open_time,
                    "offer" => $offer,
                    "order_delivery_time" => $store_detail->eta_delivery_time,
                    "order_min_amount" => round($store_detail->order_min_amount * $currency, 2),
                    "store_status" => $store_time_status,
                    //"distance" => $store_detail->distance
                ];
            }
        }
        $count_store = count($store_data);
        return response()->json([
            'count_store' => $count_store,
            "store_list" => $store_data,
        ]);
    }

    public function filterStoreList($lat_long, $filter_type, $filter_key, $service_category_id, $currency, $currency_symbol, $language = "en")
    {

        if ($lat_long == Null) {
            return response()->json([
                "status" => 0,
                "message" => __('user_messages.9'),
                "message_code" => 9,
            ]);
        }

        $selected_filter_key = explode(',', $filter_key);
        $selected_filter_int_key_array = [];
        $selected_filter_string_key_array = [];

        foreach ($selected_filter_key as $filter_key_return) {
            if (is_numeric($filter_key_return)) {
                $selected_filter_int_key_array[] = $filter_key_return - 0;
            } else {
                $selected_filter_string_key_array[] = $filter_key_return;
            }
        }

        $address_lat_long = array_map('trim', explode(",", $lat_long));
        if (count($address_lat_long) != 2) {
            return response()->json([
                "status" => 0,
//                "message" => "user address not found!",
                "message" => __('user_messages.9'),
                "message_code" => 9,
            ]);
        }
        $address_lat = $address_lat_long[0];
        $address_long = $address_lat_long[1];
        //$radius = 25;    //max radius this add in settings
        //$angle_radius = $radius / (111 * cos($address_lat)); //-0.23609747634791
        //$min_lat = $address_lat + $angle_radius; //22.059707223652
        //$max_lat = $address_lat - $angle_radius; //22.531902176348
        //$min_lon = $address_long + $angle_radius; //70.570694823652
        //$max_lon = $address_long - $angle_radius; //71.042889776348
        if ($filter_type != "search_store") {
            $store_details = StoreDetails::query()->select('store_details.id',
                'store_details.name', 'store_details.image',
                'store_details.offer_min_amount', 'store_details.offer_amount',
                'store_details.offer_type', 'store_details.order_min_amount',
                'store_details.rating', 'store_details.eta_delivery_time',
                'provider_services.current_status'
                , 'providers.service_radius'
                , DB::raw('(6367 * 2 * ASIN( SQRT( POWER( SIN(( ' . $address_lat . ' - store_details.address_lat) *  pi()/180 / 2), 2)
                +COS( ' . $address_lat . ' * pi()/180)
                * COS(store_details.address_lat * pi()/180)
                * POWER(SIN(( ' . $address_long . ' - store_details.address_long) * pi()/180 / 2), 2) ))) as distance')
            )
                ->join('provider_services', 'provider_services.id', '=', 'store_details.provider_service_id')
                ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
                ->where('provider_services.status', 1)
                ->where('provider_services.service_cat_id', $service_category_id)
                ->whereNull('providers.deleted_at')
                //->whereBetween('store_details.address_lat',  [$min_lat, $max_lat])
                //->whereBetween('store_details.address_long', [$min_lon, $max_lon])
                //->havingRaw('providers.service_radius > distance')
                ->orderByRaw("distance")
                ->get();
        } else {
            $store_details = StoreDetails::query()->select('store_details.id',
                'store_details.name', 'store_details.image',
                'store_details.offer_min_amount', 'store_details.offer_amount',
                'store_details.offer_type', 'store_details.order_min_amount',
                'store_details.rating', 'store_details.eta_delivery_time',
                'provider_services.current_status'
                , 'providers.service_radius'
                , DB::raw('(6367 * 2 * ASIN( SQRT( POWER( SIN(( ' . $address_lat . ' - store_details.address_lat) *  pi()/180 / 2), 2)
                +COS( ' . $address_lat . ' * pi()/180)
                * COS(store_details.address_lat * pi()/180)
                * POWER(SIN(( ' . $address_long . ' - store_details.address_long) * pi()/180 / 2), 2) ))) as distance')
            )
                ->join('provider_services', 'provider_services.id', '=', 'store_details.provider_service_id')
                ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
                ->where('provider_services.status', 1)
                ->where('provider_services.service_cat_id', $service_category_id)
                ->whereNull('providers.deleted_at')
                //->whereBetween('store_details.address_lat', [$min_lat, $max_lat])
                //->whereBetween('store_details.address_long', [$min_lon, $max_lon])
                ->where('store_details.name', 'LIKE', "%{$filter_key}%")
                //->havingRaw('providers.service_radius > distance')
                ->orderByRaw("distance")
                ->get();
        }
        if (!$store_details->isEmpty()) {
            if($language !="en" && $language != "" && $language != "Null" ){
                $language = $language."_";
            }else{
                $language ="";
            }
            $get = config('global.lang_constant.GET.'.$language.'value') ." ";
            $off_on_all_orders = " ".config('global.lang_constant.OFF_ON_ALL_ORDERS.'.$language.'value')." " ;
            $off_on_orders_above = " ".config('global.lang_constant.OFF_ON_ORDERS_ABOVE.'.$language.'value')." " ;

            $store_data = [];
            foreach ($store_details as $key => $store_detail) {
                $product_cat_name = array_unique(StoreProductDetails::query()->select('store_product_category.name')
                    ->join('store_product_category', 'store_product_category.id', '=', 'store_product_details.product_cate_id')
                    ->where('store_product_details.store_id', $store_detail->id)->where('store_product_category.status', 1)->where('store_product_details.status', 1)->get()->pluck('name')->toArray());
                $product_cat_count = StoreProductDetails::query()->where('store_id', $store_detail->id)->where('status', 1)->count();
                $count_product_cat_name = count($product_cat_name);
                if ($count_product_cat_name > 3) {
                    for ($n = 3; $n <= $count_product_cat_name; $n++) {
                        unset($product_cat_name[$n]);
                    }
                }
                $product_cat_name = implode(',', $product_cat_name);
                $store_time = StoreTimings::query()->select('store_timings.day', 'store_timings.store_open_time', 'store_timings.store_close_time')
                    ->where('store_id', $store_detail->id)
                    ->where('day', 'all')
                    //->orwhere('day', substr(date('l'), 0, 3))
                    ->first();
                if ($store_time == Null) {
                    $store_time = StoreTimings::query()->select('store_timings.day', 'store_timings.store_open_time', 'store_timings.store_close_time')
                        ->where('store_id', $store_detail->id)
                        //->where('day', 'all')
                        ->where('day', substr(date('l'), 0, 3))
                        ->first();
                }
                $open_time = "";
                $time = Date('H:i:s');
                if ($count_product_cat_name > 0 && $product_cat_count > 0) {
                    $open_time = "";
                    //store open status find (not for demo)
                    if ($store_time != Null) {
                        if ($time >= $store_time->store_open_time && $time <= $store_time->store_close_time && $store_detail->current_status == 1) {
                            $store_time_status = 1; //"open"
                            $open_time = "";
                        } else {
                            $store_time_status = 0; //"close"
                            $open_time = date('H:i A', strtotime($store_time->store_open_time));
                        }
                    } else {
                        $store_time_status = 0; //"close"
                        //$open_time = date('H:i A', strtotime($store_time->store_open_time));
                    }
                    //$store_time_status = 1;
                    $offer_amount = round($store_detail->offer_amount * $currency, 2);
                    $offer_min_amount = round($store_detail->offer_min_amount * $currency, 2);




                    if ($store_detail->offer_min_amount != 0 && $store_detail->offer_amount != 0) {
                        if ($store_detail->offer_type == 1) {
                            /*if ($language == "es") {
                                $offer = "Obtén ".$currency_symbol. " " . $offer_amount . " de descuento en compras superiores a ".$currency_symbol. " " . $offer_min_amount;
                            } else {
                                $offer = "Get " . $currency_symbol . $offer_amount . " off on orders above " . $currency_symbol . $offer_min_amount;
                            }*/
                            $offer = $get . $currency_symbol . $offer_amount . $off_on_orders_above . $currency_symbol . $offer_min_amount;
                        } else {
                            /*if ($language == "es") {
                                $offer = "Obtén el " . $offer_amount . "% de descuento en compras superiores a " . $currency_symbol . $offer_min_amount;
                            } else {
                                $offer = "Get " . $offer_amount . "% off on orders above " . $currency_symbol . $offer_min_amount;
                            }*/
                            $offer = $get . $offer_amount . "% ".$off_on_orders_above."". $currency_symbol . $offer_min_amount;
                        }
                    } elseif ($store_detail->offer_amount != 0 && $store_detail->offer_min_amount == 0) {
                        if ($store_detail->offer_type == 1) {
                            $offer = $get. $currency_symbol . $offer_amount ." " .$off_on_all_orders;
                        } else {
                            $offer = $get .$store_detail->offer_amount . "% ".$off_on_all_orders;
                        }
                    } else {
                        $offer = "";
                    }
                    if ($store_detail->image != Null) {
                        $banner = url('/assets/images/store-images/' . $store_detail->image);
                    } else {
                        $banner = "";
                    }
                    $store_data[] = [
                        "store_id" => $store_detail->id,
                        "store_name" => $store_detail->name,
                        "store_banner" => $banner,
                        "store_products" => $product_cat_name,
                        "average_ratings" => $store_detail->rating,
                        "total_reviews" => 100,
                        "store_open_time" => $open_time,
                        "offer" => $offer,
                        "order_delivery_time" => $store_detail->eta_delivery_time,
                        "order_min_amount" => round($store_detail->order_min_amount * $currency, 2),
                        "store_status" => $store_time_status,
                        "distance" => $store_detail->distance
                    ];
                }
            }
            if ($filter_type == "relevance") {
                if ($filter_key == "rating") {
                    usort($store_data, function ($a, $b) {
                        if ($a['average_ratings'] == $b['average_ratings']) return 0;
                        return $a['average_ratings'] < $b['average_ratings'] ? 1 : -1;
                    });
                } elseif ($filter_key == "delivery_time") {
                    usort($store_data, function ($a, $b) {
                        //return strcmp($a['order_delivery_time'], $b['order_delivery_time']);
                        if ($a['order_delivery_time'] == $b['order_delivery_time']) return 0;
                        return $a['order_delivery_time'] < $b['order_delivery_time'] ? -1 : 1;
                    });
                } elseif ($filter_key == "cost_low_to_high") {
                    usort($store_data, function ($a, $b) {
                        if ($a['order_min_amount'] == $b['order_min_amount']) return 0;
                        return $a['order_min_amount'] < $b['order_min_amount'] ? -1 : 1;
                    });
                } elseif ($filter_key == "cost_high_to_low") {
                    usort($store_data, function ($a, $b) {
                        if ($a['order_min_amount'] == $b['order_min_amount']) return 0;
                        return $a['order_min_amount'] < $b['order_min_amount'] ? 1 : -1;
                    });
                } else {
                    return response()->json([
                        'status' => 0,
                        'message' => __('user_messages.9'),
                        "message_code" => 9,
                    ]);
                }
            }
            if ($filter_type == "filter") {
                $filter_key = array_map('trim', explode(",", $filter_key));
                if (in_array("open_now", $filter_key)) {
                    $key_open_now = array_search("open_now", $filter_key);
                    unset($filter_key[$key_open_now]);
                    foreach ($store_data as $n => $data) {
                        if ($data["store_status"] != 1) {
                            unset($store_data[$n]);
                        }
                    }
                    if ($store_data == Null) {
                        return response()->json([
                            'status' => 0,
//                            'message' => 'Not Found Any Open Store!',
                            'message' => __('user_messages.61'),
                            "message_code" => 61,
                        ]);
                    }
                }
                if (in_array("with_offers", $filter_key)) {
                    $key_open_now = array_search("with_offers", $filter_key);
                    unset($filter_key[$key_open_now]);
                    foreach ($store_data as $n => $data) {
                        //if ($data["offer_min_amount"] == 0 && $data["offer_amount"] == 0) {
                        //    unset($store_data[$n]);
                        //}
                        if ($data["offer"] == Null) {
                            unset($store_data[$n]);
                        }
                    }
                    if ($store_data == Null) {
                        return response()->json([
                            'status' => 0,
                            'message' => 'Not Found Offers!',
                            "message_code" => 62,
                        ]);
                    }
                }
                if ($filter_key != Null) {
                    foreach ($store_data as $n => $data) {
                        $count = 0;
                        foreach ($filter_key as $value) {
                            $product_category = StoreProductDetails::query()->where('store_product_details.store_id', $data["store_id"])
                                ->where('store_product_details.product_cate_id', $value)
                                ->where('store_product_details.status', 1)
                                ->first();
                            if ($product_category != Null) {
                                $count = $count + 1;
                            }
                        }
                        if ($count == 0) {
                            unset($store_data[$n]);
                        }
                    }
                }
                if ($store_data == Null) {
                    return response()->json([
                        'status' => 0,
                        'message' => 'Store Category Not Found!',
                        "message_code" => 63,
                    ]);
                }

                $new_store_data = [];
                foreach ($store_data as $data) {
                    $new_store_data[] = $data;
                }
                $store_data = $new_store_data;
            }
            $count_store = count($store_data);

            $store_ids = (array_column($store_data, 'store_id'));
            //$icon_url = url('/assets/images/store-images/product-images');
            $product_category_list = StoreProductDetails::query()
                ->select('store_product_category.id as category_id', 'store_product_category.name as category_name'
                /*,DB::raw("(CASE WHEN store_product_category.icon != '' THEN (concat('$icon_url','/',store_product_category.icon)) ELSE '' END) as category_icon")*/
                )
                ->join('store_product_category', 'store_product_category.id', '=', 'store_product_details.product_cate_id')
                ->whereIn('store_product_details.store_id', $store_ids)
                ->where('store_product_category.status', 1)->distinct()->get();

            //code for get service wise slide data

            $store_banner_path = url('/assets/images/service-slider-banner/');
            $store_details = ServiceSliderBanner::query()->select('service_slider_banner.id','service_slider_banner.store_id','service_slider_banner.service_cat_id',
                'store_details.name as store_name','store_details.rating as average_ratings','store_details.eta_delivery_time',
                'store_details.offer_min_amount', 'store_details.offer_amount',
                'store_details.offer_type', 'store_details.order_min_amount',
                DB::raw("(CASE WHEN service_slider_banner.banner_image != '' THEN (concat('$store_banner_path','/',service_slider_banner.banner_image)) ELSE '' END) as store_image"),
                DB::raw('(6367 * 2 * ASIN( SQRT( POWER( SIN(( ' . $address_lat . ' - store_details.address_lat) *  pi()/180 / 2), 2)
                +COS( ' . $address_lat . ' * pi()/180)
                * COS(store_details.address_lat * pi()/180)
                * POWER(SIN(( ' . $address_long . ' - store_details.address_long) * pi()/180 / 2), 2) ))) as distance'),'providers.service_radius')
                ->join('store_details','store_details.id','=','service_slider_banner.store_id')
                ->join('provider_services', 'provider_services.id', '=', 'store_details.provider_service_id')
                ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
                ->where('service_slider_banner.status',1)
                ->where('providers.status',1)
                ->where('provider_services.status',1)
                ->where('service_slider_banner.service_cat_id','=',$service_category_id)
                //->havingRaw('providers.service_radius > distance')
                ->whereNull('providers.deleted_at')
                ->orderByRaw("distance DESC")
                ->get();
            $store_slider_data = [];
            foreach ($store_details as $keys => $store_detail) {
                $store_slider_data[$keys] = [
                    "id" => $store_detail->id,
                    "store_id" => $store_detail->store_id,
                    "service_category_id" => $store_detail->service_cat_id,
                    "store_name" => $store_detail->store_name,
                    "banner_image" => ($store_detail->store_image != "") ? $store_detail->store_image : "",

                ];
            }



            if ($count_store > 0) {
                return response()->json([
                    'status' => 1,
                    'message' => 'Success!',
                    "message_code" => 1,
                    'no_of_store' => $count_store,
                    'filter_int_key' => $selected_filter_int_key_array,
                    'filter_string_key' => $selected_filter_string_key_array,
                    'category_list' => $product_category_list,
                    'store_list' => $store_data,
                    'service_slider_data' => $store_slider_data
                ]);
            } else {
                return response()->json([
                    'status' => 0,
                    'message' => 'Store not found',
                    "message_code" => 71,
                ]);
            }
        } else {

            $dispmsg = "Store Not Found.";

            return response()->json([
                "status" => 0,
                "message" => $dispmsg,
                "message_code" => 122,
            ]);
        }
    }


    //site setting value get (pending , tax, delivery_charge)
    public function findStoreData($store_id, $currency, $currency_symbol,$language = "en", $filter_type = 0,$user_lat="",$user_long="",$user_id=0)
    {
        $store_details = StoreDetails::query()->select('store_details.id',
            'store_details.name',
            'store_details.image',
            'store_details.description',
            'store_details.eta_delivery_time',
            'store_details.order_min_amount',
            'store_details.offer_min_amount',
            'store_details.offer_amount',
            'store_details.offer_type',
            'store_details.taken_away_type',
            'store_details.address',
            'store_details.address_lat',
            'store_details.address_long',
            'store_details.packaging_charges',
            'store_details.rating as average_rating',
            'provider_services.current_status',
            'provider_services.service_cat_id',
            'service_category.is_sub_cat_flow')
            ->join('provider_services', 'provider_services.id', '=', 'store_details.provider_service_id')
            ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
            ->where('store_details.id', $store_id)->where('provider_services.status', 1)->first();
        if ($store_details != Null) {
            $is_sub_cat_flow = $store_details->is_sub_cat_flow - 0;
            $get_products = StoreProductDetails::query()->select('store_product_details.id',
                'store_product_details.image',
                'store_product_details.name',
                'store_product_details.food_type',
                'store_product_details.amount',
                'store_product_details.discount_amount',
                'store_product_details.discount_type',
                'store_product_details.description',
                'store_product_details.is_recommended',
                'store_product_category.name as category_name',
                'store_product_category.category_icon as category_icon',
                'store_product_category.parent_id as parent_category_id',
                //'store_product_category.icon as category_icon',
                'store_product_category.id as category_id')
                ->join('store_product_category', 'store_product_category.id', 'store_product_details.product_cate_id')
                ->where('store_product_details.store_id', $store_id)
                ->where('store_product_details.status', 1)
                ->where('store_product_category.status', 1);
            //1=veg,2=non-veg, 0=not required
            if (in_array($filter_type, [1, 2])) {
                $get_products->where('store_product_details.food_type', $filter_type);
            }
            $get_products = $get_products->get();
            $store_product_categories = array_unique(Arr::pluck($get_products, 'category_name', 'category_id'));
            $store_product_parent_categories = array_unique(Arr::pluck($get_products, 'parent_category_id'));
            $get_products = $get_products->groupBy('category_id');
            //get store active category
            $get_active_products = StoreProductDetails::query()->select(
                'store_product_category.name as category_name',
                //'store_product_category.icon as category_icon',
                'store_product_category.id as category_id')
                ->join('store_product_category', 'store_product_category.id', 'store_product_details.product_cate_id')
                ->where('store_product_details.store_id', $store_id)
                ->where('store_product_details.status', 1)
                ->where('store_product_category.status', 1)->get();
            $store_active_product_categories = array_unique(Arr::pluck($get_active_products, 'category_name', 'category_id'));

            $category_product_data = [];
            $lang_qty = "Quantity";

            if($language !="en" && $language != "" && $language != "Null" ){
                $language = $language."_";
            }else{
                $language ="";
            }
            $get = config('global.lang_constant.GET.'.$language.'value') ." ";
            $off_on_all_orders = " ".config('global.lang_constant.OFF_ON_ALL_ORDERS.'.$language.'value')." " ;
            $off_on_orders_above = " ".config('global.lang_constant.OFF_ON_ORDERS_ABOVE.'.$language.'value')." " ;
            $recommened_title = config('global.lang_constant.RECOMMENDED.'.$language.'value') ;

            $product_limit = 2;
            $recommened_products = [];
            if ($is_sub_cat_flow == 1){
                if ($store_product_parent_categories != Null){
                    $category_image_path = url('/assets/images/store-images/category-images/');
                    $category_product_data = StoreProductCategory::query()
                        ->select('id as category_id', 'name as category_name',
                            DB::raw("(CASE WHEN store_product_category.category_icon != '' THEN (concat('$category_image_path','/',store_product_category.category_icon)) ELSE '' END) as category_icon"),
                            DB::raw("0 as product_count")
                        )
                        //->where('service_cat_id','=', $service_category->id)
                        ->where('parent_id', '=', 0)
                        ->whereIn('id', $store_product_parent_categories)
                        ->get()->toArray();
                }
            } else {
                foreach ($get_products as $key => $products) {
                    $count_product = count($products);
                    $get_count_product = count($products);
//                foreach ($products as $Pkey=>$product) {
//                    if ($Pkey >= $product_limit) {
//                        break;
//                    }
//                    $product_size_arr = [];
//                    $product_amount = $product->amount;
//                    $product_customize_arr = [];
//                    $product_size = StoreProductSize::query()->select('id', 'name', 'amount')
//                        ->where('product_id', $product->id)->orderBy('amount', 'asc')->get();
//                    foreach ($product_size as $size_key => $size) {
//                        if ($size_key == 0) {
//                            $product_amount = $size->amount;
//                        }
//                        if ($product->discount_type == 1) {
//                            $discount_price = round((($size->amount) - ($product->discount_amount)), 2);
//                        } elseif ($product->discount_type == 2) {
//                            $discount_price = round((($size->amount) - (($size->amount) * ($product->discount_amount)) / (100)), 2);
//                        } else {
//                            $discount_price = 0;
//                        }
//                        if ($discount_price != 0) {
//                            //$offer_calculation = round(($size->amount - $discount_price), 2);
//                            $amount_price = $discount_price;
//                            $discount_price = $size->amount;
//                        } else {
//                            $amount_price = $size->amount;
//                            //$offer_calculation = 0;
//                        }
//                        $product_size_arr[] = [
//                            "id" => $size->id,
//                            "name" => $size->name,
//                            "amount" => $amount_price,
//                            "discount_price" => $discount_price,
//                            "category_name" => $lang_qty,
//                            "selection_type" => 1,
//                        ];
//                    }
//                    if ($product->discount_type == 1) {
//                        $price_discount = round((($product_amount) - ($product->discount_amount)), 2);
//                        //$price_discount = $price_discount - 0;
//                    } elseif ($product->discount_type == 2) {
//                        $price_discount = round((($product_amount) - (($product_amount) * ($product->discount_amount)) / (100)), 2);
//                        //$price_discount = $price_discount - 0;
//                    } else {
//                        $price_discount = 0;
//                    }
//                    if ($price_discount != 0) {
//                        $offer_calculation = round(($product_amount - $price_discount), 2);
//                        //$offer_calculation = $offer_calculation - 0;
//                        $product_price = $price_discount;
//                        $price_discount = $product_amount;
//                    } else {
//                        $product_price = $product_amount;
//                        $offer_calculation = 0;
//                    }
//                    if ($product_size_arr != Null) {
//                        $product_customize_arr[] = [
//                            "category_name" => $lang_qty,
//                            "selection_type" => 1,
//                            'max_selection' => 0,
//                            "customize_type" => 1,
//                            "options" => $product_size_arr
//                        ];
//                    }
//                    $product_options = StoreProductOptions::query()->select('store_product_options.id',
//                        'store_product_options.name',
//                        'store_product_options.amount',
//                        'store_product_customize_category.name as category_name',
//                        'store_product_customize_category.selection_type')
//                        ->join('store_product_customize_category', 'store_product_customize_category.id', '=', 'store_product_options.category_id')
//                        ->where('store_product_options.product_id', $product->id)->get();
//                    $product_options_category = $product_options->pluck('selection_type', 'category_name')->toArray();
//                    $product_options = $product_options->groupBy('category_name')->toArray();
//                    //$product_options_arr = [];
//                    foreach ($product_options as $option_key => $option) {
//                        $product_customize_arr[] = [
//                            'category_name' => $option_key,
//                            'selection_type' => $product_options_category[$option_key],
//                            'max_selection' => 0,
//                            "customize_type" => 2,
//                            'options' => $option
//                        ];
//                    }
//                    $product_toppings = StoreProductToppings::query()->select('store_product_toppings.id',
//                        'store_product_toppings.name',
//                        'store_product_toppings.amount',
//                        'store_product_customize_category.name as category_name',
//                        'store_product_customize_category.selection_type', 'store_product_customize_category.max_selection')
//                        ->join('store_product_customize_category', 'store_product_customize_category.id', '=', 'store_product_toppings.category_id')
//                        ->where('store_product_toppings.product_id', $product->id)->get();
//
//                    $product_toppings_category = $product_toppings->pluck('selection_type', 'category_name')->toArray();
//                    $product_toppings_max_selection = $product_toppings->pluck('max_selection', 'category_name')->toArray();
//                    $product_toppings = $product_toppings->groupBy('category_name')->toArray();
//                    //$product_toppings_arr = [];
//                    foreach ($product_toppings as $topping_key => $topping) {
//                        $product_customize_arr[] = [
//                            'category_name' => $topping_key,
//                            'selection_type' => $product_toppings_category[$topping_key],
//                            'max_selection' => $product_toppings_max_selection[$topping_key],
//                            "customize_type" => 3,
//                            'options' => $topping
//                        ];
//                    }
//
//                    $product_data[$key][] = [
//                        "product_id" => $product->id,
//                        "product_image" => $product->image != Null ? url('/assets/images/store-images/product-images/' . $product->image) : '',
//                        "product_name" => $product->name,
//                        "food_type" => $product->food_type,
//                        "product_amount" => round($product_price * $currency, 2),
//                        "discount_amount" => round($price_discount * $currency, 2),
//                        "offer_discount" => round($offer_calculation * $currency, 2),
//                        "product_discount_type_for_calculation" => $product->discount_type,
//                        "product_discount_amount_for_calculation" => $product->discount_amount,
//                        "description" => $product->description ? $product->description : '',
//                        "customize_list" => $product_customize_arr
//                    ];
//                    if($product->is_recommended == 1 )
//                    {
//                        $recommened_products[] = [
//                            "product_id" => $product->id,
//                            "product_image" => $product->image != Null ? url('/assets/images/store-images/product-images/' . $product->image) : '',
//                            "product_name" => $product->name,
//                            "food_type" => $product->food_type,
//                            "product_amount" => round($product_price * $currency, 2),
//                            "discount_amount" => round($price_discount * $currency, 2),
//                            "offer_discount" => round($offer_calculation * $currency, 2),
//                            "product_discount_type_for_calculation" => $product->discount_type,
//                            "product_discount_amount_for_calculation" => $product->discount_amount,
//                            "description" => $product->description ? $product->description : '',
//                            "customize_list" => $product_customize_arr
//                        ];
//                    }
//
//                    //$count_product = $count_product + 1;
//                }
                    $category_product_data[] = [
                        "category_id" => $key,
                        "category_name" => $store_product_categories[$key],
                        "category_icon" => "",
                        "product_count" => $count_product,
//                    "is_show_more" => $get_count_product > $product_limit ? true: false,
//                    "product_list" => $product_data[$key]
                    ];
                }
            }

            if ($is_sub_cat_flow == 0) {
                //get all recomended category
                $recommened_products = StoreProductDetails::query()->select('store_product_details.id',
                    'store_product_details.image',
                    'store_product_details.name',
                    'store_product_details.food_type',
                    'store_product_details.amount',
                    'store_product_details.discount_amount',
                    'store_product_details.discount_type',
                    'store_product_details.description',
                    'store_product_details.is_recommended',
                    'store_product_category.name as category_name',
                    //'store_product_category.icon as category_icon',
                    'store_product_category.id as category_id')
                    ->join('store_product_category', 'store_product_category.id', 'store_product_details.product_cate_id')
                    ->where('store_product_details.store_id', $store_id)
                    ->where('store_product_details.status', 1)
                    ->where('store_product_details.is_recommended', 1)
                    ->where('store_product_category.status', 1);
                //1=veg,2=non-veg, 0=not required
                if (in_array($filter_type, [1, 2])) {
                    $recommened_products->where('store_product_details.food_type', $filter_type);
                }
                $recommened_products = $recommened_products->get();
                $total_recommended_products = count($recommened_products);
                if ($recommened_products != Null && $total_recommended_products) {
                    $category_product_data[] = [
                        "category_id" => 0,
                        "category_name" => $recommened_title,
                        "product_count" => $total_recommended_products,
                        //"is_show_more" => ($total_recommended_products > $product_limit) ? true: false,
                    ];
                }
            }

            $store_time = StoreTimings::query()->select('store_timings.day', 'store_timings.store_open_time', 'store_timings.store_close_time')
                ->where('store_id', $store_details->id)
                ->where('day', 'all')
                ->first();

            if ($store_time == Null) {
                $store_time = StoreTimings::query()->select('store_timings.day', 'store_timings.store_open_time', 'store_timings.store_close_time')
                    ->where('store_id', $store_details->id)
                    //->where('day', 'all')
                    ->where('day', substr(date('l'), 0, 3))
                    ->first();

            }

            $time = Date('H:i:s');
            //store open status find (not for demo)
            if ($store_time != Null) {
                if ($time >= $store_time->store_open_time && $time <= $store_time->store_close_time && $store_details->current_status == 1) {
                    $store_time_status = 1; //"open"
                } else {
                    $store_time_status = 0; //"close"
                }
            } else {
                $store_time_status = 0; //"close"
            }
            //$store_time_status = 1;
            $offer_amount = round($store_details->offer_amount * $currency, 2);
            $offer_min_amount = round($store_details->offer_min_amount * $currency, 2);

            if ($store_details->offer_min_amount != 0 && $store_details->offer_amount != 0) {
                if ($store_details->offer_type == 1) {
                    $offer = $get . $currency_symbol . $offer_amount . $off_on_orders_above . $currency_symbol . $offer_min_amount;
                } else {
                    $offer = $get . $offer_amount . "% ".$off_on_orders_above . $currency_symbol . $offer_min_amount;
                }
            } elseif ($store_details->offer_amount != 0 && $store_details->offer_min_amount == 0) {
                $offer = $get. $store_details->offer_amount . "% ".$off_on_all_orders;

            } else {
                $offer = "";
            }
            $service_settings = ServiceSettings::query()->where('service_cat_id', $store_details->service_cat_id)->first();
            if ($service_settings != Null) {
                $tax = $service_settings->tax != Null ? $service_settings->tax : 0;
                $delivery_charge = $service_settings->delivery_charge != Null ? $service_settings->delivery_charge : 0;
            } else {
                $tax = 0;
                $delivery_charge = 0;
            }
            //get user to store distance
            $store_lat = $store_details->address_lat;
            $store_long = $store_details->address_long;
            $user_to_store_dist = 0;
            if($store_lat != "" && $store_long !="" && $user_lat !="" && $user_long !=""){
                $user_address = urlencode($user_lat.','.$user_long);
                $origin = urlencode($store_lat . "," . $store_long);
                $user_to_store_dist = 0;
                $get_coordinates = $this->get_coordinates($origin, $user_address);
                $coordinates_check = json_decode($get_coordinates, TRUE);
                if (count($coordinates_check['routes']) > 0) {
                    $total_distance = preg_replace("/[^0-9.]/", "", $coordinates_check['routes'][0]['legs'][0]['distance']['value']);
                    $user_to_store_dist =number_format( ($total_distance * 0.001),2) - 0;

                }
            }
            //get store open close data
            $store_open_close_time_data = StoreTimings::query()->select('store_timings.id','store_timings.day',
                DB::raw('store_timings.day as display_day'),
                'store_timings.store_open_time',
                'store_timings.store_close_time')
                ->where('store_id', $store_details->id)
                ->orderBy('id','asc')
                ->get();
            $store_open_close_time = [];

            if($store_open_close_time_data != Null){
                foreach ($store_open_close_time_data as $key=>$single_day)
                {
                    $store_open_close_time[$key] = [
                        'id' => $single_day->id,
                        'day_seq' => date('N', strtotime($single_day->day))-0,
                        'day' =>   $single_day->day ,
                        'display_day' =>   config('global.lang_constant.'.$single_day->day.".".$language.'value') ,
                        'store_open_time' =>  $single_day->store_open_time ,
                        'store_close_time' =>  $single_day->store_close_time ,
                    ];
                }
            }
            $sort_day = array_column($store_open_close_time, 'day_seq');
            array_multisort($sort_day, SORT_ASC, $store_open_close_time);



            $keys = array_column($category_product_data, 'category_id');
            array_multisort($keys, SORT_ASC, $category_product_data);

            //check store for store is fav or not
            $is_fav_store = 0;

            if($user_id > 0){
                $check_fav_store = UserFavouriteStore::query()->where('user_id','=',$user_id)
                    ->where('store_id','=',$store_id)->first();
                if($check_fav_store != Null){
                    $is_fav_store = 1;
                }
            }

            return response()->json([
                'status' => 1,
//                'message' => 'Success!',
                'message' => __('user_messages.1'),
                'is_subcat_flow' => $is_sub_cat_flow,
                'store_id' => $store_details->id,
                'store_name' => $store_details->name,
                'store_banner' => $store_details->image != Null ? url('/assets/images/store-images/' . $store_details->image) : '',
                'store_status' => $store_time_status,
                'open_time' => $store_time != Null ? date('H:i', strtotime($store_time->store_open_time)) : '',
                'close_time' => $store_time != Null ? date('H:i', strtotime($store_time->store_close_time)) : '',
                'average_rating' => $store_details->average_rating,
                'product_category' => implode(',', $store_active_product_categories),
                'description' => $store_details->description != Null ? $store_details->description : '',
                'delivery_time' => $store_details->eta_delivery_time,
                'order_min_amount' => round($store_details->order_min_amount * $currency, 2),
                'offer' => $offer,
                'offer_min_amount' => round($store_details->offer_min_amount * $currency, 2),
                'offer_amount' => round($store_details->offer_amount * $currency, 2),
                'offer_type' => round($store_details->offer_type * $currency, 2),
                'offer_percentage' => round($store_details->offer_amount * $currency, 2),
                'is_fav_store' =>$is_fav_store,
                "taken_away_type"=>$store_details->taken_away_type-0,
                'address' => $store_details->address,
                'latitude' => $store_details->address_lat,
                'longitude' => $store_details->address_long,
                'packaging_charges' => round($store_details->packaging_charges * $currency, 2),
                'tax_in_percentage' => round($tax * $currency, 2),
                'delivery_charges' => round($delivery_charge * $currency, 2),
                'user_to_store_dist' =>$user_to_store_dist,
                'store_open_close_time' =>$store_open_close_time,
                'category_wise_product_list' =>$category_product_data,
                'message_code' => 1,
            ]);
        } else {
            return response()->json([
                'status' => 0,
//                'message' => 'Store Not Found!',
                'message' => __('user_messages.71'),
                "message_code" => 71,
            ]);
        }
    }

    public function findStoreOrderDetails($order_id, $currency)
    {

        $order = UserProductBooking::query()->where('id', $order_id)->first();
        if ($order != Null) {
            $get_user_lang = User::query()->select('language')->where('id', $order->user_id)->whereNull('users.deleted_at')->first();
            $lang_prefix = "";
            if ($get_user_lang != "") {
                if ($get_user_lang->language != "en") {
                    $lang_prefix = $get_user_lang->language."_";
                }else {
                    $lang_prefix = "";
                }
            }
            $service_category = ServiceCategory::query()->select('id', $lang_prefix . 'name as category_name', 'icon_name as category_icon')->where('id', $order->service_cat_id)->first();
            if ($service_category == Null) {
                return response()->json([
                    'status' => 0,
//                    'message' => "something went to wrong!",
                    'message' => __('user_messages.9'),
                    "message_code" => 9,
                ]);
            }
            $store_details = StoreDetails::query()->where('id', $order->store_id)->first();
            if ($order == Null) {
                return response()->json([
                    'status' => 0,
//                    'message' => "something went to wrong!",
                    'message' => __('user_messages.9'),
                    "message_code" => 9,
                ]);
            }
            $product_list = UserProductBookingQuantity::query()->select('id','product_id as product_id','product_name',
                'num_of_items as num_of_items','food_type as food_type',
                DB::raw('ROUND(price_for_one,2) As product_price_for_one'))
                ->where('order_id', $order->id)->get();
            if ($product_list->isEmpty()) {
                return response()->json([
                    'status' => 0,
//                    'message' => "something went to wrong!",
                    'message' => __('user_messages.9'),
                    "message_code" => 9,
                ]);
            }
            $order_product_list = [];
            foreach ($product_list as $product) {
                $size = UserProductBookingSize::query()->where('order_id', $order->id)->where('product_id', $product->id)->get()->pluck('name')->toArray();
                $options = UserProductBookingOptions::query()->where('order_id', $order->id)->where('product_id', $product->id)->get()->pluck('name')->toArray();
                $toppings = UserProductBookingToppings::query()->where('order_id', $order->id)->where('product_id', $product->id)->get()->pluck('name')->toArray();
                $add_ons = array_merge($size, $options, $toppings);
                $add_ons = implode(', ', $add_ons);
                $order_product_list[] = [
                    "product_name" => $product->product_name,
                    "food_type" => $product->food_type,
                    "product_quantity" => $product->num_of_items,
                    "product_price" => round((($product->product_price_for_one) * $currency), 2),
                    "product_price_for_one" => round((($product->product_price_for_one * $product->num_of_items) * $currency), 2),
                    //"amount" => round(($product->product_price_for_one) * ($product->num_of_items), 2),
                    "add_ons" => $add_ons
                ];
            }

            if ($order->promo_code != 0) {
                try {
                    $get_discount = UsedPromocodeDetails::query()->where('id', $order->promo_code)->first();
                    $promo_code_discount = $get_discount->discount_amount;
                    $promocode_name = $get_discount->promocode_name;
                } catch (\Exception $e) {
                    $promo_code_discount = round($order->total_item_cost - $order->discount_cost + $order->tax_cost + $order->delivery_cost + $order->packaging_cost - $order->total_pay, 2);
                    $promocode_name = '';
                }
            } else {
                $promo_code_discount = 0;
                $promocode_name = '';
            }
            $find_store = StoreDetails::query()->select('providers.id','providers.country_code', 'providers.contact_number')
                ->join('provider_services', 'provider_services.id', '=', 'store_details.provider_service_id')
                ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
                ->where('store_details.id', $order->store_id)->first();

            try {
                $delivery_person_details = TransportDriverDetails::query()->select('providers.country_code', 'providers.contact_number', 'providers.avatar',
                    'transport_driver_details.rating','transport_driver_details.driver_service', 'transport_driver_details.delivery_boy',
                    'transport_driver_details.courier_boy', 'transport_driver_details.rental_service','provider_services.service_cat_id',
                    'providers.language','providers.device_token','providers.id as driver_id',DB::raw("CONCAT(providers.first_name,'') as driver_name"),'transport_driver_details.rating')
                    ->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
                    ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
                    ->where('transport_driver_details.id', $order->delivery_people_id)
                    ->first();

                if ($delivery_person_details != Null) {
                    if ($delivery_person_details->avatar != Null) {
                        if (filter_var($delivery_person_details->avatar, FILTER_VALIDATE_URL) == true) {
                            $delivery_person_image = $delivery_person_details->avatar;
                        } else {
                            $delivery_person_image = url('/assets/images/profile-images/provider/' . $delivery_person_details->avatar);
                        }
                    } else {
                        $delivery_person_image = "";
                    }
                    $delivery_person_contact_no = $delivery_person_details->country_code.$delivery_person_details->contact_number;
                    $delivery_person_rating = $delivery_person_details->rating;
                    $delivery_person_name = $delivery_person_details->driver_name;

                    $driver_service = $delivery_person_details->driver_service;
                    $delivery_boy = $delivery_person_details->delivery_boy;
                    $courier_boy = $delivery_person_details->courier_boy;
                    /*$service_lang_converted = $this->notificationClass->serviceLangConvert();*/
                    $lang = $delivery_person_details->language;

                    if($lang !="en" && $lang != "" && $lang != "Null" ){
                        $lang = $lang."_";
                    }else{
                        $lang ="";
                    }
                    $ride_service =  (config('global.lang_constant.RIDE_SERVICE.'.$lang.'value') !="")?config('global.lang_constant.RIDE_SERVICE.'.$lang.'value') ." ":config('global.lang_constant.RIDE_SERVICE.value')." ";
                    $courier_delivery = (config('global.lang_constant.COURIER_DELIVERY.'.$lang.'value') !="")?config('global.lang_constant.COURIER_DELIVERY.'.$lang.'value')." ":config('global.lang_constant.COURIER_DELIVERY.value')." " ;
                    $store_delivery = (config('global.lang_constant.STORE_DELIVERY.'.$lang.'value') !="")?config('global.lang_constant.STORE_DELIVERY.'.$lang.'value')." ":config('global.lang_constant.STORE_DELIVERY.value')." " ;
                    $rental_service = (config('global.lang_constant.RENTAL_SERVICE.'.$lang.'value')!="")?config('global.lang_constant.RENTAL_SERVICE.'.$lang.'value')." ":config('global.lang_constant.RENTAL_SERVICE.value')." " ;

                    if ($delivery_person_details->service_cat_id == 1) {
                        //$service = trim(($driver_service == 1 ? $service_lang_converted['Ride Service'][$lang] : '') . ($courier_boy == 1 ? ", ".$service_lang_converted['Courier Delivery'][$lang] : '') . ($delivery_boy == 1 ? ", ".$service_lang_converted['Store Delivery'][$lang] : ''), ', ');
                        $service = trim(($driver_service == 1 ? $ride_service : '') . ($courier_boy == 1 ? ", ".$courier_delivery : '') . ($delivery_boy == 1 ? ", ".$store_delivery : '') . ($delivery_person_details->rental_service == 1 ? ", ".$rental_service : ''), ', ');
                    } elseif ($delivery_person_details->service_cat_id == 2) {
                        $service = trim(($driver_service == 1 ? $ride_service : '') . ($courier_boy == 1 ? ", ".$courier_delivery : '') . ($delivery_boy == 1 ?  ", ".$store_delivery : '') . ($delivery_person_details->rental_service == 1 ? ", ".$rental_service : ''), ', ');
                    } elseif ($delivery_person_details->service_cat_id == 4) {
                        $service = $courier_delivery;
                    } else {
                        $service = '';
                    }

                    $delivery_person_device_token = $delivery_person_details->device_token;
                    $driver_id = $delivery_person_details->driver_id;
                } else {
                    $delivery_person_image = "";
                    $delivery_person_contact_no = "";
                    $service = '';
                    $delivery_person_device_token = '';
                    $driver_id = 0;
                    $delivery_person_rating = 0;
                    $delivery_person_name = '';

                }
            } catch (\Exception $e) {
                $delivery_person_image = "";
                $delivery_person_contact_no = "";
                $service = '';
                $delivery_person_device_token = '';
                $driver_id = 0;
                $delivery_person_rating = 0;
                $delivery_person_name = '';
            }

            return response()->json([
                'status' => 1,
//                'message' => "success!",
                'message' => __('user_messages.1'),
                "message_code" => 1,
                'order_id' => $order->id,
                'order_no' => trim($order->order_no),
                'otp' => $order->otp.'',
                'additional_remark' => "" . $order->additional_request,
                'allow_reorder' => $order->reorder_type ,
                'category_name' => $service_category->category_name,
                'category_icon' => url('/assets/images/service-category/' . $service_category->category_icon),
                'cancel_by' => $order->cancel_by != Null ? $order->cancel_by : '',
                'cancel_reason' => $order->cancel_reason != Null ? $order->cancel_reason : '',
                'cancel_charge' => ($order->status == 4) ? ($order->payment_type == 2 || $order->payment_type == 3) ? ($order->cancel_charge != Null) ? $order->cancel_charge : 0 : 0 : 0,
                'store_id' => $find_store != Null ? $find_store->id : 0,
                'store_contact_no' => $find_store != Null ? $find_store->country_code.$find_store->contact_number : '',
                'store_name' => $order->store_name,
                'store_image' => $store_details != Null && $store_details->image != Null ? url('/assets/images/store-images/' . $store_details->image) : '',
                'store_average_ratings' => $store_details != Null && $store_details->rating != Null ? $store_details->rating : 0.00,
                'delivery_address' => $order->delivery_address,
                "flat_no" => $order->flat_no != Null ? $order->flat_no : '',
                "landmark" => $order->landmark != Null ? $order->landmark : '',
                'service_date_time' => date('Y-m-d H:i:s',strtotime($order->created_at)),
                'schedule_order_date_time' => $order->order_date_time,
                'delivery_people_name' => $order->delivery_people_name != Null ? $order->delivery_people_name : '',
                'total_item_cost' => round($order->total_item_cost * $currency, 2),
                'discount_cost' => round($order->discount_cost * $currency, 2),
                'tax_cost' => round($order->tax_cost * $currency, 2),
                'tip' => round($order->tip * $currency, 2),
                'refer_discount' => round($order->refer_discount * $currency, 2),
                'delivery_cost' => round(($order->delivery_cost) * $currency, 2),
                'packaging_cost' => round($order->packaging_cost * $currency, 2),
                'total_pay' => round($order->total_pay * $currency, 2),
                'payment_type' => $order->payment_type,
                'order_status' => $order->status,
                'user_rating_status' => $order->user_rating_status,
                'user_taken_type' => $order->taken_away_type -0,
                'prescription' => $order->prescription != Null ? url('/assets/images/prescription-images/' . $order->prescription) : "",
                'promo_code_discount' => round($promo_code_discount * $currency, 2),
                'used_promo_code_name' => $promocode_name,
                "delivery_person_image" => $delivery_person_image,
                "delivery_person_contact_no" => $delivery_person_contact_no,
                "driver_selected_service_string" => $service,
                "driver_fcm_token" => $delivery_person_device_token,
                "driver_id" => $driver_id,
                "delivery_person_rating" => $delivery_person_rating,
                "delivery_person_name" => $delivery_person_name,
                'product_list' => $order_product_list

            ]);
        } else {
            return response()->json([
                'status' => 0,
//                'message' => "order not found!",
                'message' => __('user_messages.59'),
                "message_code" => 59,
            ]);
        }
    }

    public function ReferralApply($admin_referal_type=0,$admin_referral_amount=0,$single_store_item_cost=0,$all_store_item_total=0){
        //admin_promo_type : 1: Amount, 2: Percentage
        $referral_discount_amount = 0;
        if($admin_referal_type > 0 && $admin_referral_amount > 0 ){
            if($admin_referal_type == 1){
                //code for amount wise discount
                $referral_discount_amount= $admin_referral_amount;

            }else{
                //code for percentage wise discount
                $referral_discount_amount = ($single_store_item_cost*$admin_referral_amount)/100;
            }
        }
        return ($referral_discount_amount >= 0 )?$referral_discount_amount:0;
    }

    public function checkPromoCodeValid($promo_id, $amount, $user_id, $service_cat_id)
    {
        $current_date = date('y-m-d h:i:s');
        $get_promocode = PromocodeDetails::query()->where('id', $promo_id)->where('service_cat_id', $service_cat_id)->where('expiry_date_time', '>=', $current_date)->where('status', 1)->first();
        if ($get_promocode != Null) {
            try {
                $get_user_used = UsedPromocodeDetails::query()->where('user_id', $user_id)->where('promocode_id', $promo_id)->whereIn("status",[0,1])->count();
                $get_user_detail = User::query()->select('currency')->where('id','=',$user_id)->whereNull('users.deleted_at')->first();
                $user_currency = "";
                $currency_ratio = 1;
                if($get_user_detail != Null){
                    $user_currency = ($get_user_detail->currency != Null) ? $get_user_detail->currency : "$";

                    $world_currency = WorldCurrency::query()->where('symbol', $user_currency)->first();
                    if ($world_currency == Null) {
                        $world_currency = WorldCurrency::query()->where('default_currency', 1)->first();
                    }
                    $currency_ratio = $world_currency->ratio;
                }
            } catch (\Exception $e) {
                $get_user_used = 0;
            }
            if ($get_promocode->coupon_limit != 0) {
                if ($get_promocode->coupon_limit > $get_promocode->total_usage) {
                    if ($get_promocode->usage_limit > $get_user_used) {
                        if ($get_promocode->min_order_amount != Null) {
                            if ($get_promocode->min_order_amount <= $amount) {
                                if ($get_promocode->discount_type == 1) {
                                    $new_price = round($amount - (($amount) - ($get_promocode->discount_amount)), 2);
                                    $new_discount = round($amount - (($amount) - ($get_promocode->discount_amount)), 2);

                                } elseif ($get_promocode->discount_type == 2) {
                                    $new_price = round((($amount) - (($amount) * ($get_promocode->discount_amount)) / (100)), 2);
                                    $new_discount = round((($amount) * ($get_promocode->discount_amount)) / (100), 2);
                                } else {
//                                    return "zero";
//                                    return array('promo_code_status'=>0,'promo_message_code'=>9,'promo_code_message'=>"something went to wrong!",'min_order_amount'=>0,'promo_code_amount'=>0,'promo_code_name'=>"",
//                                    return array(0,9,"something went to wrong!",0,0,"");
                                    return array(0,9,__('user_messages.9'),0,0,"");
                                }
                                if ($get_promocode->max_discount_amount != Null) {
                                    if ($get_promocode->max_discount_amount < $new_discount) {
//                                        return round($get_promocode->max_discount_amount, 2);
                                        return array(1,1,__('user_messages.1'),0,round($get_promocode->max_discount_amount, 2),$get_promocode->promo_code);
                                    } else {
//                                        return round($new_discount, 2);
                                        return array(1,1,__('user_messages.1'),0,round($new_discount, 2),$get_promocode->promo_code);
                                    }
                                } else {
//                                    return round($new_discount, 2);
                                    return array(1,1,__('user_messages.1'),0,round($new_discount, 2),$get_promocode->promo_code);
                                }
                            } else {
//                                return "seven";
                                $min_order_amount = 0;
                                if($get_promocode != Null)
                                {
//                                    $min_order_amount = $get_promocode->min_order_amount." ".$user_currency;
                                    $min_order_amount = round($get_promocode->min_order_amount * $currency_ratio, 2);

                                }
//                                return array(0,235,"The promocode only applies if the order amount is greater than amount ",$min_order_amount,0,"");
                                return array(0,235,__('user_messages.235', ['value' => $user_currency." ".$min_order_amount]),$min_order_amount,0,"");
                            }
                        } else {
                            if ($get_promocode->discount_type == 1) {
                                $new_price = round($amount - (($amount) - ($get_promocode->discount_amount)), 2);
                                $new_discount = round($amount - (($amount) - ($get_promocode->discount_amount)), 2);
                            } elseif ($get_promocode->discount_type == 2) {
                                $new_price = round((($amount) - (($amount) * ($get_promocode->discount_amount)) / (100)), 2);
                                $new_discount = round((($amount) * ($get_promocode->discount_amount)) / (100), 2);
                            } else {
                                return array(0,9,__('user_messages.9'),0,0,"");
                            }
                            if ($get_promocode->max_discount_amount != Null) {
                                if ($get_promocode->max_discount_amount < $new_discount) {
//                                    return round($get_promocode->max_discount_amount, 2);
                                    return array(1,1,__('user_messages.1'),0,round($get_promocode->max_discount_amount, 2),$get_promocode->promo_code);
                                } else {
//                                    return round($new_discount, 2);
                                    return array(1,1,__('user_messages.1'),0,round($new_discount, 2),$get_promocode->promo_code);
                                }
                            } else {
//                                return round($new_discount, 2);
                                return array(1,1,__('user_messages.1'),0,round($new_discount, 2),$get_promocode->promo_code);
                            }
                        }
                    } else {
//                        return "two";
                        return array(0,177,__('user_messages.177'),0,0,"");
                    }
                } else {
//                    return "eight";
                    return array(0,236,__('user_messages.236'),0,0,"");
                }
            } else {
                if ($get_promocode->usage_limit > $get_user_used) {
                    if ($get_promocode->min_order_amount != Null) {
                        if ($get_promocode->min_order_amount <= $amount) {
                            if ($get_promocode->discount_type == 1) {
                                $new_price = round($amount - (($amount) - ($get_promocode->discount_amount)), 2);
                                $new_discount = round($amount - (($amount) - ($get_promocode->discount_amount)), 2);
                            } elseif ($get_promocode->discount_type == 2) {
                                $new_price = round((($amount) - (($amount) * ($get_promocode->discount_amount)) / (100)), 2);
                                $new_discount = round((($amount) * ($get_promocode->discount_amount)) / (100), 2);
                            } else {
                                return array(0,9,__('user_messages.9'),0,0,"");
                            }
                            if ($get_promocode->max_discount_amount != Null) {
                                if ($get_promocode->max_discount_amount < $new_discount) {
//                                    return round($get_promocode->max_discount_amount, 2);
                                    return array(1,1,__('user_messages.1'),0,round($get_promocode->max_discount_amount, 2),$get_promocode->promo_code);
                                } else {
//                                    return round($new_discount, 2);
                                    return array(1,1,__('user_messages.1'),0,round($new_discount, 2),$get_promocode->promo_code);
                                }
                            } else {
//                                return round($new_discount, 2);
                                return array(1,1,__('user_messages.1'),0,round($new_discount, 2),$get_promocode->promo_code);

                            }
                        } else {
//                            return "one";
                            return array(0,93,__('user_messages.93'),0,0,"");
                        }
                    } else {
                        if ($get_promocode->discount_type == 1) {
                            $new_price = round($amount - (($amount) - ($get_promocode->discount_amount)), 2);
                            $new_discount = round($amount - (($amount) - ($get_promocode->discount_amount)), 2);
                        } elseif ($get_promocode->discount_type == 2) {
                            $new_price = round((($amount) - (($amount) * ($get_promocode->discount_amount)) / (100)), 2);
                            $new_discount = round((($amount) * ($get_promocode->discount_amount)) / (100), 2);
                        } else {
//                          return "zero";
                            return array(0,9,__('user_messages.9'),0,0,"",);
                        }
                        if ($get_promocode->max_discount_amount != Null) {
                            if ($get_promocode->max_discount_amount < $new_discount) {
//                                return round($get_promocode->max_discount_amount, 2);
                                return array(1,1,__('user_messages.1'),0,round($get_promocode->max_discount_amount, 2),$get_promocode->promo_code);
                            } else {
//                                return round($new_discount, 2);
                                return array(1,1,__('user_messages.1'),0,round($new_discount, 2),$get_promocode->promo_code);
                            }
                        } else {
//                            return round($new_discount, 2);
                            return array(1,1,__('user_messages.1'),0,round($new_discount, 2),$get_promocode->promo_code);
                        }
                    }
                } else {
//                    return "two";
//                    return array(0,177, "Promocode usage limit exceeded!",0,0,"");
                    return array(0,177,__('user_messages.177'),0,0,"");
                }
            }
        } else {
//            return "zero";
//            return array(0,9,"something went to wrong!",0,0,"");
//            return array(0,234,"Promocode is expired!",0,0,"");
            return array(0,234,__('user_messages.234'),0,0,"");
        }
    }
    //store service end


    //other service start

    public function findOtherServiceCategory($service_cat_id)
    {
        $service_categories = OtherServiceCategory::query()->select('other_service_sub_category.id', 'other_service_sub_category.name', 'other_service_sub_category.icon_name')
            ->where('other_service_sub_category.service_cat_id', $service_cat_id)
            ->where('other_service_sub_category.status', 1)
            ->get();
        if (!$service_categories->isEmpty()) {
            $other_service_category_banner_url = url('/assets/images/provider-banners/');
            $service_cat_banner = ServiceCategory::query()->select(DB::raw("(CASE WHEN banner_image != '' THEN (concat('$other_service_category_banner_url','/',banner_image,'?v=0.3')) ELSE '' END) as service_category_banner"))
                ->where('id',$service_cat_id)
                ->first();
            $category = [];
            foreach ($service_categories as $service_category) {
                if ($service_category->icon_name != Null) {
                    $icon = url('/assets/images/service-category/other-service-sub-category/' . $service_category->icon_name);
                } else {
                    $icon = Null;
                }
                $category[] = [
                    "category_id" => $service_category->id,
                    "category_name" => $service_category->name,
                    "category_icon" => $icon
                ];
            }
            return response()->json([
                'status' => 1,
//                'message' => 'success!',
                'message' =>__('user_messages.1'),
                "message_code" => 1,
                'category_list' => $category,
                "service_category_banner" => isset($service_cat_banner) && $service_cat_banner != NUll ? $service_cat_banner->service_category_banner : ''
            ]);
        } else {
            return response()->json([
                'status' => 0,
//                'message' => 'Service Category not found!',
                'message' =>__('user_messages.124'),
                "message_code" => 124,
            ]);
        }
    }

    //radius fetch pending from database
    public function findServiceProvider($service_cat_id, $sub_cat_id, $lat_long, $currency)
    {
        if ($lat_long == Null) {
            return response()->json([
                "status" => 0,
//                "message" => "user address not found!",
                "message" => __('user_messages.9'),
                "message_code" => 9,
            ]);
        }
        $address_lat_long = array_map('trim', explode(",", $lat_long));
        $address_lat = $address_lat_long[0];
        $address_long = $address_lat_long[1];
        $radius = 25;    //max radius this add in settings
        $angle_radius = $radius / (111 * cos($address_lat)); //-0.23609747634791
        $min_lat = $address_lat + $angle_radius; //22.059707223652
        $max_lat = $address_lat - $angle_radius; //22.531902176348
        $min_lon = $address_long + $angle_radius; //70.570694823652
        $max_lon = $address_long - $angle_radius; //71.042889776348
        //not for demo
        $providers = Provider::query()->select('providers.id',
            DB::raw("CONCAT(COALESCE(providers.first_name,''),'') as name"),
            'providers.gender',
            'providers.avatar',
            'providers.service_radius',
            'provider_services.id as provider_service_id',
            'other_service_provider_details.rating',
            'other_service_provider_details.total_completed_order',
            'other_service_provider_details.time_slot_status',
            DB::raw(('other_service_provider_details.lat,other_service_provider_details.long, ( round( 6367 * acos( cos( radians(' . $address_lat . ') ) * cos( radians( other_service_provider_details.lat ) ) * cos( radians( other_service_provider_details.long ) - radians(' . $address_long . ') ) + sin( radians(' . $address_lat . ') ) * sin( radians( other_service_provider_details.lat ) ) ),2 ) ) AS distance'))
        //, DB::raw('(6367 * 2 * ASIN( SQRT( POWER( SIN(( ' . $address_lat . ' - other_service_provider_details.lat) *  pi()/180 / 2), 2)
        //    +COS( ' . $address_lat . ' * pi()/180)
        //    * COS(other_service_provider_details.lat * pi()/180)
        //    * POWER(SIN(( ' . $address_long . ' - other_service_provider_details.long) * pi()/180 / 2), 2) ))) as distance_to')
        )
            ->join('provider_services', 'provider_services.provider_id', '=', 'providers.id')
            ->join('other_service_provider_packages', 'other_service_provider_packages.provider_service_id', '=', 'provider_services.id')
            ->join('other_service_provider_details', 'other_service_provider_details.provider_id', '=', 'providers.id')
            ->where('provider_services.service_cat_id', $service_cat_id)
            ->where('other_service_provider_packages.sub_cat_id', $sub_cat_id)
            ->where('provider_services.current_status', 1)
            ->where('provider_services.status', 1)
//            ->where('other_service_provider_details.time_slot_status', 1)
            ->where('providers.status', 1)
            ->whereNull('providers.deleted_at')
            ->where('other_service_provider_packages.status',1)
//            ->havingRaw('providers.service_radius >= distance')
            ->orderBy('distance');
//        $this->checkDayShowRecord($providers,0);

        //->whereBetween('other_service_provider_details.lat', [$min_lat, $max_lat])
        //->whereBetween('other_service_provider_details.long', [$min_lon, $max_lon])
//        ->havingRaw('providers.service_radius >= distance')
        //->orderByRaw("distance")
        $providers = $providers->distinct()
            ->get();

        if ($providers->isEmpty()) {
            return response()->json([
                "status" => 0,
//                "message" => "providers not found!",
                "message" => __('user_messages.0'),
                "message_code" => 0,
            ]);
        }
        $provider_list = Null;
        foreach ($providers as $provider) {
            //if ($provider->distance <= $provider->service_radius) {
            $total_rating_count = UserRatings::join('user_service_package_booking', 'user_service_package_booking.id', '=', 'user_rating.package_book_id')
                ->where('user_service_package_booking.provider_id', $provider->id)->count();

            $provider_rating = 0;
            $provider_rating_count = UserRatings::query()->select(DB::raw('avg(rating) as ratings'))->join('user_service_package_booking', 'user_service_package_booking.id', '=', 'user_rating.package_book_id')
                ->where('user_service_package_booking.provider_id', $provider->id)->first();
            if ($provider_rating_count != Null) {
                $provider_rating = round($provider_rating_count->ratings, 2) - 0;
            }

            $package_list = OtherServiceProviderPackages::select('other_service_provider_packages.name as package_name',
                //'other_service_provider_packages.price as package_price',
                DB::raw('ROUND(other_service_provider_packages.price * ' . $currency . ',2) As package_price')
            )
                ->join('other_service_sub_category', 'other_service_sub_category.id', '=', 'other_service_provider_packages.sub_cat_id')
                ->where('other_service_provider_packages.provider_service_id', $provider->provider_service_id)
                ->where('other_service_sub_category.service_cat_id', $service_cat_id)
                ->where('other_service_sub_category.status', 1)
                ->where('other_service_provider_packages.status', 1)
                ->orderBy('other_service_provider_packages.price', 'asc')->take(4)->get();
            if ($provider->avatar != Null) {
                $avatar = url('/assets/images/profile-images/provider/' . $provider->avatar);
            } else {
                $avatar = "";
            }
            $distance = ($provider->distance != 0) ? round($provider->distance, 1) : 0;
            $provider_list[] = [
                'provider_id' => $provider->id,
                'provider_name' => trim($provider->name),
                'provider_profile_image' => $avatar,
                'provider_gender' => $provider->gender,
                'average_rating' => $provider_rating,
                'num_of_rating' => $total_rating_count,
                'distance' => $distance,
                'total_completed_order' => $provider->total_completed_order,
                'package_list' => $package_list,
                'time_slot_status' => $provider->time_slot_status
            ];
            //}
        }
        if ($provider_list == Null) {
            return response()->json([
                "status" => 0,
//                "message" => "providers not found!",
                "message" => __('user_messages.0'),
                "message_code" => 0,
            ]);
        }
        return response()->json([
            'status' => 1,
//            'message' => 'success!',
            'message' => __('user_messages.1'),
            "message_code" => 1,
            'provider_list' => $provider_list
        ]);
    }

    public function findServiceProviderPackageList($provider_id, $service_cat_id, $currency, $language = 'en')
    {
        if ($language != "en") {
            $lang_prefix = $language."_";
        } else {
            $lang_prefix = "";
        }

        $packages = OtherServiceProviderPackages::query()->select('other_service_provider_packages.id',
            'other_service_provider_packages.name',
            'other_service_provider_packages.description',
            DB::raw('ROUND(other_service_provider_packages.price * ' . $currency . ',2) As price'),
            'other_service_provider_packages.max_book_quantity',
            'other_service_sub_category.id as category_id',
            'other_service_sub_category.' . $lang_prefix . 'name as category_name')
            ->join('provider_services', 'provider_services.id', '=', 'other_service_provider_packages.provider_service_id')
            ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
            ->join('other_service_provider_details', 'other_service_provider_details.provider_id', '=', 'providers.id')
            ->join('other_service_sub_category', 'other_service_sub_category.id', '=', 'other_service_provider_packages.sub_cat_id')
            ->where('provider_services.provider_id', $provider_id)
            ->where('provider_services.service_cat_id', $service_cat_id)
            ->where('other_service_provider_packages.status', 1)
            ->where('provider_services.current_status', 1)
            ->where('provider_services.status', 1)
            ->where('providers.status', 1)
            ->where('other_service_provider_details.time_slot_status', 1)
            ->where('other_service_sub_category.status', 1)
            ->get();

        if (!$packages->isEmpty()) {
            $categories = array_unique(Arr::pluck($packages, 'category_name', 'category_id'));
            $packages = $packages->groupBy('category_id');
            foreach ($packages as $key => $list) {
                $package[] = [
                    'category_id' => $key,
                    'category_name' => $categories[$key],
                    'category_package_list' => $list
                ];
            }
            return response()->json([
                'status' => 1,
//                'message' => 'success!',
                'message' => __('user_messages.1'),
                "message_code" => 1,
                'package_list' => $package
            ]);
        } else {
            return response()->json([
                'status' => 0,
//                'message' => 'something went to wrong!',
                'message' => __('user_messages.9'),
                "message_code" => 9,
            ]);
        }
    }

    public function OtherServiceOrderDetails($order_id, $new_order, $unavailable_packages = Null,$user_lang= "")
    {
        //$check_SODS = Other Service Order Details Status true or false
        if($user_lang !="en"){
            $user_lang = $user_lang."_";
        }else{
            $user_lang = "";
        }
        //add order_status parameter in response
        $order = UserPackageBooking::query()->select('service_category.id as service_category_id',
            'service_category.'.$user_lang.'name as category_name',
            'service_category.icon_name as category_icon',
            'user_service_package_booking.id',
            'user_service_package_booking.user_id',
            'user_service_package_booking.order_no',
            'user_service_package_booking.service_date_time',
            'user_service_package_booking.lat_long',
            'user_service_package_booking.tax',
            'user_service_package_booking.tip',
            'user_service_package_booking.remark',
            'user_service_package_booking.refer_discount',
            'user_service_package_booking.total_pay',
            'user_service_package_booking.delivery_address',
            'user_service_package_booking.provider_name',
            'user_service_package_booking.cancel_by',
            'user_service_package_booking.cancel_reason',
            'user_service_package_booking.cancel_charge',
            'user_service_package_booking.status as order_status',
            'user_service_package_booking.total_item_cost',
            'user_service_package_booking.flat_no',
            'user_service_package_booking.landmark',
            'user_service_package_booking.provider_id',
            'user_service_package_booking.user_rating_status',
            'user_service_package_booking.payment_type',
            'user_service_package_booking.promo_code',
            'user_service_package_booking.service_date',
            'user_service_package_booking.service_time',
            'user_service_package_booking.book_start_time',
            'user_service_package_booking.book_end_time',
            'user_service_package_booking.select_provider_location',
            'user_service_package_booking.payment_status')
            ->join('service_category', 'service_category.id', '=', 'user_service_package_booking.service_cat_id')
            ->where('user_service_package_booking.id', $order_id)
            ->first();

        if ($order != Null) {
            $service_provider = Provider::query()->select('providers.avatar','providers.contact_number','providers.country_code', 'other_service_provider_details.rating', 'other_service_provider_details.lat', 'other_service_provider_details.long')
                ->join('other_service_provider_details', 'other_service_provider_details.provider_id', '=', 'providers.id')
                ->where('providers.id', $order->provider_id)->whereNull('providers.deleted_at')->first();

            if ($service_provider != Null) {
                $provider_profile_image = $service_provider->avatar != Null ? url('/assets/images/profile-images/provider/' . $service_provider->avatar) : '';
                $provider_rating = $service_provider->rating;
                $provider_contact = ($service_provider->contact_number != Null ) ? $service_provider->country_code.$service_provider->contact_number : "";
            } else {
                $provider_profile_image = '';
                $provider_rating = 0.00;
                $provider_contact = "";
            }

            $user_details = User::query()->where('id', $order->user_id)->whereNull('users.deleted_at')->first();
            if ($user_details != Null) {
                $user_currency = WorldCurrency::query()->where('symbol', $user_details->currency)->first();
                if ($user_currency == Null) {
                    $user_currency = WorldCurrency::query()->where('default_currency', 1)->first();
                }
            } else {
                $user_currency = WorldCurrency::query()->where('default_currency', 1)->first();
            }
            $currency = $user_currency->ratio;
            $lang_prefix = $user_lang;
            $lang_service_category_name  = $order->category_name;


            $package_items = UserPackageBookingQuantity::query()->select('num_of_items',
                'package_name', $lang_prefix . 'sub_category_name as sub_category_name', 'package_id',
                DB::raw('ROUND(price_for_one * ' . $currency . ',2) As price_for_one'),
                DB::raw('num_of_items * (ROUND(price_for_one * ' . $currency . ',2)) As total_single_package_price')
            )->where('order_id', $order->id)->get();
            $package_items = $package_items->groupBy('sub_category_name');
            $package_list = [];
            foreach ($package_items as $key => $list) {
                $package_list[] = [
                    'sub_category_name' => $key,
                    'item_list' => $list
                ];
            }

            /*$user_lang = ($user_details->language !="")?$user_details->language."_":"";
            $all_service_list  = ProviderServices::query()
                                ->select($user_lang . 'name as service_category_name')
                                ->join('service_category','service_category.id','provider_services.service_cat_id')
                                ->where('provider_services.provider_id',$order->provider_id)
                                ->where('provider_services.status',1)
                                ->orderBy('provider_services.id','desc')
                                ->get()
                                ->toArray();
            $all_service_list  = array_column($all_service_list,'service_category_name');
            $all_service_category_list = implode(",",$all_service_list);*/

            //find distance
            $delivery_lat_long = array_map('trim', explode(",", $order->lat_long));
            $delivery_lat = $delivery_lat_long[0];
            $delivery_long = $delivery_lat_long[1];
            $provider_lat_long = OtherServiceProviderDetails::query()->select('lat', 'long')->where('provider_id', $order->provider_id)->first();
            if ($provider_lat_long != Null) {
                $pickup_lat = $provider_lat_long->lat;
                $pickup_long = $provider_lat_long->long;
                $theta = $pickup_lat - $delivery_lat;
                $distance = (sin(deg2rad($pickup_lat)) * sin(deg2rad($delivery_lat))) + (cos(deg2rad($pickup_lat)) * cos(deg2rad($pickup_lat)) * cos(deg2rad($theta)));
                $distance = acos($distance);
                $distance = rad2deg($distance);
                $distance = $distance * 60 * 1.1515 * 1.609344;
                $distance = round($distance) . ' km away';
            } else {
                $distance = '';
            }
            if ($new_order == true) {
                $provider = Provider::query()->select('device_token', 'login_device','language')->where('id', $order->provider_id)->first();
                if ($provider != Null) {
                    $this->notificationClass->providerOrderRequestNotification($order->id, $order->order_status, $provider->device_token, $provider->login_device, $provider->language);
                }
                $general_settings = GeneralSettings::query()->first();
                if ($general_settings !=  Null) {
                    if ($general_settings->send_mail == 1) {
                        try{
                            $category_name = $order->category_name;

                            if ($general_settings->send_receive_email != Null) {
                                $service_name = ucwords(strtolower($category_name));
//                                $provider_name = ucwords($order->provider_name);
                                $user_name = $user_details->first_name."".$user_details->last_name;
                                $mail_type = "admin_user_new_service_request_-_handyman";
                                $to_mail = $general_settings->send_receive_email;

                                $subject = "You get a new " . $service_name . " service request ";
                                $disp_data = array("##service_name##" => $service_name, "##user_name##" => ucwords($user_name));
                                $mail_return_data = $this->notificationClass->sendMail($subject, $to_mail, $mail_type, $disp_data);
                            }
                        }
                        catch (\Exception $e){}
                    }
                }
            }
            if ($order->promo_code != 0) {
                try {
                    $get_discount = UsedPromocodeDetails::query()->where('id', $order->promo_code)->first();
                    $promo_code_discount = $get_discount->discount_amount;
                    $promocode_name = $get_discount->promocode_name;
                } catch (\Exception $e) {
                    $promo_code_discount = round($order->total_item_cost - $order->discount_cost + $order->tax_cost + $order->delivery_cost + $order->packaging_cost - $order->total_pay, 2);
                    $promocode_name = '';
                }
            } else {
                $promo_code_discount = 0;
                $promocode_name = '';
            }
//            $schedule_order_time= date("h:i A",strtotime($order->book_start_time))." - ".date("h:i A",strtotime($order->book_end_time));
            $schedule_order_time= date("H:i:s",strtotime($order->book_start_time))." - ".date("H:i:s",strtotime($order->book_end_time));
            return response()->json([
                "status" => 1,
//                "message" => "success!",
                "message" => __('user_messages.1'),
                "message_code" => 1,
                "order_id" => $order->id,
                "order_no" => $order->order_no."",
                'additional_remark' => "" . $order->remark,
                "order_payment_type" => $order->payment_type - 0,
                "order_payment_status" => $order->payment_status - 0,
                "category_name" => $lang_service_category_name,
                "category_icon" => url('/assets/images/service-category/' . $order->category_icon),
                //"service_date_time" => date('Y-m-d H:i:s',strtotime($order->created_at)),
                //"schedule_order_date_time" => $order->service_date_time,
                "schedule_order_date" => $order->service_date,
                "schedule_order_time" => $schedule_order_time,
                "start_time" => Carbon::parse($order->book_start_time)->format('H:i:s'),
                "end_time" => Carbon::parse($order->book_end_time)->format('H:i:s'),
                "provider_id" => $order->provider_id,
                "provider_name" => $order->provider_name,
                'provider_contact' =>$provider_contact,
                "distance" => $distance,
                "flat_no" => $order->flat_no != Null ? $order->flat_no : '',
                "landmark" => $order->landmark != Null ? $order->landmark : '',
                "provider_profile_image" => $provider_profile_image,
                "provider_rating" => $provider_rating,
                "user_rating_status" => $order->user_rating_status - 0,
                "tax" => round($order->tax * $currency, 2),
                "tip" => round($order->tip * $currency, 2),
                "sub_total" => round($order->total_item_cost * $currency, 2),
                "discount" => 0.00,
                "refer_discount" => round($order->refer_discount * $currency, 2),
                "total_pay" => round($order->total_pay * $currency, 2),
                "delivery_address" => $order->delivery_address,
                "cancel_by" => $order->cancel_by != Null ? $order->cancel_by : "",
                "cancel_reason" => $order->cancel_reason != Null ? $order->cancel_reason : "",
                'cancel_charge' => ($order->status == 4) ? ($order->payment_type == 2 || $order->payment_type == 3) ? ($order->cancel_charge != Null) ? $order->cancel_charge : 0 : 0 : 0,
                "order_status" => $order->order_status,
                'promocode_name' => $promocode_name,
                'promo_code_discount' => $promo_code_discount,
                'promocode_discount' => $promo_code_discount,
//              'all_service_category_list' => $all_service_category_list,
                "package_list" => $package_list,
                "unavailable_packages" => isset($unavailable_packages) && $unavailable_packages != Null ? $unavailable_packages : '',
                'select_provider_location' => $order->select_provider_location
            ]);
        } else {
            return response()->json([
                "status" => 0,
//                "message" => "order not found!",
                "message" => __('user_messages.59'),
                "message_code" => 59,
            ]);
        }
    }
    //other service end


    //user address
    public function userAddressManage($request, $add, $update, $delete)
    {
        if ($add == true || $update == true) {
            $validator = Validator::make($request->all(), [
                "address" => "required",
                "type" => "nullable|in:home,work,other",
                "lat" => "required",
                "long" => "required",
                "flat_no" => "required",
                "landmark" => "required"
            ]);
            if ($validator->fails()) {
                return response()->json([
                    "status" => 0,
                    "message" => $validator->errors()->first(),
                    "message_code" => 9,
                ]);
            }
        }
        if ($update == true || $delete == true) {
            $validator = Validator::make($request->all(), [
                "address_id" => "required",
            ]);
            if ($validator->fails()) {
                return response()->json([
                    "status" => 0,
                    "message" => $validator->errors()->first(),
                    "message_code" => 9,
                ]);
            }
        }
        $count_active_address = UserAddress::where('user_id', $request['user_id'])->where('status', 1)->count();
        $count_inactive_address = UserAddress::where('user_id', $request['user_id'])->where('status', 0)->count();
        if ($add == true) {
            if (($count_active_address + $count_inactive_address) < 5) {
                $address = new UserAddress();
                $address->user_id = $request['user_id'];
                if ($request['type'] != Null) {
                    $address->address_type = $request['type'];
                }
                $address->address = $request['address'];
                $address->lat_long = $request['lat'] . ',' . $request['long'];
                $address->flat_no = $request['flat_no'];
                $address->landmark = $request['landmark'];
                $address->save();
                return response()->json([
                    "status" => 1,
//                    "message" => "success!",
                    'message' => __('user_messages.1'),
                    "message_code" => 1,
                    "address_id" => $address->id
                ]);
            } else {
                if ($count_active_address == 5) {
                    return response()->json([
                        "status" => 0,
//                        "message" => "user only add five address,if you add new address then delete old address!",
                        'message' => __('user_messages.126'),
                        "message_code" => 9,
                    ]);
                } elseif ($count_inactive_address > 0) {
                    $address = UserAddress::where('user_id', $request['user_id'])->where('status', 0)->first();
                    $address->user_id = $request['user_id'];
                    $address->address_type = $request['type'];
                    $address->address = $request['address'];
                    $address->lat_long = $request['lat'] . ',' . $request['long'];
                    $address->flat_no = $request['flat_no'];
                    $address->landmark = $request['landmark'];
                    $address->status = 1;
                    $address->save();
                    return response()->json([
                        "status" => 1,
//                        "message" => "success!",
                        'message' => __('user_messages.1'),
                        "message_code" => 1,
                        "address_id" => $address->id
                    ]);
                } else {
                    return response()->json([
                        "status" => 0,
//                        "message" => "something went to wrong!",
                        'message' => __('user_messages.9'),
                        "message_code" => 9,
                    ]);
                }
            }
        } elseif ($update == true) {
            $address = UserAddress::where('user_id', $request['user_id'])->where('id', $request['address_id'])->first();
            $address->user_id = $request['user_id'];
            $address->address_type = $request['type'];
            $address->address = $request['address'];
            $address->lat_long = $request['lat'] . ',' . $request['long'];
            $address->flat_no = $request['flat_no'];
            $address->landmark = $request['landmark'];
            $address->save();
            return response()->json([
                "status" => 1,
//                "message" => "success!",
                'message' => __('user_messages.1'),
                "message_code" => 1,
                "address_id" => $address->id
            ]);
        } elseif ($delete == true) {
            $address = UserAddress::where('user_id', $request['user_id'])->where('id', $request['address_id'])->first();
            $address->status = 0;
            $address->save();
            return response()->json([
                "status" => 1,
//                "message" => "success!",
                'message' => __('user_messages.1'),
                "message_code" => 1,
            ]);
        } else {
            return response()->json([
                "status" => 0,
//                "message" => "something went to wrong!",
                'message' => __('user_messages.9'),
                "message_code" => 9,
            ]);
        }
    }

    //trnasport search driver for notification
    public function SearchDriverForNotification($lat, $long, $search_radians, $service_cat_id, $vehicle_type)
    {

        $driver_details = TransportDriverDetails::select('providers.device_token')
            ->whereRaw(DB::raw("(6371 * acos( cos( radians(" . $lat . ") ) * cos( radians( current_lat ) )  *
                          cos( radians( current_long ) - radians(" . $long . ") ) + sin( radians(" . $lat . ") ) * sin(
                          radians( current_lat ) ) ) ) < " . $search_radians)
            )
            ->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
            ->join('providers', 'providers.id', 'provider_services.provider_id')
            ->join('transport_vehicle_type', 'transport_vehicle_type.id', '=', 'transport_driver_details.vehicle_type_id')
            ->whereNotNull('transport_driver_details.current_lat')
            ->whereNotNull('transport_driver_details.current_long')
            ->where('transport_driver_details.vehicle_type_id', '=', $vehicle_type)
            ->whereNotNull('providers.access_token')
            ->whereNotNull('providers.device_token')
            ->where('providers.status', '=', 1)
            ->where('provider_services.service_cat_id', '=', $service_cat_id)
            ->where('provider_services.status', '=', 1)
            ->where('provider_services.current_status', '=', 1)
            ->whereNull('providers.deleted_at')
            ->get();

        $tokens = [];
        if ($driver_details->isEmpty()) {
            $tokens = [];
        } else {
            foreach ($driver_details as $key => $driver) {
                $tokens[] = $driver->device_token;
            }
        }
        return $tokens;

    }

    // send driver new ride notification

    //pending "message_code" => 1,
    public function sendUserRideBookNotification($ride_id, $current_ride_status, $token)
    {

        $general_settings = GeneralSettings::select('fcm_server_key')->first();
        if ($general_settings == Null || $general_settings->fcm_server_key == Null) {
            return response()->json([
                'status' => 0,
                'message' => "Something want wrong!",
                "message_code" => 9,
            ]);
        }
        $server_key = trim($general_settings->fcm_server_key);

        $scheduled_request_id = $ride_id;
        if ($current_ride_status == "new") {
            $ride_status = "new";
            $message = "Driver New Request";
        } elseif ($current_ride_status == "cancelled") {
            $ride_status = "cancelled";
            $message = "User Cancelled Ride";
        } else {
            $ride_status = null;
            $message = null;
        }
        //To driver Assign
        $title = "driver";
        $fcmUrl = 'https://fcm.googleapis.com/fcm/send';
        $notification = [
            'title' => $title,
            'sound' => true,
        ];
        $extraNotificationData = [
            'message' => $notification,
            'scheduled_request_id' => $scheduled_request_id,
            'user_ride_status' => $ride_status,
            'data' => $message,
        ];
        if (is_array($token)) {
            $fcmNotification = [
                'registration_ids' => $token, //multple token array
                'data' => $extraNotificationData
            ];
        } else {
            $fcmNotification = [
                'to' => $token,
                'data' => $extraNotificationData
            ];
        }
        $headers = [
            'Authorization: key=' . $server_key,
            'Content-Type: application/json'
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $fcmUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmNotification));
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;

    }
    public function get_coordinates($origin, $destination)
    {
//        $general_settings = GeneralSettings::query()->select('map_key')->first();
//        $map_key = trim($general_settings->map_key);
        $map_key = "AIzaSyBMGPhIDJy8aOePdHmxsWztcZCorr9BhG4";
        $details = "https://maps.googleapis.com/maps/api/directions/json?origin=" . $origin . "&destination=" . $destination . "&mode=DRIVING&key=" . $map_key;
        //$details = "https://maps.googleapis.com/maps/api/directions/json?origin=Rajkot,India,360001&waypoints=optimize:true|via:22.222,70.222|via:22.222,70.222via:22.222,70.222&destination=22.222,70.222&mode=DRIVING&key=".$map_key;
        $json = file_get_contents($details);
        return $json;
    }
    public function checkDeliveryCharge($order_distance=0,$service_category_id=0)
    {
        $cost_per_km  = 0;
        $delivery_charge = DeliveryCharges::query()
            ->where('delivery_charges.service_cat_id', '=', $service_category_id)
            ->where('delivery_charges.start_limit', '<=', $order_distance)
            ->where('delivery_charges.close_limit', '>', $order_distance)
            ->first();
        if($delivery_charge != NULL){
            $cost_per_km = $delivery_charge->charges;
        } else{
            $delivery_charge = DeliveryCharges::query()
                ->where('delivery_charges.service_cat_id', '=', $service_category_id)
                ->where('delivery_charges.start_limit', '<=', $order_distance)
                ->where('delivery_charges.close_limit',  '=', 0)
                ->first();
            if($delivery_charge != NULL){
                $cost_per_km = $delivery_charge->charges;
            }
        }

        if ($order_distance > 0){
            $cost_per_km = $cost_per_km;
        }
        return $cost_per_km;
    }

    function parse_signed_request($signed_request,$user_type) {
        list($encoded_sig, $payload) = explode('.', $signed_request, 2);

        if($user_type == 'user'){
            $secret = "36001d4b9b3274605d938909fefd1839";
        } elseif($user_type == 'driver'){
            $secret = "da23140a8d1e1105419c26c43c44f08e";
        } else{
            $secret = "6a358eed6c962871fe0fd96f2d8eb75a";
        }

        $sig = $this->base64_url_decode($encoded_sig);
        $data = json_decode($this->base64_url_decode($payload), true);

        $expected_sig = hash_hmac('sha256', $payload, $secret, $raw = true);
        if ($sig !== $expected_sig) {
            error_log('Bad Signed JSON signature!');
            return null;
        }

        return $data;
    }

    function base64_url_decode($input) {
        return base64_decode(strtr($input, '-_', '+/'));
    }

    public function checkDayShowRecordOLDBKP($get_store_list,$get_type = 0){
        $is_day_allow = 0;
        $general_settings = request()->get("general_settings");
        if ($general_settings != Null) {
            if ($general_settings->day_allow > 0) {
                $is_day_allow = $general_settings->day_allow - 0;
            }
        }
        $limit_date_time = date('Y-m-d', strtotime("-" . $is_day_allow . " days"));
        $get_store_list->where(function ($query) use ($limit_date_time,$get_type) {
            $query->where('providers.fix_user_show', 1)->groupBy("providers.id");
            if ($get_type == 0) {
                $query->orWhere('providers.created_at', '>=', $limit_date_time);
            }
        });
        return $get_store_list;

    }

    public function checkDayShowRecord($get_store_list,$get_type = 0){
        $is_day_allow = 0;
        $general_settings = request()->get("general_settings");
        if ($general_settings != Null) {
            $is_day_allow = $general_settings->day_allow - 0;
        }
        if ($is_day_allow > 0){
            $limit_date_time = date('Y-m-d', strtotime("-" . $is_day_allow . " days"));
            $get_store_list->where(function ($query) use ($limit_date_time,$get_type) {
                $query->where('providers.fix_user_show', 1)->groupBy("providers.id");
                if ($get_type == 0) {
                    $query->orWhere('providers.created_at', '>=', $limit_date_time);
                }
            });
            return $get_store_list;
        }
        return "";

    }
    public function checkDayShowRecordBKP($get_store_list,$get_type = 0){
        $is_day_allow = 0;
        $general_settings = request()->get("general_settings");
        if ($general_settings != Null) {
            $is_day_allow = $general_settings->day_allow - 0;
        }
        if ($is_day_allow > 0){
            $limit_date_time = date('Y-m-d', strtotime("-" . $is_day_allow . " days"));
            $get_store_list->where(function ($query) use ($limit_date_time,$get_type) {
                $query->where('providers.fix_user_show', 1)->groupBy("providers.id");
                if ($get_type == 0) {
                    $query->orWhere('providers.created_at', '>=', $limit_date_time);
                }
            });
            return $get_store_list;
        }
        return "";
    }

    public function getCartProductList($user_id,$order_add_by="0",$ret_type="json"){
        $getUserCartStoreData =  UserOrderCart::query()->select('store_id')->where('user_id',$user_id)->where('order_add_by',$order_add_by)->groupBy('store_id')->get();
        $retArrayData =  array();
        $product_list_array = [];
        if( $getUserCartStoreData->isNotEmpty() ){
            if(count($getUserCartStoreData) <= 3) {
                foreach ($getUserCartStoreData as $single_store_cart_data){
                    //get single store cart data
                    $getSingleStoreCardData =  UserOrderCart::query()->where('user_id',$user_id)->where('order_add_by',$order_add_by)->where('store_id',$single_store_cart_data->store_id)->get();
                    $get_product_list =[];
                    if($getSingleStoreCardData->isNotEmpty()){
                        foreach ($getSingleStoreCardData as $single_cart_data) {
                            $option_id =  ($single_cart_data->option_id != Null)?explode(",",$single_cart_data->option_id):[];
                            $topping_id =  ($single_cart_data->topping_id != Null)?explode(",",$single_cart_data->topping_id):[];
                            $get_product_list[] = [
                                'product_id' => $single_cart_data->product_id - 0,
                                'product_quantity' => [
                                    [
                                        'quantity' => $single_cart_data->quantity -0 ,
                                        'size' => $single_cart_data->size_id - 0,
                                        'options' => $option_id,
                                        'toppings' => $topping_id ,
                                    ]
                                ]
                            ];
                        }
                        $store_id = $single_store_cart_data->store_id;
                        $product_list_array =$get_product_list;
                        /*$product_list_array[] = [
                            'store_id' => $store_id - 0,
                            'user_taken_type' => 0,
                            'additional_info' => "",
                            'product_list' => $get_product_list,
                        ];*/
                    } else{
                        $product_list_array =  array ('error'=>"success","errorMsg"=> __('messages.159'));
                    }
                }
            } else{
                $product_list_array =  array ('error'=>"success","errorMsg"=> __('messages.160'));
            }
        } else{
            $product_list_array =  array ('error'=>"success","errorMsg"=> __('messages.161'));
        }
        if($ret_type == "array"){
            return $product_list_array;
        } else {
            return json_encode($product_list_array);
        }
    }

    public function cartOrderBook($request="",$user_id="",$product_list="",$address_id="",$payment_type=0,$card_id=0,$payment_method_type=0,$mb_way_payment_contact_number="", $order_add_by=0,$user_taken_type=0,$service_category_id=0,$promocode_id=0){

        if ($request == Null || $user_id == Null || $product_list == Null || $address_id == Null || $service_category_id <= 0 ) {
            //Session::flash('error', 'Required fields is empty!');
            Session::flash('error', __('messages.162'));
            return redirect()->back();
        }
        $user_details = User::query()->where('id',$user_id)->whereNull('users.deleted_at')->first();
        if ($user_details == Null){
            Session::flash('error', __('messages.55'));
            return redirect()->back();
        }
        $general_settings = GeneralSettings::query()->first();
        if ($general_settings == Null) {
            Session::flash('error', __('messages.163'));
            return redirect()->back();
        }
        $get_category = ServiceCategory::query()->where('id', $service_category_id)->first();
        if ($get_category == Null) {
            Session::flash('error', __('messages.158'));
            return redirect()->back();
        }
        //$payment_method_type = $payment_method_type;
        //$card_id = $card_id;
        //$mb_way_payment_contact_number = $mb_way_payment_contact_number;
        //$payment_type = $payment_type;
        $is_stripe_sandbox = $general_settings->is_stripe_sandbox;
        $is_mb_way_sandbox = $general_settings->is_mb_way_sandbox;
        if ($payment_type == 2) {
            if ($payment_method_type > 0){
                if ($payment_method_type == 1){
                    //stripe payment
                    if ($is_stripe_sandbox > 0 ){
                        if ($is_stripe_sandbox == 2) {
                            $cus_token = $user_details->sandbox_cus_token;
                            $stripe_secret_key = $general_settings->stripe_sandbox_key;
                        } else {
                            $cus_token = $user_details->cus_token;
                            $stripe_secret_key = $general_settings->stripe_key;
                        }
                        if ($stripe_secret_key == Null) {
                            Session::flash('error', __('messages.161'));
                            return redirect()->back();
                        }
                        $card_details = UserCardDetails::query()->where('id', $card_id)->where('card_is_sandbox', $is_stripe_sandbox)->first();
                        if ($card_details == Null) {
                            Session::flash('error', __('messages.164'));
                            return redirect()->back();
                        }
                        $card_token = $card_details->token;
                        if ($card_token == Null){
                            Session::flash('error', __('messages.164'));
                            return redirect()->back();
                        }
                        if ($cus_token == Null){
                            Session::flash('error', __('messages.165'));
                            return redirect()->back();
                        }
                        if ($card_details->d_type != 1){
                            $stripe = new \Stripe\StripeClient($stripe_secret_key);
                            $stripe->customers->update(
                                $cus_token."",
                                [
                                    'default_source' => $card_token. "",
                                ]
                            );
                            $card_details->d_type = 1;
                            $card_details->save();
                            $user_card_list = UserCardDetails::query()->where("user_id",$user_details->id)->where("card_is_sandbox", $is_stripe_sandbox)->where("id" ,"!=",$card_details->id)->get();
                            if ($user_card_list->isNotEmpty()){
                                foreach ($user_card_list as $key => $user_card){
                                    $get_card = UserCardDetails::query()->where("id",$user_card->id)->first();
                                    if ($get_card != Null){
                                        $get_card->d_type = 0;
                                        $get_card->save();
                                    }
                                }
                            }
                        }
                    }
                }
                elseif ($payment_method_type == 2){
                    //mb way payment
                    if ($is_mb_way_sandbox > 0 ){
                        if ($is_mb_way_sandbox == 2) {
                            $mb_way_key = $general_settings->mb_way_sandbox_key;
                            $mb_way_url = "https://sandbox.eupago.pt/clientes/rest_api/mbway/create";
                        } else {
                            $mb_way_key = $general_settings->mb_way_key;
                            $mb_way_url = "https://clientes.eupago.pt/clientes/rest_api/mbway/create";
                        }
                        $mb_way_payment_contact_number = $mb_way_payment_contact_number;
                        if ($mb_way_payment_contact_number == Null){
                            $mb_way_payment_contact_number = $user_details['contact_number'];
                        }
                        if ($mb_way_key == Null || $mb_way_payment_contact_number == Null) {
                            Session::flash('error', __('messages.161'));
                            return redirect()->back();
                        }
                    }
                }
                else {
                    Session::flash('error', __('messages.166'));
                    return redirect()->back();
                }
            }
        }

        $address_lat_long = Null;
        //$user_taken_type = $user_taken_type;
        //get user address
        if ($user_taken_type != 2){
            $address = UserAddress::query()->where('id', $address_id)->where('user_id', $user_details->id)->where('status', 1)->first();
            if ($address == Null) {
                Session::flash('error', __('messages.150'));
                return redirect()->back();
            }
            $address_lat_long = array_map('trim', explode(",", $address->lat_long));
            if (count($address_lat_long) != 2) {
                Session::flash('error', __('messages.150'));
                return redirect()->back();
            }
        }

        try {
            $store_product_list = json_decode($product_list, true);
        } catch (\Exception $exception) {
            Session::flash('error', __('messages.167'));
            return redirect()->back();
        }
        if ($store_product_list == Null) {
            Session::flash('error', __('messages.167'));
            return redirect()->back();
        }
        $get_store_details = StoreDetails::query()->select("store_details.*" )->join('provider_services', 'provider_services.id', '=', 'store_details.provider_service_id')->join('providers', 'providers.id', '=', 'provider_services.provider_id');
        $store_ids_array = array_column($store_product_list, 'store_id');
        /*$checkSameCityId = $get_store_details->whereIn('store_details.id', $store_ids_array)->pluck('providers.city_id')->toArray();
        if (!(count(array_unique($checkSameCityId)) <= 1)) {
            Session::flash('error', __('messages.168'));
            return redirect()->back();
        }

        if ( count(array_unique($checkSameCityId)) > 3){
            Session::flash('error', __('messages.160'));
            return redirect()->back();
        }*/
        /*$store_city_id = array_unique($checkSameCityId)[0];*/

        $user_taken_status = 0;
        if ($user_taken_type == 2){
            $checkSameTakeAwayOption = $get_store_details->whereIn('store_details.id', $store_ids_array)->pluck('store_details.taken_away_type')->toArray();
            if (!(count(array_unique($checkSameTakeAwayOption)) <= 1)) {
                //Session::flash('error', 'Please select all store in given take away option!');
                Session::flash('error', __('messages.169'));
                return redirect()->back();
            }
            $user_taken_status = array_unique($checkSameTakeAwayOption)[0];
        }

        $maintenance_charge = 0;
        $base_fee = 0;
        //get sub admin site settings
        $service_settings = ServiceSettings::query()->where('service_cat_id', $service_category_id)->first();
        if ($service_settings != Null) {
            $admin_commission = $service_settings->admin_commission;
            $delivery_commission = $service_settings->delivery_commission;
            $tax_cost = $service_settings->tax != 0 ? $service_settings->tax : 0;
        }
        else {
            $admin_commission = 0;
            $delivery_commission = 0;
            $tax_cost = 0;
        }
        $get_tax = $general_settings->tax;
        $admin_commission = $general_settings->admin_commission;
        $delivery_commission = $general_settings->delivery_commission;
        $order_date_time = date('Y-m-d H:i:s');
        if ($request['schedule_date_time'] != Null) {
            $place_order_type = 1;
            $place_order_date_time = date('Y-m-d H:i:s', strtotime($request['schedule_date_time']));
        } else {
            $place_order_type = 0;
            $place_order_date_time = $order_date_time;
        }
        //type = 0 store unavailble
        //type = 1 product un available
        //type = 2 both unavailable
        //type = 3 min order amount

        //store and product counting
        $req_store_ids = [];
        $unavailable_store = [];
        $order_book_array = [];
        $all_total_pay = 0;
        $all_total_item_cost = 0;
        $all_discount_cost = 0;
        foreach ($store_product_list as $store_key => $store_product_detils) {
            $next_status = 0;
            $store_id = $store_product_detils['store_id'];
            $store_details = StoreDetails::query()->where('id', $store_id)->first();

            $total_product_selling_net_amount = 0;

            $total_buy_product_net_amount = 0;
            $total_buy_price = 0;
            $total_buy_product_tax = 0;

            $unavailable_products = [];
            if ($store_details != Null) {

                $store_name = $store_details->name;
                if ($user_taken_type != 2) {
                    $address_lat = trim($address_lat_long[0]);
                    $address_long = trim($address_lat_long[1]);
                    $check_near_delivery_address = StoreDetails::query()->select('store_details.id', 'providers.service_radius'
                        , DB::raw('(6367 * 2 * ASIN( SQRT( POWER( SIN(( ' . $address_lat . ' - store_details.address_lat) *  pi()/180 / 2), 2)
                        +COS( ' . $address_lat . ' * pi()/180)
                        * COS(store_details.address_lat * pi()/180)
                        * POWER(SIN(( ' . $address_long . ' - store_details.address_long) * pi()/180 / 2), 2) ))) as distance')
                    )
                        ->join('provider_services', 'provider_services.id', '=', 'store_details.provider_service_id')
                        ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
                        ->where('provider_services.service_cat_id', $service_category_id)
                        ->where('store_details.id', $store_id)
//                        ->havingRaw('providers.service_radius > distance')
                        ->first();
                    if ($check_near_delivery_address == Null) {
                        $next_status = 1;
                        //$message = "Your delivery address out of store delivery range! Please change your delivery address.";
                        //$message = "<b>".$store_name."</b></br> Your delivery address out of store delivery range! Please change your delivery address.";
                        $message = "<b>".$store_name."</b></br> ". __('messages.152');
                        $message_code = 115;
                        $unavailable_store[] = [
                            "type" => 4,
                            "store_id" => $store_id - 0,
                            "product_ids" => "",
                            "message" => $message,
                            "message_code" => $message_code - 0,
                        ];
                    }
                }

                $store_fixed_charge = $store_details->fixed_charge;
                if ($next_status != 1) {
                    $service_status = ProviderServices::query()->where('id', $store_details->provider_service_id)->where('service_cat_id', $service_category_id)->where('status', 1)->first();
                    //->where('current_status', 1)
                    if ($service_status == Null) {
                        $next_status = 1;
                        //$message = "store currently not available!";
                        //$message = "<b>".$store_name."</b></br>store currently not available!";
                        $message = "<b>".$store_name."</b></br>".__('messages.170');
                        $message_code = 69;
                        $unavailable_store[] = [
                            "type" => 0,
                            "store_id" => $store_id - 0,
                            "product_ids" => "",
                            "message" => $message,
                            "message_code" => $message_code - 0,
                        ];
                    }
                    if ($next_status != 1) {
                        $schedule_order_type = 0;
                        $store_time = StoreTimings::query()->select('store_timings.day', 'store_timings.store_open_time', 'store_timings.store_close_time')->where('store_id', $store_details->id)->where('day', 'all')->first();
                        //->where('day', strtoupper(substr(date('l'), 0, 3)))
                        if ($store_time == Null) {
                            if ($request->get('schedule_date_time') != Null) {
                                $schedule_order_type = 1;
                                $order_date_time = date('Y-m-d H:i:s', strtotime($request->get('schedule_date_time')));
                            } else {
                                $order_date_time = date('Y-m-d H:i:s');
                            }
                            $store_time = StoreTimings::query()->select('store_timings.day', 'store_timings.store_open_time', 'store_timings.store_close_time')->where('store_id', $store_details->id)->where('day', substr(date('l', strtotime($order_date_time)), 0, 3))->first();
                            //->where('day', 'all')->where('day', strtoupper(substr(date('l'), 0, 3)))->where('day', $day)
                        }
                        $time = date('H:i:s', strtotime($order_date_time));
                        if ($store_time != Null) {
                            if ( ($time >= $store_time->store_open_time && $time <= $store_time->store_close_time)  ) {
                                //if ($time >= $store_time->store_open_time && $time <= $store_time->store_close_time) {
                                $store_time_status = 1; //"open"
                            } else {
                                $store_time_status = 0; //"close"
                            }
                        }
                        else {
                            $store_time_status = 0; //"close"
                        }
                        if ($schedule_order_type != 1) {
                            if ($store_time_status == 1) {
                                if ($service_status->current_status == 1) {
                                    $store_time_status = 1; //"close"
                                } else {
                                    $store_time_status = 0; //"close"
                                }
                            }
                        }
                        if ($store_time_status == 0) {
                            $next_status = 1;
                            //$message = "store currently not available!";
                            //$message = "<b>".$store_name."</b></br>store currently not available!";
                            $message = "<b>".$store_name."</b></br>".__('messages.170');
                            $message_code = 69;
                            $unavailable_store[] = [
                                "type" => 0,
                                "store_id" => $store_id - 0,
                                "product_ids" => "",
                                "message" => $message,
                                "message_code" => $message_code - 0,
                            ];
                        }
                        if ($next_status != 1) {
                            $product_price = [];
                            $order_product_list = [];
                            $order_product_list_string = [];
                            $discount_cost = 0;
                            $total_price = 0;
                            $total_item_cost = 0;

                            $store_details->fixed_charge;
                            $total_extra_product_charge = 0;

                            $product_list = $store_product_detils['product_list'];
                            foreach ($product_list as $product) {
                                if ($product['product_id'] != Null && $product['product_quantity'] != Null) {
                                    $product_details = StoreProductDetails::query()->where('id', $product['product_id'])->where('status', 1)->first();
                                    if ($product_details != Null) {
                                        $product_name = $product_details->name;
                                        if ($product['product_quantity'] != Null) {
                                            $get_product_tax = 0;

                                            $quantity_price = 0;
                                            $total_product_discount = 0;
                                            $quantity = 0;
                                            $order_product_quantity = [];

                                            $quantity = 0;
                                            foreach ($product['product_quantity'] as $product_quantity) {
                                                $product_category = StoreProductCategory::query()->where('id', $product_details->product_cate_id)->first();
                                                $size = $product_quantity['size'];
                                                $size_data = [];
                                                if ($size != 0) {
                                                    $size_details = StoreProductSize::query()->where('id', $size)->first();
                                                    if ($size_details != Null) {
                                                        $price = $size_details->amount;
                                                        $buy_price = $size_details->amount;
                                                        $size_data = [
                                                            "id" => $size_details->id,
                                                            "name" => $size_details->name,
                                                            "amount" => $size_details->amount,
                                                        ];
                                                    } else {
                                                        $size_get = StoreProductSize::query()->where('product_id', $product_details->id)->get();
                                                        if (count($size_get) > 2) {
                                                            //Session::flash('error', '<b>'.$product_name.'</b></br>You not selected valid size!');
                                                            Session::flash('error', '<b>'.$product_name.'</b></br> '.__('messages.171'));
                                                            return redirect()->back();
                                                        } elseif (count($size_get) == 1) {
                                                            $price = $size_get[0]->amount;
                                                            $buy_price = $size_details->amount;
                                                            $size_data = [
                                                                "id" => $size_get[0]->id,
                                                                "name" => $size_get[0]->name,
                                                                "amount" => $size_get[0]->amount
                                                            ];
                                                        } else {
                                                            $price = $product_details->amount;
                                                            $buy_price = $product_details->amount;
                                                        }
                                                    }
                                                }
                                                else {
                                                    $size_get = StoreProductSize::query()->where('product_id', $product_details->id)->get();
                                                    if (count($size_get) > 2) {
                                                        Session::flash('error', '<b>'.$product_name.'</b></br> '.__('messages.171'));
                                                        return redirect()->back();
                                                    } elseif (count($size_get) == 1) {
                                                        $price = $size_get[0]->amount;
                                                        $buy_price = $size_get[0]->amount;
                                                        $size_data = [
                                                            "id" => $size_get[0]->id,
                                                            "name" => $size_get[0]->name,
                                                            "amount" => $size_get[0]->amount
                                                        ];
                                                    } else {
                                                        $price = $product_details->amount;
                                                        $buy_price = $product_details->amount;
                                                    }
                                                }
                                                if ($product_details->discount_amount != Null && $product_details->discount_type != Null) {
                                                    if ($product_details->discount_type == 1) {
                                                        $price = $price - $product_details->discount_amount;
                                                        $discount_amount = $product_details->discount_amount;

                                                        $buy_price = $buy_price - $product_details->discount_amount;
                                                    } elseif ($product_details->discount_type == 2) {
                                                        $discount_amount = round((($price * $product_details->discount_amount) / 100), 2);
                                                        $price = $price - $discount_amount;

                                                        $discount_buy_amount = round((($buy_price * $product_details->discount_amount) / 100), 2);
                                                        $buy_price = $buy_price - $discount_buy_amount;
                                                    }
                                                    else {
                                                        $discount_amount = 0;
                                                    }
                                                }
                                                else {
                                                    $discount_amount = 0;
                                                }

                                                $options = $product_quantity['options'];
                                                $options_data = Null;
                                                $options_data = [];
                                                if ($options != Null) {
                                                    foreach ($options as $option) {
                                                        $options_details = StoreProductOptions::query()->select('store_product_options.id',
                                                            'store_product_options.name',
                                                            'store_product_options.amount',
                                                            'store_product_customize_category.name as category_name')
                                                            ->join('store_product_customize_category', 'store_product_customize_category.id', '=', 'store_product_options.category_id')
                                                            ->where('store_product_options.id', $option)
                                                            ->where('store_product_options.product_id', $product_details->id)->first();
                                                        if ($options_details != Null) {
                                                            $price = $price + $options_details->amount;
                                                            $buy_price = $buy_price + $options_details->amount;
                                                            $options_data[] = [
                                                                'id' => $options_details->id,
                                                                'name' => $options_details->name,
                                                                'amount' => $options_details->amount,
                                                                'category' => $options_details->category_name
                                                            ];
                                                        }
                                                    }
                                                }
                                                $toppings = $product_quantity['toppings'];
                                                $toppings_data = Null;
                                                $toppings_data = [];
                                                if ($toppings != Null) {
                                                    foreach ($toppings as $topping) {
                                                        $toppings_details = StoreProductToppings::query()->select('store_product_toppings.id',
                                                            'store_product_toppings.name',
                                                            'store_product_toppings.amount',
                                                            'store_product_customize_category.name as category_name')
                                                            ->join('store_product_customize_category', 'store_product_customize_category.id', '=', 'store_product_toppings.category_id')
                                                            ->where('store_product_toppings.id', $topping)
                                                            ->where('store_product_toppings.product_id', $product_details->id)->first();
                                                        if ($toppings_details != Null) {
                                                            $price = $price + $toppings_details->amount;
                                                            $buy_price = $buy_price + $toppings_details->amount;
                                                            $toppings_data[] = [
                                                                'id' => $toppings_details->id,
                                                                'name' => $toppings_details->name,
                                                                'amount' => $toppings_details->amount,
                                                                'category' => $toppings_details->category_name
                                                            ];
                                                        }
                                                    }
                                                }

                                                $quantity = $quantity + $product_quantity['quantity'];
                                                $price_for_one = $price;
                                                $price = round($price * $product_quantity['quantity'], 2);

                                                $extra_product_charge = $product_details->product_charge - 0;
                                                if ($product_details->product_charge_type == 1) {
                                                    $extra_product_charge = $extra_product_charge * $product_quantity['quantity'];
                                                }
                                                $total_extra_product_charge = $total_extra_product_charge + $extra_product_charge;

                                                $get_pTax = ($get_product_tax / 100) + 1;
                                                $tax_without_product_amount = ( (($price + $extra_product_charge) / $get_pTax));
                                                $product_tax = ($tax_without_product_amount * $get_product_tax) / 100;

                                                //without vat amount
                                                $total_product_selling_net_amount = $total_product_selling_net_amount + $tax_without_product_amount + $extra_product_charge;



                                                $total_item_cost = round($total_item_cost + $price, 2);
                                                $price = $price + $extra_product_charge;
                                                $total_price = round($total_price + $price, 2);
                                                $product_price[] = round($price, 2);

                                                //buying price
                                                $buy_price = round($buy_price * $product_quantity['quantity'], 2);

                                                $get_buy_pTax = ($get_product_tax / 100) + 1;
                                                $tax_without_buy_product_amount = ( (($buy_price + $extra_product_charge) / $get_buy_pTax));
                                                $product_buy_tax = ($tax_without_buy_product_amount * $get_product_tax) / 100;

                                                $buy_price = $buy_price + $extra_product_charge;





                                                $order_product_list[] = [
                                                    'product_id' => $product_details->id,
                                                    'product_name' => $product_details->name,
                                                    'product_quantity' => $product_quantity['quantity'] - 0,
                                                    'product_category' => $product_category != Null ? $product_category->name : Null,
                                                    'product_amount' => $price_for_one,
                                                    'product_discount' => $discount_amount,

                                                    'food_type' => $product_details->food_type,
                                                    'size' => $size_data,
                                                    'options' => $options_data,
                                                    'toppings' => $toppings_data
                                                ];
                                            }
                                            array_push($order_product_list_string, $product_details->name . ' x ' . $quantity);
                                        }
                                    }
                                    else {
                                        $unavailable_products[] = $product['product_id'];
                                    }
                                } else {
                                    $unavailable_products[] = $product['product_id'];
                                }
                            }
                            //unavailalbe product
                            $unavailable_product_list = implode(',',$unavailable_products);
                            if(count($order_product_list) > 0) {
                                if ($store_details->order_min_amount > $total_item_cost) {
                                    $req_store_order_min_amount = round(($store_details->order_min_amount), 2) - 0;
                                    $next_status = 1;
                                    //$message = "Order min amount " . $req_store_order_min_amount;
                                    //$message = "<b>".$store_name."</b></br>Order min amount <span class='currency'></span>" . $req_store_order_min_amount;
                                    $message = "<b>".$store_name."</b></br> ".__('messages.172')." <span class='currency'></span>" . $req_store_order_min_amount;

                                    $message_code = 127;
                                    $unavailable_store[] = [
                                        "type" => 3,
                                        "store_id" => $store_id - 0,
                                        "product_ids" => "",
                                        "order_min_amount" => $req_store_order_min_amount,
                                        "message" => $message,
                                        "message_code" => $message_code - 0,
                                    ];
                                }
                                if ($next_status != 1) {
                                    if (!empty($unavailable_product_list)) {
                                        $unavailable_store[] = [
                                            "type" => 1,
                                            "store_id" => $store_id - 0,
                                            "product_ids" => !empty($unavailable_product_list) ? $unavailable_product_list : '',
                                            "message" => "<b>".$store_name."</b></br> ".__('messages.173'),
                                            //"message" => "<b>".$store_name."</b></br> The added product is not available at this moment!",
                                            "message_code" => 157,
                                        ];
                                    }
                                    if ($store_details->offer_min_amount != 0 && $store_details->offer_amount != 0 && $store_details->offer_type != 0) {
                                        if ($total_price >= $store_details->offer_min_amount) {
                                            if ($store_details->offer_type == 1) {
                                                $discount_cost = $total_price - (($total_price) - ($store_details->offer_amount));
                                            } else {
                                                $discount_cost = ((($total_price) * ($store_details->offer_amount)) / 100);
                                            }
                                        }
                                    }
                                    elseif ($store_details->offer_min_amount == 0 && $store_details->offer_amount != 0 && $store_details->offer_type != 0) {
                                        if ($store_details->offer_type == 1) {
                                            $discount_cost = $total_price - (($total_price) - ($store_details->offer_amount));
                                        } else {
                                            $discount_cost = ((($total_price) * ($store_details->offer_amount)) / 100);
                                        }
                                    }
                                    else {
                                        $discount_cost = 0;
                                    }

                                    $delivery_charge = 0;
                                    if ($user_taken_type != 2) {
                                        $origin = urlencode($store_details->address_lat . "," . $store_details->address_long);
                                        $get_coordinates = $this->get_coordinates($origin, $address_lat . ',' . $address_long);
                                        $coordinates_check = json_decode($get_coordinates, TRUE);
                                        if ($coordinates_check == 0) {
                                            //Session::flash('error', 'You select address not perfoming map query!');
                                            Session::flash('error', __('messages.174'));
                                            return redirect()->back();
                                        }
                                        $total_distance = 0;
                                        if (count($coordinates_check['routes']) > 0) {
                                            //$total_distance = preg_replace("/[^0-9.]/", "", $coordinates_check['routes'][0]['legs'][0]['distance']['text']);
                                            $total_distance = preg_replace("/[^0-9.]/", "", $coordinates_check['routes'][0]['legs'][0]['distance']['value']);//5000=5km
                                            $total_distance = ($total_distance * 0.001);
                                        }
                                        $delivery_charge = 0;
                                    }

                                    $total_item_cost = round($total_item_cost, 2);
                                    $promo_code_discount = 0;

                                    $item_cost = $total_item_cost;
                                    $promocode_discount = $promo_code_discount;
                                    $packaging_cost = $store_details->packaging_charges;
                                    //$delivery_cost = $delivery_charge + $total_extra_product_charge + $store_fixed_charge + $maintenance_charge + $base_fee;
                                    $delivery_cost = $delivery_charge + $maintenance_charge + $base_fee;
                                    $partner_fee = $total_extra_product_charge + $store_fixed_charge;

                                    $step_1 = $item_cost - $discount_cost;
                                    $step_2 = $step_1 + $packaging_cost;
                                    $step_3 = $step_2 - $promocode_discount;
                                    $step_4 = $step_3 + $delivery_cost + $partner_fee;
                                    $step_5 = 0;
                                    //$step_5 = (($step_4 * $get_tax) / 100);
                                    $step_6 = $step_4 + $step_5;

                                    $order_tax = round($step_5, 2);
                                    $order_total_pay = round($step_6, 2);
                                    //$store_amount = $step_2 + $discount_cost;
                                    //$commission = (($store_amount * $admin_commission) / 100);
                                    //$order_store_amount = round(($store_amount - ($commission) - $discount_cost), 2);
                                    //$order_admin_commission = round(($commission - $promocode_discount), 2);

                                    //new counting
                                    $get_sTax = 0;
                                    $get_sTax = ($get_tax / 100) + 1;
                                    $tax_without_service_amount = ( (($delivery_charge + $base_fee + $store_details->fixed_charge) / $get_sTax));
                                    $service_tax_cost = ($tax_without_service_amount * $get_tax) / 100;
                                    $tax_cost = $service_tax_cost;


                                    $total_sell_pTotal = $total_item_cost + $total_extra_product_charge;
                                    $admin_sell_commission = 0;
                                    $city_manager_commission = 0;


                                    $city_manager_net_amount = 0;
                                    $city_manager_tax_cost = 0;

                                    $driver_admount = 0;
                                    $driver_net_amount = 0;
                                    $driver_tax_cost = 0;

                                    $order_admin_commission = round((($total_extra_product_charge + $admin_sell_commission + $delivery_commission) - $promocode_discount), 2);
                                    $order_store_amount = round((($order_total_pay) - $order_admin_commission - $driver_admount - $city_manager_commission), 2);

                                    $all_total_pay = $all_total_pay + $order_total_pay;
                                    $all_total_item_cost = ($all_total_item_cost + $total_item_cost);
                                    $all_discount_cost = ($all_discount_cost + $discount_cost);

                                    $req_store_ids[] = $store_details->id;
                                    $order_book_array[] = [
                                        "user_id" => $user_id,
                                        "store_id" => $store_details->id,
                                        "service_cat_id" => $get_category->id,
                                        "status" => 1,
                                        "store_name" => $store_details->name,
                                        "user_name" => $user_details['first_name'] . ' ' . $user_details['last_name'],
                                        "delivery_address" => isset($address->address) ? $address->address : "",
                                        "lat_long" => isset($address->lat_long) ? $address->lat_long : "",
                                        "flat_no" => isset($address->flat_no)? $address->flat_no : "",
                                        "landmark" => isset($address->landmark)? $address->landmark : "",
                                        "payment_type" => $payment_type,
                                        "payment_status" => $payment_type == 1 ? 1 : 0,
                                        "driver_algorithm" => $general_settings->driver_algorithm,
                                        "place_order_type" => $place_order_type, //scheduled or now
                                        "place_order_date_time" => $place_order_date_time, //order time scheduled or now
                                        "order_no" => random_int(1, 9) . date('siHYdm') . random_int(1, 9),
                                        "order_product_list" => implode(", ", $order_product_list_string),
                                        //count and store
                                        "store_fixed_charge" => $store_fixed_charge,//1
                                        "total_extra_product_charge" => $total_extra_product_charge,//2
                                        "maintenance_charge" => $maintenance_charge,//3
                                        "base_fee" => $base_fee,//3
                                        "packaging_cost" => $store_details->packaging_charges,
                                        "delivery_cost" => $delivery_charge,

                                        //book counting
                                        "total_item_cost" => $item_cost,
                                        "discount_cost" => $discount_cost,
                                        "promo_code_discount" => $promo_code_discount,
                                        "order_tax" => $tax_cost,
                                        "order_total_pay" => $order_total_pay,
                                        "order_store_amount" => $order_store_amount,

                                        "order_admin_commission" => $order_admin_commission,
                                        //"delivery_cost" => $delivery_cost,
                                        //"packaging_cost" => $packaging_cost,

                                        "total_product_selling_net_amount" => $total_product_selling_net_amount,
                                        "store_order_product_list" => $order_product_list,
                                    ];
                                }
                            }
                            else {
                                //$message = "store currently not available!";
                                //$message = "<b>".$store_name."</b></br> store currently not available!";
                                $message = "<b>".$store_name."</b></br> ".__('messages.170');
                                $message_code = 69;
                                $unavailable_store[] = [
                                    "type" => 2,
                                    "store_id" => $store_id - 0,
                                    "product_ids" => !empty($unavailable_product_list) ? $unavailable_product_list : '',
                                    "message" => $message,
                                    "message_code" => $message_code - 0,
                                ];
                            }
                        }
                    }
                }
            }
            else {
                //$message = "store currently not available!";
                $message = "store currently not available!". __('messages.170');
                $message_code = 69;
                $unavailable_store[] = [
                    "type" => 0,
                    "store_id" => $store_id - 0,
                    "product_ids" => "",
                    "message" => $message,
                    "message_code" => $message_code - 0,
                ];
            }
        }

        //unavailable store list
        //$unavailable_store_list = implode(',', $unavailable_store);
        $unavailable_store_list = $unavailable_store;
        if (count($order_book_array) == 0 || $unavailable_store_list != Null) {
            Session::flash('error', $message);
            return redirect()->back();
        }

        $count_req_store_ids= count($req_store_ids);
        $prescription_images = [];
        $save_prescription = [];

        //count promocode discount
        $promo_discount_cost = 0;
        $add_promocode_id = 0;
        $all_total_item_cost = ($all_total_item_cost);
        $all_discount_cost = round($all_discount_cost, 2);
        if ($promocode_id != 0) {
            $discount_on_amount = (($all_total_item_cost - $all_discount_cost));
            $promo_code = $this->checkPromoCodeValid($promocode_id, $discount_on_amount, $user_details->id, $service_category_id);
            if ($promo_code != 0) {
                if ($promo_code != 1) {
                    $get_prmocode = PromocodeDetails::query()->where('id', $promocode_id)->where('status', 1)->first();
                    if ($get_prmocode != Null) {
                        $add_promocode_details = new UsedPromocodeDetails();
                        $add_promocode_details->service_cat_id = $service_category_id;
                        $add_promocode_details->user_id = $user_id;
                        $add_promocode_details->promocode_id = $promocode_id;
                        $add_promocode_details->promocode_name = $get_prmocode->promo_code;
                        $add_promocode_details->discount_amount = $promo_code;
                        $add_promocode_details->save();
                        $add_promocode_id = $add_promocode_details->id;
                        $promo_discount_cost = $promo_code;
                        //$place_order->promo_code = $add_promocode_details->id;
                        //$place_order->save();
                    }
                }
            }
        }

        $all_total_pay = $all_total_pay - $promo_discount_cost;
        $order_refer_discount = 0;

        //user refer discount count
        if ($user_details->pending_refer_discount > 0) {
            $user = User::query()->where('id', $user_details->id)->first();
            if ($user != Null) {
                $user_refer_history = UserReferHistory::query()->where('user_id', $user_details->id)->where('user_status', 0)->first();
                $total_price = $all_total_pay;
                if ($user_refer_history != Null) {
                    if ($user_refer_history->user_discount_type == 1) {
                        $refer_discount_price = round($total_price - $user_refer_history->user_discount, 2);
                        $refer_discount_amount = round($user_refer_history->user_discount, 2);
                    } else {
                        $refer_discount_price = round((($total_price * $user_refer_history->user_discount) / 100), 2);
                        $refer_discount_amount = round($refer_discount_price, 2);
                        $refer_discount_price = round($total_price - $refer_discount_amount, 2);
                    }
                    if ($refer_discount_price < 0) {
                        $refer_discount_price = 0;
                    }
                    $all_total_pay = $refer_discount_price;
                    $order_refer_discount = round($refer_discount_amount, 2);
                    $user_refer_history->user_status = 1;
                    $user_refer_history->save();
                    $user->pending_refer_discount = $user->pending_refer_discount - 1;
                    $user->save();
                } else {
                    $user_refer_history = UserReferHistory::query()->where('refer_id', $user_details->id)->where('refer_status', 0)->first();
                    if ($user_refer_history != Null) {
                        if ($user_refer_history->refer_discount_type == 1) {
                            $refer_discount_price = round($total_price - $user_refer_history->refer_discount, 2);
                            $refer_discount_amount = round($user_refer_history->refer_discount, 2);
                        } else {
                            $refer_discount_price = round((($total_price * $user_refer_history->refer_discount) / 100), 2);
                            $refer_discount_amount = round($refer_discount_price, 2);
                            $refer_discount_price = round($total_price - $refer_discount_amount, 2);
                        }
                        if ($refer_discount_price < 0) {
                            $refer_discount_price = 0;
                        }
                        $all_total_pay = $refer_discount_price;
                        $order_refer_discount = round($refer_discount_amount, 2);
                        $user_refer_history->refer_status = 1;
                        $user_refer_history->save();
                        $user->pending_refer_discount = $user->pending_refer_discount - 1;
                        $user->save();
                    }
                }
            }
        }

        $req_store_id_list = implode(',', $req_store_ids);
        //order book
//        $order_book = new UserStoreOrderBooking();
//        $order_book->user_id = $user_id;
//        $order_book->store_ids = $req_store_id_list;
//        $order_book->user_name = $user_details['first_name'] . ' ' . $user_details['last_name'];
//        $order_book->service_cat_id = $get_category->id;
//        $order_book->order_no = random_int(1, 9) . date('siHYdm') . random_int(1, 9);
//        $order_book->city_id = $store_city_id;
//        $order_book->order_date_time = $place_order_date_time;
//        $order_book->refer_discount = $order_refer_discount;
//        $order_book->total_pay = $all_total_pay;
//        $order_book->total_item_cost = round($all_total_item_cost,2);
//        $order_book->promo_code = $add_promocode_id;
//        $order_book->payment_type = $payment_type;
//        $order_book->payment_method_type = $payment_method_type;
//        $order_book->save();

        //book order on store wise

        $user_product_booking_ids = [];
        foreach($order_book_array as $key => $order_book_details) {
            //book order on store wise
            $place_order = new UserProductBooking();
            $place_order->order_no = $order_book_details['order_no'];
            $place_order->user_id = $user_id;
            $place_order->store_id = $order_book_details['store_id'];
            $place_order->service_cat_id = $get_category->id;
            $place_order->packaging_cost = $order_book_details['packaging_cost'];
            $place_order->status = 1;
            $place_order->store_name = $order_book_details['store_name'];
            $place_order->user_name = $order_book_details['user_name'];
            $place_order->delivery_cost = $order_book_details['delivery_cost'];
            if ($user_taken_type != 2) {
                $place_order->delivery_address = $order_book_details['delivery_address'];
                $place_order->lat_long = $order_book_details['lat_long'];
                $place_order->flat_no = $order_book_details['flat_no'];
                $place_order->landmark = $order_book_details['landmark'];
            }
            $place_order->payment_type = $order_book_details['payment_type'];
            $place_order->payment_status = $order_book_details['payment_status'];
            $place_order->taken_away_type = $user_taken_type;
            $place_order->order_type = $order_book_details['place_order_type'];
            $place_order->order_date_time = $order_book_details['place_order_date_time'];
            $place_order->order_product_list = $order_book_details['order_product_list'];

            $place_order->total_item_cost = $order_book_details['total_item_cost'];
            $place_order->discount_cost = $order_book_details['discount_cost'];
            $place_order->tax_cost = $order_book_details['order_tax'];
            $place_order->total_pay = $order_book_details['order_total_pay'];
            $place_order->store_amount = $order_book_details['order_store_amount'];
            $place_order->admin_commission = $order_book_details['order_admin_commission'];

            $place_order->promo_code = $add_promocode_id;
            $place_order->refer_discount = $order_refer_discount;
            $place_order->save();

            $user_product_booking_ids[] = $place_order->id;

            $store_order_product_list = $order_book_details['store_order_product_list'];

            if ($store_order_product_list != Null) {
                foreach ($store_order_product_list as $product) {
                    $add_product = new UserProductBookingQuantity();
                    $add_product->order_id = $place_order->id;
                    $add_product->product_id = $product['product_id'];
                    $add_product->product_name = $product['product_name'];
                    $add_product->product_cat_name = $product['product_category'];
                    $add_product->num_of_items = $product['product_quantity'];
                    $add_product->price_for_one = $product['product_amount'];
                    /*$add_product->food_type = $product['food_type'];*/
                    /*$add_product->product_charge_type = $product['product_charge_type'];
                    $add_product->extra_product_charge = $product['extra_product_charge'];*/

                    /*$add_product->get_product_tax = $product['get_product_tax'];
                    $add_product->product_buy_tax = $product['product_buy_tax'];
                    $add_product->product_tax = $product['product_tax'];*/
                    $add_product->save();
                    if ($product['size'] != Null) {
                        $product_size = new UserProductBookingSize();
                        $product_size->order_id = $place_order->id;
                        $product_size->product_id = $add_product->id;
                        $product_size->name = $product['size']['name'];
                        $product_size->amount = ($product['size']['amount'] > 0 )?$product['size']['amount']:0;
                        $product_size->save();
                    }
                    if ($product['options'] != Null) {
                        foreach ($product['options'] as $options) {
                            $product_option = new UserProductBookingOptions();
                            $product_option->order_id = $place_order->id;
                            $product_option->product_id = $add_product->id;
                            $product_option->name = $options['name'];
                            $product_option->category = $options['category'];
                            $product_option->amount = $options['amount'];
                            $product_option->save();
                        }
                    }
                    if ($product['toppings'] != Null) {
                        foreach ($product['toppings'] as $toppings) {
                            $product_option = new UserProductBookingToppings();
                            $product_option->order_id = $place_order->id;
                            $product_option->product_id = $add_product->id;
                            $product_option->name = $toppings['name'];
                            $product_option->category = $toppings['category'];
                            $product_option->amount = $toppings['amount'];
                            $product_option->save();
                        }
                    }
                }
            }

            $product_status = new UserProductBookingStatus();
            $product_status->order_id = $place_order->id;
            $product_status->status = 1;
            $product_status->save();
        }

        $is_send_store_notification = 0;
        if ($place_order->payment_type == 3) {
            $wallet_balance = UserWalletTransaction::query()->where('user_id', $user_id)->orderBy('id', 'desc')->first();
            if ($wallet_balance != Null) {
                $balance = $wallet_balance->remaining_balance;
            } else {
                $balance = 0;
            }
            if ($place_order->total_pay > $balance) {
                foreach ($user_product_booking_ids as $key => $product_booking_id){
                    $placed_order = UserProductBooking::query()->where('id', $product_booking_id)->first();
                    if ($placed_order != Null){
                        $placed_order->status = 10;
                        $placed_order->save();
                    }
                }
                $place_order->order_status = 10;
                $place_order->save();

                //Session::flash('error', "You can't pay order amount through Wallet because your wallet balance is insufficient.");
                Session::flash('error', "".__('messages.175'));
                return redirect()->back();
            }
            $add_balance = new UserWalletTransaction();
            $add_balance->user_id = $user_id;
            $add_balance->transaction_type = 2;
            $add_balance->amount = $place_order->total_pay;
            $add_balance->order_no = $place_order->order_no;
            $add_balance->subject_code = 3;
            $add_balance->subject = "Paid To Store - Order #" . $place_order->order_no;
            $add_balance->remaining_balance = round($balance - $place_order->total_pay, 2);
            $add_balance->save();
            $place_order->payment_type = $payment_type;
            $place_order->payment_status = 1;
            $place_order->save();
        }

        if ($place_order->payment_type == 2) {

            if ($payment_method_type > 0){
                $user_currency = WorldCurrency::query()->where('symbol', $user_details->currency)->first();
                if ($user_currency == Null) {
                    $user_currency = WorldCurrency::query()->where('default_currency', 1)->first();
                }
                $currency = $user_currency != Null ? $user_currency->ratio : 1;
                $currency_code = $user_currency != Null ? $user_currency->currency_code : '';
                $amount = round($place_order->total_pay * $currency, 2);
                if ($payment_method_type == 1){
                    //stripe
                    if ($is_stripe_sandbox > 0 ) {
                        try {
                            Stripe::setApiKey($stripe_secret_key);
                            $charge = Charge::create([
                                'amount' => round($amount * 100),
                                'currency' => $currency_code . '',
                                //'currency' => "USD",
                                'description' => "Order Id #" . $place_order->order_no . ' ' . $place_order->user_name,
                                //'source' => $stripeToken->id,
                                'customer' => $cus_token. "",
                            ]);

                            if (isset($charge->paid) && isset($charge->status)) {
                                if ($charge->status == "succeeded" && $charge->paid == true) {
                                    $is_payment = 1;
                                } else {
                                    $place_order->order_status = 10;
                                    $place_order->save();
                                    foreach ($user_product_booking_ids as $key => $product_booking_id){
                                        $placed_order = UserProductBooking::query()->where('id', $product_booking_id)->first();
                                        if ($placed_order != Null){
                                            $placed_order->status = 10;
                                            $placed_order->save();
                                        }
                                    }
                                    //Session::flash('error', "your payment transaction failed");
                                    Session::flash('error', __('messages.176'));
                                    return redirect()->back();
                                }
                            } else {
                                $place_order->order_status = 10;
                                $place_order->save();
                                foreach ($user_product_booking_ids as $key => $product_booking_id){
                                    $placed_order = UserProductBooking::query()->where('id', $product_booking_id)->first();
                                    if ($placed_order != Null){
                                        $placed_order->status = 10;
                                        $placed_order->save();
                                    }
                                }
                                Session::flash('error', __('messages.176'));
                                return redirect()->back();
                            }
                        }
                        catch (\Exception $e) {
                            $place_order->order_status = 10;
                            $place_order->cancel_reason = $e->getMessage();
                            $place_order->save();
                            foreach ($user_product_booking_ids as $key => $product_booking_id){
                                $placed_order = UserProductBooking::query()->where('id', $product_booking_id)->first();
                                if ($placed_order != Null){
                                    $placed_order->status = 10;
                                    $placed_order->cancel_reason = $e->getMessage();
                                    $placed_order->save();
                                }
                            }
                            Session::flash('error', $e->getMessage());
                            return redirect()->back();
                        }
                    }
                    else {
                        $is_payment = 1;
                    }
                    if ($is_payment == 1){
                        foreach ($user_product_booking_ids as $key => $product_booking_id){
                            $placed_order = UserProductBooking::query()->where('id', $product_booking_id)->first();
                            if ($placed_order != Null){
                                $placed_order->payment_status = 1;
                                $placed_order->save();
                            }
                        }
                        $place_order->payment_status = 1;
                        $place_order->order_status = 1;
                        $place_order->save();
                    }
                }
                elseif ($payment_method_type == 2){
                    if ($is_mb_way_sandbox > 0 ) {
                        try {
                            $order_id = $place_order->order_no - 0;
                            $mb_way_request = [
                                "chave" => $mb_way_key."",
                                "valor" => $amount ."",
                                "id" => $order_id,
                                "alias" => $mb_way_payment_contact_number."",
                                "descricao" => "Deliking pedido realizado por: ".$place_order->user_name." not " .$amount,
                                "campos_extra" => [
                                    "id"=> 20,
                                    "valor"=> $amount. ""
                                ]
                            ];

                            $headers = [
                                'Content-Type: application/json'
                            ];
                            $ch = curl_init();
                            curl_setopt($ch, CURLOPT_URL, $mb_way_url);
                            curl_setopt($ch, CURLOPT_POST, true);
                            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($mb_way_request));
                            $result = curl_exec($ch);
                            curl_close($ch);
                            $response = json_decode($result, TRUE);
                            if (isset($response['sucesso'])) {
                                if ($response['sucesso'] == true && $response['resposta'] == "OK") {

                                    $tempUserBooking = New TempUserBooking();
                                    $tempUserBooking->user_id = $user_details['id'];
                                    $tempUserBooking->service_cat_id = $place_order->service_cat_id;
                                    $tempUserBooking->booking_id = $order_id;
                                    $tempUserBooking->transaction_id = "";
                                    $tempUserBooking->refno = $response['referencia']."";
                                    $tempUserBooking->amount = round($amount, 2);
                                    $tempUserBooking->payment_method_type = 2;
                                    $tempUserBooking->payment_status = 0;
                                    $tempUserBooking->coupon_ids = 0;
                                    $tempUserBooking->save();

                                    foreach ($user_product_booking_ids as $key => $product_booking_id){
                                        $placed_order = UserProductBooking::query()->where('id', $product_booking_id)->first();
                                        if ($placed_order != Null){
                                            $placed_order->status = 11;
                                            $placed_order->save();
                                        }
                                    }
                                    $place_order->order_status = 11;
                                    $place_order->save();

                                    $is_send_store_notification = 1;

                                }
                                else {
                                    $place_order->order_status = 10;
                                    $place_order->save();
                                    foreach ($user_product_booking_ids as $key => $product_booking_id) {
                                        $placed_order = UserProductBooking::query()->where('id', $product_booking_id)->first();
                                        if ($placed_order != Null) {
                                            $placed_order->status = 10;
                                            $placed_order->save();
                                        }
                                    }
                                    //Session::flash('error', "your payment transaction failed");
                                    Session::flash('error', __('messages.176'));
                                    return redirect()->back();
                                }
                            }
                            else {
                                $place_order->order_status = 10;
                                $place_order->save();
                                foreach ($user_product_booking_ids as $key => $product_booking_id) {
                                    $placed_order = UserProductBooking::query()->where('id', $product_booking_id)->first();
                                    if ($placed_order != Null) {
                                        $placed_order->status = 10;
                                        $placed_order->save();
                                    }
                                }
                                Session::flash('error', __('messages.176'));
                                return redirect()->back();
                            }
                        }
                        catch (\Exception $e) {
                            $place_order->order_status = 10;
                            $place_order->cancel_reason = $e->getMessage();
                            $place_order->save();
                            foreach ($user_product_booking_ids as $key => $product_booking_id){
                                $placed_order = UserProductBooking::query()->where('id', $product_booking_id)->first();
                                if ($placed_order != Null){
                                    $placed_order->status = 10;
                                    $placed_order->cancel_reason = $e->getMessage();
                                    $placed_order->save();
                                }
                            }
                            Session::flash('error', $e->getMessage());
                            return redirect()->back();
                        }
                    }
                    else {
                        foreach ($user_product_booking_ids as $key => $product_booking_id){
                            $placed_order = UserProductBooking::query()->where('id', $product_booking_id)->first();
                            if ($placed_order != Null){
                                $placed_order->payment_status = 1;
                                $placed_order->save();
                            }
                        }
                        $place_order->payment_status = 1;
                        $place_order->order_status = 1;
                        $place_order->save();
                    }
                }
                else {
                    $place_order->order_status = 10;
                    $place_order->save();
                    foreach ($user_product_booking_ids as $key => $product_booking_id){
                        $placed_order = UserProductBooking::query()->where('id', $product_booking_id)->first();
                        if ($placed_order != Null){
                            $placed_order->status = 10;
                            $placed_order->save();
                        }
                    }
                    //Session::flash('error', "invalid payment method select");
                    Session::flash('error', __('messages.166'));
                    return redirect()->back();
                }
            }
            else {
                foreach ($user_product_booking_ids as $key => $product_booking_id){
                    $placed_order = UserProductBooking::query()->where('id', $product_booking_id)->first();
                    if ($placed_order != Null){
                        $placed_order->payment_status = 1;
                        $placed_order->save();
                    }
                }
                $place_order->payment_status = 1;
                $place_order->order_status = 1;
                $place_order->save();
            }
        }

        if ($is_send_store_notification != 1){
            foreach ($user_product_booking_ids as $key => $product_booking_id) {
                $placed_order = UserProductBooking::query()->where('id', $product_booking_id)->first();
                if ($placed_order != Null) {
                    $store_provider_details = Provider::query()->select('providers.device_token', 'providers.login_device', 'providers.email', 'providers.language')
                        ->join('provider_services', 'provider_services.provider_id', '=', 'providers.id')
                        ->join('store_details', 'store_details.provider_service_id', '=', 'provider_services.id')
                        ->where('store_details.id', $placed_order->store_id)
                        ->whereNotNull('providers.device_token')
                        ->whereNotNull('providers.access_token')
                        ->whereNull('providers.deleted_at')
                        ->first();
                    if ($store_provider_details != Null) {
                        $this->notificationClass->storeDeliveryUserNotification($placed_order->id, $store_provider_details->device_token, $placed_order->status, $store_provider_details->login_device, $store_provider_details->language);
                    }
                }
            }
        }


        $order_cart = UserOrderCart::query()->where('user_id',$user_id)->where('order_add_by',$order_add_by)->delete();
        //Session::flash('success', 'Order created successfully!');
        Session::flash('success', __('messages.177'));
        if($order_add_by == 1)
        {
            return redirect()->route('get:user_orders_all',['delivery','all']);
        } else {
            return redirect()->back();
        }
    }

    public function getFareHtml($total_distance=0 ,$total_time_string="",$get_vehicle_price_cost_for_km=0,$total_distance_price=0,$get_vehicle_price_min_fare_amount=0,
                                $get_vehicle_price_time_fare=0,$total_min_price=0,$base_fare=0,$adjustment_amount=0,$sub_total=0,$total_tax=0,$total=0){
        $fare_html ="";
        $fare_html .= '<div class="col-12">
                                <div class="padding-10">
                                </div>
                                <div class="faremainSetion pt-3">';

        if($total_distance > 0) {

            $fare_html .= '<div class="col-12 fareLabel">
                                            <label class="labelFld">Distance Travel</label>
                                            <label class="labelvalue">' . $total_distance . ' Km</label>
                                    </div>';
        }
        if($total_time_string != "")
        {
            $fare_html .= '<div class="col-12 fareLabel">
                                            <label class="labelFld">Taken Time</label>
                                            <label class="labelvalue">'.$total_time_string ." approx.".'</label>
                        </div>';
        }
        if($get_vehicle_price_cost_for_km >= 0)
        {
            $fare_html .= '<div class="col-12 fareLabel">
                                            <label class="labelFld">Cost Per Km</label>
                                            <label class="labelvalue currency">'.$get_vehicle_price_cost_for_km.'</label>
                        </div>';
        }

        if($total_distance_price >= 0)
        {
            $fare_html .= '<div class="col-12 fareLabel">
                                            <label class="labelFld">Distance Fare</label>
                                            <label class="labelvalue currency">'.$total_distance_price.'</label>
                        </div>';
        }
        if($get_vehicle_price_min_fare_amount > 0)
        {
            $fare_html .= '<div class="col-12 fareLabel">
                                            <label class="labelFld">Time Fare ( <span class="currency">'.$get_vehicle_price_time_fare.'</span> / 1 Min)</label>
                                            <label class="labelvalue currency">'.$total_min_price.'</label>
                        </div>';
        }
        if($base_fare >= 0)
        {
            $cls = "";
            if($adjustment_amount == 0){
                $cls = "borderBtmCls";
            }
            $fare_html .= '<div class="col-12 fareLabel '.$cls.' ">
                                            <label class="labelFld">Base Fare</label>
                                            <label class="labelvalue currency">'.$base_fare.'</label>
                        </div>';
        }
        if($adjustment_amount > 0)
        {
            $fare_html .= '<div class="col-12 fareLabel borderBtmCls">
                                            <label class="labelFld">Adjustment Amount</label>
                                            <label class="labelvalue currency">'.$adjustment_amount.'</label>
                        </div>';
        }
        if($sub_total >= 0)
        {
            $fare_html .= '<div class="col-12 fareLabel ">
                                            <label class="labelFld">Sub Total</label>
                                            <label class="labelvalue currency">'.$sub_total.'</label>
                        </div>';
        }
        if($total_tax >= 0)
        {
            $fare_html .= '<div class="col-12 fareLabel borderBtmCls">
                                            <label class="labelFld">Tax</label>
                                            <label class="labelvalue currency">'.$total_tax.'</label>
                        </div>';
        }
        if($total >= 0){
            $fare_html .= '<div class="col-12 fareLabel  font-weight-bold">
                                        <label class="labelFld">Total</label>
                                        <label class="labelvalue currency">'.$total.'</label>
                                    </div>';
        }

        $fare_html .='</div></div>';
        return $fare_html;
    }

    public function checkServiceAvailableLocationNew($current_lat, $current_long)
    {
        $address_lat = trim($current_lat);
        $address_long = trim($current_long);
        $area_details = RestrictedArea::query()->where('status',1)->get();
        $latitudes = $longitudes = '';
        foreach ($area_details as $area){
            $latitudes = $latitudes . $area->latitude . ',';
            $longitudes = $longitudes . $area->longitude . ',';
        }
        $restricted_lat = explode(',',substr($latitudes, 0, -1));
        $restricted_long = explode(',',substr($longitudes, 0, -1));
        $points_polygon = count($restricted_lat);
        $in_Rest = 0;
        if($this->adminClassApi->is_in_restricted_area($points_polygon,$restricted_lat,$restricted_long,$address_lat,$address_long)){
            $in_Rest = 1;
        }
        return $in_Rest;
    }


    public static function dateConvertTimezonewise($datetime="")
    {

        if ($datetime != "") {
            $timezone = (Session::get('timezone') != "") ? Session::get('timezone') : "";
            if ($timezone != "") {
                $date = new DateTime();
                $date = new DateTime($datetime, new DateTimeZone("UTC"));
                $date->setTimezone(new DateTimeZone($timezone));
                $time = $date->format('Y-m-d H:i:s');
                $date->setTimezone(new DateTimeZone("UTC"));
                return $time;
            } else {
                return $datetime;
            }
        } else {
            return now();
        }
    }
    //0:user,1:store,2:driver,3:provider
    public function manageCardList($card_provider_type, $card_user_id){
        $card_details = UserCardDetails::query()->select('card_details.id as card_id', 'card_details.holder_name as card_holder_name', 'card_details.card_number as card_number')
            ->where('user_id', "=", $card_user_id)
            ->where('card_provider_type', "=", $card_provider_type)
            ->get();
        return response()->json([
            "status" => 1,
//            "message" => "success!",
            'message' => __('user_messages.1'),
            "message_code" => 1,
            "card_list" => $card_details,
        ]);
    }

    public function addCardManage($card_provider_type, $card_user_id, $request_details){
        $card_details = new UserCardDetails();
        $card_details->user_id = $card_user_id;
        $card_details->card_provider_type = $card_provider_type;
        $card_details->holder_name = $request_details['holder_name'];
        $card_details->card_number = $request_details['card_number'];
        $card_details->month = $request_details['month'];
        $card_details->year = $request_details['year'];
        $card_details->cvv = $request_details['cvv'];
        $card_details->save();

        $month = (($card_details->month == Null) ? Null: round($card_details->month));
        $year = (($card_details->year == Null) ? Null : round($card_details->year));
        $cvv = (($card_details->cvv == Null) ? Null : round($card_details->cvv));
        return response()->json([
            "status" => 1,
//            "message" => "success!",
            'message' => __('user_messages.1'),
            "message_code" => 1,
            "holder_name" => $card_details->holder_name,
            "card_number" => $card_details->card_number,
            "month" => $month,
            "year" => $year,
            "cvv" => $cvv,
        ]);
    }

    public function deleteCardManage($card_provider_type, $card_user_id, $card_id){
        UserCardDetails::query()
            ->where('card_provider_type', "=", $card_provider_type)
            ->where('user_id', "=", $card_user_id)
            ->where('id', "=" , $card_id)
            ->delete();
        return response()->json([
            "status" => 1,
//            "message" => "success!",
            'message' => __('user_messages.1'),
            "message_code" => 1,
        ]);
    }

    public function addWalletBalance($provider_type, $provider_id, $wallet_holder_name, $currency, $request_details){
        $provider_currency = WorldCurrency::query()->where('symbol', $currency)->first();
        if ($provider_currency == Null) {
            $provider_currency = WorldCurrency::query()->where('default_currency', 1)->first();
        }
        $currency = $provider_currency != Null ? $provider_currency->ratio : 1;
        $currency_code = $provider_currency != Null ? $provider_currency->currency_code : '';

        $amount_to_default = round($request_details['amount'] / $currency, 2);
        $settings =  request()->get("general_settings");

        $redirect_url = ""; $success_url = ""; $failed_url = ""; $fail_url = "";

        try {
            $get_last_transaction = UserWalletTransaction::query()
                ->where('wallet_provider_type', "=", $provider_type)
                ->where('user_id', "=", $provider_id)
                ->orderBy('id', 'desc')
                ->first();
            if ($get_last_transaction != Null) {
                $last_amount = $get_last_transaction->remaining_balance;
            } else {
                $last_amount = 0;
            }
        } catch (\Exception $e) {
            $last_amount = 0;
        }

        if ($request_details['amount'] > 0){
            $user_id = $provider_id;
            $payment_type = 1;
            //enter entry in temp_user_wallet
            $order_no = random_int(1, 9) . date('siHYdm') . random_int(1, 9);

            $temp_user_wallet = New TempUserWallet();
            $temp_user_wallet->user_id = $user_id;
            $temp_user_wallet->trans_id = 0;
            $temp_user_wallet->order_no = $order_no;
            $temp_user_wallet->total_pay = $amount_to_default;
            $temp_user_wallet->payment_status = 0;
            $temp_user_wallet->payment_type = 1;
            $temp_user_wallet->save();

            if ($settings->is_stripe_sandbox > 0) {
                //using stripe payment

                $service_cat_id = 0;
                $booking_id = random_int(1000000000,9999999999)."";
                $tempUserBooking = New TempUserBooking();
                $tempUserBooking->user_id = $provider_id;
                $tempUserBooking->service_cat_id = $service_cat_id;
                $tempUserBooking->booking_id = $order_no;
                $tempUserBooking->transaction_id = $booking_id;
                $tempUserBooking->transaction_type = 1;
                $tempUserBooking->user_type = $provider_type;
                $tempUserBooking->amount = round($amount_to_default, 2);
//                $tempUserBooking->payment_method_type = 2;
                $tempUserBooking->payment_status = 0;
                $tempUserBooking->resp_code = 000;
                $tempUserBooking->coupon_ids = "";
                $tempUserBooking->description = "add amount in wallet :".$amount_to_default;
                $tempUserBooking->save();

                $payment_controller = new StripeController();
                $redirect_url = $payment_controller->paymentStripe($request_details['amount'],$currency_code,"wallet transfer",$booking_id);
                if ($decoded['status'] = json_decode($redirect_url) == false) {
                    return $redirect_url;
                }
                extract(json_decode($redirect_url,true));
                $redirect_url = $redirect_url."";
                $success_url = route('pay.success');
                $fail_url = route('pay.fail');
            } else {
                $temp_user_wallet->payment_status = 1;
                $temp_user_wallet->save();

                $add_balance = new UserWalletTransaction();
                $add_balance->user_id = $provider_id;
                $add_balance->wallet_provider_type = $provider_type;
                $add_balance->transaction_type = 1;
                $add_balance->amount = $amount_to_default;
                $add_balance->order_no = $wallet_holder_name;
                //$add_balance->request_amount = $this->>commanClass->bcdivfunction($request->get('amount'));
                $add_balance->subject = "credit by " . $wallet_holder_name;
                $add_balance->subject_code = 1;
                $add_balance->remaining_balance = floatval($last_amount + $amount_to_default);
                $add_balance->save();
                $last_amount = $add_balance->remaining_balance;
            }
        }

        return response()->json([
            "status" => 1,
//            "message" => "success!",
            'message' => __('user_messages.1'),
            "message_code" => 1,
            "wallet_balance" => round($last_amount * $currency, 2),
            "redirect_url" => $redirect_url,
            "success_url" => $success_url,
            "failed_url" => $fail_url,
            "error_url" => ""
        ]);
    }

    public function getWalletTransactionList($provider_type, $provider_id, $currency, $user_language = 'en'){
        $user_currency = WorldCurrency::query()->where('symbol', $currency)->first();
        if ($user_currency == Null) {
            $user_currency = WorldCurrency::query()->where('default_currency', 1)->first();
        }
        $currency = $user_currency != Null ? $user_currency->ratio : 1;

        $walletTransaction = config('wallettransaction.'.$user_language);

        $transactions_list = UserWalletTransaction::query()->select(
            'user_wallet_transaction.id',
            DB::raw('ROUND(user_wallet_transaction.amount * ' . $currency . ',2) As amount'),
            'user_wallet_transaction.transaction_type',
            'user_wallet_transaction.subject',
            'user_wallet_transaction.subject_code',
            'user_wallet_transaction.order_no',
            'user_wallet_transaction.remaining_balance' ,
            'user_wallet_transaction.created_at as date_time'
        )
            ->where('wallet_provider_type', "=", $provider_type)
            ->where('user_id', "=", $provider_id)
            ->orderBy('id', 'desc')
            ->get();

        $transactions = [];
        foreach ($transactions_list as $key => $transactions_details){
            $transactions[] = [
                "id" => $transactions_details->id,
                "amount" => $transactions_details->amount,
                "transaction_type" => $transactions_details->transaction_type,
                "subject" => $transactions_details->subject_code == null ? $transactions_details->subject : $walletTransaction[$transactions_details->subject_code]." ".$transactions_details->order_no,
                "remaining_balance" => $transactions_details->remaining_balance,
                "date_time" => $transactions_details->date_time,
            ];
        }

        return response()->json([
            "status" => 1,
//            "message" => "success!",
            'message' => __('user_messages.1'),
            "message_code" => 1,
            "transactions" => $transactions
        ]);
    }

    public function getWalletBalance($provider_type, $provider_id, $currency){
        $user_currency = WorldCurrency::query()->where('symbol', $currency)->first();
        if ($user_currency == Null) {
            $user_currency = WorldCurrency::query()->where('default_currency', 1)->first();
        }
        $currency = $user_currency != Null ? $user_currency->ratio : 1;
        $wallet_balance = UserWalletTransaction::query()->where('wallet_provider_type', '=', $provider_type)->where('user_id', '=', $provider_id)->orderBy('id', 'desc')->first();
        if ($wallet_balance != Null) {
            $balance = round($wallet_balance->remaining_balance * $currency, 2);
        } else {
            $balance = 0;
        }
        return response()->json([
            "status" => 1,
//            "message" => "success!",
            'message' => __('user_messages.1'),
            "message_code" => 1,
            "wallet_balance" => $balance
        ]);
    }

    public function searchWalletTransferUserList($provider_type, $provider_id, $search){

        $transfer_user_list = [];
        $user_profile_url = url('/assets/images/profile-images/customer');
        $user_list = User::query()
            ->select("users.id as transfer_id", "users.first_name as name", "users.email", DB::raw("(CASE WHEN users.avatar != '' THEN (CASE WHEN CHAR_LENGTH(users.avatar) >= 25 THEN users.avatar ELSE concat('$user_profile_url','/',users.avatar) END) ELSE '' END) as profile_image"),
                "users.country_code as country_code", "users.contact_number as contact_number",
                DB::raw($this->user_type.' AS wallet_provider_type'))
            ->where('users.status', "=", 1)->whereNull('users.deleted_at');
        if ($provider_type == $this->user_type) {
            $user_list->where('users.id', "!=", $provider_id);
        }
        $user_list = $user_list->where(function ($query) use ($search) {
            $query->orWhere('users.email', 'LIKE', "%{$search}%");
            $query->orWhere(DB::raw('CONCAT_WS("", users.country_code, users.contact_number)'), 'LIKE', '%' . $search . '%');
        })
            ->groupBy('users.id')
            ->get()->toArray();

        $provider_profile_url = url('/assets/images/profile-images/provider');
        $provider_list = Provider::query()
            ->select("providers.id as transfer_id", "providers.first_name as name", "providers.email", DB::raw("(CASE WHEN providers.avatar != '' THEN (CASE WHEN CHAR_LENGTH(providers.avatar) >= 25 THEN providers.avatar ELSE concat('$provider_profile_url','/',providers.avatar) END) ELSE '' END) as profile_image"), "providers.country_code as country_code", "providers.contact_number as contact_number",
                DB::raw('(CASE WHEN service_category.category_type IN (1,5) THEN '.$this->driver_type.' WHEN service_category.category_type IN (2) THEN '.$this->store_type.' WHEN service_category.category_type IN (3,4) THEN '.$this->provider_type.' ELSE '.$this->user_type.' END) AS wallet_provider_type')
            )
            ->join('provider_services', 'provider_services.provider_id', '=', 'providers.id')
            ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
            ->where('providers.status', '=', 1)
            ->where('provider_services.status', '=', 1);
        if ($provider_type == $this->provider_type) {
            $provider_list->where('providers.id', "!=", $provider_id);
        }
        $provider_list = $provider_list->where(function ($query) use ($search) {
            $query->orWhere('providers.email', 'LIKE', "%{$search}%");
            $query->orWhere(DB::raw('CONCAT_WS("", providers.country_code, providers.contact_number)'), 'LIKE', '%' . $search . '%');
        })
            ->groupBy('providers.id')
            ->get()->toArray();

        $transfer_user_list = array_merge($transfer_user_list,$user_list);
        $transfer_user_list = array_merge($transfer_user_list,$provider_list);
        return response()->json([
            "status" => 1,
//            "message" => "success!",
            'message' => __('user_messages.1'),
            "message_code" => 1,
            "transfer_user_list" => $transfer_user_list
        ]);
    }

    public function walletToWalletTransfer($provider_type, $provider_id, $wallet_holder_name, $currency, $request_details){

        $provider_currency = WorldCurrency::query()->where('symbol', $currency)->first();
        if ($provider_currency == Null) {
            $provider_currency = WorldCurrency::query()->where('default_currency', 1)->first();
        }
        $currency = $provider_currency != Null ? $provider_currency->ratio : 1;
        $currency_code = $provider_currency != Null ? $provider_currency->currency_code : '';

        $amount_to_default = round($request_details['amount'] / $currency, 2);
        try {
            $get_last_transaction = UserWalletTransaction::query()
                ->where('wallet_provider_type', "=", $provider_type)
                ->where('user_id', "=", $provider_id)
                ->orderBy('id', 'desc')
                ->first();
            if ($get_last_transaction != Null) {
                $last_amount = $get_last_transaction->remaining_balance;
            } else {
                $last_amount = 0;
            }
        } catch (\Exception $e) {
            $last_amount = 0;
        }
        if ($request_details['amount'] > 0) {
            if ($amount_to_default > $last_amount) {
                return response()->json([
                    "status" => 0,
//                    "message" => "You can't transfer amount through Wallet because your wallet balance is insufficient.",
                    'message' => __('user_messages.110'),
                    "message_code" => 110
                ]);
            }
            $transfer_id = $request_details['transfer_id'];
            $wallet_provider_type = $request_details['wallet_provider_type'];


            if (in_array($wallet_provider_type, [$this->store_type, $this->driver_type, $this->provider_type])) {
                $transfer_provider_user_details = Provider::query()
                    ->select("providers.id as id", "providers.first_name as name", "providers.device_token as device_token", "providers.login_device as login_device","providers.language")
                    ->join('provider_services', 'provider_services.provider_id', '=', 'providers.id')
                    ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
                    ->where('providers.status', '=', 1)
                    ->where('provider_services.status', '=', 1)
                    ->where('providers.id', "=", $transfer_id)
                    ->whereNull('providers.deleted_at')
                    ->first();

                if ($wallet_provider_type == $this->store_type) {
                    $title = __('store_messages.262',[],$transfer_provider_user_details->language);
                    $message = __('store_messages.263',[],$transfer_provider_user_details->language);
                } elseif ($wallet_provider_type == $this->driver_type) {
                    $title = __('driver_messages.262',[],$transfer_provider_user_details->language);
                    $message = __('driver_messages.263',[],$transfer_provider_user_details->language);
                } else {
                    $title = __('provider_messages.262',[],$transfer_provider_user_details->language);
                    $message = __('provider_messages.263',[],$transfer_provider_user_details->language);
                }

            } elseif ($wallet_provider_type == $this->user_type) {
                $transfer_provider_user_details = User::query()
                    ->select("users.id as id", "users.first_name as name", "users.device_token as device_token", "users.login_device as login_device","users.language")
                    ->where('users.status', "=", 1)
                    ->where('users.id', "=", $transfer_id)
                    ->whereNull('users.deleted_at')
                    ->first();

                $title = __('user_messages.262',[],$transfer_provider_user_details->language);
                $message = __('user_messages.263',[],$transfer_provider_user_details->language);

            } else {
                return response()->json([
                    "status" => 0,
//                    "message" => "Invalid wallet transfer type",
                    'message' => __('user_messages.9'),
                    "message_code" => 9
                ]);
            }
            if ($transfer_provider_user_details == Null) {
                return response()->json([
                    "status" => 0,
//                    "message" => "App Transfer User Not Found",
                    'message' => __('user_messages.5'),
                    "message_code" => 5
                ]);
            }

            $transfer_id = $transfer_provider_user_details->id;
            $transfer_wallet_holder_name = $transfer_provider_user_details->name . "";
            $transfer_wallet_holder_device_token = $transfer_provider_user_details->device_token;
            $transfer_wallet_holder_login_device = $transfer_provider_user_details->login_device;


            try {
                $get_last_transaction = UserWalletTransaction::query()
                    ->where('wallet_provider_type', "=", $wallet_provider_type)
                    ->where('user_id', "=", $transfer_id)
                    ->orderBy('id', 'desc')
                    ->first();
                if ($get_last_transaction != Null) {
                    $transfer_last_amount = $get_last_transaction->remaining_balance;
                } else {
                    $transfer_last_amount = 0;
                }
            } catch (\Exception $e) {
                $transfer_last_amount = 0;
            }

            $debited_balance = new UserWalletTransaction();
            $debited_balance->user_id = $provider_id;
            $debited_balance->wallet_provider_type = $provider_type;
            $debited_balance->transaction_type = 2;
            $debited_balance->amount = $amount_to_default;
            $debited_balance->subject = "Wallet Amount Transfer to " . $transfer_wallet_holder_name;
            $debited_balance->remaining_balance = floatval($last_amount - $amount_to_default);
            $debited_balance->subject_code = 8;
            $debited_balance->order_no = $transfer_wallet_holder_name;
            $debited_balance->save();

            $add_balance = new UserWalletTransaction();
            $add_balance->user_id = $transfer_id;
            $add_balance->wallet_provider_type = $wallet_provider_type;
            $add_balance->transaction_type = 1;
            $add_balance->amount = $amount_to_default;
            $add_balance->subject = "credit by " . $wallet_holder_name;
            $add_balance->remaining_balance = floatval($transfer_last_amount + $amount_to_default);
            $add_balance->subject_code = 1;
            $add_balance->order_no = $wallet_holder_name;
            $add_balance->save();

            if ($transfer_wallet_holder_device_token != Null && $transfer_wallet_holder_login_device != Null){
               $user_wallet_notication = $this->notificationClass->userWalletTransferNotification($title,$message,$transfer_wallet_holder_device_token,$transfer_wallet_holder_login_device,$amount_to_default);
            }
        }
        else {
            return response()->json([
                "status" => 0,
//                "message" => "amount can`t be null.",
                'message' => __('user_messages.331'),
                "message_code" => 331
            ]);
        }
        return response()->json([
            "status" => 1,
//            "message" => "success!",
            'message' => __('user_messages.1'),
            "message_code" => 1,
        ]);

    }
}
