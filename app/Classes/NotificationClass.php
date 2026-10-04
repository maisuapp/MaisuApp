<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 18-02-2019
 * Time: 12:44 PM
 */

namespace App\Classes;

use App\Jobs\AutoMail;
use App\Jobs\NearestAlgoSendDriverNotification;
use App\model\ApiLogDetail;
use App\model\DriverNewRequestNotification;
use App\model\EmailTemplates;
use App\model\GeneralSettings;
use App\model\Provider;
use App\model\ProviderServices;
use App\model\ProviderUserRunningService;
use App\model\ServiceCategory;
use App\model\ServiceSettings;
use App\model\ShopperNewRequestNotification;
use App\model\ShopperProviderDocuments;
use App\model\ShopperRequiredDocuments;
use App\model\StoreDetails;
use App\model\TransportDriverDetails;
use App\model\TransportRentalRideBooking;
use App\model\TransportRideBook;
use App\model\TransportRideDriverToken;
use App\model\TransportVehicleType;
use App\model\User;
use App\model\UserPackageBooking;
use App\model\UserProductBooking;
use App\model\UserProductBookingStatus;
use App\model\UserRideWayPoint;
use App\model\UserRunningRide;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use DateTime;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationClass
{
    private $transport_service_id_array;
    private $transport_rental_service_id_array;
    private $courier_service_id_array;
    private $delivery_service_id_array;
    private $courier_scooter;
    private $courier_car;
    private $bike_ride;
    private $taxi_ride;
    private $email_time = 5;//in seconds

    public function __construct()
    {
        $this->transport_service_id_array = [1, 2];
        $this->transport_rental_service_id_array = [31, 32];
        $this->courier_service_id_array = [4];
        $this->delivery_service_id_array = [5, 6, 7, 8, 9, 10];
        $this->courier_scooter = 10;
        $this->courier_car = 14;
        $this->bike_ride = 1;
        $this->taxi_ride = 2;
    }

    //pending courier notification
    public function ApiLogDetail($logger_type=Null, $logger_id=Null, $log_api_name="", $request=[]){
        try {
            $remove_log_date = date('Y-m-d H:i:s', strtotime("-3 days"));
            ApiLogDetail::query()->where("created_at","<=",$remove_log_date)->delete();
            $api_log_detail = new ApiLogDetail();
            $api_log_detail->logger_type = $logger_type;
            $api_log_detail->logger_id = $logger_id;
            $api_log_detail->log_api_name = $log_api_name;
            $api_log_detail->log_json = json_encode($request);
            $api_log_detail->save();
        }catch (\Exception $e){}
    }
    //notification changes return ride book

    //pending courier notification
    public function driverRequestNotification($service_cat_id, $request_id,$gender=null)
    {

        if ($service_cat_id != Null || $request_id != Null) {
            if (in_array($service_cat_id, $this->transport_service_id_array)) {
                //return $this->driverTransportRequestNotification($service_cat_id, $request_id);
                $send_notification = $this->driverTransportRideRequestNotification($service_cat_id, $request_id,$gender);
                if ($decoded['status'] = json_decode($send_notification) == false) {
                    return $send_notification;
                }
                return $send_notification;
            }
            if (in_array($service_cat_id, $this->transport_rental_service_id_array)) {
                //return $this->driverTransportRequestNotification($service_cat_id, $request_id);
                $send_notification = $this->driverTransportRentalRideRequestNotification($service_cat_id, $request_id);
                if ($decoded['status'] = json_decode($send_notification) == false) {
                    return $send_notification;
                }
                return $send_notification;
            } elseif (in_array($service_cat_id, $this->delivery_service_id_array)) {

                $send_notification = $this->driverDeliveryRequestNotification($service_cat_id, $request_id);
                if ($decoded['status'] = json_decode($send_notification) == false) {
                    return $send_notification;
                }
                return "success";
            } elseif (in_array($service_cat_id, $this->courier_service_id_array)) {
                // pending
                $send_notification = $this->driverTransportCourierRequestNotification($service_cat_id, $request_id);
                if ($decoded['status'] = json_decode($send_notification) == false) {
                    return $send_notification;
                }
            }
        }
        return "";
    }

    public function DriverNewRequestNotification($ride_driver_token_id,$reject_notification = 0){
        $general_settings = GeneralSettings::query()->first();
        $ride_driver_token = TransportRideDriverToken::query()->where("id", $ride_driver_token_id)->first();
        if ($ride_driver_token != Null && $general_settings != null && $ride_driver_token->android_tokens != Null) {
            $expire_date_time = date('Y-m-d h:i:s', strtotime("-60 minutes"));
            $driver_expire_date_time = $ride_driver_token->expire_date_time;
            $service_setting = ServiceSettings::query()->where('service_cat_id', $ride_driver_token->service_cat_id)->first();
            if ($service_setting != Null) {
                $radius = $service_setting->provider_search_radius;
                $timeout = $service_setting->provider_accept_timeout;
            } else {
                $radius = 5;
                $timeout = 60;
            }
            if (in_array($ride_driver_token->service_cat_id, $this->courier_service_id_array)) {
                $ride_request = TransportRideBook::query()->select(
                    'user_ride_booking.id as request_id',
                    'user_ride_booking.pickup_datetime',
                    'user_ride_booking.pickup_address',
                    'user_ride_booking.ride_type',
                    'user_ride_booking.is_way_point',
                    'user_ride_booking.retry_time',
                    'user_ride_booking.driver_algorithm',
                    'user_ride_booking.pickup_latlong',
                    'user_ride_booking.destination_address',
                    'user_ride_booking.destination_latlong',
                    'user_ride_booking.payment_type',
                    'user_ride_booking.total_pay',
                    'user_ride_booking.admin_commission',
                    'user_ride_booking.vehicle_type_id',
                    'service_category.id as service_cat_id',
                    'service_category.name as service_cat_name',
                    'service_category.icon_name as service_cat_icon',
                    'user_courier_service_details.goods_height',
                    'user_courier_service_details.goods_width',
                    'user_courier_service_details.goods_weight')
                    ->join('service_category', 'service_category.id', '=', 'user_ride_booking.service_cat_id')
                    ->join('user_courier_service_details', 'user_courier_service_details.ride_id', '=', 'user_ride_booking.id')
                    ->where('user_ride_booking.id', $ride_driver_token->ride_id)
                    ->where('user_ride_booking.driver_algorithm', 1)
                    ->where('user_ride_booking.status',0)
                    ->whereNull("driver_id")
                    ->first();
                if ($ride_request != Null) {
                    $date = date("Y-m-d H:i:s");
                    if (strtotime($driver_expire_date_time) <= strtotime($date) || $reject_notification == 1) {
                        $this->driverTransportCourierAllRequestNotification($ride_request->service_cat_id, $expire_date_time, $ride_request, $timeout, $radius, $general_settings->fcm_server_key, $ride_driver_token->android_tokens);
                    } else {
                        $differenceInSeconds = abs(strtotime($driver_expire_date_time) - strtotime($date));
                        $data = [
                            "ride_driver_token_id" => $ride_driver_token->id,
                        ];
                        $sendNotificationJob = (new NearestAlgoSendDriverNotification($data))->delay(now()->addSeconds($differenceInSeconds));
                        $dd = app('Illuminate\Contracts\Bus\Dispatcher')->dispatch($sendNotificationJob);
                    }
                }
            } else if (in_array($ride_driver_token->service_cat_id, $this->transport_service_id_array)) {
                $ride_request = TransportRideBook::query()->select(
                    'user_ride_booking.id as request_id',
                    'user_ride_booking.pickup_datetime',
                    'user_ride_booking.driver_algorithm',
                    'user_ride_booking.retry_time',
                    'user_ride_booking.pickup_address',
                    'user_ride_booking.is_way_point',
                    'user_ride_booking.ride_type',
                    'user_ride_booking.handicap',
                    'user_ride_booking.child_seat',
                    'user_ride_booking.pickup_latlong',
                    'user_ride_booking.destination_address',
                    'user_ride_booking.destination_latlong',
                    'user_ride_booking.payment_type',
                    'user_ride_booking.vehicle_type_id',
                    'service_category.id as service_cat_id',
                    'service_category.name as service_cat_name',
                    'service_category.icon_name as service_cat_icon')
                    ->join('service_category', 'service_category.id', '=', 'user_ride_booking.service_cat_id')
                    ->where('user_ride_booking.id', $ride_driver_token->ride_id)
                    ->where('user_ride_booking.driver_algorithm', 1)
                    ->where('user_ride_booking.status',0)
                    ->whereNull("driver_id")
                    ->first();
                if ($ride_request != Null) {
                    $date = date("Y-m-d H:i:s");
                    if (strtotime($driver_expire_date_time) <= strtotime($date) || $reject_notification == 1) {
                        $this->driverTransportAllRequestNotification($ride_request->service_cat_id, $expire_date_time, $ride_request, $timeout, $radius, $ride_driver_token->android_tokens);
                    } else {
                        $differenceInSeconds = abs(strtotime($driver_expire_date_time) - strtotime($date));
                        $data = [
                            "ride_driver_token_id" => $ride_driver_token->id,
                        ];
                        $sendNotificationJob = (new NearestAlgoSendDriverNotification($data))->delay(now()->addSeconds($differenceInSeconds));
                        $dd = app('Illuminate\Contracts\Bus\Dispatcher')->dispatch($sendNotificationJob);
                    }
                }
            } else if (in_array($ride_driver_token->service_cat_id, $this->transport_rental_service_id_array)) {
                $ride_request = TransportRentalRideBooking::query()->select(
                    'user_rental_ride_booking.id as request_id',
                    'user_rental_ride_booking.pickup_date_time as pickup_datetime',
                    'user_rental_ride_booking.drop_date_time as drop_datetime',
                    'user_rental_ride_booking.pickup_address',
                    'user_rental_ride_booking.driver_algorithm',
                    'user_rental_ride_booking.pickup_lat_long as pickup_latlong',
                    'user_rental_ride_booking.drop_address as destination_address',
                    'user_rental_ride_booking.drop_lat_long as destination_latlong',
                    'user_rental_ride_booking.payment_type',
                    'user_rental_ride_booking.vehicle_type_id',
                    'service_category.id as service_cat_id',
                    'service_category.name as service_cat_name',
                    'service_category.icon_name as service_cat_icon',
                    DB::raw("(CASE WHEN user_rental_ride_booking.id != '' THEN 1 ELSE 0 END) as is_rental"))
                    ->join('service_category', 'service_category.id', '=', 'user_rental_ride_booking.service_cat_id')
                    ->where('user_rental_ride_booking.id', $ride_driver_token->ride_id)
                    ->where('user_rental_ride_booking.driver_algorithm', 1)
                    ->where('user_rental_ride_booking.status',0)
                    ->whereNull("driver_id")
                    ->first();
                if ($ride_request != Null) {
                    $date = date("Y-m-d H:i:s");
                    if (strtotime($driver_expire_date_time) <= strtotime($date) || $reject_notification == 1) {
                        $this->driverTransportRentalAllRequestNotification($ride_request->service_cat_id, $expire_date_time, $ride_request, $timeout, $radius, $ride_driver_token->android_tokens);
                    } else {
                        $differenceInSeconds = abs(strtotime($driver_expire_date_time) - strtotime($date));
                        $data = [
                            "ride_driver_token_id" => $ride_driver_token->id,
                        ];
                        $sendNotificationJob = (new NearestAlgoSendDriverNotification($data))->delay(now()->addSeconds($differenceInSeconds));
                        $dd = app('Illuminate\Contracts\Bus\Dispatcher')->dispatch($sendNotificationJob);
                    }
                }
            } else if (in_array($ride_driver_token->service_cat_id, $this->delivery_service_id_array)) {
                $order_details = UserProductBooking::query()->select(
                    'user_store_product_booking.id as request_id',
                    'user_store_product_booking.created_at as pickup_datetime',
                    'store_details.address as pickup_address',
                    'store_details.address_lat as pickup_lat',
                    'user_store_product_booking.driver_algorithm',
                    'store_details.address_long as pickup_long',
                    'user_store_product_booking.delivery_address as destination_address',
                    'user_store_product_booking.lat_long as destination_latlong',
                    'user_store_product_booking.payment_type',
                    'user_store_product_booking.admin_commission',
                    'user_store_product_booking.total_pay',
                    'service_category.id as service_cat_id',
                    'service_category.name as service_cat_name',
                    'store_details.name as store_name',
                    'service_category.icon_name as service_cat_icon')
                    ->join('service_category', 'service_category.id', '=', 'user_store_product_booking.service_cat_id')
                    ->join('store_details', 'store_details.id', '=', 'user_store_product_booking.store_id')
                    ->where('user_store_product_booking.id', $ride_driver_token->delivery_id)
                    ->where('user_store_product_booking.driver_algorithm', 1)
                    ->where('user_store_product_booking.order_delivery_status', 0)
                    ->whereNull("delivery_people_id")
                    ->first();
                if ($order_details != Null) {
                    $date = date("Y-m-d H:i:s");
                    if (strtotime($driver_expire_date_time) <= strtotime($date) || $reject_notification == 1) {
                        $this->driverDeliveryAllRequestNotification($expire_date_time, $order_details, $timeout, $radius, $general_settings->fcm_server_key, $ride_driver_token->android_tokens);
                    } else {
                        $differenceInSeconds = abs(strtotime($driver_expire_date_time) - strtotime($date));
                        $data = [
                            "ride_driver_token_id" => $ride_driver_token->id,
                        ];
                        $sendNotificationJob = (new NearestAlgoSendDriverNotification($data))->delay(now()->addSeconds($differenceInSeconds));
                        $dd = app('Illuminate\Contracts\Bus\Dispatcher')->dispatch($sendNotificationJob);
                    }
                }
            }
        }
        return "";
    }

    public function getDriverForRide($service_cat_id,$expire_date_time,$ride_request,$login_type,$radius,$gender = 0){
        $not_multi_delivery_provider_id = [];
        $skip_ids = [];
        if($service_cat_id == 0){
            $lat = $ride_request->pickup_lat;
            $long = $ride_request->pickup_long;
            $skip_ids = [1,2,4,31,32];
        } else{
            $latlong = array_map('trim', explode(",", $ride_request->pickup_latlong));
            $lat = $latlong[0];
            $long = $latlong[1];
            $skip_ids = [1, 2, 4, 5, 6, 7, 8, 9, 10, 31, 32];
        }

        $not_multi_delivery_provider_id = ProviderUserRunningService::query()->whereIn('service_cat_id',$skip_ids)->get()->pluck('provider_id');

        if($ride_request->driver_algorithm == 1){
            $column_name = DB::raw("(6371 * acos( cos( radians(" . $lat . ") ) * cos( radians( current_lat ) )  * cos( radians( current_long ) - radians(" . $long . ") ) + sin( radians(" . $lat . ") ) * sin(radians( current_lat ) ) ) ) as distance ");
        } else{
            $column_name = 'transport_driver_details.id as driver_id';
        }

        $provider_tokens = Provider::query()->select('providers.id', 'providers.device_token',$column_name,'providers.first_name','providers.login_device','providers.language')
            ->whereRaw(DB::raw("(6371 * acos( cos( radians(" . $lat . ") ) * cos( radians( current_lat ) )  * cos( radians( current_long ) - radians(" . $long . ") ) + sin( radians(" . $lat . ") ) * sin(radians( current_lat ) ) ) ) < " . $radius))
            ->join('provider_services', 'provider_services.provider_id', '=', 'providers.id')
            ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
            ->join('transport_driver_details', 'transport_driver_details.provider_service_id', '=', 'provider_services.id')
            ->where('providers.status', 1)
            ->where('provider_services.status', 1)
            ->where('provider_services.current_status', 1)
            ->where('transport_driver_details.last_online_date_time', '>=', $expire_date_time)
            ->whereNotNull('transport_driver_details.current_lat')
            ->whereNotNull('transport_driver_details.current_long')
            ->whereNotNull('providers.access_token')
            ->whereNotNull('providers.device_token')
            ->whereNull('providers.deleted_at');

        if ($not_multi_delivery_provider_id != Null) {
            $provider_tokens = $provider_tokens->whereNotIn('providers.id', $not_multi_delivery_provider_id);
        }

        if(in_array($ride_request->service_cat_id,$this->delivery_service_id_array)){
            if(request()->get('general_settings')->is_private_driver_module == 1 )
            {
                if($ride_request->is_private_driver_notification == 1) {
                    // Only Personal Driver Notification
                    $provider_tokens = $provider_tokens->where('transport_driver_details.store_id', '=', $ride_request->store_id);
                } elseif ($ride_request->is_private_driver_notification == 0) {
                    // Both Personal & Public Driver Notification
                    $provider_tokens = $provider_tokens->where(function ($query) use ($ride_request) {
                        $query->where('transport_driver_details.store_id', '=', $ride_request->store_id)
                            ->orWhere('transport_driver_details.store_id', '=', 0);
                    });
                } else {
                    // Only Public Driver Notification
                    $provider_tokens = $provider_tokens->where('transport_driver_details.store_id', '=', 0);
                }
            }
        }
        if ($ride_request->child_seat == 1) {
            $provider_tokens = $provider_tokens->where('transport_driver_details.child_seat', '=', $ride_request->child_seat);
        }
        if ($ride_request->handicap == 1) {
            $provider_tokens = $provider_tokens->where('transport_driver_details.handicap', '=', $ride_request->handicap);
        }
        if ($gender != 0) {
            $provider_tokens = $provider_tokens->where('providers.gender', '=', $gender);
        }
        if(isset($login_type) && $login_type > 0) {
            if ($login_type == 1) {
                $provider_tokens = $provider_tokens->where('providers.login_device', 1);
            } elseif ($login_type == 3) {
                $provider_tokens = $provider_tokens->whereIn('providers.login_device', [3,4]);
            }elseif ($login_type == 5) {
                $provider_tokens = $provider_tokens->whereIn('providers.login_device', [1,2,3,4]);
            } else {
                $provider_tokens = $provider_tokens->where('providers.login_device', 2);
            }
        }
        if($ride_request->is_rental == 1){
            $provider_tokens = $provider_tokens
                ->where('transport_driver_details.driver_service', 1)
                ->where('transport_driver_details.rental_service', 1)
                ->where('transport_driver_details.vehicle_type_id', $ride_request->vehicle_type_id)
                ->where('service_category.id', $service_cat_id);
        } else{
            if ($service_cat_id == 4) {

                if ($ride_request->vehicle_type_id == $this->courier_scooter) {
                    $vehicle_type_list = TransportVehicleType::query()->where('service_cat_id', $this->bike_ride)->get()->pluck('id')->toArray();
                    $provider_tokens = $provider_tokens->whereIn('transport_driver_details.vehicle_type_id', $vehicle_type_list)
                        ->where('service_category.id', $this->bike_ride)
                        ->where('transport_driver_details.courier_boy', 1);
                } else if ($ride_request->vehicle_type_id == $this->courier_car) {
                    $vehicle_type_list = TransportVehicleType::query()->where('service_cat_id', $this->taxi_ride)->get()->pluck('id')->toArray();
                    $provider_tokens = $provider_tokens->whereIn('transport_driver_details.vehicle_type_id', $vehicle_type_list)
                        ->where('service_category.id', $this->taxi_ride)
                        ->where('transport_driver_details.courier_boy', 1);
                } else {
                    $provider_tokens = $provider_tokens->where('transport_driver_details.vehicle_type_id', $ride_request->vehicle_type_id)
                        ->where('service_category.id', $service_cat_id)
                        ->where('transport_driver_details.courier_boy', 1);
                }
            } else if ($service_cat_id == 0) {
                $provider_tokens = $provider_tokens->where('transport_driver_details.delivery_boy', 1)
                    ->whereIn('service_category.id', [1, 2]);
            } else{
                $provider_tokens = $provider_tokens->where('transport_driver_details.vehicle_type_id', $ride_request->vehicle_type_id)
                    ->where('service_category.id', $service_cat_id)
                    ->where('transport_driver_details.driver_service', 1);
            }
        }
        $provider_token = $provider_tokens->get()->toArray();

        if($ride_request->driver_algorithm == 1){
            usort($provider_token, function ($a, $b) {
                if ($a['distance'] == $b['distance']) return 0;
                return $a['distance'] < $b['distance'] ? -1 : 1;
            });
        }

        /*\Log::info('Driver from get driver function');
        \Log::info($provider_token);*/

        return $provider_token;
    }

    //ride request
    public function driverTransportRideRequestNotification($service_cat_id, $request_id,$gender=null)
    {
        $ride_request = TransportRideBook::select('user_ride_booking.id as request_id',
            'user_ride_booking.pickup_datetime','user_ride_booking.driver_algorithm',
            'user_ride_booking.pickup_address', 'user_ride_booking.is_way_point','user_ride_booking.no_of_retry',
            'user_ride_booking.ride_type', 'user_ride_booking.handicap', 'user_ride_booking.child_seat',
            'user_ride_booking.pickup_latlong', 'user_ride_booking.destination_address',
            'user_ride_booking.destination_latlong', 'user_ride_booking.payment_type', 'user_ride_booking.vehicle_type_id',
            'service_category.id as service_cat_id', 'service_category.name as service_cat_name',
            'service_category.icon_name as service_cat_icon')
            ->join('service_category', 'service_category.id', '=', 'user_ride_booking.service_cat_id')
            ->where('user_ride_booking.id', $request_id)->first();
        if ($ride_request != null) {
            $service_setting = ServiceSettings::where('service_cat_id', $service_cat_id)->first();
            if ($service_setting != Null) {
                $radius = $service_setting->provider_search_radius;
                $timeout = $service_setting->provider_accept_timeout;
            } else {
                $radius = 5;
                $timeout = 60;
            }
//            $expire_date_time = date('Y-m-d h:i:s', strtotime("-10 minutes"));
            $expire_date_time = date('Y-m-d h:i:s', strtotime("-60 minutes"));
            if($ride_request->driver_algorithm == 0){
                $this->driverTransportAndroidRequestNotification($service_cat_id, $expire_date_time, $ride_request, $timeout, $radius,$gender);
//                $this->driverTransportIosRequestNotification($service_cat_id, $expire_date_time, $ride_request, $timeout, $radius,$gender);
//                $this->driverTransportFlutterRequestNotification($service_cat_id, $expire_date_time, $ride_request, $timeout, $radius,$gender);
            } else{
                $driver_token = Null;
                $ride_driver_token = TransportRideDriverToken::query()->where("ride_id", $ride_request->request_id)->where("service_cat_id", $service_cat_id)->first();
                if ($ride_driver_token != Null) {
                    $date = date("Y-m-d H:i:s");
                    $driver_expire_date_time = $ride_driver_token->expire_date_time;
                    if (strtotime($driver_expire_date_time) <= strtotime($date)) {
                        if ($ride_driver_token->android_tokens != Null && $ride_driver_token->android_tokens != '') {
                            $driver_token = $ride_driver_token->android_tokens;
                        }
                    } else {
                        if ($ride_driver_token->android_tokens != Null && $ride_driver_token->android_tokens != '') {
                            $differenceInSeconds = abs(strtotime($driver_expire_date_time) - strtotime($date));
                            $data = [
                                "ride_driver_token_id" => $ride_driver_token->id,
                            ];
                            $sendNotificationJob = (new NearestAlgoSendDriverNotification($data))->delay(now()->addSeconds($differenceInSeconds));
                            $dd = app('Illuminate\Contracts\Bus\Dispatcher')->dispatch($sendNotificationJob);
                        }
                        return "success";
                    }
                }
                $this->driverTransportAllRequestNotification($service_cat_id, $expire_date_time, $ride_request, $timeout, $radius,$driver_token,$gender);
            }
            return "success";
        }
    }

    //default base64_encode function is causing some error so custom func is used
    public function customBase64UrlEncoder($text){
        return str_replace(
            ['+', '/', '='],
            ['-', '_', ''],
            base64_encode($text)
        );
    }

    //fetch fcm bearer token or renew if its expired
    public function fetchFCMBearerToken()
    {
        $current_date_time = date('Y-m-d H:i:s');
        //current token is expired so generate new token
        if (request()->get('general_settings')->fcm_bearer_token_expiry_date == Null || $current_date_time >= request()->get('general_settings')->fcm_bearer_token_expiry_date){

            //to use direct json file instead of custom file in config directory enable this code and use below variables instead
            //read service account details
            //$authConfigString = file_get_contents(asset('service_account.json'));
            //decode service account details
            //$authConfig = json_decode($authConfigString);

            //read private key for service account details from config directory
            $secret = openssl_get_privatekey(config('firebase-cloud-messaging.configurations.private_key'));

            //create the token header
            $header = json_encode([
                'typ' => 'JWT',
                'alg' => 'RS256'
            ]);

            //get seconds since 1 January 1970
            $time = time();

            //allow 1 minute time deviation
            $start = $time - 60;
            $end = $time + 3600;

            //initialize payload
            $payload = json_encode([
                "iss" => config('firebase-cloud-messaging.configurations.client_email'),
                "scope" => "https://www.googleapis.com/auth/firebase.messaging",
                "aud" => config('firebase-cloud-messaging.configurations.token_uri'),
                "exp" => $end,
                "iat" => $start
            ]);

            //encode header
            $base64UrlHeader = $this->customBase64UrlEncoder($header);

            //encode payload
            $base64UrlPayload = $this->customBase64UrlEncoder($payload);

            //create signature hash
            $result = openssl_sign($base64UrlHeader . "." . $base64UrlPayload, $signature, $secret, OPENSSL_ALGO_SHA256);

            //encode signature to Base64Url String
            $base64UrlSignature = $this->customBase64UrlEncoder($signature);

            //create JWT
            $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;

            //request new token in firebase
            $options = array(
                'http' => array(
                    'method'  => 'POST',
                    'content' => 'grant_type=urn:ietf:params:oauth:grant-type:jwt-bearer&assertion=' . $jwt,
                    'header'  => 'Content-Type: application/x-www-form-urlencoded'
                )
            );

            //create stream context with option
            $context  = stream_context_create($options);

            //read the created stream context and save the string from file
            $responseText = file_get_contents(config('firebase-cloud-messaging.configurations.token_uri'), false, $context);

            //decode the response text and read the token amd its expiry time in seconds from json object
            $response = json_decode($responseText);

            if ($response == Null || $response->access_token == Null){
                //didn't get valid response while requesting new token so use the existing token
                return request()->get('general_settings')->fcm_bearer_token;
            }

            //generate expiry date according to settings
            $expiry_date_time = date('Y-m-d H:i', strtotime("+" . request()->get('general_settings')->fcm_bearer_token_expiry_mins . " minutes", strtotime($current_date_time)));

            //update existing token with new token & its expiry date
            GeneralSettings::query()->update(['fcm_bearer_token' => $response->access_token,'fcm_bearer_token_expiry_date' => $expiry_date_time]);

            return $response->access_token;
        }
        else{
            //current token is not expired so use the existing token
            return request()->get('general_settings')->fcm_bearer_token;
        }
    }
    //send flow notification via cURL call to FCM V1 API
    public function sendFlowNotification($device_token_array,$notification_data_array,$multiple_tokens)
    {
        //cURL url
        $fcm_url = "https://fcm.googleapis.com/v1/projects/" . config('firebase-cloud-messaging.configurations.project_id') . "/messages:send";
        //fetch fcm bearer token and renew if its expired
        $fcm_bearer_token = $this->fetchFCMBearerToken();
        //cURL headers
        $headers = [
            "Content-Type: application/json",
            "Authorization: Bearer " . $fcm_bearer_token
        ];
        //cURL body (send the body in json format and make sure to keep string as datatype in notification data object)
        $body = [
            "validate_only" => false,
            "message" => [
                "token" => $multiple_tokens == 0 ? $device_token_array : Null,
                "data" => $notification_data_array,
                "notification" => [
                    "title" => $notification_data_array['title'] . "",
                    "body" => $notification_data_array['message'] . ""
                ],
                "android" => [
                    "notification" => [
                        "click_action" => "FLUTTER_NOTIFICATION_CLICK"
                    ]
                ],
                "apns" => [
                    "payload" => [
                        "aps" => [
                            "content-available" => 1
                        ]
                    ]
                ]
            ]
        ];
        //make a cURL call with curl_multi_init func since multiple tokens are given
        if ($multiple_tokens == 1) {

            $curl_temp = "curl_";
            $response_array = array([
                "is_notification_sent" => 0,
                "cURL_success_response" => array(),
                "cURL_error_response" => array(),
            ]);

            //create cURL multi handle
            $multi_handle = curl_multi_init();

            //in case of array not given then create an array
            $device_token_array = is_array($device_token_array) ? $device_token_array : array($device_token_array);

            foreach ($device_token_array as $key => $token) {
                // create cURL resources for each token
                ${$curl_temp . $key} = curl_init();

                //set the current token in the body
                $body['message']['token'] = $token . "";

                // set URL and other appropriate options
                curl_setopt(${$curl_temp . $key}, CURLOPT_URL, $fcm_url);
                curl_setopt(${$curl_temp . $key}, CURLOPT_POST, true);
                curl_setopt(${$curl_temp . $key}, CURLOPT_HTTPHEADER, $headers);
                curl_setopt(${$curl_temp . $key}, CURLOPT_RETURNTRANSFER, true);
                curl_setopt(${$curl_temp . $key}, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt(${$curl_temp . $key}, CURLOPT_POSTFIELDS, json_encode($body));

                //add individual cURL handles into cURL multi handle
                curl_multi_add_handle($multi_handle, ${$curl_temp . $key});
            }

            //execute the cURL multi handle
            $running = 0;
            do {
                curl_multi_exec($multi_handle, $running);
            } while ($running);

            foreach ($device_token_array as $key => $token) {
                //remove individual cURL handles from cURL multi handle
                curl_multi_remove_handle($multi_handle, ${$curl_temp . $key});

                //get return response from all handles
                if (curl_getinfo(${$curl_temp . $key},CURLINFO_HTTP_CODE) == 200){
                    $response_array["is_notification_sent"] = 1;
                    $response_array["cURL_success_response"][$key] = curl_multi_getcontent(${$curl_temp . $key});
                }
                else{
                    $response_array["cURL_error_response"][$key] = curl_multi_getcontent(${$curl_temp . $key});
                }
            }

            //close cURL multi handles
            curl_multi_close($multi_handle);

            return $response_array;
        }
        //make a normal cURL call as single token is given
        else{
            //initialize cURL call
            $curl = curl_init();
            //set cURL preferences
            curl_setopt($curl, CURLOPT_URL, $fcm_url);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($body));
            //execute cURL call
            $response = curl_exec($curl);
            //close cURL call
            curl_close($curl);

            return $response;
        }
    }
    //send push notification via cURL call to FCM V1 API
    public function sendPushNotification($topic,$title,$message,$notification_type)
    {
        //cURL url
        $fcm_url = "https://fcm.googleapis.com/v1/projects/" . config('firebase-cloud-messaging.configurations.project_id') . "/messages:send";
        //fetch fcm bearer token and renew if its expired
        $fcm_bearer_token = $this->fetchFCMBearerToken();
        //cURL headers
        $headers = [
            "Content-Type: application/json",
            "Authorization: Bearer " . $fcm_bearer_token
        ];
        //cURL body (send the body in json format and keep everything in string)
        $body = [
            "validate_only" => false,
            "message" => [
                "topic" => $topic,
                "data" => [
                    "sound" => "true",
                    "notification_type" => $notification_type . "",
                    "title" => $title . "",
                    "body" => $message . "",
                    "message" => $message . ""
                ],
                "notification" => [
                    "title" => $title . "",
                    "body" => $message . ""
                ],
                "android" => [
                    "notification" => [
                        "click_action" => "FLUTTER_NOTIFICATION_CLICK"
                    ]
                ],
                "apns" => [
                    "payload" => [
                        "aps" => [
                            "content-available" => 1
                        ]
                    ]
                ]
            ]
        ];
        //initialize cURL call
        $curl = curl_init();
        //set cURL preferences
        curl_setopt($curl, CURLOPT_URL, $fcm_url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($body));
        //execute cURL call
        $response = curl_exec($curl);
        //close cURL call
        curl_close($curl);

        return $response;
    }
    //Provider Notification for purchase & cancel & expire subscription plan
    public function sendSubscriptionNotification($token,$language,$status)
    {
        //status 0 for cancel, 1 for purchase, 2 for expire

        //language
        $language = $language != Null ? $language : 'en';

        //title for notification
        $title = __('driver_messages.91', [], $language);
        $title_code = "91";

        //message for notification
        if ($status == 1) {
            //for purchase of subscription plan
            $message = __('driver_messages.344', [], $language);
            $message_code = "114";
        } elseif ($status == 0) {
            //for cancel of subscription plan
            $message = __('driver_messages.345', [], $language);
            $message_code = "112";
        } elseif ($status == 2) {
            //for expire of subscription plan
            $message = __('driver_messages.346', [], $language);
            $message_code = "112";
        } else {
            return response()->json([
                "status" => 0,
                "message" => __('driver_messages.9'),
                "message_code" => 9,
            ]);
        }

        $notification_data_array = [
            "sound" => "true",
            "notification_type" => "11",
            "title" => $title . "",
            "message" => $message . "",
            "body" => $message . "",
            "title_code" => $title_code,
            "message_code" => $message_code
        ];

        $response = $this->sendFlowNotification($token,$notification_data_array,0);

        return $response;
    }

    public function driverTransportAllRequestNotification($service_cat_id, $expire_date_time, $ride_request, $timeout, $radius,$driver_token = Null,$gender = 0)
    {
        $get_provider_token_list = Null;
        $ride_driver_token = Null;
        if ($driver_token == Null) {
            if($ride_request->no_of_retry == 0 || $driver_token == Null) {
                $driver_token = $this->getDriverForRide($service_cat_id, $expire_date_time, $ride_request, 0, $radius,$gender);
                $get_provider_token_list = $driver_token;
                $provider_token = isset($driver_token[0]['device_token']) ? $driver_token[0]['device_token'] : '';
                $login_device = isset($driver_token[0]['login_device']) ? $driver_token[0]['login_device'] : 0;
                $language = isset($driver_token[0]['language']) ? $driver_token[0]['language'] : 'en';
                unset($driver_token[0]);
                $provider_ids = implode(',', array_column($driver_token, 'id'));
            } else{
                $ride_driver_token = TransportRideDriverToken::query()
                    ->where('ride_id', $ride_request->request_id)
                    ->where('service_cat_id', $service_cat_id)
                    ->first();
                if($ride_driver_token->android_tokens != Null){
                    $provider_ids = explode(',', $ride_driver_token->android_tokens);
                    $first_provider_id = isset($provider_ids[0]) ? $provider_ids[0] : 0;
                    $provider = Provider::query()->select('device_token', 'login_device')->where('id', $first_provider_id)->whereNull('providers.deleted_at')->first();
                    $provider_token = $provider->device_token != Null ? $provider->device_token : '';
                    $login_device = $provider->login_device;
                    $language = $provider->language != Null ? $provider->language : 'en';
                    unset($provider_ids[0]);
                    $provider_ids = implode(',', $provider_ids);
                }
            }
        } else {
            $provider_ids = explode(',', $driver_token);
            $first_provider_id = isset($provider_ids[0]) ? $provider_ids[0] : 0;
            $provider = Provider::query()->select('device_token', 'login_device')->where('id', $first_provider_id)->whereNull('providers.deleted_at')->first();
            $provider_token = $provider->device_token != Null ? $provider->device_token : '';
            $login_device = $provider->login_device;
            $language = $provider->language != Null ? $provider->language : 'en';
            unset($provider_ids[0]);
            $provider_ids = implode(',', $provider_ids);
        }

        $ride_driver_token = TransportRideDriverToken::query()->where('ride_id', $ride_request->request_id)->first();
        if ($ride_driver_token != Null) {
            $ride_driver_token->android_tokens = isset($provider_ids) ? $provider_ids : Null;
            $ride_driver_token->expire_date_time = date('Y-m-d H:i:s', time() + $timeout);
            $ride_driver_token->save();
        } else {
            $ride_driver_token = new TransportRideDriverToken();
            $ride_driver_token->ride_id = $ride_request->request_id;
            $ride_driver_token->service_cat_id = $ride_request->service_cat_id;
            $ride_driver_token->expire_date_time = date('Y-m-d H:i:s', time() + $timeout);
            $ride_driver_token->android_tokens = isset($provider_ids) ? $provider_ids : Null;
            $ride_driver_token->save();
        }

        $general_settings = GeneralSettings::select('fcm_server_key')->first();
        if ($general_settings == Null || $general_settings->fcm_server_key == Null) {
            return response()->json([
                'status' => 0,
//                'message' => "Something want wrong!",
                'message' => __('user_messages.9'),
                'message_code' => 9,
            ]);
        }

        $pickup = array_map('trim', explode(",", $ride_request->pickup_latlong));
        $destination = array_map('trim', explode(",", $ride_request->destination_latlong));
        $way_points[] = [
            "address" => $ride_request->pickup_address,
            "address_lat" => trim($pickup[0]),
            "address_long" => trim($pickup[1])
        ];
        if ($ride_request->is_way_point == 1) {
            $ride_way_point = UserRideWayPoint::query()->where('ride_id', $ride_request->request_id)->first();
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
                        "address" => $ride_way_point->way_point_1,
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
            "address" => $ride_request->destination_address,
            "address_lat" => trim($destination[0]),
            "address_long" => trim($destination[1])
        ];

        $server_key = trim($general_settings->fcm_server_key);

        $success = null;
        $failure = null;

        //To driver Assign
        if(isset($provider_ids) && isset($login_device)){
            $extraNotificationData = [
                'title' => $ride_request->service_cat_name . "",
                'title_code' => "22",
                'sound' => "true",
                'description' => __('driver_messages.90', [],$language) . "",
                'message_code' => "90",
                'message' => __('driver_messages.90', [],$language) . "",
                'request_id' => $ride_request->request_id . "",
                'service_category_id' => $ride_request->service_cat_id . "",
                'service_category_name' => $ride_request->service_cat_name . "",
                'service_category_icon' => $ride_request->service_cat_icon != Null ? url('/assets/images/service-category/' . $ride_request->service_cat_icon) . '' : '',
                "address_list" => json_encode($way_points),
                'notification_type' => $ride_request->ride_type ."",
                'is_way_points' => $ride_request->is_way_point . "",
                'schedule_date_time' => $ride_request->pickup_datetime . "",
                'schedule_display_date_time' => Date('h:i, d M Y', strtotime($ride_request->pickup_datetime)),
                'payment_type' => $ride_request->payment_type . "",
                'driver_algorithm' => $ride_request->driver_algorithm . "",
                'request_timeout' => $timeout . ""
            ];
        }
        if ($get_provider_token_list != Null) {
            foreach ($get_provider_token_list as $key => $provider) {
                $driver_request_notify = new DriverNewRequestNotification();
                $driver_request_notify->driver_id = $provider['id'];
                $driver_request_notify->request_id = $ride_request->request_id;
                $driver_request_notify->service_cat_id = $ride_request->service_cat_id;
                $driver_request_notify->notification_data = isset($extraNotificationData) ? json_encode($extraNotificationData) : Null;
                $driver_request_notify->save();

                $get_provider_token[] = [
                    $provider['id'],
                ];
            }
        }

        $response_array = $this->sendFlowNotification($provider_token,$extraNotificationData,1);

        if (!empty($response_array) && !empty($response_array['cURL_success_response'])){
            if ($ride_driver_token != Null) {
                $data = [
                    "ride_driver_token_id" => $ride_driver_token->id,
                ];
                $sendNotificationJob = (new NearestAlgoSendDriverNotification($data))->delay(now()->addSeconds($timeout));
                $dd = app('Illuminate\Contracts\Bus\Dispatcher')->dispatch($sendNotificationJob);
            }
        }else{
            $driver_token = Null;
            $ride_driver_token = TransportRideDriverToken::query()->where("ride_id", $ride_request->request_id)->where("service_cat_id", $service_cat_id)->first();
            if ($ride_driver_token != Null) {
                if ($ride_driver_token->android_tokens != Null) {
                    $driver_token = $ride_driver_token->android_tokens;
                    $this->driverTransportAllRequestNotification($service_cat_id, $expire_date_time, $ride_request, $timeout, $radius,$driver_token,$gender);
                }
            }
        }

        return $response_array;
    }

    public function driverTransportAndroidRequestNotification($service_cat_id, $expire_date_time, $ride_request, $timeout, $radius,$gender=null,$driver_token = Null)
    {
        $driver_token = $this->getDriverForRide($service_cat_id, $expire_date_time, $ride_request, 1, $radius,$gender);
        $get_provider_token_list = $driver_token;
        $provider_token = array_column($driver_token, 'device_token');

        /*$latlong = array_map('trim', explode(",", $ride_request->pickup_latlong));
        $lat = $latlong[0];
        $long = $latlong[1];

        $get_provider_token = [];
        $get_provider_token_list = Provider::query()->select('providers.id', 'providers.device_token')
            ->whereRaw(DB::raw("(6371 * acos( cos( radians(" . $lat . ") ) * cos( radians( current_lat ) )  * cos( radians( current_long ) - radians(" . $long . ") ) + sin( radians(" . $lat . ") ) * sin(radians( current_lat ) ) ) ) < " . $radius))
            ->join('provider_services', 'provider_services.provider_id', '=', 'providers.id')
            ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
            ->join('transport_driver_details', 'transport_driver_details.provider_service_id', '=', 'provider_services.id')
            ->where('service_category.id', $service_cat_id)
            ->where('providers.status', 1)
            ->where('providers.login_device', 1)
            ->where('provider_services.status', 1)
            ->where('provider_services.current_status', 1)
            ->where('transport_driver_details.driver_service', 1)
            ->where('transport_driver_details.vehicle_type_id', $ride_request->vehicle_type_id)
            ->where('transport_driver_details.last_online_date_time', '>=', $expire_date_time)
            ->whereNotNull('transport_driver_details.current_lat')
            ->whereNotNull('transport_driver_details.current_long')
            ->whereNotNull('providers.access_token')
            ->whereNotNull('providers.device_token');
        if ($ride_request->handicap != 0) {
            $get_provider_token_list->where('transport_driver_details.handicap', '=', $ride_request->handicap);
        }
        if ($ride_request->child_seat != 0) {
            $get_provider_token_list->where('transport_driver_details.child_seat', '=', $ride_request->child_seat);
        }
        if (isset($gender) && $gender != Null && $gender != 0) {
        $get_provider_token_list->where('providers.gender', '=', $gender);
        }
        $get_provider_token_list = $get_provider_token_list->get();*/
        //->pluck('device_token')->toArray();

        $general_settings = GeneralSettings::query()->select('fcm_server_key')->first();
        if ($general_settings == Null || $general_settings->fcm_server_key == Null) {
            return response()->json([
                'status' => 0,
//                'message' => "Something want wrong!",
                'message' => __('user_messages.9'),
                'message_code' => 9,
            ]);
        }

        $pickup = array_map('trim', explode(",", $ride_request->pickup_latlong));
        $destination = array_map('trim', explode(",", $ride_request->destination_latlong));
        $way_points[] = [
            "address" => $ride_request->pickup_address,
            "address_lat" => trim($pickup[0]),
            "address_long" => trim($pickup[1])
        ];
        if ($ride_request->is_way_point == 1) {
            $ride_way_point = UserRideWayPoint::query()->where('ride_id', $ride_request->request_id)->first();
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
                        "address" => $ride_way_point->way_point_1,
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
            "address" => $ride_request->destination_address,
            "address_lat" => trim($destination[0]),
            "address_long" => trim($destination[1])
        ];

        $server_key = trim($general_settings->fcm_server_key);

        //To driver Assign
        $title = $ride_request->service_cat_name;
        $extraNotificationData = [
            'title' => $title . "",
            'title_code' => "22",
            'sound' => "true",
            'description' => "New Request",
            'message_code' => "90",
            'message' => "New Request",
            'request_id' => $ride_request->request_id . "",
            'service_category_id' => $ride_request->service_cat_id . "",
            'service_category_name' => $ride_request->service_cat_name . "",
            'service_category_icon' => $ride_request->service_cat_icon != Null ? url('/assets/images/service-category/' . $ride_request->service_cat_icon) . '' : '',
            "address_list" => json_encode($way_points),
            'notification_type' => $ride_request->ride_type . '',
            'is_way_points' => $ride_request->is_way_point . '',
            'schedule_date_time' => $ride_request->pickup_datetime . '',
            'schedule_display_date_time' => Date('h:i, d M Y', strtotime($ride_request->pickup_datetime)),
            'payment_type' => $ride_request->payment_type . '',
            'driver_algorithm' => $ride_request->driver_algorithm . '',
            'request_timeout' => $timeout . ''
        ];

        if ($get_provider_token_list != Null) {
            foreach ($get_provider_token_list as $key => $provider) {
                //$driver_request_notify_check = DriverNewRequestNotification::query()->where('driver_id', $provider->id)->first();
                //if ($driver_request_notify_check == Null) {
                $driver_request_notify = new DriverNewRequestNotification();
                $driver_request_notify->driver_id = $provider['id'];
                $driver_request_notify->request_id = $ride_request->request_id;
                $driver_request_notify->service_cat_id = $ride_request->service_cat_id;
                $driver_request_notify->notification_data = json_encode($extraNotificationData);
                $driver_request_notify->save();

                $get_provider_token[] = [
                    $provider['id'],
                ];
                //}
            }
        }

        $response_array = $this->sendFlowNotification($provider_token,$extraNotificationData,1);

        return $response_array;
    }

    public function driverTransportRentalRideRequestNotification($service_cat_id, $request_id)
    {
          $ride_request = TransportRentalRideBooking::query()->select(
            'user_rental_ride_booking.id as request_id',
            'user_rental_ride_booking.pickup_date_time as pickup_datetime',
            'user_rental_ride_booking.drop_date_time as drop_datetime',
            'user_rental_ride_booking.pickup_address',
            'user_rental_ride_booking.driver_algorithm',
            'user_rental_ride_booking.pickup_lat_long as pickup_latlong',
            'user_rental_ride_booking.drop_address as destination_address',
            'user_rental_ride_booking.drop_lat_long as destination_latlong',
            'user_rental_ride_booking.payment_type',
            'user_rental_ride_booking.vehicle_type_id',
            'service_category.id as service_cat_id',
            'service_category.name as service_cat_name',
            'service_category.icon_name as service_cat_icon',
             DB::raw("(CASE WHEN user_rental_ride_booking.id != '' THEN 1 ELSE 0 END) as is_rental")
            )
            ->join('service_category', 'service_category.id', '=', 'user_rental_ride_booking.service_cat_id')
            ->where('user_rental_ride_booking.id', '=', $request_id)
            ->first();
        if ($ride_request != null) {
            $service_setting = ServiceSettings::query()->where('service_cat_id', $service_cat_id)->first();
            if ($service_setting != Null) {
                $radius = $service_setting->provider_search_radius;
                $timeout = $service_setting->provider_accept_timeout;
            } else {
                $radius = 5;
                $timeout = 60;
            }
            //$expire_date_time = date('Y-m-d h:i:s', strtotime("-10 minutes"));
            $expire_date_time = date('Y-m-d h:i:s', strtotime("-60 minutes"));
            $driver_service_cat_id = $service_cat_id;
            if ($driver_service_cat_id == 31) {
                $driver_service_cat_id = 1;
            }
            if ($driver_service_cat_id == 32) {
                $driver_service_cat_id = 2;
            }
            if($ride_request->driver_algorithm == 0){

                $this->driverTransportRentalAndroidRequestNotification($driver_service_cat_id, $expire_date_time, $ride_request, $timeout, $radius);
                $this->driverTransportRentalIosRequestNotification($driver_service_cat_id, $expire_date_time, $ride_request, $timeout, $radius);
                $this->driverTransportRentalFlutterRequestNotification($driver_service_cat_id, $expire_date_time, $ride_request, $timeout, $radius);
            } else {
                $driver_token = Null;
                $ride_driver_token = TransportRideDriverToken::query()->where("ride_id", $ride_request->request_id)->where("service_cat_id", $service_cat_id)->first();
                if ($ride_driver_token != Null) {
                    $date = date("Y-m-d H:i:s");
                    $driver_expire_date_time = $ride_driver_token->expire_date_time;
                    if (strtotime($driver_expire_date_time) <= strtotime($date)) {
                        if ($ride_driver_token->android_tokens != Null && $ride_driver_token->android_tokens != '') {
                            $driver_token = $ride_driver_token->android_tokens;
                        }
                    } else {
                        if ($ride_driver_token->android_tokens != Null && $ride_driver_token->android_tokens != '') {
                            $differenceInSeconds = abs(strtotime($driver_expire_date_time) - strtotime($date));
                            $data = [
                                "ride_driver_token_id" => $ride_driver_token->id,
                            ];
                            $sendNotificationJob = (new NearestAlgoSendDriverNotification($data))->delay(now()->addSeconds($differenceInSeconds));
                            $dd = app('Illuminate\Contracts\Bus\Dispatcher')->dispatch($sendNotificationJob);
                        }
                        return "success";
                    }
                }
                $this->driverTransportRentalAllRequestNotification($driver_service_cat_id, $expire_date_time, $ride_request, $timeout, $radius, $driver_token);
            }
            return "success";
        }
    }

    public function driverTransportRentalAllRequestNotification($service_cat_id, $expire_date_time, $ride_request, $timeout, $radius,$driver_token = Null)
    {
        $ride_driver_token = null;
        if ($driver_token == Null) {
            $driver_token = $this->getDriverForRide($service_cat_id, $expire_date_time, $ride_request, 5, $radius);
            $provider_token = isset($driver_token[0]['device_token']) ? $driver_token[0]['device_token'] : '';
            $login_device = isset($driver_token[0]['login_device']) ? $driver_token[0]['login_device'] : 0;
            $language = isset($driver_token[0]['language']) ? $driver_token[0]['language'] : 'en';
            unset($driver_token[0]);
            $provider_ids = implode(',', array_column($driver_token, 'id'));
        } else {
            $provider_ids = explode(',', $driver_token);
            $first_provider_id = isset($provider_ids[0]) ? $provider_ids[0] : 0;
            $provider = Provider::query()->select('device_token','login_device')->where('id', $first_provider_id)->whereNull('providers.deleted_at')->first();
            $provider_token = $provider->device_token != Null ? $provider->device_token : '';
            $login_device = $provider->login_device;
            $language = isset($driver_token[0]['language']) ? $driver_token[0]['language'] : 'en';
            unset($provider_ids[0]);
            $provider_ids = implode(',', $provider_ids);
        }

        $ride_driver_token = TransportRideDriverToken::query()->where('ride_id', $ride_request->request_id)->first();
        if ($ride_driver_token != Null) {
            $ride_driver_token->android_tokens = $provider_ids;
            $ride_driver_token->expire_date_time = date('Y-m-d H:i:s', time() + $timeout);
            $ride_driver_token->save();
        } else {
            $ride_driver_token = new TransportRideDriverToken();
            $ride_driver_token->ride_id = $ride_request->request_id;
            $ride_driver_token->service_cat_id = $ride_request->service_cat_id;
            $ride_driver_token->expire_date_time = date('Y-m-d H:i:s', time() + $timeout);
            $ride_driver_token->android_tokens = $provider_ids;
            $ride_driver_token->save();
        }
        $general_settings = GeneralSettings::query()->select('fcm_server_key')->first();
        if ($general_settings == Null || $general_settings->fcm_server_key == Null) {
            return response()->json([
                'status' => 0,
                'message' => __('driver_messages.9', [],$language),
                'message_code' => 9,
            ]);
        }
        $pickup = array_map('trim', explode(",", $ride_request->pickup_latlong));
        $destination = array_map('trim', explode(",", $ride_request->destination_latlong));
        $way_points[] = [
            "address" => $ride_request->pickup_address,
            "address_lat" => trim($pickup[0]),
            "address_long" => trim($pickup[1])
        ];
        $way_points[] = [
            "address" => $ride_request->destination_address,
            "address_lat" => trim($destination[0]),
            "address_long" => trim($destination[1])
        ];

        $server_key = trim($general_settings->fcm_server_key);
        //To driver Assign
        $extraNotificationData = [
            'title' => $title . "",
            'title_code' => "22",
            'sound' => "true",
            'description' => __('driver_messages.90', [],$language),
            'message_code' => "90",
            'message' => __('driver_messages.90', [],$language),
            'request_id' => $ride_request->request_id . "",
            'service_category_id' => $ride_request->service_cat_id . "",
            'service_category_name' => $ride_request->service_cat_name . "",
            'service_category_icon' => $ride_request->service_cat_icon != Null ? url('/assets/images/service-category/' . $ride_request->service_cat_icon) . "" : '',
            "address_list" => json_encode($way_points),
            'notification_type' => "1",
            'is_way_points' => "0",
            'schedule_date_time' => $ride_request->pickup_datetime . "",
            'pickup_datetime' => $ride_request->pickup_datetime . "",
            'drop_datetime' => $ride_request->drop_datetime . "",
            'schedule_display_date_time' => Date('h:i, d M Y', strtotime($ride_request->pickup_datetime)),
            'payment_type' => $ride_request->payment_type . "",
            'request_timeout' => $timeout . ""
        ];

        $response_array = $this->sendFlowNotification($provider_token,$extraNotificationData,1);

        if (!empty($response_array) && !empty($response_array['cURL_success_response'])){
            if ($ride_driver_token != Null) {
                $data = [
                    "ride_driver_token_id" => $ride_driver_token->id,
                ];
                $sendNotificationJob = (new NearestAlgoSendDriverNotification($data))->delay(now()->addSeconds($timeout));
                $dd = app('Illuminate\Contracts\Bus\Dispatcher')->dispatch($sendNotificationJob);
            }
        }else{
            $driver_token = Null;
            $ride_driver_token = TransportRideDriverToken::query()->where("ride_id", $ride_request->request_id)->where("service_cat_id", $service_cat_id)->first();
            if ($ride_driver_token != Null) {
                if ($ride_driver_token->android_tokens != Null) {
                    $driver_token = $ride_driver_token->android_tokens;
                    $this->driverTransportRentalAllRequestNotification($service_cat_id, $expire_date_time, $ride_request, $timeout, $radius,$driver_token);
                }
            }
        }

        return $response_array;
    }

    //code for send flutter rental notification
    public function driverTransportRentalFlutterRequestNotification($service_cat_id, $expire_date_time, $ride_request, $timeout, $radius,$driver_token = Null)
    {
        $driver_token = $this->getDriverForRide($service_cat_id,$expire_date_time,$ride_request,3,$radius);
        $provider_token = array_column($driver_token,'device_token');

        /* $latlong = array_map('trim', explode(",", $ride_request->pickup_latlong));
         $lat = $latlong[0];
         $long = $latlong[1];
         $get_provider_token = Provider::query()->select('providers.id', 'providers.device_token')
             ->whereRaw(DB::raw("(6371 * acos( cos( radians(" . $lat . ") ) * cos( radians( current_lat ) )  * cos( radians( current_long ) - radians(" . $long . ") ) + sin( radians(" . $lat . ") ) * sin(radians( current_lat ) ) ) ) < " . $radius))
             ->join('provider_services', 'provider_services.provider_id', '=', 'providers.id')
             ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
             ->join('transport_driver_details', 'transport_driver_details.provider_service_id', '=', 'provider_services.id')
             ->where('service_category.id', $service_cat_id)
             ->where('providers.status', 1)
             ->where('providers.login_device', 1)
             ->where('provider_services.status', 1)
             ->where('provider_services.current_status', 1)
             ->where('transport_driver_details.driver_service', 1)
             ->where('transport_driver_details.rental_service', 1)
             ->where('transport_driver_details.vehicle_type_id', $ride_request->vehicle_type_id)
             ->where('transport_driver_details.last_online_date_time', '>=', $expire_date_time)
             ->whereNotNull('transport_driver_details.current_lat')
             ->whereNotNull('transport_driver_details.current_long')
             ->whereNotNull('providers.access_token')
             ->whereNotNull('providers.device_token');
         $provider_token = $get_provider_token->get()->pluck('device_token')->toArray();*/

        $general_settings = GeneralSettings::query()->select('fcm_server_key')->first();
        if ($general_settings == Null || $general_settings->fcm_server_key == Null) {
            return response()->json([
                'status' => 0,
                'message' => "Something want wrong!",
            ]);
        }

        $pickup = array_map('trim', explode(",", $ride_request->pickup_latlong));
        $destination = array_map('trim', explode(",", $ride_request->destination_latlong));
        $way_points[] = [
            "address" => $ride_request->pickup_address,
            "address_lat" => trim($pickup[0]),
            "address_long" => trim($pickup[1])
        ];
        $way_points[] = [
            "address" => $ride_request->destination_address,
            "address_lat" => trim($destination[0]),
            "address_long" => trim($destination[1])
        ];

        $server_key = trim($general_settings->fcm_server_key);

        //To driver Assign
        $title = $ride_request->service_cat_name;
        $extraNotificationData = [
            'title' => $title . "",
            'title_code' => "22",
            'sound' => "true",
            'description' => "New Request",
            'text' => "New Request",
            'body' => "New Request",
            'message_code' => "90",
            'message' => "New Request",
            'request_id' => $ride_request->request_id . "",
            'service_category_id' => $ride_request->service_cat_id . "",
            'service_category_name' => $ride_request->service_cat_name . "",
            'service_category_icon' => $ride_request->service_cat_icon != Null ? url('/assets/images/service-category/' . $ride_request->service_cat_icon) . '' : '',
            "address_list" => json_encode($way_points),
            'notification_type' => "1",
            'is_way_points' => "0",
            'schedule_date_time' => $ride_request->pickup_datetime . "",
            'pickup_datetime' => $ride_request->pickup_datetime . "",
            'drop_datetime' => $ride_request->drop_datetime . "",
            'schedule_display_date_time' => Date('h:i, d M Y', strtotime($ride_request->pickup_datetime)),
            'payment_type' => $ride_request->payment_type . "",
            'request_timeout' => $timeout . "",
            "click_action" => "FLUTTER_NOTIFICATION_CLICK"
        ];

        $response_array = $this->sendFlowNotification($provider_token,$extraNotificationData,1);

        return $response_array;
    }

    //courier ride request
    public function driverTransportCourierRequestNotification($service_cat_id, $request_id)
    {
        $ride_request = TransportRideBook::query()->select('user_ride_booking.id as request_id',
            'user_ride_booking.pickup_datetime',
            'user_ride_booking.pickup_address',
            'user_ride_booking.ride_type',
            'user_ride_booking.is_way_point',
            'user_ride_booking.no_of_retry',
            'user_ride_booking.driver_algorithm',
            'user_ride_booking.pickup_latlong',
            'user_ride_booking.destination_address',
            'user_ride_booking.destination_latlong',
            'user_ride_booking.payment_type',
            'user_ride_booking.total_pay',
            'user_ride_booking.admin_commission',
            'user_ride_booking.vehicle_type_id',
            'service_category.id as service_cat_id',
            'service_category.name as service_cat_name',
            'service_category.icon_name as service_cat_icon',
            'user_courier_service_details.goods_height',
            'user_courier_service_details.goods_width',
            'user_courier_service_details.goods_weight')
            ->join('service_category', 'service_category.id', '=', 'user_ride_booking.service_cat_id')
            ->join('user_courier_service_details', 'user_courier_service_details.ride_id', '=', 'user_ride_booking.id')
            ->where('user_ride_booking.id', $request_id)->first();
        if ($ride_request != null) {
            $service_setting = ServiceSettings::query()->where('service_cat_id', $service_cat_id)->first();
            if ($service_setting != Null) {
                $radius = $service_setting->provider_search_radius;
                $timeout = $service_setting->provider_accept_timeout;
            } else {
                $radius = 5;
                $timeout = 60;
            }
            $expire_date_time = date('Y-m-d h:i:s', strtotime("-60 minutes"));

            $general_settings = GeneralSettings::query()->select('fcm_server_key')->first();
            if ($general_settings == Null || $general_settings->fcm_server_key == Null) {
                return response()->json([
                    'status' => 0,
                    'message' => "Something want wrong!"
                ]);
            }

            if($ride_request->driver_algorithm == 0){
//                $this->driverTransportCourierAndroidRequestNotification($service_cat_id, $expire_date_time, $ride_request, $timeout, $radius, $general_settings->fcm_server_key);
//                $this->driverTransportCourierIosRequestNotification($service_cat_id, $expire_date_time, $ride_request, $timeout, $radius, $general_settings->fcm_server_key);
                $this->driverTransportCourierFlutterRequestNotification($service_cat_id, $expire_date_time, $ride_request, $timeout, $radius, $general_settings->fcm_server_key);
            } else{
                $driver_token = Null;
                $ride_driver_token = TransportRideDriverToken::query()->where("ride_id", $ride_request->request_id)->where("service_cat_id", $service_cat_id)->first();
                if ($ride_driver_token != Null) {
                    if ($ride_driver_token->android_tokens != Null && $ride_driver_token->android_tokens != '') {
                        $driver_token = $ride_driver_token->android_tokens;
                    }
                }
                $this->driverTransportCourierAllRequestNotification($service_cat_id, $expire_date_time, $ride_request, $timeout, $radius, $general_settings->fcm_server_key,$driver_token);
            }
            return "success";
        }
    }

    public function driverTransportCourierAllRequestNotification($service_cat_id, $expire_date_time, $ride_request, $timeout, $radius, $fcm_server_key,$driver_token = Null)
    {
        if ($driver_token == Null) {
            if($ride_request->no_of_retry == 0 || $driver_token == Null){
                $driver_token = $this->getDriverForRide($service_cat_id, $expire_date_time, $ride_request, 0, $radius);
                $get_provider_token_list = $driver_token;
                $provider_token = isset($driver_token[0]['device_token']) ? $driver_token[0]['device_token'] : '';
                $login_device = isset($driver_token[0]['login_device']) ? $driver_token[0]['login_device'] : 0;
                $language = isset($driver_token[0]['language']) ? $driver_token[0]['language'] : 'en';
                unset($driver_token[0]);
                $provider_ids = implode(',', array_column($driver_token, 'id'));
            } else{
                $ride_driver_token = TransportRideDriverToken::query()
                    ->where('ride_id', $ride_request->request_id)
                    ->where('service_cat_id', $service_cat_id)
                    ->first();
                if($ride_driver_token->android_tokens != Null) {
                    $provider_ids = explode(',', $ride_driver_token->android_tokens);
                    $first_provider_id = isset($provider_ids[0]) ? $provider_ids[0] : 0;
                    $provider = Provider::query()->select('device_token', 'login_device')->where('id', $first_provider_id)->whereNull('providers.deleted_at')->first();
                    $provider_token = $provider->device_token != Null ? $provider->device_token : '';
                    $login_device = $provider->login_device;
                    $language = $provider->language != Null ? $provider->language : 'en';
                    unset($provider_ids[0]);
                    $provider_ids = implode(',', $provider_ids);
                }
            }
        } else {
            $provider_ids = explode(',', $driver_token);
            $first_provider_id = isset($provider_ids[0]) ? $provider_ids[0] : 0;
            $provider = Provider::query()->select('device_token','login_device')->where('id', $first_provider_id)->whereNull('providers.deleted_at')->first();
            $provider_token = $provider->device_token != Null ? $provider->device_token : '';
            $login_device = $provider->login_device;
            $language = $provider->language != Null ? $provider->language : 'en';
            unset($provider_ids[0]);
            $provider_ids = implode(',', $provider_ids);
        }
        $ride_driver_token = TransportRideDriverToken::query()->where('ride_id', $ride_request->request_id)->first();
        if ($ride_driver_token != Null) {
            $ride_driver_token->android_tokens = isset($provider_ids) ? $provider_ids : Null;;
            $ride_driver_token->expire_date_time = date('Y-m-d H:i:s', time() + $timeout);
            $ride_driver_token->save();
        } else {
            $ride_driver_token = new TransportRideDriverToken();
            $ride_driver_token->ride_id = $ride_request->request_id;
            $ride_driver_token->service_cat_id = $ride_request->service_cat_id;
            $ride_driver_token->expire_date_time = date('Y-m-d H:i:s', time() + $timeout);
            $ride_driver_token->android_tokens = isset($provider_ids) ? $provider_ids : Null;;
            $ride_driver_token->save();
        }

        $server_key = trim($fcm_server_key);

        $pickup = array_map('trim', explode(",", $ride_request->pickup_latlong));
        $destination = array_map('trim', explode(",", $ride_request->destination_latlong));
        $way_points[] = [
            "address" => $ride_request->pickup_address,
            "address_lat" => trim($pickup[0]),
            "address_long" => trim($pickup[1])
        ];
        if ($ride_request->is_way_point == 1) {
            $ride_way_point = UserRideWayPoint::query()->where('ride_id', $ride_request->request_id)->first();
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
                        "address" => $ride_way_point->way_point_1,
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
            "address" => $ride_request->destination_address,
            "address_lat" => trim($destination[0]),
            "address_long" => trim($destination[1])
        ];

        $success = null;
        $failure = null;

        //To driver Assign
        if(isset($provider_ids) && isset($login_device)){
            $title = $ride_request->service_cat_name;
            $extraNotificationData = [
                'title' => $title . "",
                'title_code' => "22",
                'sound' => "true",
                'description' => __('driver_messages.90', [],$language),
                'message_code' => "90",
                'message' => __('driver_messages.90', [],$language),
                'request_id' => $ride_request->request_id . "",
                'service_category_id' => $ride_request->service_cat_id . "",
                'service_category_name' => $ride_request->service_cat_name . "",
                'service_category_icon' => $ride_request->service_cat_icon != Null ? url('/assets/images/service-category/' . $ride_request->service_cat_icon) . "" : '',
                "address_list" => json_encode($way_points),
                'notification_type' => $ride_request->ride_type . "",
                'is_way_points' => $ride_request->is_way_point . "",
                'schedule_date_time' => $ride_request->pickup_datetime . "",
                'schedule_display_date_time' => Date('h:i, d M Y', strtotime($ride_request->pickup_datetime)),
                'payment_type' => $ride_request->payment_type . "",
                'goods_height_width' => $ride_request->goods_height . ' X ' . $ride_request->goods_width . "",
                'goods_weight' => $ride_request->goods_weight . "",
                'driver_algorithm' => $ride_request->driver_algorithm . "",
                'request_timeout' => $timeout . ""
            ];
        }

        if ($get_provider_token_list != Null) {
            foreach ($get_provider_token_list as $key => $provider) {
                $driver_request_notify = new DriverNewRequestNotification();
                $driver_request_notify->driver_id = $provider['id'];
                $driver_request_notify->request_id = $ride_request->request_id;
                $driver_request_notify->service_cat_id = $ride_request->service_cat_id;
                $driver_request_notify->notification_data = isset($extraNotificationData) ? json_encode($extraNotificationData) : '';
                $driver_request_notify->save();
                $get_provider_token[] = [
                    $provider['id'],
                ];
            }
        }

        $response_array = $this->sendFlowNotification($provider_token,$extraNotificationData,1);

        if (!empty($response_array) && !empty($response_array['cURL_success_response'])){
            if ($ride_driver_token != Null) {
                $data = [
                    "ride_driver_token_id" => $ride_driver_token->id,
                ];
                $sendNotificationJob = (new NearestAlgoSendDriverNotification($data))->delay(now()->addSeconds($timeout));
                $dd = app('Illuminate\Contracts\Bus\Dispatcher')->dispatch($sendNotificationJob);
            }
        }else{
            $driver_token = Null;
            $ride_driver_token = TransportRideDriverToken::query()->where("ride_id", $ride_request->request_id)->where("service_cat_id", $service_cat_id)->first();
            if ($ride_driver_token != Null) {
                if ($ride_driver_token->android_tokens != Null) {
                    $driver_token = $ride_driver_token->android_tokens;
                    $this->driverTransportCourierAllRequestNotification($service_cat_id, $expire_date_time, $ride_request, $timeout, $radius, $fcm_server_key,$driver_token);
                }
            }
        }

        return $response_array;
    }

    //courier ride Flutter request
    public function driverTransportCourierFlutterRequestNotification($service_cat_id, $expire_date_time, $ride_request, $timeout, $radius, $fcm_server_key,$driver_token = Null)
    {
        $driver_token = $this->getDriverForRide($service_cat_id, $expire_date_time, $ride_request, 3, $radius);
        $get_provider_token_list = $driver_token;
        $provider_token = array_column($driver_token,'device_token');

        /*$latlong = array_map('trim', explode(",", $ride_request->pickup_latlong));
        $lat = $latlong[0];
        $long = $latlong[1];
        $get_provider_token = [];
        if ($ride_request->vehicle_type_id == $this->courier_scooter) {
            $vehicle_type_list = TransportVehicleType::where('service_cat_id', $this->bike_ride)->get()->pluck('id')->toArray();
            $get_provider_token_list = Provider::select('providers.id', 'providers.device_token')
                ->whereRaw(DB::raw("(6371 * acos( cos( radians(" . $lat . ") ) * cos( radians( current_lat ) )  * cos( radians( current_long ) - radians(" . $long . ") ) + sin( radians(" . $lat . ") ) * sin(radians( current_lat ) ) ) ) < " . $radius))
                ->join('provider_services', 'provider_services.provider_id', '=', 'providers.id')
                ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
                ->join('transport_driver_details', 'transport_driver_details.provider_service_id', '=', 'provider_services.id')
                ->where('service_category.id', $this->bike_ride)
                ->where('providers.status', 1)
                ->where('providers.login_device', 1)
                ->where('provider_services.status', 1)
                ->where('provider_services.current_status', 1)
                ->where('transport_driver_details.courier_boy', 1)
                ->whereIN('transport_driver_details.vehicle_type_id', $vehicle_type_list)
                ->where('transport_driver_details.last_online_date_time', '>=', $expire_date_time)
                ->whereNotNull('transport_driver_details.current_lat')
                ->whereNotNull('transport_driver_details.current_long')
                ->whereNotNull('providers.access_token')
                ->whereNotNull('providers.device_token')
                ->get();
        } elseif ($ride_request->vehicle_type_id == 14){
            $vehicle_type_list = TransportVehicleType::whereIn('service_cat_id', [$this->taxi_ride])->get()->pluck('id')->toArray();
            $get_provider_token_list = Provider::select('providers.id', 'providers.device_token')
                ->whereRaw(DB::raw("(6371 * acos( cos( radians(" . $lat . ") ) * cos( radians( current_lat ) )  * cos( radians( current_long ) - radians(" . $long . ") ) + sin( radians(" . $lat . ") ) * sin(radians( current_lat ) ) ) ) < " . $radius))
                ->join('provider_services', 'provider_services.provider_id', '=', 'providers.id')
                ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
                ->join('transport_driver_details', 'transport_driver_details.provider_service_id', '=', 'provider_services.id')
                ->whereIn('service_category.id', [$this->taxi_ride])
                ->where('providers.status', 1)
                ->where('provider_services.status', 1)
                ->where('provider_services.current_status', 1)
                ->where('transport_driver_details.courier_boy', 1)
                ->whereIN('transport_driver_details.vehicle_type_id', $vehicle_type_list)
                ->where('transport_driver_details.last_online_date_time', '>=', $expire_date_time)
                ->whereNotNull('transport_driver_details.current_lat')
                ->whereNotNull('transport_driver_details.current_long')
                ->whereNotNull('providers.access_token')
                ->whereNotNull('providers.device_token')
                ->get();
        }  else {
            $get_provider_token_list = Provider::select('providers.id', 'providers.device_token')
                ->whereRaw(DB::raw("(6371 * acos( cos( radians(" . $lat . ") ) * cos( radians( current_lat ) )  * cos( radians( current_long ) - radians(" . $long . ") ) + sin( radians(" . $lat . ") ) * sin(radians( current_lat ) ) ) ) < " . $radius))
                ->join('provider_services', 'provider_services.provider_id', '=', 'providers.id')
                ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
                ->join('transport_driver_details', 'transport_driver_details.provider_service_id', '=', 'provider_services.id')
                ->where('service_category.id', $service_cat_id)
                ->where('providers.status', 1)
                ->where('providers.login_device', 1)
                ->where('provider_services.status', 1)
                ->where('provider_services.current_status', 1)
                ->where('transport_driver_details.courier_boy', 1)
                ->where('transport_driver_details.vehicle_type_id', $ride_request->vehicle_type_id)
                ->where('transport_driver_details.last_online_date_time', '>=', $expire_date_time)
                ->whereNotNull('transport_driver_details.current_lat')
                ->whereNotNull('transport_driver_details.current_long')
                ->whereNotNull('providers.access_token')
                ->whereNotNull('providers.device_token')
                ->get();
        }*/
        $server_key = trim($fcm_server_key);

        $pickup = array_map('trim', explode(",", $ride_request->pickup_latlong));
        $destination = array_map('trim', explode(",", $ride_request->destination_latlong));
        $way_points[] = [
            "address" => $ride_request->pickup_address,
            "address_lat" => trim($pickup[0]),
            "address_long" => trim($pickup[1])
        ];
        if ($ride_request->is_way_point == 1) {
            $ride_way_point = UserRideWayPoint::query()->where('ride_id', $ride_request->request_id)->first();
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
                        "address" => $ride_way_point->way_point_1,
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
            "address" => $ride_request->destination_address,
            "address_lat" => trim($destination[0]),
            "address_long" => trim($destination[1])
        ];

        //To driver Assign
        $title = $ride_request->service_cat_name;
        $extraNotificationData = [
            'title' => $title . "",
            'title_code' => "22",
            'sound' => "true",
            'description' => "New Request",
            'body' => "New Request",
            'message_code' => "90",
            'message' => "New Request",
            'request_id' => $ride_request->request_id . "",
            'service_category_id' => $ride_request->service_cat_id . "",
            'service_category_name' => $ride_request->service_cat_name . "",
            'service_category_icon' => $ride_request->service_cat_icon != Null ? url('/assets/images/service-category/' . $ride_request->service_cat_icon) . '' : '',
            "address_list" => json_encode($way_points),
            'notification_type' => $ride_request->ride_type . "",
            'is_way_points' => $ride_request->is_way_point . "",
            'schedule_date_time' => $ride_request->pickup_datetime,
            'schedule_display_date_time' => Date('h:i, d M Y', strtotime($ride_request->pickup_datetime)),
            'payment_type' => $ride_request->payment_type . "",
            'goods_height_width' => $ride_request->goods_height . ' X ' . $ride_request->goods_width . "",
            'goods_weight' => $ride_request->goods_weight . "",
            'driver_algorithm' => $ride_request->driver_algorithm . "",
            'request_timeout' => $timeout . "",
            "click_action" => "FLUTTER_NOTIFICATION_CLICK"
        ];
        if ($get_provider_token_list != Null) {
            foreach ($get_provider_token_list as $key => $provider) {
                //$driver_request_notify_check = DriverNewRequestNotification::query()->where('driver_id', $provider->id)->first();
                //if ($driver_request_notify_check == Null) {
                $driver_request_notify = new DriverNewRequestNotification();
                $driver_request_notify->driver_id = $provider['id'];
                $driver_request_notify->request_id = $ride_request->request_id;
                $driver_request_notify->service_cat_id = $ride_request->service_cat_id;
                $driver_request_notify->notification_data = json_encode($extraNotificationData);
                $driver_request_notify->save();

                $get_provider_token[] = [
                    $provider['id'],
                ];
                //}
            }
        }

        $response_array = $this->sendFlowNotification($provider_token,$extraNotificationData,1);

        return $response_array;
    }

    //delivery ride request
    public function driverDeliveryRequestNotification($service_cat_id, $request_id)
    {
        $ride_request = UserProductBooking::query()->select(
            'user_store_product_booking.id as request_id',
            'user_store_product_booking.created_at as pickup_datetime',
            'store_details.address as pickup_address',
            'store_details.address_lat as pickup_lat',
            'store_details.id as store_id',
            'store_details.is_private_driver_notification as is_private_driver_notification',
            'user_store_product_booking.driver_algorithm',
            'store_details.address_long as pickup_long',
            'user_store_product_booking.delivery_address as destination_address',
            'user_store_product_booking.lat_long as destination_latlong',
            'user_store_product_booking.payment_type',
            'user_store_product_booking.admin_commission',
            'user_store_product_booking.total_pay',
            'service_category.id as service_cat_id',
            'service_category.name as service_cat_name',
            'store_details.name as store_name',
            'service_category.icon_name as service_cat_icon')
            ->join('service_category', 'service_category.id', '=', 'user_store_product_booking.service_cat_id')
            ->join('store_details', 'store_details.id', '=', 'user_store_product_booking.store_id')
            ->where('user_store_product_booking.id', $request_id)->first();
        if ($ride_request != null) {
            $service_setting = ServiceSettings::query()->where('service_cat_id', $service_cat_id)->first();

            if ($service_setting != Null) {
                $radius = $service_setting->provider_search_radius;
                $timeout = $service_setting->provider_accept_timeout;
            } else {
                $radius = 5;
                $timeout = 60;
            }
            $general_settings = GeneralSettings::query()->select('fcm_server_key')->first();
            if ($general_settings == Null || $general_settings->fcm_server_key == Null) {
                return response()->json([
                    'status' => 0,
                    'message' => "Something want wrong!"
                ]);
            }
            $expire_date_time = date('Y-m-d h:i:s', strtotime("-60 minutes"));

            if($ride_request->driver_algorithm == 0){
//                $this->driverDeliveryAndroidRequestNotification($expire_date_time, $ride_request, $timeout, $radius, $general_settings->fcm_server_key);
//                $this->driverDeliveryIosRequestNotification($expire_date_time, $ride_request, $timeout, $radius, $general_settings->fcm_server_key);
                $this->driverDeliveryFlutterRequestNotification($expire_date_time, $ride_request, $timeout, $radius, $general_settings->fcm_server_key);
            } else{
                $driver_token = Null;
                $ride_driver_token = TransportRideDriverToken::query()->where("delivery_id", $ride_request->request_id)->where("service_cat_id", $service_cat_id)->first();
                if ($ride_driver_token != Null) {
                    if ($ride_driver_token->android_tokens != Null && $ride_driver_token->android_tokens != '') {
                        $driver_token = $ride_driver_token->android_tokens;
                    }
                }
                $this->driverDeliveryAllRequestNotification($expire_date_time, $ride_request, $timeout, $radius, $general_settings->fcm_server_key,$driver_token);
            }
            return "success";

        } else {
            return "something went to wrong";
        }
    }

    public function driverDeliveryAllRequestNotification($expire_date_time, $ride_request, $timeout, $radius, $fcm_server_key,$driver_token = Null)
    {
        $get_provider_token_list = Null;
        $ride_driver_token = Null;
        if ($driver_token == Null) {
            $driver_token = $this->getDriverForRide(0, $expire_date_time, $ride_request, 0, $radius);
            $get_provider_token_list = $driver_token;
            $provider_token = isset($driver_token[0]['device_token']) ? $driver_token[0]['device_token'] : '';
            $login_device = isset($driver_token[0]['login_device']) ? $driver_token[0]['login_device'] : 0;
            $language = isset($driver_token[0]['language']) ? $driver_token[0]['language'] : 'en';
            unset($driver_token[0]);
            $provider_ids = implode(',', array_column($driver_token, 'id'));
        } else {
            $provider_ids = explode(',', $driver_token);
            $first_provider_id = isset($provider_ids[0]) ? $provider_ids[0] : 0;
            $provider = Provider::query()->select('device_token','login_device')->where('id', $first_provider_id)->whereNull('providers.deleted_at')->first();
            $provider_token = $provider->device_token != Null ? $provider->device_token : '';
            $login_device = $provider->login_device;
            $language = $provider->language != Null ? $provider->language : 'en';
            unset($provider_ids[0]);
            $provider_ids = implode(',', $provider_ids);
        }
        $ride_driver_token = TransportRideDriverToken::query()->where('delivery_id', $ride_request->request_id)->first();
        if ($ride_driver_token != Null) {
            $ride_driver_token->android_tokens = $provider_ids;
            $ride_driver_token->expire_date_time = date('Y-m-d H:i:s', time() + $timeout);
            $ride_driver_token->save();
        } else {
            $ride_driver_token = new TransportRideDriverToken();
            $ride_driver_token->delivery_id = $ride_request->request_id;
            $ride_driver_token->service_cat_id = $ride_request->service_cat_id;
            $ride_driver_token->expire_date_time = date('Y-m-d H:i:s', time() + $timeout);;
            $ride_driver_token->android_tokens = $provider_ids;
            $ride_driver_token->save();
        }
        $server_key = trim($fcm_server_key);
        $destination = array_map('trim', explode(",", $ride_request->destination_latlong));
        $way_points[] = [
            "address" => $ride_request->pickup_address,
            "address_lat" => trim($ride_request->pickup_lat),
            "address_long" => trim($ride_request->pickup_long)
        ];
        $way_points[] = [
            "address" => $ride_request->destination_address,
            "address_lat" => trim($destination[0]),
            "address_long" => trim($destination[1])
        ];

        $success = null;
        $failure = null;

        //To driver Assign
        $title = $ride_request->service_cat_name;
        $extraNotificationData = [
            'title' => $title . "",
            'title_code' => "22",
            'sound' => "true",
            'description' => __('driver_messages.90', [],$language),
            'message_code' => "41",
            'message' => __('driver_messages.90', [],$language),
            'request_id' => $ride_request->request_id . "",
            'service_category_id' => $ride_request->service_cat_id . "",
            'service_category_name' => ($ride_request->service_cat_name != Null) ? $ride_request->service_cat_name . "" : "",
            'store_name' => ($ride_request->store_name != Null) ? $ride_request->store_name . "" : "",
            'service_category_icon' => $ride_request->service_cat_icon != Null ? url('/assets/images/service-category/' . $ride_request->service_cat_icon) . '' : '',
            "address_list" => json_encode($way_points),
            'notification_type' => "0",
            'is_way_points' => "0",
            'schedule_date_time' => $ride_request->pickup_datetime,
            'schedule_display_date_time' => Date('h:i, d M Y', strtotime($ride_request->pickup_datetime)),
            'payment_type' => $ride_request->payment_type . "",
            'driver_algorithm' => $ride_request->driver_algorithm . "",
            'request_timeout' => $timeout . ""
        ];

        if ($get_provider_token_list != Null) {
            foreach ($get_provider_token_list as $key => $provider) {
                $driver_request_notify = new DriverNewRequestNotification();
                $driver_request_notify->driver_id = $provider['id'];
                $driver_request_notify->request_id = $ride_request->request_id;
                $driver_request_notify->service_cat_id = $ride_request->service_cat_id;
                $driver_request_notify->notification_data = json_encode($extraNotificationData);
                $driver_request_notify->save();

                $get_provider_token[] = [
                    $provider['id'],
                ];
            }
        }

        $response_array = $this->sendFlowNotification($provider_token,$extraNotificationData,1);

        if (!empty($response_array) && !empty($response_array['cURL_success_response'])){
            if ($ride_driver_token != Null) {
                $data = [
                    "ride_driver_token_id" => $ride_driver_token->id,
                ];
                $sendNotificationJob = (new NearestAlgoSendDriverNotification($data))->delay(now()->addSeconds($timeout));
                $dd = app('Illuminate\Contracts\Bus\Dispatcher')->dispatch($sendNotificationJob);
            }
        }else{
            $driver_token = Null;
            $ride_driver_token = TransportRideDriverToken::query()->where("ride_id", $ride_request->request_id)->where("service_cat_id", $ride_request->service_cat_id)->first();
            if ($ride_driver_token != Null) {
                if ($ride_driver_token->android_tokens != Null) {
                    $driver_token = $ride_driver_token->android_tokens;
                    $this->driverDeliveryAllRequestNotification($expire_date_time, $ride_request, $timeout, $radius, $fcm_server_key,$driver_token);
                }
            }
        }

        return $response_array;
    }

    //delivery ride Flutter request
    public function driverDeliveryFlutterRequestNotification($expire_date_time, $ride_request, $timeout, $radius, $fcm_server_key,$driver_token = Null)
    {
        $driver_token = $this->getDriverForRide(0, $expire_date_time, $ride_request, 3, $radius);
        $get_provider_token_list = $driver_token;
        $provider_token = array_column($driver_token,'device_token');
        $server_key = trim($fcm_server_key);
        $way_points[] = [
            "address" => $ride_request->pickup_address,
            "address_lat" => trim($ride_request->pickup_lat),
            "address_long" => trim($ride_request->pickup_long)
        ];
        $destination = array_map('trim', explode(",", $ride_request->destination_latlong));
        $way_points[] = [
            "address" => $ride_request->destination_address,
            "address_lat" => trim($destination[0]),
            "address_long" => trim($destination[1])
        ];
        //To driver Assign
        $title = $ride_request->service_cat_name;
        $extraNotificationData = [
            'title' => $title . "",
            'title_code' => "22",
            'sound' => "true",
            'description' => "New Order",
            'message_code' => "41",
            'message' => "New Order",
            'body' => "New Order",
            'request_id' => $ride_request->request_id . "",
            'service_category_id' => $ride_request->service_cat_id . "",
            'service_category_name' => ($ride_request->service_cat_name != Null) ? $ride_request->service_cat_name . "" : "",
            'store_name' => ($ride_request->store_name != Null) ? $ride_request->store_name . "" : "",
            'service_category_icon' => $ride_request->service_cat_icon != Null ? url('/assets/images/service-category/' . $ride_request->service_cat_icon) . "" : '',
            "address_list" => json_encode($way_points),
            'notification_type' => "0",
            'is_way_points' => "0",
            'schedule_date_time' => $ride_request->pickup_datetime,
            'schedule_display_date_time' => Date('h:i, d M Y', strtotime($ride_request->pickup_datetime)),
            'payment_type' => $ride_request->payment_type . "",
            'driver_algorithm' => $ride_request->driver_algorithm . "",
            'request_timeout' => $timeout . "",
            "click_action" => "FLUTTER_NOTIFICATION_CLICK"
        ];

        if ($get_provider_token_list != Null) {
            foreach ($get_provider_token_list as $key => $provider) {
                $driver_request_notify = new DriverNewRequestNotification();
                $driver_request_notify->driver_id = $provider['id'];
                $driver_request_notify->request_id = $ride_request->request_id;
                $driver_request_notify->service_cat_id = $ride_request->service_cat_id;
                $driver_request_notify->notification_data = json_encode($extraNotificationData);
                $driver_request_notify->save();

                $get_provider_token[] = [
                    $provider['id'],
                ];
            }
        }

        $response_array = $this->sendFlowNotification($provider_token,$extraNotificationData,1);

        return $response_array;
    }

    public function driverAcceptedTransportRequestNotification($request_id, $driver_service_id, $driver_id, $currency)
    {
        $ride = TransportRideBook::query()->where('id', $request_id)->first();
        if ($ride != Null) {
            if ($ride->status == 1) {
                return response()->json([
                    "status" => 0,
//                    "message" => "Sorry, You are too late to the accept ride",
                    "message" => __('driver_messages.23'),
                    "message_code" => 23,
                ]);
            }
            elseif ($ride->status == 4) {
                return response()->json([
                    "status" => 0,
//                    "message" => "Sorry, Your ride request is cancelled by user",
                    "message" => __('driver_messages.24'),
                    "message_code" => 24,
                ]);
            }
            elseif ($ride->status == 10) {
                return response()->json([
                    "status" => 0,
//                    "message" => "Sorry, Your ride request is failed",
                    "message" => __('driver_messages.25'),
                    "message_code" => 25,
                ]);
            }
            $get_driver_id = TransportDriverDetails::query()->select('transport_driver_details.id','transport_driver_details.delivery_boy',
                DB::raw("CONCAT(COALESCE(providers.first_name,''),' ',COALESCE(providers.last_name,'')) as driver_name"), 'providers.email')
                ->join('provider_services', 'provider_services.id', 'transport_driver_details.provider_service_id')
                ->join('providers', 'providers.id', 'provider_services.provider_id')
                ->where('transport_driver_details.provider_service_id', $driver_service_id)
                ->where('providers.status', 1)
                ->where('provider_services.status', 1)
                ->where('provider_services.current_status', 1)
                ->whereNull('providers.deleted_at')
                ->first();
            if ($get_driver_id != Null) {
                //offline driver status
                $driver_service = ProviderServices::query()->where('id', $driver_service_id)->first();
                if ($driver_service != Null) {
                    $user = User::query()->where('id', $ride->user_id)->first();
                    if ($user == Null) {
                        return response()->json([
                            "status" => 0,
//                            "message" => "user not found",
                            "message" => __('driver_messages.9'),
                            "message_code" => 9,
                        ]);
                    }
                    if ($ride->ride_type == 0) {
                        if ($get_driver_id->delivery_boy != 1) {
                            $driver_service->current_status = 0;
                            $driver_service->save();
                        }

                        $ride->status = 1;
                    } else {
                        $ride->status = 2;
                    }
                    $ride->driver_name = $get_driver_id->driver_name;
                    $ride->driver_id = $get_driver_id->id;
                    $ride->save();

                    if ($ride->ride_type == 0) {
                        ProviderUserRunningService::query()->where('provider_id', $driver_id)->where('user_id', $ride->user_id)->where('service_cat_id', $ride->service_cat_id)->where('booking_id', $ride->id)->delete();
                        $provider_running_service = new ProviderUserRunningService();
                        $provider_running_service->provider_id = $driver_id;
                        $provider_running_service->user_id = $ride->user_id;
                        $provider_running_service->service_cat_id = $ride->service_cat_id;
                        $provider_running_service->booking_id = $ride->id;
                        $provider_running_service->save();

                        $user_running_ride = UserRunningRide::query()->where('user_id', $ride->user_id)->where('service_cat_id', $ride->service_cat_id)->where('booking_id', $ride->id)->first();
                        if ($user_running_ride == Null) {
                            $user_running_ride = new UserRunningRide();
                            $user_running_ride->user_id = $ride->user_id;
                            $user_running_ride->service_cat_id = $ride->service_cat_id;
                            $user_running_ride->booking_id = $ride->id;
                            $user_running_ride->save();
                        }
                    }
                    DriverNewRequestNotification::query()->where('service_cat_id', $ride->service_cat_id)->where('request_id', $ride->id)->delete();
                    TransportRideDriverToken::query()->where('ride_id', $ride->id)->delete();

                    $notification_log = $this->userTransportNotification($ride->id, $user->device_token, $ride->status, $user->login_device,$user->language);

                    $pickup = array_map('trim', explode(",", $ride->pickup_latlong));
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
                                    "address" => $ride_way_point->way_point_1,
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
                    $destination = array_map('trim', explode(",", $ride->destination_latlong));
                    $way_points[] = [
                        "address" => $ride->destination_address,
                        "address_lat" => trim($destination[0]),
                        "address_long" => trim($destination[1])
                    ];
                    if ($user->avatar != Null) {
                        if (filter_var($user->avatar, FILTER_VALIDATE_URL) == true) {
                            $avatar = $user->avatar;
                        } else {
                            $avatar = url('/assets/images/profile-images/customer/' . $user->avatar);
                        }
                    } else {
                        $avatar = "";
                    }
                    $ride_details_arr = [
                        "ride_id" => $ride->id,
                        "user_id" => $user->id,
                        "user_fcm_token" => $user->device_token,
                        "booking_no" => $ride->ride_no,
                        "service_category_id" => $ride->service_cat_id,
                        "user_profile_image" => $avatar,
                        "user_name" => $user->first_name . " " . $user->last_name,
                        "contact_number" => $user->contact_number,
                        "address_list" => $way_points,
                        "flat_no" => '',
                        "landmark" => '',
                        "pickup_datetime" => $ride->pickup_datetime,
                        "total_amount" => round($ride->total_pay * $currency, 2),
                        "payment_type" => $ride->payment_type,
                        "ride_status" => $ride->status,
                        "way_point_status" => $ride->way_point_status,
                    ];

                    $general_settings = request()->get("general_settings");
                    if ($general_settings !=  Null) {
                        if ($general_settings->send_mail == 1) {
                            $service_category = ServiceCategory::query()->where('id', $ride->service_cat_id)->first();
                            if ($service_category != Null) {

                                $service_name = ucwords(strtolower($service_category->name));
                                $user_name = ucwords($ride->user_name);
                                $driver_name = ucwords($get_driver_id->driver_name);
                                $pickup_address = $ride->pickup_address;
                                $destination_address = $ride->destination_address;
                                $pickup_datetime = date("Y-m-d h:i:s", strtotime($ride->pickup_datetime));

                                //sending mail driver
                                try {
                                    $mail_type = "driver_new_ride_request_–_transport";
                                    $to_mail = $get_driver_id->email;
                                    $subject = "You have a new " . $general_settings->mail_site_name . " " . $service_name . " Ride Request";
                                    $disp_data = array("##driver_name##" => $driver_name, "##service_name##" => $service_name,
                                        "##pickup_location##" => $pickup_address, "##destination_location##" => $destination_address,
                                        "##pickup_time_location##" => $pickup_datetime, "##user_name##" => $user_name);
                                    $mail_return_data = $this->sendMail($subject, $to_mail, $mail_type, $disp_data);
                                }
                                catch (\Exception $e) {}

                                if ($general_settings->send_receive_email != Null){
                                    //send mail to admin
                                    try{
                                        $mail_type = "admin_new__ride_request_-_transport";
                                        $to_mail = $general_settings->send_receive_email;
                                        $subject = $driver_name." driver get a new " .$service_name ." ride request" ;
                                        $disp_data = array("##driver_name##"=>$driver_name,"##service_name##"=>$service_name,
                                            "##pickup_location##"=>$pickup_address,"##destination_location##"=>$destination_address,
                                            "##pickup_time_location##"=>$pickup_datetime,"##user_name##"=>$user_name);
                                        $mail_return_data = $this->sendMail($subject,$to_mail,$mail_type,$disp_data);
                                    }
                                    catch (\Exception $e){}
                                }
                            }
                        }
                    }
                    return response()->json([
                        "status" => 1,
                        //"message" => "success!",
                        "message" => __('driver_messages.1'),
                        "message_code" => 1,
                        "driver_current_status" => $driver_service->current_status,
                        "ride_details" => $ride_details_arr,
                        "noti_log" => $notification_log
                    ]);
                } else {
                    return response()->json([
                        "status" => 0,
                        //"message" => "driver service not found!",
                        "message" => __('driver_messages.9'),
                        "message_code" => 9,
                    ]);
                }
            } else {
                return response()->json([
                    "status" => 0,
                    //"message" => "something went to wrong!",
                    "message" => __('driver_messages.9'),
                    "message_code" => 9,
                ]);
            }
        } else {
            return response()->json([
                "status" => 0,
//                "message" => "ride not found!",
                "message" => __('driver_messages.26'),
                "message_code" => 26,
            ]);
        }
    }

    public function driverAcceptedDeliveryRequestNotification($request_id, $driver_service_id, $driver_id, $currency)
    {
        $delivery = UserProductBooking::query()->where('id', $request_id)->first();
        if ($delivery != Null) {
            if ($delivery->status == 1) {
                return response()->json([
                    "status" => 0,
//                    "message" => "store not accepting user request",
                    "message" => __('driver_messages.28'),
                    "message_code" => 28,
                ]);
            } elseif ($delivery->status == 3) {
                return response()->json([
                    "status" => 0,
//                    "message" => "Sorry, Your delivery order is rejected by store",
                    "message" => __('driver_messages.29'),
                    "message_code" => 29,
                ]);
            } elseif ($delivery->status == 4) {
                return response()->json([
                    "status" => 0,
//                    "message" => "Sorry, Your delivery request is cancelled by user",
                    "message" => __('driver_messages.30'),
                    "message_code" => 30,
                ]);
            } elseif ($delivery->delivery_people_id != Null) {
                return response()->json([
                    "status" => 0,
//                    "message" => "Sorry, You are too late to the accept ride",
                    "message" => __('driver_messages.31'),
                    "message_code" => 31,
                ]);
            } else {
                if ($delivery->order_delivery_status == 1) {
                    return response()->json([
                        "status" => 0,
//                        "message" => "Sorry, You are too late to the accept ride",
                        "message" => __('driver_messages.31'),
                        "message_code" => 31,
                    ]);
                } elseif ($delivery->order_delivery_status == 4) {
                    return response()->json([
                        "status" => 0,
//                        "message" => "Sorry, Your delivery request is cancelled by user",
                        "message" => __('driver_messages.30'),
                        "message_code" => 30,
                    ]);
                } elseif ($delivery->order_delivery_status == 10) {
                    return response()->json([
                        "status" => 0,
//                        "message" => "Sorry, Your ride request is failed",
                        "message" => __('driver_messages.25'),
                        "message_code" => 25,
                    ]);
                }
                $get_driver_id = TransportDriverDetails::query()->select('transport_driver_details.id', DB::raw("CONCAT(COALESCE(providers.first_name,''),' ',COALESCE(providers.last_name,'')) as driver_name"))
                    ->join('provider_services', 'provider_services.id', 'transport_driver_details.provider_service_id')
                    ->join('providers', 'providers.id', 'provider_services.provider_id')
                    ->where('transport_driver_details.provider_service_id', $driver_service_id)
                    ->where('providers.status', 1)
                    ->where('provider_services.status', 1)
                    ->where('provider_services.current_status', 1)
                    ->whereNull('providers.deleted_at')
                    ->first();
                if ($get_driver_id != Null) {
                    $user = User::query()->where('id', $delivery->user_id)->first();
                    if ($user == Null) {
                        return response()->json([
                            "status" => 0,
//                            "message" => "user not found",
                            "message" => __('driver_messages.9'),
                            "message_code" => 9,
                        ]);
                    }
                    $store = StoreDetails::query()->select('providers.id', 'providers.device_token', 'providers.login_device', 'providers.language')
                        ->join('provider_services', 'provider_services.id', '=', 'store_details.provider_service_id')
                        ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
                        ->where('store_details.id', $delivery->store_id)
                        ->whereNull('providers.deleted_at')
                        ->first();
                    if ($store == Null) {
                        return response()->json([
                            "status" => 0,
//                            "message" => "store not found",
                            "message" => __('driver_messages.9'),
                            "message_code" => 9,
                        ]);
                    }
                    //offline driver status
                    $driver_service = ProviderServices::query()->where('id', $driver_service_id)->first();
                    if ($driver_service != Null) {
                        // do not off current status for multi delivery
                        /*$driver_service->current_status = 0;*/
                        /*$driver_service->save();*/

                        //set driver id in ride booking
                        $delivery->delivery_people_id = $get_driver_id->id;
                        $delivery->delivery_people_name = $get_driver_id->driver_name;
                        //$delivery->status = 6;
                        $delivery->order_delivery_status = 1;
                        $delivery->save();

                        $check_duplicate = UserProductBookingStatus::query()->where('order_id', $delivery->id)->where('status', 6)->first();
                        if ($check_duplicate == Null) {
                            $store_delivery_status = new UserProductBookingStatus();
                            $store_delivery_status->order_id = $delivery->id;
                            $store_delivery_status->status = 6;
                            $store_delivery_status->save();
                        }

                        //user assign delivery driver notification
                        $this->userAssignDeliveryDriverNotification($delivery->id, $user->device_token, $delivery->order_delivery_status, $user->login_device, $user->language);

                        $this->storeDeliveryDriverNotification($delivery->id, $store->device_token, $delivery->order_delivery_status, $store->login_device,$store->language);
                        $user_profile_url = url('/assets/images/profile-images/customer');

                        $ride_details = UserProductBooking::query()->select('user_store_product_booking.id as ride_id',
                            'user_store_product_booking.order_no as booking_no',
                            'user_store_product_booking.service_cat_id as service_category_id',
                            DB::raw("(CASE WHEN users.avatar != '' THEN (CASE WHEN CHAR_LENGTH(users.avatar) >= 25 THEN users.avatar ELSE concat('$user_profile_url','/',users.avatar) END) ELSE '' END) as user_profile_image"),
                            DB::raw("(concat(users.first_name,' ',users.last_name)) as user_name"),
                            'users.contact_number',
                            'store_details.address as pickup_address',
                            'user_store_product_booking.flat_no',
                            'user_store_product_booking.landmark',
                            DB::raw("(concat(store_details.address_lat,',',store_details.address_long)) as pickup_latlong"),
                            'user_store_product_booking.delivery_address as destination_address',
                            'user_store_product_booking.lat_long as destination_latlong',
                            'user_store_product_booking.created_at as pickup_datetime',
                            'user_store_product_booking.total_pay as total_amount',
                            'user_store_product_booking.payment_type as payment_type',
//                            DB::raw("(CASE WHEN user_store_product_booking.payment_type != 1 THEN (CASE WHEN user_store_product_booking.payment_type != 2 THEN (CASE WHEN user_store_product_booking.payment_type != 3 THEN '' ELSE 'wallet' END) ELSE 'card' END) ELSE 'cash' END) as payment_type"),
                            'user_store_product_booking.order_delivery_status as ride_status')
                            ->join('users', 'users.id', '=', 'user_store_product_booking.user_id')
                            ->join('store_details', 'store_details.id', '=', 'user_store_product_booking.store_id')
                            ->where('user_store_product_booking.id', $delivery->id)
                            ->whereNull('users.deleted_at')
                            ->first();
                        if ($ride_details != Null) {
                            $get_user = UserProductBooking::query()->select('user_id')->where('id', $ride_details->ride_id)->first();
                            if ($get_user != Null) {
                                ProviderUserRunningService::query()->where('provider_id', $driver_id)->where('user_id', $ride_details->user_id)->where('service_cat_id', $ride_details->service_category_id)->where('booking_id', $ride_details->id)->delete();
                                $provider_running_service = new ProviderUserRunningService();
                                $provider_running_service->provider_id = $driver_id;
                                $provider_running_service->user_id = $get_user->user_id;
                                $provider_running_service->service_cat_id = $ride_details->service_category_id;
                                $provider_running_service->booking_id = $ride_details->ride_id;
                                $provider_running_service->save();
                            }

                            $pickup = array_map('trim', explode(",", $ride_details->pickup_latlong));
                            $destination = array_map('trim', explode(",", $ride_details->destination_latlong));
                            $way_points[] = [
                                "address" => $ride_details->pickup_address,
                                "address_lat" => trim($pickup[0]),
                                "address_long" => trim($pickup[1])
                            ];
                            $way_points[] = [
                                "address" => $ride_details->destination_address,
                                "address_lat" => trim($destination[0]),
                                "address_long" => trim($destination[1])
                            ];
                            $ride_details_arr = [
                                "ride_id" => $ride_details->ride_id,
                                "booking_no" => $ride_details->booking_no,
                                "service_category_id" => $ride_details->service_category_id,
                                "user_profile_image" => $ride_details->user_profile_image,
                                "user_name" => $ride_details->user_name,
                                "contact_number" => $ride_details->contact_number,
                                "address_list" => $way_points,
                                "flat_no" => $ride_details->flat_no != Null ? $ride_details->flat_no : '',
                                "landmark" => $ride_details->landmark != Null ? $ride_details->landmark : '',
                                "pickup_datetime" => $ride_details->pickup_datetime,
                                "total_amount" => round($ride_details->total_amount * $currency, 2),
                                "payment_type" => $ride_details->payment_type,
                                "ride_status" => $ride_details->ride_status,
                                "way_point_status" => 0,
                            ];

                            DriverNewRequestNotification::query()->where('service_cat_id', $ride_details->service_category_id)->where('request_id', $ride_details->ride_id)->delete();
                            TransportRideDriverToken::query()->where('ride_id',$delivery->id)->delete();
                            return response()->json([
                                "status" => 1,
//                                "message" => "success!",
                                "message" => __('driver_messages.1'),
                                "message_code" => 1,
                                "driver_current_status" => $driver_service->current_status,
                                "ride_details" => $ride_details_arr
                            ]);
                        } else {
                            return response()->json([
                                "status" => 0,
//                                "message" => "ride details not found!",
                                "message" => __('driver_messages.26'),
                                "message_code" => 26,
                            ]);
                        }
                    } else {
                        return response()->json([
                            "status" => 0,
//                            "message" => "driver service not found!",
                            "message" => __('driver_messages.9'),
                            "message_code" => 9,
                        ]);
                    }
                } else {
                    return response()->json([
                        "status" => 0,
//                        "message" => "something went to wrong!",
                        "message" => __('driver_messages.9'),
                        "message_code" => 9,
                    ]);
                }
            }
        } else {
            return response()->json([
                "status" => 0,
//                "message" => "ride not found!",
                "message" => __('driver_messages.26'),
                "message_code" => 26,
            ]);
        }
    }

    public function driverCancelNotification($request_id,$token, $service_cat_id, $driver_service_id, $login_device, $language, $cancel_by = Null)
    {
        if ($token != "") {
            $driver_service = ProviderServices::query()->where('id', $driver_service_id)->first();
            $driver_service->current_status = 1;
            $driver_service->save();

            $general_settings = GeneralSettings::query()->select('fcm_server_key')->first();
            $service = ServiceCategory::query()->where('id',$service_cat_id)->first();
            if ($general_settings == Null || $general_settings->fcm_server_key == Null) {
                return response()->json([
                    'status' => 0,
                    'message' => __('driver_messages.9'),
                    "message_code" => 9,
                ]);
            }
            $title_code = 42;
            $server_key = trim($general_settings->fcm_server_key);

            $language = $language != Null ? $language : 'en';

            if (in_array($service_cat_id, $this->transport_service_id_array) || in_array($service_cat_id, $this->courier_service_id_array)) {
                if ($cancel_by != Null && strtolower($cancel_by) == "admin") {
//                    $message = "Ride Cancelled by Admin";
                    $message = __('driver_messages.113', [],$language);
                    $message_code = 113;
                } else {
//                    $message = "Ride Cancelled by User";
                    $message = __('driver_messages.35', [],$language);
                    $message_code = 35;
                }
            } elseif (in_array($service_cat_id, $this->delivery_service_id_array)) {
                if ($cancel_by != Null && strtolower($cancel_by) == "admin") {
//                    $message = "Ride Cancelled by Admin";
                    $message = __('driver_messages.111', [],$language);
                    $message_code = 111;
                } else {
//                    $message = "Ride Cancelled by User";
                    $message = __('driver_messages.36', [],$language);
                    $message_code = 36;
                }
                $title_code = 42;
            } else {
                return response()->json([
                    'status' => 0,
                    'message' => __('driver_messages.9'),
                    "message_code" => 9,
                ]);
            }

//            $title = "Cancel Driver Ride";
            $title = __('driver_messages.42', [],$language);
            $extraNotificationData = [
                'title' => $title . "",
                "title_code" => $title_code . "",
                'sound' => "true",
                'notification_type' => "2",
                'message' => $message . "",
                'message_code' => $message_code . "",
                'ride_id' => $request_id . "",
                'service_category_id' => $service_cat_id . "",
                'service_category_name' => $service->name . "",
            ];

            $response = $this->sendFlowNotification($token, $extraNotificationData, 0);
            return $response;
        }
    }

    public function driverCompletedNotification($token, $service_cat_id, $driver_service_id, $login_device,$language)
    {
        if ($token != "") {

            $language = $language != Null ? $language : 'en';

            $driver_service = ProviderServices::query()->where('id', $driver_service_id)->first();
            $driver_service->current_status = 1;
            $driver_service->save();

            $general_settings = GeneralSettings::query()->select('fcm_server_key')->first();
            $service = ServiceCategory::query()->where('id',$service_cat_id)->first();
            if ($general_settings == Null || $general_settings->fcm_server_key == Null) {
                return response()->json([
                    'status' => 0,
                    'message' => __('driver_messages.1'),
                    "message_code" => 9,
                ]);
            }
            $server_key = trim($general_settings->fcm_server_key);
            if (in_array($service_cat_id, $this->transport_service_id_array) || in_array($service_cat_id, $this->courier_service_id_array)) {
//                $message = "ride completed by admin!";
                $message = __('driver_messages.114', [],$language);
                $message_code = 114;
//                $title = "ride completed";
                $title = __('driver_messages.150', [],$language);
                $title_code = 150;
            } elseif (in_array($service_cat_id, $this->delivery_service_id_array)) {
//                $message = "order completed by admin!";
                $message = __('driver_messages.112', [],$language);
                $message_code = 112;
//                $title = "Order completed";
                $title = __('driver_messages.151', [],$language);
                $title_code = 151;
            } else {
                return response()->json([
                    'status' => 0,
                    'message' => __('driver_messages.9'),
                    "message_code" => 9,
                ]);
            }
            $extraNotificationData = [
                'title' => $title . "",
                "title_code" => $title_code . "",
                'sound' => "true",
                'notification_type' => "2",
                'message' => $message . "",
                'message_code' => $message_code . "",
                'service_category_id' => $service_cat_id . "" ,
                'service_category_name' => $service->name . "",
            ];

            $response = $this->sendFlowNotification($token, $extraNotificationData, 0);
            return $response;
        }
    }

    //store notification
    public function storeDeliveryDriverNotification($delivery_id, $device_token, $delivery_status, $login_device,$language)
    {
        if ($delivery_id == Null || $device_token == Null || $delivery_status == Null) {
            return response()->json([
                'status' => 0,
                'message' => __('driver_messages.9'),
                "message_code" => 9,
            ]);
        }
        $general_settings = GeneralSettings::query()->select('fcm_server_key')->first();
        if ($general_settings == Null || $general_settings->fcm_server_key == Null) {
            return response()->json([
                'status' => 0,
                'message' => __('driver_messages.9'),
                "message_code" => 9,
            ]);
        }
        $delivery = UserProductBooking::query()->where('id', $delivery_id)->first();
        if ($delivery == Null) {
            return response()->json([
                'status' => 0,
                'message' => __('driver_messages.9'),
                "message_code" => 9,
            ]);
        }
        $server_key = trim($general_settings->fcm_server_key);

        $language = $language != Null ? $language : 'en';

        if ($delivery_status == 1) {
//            $message = "Driver Accepted Order";
            $message = __('store_messages.37', [] ,$language);
            //$message = "Driver Accepted Order No. #" . $delivery->order_no;
            $message_code = 37;
        } elseif ($delivery_status == 3) {
//            $message = "Driver arrived to the store.";
            $message = __('store_messages.38', [] ,$language);
            $message_code = 38;
        } elseif ($delivery_status == 7) {
//            $message = "Driver Payment Collecting Successfully.";
            $message = __('store_messages.39', [] ,$language);
            $message_code = 39;
        } elseif ($delivery_status == 9) {
//            $message = "Driver completed the Store Delivery.";
            $message = __('store_messages.40', [] ,$language);
            $message_code = 40;
        } else {
            return response()->json([
                'status' => 0,
                'message' => __('driver_messages.9'),
                'message_code' => 9
            ]);
        }

        //notification_type 0=simple, 1= generate notification by user, 2= generate notification by driver
        $title = __('store_messages.43', [] ,$language);
        $extraNotificationData = [
            'title' => $title . "",
            'title_code' => "43",
            'sound' => "true",
            'notification_type' => "2",
            'order_id' => $delivery_id . "",
            'delivery_status' => $delivery_status . "",
            'message' => $message . "",
            'message_code' => $message_code . ""
        ];

        $response = $this->sendFlowNotification($device_token, $extraNotificationData, 0);
        return $response;
    }

    public function storeDeliveryUserNotification($delivery_id, $device_token, $order_status, $login_device,$language)
    {
        if ($delivery_id == Null || $device_token == Null || $order_status == Null) {
            return response()->json([
                'status' => 0,
                'message' => __('driver_messages.9'),
                'message_code' => 9
            ]);
        }
        $general_settings = GeneralSettings::query()->select('fcm_server_key')->first();
        if ($general_settings == Null || $general_settings->fcm_server_key == Null) {
            return response()->json([
                'status' => 0,
                'message' => __('driver_messages.9'),
                'message_code' => 9
            ]);
        }
        $delivery = UserProductBooking::query()->where('id', $delivery_id)->first();
        if ($delivery == Null) {
            return response()->json([
                'status' => 0,
                'message' => __('driver_messages.9'),
                'message_code' => 9
            ]);
        }
        $server_key = trim($general_settings->fcm_server_key);

        $language = $language != Null ? $language : 'en';

        if ($order_status == 1) {
//            $title_name = "New Order";
            $title_name = __('store_messages.41', [] ,$language);
            $title_code = 41;
//            $message = "New Order Placed";
            $message = __('store_messages.43', [] ,$language);
            //$message = "#" . $delivery->order_no . " Order Placed";
            $message_code = 43;
            if ($delivery->taken_away_type == 2) {
//                $message = "Store Pickup";
                $message = __('store_messages.144', [] ,$language);
                $message_code = 144;
            }
        } elseif ($order_status == 4) {
//            $title_name = "Cancel Order";
            $title_name = __('store_messages.42', [] ,$language);
            $title_code = 42;
//            $message = "#" . $delivery->order_no . " " . trim(ucwords(strtolower($delivery->cancel_by))) . " cancelled store delivery";
//            $message = trim(ucwords(strtolower($delivery->cancel_by))) . " cancelled store delivery";
            $message = __('store_messages.44', [] ,$language);
            $message_code = 44;
            if (trim(ucwords(strtolower($delivery->cancel_by))) == "Admin") {
                $message = __('store_messages.111', [] ,$language);
                $message_code = 111;
            }

        } elseif ($order_status == 9) {
//            $title_name = "Order Completed";
            $title_name = __('store_messages.102', [] ,$language);
            $title_code = 102;
//            $message = "Order completed by admin";
            $message = __('store_messages.112', [] ,$language);
            $message_code = 112;
        } else {
            return response()->json([
                'status' => 0,
                'message' => __('driver_messages.9'),
                'message_code' => 9
            ]);
        }

        //notification_type 0=simple, 1= generate notification by user, 2= generate notification by driver
        $title = $title_name;
        $extraNotificationData = [
            'title' => $title . "",
            'title_code' => $title_code . "",
            'sound' => "true",
            'notification_type' => "1",
            'order_id' => $delivery_id . "",
            'order_status' => $order_status . "",
            'message' => $message . "",
            'message_code' => $message_code . ""
        ];

        $response = $this->sendFlowNotification($device_token, $extraNotificationData, 0);
        return $response;
    }

    //user notification
    public function userTransportNotification($ride_id, $device_token, $ride_status, $login_device, $language = Null)
    {
        $language = $language != Null ? $language : 'en';

        if ($ride_id == Null || $device_token == Null || $ride_status == Null) {
            return response()->json([
                'status' => 0,
//                'message' => "Something want wrong!",
                'message' => __('driver_messages.9'),
                'message_code' => 9
            ]);
        }
        $general_settings = GeneralSettings::query()->select('fcm_server_key')->first();
        if ($general_settings == Null || $general_settings->fcm_server_key == Null) {
            return response()->json([
                'status' => 0,
//                'message' => "Something want wrong!",
                'message' => __('driver_messages.9'),
                'message_code' => 9
            ]);
        }
        $ride = TransportRideBook::query()->where('id', $ride_id)->first();
        if ($ride == Null) {
            return response()->json([
                'status' => 0,
//                'message' => "Something want wrong!",
                'message' => __('driver_messages.9'),
                'message_code' => 9
            ]);
        }
        $server_key = trim($general_settings->fcm_server_key);
        $title =  __('user_messages.91',[],$language);
        $title_code = 91;
        if ($ride_status == 1) {
//            $message = "Driver Accepted Ride.";
            $message = __('user_messages.16',[],$language);
            $message_code = 16;
        } elseif ($ride_status == 2) {
//            $message = "Driver Accepted Schedule Ride.";
            $message = __('user_messages.45',[],$language);
            $message_code = 45;
        } elseif ($ride_status == 3) {
//            $message = "Driver arrived at pickup location";
            $message = __('user_messages.46',[],$language);
            $message_code = 46;
        } elseif ($ride_status == 4) {
//          $message = trim(ucwords(strtolower($ride->cancel_by))) . " Cancelled Ride.";
            if (trim(ucwords(strtolower($ride->cancel_by))) == "Admin") {
                $message =  __('user_messages.113',[],$language);
                $message_code = 113;
                $title =  __('user_messages.65',[],$language);
                $title_code = 65;
            } else {
                $message =  __('user_messages.20',[],$language);
                $message_code = 20;
                $title =  __('user_messages.65',[],$language);
                $title_code = 65;
            }
        } elseif ($ride_status == 5) {
            if(in_array($ride->service_cat_id,$this->delivery_service_id_array) )
            {
//                $message = "Driver started delivery";
                $message =  __('user_messages.19',[],$language);

            }else{

                if($ride->is_way_point == 1 && $ride->way_point_status > 0) {
                    if($ride->way_point_status == 1){
                        $message =  __('user_messages.272',[],$language);
                        $message_code = 272;
                    }elseif($ride->way_point_status == 2){
                        $message =  __('user_messages.273',[],$language);
                        $message_code = 273;
                    }elseif($ride->way_point_status == 3){
                        $message =  __('user_messages.274',[],$language);
                        $message_code = 274;
                    }else{
//                        $message = "Driver started ride";
                        $message =  __('user_messages.19',[],$language);
                        $message_code = 19;
                    }
//                    $message = "Driver Reached at Stop ".$ride->way_point_status;
//                    \Log::info("way points status in");
//                    \Log::info($message);
                } else {
//                    $message = "Driver started ride";
                    $message =  __('user_messages.19',[],$language);
                    $message_code = 19;
                }
            }
//            $message = "Driver started delivery/ride.";

        } elseif ($ride_status == 6) {
//            $message = "Driver arrived at destination.";
            $message =  __('user_messages.48',[],$language);
            $message_code = 48;
        } elseif ($ride_status == 7) {
//            $message = "ride completed by admin!";
            $message =  __('user_messages.114',[],$language);
            $message_code = 114;
            $title_code = 150;
//            $title = "ride completed";
            $title =  __('user_messages.150',[],$language);

        } elseif ($ride_status == 9) {
            if(in_array($ride->service_cat_id,$this->delivery_service_id_array) )
            {
//                $message = "Driver completed delivery";
                $message =  __('user_messages.40',[],$language);
            }else{
                $message = __('user_messages.21',[],$language);
            }
            //$message = "Driver completed ride/delivery.";
            $message_code = 49;
        }
        //elseif ($ride_status == 10) {
        //    $message = "Your ride is failed.";
        //}
        else {
            return response()->json([
                'status' => 0,
//                'message' => "Something want wrong!",
                'message' =>  __('driver_messages.9'),
                'message_code' => 9
            ]);
        }
        //notification type 0= simple , 1= communication

        $extraNotificationData = [
            'title' => $title . "",
            'title_code' => $title_code . "",
            'sound' => "true",
            'notification_type' => "1",
            'order_id' => $ride_id . "",
            'order_status' => $ride_status . "",
            'message' => $message . "",
            'message_code' => $message_code . "",
            'service_category_id' => $ride->service_cat_id . "",
            'booking_type' => $ride->ride_type . ""
        ];

        $respone = $this->sendFlowNotification($device_token, $extraNotificationData, 0);
        return $respone;
    }

    public function userAssignDeliveryDriverNotification($delivery_id, $device_token, $delivery_status, $login_device, $language = Null)
    {
        $language = $language != Null ? $language : 'en';

        if ($delivery_id == Null || $device_token == Null || $delivery_status == Null) {
            return response()->json([
                'status' => 0,
//                'message' => "Something want wrong!",
                'message' => __('driver_messages.9'),
                'message_code' => 9,
            ]);
        }
        $general_settings = GeneralSettings::query()->select('fcm_server_key')->first();
        if ($general_settings == Null || $general_settings->fcm_server_key == Null) {
            return response()->json([
                'status' => 0,
//                'message' => "Something want wrong!",
                'message' => __('driver_messages.9'),
                'message_code' => 9,
            ]);
        }
        $delivery = UserProductBooking::query()->where('id', $delivery_id)->first();
        if ($delivery == Null) {
            return response()->json([
                'status' => 0,
//                'message' => "Something want wrong!",
                'message' => __('driver_messages.9'),
                'message_code' => 9,
            ]);
        }
        $server_key = trim($general_settings->fcm_server_key);
        if ($delivery_status == 1) {
//            $message = "Assign Delivery Driver";
            $message = __('user_messages.37',[],$language);
            $message_code = 37;
        } elseif ($delivery_status == 2) {
//            $message = "Assign Delivery Driver";
            $message = __('user_messages.37',[],$language);
            $message_code = 37;
        } else {
            return response()->json([
                'status' => 0,
//                'message' => "Something want wrong!",
                'message' => __('driver_messages.9'),
                'message_code' => 9,
            ]);
        }
        //notification type 0= simple , 1= communication
//        $title = "Assign Delivery Driver";
        $title = __('user_messages.50',[],$language);
        $extraNotificationData = [
            'title' => $title . "",
            'title_code' => "50",
            'sound' => "true",
            'notification_type' => "1",
            'order_id' => $delivery->id . "",
            'order_status' => $delivery->status . "" ,
            'message' => $message . "",
            'message_code' => $message_code . "",
            'service_category_id' => $delivery->service_cat_id . "",
            'booking_type' => "0"
        ];

        $response = $this->sendFlowNotification($device_token, $extraNotificationData, 0);
        return $response;
    }

    public function userStoreDeliveryNotification($delivery_id, $device_token, $delivery_status, $login_device, $language)
    {
        if ($delivery_id == Null || $device_token == Null || $delivery_status == Null) {
            return response()->json([
                'status' => 0,
                'message' => __('driver_messages.9'),
                'message_code' => 9,
            ]);
        }
        $general_settings = GeneralSettings::query()->select('fcm_server_key')->first();
        if ($general_settings == Null || $general_settings->fcm_server_key == Null) {
            return response()->json([
                'status' => 0,
                'message' => __('driver_messages.9'),
                'message_code' => 9,
            ]);
        }
        $delivery = UserProductBooking::query()->where('id', $delivery_id)->first();
        if ($delivery == Null) {
            return response()->json([
                'status' => 0,
                'message' => __('driver_messages.9'),
                'message_code' => 9,
            ]);
        }
        $server_key = trim($general_settings->fcm_server_key);

        $language = $language != Null ? $language : 'en';

//        $title = "Order Notification";
        $title = __('user_messages.91',[],$language);
        $title_code = 91;
        if ($delivery_status == 2) {
//            $message = "Order Accepted by Store";
            $message = __('user_messages.51',[],$language);
            $message_code = 51;
        } elseif ($delivery_status == 3) {
//            $message = "Order Rejected by " . trim(ucwords(strtolower($delivery->cancel_by)));
            $message = __('user_messages.52',[],$language);
            $message_code = 52;
            if (trim(ucwords(strtolower($delivery->cancel_by))) == "Admin") {
                $message = __('user_messages.250',[],$language);
                $message_code = 250;
            }
        } elseif ($delivery_status == 4) {
//            $message = "Order Cancelled by " . trim(ucwords(strtolower($delivery->cancel_by)));
            $message = __('user_messages.53',[],$language);
            $message_code = 53;

            $title = __('user_messages.42',[],$language);
            $title_code = 42;

            if (trim(ucwords(strtolower($delivery->cancel_by))) == "Admin") {
                $message = __('user_messages.111',[],$language);
                $message_code = 111;
            }
        } elseif ($delivery_status == 5) {
//            $message = "Order Start Processing";
            $message = __('user_messages.54',[],$language);
            $message_code = 54;
            /*if ($delivery->taken_away_type == 2) {
                $title = "Ready For Pickup";
                $title_code = 142;
                $message = "Your order is ready for Pickup";
                $message_code = 143;
            }*/
        } elseif ($delivery_status == 8) {
//            $message = "Order On the Way";
            $message = __('user_messages.55',[],$language);
            $message_code = 55;
            if ($delivery->taken_away_type == 2) {
//                $title = "Ready For Pickup";
                $title = __('user_messages.142',[],$language);
                $title_code = 142;

//                $message = "Your order is ready for Pickup";
                $message = __('user_messages.143',[],$language);
                $message_code = 143;
            }
        } elseif ($delivery_status == 9) {
//            $message = "Order Delivered";
            $message = __('user_messages.56',[],$language);
            $message_code = 56;
            if ($delivery->taken_away_type == 2) {
//                $message = "Order Picked up";
                $message = __('user_messages.144',[],$language);
                $message_code = 144;
            }
        } else {
            return response()->json([
                'status' => 0,
                'message' => __('driver_messages.9'),
                'message_code' => 9,
            ]);
        }

        //notification type 0= simple , 1= communication
        $extraNotificationData = [
            'title' => $title . "",
            'title_code' => $title_code . "",
            'sound' => "true",
            'notification_type' => "1",
            'order_id' => $delivery->id . "",
            'order_status' => $delivery->status . "",
            'message' => $message . "",
            'message_code' => $message_code . "",
            'service_category_id' => $delivery->service_cat_id . "",
            'booking_type' => "0"
        ];

        $response = $this->sendFlowNotification($device_token, $extraNotificationData, 0);
        return $response;
    }

    public function userProviderPackageNotification($order_id, $device_token, $delivery_status, $login_device,$language,$completed_by = 0)
    {
        if ($order_id == Null || $device_token == Null || $delivery_status == Null) {
            return response()->json([
                'status' => 0,
                'message' => __('driver_messages.9'),
                'message_code' => 9,
            ]);
        }
        $general_settings = GeneralSettings::query()->select('fcm_server_key')->first();
        if ($general_settings == Null || $general_settings->fcm_server_key == Null) {
            return response()->json([
                'status' => 0,
                'message' => __('driver_messages.9'),
                'message_code' => 9,
            ]);
        }
        $delivery = UserPackageBooking::query()->where('id', $order_id)->first();
        if ($delivery == Null) {
            return response()->json([
                'status' => 0,
                'message' => __('driver_messages.9'),
                'message_code' => 9,
            ]);
        }
        $server_key = trim($general_settings->fcm_server_key);

        $language = $language != Null ? $language : 'en';

//        $title = "Order Notification";
        $title = __('user_messages.91',[],$language);
        $title_code = 91;

        if ($delivery_status == 2) {
//            $message = "Service Accepted by Provider";
            $message = __('user_messages.95',[],$language);
            $message_code = 95;
        } elseif ($delivery_status == 3) {
//            $message = "Schedule Service Accepted by Provider";
            $message = __('user_messages.96',[],$language);
            $message_code = 96;
        } elseif ($delivery_status == 4) {
//            $message = "Service Rejected by Provider";
            $message = __('user_messages.97',[],$language);
            $message_code = 97;
        } elseif ($delivery_status == 5) {
//            $message = "Service Cancelled by " . trim(ucwords(strtolower($delivery->cancel_by)));
            $message = __('user_messages.98',[],$language);
            $message_code = 98;

            $title = __('user_messages.42',[],$language);
            $title_code = 42;

            if (trim(ucwords(strtolower($delivery->cancel_by))) == "Admin") {
                $message = __('user_messages.111',[],$language);
                $message_code = 111;

                $title = __('user_messages.42',[],$language);
                $title_code = 42;
            }

        } elseif ($delivery_status == 6) {
//            $message = "Provider On the Way";
            $message = __('user_messages.99',[],$language);
            $message_code = 99;
        } elseif ($delivery_status == 7) {
//            $message = "Provider Arrived at Destination";
            $message = __('user_messages.100',[],$language);
            $message_code = 100;
        } elseif ($delivery_status == 8) {
//            $message = "Provider Start Processing";
            $message = __('user_messages.101',[],$language);
            $message_code = 101;
        } elseif ($delivery_status == 9 && $completed_by == 0) {
//            $message = "Your Order Completed";
            $message = __('user_messages.102',[],$language);
            $message_code = 102;
        } elseif ($delivery_status == 9 && $completed_by == 1){
            $message = __('user_messages.112',[],$language);
            $message_code = 112;
        } else {
            return response()->json([
                'status' => 0,
                'message' => __('driver_messages.9'),
                'message_code' => 9,
            ]);
        }

        //notification type 0= simple , 1= communication
        $extraNotificationData = [
            'title' => $title . "",
            'title_code' => $title_code . "",
            'sound' => "true",
            'notification_type' => "1",
            'order_id' => $delivery->id . "",
            'order_status' => $delivery->status . "",
            'message' => $message . "",
            'message_code' => $message_code . "",
            'service_category_id' => $delivery->service_cat_id . "",
            'booking_type' => $delivery->order_type . ""
        ];
        $response = $this->sendFlowNotification($device_token, $extraNotificationData, 0);
        return $response;
    }

    public function providerOrderPackageNotification($order_id, $device_token, $order_status, $login_device,$language,$completed_by = 0)
    {
        if ($order_id == Null || $device_token == Null || $order_status == Null) {
            return response()->json([
                'status' => 0,
                'message' => __('provider_messages.9'),
                'message_code' => 9,
            ]);
        }
        $general_settings = GeneralSettings::query()->select('fcm_server_key')->first();
        if ($general_settings == Null || $general_settings->fcm_server_key == Null) {
            return response()->json([
                'status' => 0,
                'message' => __('provider_messages.9'),
                'message_code' => 9,
            ]);
        }

        $server_key = trim($general_settings->fcm_server_key);
        $language = $language != Null ? $language : 'en';
        //$title = "Order Notification";
        $title = __('provider_messages.91',[],$language);
        $title_code = 91;
        if ($order_status == 6) {
            //$message = "User On the Way";
            $message = __('provider_messages.311',[],$language);
            $message_code = 264;
        }
        elseif ($order_status == 9 && $completed_by == 0) {
            //$message = "Order completed by admin!";
            $message = __('provider_messages.112',[],$language);
            $message_code = 112;
        }
        elseif ($order_status == 9 && $completed_by == 1) {
            //$message = "Order completed by admin!";
            $message = __('provider_messages.329',[],$language);
            $message_code = 329;
        }
        elseif ($order_status == 12) {
            //$message = "User Arrived at Destination";
            $message = __('provider_messages.312',[],$language);
            $message_code = 265;
        }
        else {
            return response()->json([
                'status' => 0,
                'message' => __('provider_messages.9'),
                'message_code' => 9,
            ]);
        }
        //notification type 0= simple , 1= communication
        $extraNotificationData = [
            'title' => $title . "",
            'title_code' => $title_code . "",
            'sound' => "true",
            'notification_type' => "1",
            'order_id' => $order_id . "",
            'order_status' => $order_status . "" ,
            'message' => $message . "",
            'body' => $message . "",
            'message_code' => $message_code . "",
            "click_action" => "FLUTTER_NOTIFICATION_CLICK"
        ];

        $response = $this->sendFlowNotification($device_token, $extraNotificationData, 0);
        return $response;
    }

    //on demand notification
    public function providerOrderRequestNotification($order_id, $order_status, $device_token, $provider_login_device,$language)
    {
        if ($order_id == Null || $device_token == Null || $provider_login_device == Null) {
            return response()->json([
                'status' => 0,
                'message' => __('user_messages.9'),
                'message_code' => 9,
            ]);
        }

        $general_settings = GeneralSettings::query()->select('fcm_server_key')->first();
        if ($general_settings == Null || $general_settings->fcm_server_key == Null) {
            return response()->json([
                'status' => 0,
                'message' => __('user_messages.9'),
                'message_code' => 9,
            ]);
        }
        $server_key = trim($general_settings->fcm_server_key);
        Log::info("server_key: ".$server_key);

        $language = $language != Null ? $language : 'en';

        //To driver Assign
//        $title = "New Request";
        $title = __('provider_messages.41',[],$language);

        $extraNotificationData = [
            'title' => $title . "",
            'title_code' => "41",
            'sound' => "true",
            'notification_type' => "1",
            'order_id' => $order_id . "",
            'order_status' => $order_status . "",
            'message' => __('provider_messages.90',[],$language),
            'message_code' => "90"
        ];

        $response =  $this->sendFlowNotification($device_token, $extraNotificationData, 0);
        return $response;
    }

    public function providerOrderCancelRequestNotification($order_id, $order_status, $device_token, $provider_login_device,$language)
    {
        if ($order_id == Null || $device_token == Null || $provider_login_device == Null) {
            return response()->json([
                'status' => 0,
                'message' => __('provider_messages.9'),
                'message_code' => 9,
            ]);
        }
        $order_details = UserPackageBooking::query()->where('id', $order_id)->first();
        if ($order_details == Null) {
            return response()->json([
                'status' => 0,
                'message' => __('provider_messages.9'),
                'message_code' => 9,
            ]);
        }
        $general_settings = GeneralSettings::query()->select('fcm_server_key')->first();
        if ($general_settings == Null || $general_settings->fcm_server_key == Null) {
            return response()->json([
                'status' => 0,
                'message' => __('provider_messages.9'),
                'message_code' => 9,
            ]);
        }
        $server_key = trim($general_settings->fcm_server_key);

        $language = $language != Null ? $language : 'en';

        // To driver Assign

//        $title = "Cancel Order";
        $title = __('provider_messages.42', [], $language);
        $title_code = 42;

//        $message = "Order Cancel By " . trim(ucwords(strtolower($order_details->cancel_by)));

        if (trim(ucwords(strtolower($order_details->cancel_by))) == "Admin") {
            $message =  __('provider_messages.111', [], $language);
            $message_code = 111;
        } else {
            $message =  __('provider_messages.36', [], $language);
            $message_code = 36;
        }

        $extraNotificationData = [
            'title' => $title. "",
            'title_code' => $title_code. "",
            'sound' => "true",
            'notification_type' => "2",
            'order_id' => $order_id. "",
            'order_status' => $order_status. "",
            'message' => $message. "",
            'message_code' => $message_code. ""
        ];

        $response = $this->sendFlowNotification($device_token, $extraNotificationData, 0);
        return $response;
    }

    //driver manual assign delivery notification
    public function driverManualAssignDeliveryRequestNotification($request_id, $device_token, $login_device, $language, $is_reassign = 0)
    {
        $store_order = UserProductBooking::query()->where('id', $request_id)->first();
        if ($store_order == Null) {
            return response()->json([
                'status' => 0,
                'message' => __('driver_messages.9'),
                "message_code" => 9,
            ]);
        }
        $general_settings = GeneralSettings::query()->select('fcm_server_key')->first();
        if ($general_settings == Null || $general_settings->fcm_server_key == Null) {
            return response()->json([
                'status' => 0,
                'message' => __('driver_messages.9'),
                "message_code" => 9,
            ]);
        }

        $server_key = trim($general_settings->fcm_server_key);
        $language = $language != Null ? $language : 'en';

//        $title = "New Order Request";
        $title = __('driver_messages.41', [],$language);
        $title_code = 41;

//        $message = "Assign driver by store";
        $message = __('driver_messages.106', [],$language);
        $message_code = 106;

        $fcmNotification = [];
        $notification_type = 4;

        if ($is_reassign > 0){
//            $title = "Order Re-assigned by admin";
            $title = __('driver_messages.286', [],$language);
            $title_code = 286;
//            $message = "Your ride was assign to another driver";
            $message = __('driver_messages.270', [],$language);
            $message_code = 270;
            $notification_type = 5;
        }

        $extraNotificationData = [
            'title' => $title . "",
            'title_code' => $title_code . "",
            'sound' => "true",
            'notification_type' => $notification_type . "",
            'message' => $message . "",
            'message_code' => $message_code . "",
            'service_category_id' => $store_order->service_cat_id . "",
            'order_id' => $store_order->id . "",
        ];

        $response = $this->sendFlowNotification($device_token, $extraNotificationData, 0);
        return $response;
    }

    public function driverManualAssignTransportRequestNotification($request_id, $device_token, $login_device, $language, $is_reassign = 0)
    {
        $ride_details = TransportRideBook::query()->where('id', $request_id)->first();
        if ($ride_details == Null) {
            return response()->json([
                'status' => 0,
                'message' => __('driver_messages.9'),
                "message_code" => 9,
            ]);
        }
        $general_settings = GeneralSettings::query()->select('fcm_server_key')->first();
        if ($general_settings == Null || $general_settings->fcm_server_key == Null) {
            return response()->json([
                'status' => 0,
                'message' => __('driver_messages.9'),
                "message_code" => 9,
            ]);
        }

        $server_key = trim($general_settings->fcm_server_key);
        $language = $language != Null ? $language : '';

//        $title = "New Ride Request";
        $title = __('driver_messages.22', [],$language);
        $title_code = 22;

//        $message = "Assign driver by Admin";
        $message = __('driver_messages.107', [],$language);
        $message_code = 107;

        $fcmNotification = [];
        $notification_type = 4;

        if ($is_reassign > 0) {
//            $title = "Ride Re-assigned by admin";
            $title = __('driver_messages.286', [],$language);
            $title_code = 286;

//            $message = "Your ride was assign to another driver";
            $message = __('driver_messages.270', [],$language);
            $message_code = 270;
            $notification_type = 5;
        }

        $extraNotificationData = [
            'title' => $title . "",
            'title_code' => $title_code . "",
            'sound' => "true",
            'notification_type' => $notification_type . "",
            'message' => $message . "",
            'message_code' => $message_code . "",
            'service_category_id' => $ride_details->service_cat_id . "",
            'order_id' => $ride_details->id . "",
        ];

        $response = $this->sendFlowNotification($device_token, $extraNotificationData, 0);
        return $response;
    }

    //user rental notification
    public function userTransportRentalNotification($ride_id)
    {
        if ($ride_id == Null) {
            return response()->json([
                'status' => 0,
                'message' => __('driver_messages.9'),
                'message_code' => 9
            ]);
        }
        $general_settings = GeneralSettings::query()->select('fcm_server_key')->first();
        if ($general_settings == Null || $general_settings->fcm_server_key == Null) {
            return response()->json([
                'status' => 0,
                'message' => __('driver_messages.9'),
                'message_code' => 9
            ]);
        }
        $ride = TransportRentalRideBooking::query()->where('id', $ride_id)->first();
        if ($ride == Null) {
            return response()->json([
                'status' => 0,
                'message' => __('driver_messages.9'),
                'message_code' => 9
            ]);
        }
        $user_details = User::query()->where('id', $ride->user_id)->whereNull('users.deleted_at')->first();
        if ($user_details == Null) {
            return response()->json([
                'status' => 0,
                'message' => __('driver_messages.9'),
                'message_code' => 9
            ]);
        }
        $language = $user_details->language != Null ? $user_details->language : 'en';

//        $title = "Ride Notification";
        $title = __('user_messages.91',[],$language);
        $title_code = 91;

        $server_key = trim($general_settings->fcm_server_key);
        if ($ride->status == 1) {

//            $message = "Driver Accepted Ride.";
            $message = __('user_messages.16',[],$language);
            $message_code = 16;

        } elseif ($ride->status == 2) {
//            $message = trim(ucwords(strtolower($ride->cancel_by))) . " Cancelled Ride.";
            if (trim(ucwords(strtolower($ride->cancel_by))) == "Admin") {
                $message =  __('user_messages.113',[],$language);
                $message_code = 113;

                $title =  __('user_messages.65',[],$language);
                $title_code = 65;
            } else {
                $message =  __('user_messages.20',[],$language);
                $message_code = 20;

                $title =  __('user_messages.65',[],$language);
                $title_code = 65;
            }
        } elseif ($ride->status == 3) {
//            $message = "Vehicle Allocated to User.";
            $message =  __('user_messages.137',[],$language);
            $message_code = 137;
        } elseif ($ride->status == 6) {
//            $message = "Vehicle Allocated to Driver.";
            $message =  __('user_messages.138',[],$language);
            $message_code = 138;
        } elseif ($ride->status == 9) {
//            $message = "completed the ride.";
            $message =  __('user_messages.141',[],$language);
            $message_code = 141;
        }
        //elseif ($ride_status == 10) {
        //    $message = "Your ride is failed.";
        //}
        else {
            return response()->json([
                'status' => 0,
                'message' => __('driver_messages.9'),
                'message_code' => 9
            ]);
        }
        //notification type 0= simple , 1= communication
        $extraNotificationData = [
            'title' => $title . "",
            'title_code' => $title_code . "",
            'sound' => "true",
            'notification_type' => "1",
            'order_id' => $ride_id . "",
            'order_status' => $ride->status . "",
            'message' => $message . "",
            'message_code' => $message_code . "",
            'service_category_id' => $ride->service_cat_id . "",
            'booking_type' => "1"
        ];

        $response = $this->sendFlowNotification($user_details->device_token, $extraNotificationData, 0);
        return $response;
    }

    //driver rental notification
    public function driverTransportRentalNotification($ride_id)
    {
        if ($ride_id == Null) {
            return response()->json([
                'status' => 0,
                'message' => __('driver_messages.9'),
                'message_code' => 9
            ]);
        }
        $ride = TransportRentalRideBooking::query()->where('id', $ride_id)->first();
        if ($ride == Null) {
            return response()->json([
                'status' => 0,
                'message' => __('driver_messages.9'),
                'message_code' => 9
            ]);
        }
        $driver = TransportDriverDetails::query()->select('providers.device_token', 'providers.login_device', 'providers.language')
            ->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
            ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
            ->whereNull('providers.deleted_at')
            ->where('transport_driver_details.id', $ride->driver_id)
            ->first();

        $general_settings = GeneralSettings::query()->select('fcm_server_key')->first();
        if ($general_settings == Null || $general_settings->fcm_server_key == Null) {
            return response()->json([
                'status' => 0,
                'message' => __('driver_messages.9'),
                'message_code' => 9
            ]);
        }

        $language = $driver->language != Null ? $driver->language : 'en';

//        $title = "Rental Ride";
        $title = __('driver_messages.152', [],$language);

        if ($ride->status == 2) {
//            $message = trim(ucwords(strtolower($ride->cancel_by))) . " Cancelled Ride.";

            if (trim(ucwords(strtolower($ride->cancel_by))) == "admin") {
                $message = __('driver_messages.153', [],$language);
                $message_code = 153;

                $title = __('driver_messages.152', [],$language);
                $title_code = 152;
            } else {
                $message = __('driver_messages.153', [],$language);
                $message_code = 153;

                $title = __('driver_messages.152', [],$language);
                $title_code = 152;
            }
        } elseif ($ride->status == 4) {
//            $message = "User Started Ride.";
            $message = __('driver_messages.139', [],$language);
            $message_code = 139;

            $title = __('driver_messages.152', [],$language);
            $title_code = 152;
        } elseif ($ride->status == 5) {
//            $message = "User Arrived at Destination Location.";
            $message = __('driver_messages.140', [],$language);
            $message_code = 140;

            $title = __('driver_messages.152', [],$language);
            $title_code = 152;

        } elseif ($ride->status == 9) {
//            $message = "completed the rental service.";
            $message = __('driver_messages.141', [],$language);
            $message_code = 141;

            $title = __('driver_messages.152', [],$language);
            $title_code = 152;
        }
        //elseif ($ride_status == 10) {
        //    $message = "Your ride is failed.";
        //}
        else {
            return response()->json([
                'status' => 0,
                'message' => __('driver_messages.9'),
                'message_code' => 9
            ]);
        }

        $extraNotificationData = [
            'title' => $title. "",
            "title_code" => $title_code. "",
            'sound' => "true",
            'notification_type' => "3",
            'ride_id' => $ride_id. "",
            'message' => $message. "",
            'message_code' => $message_code. "",
            'service_category_id' => $ride->service_cat_id. "",
        ];

        $response = $this->sendFlowNotification($driver->device_token, $extraNotificationData, 0);
        return $response;
    }

    //shopper to store notification
    public function storeDeliveryShopperNotification($delivery_id, $device_token, $delivery_status, $login_device)
    {
        return "ss";
    }

    public function shopperCancelNotification($delivery_id, $token, $service_cat_id, $login_device)
    {
        return "";
    }

    public function shopperDeliveryRequestNotification($service_cat_id, $request_id)
    {
        return "ss";
    }

    //driver manual assign delivery notification
    public function shoperManualAssignDeliveryRequestNotification($request_id, $device_token, $login_device)
    {
        return "ss";
    }

    public function dateLangConvert($date,$lang="en"){
        if($lang == "en"){
            return $date;
        }else{
            $search = config('dateconstants.en');
            $replace = config('dateconstants.'.$lang);
            if($replace == Null){
                return $date;
            }
            $new_date = str_ireplace($search, $replace, $date);
            return $new_date;
        }
    }

    public function statusLangConvert($status,$lang = 'en'){
        if($lang == "en"){
            return $status;
        }else{
            $search = config('statusconstants.en');
            $replace = config('statusconstants.'.$lang);
            if($replace == Null){
                return $status;
            }
            $new_date = str_ireplace($search, $replace, $status);
            return $new_date;
        }
    }

    public function serviceLangConvert(){
        $service = [
            'Bike Riding' => [
                'en' => 'Bike Riding', 'ar' => 'ركوب الدراجة', 'de' => 'Radfahren', 'ko' => '자전거 타기', 'fr' => 'Faire du vélo',
                'ceb' => 'Pagsakay sa Bisikleta', 'es' => 'Montar bicicleta', 'fil' => 'Pagsakay sa Bike',
                'zh-rCN' => '骑自行车', 'zh-Hans' => '骑自行车', 'zh' => '騎自行車', 'zh-Hant' => '騎自行車', 'ja' => '自転車に乗る'
            ],
            'Courier Delivery' => [
                'en' => 'Courier Delivery', 'ar' => 'تسليم البريد السريع', 'de' => 'Kurierlieferung', 'ko' => '택배 배송', 'fr' => 'La livraison de courrier',
                'ceb' => 'Paghatud sa Courier', 'es' => 'Entrega por mensajería', 'fil' => 'Paghahatid ng Courier',
                'zh-rCN' => '快递运送', 'zh-Hans' => '快递运送', 'zh' => '快遞運送', 'zh-Hant' => '快遞運送', 'ja' => '宅配便'
            ],
            'Store Delivery' => [
                'en' => 'Store Delivery', 'ar' => 'تسليم المتجر', 'de' => 'Ladenlieferung', 'ko' => '매장 배송', 'fr' => 'Livraison en magasin',
                'ceb' => 'Paghatud sa Tindahan', 'es' => 'Entrega en tienda', 'fil' => 'Paghahatid sa Tindahan',
                'zh-rCN' => '店铺送货', 'zh-Hans' => '店铺送货', 'zh' => '店鋪送貨', 'zh-Hant' => '店鋪送貨', 'ja' => '店舗配送'
            ],
            'Taxi Riding' => [
                'en' => 'Taxi Riding', 'ar' => 'ركوب سيارات الأجرة', 'de' => 'Taxifahren', 'ko' => '택시 타기', 'fr' => 'Promenade en taxi',
                'ceb' => 'Pagsakay sa Taxi', 'es' => 'Paseo en taxi', 'fil' => 'Pagsakay sa Taxi',
                'zh-rCN' => '乘坐出租车', 'zh-Hans' => '乘坐出租车', 'zh' => '乘坐出租車', 'zh-Hant' => '乘坐出租車', 'ja' => 'タクシー乗り'
            ],
            'Ride Service' => [
                'en' => 'Ride Service', 'ar' =>'خدمة الركوب', 'de' => 'Fahrservice', 'ko' => '라이드 서비스', 'fr' => 'Service de conduite',
                'ceb' => 'Pagsakay sa Serbisyo', 'es' => 'Servicio de viaje', 'fil' => 'Pagsakay sa Serbisyo',
                'zh-rCN' => '乘车服务', 'zh-Hans' => '乘车服务', 'zh' => '乘車服務', 'zh-Hant' => '乘車服務', 'ja' => 'ライドサービス'
            ],
            'Rental Service' => [
                'en' => 'Rental Service', 'ar' =>'خدمات التأجير', 'de' => 'Vermietung', 'ko' => '대여 서비스', 'fr' => 'Services de location',
                'ceb' => 'Mga Serbisyo sa Pag-abang', 'es' => 'Servicios de alquiler', 'fil' => 'Mga Serbisyo sa Pagrenta',
                'zh-rCN' => '租赁服务', 'zh-Hans' => '租赁服务', 'zh' => '租賃服務', 'zh-Hant' => '租賃服務', 'ja' => 'レンタルサービス'
            ],
        ];

        return $service;
    }

    public function userWalletTransferNotification($title,$message,$transfer_wallet_holder_device_token = "", $transfer_wallet_holder_login_device = "",$amount_to_default = 0){
        if ($transfer_wallet_holder_device_token == "" || $transfer_wallet_holder_login_device =="") {
            return response()->json([
                'status' => 0,
                'message' => __('user_messages.9'),
                'message_code' => 9,
            ]);
        }
        $general_settings = GeneralSettings::query()->select('fcm_server_key')->first();
        if ($general_settings == Null || $general_settings->fcm_server_key == Null) {
            return response()->json([
                'status' => 0,
                'message' => __('user_messages.9'),
                'message_code' => 9,
            ]);
        }

        $server_key = trim($general_settings->fcm_server_key);
        $device_token = $transfer_wallet_holder_device_token;


        $title_code = 262;
        $message_code = 263;

        $extraNotificationData = [
            'title' => $title . "",
            'title_code' => $title_code . "",
            'sound' => "true",
            'notification_type' => "6",
            'message' => $message . "",
            'message_code' => $message_code . "",
        ];

        $response = $this->sendFlowNotification($transfer_wallet_holder_device_token, $extraNotificationData, 0);
        return $response;
    }

    public function sendMail($subject="",$to_mail,$mail_type="",$data=[]){
        try {
            $general_setting = request()->get("general_settings");

            $template = EmailTemplates::query()->where('type',$mail_type)->where('status',1)->first();
            if ($general_setting != Null && $template != Null){
                if($general_setting->send_mail == 1){
                    $smtp_user_name = ($general_setting->smtp_user_name != Null)?$general_setting->smtp_user_name:"";
                    $smtp_password = ($general_setting->smtp_password != Null)?$general_setting->smtp_password:"";
                    $smtp_hostname = ($general_setting->smtp_hostname != Null)?$general_setting->smtp_hostname:"";
                    $smtp_port = ($general_setting->smtp_port != Null)?$general_setting->smtp_port:"";
                    $smtp_encryption = ($general_setting->smtp_encryption != Null)?$general_setting->smtp_encryption:"";
                    if($smtp_user_name !="" &&  $smtp_password != "" && $smtp_hostname !="" && $smtp_port !="" && $smtp_encryption != ""  ) {
                        //$site_logo = asset($this->getWebCompanyWiseImage("logo", $general_setting->website_logo));
                        //$mail_logo = asset($this->getWebCompanyWiseImage("email_template", "e-temp-email.png"));
                        //$fb_logo = asset($this->getWebCompanyWiseImage("email_template", "e-temp-facebook.png"));
                        //$twitter_logo = asset($this->getWebCompanyWiseImage("email_template", "e-temp-twitter.png"));
                        //$web_logo = asset($this->getWebCompanyWiseImage("email_template", "e-temp-world-wide-web.png"));

                        $site_logo = asset("/assets/images/email-temp-images/" . $general_setting->website_logo);
                        $mail_logo = asset("/assets/images/email-temp-images/e-temp-email.png");
                        $fb_logo = asset("/assets/images/email-temp-images/e-temp-facebook.png");
                        $twitter_logo = asset("/assets/images/email-temp-images/e-temp-twitter.png");
                        $web_logo = asset("/assets/images/email-temp-images/e-temp-world-wide-web.png");

                        $site_email = ($general_setting->email != "") ? $general_setting->email : "#";
                        $twitter_link = ($general_setting->twitter_link != "") ? $general_setting->twitter_link : "#";
                        $facebook_link = ($general_setting->facebook_link != "") ? $general_setting->facebook_link : "#";
                        $mail_site_name = ($general_setting->mail_site_name != "") ? $general_setting->mail_site_name : "";
                        $site_url = ($general_setting->site_url != "") ? $general_setting->site_url : request()->getHost();

                        $disp_data = $data;

                        $general_data = array(
                            "##logo##" => $site_logo ,
                            "##mail_site_name##" => $mail_site_name,
                            "##site_url##" => $site_url,
                            "##site_email##"  =>  $site_email,
                            "##twitter_link##" => $twitter_link,
                            "##facebook_link##" => $facebook_link,
                            "##mail_logo##" => $mail_logo,
                            "##fb_logo##" => $fb_logo,
                            "##twitter_logo##" => $twitter_logo,
                            "##web_logo##" => $web_logo,
                        );
                        $final_data = array_merge($disp_data,$general_data);
                        $template_content = str_replace(array_keys($final_data),$final_data,$template->content);

                        $email=$to_mail;

                        $data = [
                            "type" => 3,//store
                            "path" => "mail_template.temp",
                            "email" => $email,
                            "subject" => $subject,
                            "mail_site_name" => $mail_site_name,
                            "template_content" => $template_content,
                            "smtp_user_name" => $smtp_user_name,
                            "smtp_password" => $smtp_password,
                            "smtp_hostname" => $smtp_hostname,
                            "smtp_port" => $smtp_port,
                            "smtp_encryption" => $smtp_encryption,
                        ];
                        $emailJob = (new AutoMail($data))->delay(now()->addSeconds($this->email_time));
                        app('Illuminate\Contracts\Bus\Dispatcher')->dispatch($emailJob);
                    }
                    return "";
                }
                return "";
            }
            return "";
        }
        catch (\Exception $e){
            return $e->getMessage();
        }
    }

    public static function convertTimezone($date="",$from_timezone="",$to_timezone="",$is_date="y"){
        $default_server_timezone = "";
        $general_settings = request()->get("general_settings");
        if ($general_settings != Null) {
            if ($general_settings->default_server_timezone != "") {
                $default_server_timezone= $general_settings->default_server_timezone;
            }
        }
        if($from_timezone == ""){
            $from_timezone = $default_server_timezone;
        }
        if($to_timezone == ""){
            $to_timezone = $default_server_timezone;
        }

        if($from_timezone !="" && $to_timezone != "" && ($from_timezone != $to_timezone)){
            if($is_date == "n"){
                $format = "H:i:s";
            }elseif($is_date == "d"){
                $format = "Y-m-d h:i:s A";
            }else{
                $format = "Y-m-d H:i:s";
            }
            $date = new DateTime($date, new DateTimeZone($from_timezone));
            $date->format($format);
            $date->setTimezone(new DateTimeZone($to_timezone));
            $date = $date->format($format);
            return $date;
        }else{
            return $date;
        }
    }

    public function defaultProviderOpenCloseTime($default_start_time="",$default_end_time="",$default_provider_slot_time=""){
        $general_settings = request()->get("general_settings"); $get_time_periods = collect();
        if($default_start_time == "" || $default_end_time == "")
        {
            $default_start_time= ($general_settings->default_start_time != Null)?$general_settings->default_start_time:"00:00:00";
            $default_end_time= ($general_settings->default_end_time != Null)?$general_settings->default_end_time:"23:59:59";
            $default_provider_start_time= ($general_settings->provider_start_time != Null)?$general_settings->provider_start_time:"09:00:00";
            $default_provider_end_time= ($general_settings->provider_end_time != Null)?$general_settings->provider_end_time:"19:00:00";
        }else{
            $default_provider_start_time = $default_start_time;
            $default_provider_end_time = $default_end_time;
        }
        $default_provider_slot_time= ($general_settings->provider_slot_time != Null)?$general_settings->provider_slot_time:"60";
        $default_start_time = Carbon::parse($default_start_time)->format('H:i:s');
        $default_end_time = Carbon::parse($default_end_time)->format('H:i:s');

        $create_period = (new CarbonPeriod($default_start_time,"$default_provider_slot_time minutes",$default_end_time))->toArray();
        foreach ($create_period as $key => $periode_time){
            if (!isset($create_period[$key+1])) break;
            $get_time_periods->push([
                'start_time'=>$periode_time->format('H:i:s'),
                'end_time'=>$create_period[$key+1]->format('H:i:s'),
            ]);
        }
        if($default_end_time == '23:59:59'){
            $get_time_periods->push([
                'start_time'=>$get_time_periods->last()['end_time'],
                'end_time'=>'24:00:00'
            ]);
        }
        return $provider_default_time_Array = [
            'default_provider_start_time' => $default_provider_start_time,
            'default_provider_end_time' => $default_provider_end_time,
            'default_provider_slot' => $get_time_periods,
        ];

//        $start = new DateTime($default_provider_start_time);
//        $end = new DateTime($default_provider_end_time);
//        $startTime = $start->format('H:i:s');
//        $endTime = $end->format('H:i:s');
//        $i=0;
//        $time = [];
//        while(strtotime($startTime) <= strtotime($endTime))
//        {
//            $start = $startTime;
//            $end = date('H:i:s',strtotime('+'.$default_provider_slot_time.' minutes',strtotime($startTime)));
//            $startTime = date('H:i:s',strtotime('+'.$default_provider_slot_time.' minutes',strtotime($startTime)));
//            $i++;
//            if($i == 25){
//                break;
//            }
//            if(strtotime($startTime) <= strtotime($endTime))
//            {
//                $time[$i] = array(
//                    'start_time'=>$start,
//                    'end_time'=>$end,
//                );
//            }
//            if($end == '00:00:00'){
//                break;
//            }
//        }
//
//        $provider_default_time_Array = array(
//            'default_provider_start_time'=>$default_provider_start_time,
//            'default_provider_end_time'=>$default_provider_end_time,
//            'default_provider_slot'=>$time,
//        );
//        return $provider_default_time_Array;
    }

    public function userProviderReviewNotify($device_token,$service_cat_id,$language,$login_device,$provider_type,$order_id){

        if ($device_token == Null) {
            return response()->json([
                'status' => 0,
                'message' => __('provider_messages.9'),
                'message_code' => 9,
            ]);
        }
        $general_settings = GeneralSettings::query()->select('fcm_server_key')->first();
        if ($general_settings == Null || $general_settings->fcm_server_key == Null) {
            return response()->json([
                'status' => 0,
                'message' => __('provider_messages.9'),
                'message_code' => 9,
            ]);
        }
        $server_key = trim($general_settings->fcm_server_key);

        $language = $language != Null ? $language : 'en';

//        $title = "Order Notification";
        $title = __('provider_messages.91',[],$language);
        $title_code = 91;

        if ($provider_type == "user") {
            $message = __('provider_messages.336', [], $language);
        } else {
            $message = __('user_messages.336', [], $language);
        }
        $message_code = 336;

        //notification type 0= simple , 1= communication
        $extraNotificationData = [
            'title' => $title . "",
            'title_code' => $title_code . "",
            'sound' => "true",
            'notification_type' => "1",
            'order_id' => $order_id . "",
            'message' => $message . "",
            'message_code' => $message_code . "",
            'service_category_id' => $service_cat_id . ""
        ];

        $response = $this->sendFlowNotification($device_token, $extraNotificationData, 0);
        return $response;
    }

    public function userProviderPaymentNotification($device_token,$service_cat_id,$language,$login_device,$order_number,$order_status,$order_id)
    {
        if ($device_token == Null) {
            return response()->json([
                'status' => 0,
                'message' => __('provider_messages.9'),
                'message_code' => 9,
            ]);
        }
        $general_settings = GeneralSettings::query()->select('fcm_server_key')->first();
        if ($general_settings == Null || $general_settings->fcm_server_key == Null) {
            return response()->json([
                'status' => 0,
                'message' => __('provider_messages.9'),
                'message_code' => 9,
            ]);
        }
        $server_key = trim($general_settings->fcm_server_key);

        $language = $language != Null ? $language : 'en';

//        $title = "Order Notification";
        $title = __('provider_messages.91',[],$language);
        $title_code = 91;

        $message = __('provider_messages.335',[],$language);
        $message_code = 335;

        //notification type 0= simple , 1= communication
        $extraNotificationData = [
            'title' => $title. "",
            'title_code' => $title_code. "",
            'sound' => "true",
            'notification_type' => "1",
            'order_id' => $order_id. "",
            'order_status' => $order_status. "",
            'message' => $message. "",
            'message_code' => $message_code. "",
            'service_category_id' => $service_cat_id. ""
        ];

        $response = $this->sendFlowNotification($device_token, $extraNotificationData, 0);
        return $response;
    }

}

