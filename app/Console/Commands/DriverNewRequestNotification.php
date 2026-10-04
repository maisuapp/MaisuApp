<?php

namespace App\Console\Commands;

use App\Classes\NotificationClass;
use App\model\GeneralSettings;
use App\model\ServiceSettings;
use App\model\TransportRentalRideBooking;
use App\model\TransportRideBook;
use App\model\TransportRideDriverToken;
use App\model\UserProductBooking;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DriverNewRequestNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'driver_new_request_notification {--delay= : 15}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
    private $notificationClass;

    /**
     * Create a new command instance.
     *
     * @param NotificationClass $notificationClass
     * @return void
     */
    public function __construct(NotificationClass $notificationClass)
    {
        parent::__construct();
        $this->notificationClass = $notificationClass;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        sleep(intval($this->option('delay')));

        $general_settings = GeneralSettings::query()->first();
        $expire_date_time = date('Y-m-d H:i:s', strtotime("-6000 minutes"));
        $ride_driver_tokens = TransportRideDriverToken::query()->get();
        if($ride_driver_tokens != Null){
            $service_setting = ServiceSettings::where('service_cat_id', 4)->first();
            if ($service_setting != Null) {
                $radius = $service_setting->provider_search_radius;
                $timeout = $service_setting->provider_accept_timeout;
            } else {
                $radius = 5;
                $timeout = 60;
            }

            foreach ($ride_driver_tokens as $ride_driver_token) {
                if($ride_driver_token->service_cat_id == 4){
                    $ride_request = TransportRideBook::select('user_ride_booking.id as request_id',
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
                        ->where('user_ride_booking.id', $ride_driver_token->ride_id)->first();
                    if ($ride_request != Null) {
                        $time_to_check = date("Y-m-d H:i:s", strtotime("+" . $general_settings->user_timeout ." seconds",strtotime($ride_request->retry_time)));
                        if(time() < strtotime($time_to_check)) {
                            $ride = TransportRideBook::query()->where('id',$ride_driver_token->ride_id)->first();
                            $ride->retry_time = date('Y-m-d H:i:s');
                            $ride->save();
                            if (strtotime($ride_driver_token->expire_date_time) <= time()) {
                                if ($ride_driver_token->android_tokens != Null) {
                                    $this->notificationClass->driverTransportCourierAllRequestNotification($ride_request->service_cat_id, $expire_date_time, $ride_request, $timeout, $radius, $general_settings->fcm_server_key, $ride_driver_token->android_tokens);
                                }
                            }
                        }
                    }
                } else if(in_array($ride_driver_token->service_cat_id,[1,2])){
                    $ride_request = TransportRideBook::select('user_ride_booking.id as request_id',
                        'user_ride_booking.pickup_datetime','user_ride_booking.driver_algorithm','user_ride_booking.retry_time',
                        'user_ride_booking.pickup_address', 'user_ride_booking.is_way_point',
                        'user_ride_booking.ride_type', 'user_ride_booking.handicap', 'user_ride_booking.child_seat',
                        'user_ride_booking.pickup_latlong', 'user_ride_booking.destination_address',
                        'user_ride_booking.destination_latlong', 'user_ride_booking.payment_type', 'user_ride_booking.vehicle_type_id',
                        'service_category.id as service_cat_id', 'service_category.name as service_cat_name',
                        'service_category.icon_name as service_cat_icon')
                        ->join('service_category', 'service_category.id', '=', 'user_ride_booking.service_cat_id')
                        ->where('user_ride_booking.id', $ride_driver_token->ride_id)->first();
                    if ($ride_request != Null) {
                        $time_to_check = date("Y-m-d H:i:s", strtotime("+" . $general_settings->user_timeout ." seconds",strtotime($ride_request->retry_time)));
                        if(time() < strtotime($time_to_check)) {
                            $ride = TransportRideBook::query()->where('id',$ride_driver_token->ride_id)->first();
                            $ride->retry_time = date('Y-m-d H:i:s');
                            $ride->save();
                            if (strtotime($ride_driver_token->expire_date_time) <= time()) {
                                $service_setting = ServiceSettings::where('service_cat_id', $ride_request->service_cat_id)->first();
                                if ($service_setting != Null) {
                                    $radius = $service_setting->provider_search_radius;
                                    $timeout = $service_setting->provider_accept_timeout;
                                } else {
                                    $radius = 5;
                                    $timeout = 60;
                                }
                                if ($ride_driver_token->android_tokens != Null) {
                                    $this->notificationClass->driverTransportAllRequestNotification($ride_request->service_cat_id, $expire_date_time, $ride_request, $timeout, $radius, $ride_driver_token->android_tokens);
                                }
                            }
                        }
                    }
                } else if(in_array($ride_driver_token->service_cat_id,[31,32])){
                    $ride_request = TransportRentalRideBooking::query()->select('user_rental_ride_booking.id as request_id',
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
                        ->where('user_rental_ride_booking.id',$ride_driver_token->ride_id)->first();
                    if ($ride_request != Null) {
                        if(strtotime($ride_driver_token->expire_date_time) <= time()) {
                            $service_setting = ServiceSettings::where('service_cat_id', $ride_request->service_cat_id)->first();
                            if ($service_setting != Null) {
                                $radius = $service_setting->provider_search_radius;
                                $timeout = $service_setting->provider_accept_timeout;
                            } else {
                                $radius = 5;
                                $timeout = 60;
                            }
                            if ($ride_driver_token->android_tokens != Null) {
                                $this->notificationClass->driverTransportRentalAllRequestNotification($ride_request->service_cat_id, $expire_date_time, $ride_request, $timeout, $radius, $ride_driver_token->android_tokens);
                            }
                        }
                    }
                } else{
                    $order_details = UserProductBooking::select(
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
                        ->where('user_store_product_booking.id', $ride_driver_token->delivery_id)->first();
                    if ($order_details != Null) {
                        if(strtotime($ride_driver_token->expire_date_time) <= time()) {
                            $service_setting = ServiceSettings::where('service_cat_id', $order_details->service_cat_id)->first();
                            if ($service_setting != Null) {
                                $radius = $service_setting->provider_search_radius;
                                $timeout = $service_setting->provider_accept_timeout;
                            } else {
                                $radius = 5;
                                $timeout = 60;
                            }
                            if ($ride_driver_token->android_tokens != Null) {
                                $this->notificationClass->driverDeliveryAllRequestNotification($expire_date_time, $order_details, $timeout, $radius, $general_settings->fcm_server_key, $ride_driver_token->android_tokens);
                            }
                        }
                    }
                }


            }
        }
    }
}
