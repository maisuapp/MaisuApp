<?php

namespace App\Http\Controllers\User;

use App\Classes\AdminClass;
use App\Classes\NotificationClass;
use App\Classes\TokenClassApi;
use App\Classes\UserClassApi;
use App\Jobs\AutoMail;
use App\model\GeneralSettings;
use App\model\PageSettings;
use App\model\PromocodeDetails;
use App\model\Provider;
use App\model\ProviderServices;
use App\model\ProviderUserRunningService;
use App\model\ServiceCategory;
use App\model\ServiceSettings;
use App\model\TransportCategoryList;
use App\model\TransportCourierDetails;
use App\model\TransportDriverDetails;
use App\model\TransportRideBook;
use App\model\TransportVehicleType;
use App\model\TransportWeightList;
use App\model\UserOrderCart;
use App\model\UsedPromocodeDetails;
use App\model\User;
use App\model\UserAddress;
use App\model\UserCardDetails;
use App\model\UserReferHistory;
use App\Model\UserRideWayPoint;
use App\model\UserRunningRide;
use App\model\UserVerification;
use App\model\UserWalletTransaction;
use App\model\WorldCurrency;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Twilio\Rest\Client;

class TransportController extends Controller
{
    //
    private $email_time = 5;//in seconds
    private $adminClass;
    private $bike_ride;
    private $taxi_ride;
    private $bike_rental;
    private $taxi_rental;
    private $courier_service;
    private $driverClassApi;
    private $notificationClass;
    private $transport_service_id_array;
    private $courier_service_id_array;
    private $delivery_service_id_array;
    private $userClassapi;

    private $store_order_status;

    public function __construct(AdminClass $adminClass, NotificationClass $notificationClass,UserClassApi $userClassapi)
    {
        $this->userClassapi = $userClassapi;
        $this->notificationClass = $notificationClass;
        $this->adminClass = $adminClass;
        $this->bike_ride = 1;
        $this->taxi_ride = 2;
        $this->courier_service = 4;
        $this->bike_rental = 31;
        $this->taxi_rental = 32;
        $this->transport_service_id_array = [1, 2];
        $this->courier_service_id_array = [4];
        $this->delivery_service_id_array = [5, 6, 7, 8, 9, 10];
        $this->store_order_status = ['', 'pending', 'approved', 'rejected', 'cancelled', 'processing', 'approved-by-driver', 'arrived-driver', 'ongoing', 'completed', 'failed'];
    }

    public function getRideBooking($service = "bike-ride")
    {

        $exitCode = Artisan::call('cache:clear');
        if (!Auth::guard("user")->check()) {
            Auth::logout();
        }
        if($service != "taxi-ride" && $service != "bike-ride" && $service != "courier-service")
        {
            $service = "bike-ride";
        }

        if($service == "bike-ride"){
            $title = "Bike Ride";
            $get_vehicle_lists =  TransportVehicleType::query()->select()->where('service_cat_id',$this->bike_ride)->where('status',1)->get();

        }elseif($service == "taxi-ride"){
            $title = "Taxi Ride";
            $get_vehicle_lists =  TransportVehicleType::query()->select()->where('service_cat_id',$this->taxi_ride)->where('status',1)->get();

        }elseif($service == "courier-service"){
            $title = "Courier ";
            $transport_weight_list = TransportWeightList::query()->where('status','=',1)->get();
            $transport_category_list = TransportCategoryList::query()->where('status','=',1)->get();
            $get_vehicle_lists =  TransportVehicleType::query()->select()->where('service_cat_id',$this->courier_service)->where('status',1)->get();

            $user_id = isset(Auth::guard("user")->user()->id)?Auth::guard("user")->user()->id:0;

            $card_list = UserCardDetails::query()->select('card_details.id', 'card_details.holder_name as card_holder_name', 'card_details.card_number as card_number')
                    ->where('user_id','=',$user_id )
                    ->orderBy('id','desc')
                    ->get();

            return view('front.pages.transport.courier-booking',compact('service','card_list','title','get_vehicle_lists','transport_weight_list','transport_category_list'));

        }else{
            $get_vehicle_lists =  TransportVehicleType::query()->select()->where('service_cat_id',$this->bike_ride)->where('status',1)->get();

        }

        return view('front.pages.transport.ride-booking',compact('service','title','get_vehicle_lists'));
    }