//    public function driverTransportIosRequestNotification($service_cat_id, $expire_date_time, $ride_request, $timeout, $radius,$gender,$driver_token = Null)
//    {
//        $driver_token = $this->getDriverForRide($service_cat_id, $expire_date_time, $ride_request, 2, $radius);
//        $get_provider_token_list = $driver_token;
//        $provider_token = array_column($driver_token, 'device_token');
//
//        /*$latlong = array_map('trim', explode(",", $ride_request->pickup_latlong));
//        $lat = $latlong[0];
//        $long = $latlong[1];
//
//        $get_provider_token = [];
//        $get_provider_token_list = Provider::query()->select('providers.id', 'providers.device_token')
//            ->whereRaw(DB::raw("(6371 * acos( cos( radians(" . $lat . ") ) * cos( radians( current_lat ) )  * cos( radians( current_long ) - radians(" . $long . ") ) + sin( radians(" . $lat . ") ) * sin(radians( current_lat ) ) ) ) < " . $radius))
//            ->join('provider_services', 'provider_services.provider_id', '=', 'providers.id')
//            ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
//            ->join('transport_driver_details', 'transport_driver_details.provider_service_id', '=', 'provider_services.id')
//            ->where('service_category.id', $service_cat_id)
//            ->where('providers.status', 1)
//            ->where('providers.login_device', 2)
//            ->where('provider_services.status', 1)
//            ->where('provider_services.current_status', 1)
//            ->where('transport_driver_details.driver_service', 1)
//            ->where('transport_driver_details.vehicle_type_id', $ride_request->vehicle_type_id)
//            ->where('transport_driver_details.last_online_date_time', '>=', $expire_date_time)
////            ->where('transport_driver_details.handicap', '=', $ride_request->handicap)
////            ->where('transport_driver_details.child_seat', '=', $ride_request->child_seat)
//            ->whereNotNull('transport_driver_details.current_lat')
//            ->whereNotNull('transport_driver_details.current_long')
//            ->whereNotNull('providers.access_token')
//            ->whereNotNull('providers.device_token');
//
//        if ($ride_request->handicap != 0) {
//            $get_provider_token_list->where('transport_driver_details.handicap', '=', $ride_request->handicap);
//        }
//        if ($ride_request->child_seat != 0) {
//            $get_provider_token_list->where('transport_driver_details.child_seat', '=', $ride_request->child_seat);
//        }
//        if (isset($gender) && $gender != Null && $gender != 0) {
//            $get_provider_token_list->where('providers.gender', '=', $gender);
//        }
//        $get_provider_token_list = $get_provider_token_list->get();*/
//
//        $general_settings = GeneralSettings::query()->select('fcm_server_key')->first();
//        if ($general_settings == Null || $general_settings->fcm_server_key == Null) {
//            return response()->json([
//                'status' => 0,
////                'message' => "Something want wrong!",
//                'message' => __('user_messages.9'),
//                'message_code' => 9,
//            ]);
//        }
//
//        $pickup = array_map('trim', explode(",", $ride_request->pickup_latlong));
//        $destination = array_map('trim', explode(",", $ride_request->destination_latlong));
//        $way_points[] = [
//            "address" => $ride_request->pickup_address,
//            "address_lat" => trim($pickup[0]),
//            "address_long" => trim($pickup[1])
//        ];
//        if ($ride_request->is_way_point == 1) {
//            $ride_way_point = UserRideWayPoint::query()->where('ride_id', $ride_request->request_id)->first();
//            if ($ride_way_point != Null) {
//                if ($ride_way_point->way_point_1 != Null && $ride_way_point->lat_long_1 != Null) {
//                    $lat_long_1 = array_map('trim', explode(",", $ride_way_point->lat_long_1));
//                    $way_points[] = [
//                        "address" => $ride_way_point->way_point_1,
//                        "address_lat" => trim($lat_long_1[0]),
//                        "address_long" => trim($lat_long_1[1])
//                    ];
//                }
//                if ($ride_way_point->way_point_2 != Null && $ride_way_point->lat_long_2 != Null) {
//                    $lat_long_2 = explode(",", $ride_way_point->lat_long_2);
//                    $way_points[] = [
//                        "address" => $ride_way_point->way_point_1,
//                        "address_lat" => trim($lat_long_2[0]),
//                        "address_long" => trim($lat_long_2[1])
//                    ];
//                }
//                if ($ride_way_point->way_point_3 != Null && $ride_way_point->lat_long_3 != Null) {
//                    $lat_long_3 = explode(",", $ride_way_point->lat_long_3);
//                    $way_points[] = [
//                        "address" => $ride_way_point->way_point_3,
//                        "address_lat" => trim($lat_long_3[0]),
//                        "address_long" => trim($lat_long_3[1])
//                    ];
//                }
//
//            }
//        }
//        $way_points[] = [
//            "address" => $ride_request->destination_address,
//            "address_lat" => trim($destination[0]),
//            "address_long" => trim($destination[1])
//        ];
//
//        $server_key = trim($general_settings->fcm_server_key);
//
//        //To driver Assign
//        $title = $ride_request->service_cat_name;
//        $extraNotificationData = [
//            'request_id' => $ride_request->request_id . "",
//            'service_category_id' => $ride_request->service_cat_id . "",
//            'service_category_name' => $ride_request->service_cat_name . "",
//            'service_category_icon' => $ride_request->service_cat_icon != Null ? url('/assets/images/service-category/' . $ride_request->service_cat_icon) . '' : '',
//            "address_list" => json_encode($way_points),
//            'notification_type' => $ride_request->ride_type . "",
//            'is_way_points' => $ride_request->is_way_point . "",
//            'schedule_date_time' => $ride_request->pickup_datetime,
//            'schedule_display_date_time' => Date('h:i, d M Y', strtotime($ride_request->pickup_datetime)),
//            'payment_type' => $ride_request->payment_type . "",
//            'driver_algorithm' => $ride_request->driver_algorithm . "",
//            'request_timeout' => $timeout . ""
//        ];
//        if ($get_provider_token_list != Null) {
//            foreach ($get_provider_token_list as $key => $provider) {
//                //$driver_request_notify_check = DriverNewRequestNotification::query()->where('driver_id', $provider->id)->first();
//                //if ($driver_request_notify_check == Null) {
//                $driver_request_notify = new DriverNewRequestNotification();
//                $driver_request_notify->driver_id = $provider['id'];
//                $driver_request_notify->request_id = $ride_request->request_id;
//                $driver_request_notify->service_cat_id = $ride_request->service_cat_id;
//                $driver_request_notify->notification_data = json_encode($extraNotificationData);
//                $driver_request_notify->save();
//
//                $get_provider_token[] = [
//                    $provider['id'],
//                ];
//                //}
//            }
//        }
//
//        $response_array = $this->sendFlowNotification($provider_token,$extraNotificationData,1);
//        return $response_array;
//    }