    public function postRidePreview(Request $request,$service){

        $service_type = $service;
        $dest_address = $request->get('dest_address');
        $dest_lat = $request->get('dest_lat');
        $dest_long = $request->get('dest_long');
        $source_address = $request->get('source_address');
        $source_lat = $request->get('source_lat');
        $source_long = $request->get('source_long');
        $promocode = $request->get('promocode');
        $vehicle_type_id = $request->get('vehicle_type');
        $weight_id = $request->get('weight_id');
        $eta_distance = $request->get('eta_distance');
        $eta_distance_txt = ($request->get('eta_distance_txt') !="")?$request->get('eta_distance_txt'):"";
        $eta_time = $request->get('eta_time');
        $eta_time_txt = ($request->get('eta_time_txt') !="")?$request->get('eta_time_txt'):"";
        $user_id  = 0;

        $return_data = array();
        $success = false;
        $errmessage = "";


        //check location exist in service area or not
        $src_area = $this->userClassapi->checkServiceAvailableLocationNew($source_lat, $source_long);
        $dest_area = $this->userClassapi->checkServiceAvailableLocationNew($dest_lat, $dest_long);

        if($src_area == 1 || $dest_area == 1 ){

            return response()->json([
                'success' => false,
                'errmessage' => "Service is not available in your region, please try with other location",
                "htmlData"=>$this->userClassapi->getFareHtml()

            ]);
        }


        $service_category =  ServiceCategory::query()->where('slug',$service_type)->first();
        if($service_category != Null){


            $service_category_id = $service_category->id;
            if($this->courier_service == $service_category_id){
                //code check courier send request to sepefic
                $get_vehicle_price = TransportVehicleType::query()->where('service_cat_id', $service_category_id)->where('weight_limit', $weight_id)->first();
            }else{
                $get_vehicle_price = TransportVehicleType::query()->where('service_cat_id', $service_category_id)->where('id', $vehicle_type_id)->first();
            }

            if ($get_vehicle_price == Null) {

                return response()->json([
                    'success' => false,
                    'errmessage' => "Vehicle Type Not Found!",
                    "htmlData"=>$this->userClassapi->getFareHtml()

                ]);

            }
            $get_vehicle_price_id = $get_vehicle_price->id;
            $get_vehicle_price_cost_for_km = $get_vehicle_price->cost_for_km;
            $get_vehicle_price_name = $get_vehicle_price->name;
            $get_vehicle_price_base_fare = $get_vehicle_price->base_fare;
            $get_vehicle_price_time_fare = $get_vehicle_price->time_fare;
            $get_vehicle_price_min_fare_amount = $get_vehicle_price->min_fare_amount;

            if ($eta_distance == 0 || $eta_time == 0) {
                return response()->json([
                    'success' => false,
                    'errmessage' => "Location Not Found!",
                    "htmlData"=>$this->userClassapi->getFareHtml()
                ]);
            }
            $total_distance = 0;
            $total_mins = 0;
            $total_time_string = "";
            if ($eta_distance > 0 || $eta_time == 0) {

                $total_distance = $eta_distance;
                $total_mins = $eta_time;
                $total_mins =  ($total_mins > 0)?($total_mins/60 ):"0";
                $total_mins = ceil($total_mins);
                $total_time_string =$eta_time_txt;
                $total_distance = round(($total_distance * 0.001),2);
            }

            $total_min_price = round($total_mins * $get_vehicle_price_time_fare,2);
            $total_distance_price = round($total_distance *  $get_vehicle_price_cost_for_km);
            $base_fare = round($get_vehicle_price_base_fare,2);



            $total_fare_amount = $base_fare + $total_distance_price + $total_min_price;
            $adjustment_amount = 0;
            if($total_fare_amount < $get_vehicle_price_min_fare_amount){
                $adjustment_amount = round(($get_vehicle_price_min_fare_amount - $total_fare_amount), 2); //adjust amount
            }


            $total_fare_amount =  $total_fare_amount + $adjustment_amount;

            //get prmocode discount
//            $promocode_amount = $this->userClassapi->checkPromoCodeValid($promocode,$total_fare_amount, $user_id, $service_category_id,$type="w");
            $promocode_amount = 0;

            $total_fare_amount = $total_fare_amount - $promocode_amount;

            $service_settings = ServiceSettings::query()->where('service_cat_id', $service_category_id)->first();
            if ($service_settings != Null) {
                $admin_commission = $service_settings->admin_commission;
                $get_tax = $service_settings->tax;
            } else {
                $admin_commission = 0;
                $get_tax = 0;
            }


            $sub_total = $total_fare_amount;
            $total_tax = round((($total_fare_amount * $get_tax) / 100), 2); //tax count

            $total = $total_fare_amount + $total_tax;

            $final_fare_html = $this->userClassapi->getFareHtml($total_distance ,$total_time_string,$get_vehicle_price_cost_for_km,$total_distance_price,$get_vehicle_price_min_fare_amount,$get_vehicle_price_time_fare,
            $total_min_price,$base_fare,$adjustment_amount,$sub_total,$total_tax,$total);

            $success = true;
            $errmessage =  "Success";
            $htmlData =  $final_fare_html;

        }else{
            $errmessage =  "Service Category Not Found!";
        }


        return response()->json([
            'success' => $success,
            'errmessage' => $errmessage,
            'htmlData' => $htmlData

        ]);

    }