//code for send flutter transport notification
//    public function driverTransportFlutterRequestNotification($service_cat_id, $expire_date_time, $ride_request, $timeout, $radius,$gender=null,$driver_token = Null)
//    {
//        $driver_token = $this->getDriverForRide($service_cat_id, $expire_date_time, $ride_request, 3, $radius,$gender);
//        $get_provider_token_list = $driver_token;
//        $provider_token = array_column($driver_token, 'device_token');
//
//        /*$latlong = array_map('trim', explode(",", $ride_request->pickup_latlong));
//        $lat = $latlong[0];
//        $long = $latlong[1];
//
//        $get_provider_token = [];
//        $get_provider_token_list = Provider::query()->select('providers.id', 'providers.device_token')
//            ->whereRaw(DB::raw("(6371 * acos( cos( radians(" . $lat . ") ) * cos( radians( current_lat ) )  * cos( radians( current_long ) - radians(" . $long . ") ) + sin( radians(" . $lat . ") ) * sin(radians( current_lat ) ) ) ) < " . $radius))
//            ->join('provider_services', 'provider_services.provider_id', '=', 'providers.id')
//            ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
//            ->join('transport_driver_details', 'transport_driver_details.provider_service_id', '=', 'provider_services.id')
//            ->where('service_category.id', $service_cat_id)
//            ->where('providers.status', 1)
//            ->where('providers.login_device', 1)
//            ->where('provider_services.status', 1)
//            ->where('provider_services.current_status', 1)
//            ->where('transport_driver_details.driver_service', 1)
//            ->where('transport_driver_details.vehicle_type_id', $ride_request->vehicle_type_id)
//            ->where('transport_driver_details.last_online_date_time', '>=', $expire_date_time)
//            ->whereNotNull('transport_driver_details.current_lat')
//            ->whereNotNull('transport_driver_details.current_long')
//            ->whereNotNull('providers.access_token')
//            ->whereNotNull('providers.device_token');
//        if ($ride_request->handicap != 0) {
//            $get_provider_token_list->where('transport_driver_details.handicap', '=', $ride_request->handicap);
//        }
//        if ($ride_request->child_seat != 0) {
//            $get_provider_token_list->where('transport_driver_details.child_seat', '=', $ride_request->child_seat);
//        }
//        if (isset($gender) && $gender != Null && $gender != 0) {
//        $get_provider_token_list->where('providers.gender', '=', $gender);
//        }
//        $get_provider_token_list = $get_provider_token_list->get();*/
//
//        $general_settings = GeneralSettings::query()->select('fcm_server_key')->first();
//        if ($general_settings == Null || $general_settings->fcm_server_key == Null) {
//            return response()->json([
//                'status' => 0,
////                'message' => "Something want wrong!",
//                'message' => __('user_messages.9'),
//                'message_code' => 9,
//            ]);
//        }
//
//        $pickup = array_map('trim', explode(",", $ride_request->pickup_latlong));
//        $destination = array_map('trim', explode(",", $ride_request->destination_latlong));
//        $way_points[] = [
//            "address" => $ride_request->pickup_address,
//            "address_lat" => trim($pickup[0]),
//            "address_long" => trim($pickup[1])
//        ];
//        if ($ride_request->is_way_point == 1) {
//            $ride_way_point = UserRideWayPoint::query()->where('ride_id', $ride_request->request_id)->first();
//            if ($ride_way_point != Null) {
//                if ($ride_way_point->way_point_1 != Null && $ride_way_point->lat_long_1 != Null) {
//                    $lat_long_1 = array_map('trim', explode(",", $ride_way_point->lat_long_1));
//                    $way_points[] = [
//                        "address" => $ride_way_point->way_point_1,
//                        "address_lat" => trim($lat_long_1[0]),
//                        "address_long" => trim($lat_long_1[1])
//                    ];
//                }
//                if ($ride_way_point->way_point_2 != Null && $ride_way_point->lat_long_2 != Null) {
//                    $lat_long_2 = explode(",", $ride_way_point->lat_long_2);
//                    $way_points[] = [
//                        "address" => $ride_way_point->way_point_1,
//                        "address_lat" => trim($lat_long_2[0]),
//                        "address_long" => trim($lat_long_2[1])
//                    ];
//                }
//                if ($ride_way_point->way_point_3 != Null && $ride_way_point->lat_long_3 != Null) {
//                    $lat_long_3 = explode(",", $ride_way_point->lat_long_3);
//                    $way_points[] = [
//                        "address" => $ride_way_point->way_point_3,
//                        "address_lat" => trim($lat_long_3[0]),
//                        "address_long" => trim($lat_long_3[1])
//                    ];
//                }
//
//            }
//        }
//        $way_points[] = [
//            "address" => $ride_request->destination_address,
//            "address_lat" => trim($destination[0]),
//            "address_long" => trim($destination[1])
//        ];
//
//        $server_key = trim($general_settings->fcm_server_key);
//
//        //To driver Assign
//        $title = $ride_request->service_cat_name;
//        $fcmUrl = 'https://fcm.googleapis.com/fcm/send';
//        $extraNotificationData = [
//            'title' =>"New Request",
//            'title_code' => 22,
//            'sound' => true,
//            'description' => "New Request",
//            'body' => "New Request",
//            'message_code' => 90,
//            'message' => "New Request",
//            'request_id' => $ride_request->request_id,
//            'service_category_id' => $ride_request->service_cat_id,
//            'service_category_name' => $ride_request->service_cat_name,
//            'service_category_icon' => $ride_request->service_cat_icon != Null ? url('/assets/images/service-category/' . $ride_request->service_cat_icon) : '',
//            "address_list" => $way_points,
//            'notification_type' => $ride_request->ride_type,
//            'is_way_points' => $ride_request->is_way_point,
//            'schedule_date_time' => $ride_request->pickup_datetime,
//            'schedule_display_date_time' => Date('h:i, d M Y', strtotime($ride_request->pickup_datetime)),
//            'payment_type' => $ride_request->payment_type,
//            'driver_algorithm' => $ride_request->driver_algorithm,
//            'request_timeout' => $timeout,
//            "click_action" => "FLUTTER_NOTIFICATION_CLICK"
//        ];
//        if ($get_provider_token_list != Null) {
//            foreach ($get_provider_token_list as $key => $provider) {
//                //$driver_request_notify_check = DriverNewRequestNotification::query()->where('driver_id', $provider->id)->first();
//                //if ($driver_request_notify_check == Null) {
//                $driver_request_notify = new DriverNewRequestNotification();
//                $driver_request_notify->driver_id = $provider['id'];
//                $driver_request_notify->request_id = $ride_request->request_id;
//                $driver_request_notify->service_cat_id = $ride_request->service_cat_id;
//                $driver_request_notify->notification_data = json_encode($extraNotificationData);
//                $driver_request_notify->save();
//
//                $get_provider_token[] = [
//                    $provider['id'],
//                ];
//                //}
//            }
//        }
//
//        if (is_array($provider_token)) {
//            $fcmNotification = [
//                'registration_ids' => $provider_token, //multiple token array
//                'data' => $extraNotificationData,
//                'notification' => $extraNotificationData,
//                'priority' => 'high',
//                ];
//        } else {
//            $fcmNotification = [
//                'to' => $provider_token,
//                'data' => $extraNotificationData,
//                'notification' => $extraNotificationData,
//                'priority' => 'high',
//            ];
//        }
//        $headers = [
//            'Authorization: key=' . $server_key,
//            'Content-Type: application/json'
//        ];
//        $ch = curl_init();
//        curl_setopt($ch, CURLOPT_URL, $fcmUrl);
//        curl_setopt($ch, CURLOPT_POST, true);
//        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmNotification));
//        $result = curl_exec($ch);
//        curl_close($ch);
//        return $result;
//    }