    public function postRideBook(Request $request,$service){

        $service_type = $service;
        $ride_id = $request->get('ride_id');
        $dest_address = $request->get('dest_address');
        $dest_lat = $request->get('dest_lat');
        $dest_long = $request->get('dest_long');
        $source_address = $request->get('source_address');
        $source_lat = $request->get('source_lat');
        $source_long = $request->get('source_long');
        $promocode = $request->get('promocode');
        $vehicle_type_id = $request->get('vehicle_type');
        $weight_id = $request->get('weight_id');
        $category_id = $request->get('category_id');
        $card_id = $request->get('card_id');
        $user_ride_type = $request->get('ride_type');
        $user_id = Auth::guard("user")->user()->id;

        $eta_distance = $request->get('eta_distance');
        $eta_distance_txt = ($request->get('eta_distance_txt') !="")?$request->get('eta_distance_txt'):"";
        $eta_time = $request->get('eta_time');
        $eta_time_txt = ($request->get('eta_time_txt') !="")?$request->get('eta_time_txt'):"";
        $pickup_date_time = $request->get('pickup_date_time');


        $return_data = array();
        $success = false;
        $errmessage = "";

        if($ride_id > 0)
        {
            //code for if ride is already exist
            $ride = TransportRideBook::query()->where('id', '=', $ride_id)->first();

            if($ride != Null)
            {
                $service_category =  ServiceCategory::query()->where('slug',$service_type)->first();
                if($service_category != Null) {
                    $service_category_id = $service_category->id;
                    $service_setting = ServiceSettings::query()->where('service_cat_id',$service_category_id)->first();
                    if ($service_setting == Null) {
                        return response()->json([
                            "status" => 0,
                            "message" => "something went to wrong!",
                            "message_code" => 9,
                        ]);
                    }
                    if ($service_setting->provider_accept_timeout != Null) {
                        $timeout = $service_setting->provider_accept_timeout;
                    } else {
                        $timeout = 60;
                    }
                    $driver_id= $ride->driver_id;
                    if($driver_id == null){
                        $this->notificationClass->driverRequestNotification($service_category_id, $ride->id);
                    }
                    return response()->json([
                        "status" => 1,
                        "message" => "Ride Booking Successfully!",
                        'success' => true,
                        "message_code" => 1,
                        "ride_id" => $ride->id,
                        "booking_no" => $ride->ride_no,
                        "accept_time_out" => round($timeout),
                        "redirect_path" => route('get:user_orders_all',['transport','all'])
                    ]);
                }else{
                    $errmessage =  "Service Category Not Found!";
                }
            }
            else{
                $errmessage =  "Ride Not Found!";
            }
        }
        else{
            //1 :cash , 2: card, 3:wallet
            $payment_type = ($request->get('payment_type') >=1 && $request->get('payment_type') <= 3)?$request->get('payment_type'):1;

            $src_area = $this->userClassapi->checkServiceAvailableLocationNew($source_lat, $source_long);
//            $dest_area = $this->userClassapi->checkServiceAvailableLocationNew($dest_lat, $dest_long);
            $dest_area = 0;
            if($src_area == 1 || $dest_area == 1 ){
                return response()->json([
                    'success' => false,
                    'errmessage' => "Service is not available in your region, please try with other location",
                    "htmlData"=>$this->userClassapi->getFareHtml()
                ]);
            }
            //find price service_type
            $service_category =  ServiceCategory::query()->where('slug',$service_type)->first();
            if($service_category != Null)
            {
                $user_details =  User::query()->where('id',$user_id)->first();
                if($user_details == Null){
                    return response()->json([
                        'success' => false,
                        "message" => "User Not Found!",
                        "message_code" => 9,
                    ]);
                }

                $service_category_id = $service_category->id;

                if($this->courier_service == $service_category_id){
                    //code check courier send request to sepefic
                    $get_vehicle_price = TransportVehicleType::query()->where('service_cat_id', $service_category_id)->where('weight_limit', $weight_id)->first();
                }else{
                    $get_vehicle_price = TransportVehicleType::query()->where('service_cat_id', $service_category_id)->where('id', $vehicle_type_id)->first();
                }

//                $get_vehicle_price = TransportVehicleType::query()->where('id', $vehicle_type_id)->first();
                if ($get_vehicle_price == Null) {
                    return response()->json([
                        'success' => false,
                        "message" => "Vehicle Type Not Found!",
                        "message_code" => 9,
                    ]);
                }
                $get_vehicle_price_id = $get_vehicle_price->id;
                $get_vehicle_weight_limit_id = $get_vehicle_price->weight_limit;
                $get_vehicle_price_cost_for_km = $get_vehicle_price->cost_for_km;
                $get_vehicle_price_name = $get_vehicle_price->name;
                $get_vehicle_price_base_fare = $get_vehicle_price->base_fare;
                $get_vehicle_price_time_fare = $get_vehicle_price->time_fare;
                $get_vehicle_price_min_fare_amount = $get_vehicle_price->min_fare_amount;


                if($service_category_id == $this->courier_service)
                {
                    $validator = Validator::make($request->all(), [
                        'payment_type' => 'Required|in:1,2,3',
                        'card_id' => 'required_if:payment_type,==,2',
                    ]);

                    if ($validator->fails()) {
                        $failedRules = $validator->failed();
                        if (isset($failedRules['payment_type']['Required'])) {
                            return response()->json([
                                'success' => false,
                                "errmessage" => 'Please add payment type before place order',
                                "message_code" => 9,
                            ]);
                        }
                        if (isset($failedRules['card_id']['RequiredIf'])) {
                            return response()->json([
                                'success' => false,
                                "errmessage" => 'Please select proper card for payment',
                                "message_code" => 9,
                            ]);
                        }
                    }
                    //check total for wallet patment gateway before data entry in db
                    if($payment_type == 3){
                        $get_user_wallet_details  = UserWalletTransaction::query()
                            ->where('user_id',$user_id)
                            ->orderBy('id','desc')
                            ->first();
                        if($get_user_wallet_details != Null)
                        {
                            $avail_wallet_balance = $get_user_wallet_details->remaining_balance;
                            if($avail_wallet_balance > 0){
                                $eta_time =  ($eta_time > 0)?($eta_time/60 ):"0";
                                $eta_time = ceil($eta_time);

                                $eta_distance = round(($eta_distance * 0.001),2);

                                try{
                                    $km = explode(' ', $eta_distance);
                                    $km_count = floatval($km[0]);
                                    $distance_km = $km_count;

                                    $eta = explode(' ', $eta_time);
                                    $eta_count = intval($eta[0]);

                                } catch (\Exception $e) {
                                    $distance_km = 0;
                                    $eta_count = 0;
                                }
                                $pre_base_fare = round(($get_vehicle_price_base_fare), 2);
                                $pre_total_distance_amount = round(($distance_km * $get_vehicle_price_cost_for_km), 2);
                                $pre_time_fare_amount = round(($eta_count * $get_vehicle_price_time_fare), 2);

                                $service_settings = ServiceSettings::query()->where('service_cat_id', $service_category_id)->first();
                                if ($service_settings != Null) {
                                    $admin_commission = $service_settings->admin_commission;
                                    $get_tax = $service_settings->tax;
                                } else {
                                    $admin_commission = 0;
                                    $get_tax = 0;
                                }

                                $pre_step_1 = $pre_base_fare + $pre_total_distance_amount + $pre_time_fare_amount; //ride amount
                                $pre_adjustment_amount = 0;
                                $pre_promo_code_discount=0;
                                if ($pre_step_1 < $get_vehicle_price_min_fare_amount) {
                                    $pre_adjustment_amount = round(($get_vehicle_price_min_fare_amount - $pre_step_1), 2); //adjust amount
                                }
                                $pre_step_2 = round(($pre_step_1 + $pre_adjustment_amount), 2); //add adjust amount
                                $pre_step_3 = $pre_step_2 - $pre_promo_code_discount; //promo code discount
                                $pre_step_4 = round((($pre_step_3 * $get_tax) / 100), 2); //tax count
                                $pre_step_5 = $pre_step_3 + $pre_step_4; //count ride amount


                                if($pre_step_5 > $avail_wallet_balance ){
                                    return response()->json([
                                        'success' => false,
                                        "errmessage" => 'Sorry, You have not enough balance in wallet',
                                        "message_code" => 9,
                                    ]);
                                }
                            }else{
                                return response()->json([
                                    'success' => false,
                                    "errmessage" => 'Sorry, You have not enough balance in wallet',
                                    "message_code" => 9,
                                ]);
                            }
                        }else{
                            return response()->json([
                                'success' => false,
                                "errmessage" => 'Sorry, You have not enough balance in wallet',
                                "message_code" => 9,
                            ]);
                        }
                    }
                }

                $ride = new TransportRideBook();
                $ride->user_id = $user_id;
//                $ride->area_id = null;
                $ride->service_cat_id = $service_category_id;
                $ride->vehicle_type_id = $get_vehicle_price_id;
                $ride->vehicle_cost_for_km = $get_vehicle_price_cost_for_km;
                $ride->vehicle_cost_for_min = $get_vehicle_price_time_fare;
                $ride->vehicle_type_name = $get_vehicle_price_name;
                $ride->ride_no = $ride->generateRideNo();
                $ride->user_name = $user_details->first_name . ' ' . $user_details->last_name;
                if ($pickup_date_time != Null && $user_ride_type == 1) {
                    $ride->pickup_datetime = date('Y-m-d H:i:s', strtotime($pickup_date_time));
                    $ride->ride_type = 1;
                } else {
                    $ride->pickup_datetime = date('Y-m-d H:i:s');
                }

                $ride->pickup_address = $source_address;
                $ride->pickup_latlong = $source_lat . ',' . $source_long;
                $ride->destination_address = $dest_address;
                $ride->destination_latlong = $dest_lat . ',' . $dest_long;

                $eta_time =  ($eta_time > 0)?($eta_time/60 ):"0";
                $eta_time = ceil($eta_time);

                $eta_distance = round(($eta_distance * 0.001),2);

                $ride->total_distance = $eta_distance;
                $ride->eta = $eta_time;
                //$ride->base_fare = $request['estimate_price'];
                //$ride->tax = $request['tax'];
                //$ride->total_cost = $request['total_price'];
                $ride->payment_type = strtolower(trim($payment_type));
                $ride->payment_status = 0;
                $ride->additional_request =  "";
                $ride->order_from = 1;

                if ($service_category_id == 2) {
                    $ride->handicap =  ($request->get('childseat') =='y')?1:0;
                    $ride->child_seat = ($request->get('handicap') == 'y')?1:0;
                } else {
                    $ride->handicap = 0;
                    $ride->child_seat = 0;
                }
                $ride->status = 0;
                $ride->save();
                if ($service_category_id == 4) {
                    $get_weight_details = TransportWeightList::query()->where('id','=',$get_vehicle_weight_limit_id)->first();
                    $weight_start_limit = 0;
                    $weight_close_limit = 0;
                    if($get_weight_details != Null){
                        $weight_start_limit = $get_weight_details->start_limit;
                        $weight_close_limit = $get_weight_details->close_limit;
                    }
                    $category_id = ($category_id>0)?$category_id:0;
                    $transport_category_list =TransportCategoryList::query()->where('id','=',$category_id)->first();
                    $category_name ="";
                    if($transport_category_list != Null){
                        $category_name = $transport_category_list->category_name;
                    }

                    $courier_details = new TransportCourierDetails();
                    $courier_details->ride_id = $ride->id;
                    $courier_details->weight_id = $get_vehicle_weight_limit_id;
                    $courier_details->category_id =$transport_category_list->id;
                    $courier_details->category_name = $category_name;
                    $courier_details->weight_start_limit = $weight_start_limit;
                    $courier_details->weight_close_limit = $weight_close_limit;

                    $courier_details->sender_name = ($request->get('sender_name') != "")?$request->get('sender_name'):"";
                    $courier_details->sender_contact_number = ($request->get('sender_contact_number') !="")?$request->get('sender_contact_number'):0;
                    $courier_details->sender_landmark = ($request->get('sender_landmark') !="")?$request->get('sender_landmark'):0;
                    $courier_details->house_name = ($request->get('house_name'))?$request->get('house_name'):"";

                    $courier_details->recipient_name = ($request->get('recipient_name') != "")?$request->get('recipient_name'):"";
                    $courier_details->recipient_contact_number = ($request->get('recipient_contact_number') !="")?$request->get('recipient_contact_number'):0;
                    $courier_details->recipient_landmark = ($request->get('recipient_landmark') !="")?$request->get('recipient_landmark'):"";


                    $courier_details->goods_height = ($request['goods_height']!="")?$request['goods_height']:0;
                    $courier_details->goods_width = ($request['goods_width']!="")?$request['goods_width']:0;
                    $courier_details->goods_weight = ($request['goods_weight']!="")?round($request['goods_weight'], 2):0;
                    $courier_details->description = $request->get('description');
                    if ($request->get('delivery_instruction') != Null) {
                        $courier_details->delivery_instruction = $request->get('delivery_instruction');
                    }
                    $courier_details->save();

                    if($payment_type == 3){
                        $add_balance = new UserWalletTransaction();
                        $add_balance->user_id = $user_id;
                        $add_balance->transaction_type = 2;
                        $add_balance->order_no = $ride->ride_no;
                        $add_balance->amount = $pre_step_5;
                        $add_balance->subject = "Paid For - Courier Payment - Order #".$ride->ride_no ;
                        $add_balance->subject_code = 7;
                        $add_balance->remaining_balance = round($avail_wallet_balance - $pre_step_5, 2);
                        $add_balance->save();
                        //update payment status
                        $ride->payment_status = 1;

                    }else if($payment_type == 2){
                        $ride->payment_status = 1;
                    }else{
                        $ride->payment_status = 0;
                    }
                    $ride->save();
                }
                //temp add 1 for address count rental
                $address_count = 1;
                if($service_category_id != 4 && $address_count > 2) {
                    //code rental service
                    //                $ride->is_way_point = 1;
                    //                $ride->way_point_status = 0;
                    //                $ride->save();
                    //
                    //                $ride_way_point = new UserRideWayPoint();
                    //                $ride_way_point->ride_id = $ride->id;
                    //                $ride_way_point->way_point_1 = $new_address_list[1]['address'];
                    //                $ride_way_point->lat_long_1 = $new_address_list[1]['address_lat'] . ',' . $new_address_list[1]['address_long'];
                    //                if ($address_count > 3) {
                    //                    $ride_way_point->way_point_2 = $new_address_list[2]['address'];
                    //                    $ride_way_point->lat_long_2 = $new_address_list[2]['address_lat'] . ',' . $new_address_list[2]['address_long'];
                    //                }
                    //                if ($address_count > 4) {
                    //                    $ride_way_point->way_point_3 = $new_address_list[3]['address'];
                    //                    $ride_way_point->lat_long_3 = $new_address_list[3]['address_lat'] . ',' . $new_address_list[3]['address_long'];
                    //                }
                    //                $ride_way_point->save();
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
                $ride->BookingCountDistanceAmount($ride->total_distance, $get_vehicle_price_cost_for_km, $get_vehicle_price_base_fare, $get_vehicle_price_time_fare, $ride->eta);
                //$ride->BookingCountDistanceAmount($ride->total_distance, $get_vehicle_price_cost_for_km, $get_vehicle_price_base_fare);
                $promo_code_discount = 0;
                if ($promocode != 0) {
                    $get_discount_on_amount = ($ride->base_fare + $ride->total_distance_amount + $ride->time_fare_amount);
                    $min_fare_amount = ($get_vehicle_price_min_fare_amount);
                    $adjustment_amount = 0;
                    if ($get_discount_on_amount < $min_fare_amount) {
                        $adjustment_amount = round(($min_fare_amount - $get_discount_on_amount), 1); //adjust amount
                    }
                    $discount_on_amount = ($get_discount_on_amount + $adjustment_amount);
                    //$discount_on_amount = ($ride->base_fare + $ride->total_distance_amount + $ride->time_fare_amount);
                    $promo_code = $this->userClassapi->checkPromoCodeValid($promocode, $discount_on_amount, $user_details->id, $request['service_category_id']);
                    if ($promo_code != 0) {
                        if ($promo_code != 1) {
                            $get_prmocode = PromocodeDetails::query()->where('id', $promocode)->where('status', 1)->first();
                            if ($get_prmocode != Null) {
                                $promo_code_discount = $promo_code;
                                //$place_order->total_pay = round($place_order->total_pay - $promo_code, 2);
                                $add_promocode_details = new UsedPromocodeDetails();
                                $add_promocode_details->service_cat_id = $service_category_id;
                                $add_promocode_details->user_id = $user_details->id;
                                $add_promocode_details->promocode_id = $get_prmocode->id;
                                $add_promocode_details->promocode_name = $get_prmocode->promo_code;
                                $add_promocode_details->discount_amount = $promo_code;
                                $add_promocode_details->save();

                                //$ride->promo_code = $add_promocode_details->id;
                                $ride->promo_code = $add_promocode_details->id;
                                $ride->save();
                            }
                        }
                    }
                }
                $ride->BookingCostCount($ride->base_fare, $ride->total_distance_amount, $promo_code_discount, $get_tax, $admin_commission, $get_vehicle_price_min_fare_amount, $ride->time_fare_amount);
                //$ride->BookingCostCount($ride->base_fare, $ride->total_distance_amount, $promo_code_discount, $get_tax, $admin_commission, $get_vehicle_price_min_fare_amount);

                if ($user_details->pending_refer_discount > 0) {
                    $user = User::query()->where('id', $user_details->id)->first();
                    if ($user != Null) {
                        $user_refer_history = UserReferHistory::query()->where('user_id', $user_details->id)->where('user_status', 0)->first();
                        $total_price = $ride->total_pay;
                        if ($user_refer_history != Null) {
                            if ($user_refer_history->user_discount_type == 1) {
                                $refer_discount_price = round($total_price - $user_refer_history->user_discount);
                            } else {
                                $refer_discount_price = round((($total_price * $user_refer_history->user_discount) / 100), 1);
                                $refer_discount_price = round($total_price - $refer_discount_price);
                            }
                            if ($refer_discount_price < 0) {
                                $refer_discount_price = 0;
                            }
                            $ride->total_pay = $refer_discount_price;
                            $ride->refer_discount = round($user_refer_history->user_discount, 1);
                            $ride->save();
                            $user_refer_history->user_status = 1;
                            $user_refer_history->save();
                            $user->pending_refer_discount = $user->pending_refer_discount - 1;
                            $user->save();
                        } else {
                            $user_refer_history = UserReferHistory::query()->where('refer_id', $user_details->id)->where('refer_status', 0)->first();
                            if ($user_refer_history != Null) {
                                if ($user_refer_history->refer_discount_type == 1) {
                                    $refer_discount_price = round($total_price - $user_refer_history->refer_discount);
                                } else {
                                    $refer_discount_price = round((($total_price * $user_refer_history->refer_discount) / 100), 1);
                                    $refer_discount_price = round($total_price - $refer_discount_price);
                                }
                                if ($refer_discount_price < 0) {
                                    $refer_discount_price = 0;
                                }
                                $ride->total_pay = $refer_discount_price;
                                $ride->refer_discount = round($user_refer_history->user_discount, 1);
                                $ride->save();
                                $user_refer_history->refer_status = 1;
                                $user_refer_history->save();
                                $user->pending_refer_discount = $user->pending_refer_discount - 1;
                                $user->save();
                            }
                        }
                    }
                }

                $go_prime_discount = 0;

                $service_setting = ServiceSettings::query()->where('service_cat_id', $service_category_id)->first();
                if ($service_setting != Null) {
                    $timeout = $service_setting->provider_accept_timeout;
                } else {
                    $timeout = 60;
                }

                $ride_id = $ride->id;
                $service_category_id = $ride->service_cat_id;
                $this->notificationClass->driverRequestNotification($service_category_id, $ride_id);

                $general_settings = request()->get("general_settings");
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
                    "message" => "Ride Booking Successfully!",
                    'success' => true,
                    "message_code" => 1,
                    "ride_id" => $ride->id,
//                    "booking_no" => $ride->ride_no,
                    "accept_time_out" => round($timeout),
                    "redirect_path"=>route('get:user_orders_all',['transport','all'])
                ]);
            }else{
                $errmessage =  "Service Category Not Found!";
            }
        }

    }

    public function postRideCancel(Request $request){
        $user_id = Auth::guard("user")->user()->id;
        $ride_id = $request->get('ride_id');
        $user_details =  User::query()->where('id',$user_id)->first();
        if($user_details == Null){
            return response()->json([
                'success' => false,
                "message" => "User Not Found!",
                "message_code" => 9,
            ]);
        }
        $get_ride_data =  TransportRideBook::query()->where('user_id',$user_id)->where('id',$ride_id)->first();
        if($get_ride_data != Null)
        {
            $ride_cancel = $this->userClassapi->transportRideBookingCancel($ride_id, "user", "");
            $ride_cancel_data =  $ride_cancel->getContent();
            $ride_cancel_data = json_decode($ride_cancel_data, true);
            if($ride_cancel_data['status'] == 1){
                Session::flash('success', "Cancel Ride Successfully!");
                return response()->json([
                    'success' => true,
                    "message" => "Cancel Ride Successfully!",
                    "redirect_path"=>route('get:user_orders_all',['transport','all']),
                    "message_code" => 9,
                ]);
            }else{
                $errorMsg = $ride_cancel_data['message'];
                return response()->json([
                    'success' => false,
                    "message" => $errorMsg,
                    "message_code" => 9,
                ]);
            }
        }else{
            return response()->json([
                'success' => false,
                "message" => "Ride Not Found",
                "message_code" => 9,
            ]);
        }


    }

    public function postCheckRideStatus(Request $request){
        $user_id = Auth::guard("user")->user()->id;
        $ride_id = $request->get('ride_id');

        $user_details =  User::query()->where('id',$user_id)->first();
        if($user_details == Null){
            return response()->json([
                'success' => false,
                "message" => "User Not Found!",
                "message_code" => 9,
            ]);
        }
        $get_ride_data =  TransportRideBook::query()->where('user_id',$user_id)->where('id',$ride_id)->first();
        if($get_ride_data != Null)
        {
            $ride_status = $get_ride_data->status;
            if($ride_status > 0){
                Session::flash('success', "Successfully Ride Booked!");
                return response()->json([
                    'success' => true,
                    "message" => "Successfully Ride Booked!",
                    "redirect_path"=>route('get:user_orders_all',['transport','all']),
                    "message_code" => 9,
                ]);
            }else{
                return response()->json([
                    'success' => false,
                    "message" => $ride_status,
                    "message_code" => 9,
                ]);
            }
        }else{
            return response()->json([
                'success' => false,
                "message" => "Ride Not Found",
                "message_code" => 9,
            ]);
        }
    }

    public function getRideDetails(Request $request,$ride_id){

        $user_id =  Auth::guard("user")->user()->id;

        $ride_Details = TransportRideBook::query()->select(
            'user_ride_booking.id', 'user_ride_booking.ride_no',
            'user_ride_booking.service_cat_id',
            'service_category.slug as slug',
            'user_ride_booking.user_id', 'user_ride_booking.driver_id',
            'user_ride_booking.user_name', 'user_ride_booking.driver_name',
            'user_ride_booking.pickup_datetime', 'user_ride_booking.destination_datetime',
            'user_ride_booking.pickup_address', 'user_ride_booking.destination_address',
//            'user_ride_booking.vehicle_type_name',
            'user_ride_booking.eta',
            'user_ride_booking.vehicle_cost_for_km',
            'user_ride_booking.base_fare',
            'user_ride_booking.total_distance_amount',
            'user_ride_booking.time_fare_amount',
            'user_ride_booking.ride_type',
            'user_ride_booking.total_distance',
            'user_ride_booking.adjustment_amount',
            'user_ride_booking.tax',
            'user_ride_booking.total_pay', 'user_ride_booking.payment_type', 'user_ride_booking.payment_status',
            'user_ride_booking.promo_code',
            'user_ride_booking.status',
            'user_ride_booking.created_at',
            'transport_vehicle_type.name as vehicle_type_name'
        )
            ->join('transport_vehicle_type', 'transport_vehicle_type.id', '=', 'user_ride_booking.vehicle_type_id')
            ->join('service_category', 'service_category.id', '=', 'user_ride_booking.service_cat_id')
            ->where('user_ride_booking.id', $ride_id)
            ->where('user_ride_booking.user_id', $user_id)
            ->first();

        if ($ride_Details == Null) {
            return redirect()->back();
        }

        $user_details = User::query()->select('id', 'contact_number','country_code', 'email')->where('id', $ride_Details->user_id)->first();

        $driver_details = TransportDriverDetails::select('transport_driver_details.id', 'providers.email', 'providers.contact_number','providers.country_code')
            ->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
            ->join('providers', 'providers.id', 'provider_services.provider_id')
            ->where('transport_driver_details.id', $ride_Details->driver_id)
            ->first();

        $total = round($ride_Details->total_distance_amount + $ride_Details->time_fare_amount + $ride_Details->adjustment_amount + $ride_Details->base_fare + $ride_Details->tax, 1);
        $discount = round(0, 1);
        $grand_total = round($ride_Details->total_pay - $discount, 1);
        $user_open_tab = "ride-detail";
        $title_code = "Ride Details";

        $slug = $ride_Details->slug;

        if (in_array($ride_Details->service_cat_id, $this->courier_service_id_array)) {
            //get courier details
            $user_courier_details = TransportCourierDetails::query()->select('category_name','weight_start_limit','weight_close_limit','estimate_price',
                                    'delivery_instruction','sender_name','sender_contact_number','sender_landmark','house_name as sender_house_name',
                                    'recipient_name','recipient_contact_number','recipient_landmark')
                                    ->where('ride_id','=',$ride_id)
                                    ->first();


            $user_open_tab = "courier-detail";
            $title_code = "Courier Details";
            $view = view('front.pages.user_details_tabview', compact('slug', 'title_code','user_open_tab','ride_Details','user_details', 'driver_details', 'user_courier_details','grand_total', 'total', 'discount'));
        } else {
            $view = view('front.pages.user_details_tabview', compact('slug', 'title_code','user_open_tab','ride_Details', 'user_details', 'driver_details', 'grand_total', 'total', 'discount'));
        }
        return $view;
    }

    public function getTransportUpdateRideStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
                "id" => "required|numeric",
                "update_status" => "required|numeric|in:4,9",
                "orderslug" => "required"
            ]
        );
        if ($validator->fails()) {
            return response()->json([
                "status" => false,
                "message" => $validator->errors()->first()
            ]);
        }
        $id = $request->get('id');
        $update_status = $request->get('update_status');
        $orderslug = $request->get('orderslug');
        if ($update_status == 4) {
            $validator = Validator::make($request->all(), [
                    "reason" => "required"
                ]
            );
            if ($validator->fails()) {
                return response()->json([
                    "status" => false,
                    "message" => $validator->errors()->first()
                ]);
            }
        }
        if($orderslug == "bike-ride" || $orderslug == "taxi-ride" || $orderslug == "courier-service"){
            $transport_ride = TransportRideBook::query()->where('id', $id)->first();
        }elseif($orderslug == "bike-rental" || $orderslug == "taxi-rental"){
            $transport_ride = TransportRideBook::query()->where('id', $id)->first();
        }else{
            $transport_ride = Null;
        }

        if ($transport_ride == Null) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }
//        $user_details = User::select('device_token', 'login_device')->where('id', $transport_ride->user_id)->first();
        $user_details = User::query()->where('id', $transport_ride->user_id)->first();
        if ($transport_ride->status == 4) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found'
            ]);
        }
        switch ($update_status) {
            case 4:
                if ($transport_ride->status != 4 && $transport_ride->status != 9 && $transport_ride->status != 10) {
                    $transport_ride->status = $update_status;
                    $transport_ride->cancel_by = "admin";
                    $transport_ride->cancel_reason = $request->get('reason');
                    $transport_ride->save();
                    if ($user_details != Null) {
                        $this->notificationClass->userTransportNotification($transport_ride->id, $user_details->device_token, $transport_ride->status, $user_details->login_device,$user_details->language);
                    }
                    if ($transport_ride->driver_id != Null) {
                        ProviderUserRunningService::query()->where('service_cat_id', $transport_ride->service_cat_id)->where('user_id', $transport_ride->user_id)->where('booking_id', $transport_ride->id)->delete();
                        UserRunningRide::query()->where('user_id', $transport_ride->user_id)->where('service_cat_id', $transport_ride->service_cat_id)->where('booking_id', $transport_ride->id)->delete();
                        $driver_details = Provider::query()->select('providers.device_token', 'provider_services.id', 'providers.name', 'providers.login_device', 'providers.language')
                            ->join('provider_services', 'provider_services.provider_id', '=', 'providers.id')
                            ->join('transport_driver_details', 'transport_driver_details.provider_service_id', '=', 'provider_services.id')
                            ->where('transport_driver_details.id', $transport_ride->driver_id)->first();

                        if ($driver_details != Null) {
                            $cancel_by = "user";
                            $this->notificationClass->driverCancelNotification($driver_details->device_token, $transport_ride->service_cat_id, $driver_details->id, $driver_details->login_device,$driver_details->language,$cancel_by);
                        }
                    }
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Data not found'
                    ]);
                }
                break;