//    public function driverTransportRentalAndroidRequestNotification($service_cat_id, $expire_date_time, $ride_request, $timeout, $radius,$driver_token = Null)
//    {
//        $driver_token = $this->getDriverForRide($service_cat_id,$expire_date_time,$ride_request,1,$radius);
//        $provider_token = array_column($driver_token,'device_token');
//
//        /* $latlong = array_map('trim', explode(",", $ride_request->pickup_latlong));
//         $lat = $latlong[0];
//         $long = $latlong[1];
//         $get_provider_token = Provider::query()->select('providers.id', 'providers.device_token')
//             ->whereRaw(DB::raw("(6371 * acos( cos( radians(" . $lat . ") ) * cos( radians( current_lat ) )  * cos( radians( current_long ) - radians(" . $long . ") ) + sin( radians(" . $lat . ") ) * sin(radians( current_lat ) ) ) ) < " . $radius))
//             ->join('provider_services', 'provider_services.provider_id', '=', 'providers.id')
//             ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
//             ->join('transport_driver_details', 'transport_driver_details.provider_service_id', '=', 'provider_services.id')
//             ->where('service_category.id', $service_cat_id)
//             ->where('providers.status', 1)
//             ->where('providers.login_device', 1)
//             ->where('provider_services.status', 1)
//             ->where('provider_services.current_status', 1)
//             ->where('transport_driver_details.driver_service', 1)
//             ->where('transport_driver_details.rental_service', 1)
//             ->where('transport_driver_details.vehicle_type_id', $ride_request->vehicle_type_id)
//             ->where('transport_driver_details.last_online_date_time', '>=', $expire_date_time)
//             ->whereNotNull('transport_driver_details.current_lat')
//             ->whereNotNull('transport_driver_details.current_long')
//             ->whereNotNull('providers.access_token')
//             ->whereNotNull('providers.device_token');
//         $provider_token = $get_provider_token->get()->pluck('device_token')->toArray();*/
//
//        $general_settings = GeneralSettings::query()->select('fcm_server_key')->first();
//        if ($general_settings == Null || $general_settings->fcm_server_key == Null) {
//            return response()->json([
//                'status' => 0,
//                'message' => "Something want wrong!",
//            ]);
//        }
//
//        $pickup = array_map('trim', explode(",", $ride_request->pickup_latlong));
//        $destination = array_map('trim', explode(",", $ride_request->destination_latlong));
//        $way_points[] = [
//            "address" => $ride_request->pickup_address,
//            "address_lat" => trim($pickup[0]),
//            "address_long" => trim($pickup[1])
//        ];
//        $way_points[] = [
//            "address" => $ride_request->destination_address,
//            "address_lat" => trim($destination[0]),
//            "address_long" => trim($destination[1])
//        ];
//
//        $server_key = trim($general_settings->fcm_server_key);
//
//        //To driver Assign
//        $title = $ride_request->service_cat_name;
//        $fcmUrl = 'https://fcm.googleapis.com/fcm/send';
//        $extraNotificationData = [
//            'title' => $title,
//            'title_code' => 22,
//            'sound' => true,
//            'description' => "New Request",
//            'message_code' => 90,
//            'message' => "New Request",
//            'request_id' => $ride_request->request_id,
//            'service_category_id' => $ride_request->service_cat_id,
//            'service_category_name' => $ride_request->service_cat_name,
//            'service_category_icon' => $ride_request->service_cat_icon != Null ? url('/assets/images/service-category/' . $ride_request->service_cat_icon) : '',
//            "address_list" => $way_points,
//            'notification_type' => 1,
//            'is_way_points' => 0,
//            'schedule_date_time' => $ride_request->pickup_datetime,
//            'pickup_datetime' => $ride_request->pickup_datetime,
//            'drop_datetime' => $ride_request->drop_datetime,
//            'schedule_display_date_time' => Date('h:i, d M Y', strtotime($ride_request->pickup_datetime)),
//            'payment_type' => $ride_request->payment_type,
//            'request_timeout' => $timeout
//        ];
//        if (is_array($provider_token)) {
//            $fcmNotification = [
//                'registration_ids' => $provider_token, //multiple token array
//                'data' => $extraNotificationData
//            ];
//        } else {
//            $fcmNotification = [
//                'to' => $provider_token,
//                'data' => $extraNotificationData
//            ];
//        }
//        $headers = [
//            'Authorization: key=' . $server_key,
//            'Content-Type: application/json'
//        ];
//        $ch = curl_init();
//        curl_setopt($ch, CURLOPT_URL, $fcmUrl);
//        curl_setopt($ch, CURLOPT_POST, true);
//        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmNotification));
//        $result = curl_exec($ch);
//        curl_close($ch);
//
//        return $result;
//    }

//    public function driverTransportRentalIosRequestNotification($service_cat_id, $expire_date_time, $ride_request, $timeout, $radius,$driver_token = Null)
//    {
//        $driver_token = $this->getDriverForRide($service_cat_id,$expire_date_time,$ride_request,2,$radius);
//        $provider_token = array_column($driver_token,'device_token');
//
//        /*$latlong = array_map('trim', explode(",", $ride_request->pickup_latlong));
//        $lat = $latlong[0];
//        $long = $latlong[1];
//        $get_provider_token = Provider::query()->select('providers.id', 'providers.device_token')
//            ->whereRaw(DB::raw("(6371 * acos( cos( radians(" . $lat . ") ) * cos( radians( current_lat ) )  * cos( radians( current_long ) - radians(" . $long . ") ) + sin( radians(" . $lat . ") ) * sin(radians( current_lat ) ) ) ) < " . $radius))
//            ->join('provider_services', 'provider_services.provider_id', '=', 'providers.id')
//            ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
//            ->join('transport_driver_details', 'transport_driver_details.provider_service_id', '=', 'provider_services.id')
//            ->where('service_category.id', $service_cat_id)
//            ->where('providers.status', 1)
//            ->where('providers.login_device', 2)
//            ->where('provider_services.status', 1)
//            ->where('provider_services.current_status', 1)
//            ->where('transport_driver_details.driver_service', 1)
//            ->where('transport_driver_details.rental_service', 1)
//            ->where('transport_driver_details.vehicle_type_id', $ride_request->vehicle_type_id)
//            ->where('transport_driver_details.last_online_date_time', '>=', $expire_date_time)
//            ->whereNotNull('transport_driver_details.current_lat')
//            ->whereNotNull('transport_driver_details.current_long')
//            ->whereNotNull('providers.access_token')
//            ->whereNotNull('providers.device_token');
//        $provider_token = $get_provider_token->get()->pluck('device_token')->toArray();*/
//
//        $general_settings = GeneralSettings::query()->select('fcm_server_key')->first();
//        if ($general_settings == Null || $general_settings->fcm_server_key == Null) {
//            return response()->json([
//                'status' => 0,
//                'message' => "Something want wrong!",
//            ]);
//        }
//
//        $pickup = array_map('trim', explode(",", $ride_request->pickup_latlong));
//        $destination = array_map('trim', explode(",", $ride_request->destination_latlong));
//        $way_points[] = [
//            "address" => $ride_request->pickup_address,
//            "address_lat" => trim($pickup[0]),
//            "address_long" => trim($pickup[1])
//        ];
//        $way_points[] = [
//            "address" => $ride_request->destination_address,
//            "address_lat" => trim($destination[0]),
//            "address_long" => trim($destination[1])
//        ];
//
//        $server_key = trim($general_settings->fcm_server_key);
//
//        //To driver Assign
//        $title = $ride_request->service_cat_name;
//        $fcmUrl = 'https://fcm.googleapis.com/fcm/send';
//        $extraNotificationData = [
//            'request_id' => $ride_request->request_id,
//            'service_category_id' => $ride_request->service_cat_id,
//            'service_category_name' => $ride_request->service_cat_name,
//            'service_category_icon' => $ride_request->service_cat_icon != Null ? url('/assets/images/service-category/' . $ride_request->service_cat_icon) : '',
//            "address_list" => $way_points,
//            'notification_type' => 1,
//            'is_way_points' => 0,
//            'schedule_date_time' => $ride_request->pickup_datetime,
//            'pickup_datetime' => $ride_request->pickup_datetime,
//            'drop_datetime' => $ride_request->drop_datetime,
//            'schedule_display_date_time' => Date('h:i, d M Y', strtotime($ride_request->pickup_datetime)),
//            'payment_type' => $ride_request->payment_type,
//            'request_timeout' => $timeout
//        ];
//        $notification = [
//            'title' => $title,
//            'text' => "New Request",
//            'sound' => 'default',
//            'badge' => '1',
//            'addData' => $extraNotificationData,
//            "title_loc_key" => 22,
//            "body_loc_key" => 90,
//        ];
//        if (is_array($provider_token)) {
//            $arrayToSend = array(
//                'registration_ids' => $provider_token,
//                'notification' => $notification,
//                'priority' => 'high',
//                "content_available" => true
//            );
//        } else {
//            $arrayToSend = array(
//                'to' => $provider_token,
//                'notification' => $notification,
//                'priority' => 'high',
//                "content_available" => true,
//            );
//        }
//        $headers = [
//            'Authorization: key=' . $server_key,
//            'Content-Type: application/json'
//        ];
//        $ch = curl_init();
//        curl_setopt($ch, CURLOPT_URL, $fcmUrl);
//        curl_setopt($ch, CURLOPT_POST, true);
//        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($arrayToSend));
//        $result = curl_exec($ch);
//        curl_close($ch);
//
//        return $result;
//    }