//            case 9:
//                if ($transport_ride->status > 0) {
//                    if ($transport_ride->status != 4 && $transport_ride->status != 9 && $transport_ride->status != 10) {
//                        if ($transport_ride->driver_id != Null) {
//                            $driver_details = TransportDriverDetails::query()->where('id', $transport_ride->driver_id)->first();
//                            if ($driver_details == Null) {
//                                return response()->json([
//                                    'success' => false,
//                                    'message' => 'Data not found'
//                                ]);
//                            }
//
//                            $update_driver_status = ProviderServices::query()->where('id', $driver_details->provider_service_id)->first();
//                            if ($update_driver_status == Null) {
//                                return response()->json([
//                                    'success' => false,
//                                    'message' => 'Data not found'
//                                ]);
//                            }
//
//                            $driver = Provider::query()->where('id', $update_driver_status->provider_id)->first();
//                            if ($update_driver_status == Null) {
//                                return response()->json([
//                                    'success' => false,
//                                    'message' => 'Data not found'
//                                ]);
//                            }
//
////                            $update_driver_status->current_status = 1;
////                            $update_driver_status->save();
////                            ProviderUserRunningService::query()->where('service_cat_id', $transport_ride->service_cat_id)->where('user_id', $transport_ride->user_id)->where('booking_id', $transport_ride->id)->delete();
//                            $driver_details = Provider::query()->select('providers.email', 'providers.device_token', 'provider_services.id', 'providers.name', 'providers.login_device')
//                                ->join('provider_services', 'provider_services.provider_id', '=', 'providers.id')
//                                ->join('transport_driver_details', 'transport_driver_details.provider_service_id', '=', 'provider_services.id')
//                                ->where('transport_driver_details.id', $transport_ride->driver_id)->first();
//                            if ($driver_details != Null) {
//                                $this->notificationClass->driverCompletedNotification($driver_details->device_token, $transport_ride->service_cat_id, $driver_details->id, $driver_details->login_device);
//                            }
//                        }
////                        if ($request->get('payment_type') == 3) {
////                            if ($transport_ride->payment_status == 0) {
////                                $wallet_balance = UserWalletTransaction::where('user_id', $request->get('user_id'))->orderBy('id', 'desc')->first();
////                                if ($wallet_balance != Null) {
////                                    $balance = $wallet_balance->remaining_balance;
////                                } else {
////                                    $balance = 0;
////                                }
////                                if ($transport_ride->total_pay > $balance) {
////                                    return response()->json([
////                                        'success' => false,
////                                        'message' => "You can't pay ride amount through Wallet because your wallet balance is insufficient."
////                                    ]);
////                                }
////                                $add_balance = new UserWalletTransaction();
////                                $add_balance->user_id = $request->get('user_id');
////                                $add_balance->transaction_type = 2;
////                                $add_balance->amount = $transport_ride->total_pay;
////                                $add_balance->subject = "Paid To Ride - Order #" . $transport_ride->ride_no;
////                                $add_balance->remaining_balance = round($balance - $transport_ride->total_pay, 2);
////                                $add_balance->save();
////                            }
////                        }
//                        $transport_ride->status = 7;
////                        $transport_ride->payment_status = 1;
//                        $transport_ride->completed_by = 1;
//                        $transport_ride->save();
//                        if ($user_details != Null) {
//                            $this->notificationClass->userTransportNotification($transport_ride->id, $user_details->device_token, $transport_ride->status, $user_details->login_device);
//                        }
//                    } else {
//                        return response()->json([
//                            'success' => false,
//                            'message' => 'Data not found'
//                        ]);
//                    }
//                } else {
//                    return response()->json([
//                        'success' => false,
//                        'message' => 'Data not found'
//                    ]);
//                }
//                break;
            default:
                return response()->json([
                    'success' => false,
                    'message' => 'Data not found'
                ]);
        }
        return response()->json([
            'success' => true
        ]);
    }


}