//courier ride android request
//    public function driverTransportCourierAndroidRequestNotification($service_cat_id, $expire_date_time, $ride_request, $timeout, $radius, $fcm_server_key,$driver_token = Null)
//    {
//        $driver_token = $this->getDriverForRide($service_cat_id, $expire_date_time, $ride_request, 1, $radius);
//        $get_provider_token_list = $driver_token;
//        $provider_token = array_column($driver_token,'device_token');
//
//        /*$latlong = array_map('trim', explode(",", $ride_request->pickup_latlong));
//        $lat = $latlong[0];
//        $long = $latlong[1];
//        $get_provider_token = [];
//        if ($ride_request->vehicle_type_id == $this->courier_scooter) {
//            $vehicle_type_list = TransportVehicleType::where('service_cat_id', $this->bike_ride)->get()->pluck('id')->toArray();
//            $get_provider_token_list = Provider::select('providers.id', 'providers.device_token')
//                ->whereRaw(DB::raw("(6371 * acos( cos( radians(" . $lat . ") ) * cos( radians( current_lat ) )  * cos( radians( current_long ) - radians(" . $long . ") ) + sin( radians(" . $lat . ") ) * sin(radians( current_lat ) ) ) ) < " . $radius))
//                ->join('provider_services', 'provider_services.provider_id', '=', 'providers.id')
//                ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
//                ->join('transport_driver_details', 'transport_driver_details.provider_service_id', '=', 'provider_services.id')
//                ->where('service_category.id', $this->bike_ride)
//                ->where('providers.status', 1)
//                ->where('providers.login_device', 1)
//                ->where('provider_services.status', 1)
//                ->where('provider_services.current_status', 1)
//                ->where('transport_driver_details.courier_boy', 1)
//                ->whereIN('transport_driver_details.vehicle_type_id', $vehicle_type_list)
//                ->where('transport_driver_details.last_online_date_time', '>=', $expire_date_time)
//                ->whereNotNull('transport_driver_details.current_lat')
//                ->whereNotNull('transport_driver_details.current_long')
//                ->whereNotNull('providers.access_token')
//                ->whereNotNull('providers.device_token')
//                ->get();
//        } elseif ($ride_request->vehicle_type_id == 14){
//            $vehicle_type_list = TransportVehicleType::whereIn('service_cat_id', [$this->taxi_ride])->get()->pluck('id')->toArray();
//            $get_provider_token_list = Provider::select('providers.id', 'providers.device_token')
//                ->whereRaw(DB::raw("(6371 * acos( cos( radians(" . $lat . ") ) * cos( radians( current_lat ) )  * cos( radians( current_long ) - radians(" . $long . ") ) + sin( radians(" . $lat . ") ) * sin(radians( current_lat ) ) ) ) < " . $radius))
//                ->join('provider_services', 'provider_services.provider_id', '=', 'providers.id')
//                ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
//                ->join('transport_driver_details', 'transport_driver_details.provider_service_id', '=', 'provider_services.id')
//                ->whereIn('service_category.id', [$this->taxi_ride])
//                ->where('providers.status', 1)
//                ->where('provider_services.status', 1)
//                ->where('provider_services.current_status', 1)
//                ->where('transport_driver_details.courier_boy', 1)
//                ->whereIN('transport_driver_details.vehicle_type_id', $vehicle_type_list)
//                ->where('transport_driver_details.last_online_date_time', '>=', $expire_date_time)
//                ->whereNotNull('transport_driver_details.current_lat')
//                ->whereNotNull('transport_driver_details.current_long')
//                ->whereNotNull('providers.access_token')
//                ->whereNotNull('providers.device_token')
//                ->get();
//        }  else {
//            $get_provider_token_list = Provider::select('providers.id', 'providers.device_token')
//                ->whereRaw(DB::raw("(6371 * acos( cos( radians(" . $lat . ") ) * cos( radians( current_lat ) )  * cos( radians( current_long ) - radians(" . $long . ") ) + sin( radians(" . $lat . ") ) * sin(radians( current_lat ) ) ) ) < " . $radius))
//                ->join('provider_services', 'provider_services.provider_id', '=', 'providers.id')
//                ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
//                ->join('transport_driver_details', 'transport_driver_details.provider_service_id', '=', 'provider_services.id')
//                ->where('service_category.id', $service_cat_id)
//                ->where('providers.status', 1)
//                ->where('providers.login_device', 1)
//                ->where('provider_services.status', 1)
//                ->where('provider_services.current_status', 1)
//                ->where('transport_driver_details.courier_boy', 1)
//                ->where('transport_driver_details.vehicle_type_id', $ride_request->vehicle_type_id)
//                ->where('transport_driver_details.last_online_date_time', '>=', $expire_date_time)
//                ->whereNotNull('transport_driver_details.current_lat')
//                ->whereNotNull('transport_driver_details.current_long')
//                ->whereNotNull('providers.access_token')
//                ->whereNotNull('providers.device_token')
//                ->get();
//        }*/
//        $server_key = trim($fcm_server_key);
//
//        $pickup = array_map('trim', explode(",", $ride_request->pickup_latlong));
//        $destination = array_map('trim', explode(",", $ride_request->destination_latlong));
//        $way_points[] = [
//            "address" => $ride_request->pickup_address,
//            "address_lat" => trim($pickup[0]),
//            "address_long" => trim($pickup[1])
//        ];
//        if ($ride_request->is_way_point == 1) {
//            $ride_way_point = UserRideWayPoint::query()->where('ride_id', $ride_request->request_id)->first();
//            if ($ride_way_point != Null) {
//                if ($ride_way_point->way_point_1 != Null && $ride_way_point->lat_long_1 != Null) {
//                    $lat_long_1 = array_map('trim', explode(",", $ride_way_point->lat_long_1));
//                    $way_points[] = [
//                        "address" => $ride_way_point->way_point_1,
//                        "address_lat" => trim($lat_long_1[0]),
//                        "address_long" => trim($lat_long_1[1])
//                    ];
//                }
//                if ($ride_way_point->way_point_2 != Null && $ride_way_point->lat_long_2 != Null) {
//                    $lat_long_2 = explode(",", $ride_way_point->lat_long_2);
//                    $way_points[] = [
//                        "address" => $ride_way_point->way_point_1,
//                        "address_lat" => trim($lat_long_2[0]),
//                        "address_long" => trim($lat_long_2[1])
//                    ];
//                }
//                if ($ride_way_point->way_point_3 != Null && $ride_way_point->lat_long_3 != Null) {
//                    $lat_long_3 = explode(",", $ride_way_point->lat_long_3);
//                    $way_points[] = [
//                        "address" => $ride_way_point->way_point_3,
//                        "address_lat" => trim($lat_long_3[0]),
//                        "address_long" => trim($lat_long_3[1])
//                    ];
//                }
//
//            }
//        }
//        $way_points[] = [
//            "address" => $ride_request->destination_address,
//            "address_lat" => trim($destination[0]),
//            "address_long" => trim($destination[1])
//        ];
//
//        //To driver Assign
//        $title = $ride_request->service_cat_name;
//        $fcmUrl = 'https://fcm.googleapis.com/fcm/send';
//        $extraNotificationData = [
//            'title' => $title,
//            'title_code' => 22,
//            'sound' => true,
//            'description' => "New Request",
//            'message_code' => 90,
//            'message' => "New Request",
//            'request_id' => $ride_request->request_id,
//            'service_category_id' => $ride_request->service_cat_id,
//            'service_category_name' => $ride_request->service_cat_name,
//            'service_category_icon' => $ride_request->service_cat_icon != Null ? url('/assets/images/service-category/' . $ride_request->service_cat_icon) : '',
//            "address_list" => $way_points,
//            'notification_type' => $ride_request->ride_type,
//            'is_way_points' => $ride_request->is_way_point,
//            'schedule_date_time' => $ride_request->pickup_datetime,
//            'schedule_display_date_time' => Date('h:i, d M Y', strtotime($ride_request->pickup_datetime)),
//            'payment_type' => $ride_request->payment_type,
//            'goods_height_width' => $ride_request->goods_height . ' X ' . $ride_request->goods_width,
//            'goods_weight' => $ride_request->goods_weight,
//            'driver_algorithm' => $ride_request->driver_algorithm,
//            'request_timeout' => $timeout
//        ];
//
//        if ($get_provider_token_list != Null) {
//            foreach ($get_provider_token_list as $key => $provider) {
//                //$driver_request_notify_check = DriverNewRequestNotification::query()->where('driver_id', $provider->id)->first();
//                //if ($driver_request_notify_check == Null) {
//                $driver_request_notify = new DriverNewRequestNotification();
//                $driver_request_notify->driver_id = $provider['id'];
//                $driver_request_notify->request_id = $ride_request->request_id;
//                $driver_request_notify->service_cat_id = $ride_request->service_cat_id;
//                $driver_request_notify->notification_data = json_encode($extraNotificationData);
//                $driver_request_notify->save();
//
//                $get_provider_token[] = [
//                    $provider['id'],
//                ];
//                //}
//            }
//        }
//
//        if (is_array($provider_token)) {
//            $fcmNotification = [
//                'registration_ids' => $provider_token, //multiple token array
//                'data' => $extraNotificationData
//            ];
//        } else {
//            $fcmNotification = [
//                'to' => $provider_token,
//                'data' => $extraNotificationData
//            ];
//        }
//        $headers = [
//            'Authorization: key=' . $server_key,
//            'Content-Type: application/json'
//        ];
//        $ch = curl_init();
//        curl_setopt($ch, CURLOPT_URL, $fcmUrl);
//        curl_setopt($ch, CURLOPT_POST, true);
//        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmNotification));
//        $result = curl_exec($ch);
//        curl_close($ch);
//
//        return $result;
//    }

//courier ride ios request
//    public function driverTransportCourierIosRequestNotification($service_cat_id, $expire_date_time, $ride_request, $timeout, $radius, $fcm_server_key,$driver_token = Null)
//    {
//        $driver_token = $this->getDriverForRide($service_cat_id, $expire_date_time, $ride_request, 2, $radius);
//        $get_provider_token_list = $driver_token;
//        $provider_token = array_column($driver_token, 'device_token');
//
//       /* $latlong = array_map('trim', explode(",", $ride_request->pickup_latlong));
//        $lat = $latlong[0];
//        $long = $latlong[1];
//        $get_provider_token = [];
//        if ($ride_request->vehicle_type_id == $this->courier_scooter) {
//            $vehicle_type_list = TransportVehicleType::where('service_cat_id', $this->bike_ride)->get()->pluck('id')->toArray();
//            $get_provider_token_list = Provider::select('providers.id', 'providers.device_token')
//                ->whereRaw(DB::raw("(6371 * acos( cos( radians(" . $lat . ") ) * cos( radians( current_lat ) )  * cos( radians( current_long ) - radians(" . $long . ") ) + sin( radians(" . $lat . ") ) * sin(radians( current_lat ) ) ) ) < " . $radius))
//                ->join('provider_services', 'provider_services.provider_id', '=', 'providers.id')
//                ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
//                ->join('transport_driver_details', 'transport_driver_details.provider_service_id', '=', 'provider_services.id')
//                ->where('service_category.id', $this->bike_ride)
//                ->where('providers.status', 1)
//                ->where('providers.login_device', 2)
//                ->where('provider_services.status', 1)
//                ->where('provider_services.current_status', 1)
//                ->where('transport_driver_details.courier_boy', 1)
//                ->whereIN('transport_driver_details.vehicle_type_id', $vehicle_type_list)
//                ->where('transport_driver_details.last_online_date_time', '>=', $expire_date_time)
//                ->whereNotNull('transport_driver_details.current_lat')
//                ->whereNotNull('transport_driver_details.current_long')
//                ->whereNotNull('providers.access_token')
//                ->whereNotNull('providers.device_token')
//                ->get();
//            //->pluck('device_token')->toArray();
//        } else {
//            $get_provider_token_list = Provider::select('providers.id', 'providers.device_token')
//                ->whereRaw(DB::raw("(6371 * acos( cos( radians(" . $lat . ") ) * cos( radians( current_lat ) )  * cos( radians( current_long ) - radians(" . $long . ") ) + sin( radians(" . $lat . ") ) * sin(radians( current_lat ) ) ) ) < " . $radius))
//                ->join('provider_services', 'provider_services.provider_id', '=', 'providers.id')
//                ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
//                ->join('transport_driver_details', 'transport_driver_details.provider_service_id', '=', 'provider_services.id')
//                ->where('service_category.id', $service_cat_id)
//                ->where('providers.status', 1)
//                ->where('providers.login_device', 2)
//                ->where('provider_services.status', 1)
//                ->where('provider_services.current_status', 1)
//                ->where('transport_driver_details.courier_boy', 1)
//                ->where('transport_driver_details.vehicle_type_id', $ride_request->vehicle_type_id)
//                ->where('transport_driver_details.last_online_date_time', '>=', $expire_date_time)
//                ->whereNotNull('transport_driver_details.current_lat')
//                ->whereNotNull('transport_driver_details.current_long')
//                ->whereNotNull('providers.access_token')
//                ->whereNotNull('providers.device_token')
//                ->get();
//            //->pluck('device_token')->toArray();
//        }*/
//
//        $server_key = trim($fcm_server_key);
//
//        $pickup = array_map('trim', explode(",", $ride_request->pickup_latlong));
//        $destination = array_map('trim', explode(",", $ride_request->destination_latlong));
//        $way_points[] = [
//            "address" => $ride_request->pickup_address,
//            "address_lat" => trim($pickup[0]),
//            "address_long" => trim($pickup[1])
//        ];
//        if ($ride_request->is_way_point == 1) {
//            $ride_way_point = UserRideWayPoint::query()->where('ride_id', $ride_request->request_id)->first();
//            if ($ride_way_point != Null) {
//                if ($ride_way_point->way_point_1 != Null && $ride_way_point->lat_long_1 != Null) {
//                    $lat_long_1 = array_map('trim', explode(",", $ride_way_point->lat_long_1));
//                    $way_points[] = [
//                        "address" => $ride_way_point->way_point_1,
//                        "address_lat" => trim($lat_long_1[0]),
//                        "address_long" => trim($lat_long_1[1])
//                    ];
//                }
//                if ($ride_way_point->way_point_2 != Null && $ride_way_point->lat_long_2 != Null) {
//                    $lat_long_2 = explode(",", $ride_way_point->lat_long_2);
//                    $way_points[] = [
//                        "address" => $ride_way_point->way_point_1,
//                        "address_lat" => trim($lat_long_2[0]),
//                        "address_long" => trim($lat_long_2[1])
//                    ];
//                }
//                if ($ride_way_point->way_point_3 != Null && $ride_way_point->lat_long_3 != Null) {
//                    $lat_long_3 = explode(",", $ride_way_point->lat_long_3);
//                    $way_points[] = [
//                        "address" => $ride_way_point->way_point_3,
//                        "address_lat" => trim($lat_long_3[0]),
//                        "address_long" => trim($lat_long_3[1])
//                    ];
//                }
//
//            }
//        }
//        $way_points[] = [
//            "address" => $ride_request->destination_address,
//            "address_lat" => trim($destination[0]),
//            "address_long" => trim($destination[1])
//        ];
//
//        //To driver Assign
//        $title = $ride_request->service_cat_name;
//        $fcmUrl = 'https://fcm.googleapis.com/fcm/send';
//        $extraNotificationData = [
//            'request_id' => $ride_request->request_id,
//            'service_category_id' => $ride_request->service_cat_id,
//            'service_category_name' => $ride_request->service_cat_name,
//            'service_category_icon' => $ride_request->service_cat_icon != Null ? url('/assets/images/service-category/' . $ride_request->service_cat_icon) : '',
//            "address_list" => $way_points,
//            'notification_type' => $ride_request->ride_type,
//            'is_way_points' => $ride_request->is_way_point,
//            'schedule_date_time' => $ride_request->pickup_datetime,
//            'schedule_display_date_time' => Date('h:i, d M Y', strtotime($ride_request->pickup_datetime)),
//            'payment_type' => $ride_request->payment_type,
//            'goods_height_width' => $ride_request->goods_height . ' X ' . $ride_request->goods_width,
//            'goods_weight' => $ride_request->goods_weight,
//            'driver_algorithm' => $ride_request->driver_algorithm,
//            'request_timeout' => $timeout
//        ];
//        $notification = [
//            'title' => $title,
//            'text' => "New Request",
//            'sound' => 'default',
//            'badge' => '1',
//            'addData' => $extraNotificationData,
//            "title_loc_key" => 22,
//            "body_loc_key" => 90,
//        ];
//
//        if ($get_provider_token_list != Null) {
//            foreach ($get_provider_token_list as $key => $provider) {
//                //$driver_request_notify_check = DriverNewRequestNotification::query()->where('driver_id', $provider->id)->first();
//                //if ($driver_request_notify_check == Null) {
//                $driver_request_notify = new DriverNewRequestNotification();
//                $driver_request_notify->driver_id = $provider['id'];
//                $driver_request_notify->request_id = $ride_request->request_id;
//                $driver_request_notify->service_cat_id = $ride_request->service_cat_id;
//                $driver_request_notify->notification_data = json_encode($extraNotificationData);
//                $driver_request_notify->save();
//
//                $get_provider_token[] = [
//                    $provider['id'],
//                ];
//                //}
//            }
//
//        }
//        if (is_array($provider_token)) {
//            $arrayToSend = array(
//                'registration_ids' => $provider_token,
//                'notification' => $notification,
//                'priority' => 'high',
//                "content_available" => true
//            );
//        } else {
//            $arrayToSend = array(
//                'registration_ids' => $provider_token,
//                'notification' => $notification,
//                'priority' => 'high',
//                "content_available" => true
//            );
//        }
//        $headers = [
//            'Authorization: key=' . $server_key,
//            'Content-Type: application/json'
//        ];
//        $ch = curl_init();
//        curl_setopt($ch, CURLOPT_URL, $fcmUrl);
//        curl_setopt($ch, CURLOPT_POST, true);
//        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($arrayToSend));
//        $result = curl_exec($ch);
//        curl_close($ch);
//        return $result;
//    }

//delivery ride android request
//    public function driverDeliveryAndroidRequestNotification($expire_date_time, $ride_request, $timeout, $radius, $fcm_server_key,$driver_token = Null)
//    {
//
//        $driver_token = $this->getDriverForRide(0, $expire_date_time, $ride_request, 1, $radius);
//        $get_provider_token_list = $driver_token;
//        $provider_token = array_column($driver_token,'device_token');
//
//        /*$lat = $ride_request->pickup_lat;
//        $long = $ride_request->pickup_long;
//        $get_provider_token = [];
//        $get_provider_token_list = Provider::select('providers.id', 'providers.device_token')
//            ->whereRaw(DB::raw("(6371 * acos( cos( radians(" . $lat . ") ) * cos( radians( current_lat ) )  *
//                          cos( radians( current_long ) - radians(" . $long . ") ) + sin( radians(" . $lat . ") ) * sin(
//                          radians( current_lat ) ) ) ) < " . $radius)
//            )
//            ->join('provider_services', 'provider_services.provider_id', '=', 'providers.id')
//            ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
//            ->join('transport_driver_details', 'transport_driver_details.provider_service_id', '=', 'provider_services.id')
//            ->where('service_category.id', 1)
//            ->where('providers.status', 1)
//            ->where('providers.login_device', 1)
//            ->where('provider_services.status', 1)
//            ->where('provider_services.current_status', 1)
//            ->where('transport_driver_details.delivery_boy', 1)
//            ->where('transport_driver_details.last_online_date_time', '>=', $expire_date_time)
//            ->whereNotNull('transport_driver_details.current_lat')
//            ->whereNotNull('transport_driver_details.current_long')
//            ->whereNotNull('providers.access_token')
//            ->whereNotNull('providers.device_token')
//            ->get();*/
//        $server_key = trim($fcm_server_key);
//        $way_points[] = [
//            "address" => $ride_request->pickup_address,
//            "address_lat" => trim($ride_request->pickup_lat),
//            "address_long" => trim($ride_request->pickup_long)
//        ];
//        $destination = array_map('trim', explode(",", $ride_request->destination_latlong));
//        $way_points[] = [
//            "address" => $ride_request->destination_address,
//            "address_lat" => trim($destination[0]),
//            "address_long" => trim($destination[1])
//        ];
//        //To driver Assign
//        $title = $ride_request->service_cat_name;
//        $fcmUrl = 'https://fcm.googleapis.com/fcm/send';
//        $extraNotificationData = [
//            'title' => $title,
//            'title_code' => 22,
//            'sound' => true,
//            'description' => "New Order",
//            'message_code' => 41,
//            'message' => "New Order",
//            'request_id' => $ride_request->request_id,
//            'service_category_id' => $ride_request->service_cat_id,
//            'service_category_name' => ($ride_request->service_cat_name != Null) ? $ride_request->service_cat_name : "",
//            'store_name' => ($ride_request->store_name != Null) ? $ride_request->store_name : "",
//            'service_category_icon' => $ride_request->service_cat_icon != Null ? url('/assets/images/service-category/' . $ride_request->service_cat_icon) : '',
//            "address_list" => $way_points,
//            'notification_type' => 0,
//            'is_way_points' => 0,
//            'schedule_date_time' => $ride_request->pickup_datetime,
//            'schedule_display_date_time' => Date('h:i, d M Y', strtotime($ride_request->pickup_datetime)),
//            'payment_type' => $ride_request->payment_type,
//            'driver_algorithm' => $ride_request->driver_algorithm,
//            'request_timeout' => $timeout
//        ];
//
//        if ($get_provider_token_list != Null) {
//            foreach ($get_provider_token_list as $key => $provider) {
//                $driver_request_notify = new DriverNewRequestNotification();
//                $driver_request_notify->driver_id = $provider['id'];
//                $driver_request_notify->request_id = $ride_request->request_id;
//                $driver_request_notify->service_cat_id = $ride_request->service_cat_id;
//                $driver_request_notify->notification_data = json_encode($extraNotificationData);
//                $driver_request_notify->save();
//
//                $get_provider_token[] = [
//                    $provider['id'],
//                ];
//            }
//        }
//        if (is_array($provider_token)) {
//            $fcmNotification = [
//                'registration_ids' => $provider_token, //multple token array
//                'data' => $extraNotificationData
//            ];
//        } else {
//            $fcmNotification = [
//                'to' => $provider_token,
//                'data' => $extraNotificationData
//            ];
//        }
//        $headers = [
//            'Authorization: key=' . $server_key,
//            'Content-Type: application/json'
//        ];
//        $ch = curl_init();
//        curl_setopt($ch, CURLOPT_URL, $fcmUrl);
//        curl_setopt($ch, CURLOPT_POST, true);
//        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmNotification));
//        $result = curl_exec($ch);
//        curl_close($ch);
//        return $result;
//    }

//delivery ride ios request
//    public function driverDeliveryIosRequestNotification($expire_date_time, $ride_request, $timeout, $radius, $fcm_server_key,$driver_token = Null)
//    {
//
//        $driver_token = $this->getDriverForRide(0, $expire_date_time, $ride_request, 2, $radius);
//        $get_provider_token_list = $driver_token;
//        $provider_token = array_column($driver_token,'device_token');
//        $server_key = trim($fcm_server_key);
//        $way_points[] = [
//            "address" => $ride_request->pickup_address,
//            "address_lat" => trim($ride_request->pickup_lat),
//            "address_long" => trim($ride_request->pickup_long)
//        ];
//        $destination = array_map('trim', explode(",", $ride_request->destination_latlong));
//        $way_points[] = [
//            "address" => $ride_request->destination_address,
//            "address_lat" => trim($destination[0]),
//            "address_long" => trim($destination[1])
//        ];
//        //To driver Assign
//        $title = $ride_request->service_cat_name;
//        $fcmUrl = 'https://fcm.googleapis.com/fcm/send';
//        $extraNotificationData = [
//            'request_id' => $ride_request->request_id,
//            'service_category_id' => $ride_request->service_cat_id,
//            'service_category_name' => ($ride_request->service_cat_name != Null) ? $ride_request->service_cat_name : "",
//            'store_name' => ($ride_request->store_name != Null) ? $ride_request->store_name : "",
//            'service_category_icon' => $ride_request->service_cat_icon != Null ? url('/assets/images/service-category/' . $ride_request->service_cat_icon) : '',
//            "address_list" => $way_points,
//            'notification_type' => 0,
//            'is_way_points' => 0,
//            'schedule_date_time' => $ride_request->pickup_datetime,
//            'schedule_display_date_time' => Date('h:i, d M Y', strtotime($ride_request->pickup_datetime)),
//            'payment_type' => $ride_request->payment_type,
//            'driver_algorithm' => $ride_request->driver_algorithm,
//            'request_timeout' => $timeout
//        ];
//        $notification = [
//            'title' => $title,
//            'text' => "New Order",
//            'sound' => 'default',
//            'badge' => '1',
//            'addData' => $extraNotificationData,
//            "title_loc_key" => 22,
//            "body_loc_key" => 41,
//        ];
//        if ($get_provider_token_list != Null) {
//            foreach ($get_provider_token_list as $key => $provider) {
//                $driver_request_notify = new DriverNewRequestNotification();
//                $driver_request_notify->driver_id = $provider['id'];
//                $driver_request_notify->request_id = $ride_request->request_id;
//                $driver_request_notify->service_cat_id = $ride_request->service_cat_id;
//                $driver_request_notify->notification_data = json_encode($extraNotificationData);
//                $driver_request_notify->save();
//
//                $get_provider_token[] = [
//                    $provider['id'],
//                ];
//            }
//        }
//        if (is_array($provider_token)) {
//            $arrayToSend = array(
//                'registration_ids' => $provider_token,
//                'notification' => $notification,
//                'priority' => 'high',
//                "content_available" => true
//            );
//        } else {
//            $arrayToSend = array(
//                'registration_ids' => $provider_token,
//                'notification' => $notification,
//                'priority' => 'high',
//                "content_available" => true
//            );
//        }
//        $headers = [
//            'Authorization: key=' . $server_key,
//            'Content-Type: application/json'
//        ];
//        $ch = curl_init();
//        curl_setopt($ch, CURLOPT_URL, $fcmUrl);
//        curl_setopt($ch, CURLOPT_POST, true);
//        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($arrayToSend));
//        $result = curl_exec($ch);
//        curl_close($ch);
//        return $result;
//    }

//push notification in user in ios and android
//    public function forIosUserMassPushNotification($server_key, $token_user, $title,$message)
//    {
//        $fcmUrl = 'https://fcm.googleapis.com/fcm/send';
//        $body = array(
//            "to" => "/topics/" . $token_user,
//            "notification" => array(
//                "body" => $message,
//                "title" => $title,
//                "sound" => "default",
//                "content-available" => 1,
//                "click_action" => "FLUTTER_NOTIFICATION_CLICK"
//            ),
//            "content_available" => true,
//            "data" => array(
//                "title" => $title,
//                "body" => $message,
//                'message' => $message,
//                'notification_type' => 0,
//                'priority' => 'high',
//            ),
//        );
//        $headers = array
//        (
//            'Authorization: key=' . $server_key,
//            'Content-Type: application/json'
//        );
//
//        $ch = curl_init();
//        curl_setopt($ch, CURLOPT_URL, $fcmUrl);
//        curl_setopt($ch, CURLOPT_POST, true);
//        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
//        $result = curl_exec($ch);
//        curl_close($ch);
//        return $result;
//    }

//push notification in Driver in ios and android
//    public function forIosDriverMassPushNotification($server_key, $token_driver,$title, $message)
//    {
//        $fcmUrl = 'https://fcm.googleapis.com/fcm/send';
//        $body = array(
//            "to" => "/topics/" . $token_driver,
//            "notification" => array(
//                "body" => $message,
//                "title" => $title,
//                "sound" => "default"
//            ),
//            "content_available" => true,
//            "data" => array(
//                "title" => $title,
//                "body" => $message,
//                'message' => $message,
//                'notification_type' => 3,
//            ),
//        );
//        $headers = array
//        (
//            'Authorization: key=' . $server_key,
//            'Content-Type: application/json'
//        );
//        $ch = curl_init();
//        curl_setopt($ch, CURLOPT_URL, $fcmUrl);
//        curl_setopt($ch, CURLOPT_POST, true);
//        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
//        $result = curl_exec($ch);
//        curl_close($ch);
//        return $result;
//    }

//push notification in Store in ios and android
//    public function forIosStoreMassPushNotification($server_key, $token_store,$title,$message)
//    {
//        $fcmUrl = 'https://fcm.googleapis.com/fcm/send';
//        $body = array(
//            "to" => "/topics/" . $token_store,
//            "notification" => array(
//                "body" => $message,
//                "title" => $title,
//                "sound" => "default"
//            ),
//            "content_available" => true,
//            "data" => array(
//                "title" => $title,
//                "body" => $message,
//                'message' => $message,
//                'notification_type' => 3,
//            ),
//        );
//        $headers = array
//        (
//            'Authorization: key=' . $server_key,
//            'Content-Type: application/json'
//        );
//        $ch = curl_init();
//        curl_setopt($ch, CURLOPT_URL, $fcmUrl);
//        curl_setopt($ch, CURLOPT_POST, true);
//        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
//        $result = curl_exec($ch);
//        curl_close($ch);
//
//        return $result;
//    }

//push notification in Provider in ios and android
//    public function forIosProviderMassPushNotification($server_key, $token_provider,$title,$message)
//    {
//        $fcmUrl = 'https://fcm.googleapis.com/fcm/send';
//        $body = array(
//            "to" => "/topics/" . $token_provider,
//            "notification" => array(
//                "body" => $message,
//                "title" => $title,
//                "sound" => "default"
//            ),
//            "content_available" => true,
//            "data" => array(
//                "title" => $title,
//                "body" => $message,
//                'message' => $message,
//                'notification_type' => 3,
//            ),
//        );
//        $headers = array
//        (
//            'Authorization: key=' . $server_key,
//            'Content-Type: application/json'
//        );
//        $ch = curl_init();
//        curl_setopt($ch, CURLOPT_URL, $fcmUrl);
//        curl_setopt($ch, CURLOPT_POST, true);
//        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
//        $result = curl_exec($ch);
//        curl_close($ch);
//
//        return $result;
//    }
