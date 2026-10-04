<?php

namespace App\Http\Controllers\Api\Transport;

use App\Classes\AdminClass;
use App\Classes\DriverClassApi;
use App\Classes\NotificationClass;
use App\model\Admin;
use App\model\AppVersionSetting;
use App\model\DriverNewRequestNotification;
use App\model\GeneralSettings;
use App\model\PromocodeDetails;
use App\model\Provider;
use App\model\ProviderBankDetails;
use App\model\ProviderDocuments;
use App\model\ProviderServices;
use App\model\ProviderUserRunningService;
use App\model\RequiredDocuments;
use App\model\ServiceCategory;
use App\model\ServiceSettings;
use App\model\StoreDetails;
use App\model\TransportCourierDetails;
use App\model\TransportDriverDetails;
use App\model\TransportRatings;
use App\model\TransportRentalRideBooking;
use App\model\TransportRideBook;
use App\model\TransportRideDriverToken;
use App\model\TransportRideRoute;
use App\model\TransportVehicleType;
use App\model\UsedPromocodeDetails;
use App\model\User;
use App\model\UserProductBooking;
use App\model\UserProductBookingQuantity;
use App\model\UserProductBookingStatus;
use App\model\UserRatings;
use App\model\UserRideWayPoint;
use App\model\UserRunningRide;
use App\model\WorldCurrency;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

class DriverController extends Controller
{
//        json response status [
//            0 => false,
//            1 => true,
//            2 => registration pending,
//            3 => app user blocked,
//            4 => app user access token not match,
//            5 => app user not found,
//            6 => user service not register,
//            7 => user service rejected,
//            8 => user service register status pending
//          ]

    //ApiLogDetail logger type => 0:user,1:store,2:driver,3:provider
    private $bike_ride;
    private $taxi_ride;
    private $courier_service;
    private $driverClassApi;
    private $notificationClass;
    private $transport_service_id_array;
    private $transport_rental_service_id_array = [31,32];
    private $courier_service_id_array;
    private $delivery_service_id_array;
    private $adminClass;

    public function __construct(DriverClassApi $driverClassApi, NotificationClass $notificationClass, AdminClass $adminClass)
    {
        $this->bike_ride = 1;
        $this->taxi_ride = 2;
        $this->courier_service = 4;
        $this->driverClassApi = $driverClassApi;
        $this->notificationClass = $notificationClass;
        $this->transport_service_id_array = [1, 2];
        $this->courier_service_id_array = [4];
        $this->delivery_service_id_array = [5, 6, 7, 8, 9, 10];
        $this->adminClass= $adminClass;
    }

    public function postTransportDriverServiceRegister(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "driver_id" => "required|numeric",
            "access_token" => "required",
            "service_category_id" => "required|numeric|in:1,2,4",
            "current_lat" => "required",
            "current_long" => "required"
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }

        $driver_id = $request->get('driver_id');
        $driver_details = Provider::query()->where('id', $driver_id)->first();
        if ($driver_details != Null) {
            if ($driver_details->status == 2) {
                return response()->json([
                    "status" => 3,
//                    "message" => 'Your account is currently blocked, so not authorised to allow any activity!',
                    "message" => __('driver_messages.3'),
                    "message_code" => 3,
                ]);
            }
            if ($driver_details->access_token != $request->get('access_token')) {
                return response()->json([
                    'status' => 4,
//                    'message' => "Access Token Not Match!",
                    'message' => __('driver_messages.4'),
                    "message_code" => 4,
                ]);
            }
        } else {
            return response()->json([
                "status" => 5,
//                "message" => 'driver not found!',
                "message" => __('driver_messages.5'),
                "message_code" => 5,
            ]);
        }

        $req_service_category_id = $request->get('service_category_id');

        $validator = Validator::make($request->all(), [
            "vehicle_type_id" => "required|numeric",
            "manufacture_name" => "required",
            "model_name" => "required",
            "model_year" => "required",
            "vehicle_plat_no" => "required",
            //"document_list" => "required"
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }
        if ($req_service_category_id == $this->bike_ride) {
            $validator = Validator::make($request->all(), [
                "sub_service_list" => "required",
                "is_rental" => "required|in:0,1"
            ]);
            if ($validator->fails()) {
                return response()->json([
                    "status" => 0,
                    "message" => $validator->errors()->first(),
                    "message_code" => 9,
                ]);
            }
            //service json decode
            $service_list = json_decode($request->get('sub_service_list'), true);
            if (!array_key_exists("bike_ride", $service_list) || !array_key_exists("store_delivery", $service_list) || !array_key_exists("courier_service", $service_list)) {
                return response()->json([
                    "status" => 0,
//                    "message" => "something went to wrong!",
                    "message" => __('driver_messages.9'),
                    "message_code" => 9,
                ]);
            }
            $bike_rider_status = $service_list['bike_ride'] == 1 ? 1 : 0;
            $store_delivery_status = $service_list['store_delivery'] == 1 ? 1 : 0;
            $courier_service_status = $service_list['courier_service'] == 1 ? 1 : 0;
        }
        elseif ($req_service_category_id == $this->taxi_ride) {
            $validator = Validator::make($request->all(), [
                "no_of_seat" => "required|numeric",
                "vehicle_color" => "nullable",
                "handicap_accessibility" => "required|numeric|in:0,1",
                "child_seat_accessibility" => "required|numeric|in:0,1",
                "sub_service_list" => "required",
                "is_rental" => "required|in:0,1"
            ]);
            if ($validator->fails()) {
                return response()->json([
                    "status" => 0,
                    "message" => $validator->errors()->first(),
                    "message_code" => 9,
                ]);
            }
            //service json decode
            $service_list = json_decode($request->get('sub_service_list'), true);
            if (!array_key_exists("bike_ride", $service_list) || !array_key_exists("store_delivery", $service_list) || !array_key_exists("courier_service", $service_list)) {
                return response()->json([
                    "status" => 0,
//                    "message" => "something went to wrong!",
                    "message" => __('driver_messages.9'),
                    "message_code" => 9,
                ]);
            }
            $bike_rider_status = $service_list['bike_ride'] == 1 ? 1 : 0;
            $store_delivery_status = $service_list['store_delivery'] == 1 ? 1 : 0;
            $courier_service_status = $service_list['courier_service'] == 1 ? 1 : 0;
        }
        elseif ($req_service_category_id == $this->courier_service) {
            $validator = Validator::make($request->all(), [
                    "support_weight" => "nullable"
                ]
            );
            if ($validator->fails()) {
                return response()->json([
                    "status" => 0,
                    "message" => $validator->errors()->first(),
                    "message_code" => 9,
                ]);
            }
        }
        else {
            return response()->json([
                "status" => 0,
//                "message" => "something went to wrong!",
                "message" => __('driver_messages.9'),
                "message_code" => 9,
            ]);
        }

        $check_driver_bike_approved_status = ProviderServices::query()->where('provider_id', $driver_id)->where('service_cat_id', $this->bike_ride)->first();
        $check_driver_taxi_approved_status = ProviderServices::query()->where('provider_id', $driver_id)->where('service_cat_id', $this->taxi_ride)->first();
        $check_driver_courier_approved_status = ProviderServices::query()->where('provider_id', $driver_id)->where('service_cat_id', $this->courier_service)->first();

        if ($check_driver_bike_approved_status != Null || $check_driver_taxi_approved_status != Null || $check_driver_courier_approved_status != Null) {
            return response()->json([
                "status" => 0,
//                "message" => "drive already register service!",
                "message" => __('driver_messages.9'),
                "message_code" => 9,
            ]);
        }

        $add_driver_service = new ProviderServices();
        $add_driver_service->provider_id = $driver_id;
        $add_driver_service->service_cat_id = $request->get('service_category_id');
        $add_driver_service->current_status = 1;
        //$add_driver_service->status = 0;
        $add_driver_service->status = 1;
        $add_driver_service->save();

        $add_driver_vehicle_details = new TransportDriverDetails();
        $add_driver_vehicle_details->provider_service_id = $add_driver_service->id;
        $add_driver_vehicle_details->vehicle_type_id = $request->get('vehicle_type_id');
        $add_driver_vehicle_details->current_lat = $request->get('current_lat') != Null ? $request->get('current_lat') : Null;
        $add_driver_vehicle_details->current_long = $request->get('current_long') != Null ? $request->get('current_long') : Null;
        $add_driver_vehicle_details->vehicle_company = $request->get('manufacture_name');
        $add_driver_vehicle_details->plat_no = $request->get('vehicle_plat_no');
        $add_driver_vehicle_details->model_name = $request->get('model_name');
        $add_driver_vehicle_details->model_year = $request->get('model_year');
        if ($request->get('service_category_id') == $this->bike_ride) {
            //bike ride or store delivery or courier boy
            $add_driver_vehicle_details->driver_service = $bike_rider_status;
            $add_driver_vehicle_details->delivery_boy = $store_delivery_status;
            $add_driver_vehicle_details->courier_boy = $courier_service_status;
            $add_driver_vehicle_details->no_of_seat = 1;
            $add_driver_vehicle_details->rental_service = $request->get('is_rental');
            $add_driver_vehicle_details->save();
        }
        elseif ($request->get('service_category_id') == $this->taxi_ride) {
            //taxi ride
            //$add_driver_vehicle_details->driver_service = 1;
            //$add_driver_vehicle_details->delivery_boy = 0;
            //$add_driver_vehicle_details->courier_boy = 0;

            $add_driver_vehicle_details->driver_service = $bike_rider_status;
            $add_driver_vehicle_details->delivery_boy = $store_delivery_status;
            $add_driver_vehicle_details->courier_boy = $courier_service_status;

            if ($request->get('vehicle_color') != Null) {
                $add_driver_vehicle_details->vehicle_color = $request->get('vehicle_color');
            }
            $add_driver_vehicle_details->no_of_seat = $request->get('no_of_seat');
            $add_driver_vehicle_details->handicap = $request->get('handicap_accessibility');
            $add_driver_vehicle_details->child_seat = $request->get('child_seat_accessibility');
            $add_driver_vehicle_details->rental_service = $request->get('is_rental');
            $add_driver_vehicle_details->save();
        }
        elseif ($request->get('service_category_id') == 4) {
            //courier service
            $add_driver_vehicle_details->driver_service = 0;
            $add_driver_vehicle_details->delivery_boy = 0;
            $add_driver_vehicle_details->courier_boy = 1;
//            $add_driver_vehicle_details->support_weight = $request->get('support_weight');
            $add_driver_vehicle_details->save();
        }
        else {
            $add_driver_vehicle_details->save();
            TransportDriverDetails::query()->where('id', $add_driver_vehicle_details->id)->delete();
            ProviderServices::query()->where('id', $add_driver_service->id)->delete();
            return response()->json([
                "status" => 0,
//                "message" => "something want to wrong!",
                "message" => __('driver_messages.9'),
                "message_code" => 9,
            ]);
        }


        //document upload pending
        //$driver_details->status = 0;
        //$driver_details->save();

//        $get_documents = RequiredDocuments::query()->where('service_cat_id', $request->get('service_category_id'))->where('status', 1)->get();
//        if ($get_documents->isEmpty()) {
////            $add_driver_vehicle_details->save();
//            $driver_check = Provider::query()->where('id', $driver_details->id)->first();
//            if ($driver_check == Null) {
//                try {
//                    TransportDriverDetails::query()->where('id', $add_driver_vehicle_details->id)->delete();
//                    ProviderServices::query()->where('id', $add_driver_service->id)->delete();
//                } catch (\Exception $e) {
//                    return response()->json([
//                        "status" => 0,
//                        //"message" => "something want to wrong!",
//                        "message" => __('driver_messages.9'),
//                        "message_code" => 9,
//                    ]);
//                }
//                return response()->json([
//                    "status" => 0,
//                    //"message" => "something want to wrong!",
//                    "message" => __('driver_messages.9'),
//                    "message_code" => 9,
//                ]);
//            }
////            $driver_details->status = 0;
////            $driver_details->save();
////            return response()->json([
////                "status" => 4,
////                //"message" => "your vehicle and document details submitted successfully!",
////                "message" => __('driver_messages.77'),
////                "message_code" => 77,
////            ]);
//        }
//        $validator = Validator::make($request->all(), [
//            "document_id_list" => "required",
//            "document_list" => "required",
//        ]);
//        if ($validator->fails()) {
//            TransportDriverDetails::query()->where('id', $add_driver_vehicle_details->id)->delete();
//            ProviderServices::query()->where('id', $add_driver_service->id)->delete();
//            return response()->json([
//                "status" => 0,
//                "message" => $validator->errors()->first(),
//                "message_code" => 9,
//            ]);
//        }
//        $count_required_documents = count($get_documents);
//        $store_images = [];
//        if ($request->hasfile('document_list')) {
//            foreach ($request->file('document_list') as $key => $image) {
//                $destinationPath = public_path('/assets/images/provider-documents/');
////                $file = $request->file('document_list');
//                $img = Image::make($image->getRealPath());
//                $img->orientate();
//                $file_new = rand(1, 9) . date('siHYdm') . rand(1, 9) . '.' . $image->getClientOriginalExtension();
//                $img->resize(500, 500, function ($constraint) {
//                    $constraint->aspectRatio();
//                })->save($destinationPath . $file_new);
//
////                $name = rand(1, 9) . date('ymdhis') . rand(1, 9) . '.' . $image->getClientOriginalExtension();
////                $image->move(public_path() . '/assets/images/provider-documents/', $name);
//                $store_images[] = $file_new;
//            }
//        }
//        $count_doc_images = count($store_images);
//        $document_id = array_map('trim', explode(",", $request->get('document_id_list')));
//        $count_doc_id = count($document_id);
//        if ($count_doc_images == $count_doc_id) {
//            if ($count_required_documents <= $count_doc_id) {
//                $add_driver_vehicle_details->save();
//                $save_document = [];
//                foreach ($get_documents as $key => $document) {
//
//                    if (in_array($document->id, $document_id)) {
//                        $key_doc_id = array_search($document->id, $document_id);
////                        try {
////                            $check_documents = ProviderDocuments::where('req_document_id', $document->id)->where('provider_service_id', $add_driver_service->id)->first();
////                            if ($check_documents == Null) {
////                                $check_documents = new ProviderDocuments();
////                                $check_documents->provider_service_id = $add_driver_service->id;
////                                $check_documents->req_document_id = $document->id;
////                            }
////                        } catch (\Exception $e) {
////
////                        }
//                        if ($key != 1) {
//                            $check_documents = ProviderDocuments::query()->where('req_document_id', $document->id)->where('provider_service_id', $add_driver_service->id)->first();
//                            if ($check_documents == Null) {
//                                $check_documents = new ProviderDocuments();
//                            }
//                        } else {
//                            $check_documents = new ProviderDocuments();
//                        }
//                        $check_documents->provider_service_id = $add_driver_service->id;
//                        $check_documents->req_document_id = $document->id;
//                        $check_documents->document_file = $store_images[$key_doc_id];
//                        $check_documents->status = 0;
//                        $check_documents->save();
//                        $save_document[] = $store_images[$key_doc_id];
//                    }
//                }
//                foreach ($store_images as $store_image) {
//                    if (!in_array($store_image, $save_document)) {
//                        if (\File::exists(public_path('/assets/images/provider-documents/' . $store_image))) {
//                            \File::delete(public_path('/assets/images/provider-documents/' . $store_image));
//                        }
//                    }
//                }
//                $check_driver_details = TransportDriverDetails::query()->where('provider_service_id', $add_driver_service->id)->first();
//                if ($check_driver_details == Null) {
//                    try {
//                        TransportDriverDetails::query()->where('id', $add_driver_vehicle_details->id)->delete();
//                        ProviderServices::query()->where('id', $add_driver_service->id)->delete();
//                        foreach ($get_documents as $document) {
//                            if (\File::exists(public_path('/assets/images/provider-documents/' . $document->document_file))) {
//                                \File::delete(public_path('/assets/images/provider-documents/' . $document->document_file));
//                            }
//                            $document->delete();
//                        }
//                        foreach ($store_images as $store_image) {
//                            if (\File::exists(public_path('/assets/images/provider-documents/' . $store_image))) {
//                                \File::delete(public_path('/assets/images/provider-documents/' . $store_image));
//                            }
//                        }
//                    } catch (\Exception $e) {
//                        return response()->json([
//                            "status" => 0,
//                            //"message" => "something want to wrong!",
//                            "message" => __('driver_messages.9'),
//                            "message_code" => 9,
//                        ]);
//                    }
//                    return response()->json([
//                        "status" => 0,
//                        //"message" => "something want to wrong!",
//                        "message" => __('driver_messages.9'),
//                        "message_code" => 9,
//                    ]);
//                }
//            } else {
//                TransportDriverDetails::query()->where('id', $add_driver_vehicle_details->id)->delete();
//                ProviderServices::query()->where('id', $add_driver_service->id)->delete();
//
//                foreach ($store_images as $store_image) {
//                    if (\File::exists(public_path('/assets/images/provider-documents/' . $store_image))) {
//                        \File::delete(public_path('/assets/images/provider-documents/' . $store_image));
//                    }
//                }
//                return response()->json([
//                    "status" => 0,
//                    //"message" => "please upload all required documents!",
//                    "message" => __('driver_messages.78'),
//                    "message_code" => 78,
//                ]);
//            }
//        }
//        else {
//            TransportDriverDetails::query()->where('id', $add_driver_vehicle_details->id)->delete();
//            ProviderServices::query()->where('id', $add_driver_service->id)->delete();
//            foreach ($store_images as $store_image) {
//                if (\File::exists(public_path('/assets/images/provider-documents/' . $store_image))) {
//                    \File::delete(public_path('/assets/images/provider-documents/' . $store_image));
//                }
//            }
//            return response()->json([
//                "status" => 0,
//                //"message" => "document upload images list not match",
//                "message" => __('driver_messages.9'),
//                "message_code" => 9,
//            ]);
//        }

        $driver_details->status = 1;
        $driver_details->save();

        $vehicle_icon_url = url('/assets/images/service-category/transport-service-type');
        $driver_vehicle_details = TransportDriverDetails::query()->select(
            'provider_services.current_status as driver_current_status',
            'transport_driver_details.driver_service',
            'transport_driver_details.delivery_boy',
            'transport_driver_details.courier_boy',
            'transport_driver_details.rental_service',
            'provider_services.service_cat_id',
            'transport_vehicle_type.name as vehicle_type_name',
            DB::raw("(CASE WHEN transport_vehicle_type.icon_name!='' THEN concat('$vehicle_icon_url','/',transport_vehicle_type.icon_name) ELSE '' END) as vehicle_type_icon"))
            ->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
            ->join('transport_vehicle_type', 'transport_vehicle_type.id', '=', 'transport_driver_details.vehicle_type_id')
            //->join('providers', 'providers.id', '=', 'provider_services.provider_id')
            ->where('transport_driver_details.provider_service_id', $add_driver_service->id)
            ->first();

        if ($driver_vehicle_details == Null) {
            TransportDriverDetails::query()->where('id', $add_driver_vehicle_details->id)->delete();
            ProviderServices::query()->where('id', $add_driver_service->id)->delete();
            return response()->json([
                "status" => 0,
//                "message" => "driver details not found!",
                "message" => __('driver_messages.15'),
                "message_code" => 15,
            ]);
        }

        $driver_service = $driver_vehicle_details->driver_service;
        $delivery_boy = $driver_vehicle_details->delivery_boy;
        $courier_boy = $driver_vehicle_details->courier_boy;
        $service_lang_converted = $this->notificationClass->serviceLangConvert();
        $lang = $driver_details->language;

        if($lang !="en" && $lang != "" && $lang != "Null" ){
            $lang = $lang."_";
        }else{
            $lang ="";
        }

        $ride_service =  (config('global.lang_constant.RIDE_SERVICE.'.$lang.'value') !="")?config('global.lang_constant.RIDE_SERVICE.'.$lang.'value') ." ":config('global.lang_constant.RIDE_SERVICE.value')." ";
        $courier_delivery = (config('global.lang_constant.COURIER_DELIVERY.'.$lang.'value') !="")?config('global.lang_constant.COURIER_DELIVERY.'.$lang.'value')." ":config('global.lang_constant.COURIER_DELIVERY.value')." " ;
        $store_delivery = (config('global.lang_constant.STORE_DELIVERY.'.$lang.'value') !="")?config('global.lang_constant.STORE_DELIVERY.'.$lang.'value')." ":config('global.lang_constant.STORE_DELIVERY.value')." " ;
        $rental_service = (config('global.lang_constant.RENTAL_SERVICE.'.$lang.'value')!="")?config('global.lang_constant.RENTAL_SERVICE.'.$lang.'value')." ":config('global.lang_constant.RENTAL_SERVICE.value')." " ;

        if ($driver_vehicle_details->service_cat_id == 1) {
            $service = trim(($driver_service == 1 ? $ride_service : '') . ($courier_boy == 1 ? ", ".$courier_delivery : '') . ($delivery_boy == 1 ? ", ".$store_delivery : ''), ', ');
        } elseif ($driver_vehicle_details->service_cat_id == 2) {
            $service = trim(($driver_service == 1 ? $ride_service : '') . ($courier_boy == 1 ? ", ".$courier_delivery : '') . ($delivery_boy == 1 ? ", ".$store_delivery : '') . ($driver_vehicle_details->rental_service == 1 ? ", ".$rental_service : ''), ', ');
        } elseif ($driver_vehicle_details->service_cat_id == 4) {
            $service = $courier_delivery;
        } else {
            TransportDriverDetails::query()->where('id', $add_driver_vehicle_details->id)->delete();
            ProviderServices::query()->where('id', $add_driver_service->id)->delete();
            return response()->json([
                "status" => 0,
//                "message" => "something went to wrong!!",
                "message" => __('driver_messages.9'),
                "message_code" => 9,
            ]);
        }
        return response()->json([
            'status' => 1,
//            'message' => 'Success!',
            'message' => __('driver_messages.1'),
            "message_code" => 1,
            'driver_service_id' => $add_driver_service->id,
            'service_status' => $add_driver_service->status,
            'driver_current_status' => $driver_vehicle_details->driver_current_status,
            'service_category_id' => $add_driver_service->service_cat_id,
            'driver_service' => $service,
            'vehicle_type_icon' => $driver_vehicle_details->vehicle_type_icon,
            'vehicle_type_name' => $driver_vehicle_details->vehicle_type_name
        ]);
    }

    //get driver new notification request
    public function postGetDriverNewNotificationRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
                "driver_id" => "required|numeric",
                "access_token" => "required",
                "driver_service_id" => "required|numeric"
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

        $driver_request_all = DriverNewRequestNotification::query()->get();
        if ($driver_request_all != Null) {
            foreach ($driver_request_all as $key => $driver_request) {
                $get_driver_request = DriverNewRequestNotification::query()->where('id', $driver_request->id)->first();
                if ($get_driver_request != Null) {
                    $created_date_time = (date('Y-m-d H:i:s', strtotime('+2 minutes', strtotime($get_driver_request->created_at))));
                    $current_date_time = (date('Y-m-d H:i:s'));
                    if ($current_date_time >= $created_date_time) {
                        DriverNewRequestNotification::query()->where('id', $get_driver_request->id)->delete();
                    }
                }
            }
        }

        $driver_request_notify_check = DriverNewRequestNotification::query()->where('driver_id', $driver_id)->first();
        if ($driver_request_notify_check != Null) {
            $service_category_id = $driver_request_notify_check->service_cat_id;
            if (in_array($service_category_id, [1, 2, 4])) {
                $ride = TransportRideBook::query()->where('id', $driver_request_notify_check->request_id)->where('service_cat_id', $service_category_id)->first();
                if ($ride != Null) {
                    if ($ride->status == 0) {
                        $notification_data = json_decode($driver_request_notify_check->notification_data, true);
                        return response()->json([
                            "status" => 1,
//                            "message" => "success!",
                            "message" => __('driver_messages.1'),
                            "message_code" => 1,
                            "new_request" => $notification_data,
                        ]);
                    } elseif ($ride->status == 4) {
                        DriverNewRequestNotification::query()->where('driver_id', $driver_id)->where('request_id', $driver_request_notify_check->request_id)->delete();
                        return response()->json([
                            "status" => 0,
//                            "message" => "Sorry, Your ride request is cancelled by user",
                            "message" => __('driver_messages.24'),
                            "message_code" => 24,
                        ]);
                    } else {
                        DriverNewRequestNotification::query()->where('driver_id', $driver_id)->where('request_id', $driver_request_notify_check->request_id)->delete();
                        return response()->json([
                            "status" => 0,
//                            "message" => "Sorry, You are too late to the accept ride",
                            "message" => __('driver_messages.23'),
                            "message_code" => 23,
                        ]);
                    }
                } else {
                    return response()->json([
                        "status" => 0,
//                        "message" => "ride not found!",
                        "message" => __('driver_messages.26'),
                        "message_code" => 26,
                    ]);
                }
            } elseif (in_array($service_category_id, [5, 6, 7, 8, 9, 10])) {
                $delivery = UserProductBooking::query()->where('id', $driver_request_notify_check->request_id)->where('service_cat_id', $service_category_id)->first();
                if ($delivery != Null) {
                    if ($delivery->status == 3) {
                        DriverNewRequestNotification::query()->where('driver_id', $driver_id)->where('request_id', $driver_request_notify_check->request_id)->delete();
                        return response()->json([
                            "status" => 0,
//                            "message" => "Sorry, Your delivery request is cancelled by store",
                            "message" => __('driver_messages.29'),
                            "message_code" => 29,
                        ]);
                    } elseif ($delivery->status == 4) {
                        DriverNewRequestNotification::query()->where('driver_id', $driver_id)->where('request_id', $driver_request_notify_check->request_id)->delete();
                        return response()->json([
                            "status" => 0,
//                            "message" => "Sorry, Your delivery request is cancelled by user",
                            "message" => __('driver_messages.24'),
                            "message_code" => 24,
                        ]);
                    } elseif ($delivery->status == 10) {
                        DriverNewRequestNotification::query()->where('driver_id', $driver_id)->where('request_id', $driver_request_notify_check->request_id)->delete();
                        return response()->json([
                            "status" => 0,
//                            "message" => "Sorry, Your delivery request is failed",
                            "message" => __('driver_messages.25'),
                            "message_code" => 25,
                        ]);
                    } elseif ($delivery->delivery_people_id != Null) {
                        DriverNewRequestNotification::query()->where('request_id', $driver_request_notify_check->request_id)->delete();
                        return response()->json([
                            "status" => 0,
//                            "message" => "Sorry, You are too late to the accept ride",
                            "message" => __('driver_messages.23'),
                            "message_code" => 23,
                        ]);
                    } elseif ($delivery->delivery_people_id == Null) {
                        $notification_data = json_decode($driver_request_notify_check->notification_data, true);
                        return response()->json([
                            "status" => 1,
//                            "message" => "success!",
                            "message" => __('driver_messages.1'),
                            "message_code" => 1,
                            "new_request" => $notification_data,
                        ]);
                    } else {
                        return response()->json([
                            "status" => 0,
//                            "message" => "something went to wrong!",
                            "message" => __('driver_messages.9'),
                            "message_code" => 9,
                        ]);
                    }
                } else {
                    return response()->json([
                        "status" => 0,
//                        "message" => "ride not found!",
                        "message" => __('driver_messages.26'),
                        "message_code" => 26,
                    ]);
                }
            } else {
                return response()->json([
                    "status" => 0,
//                    "message" => "something went to wrong!",
                    "message" => __('driver_messages.9'),
                    "message_code" => 9,
                ]);
            }

        } else {
            return response()->json([
                "status" => 0,
//                "message" => "Sorry, You have no new request!",
                "message" => __('driver_messages.149'),
                "message_code" => 149,
            ]);
        }
    }

    //get driver new notification request
    public function postDriverRejectNotification(Request $request)
    {
        $validator = Validator::make($request->all(), [
                "driver_id" => "required|numeric",
                "access_token" => "required",
                "driver_service_id" => "required|numeric",
                "request_id" => "required",
                "service_category_id" => "required",
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
        $service_category_id = $request->get('service_category_id');
        $request_id = $request->get('request_id');

        $driver_request_notify_check = DriverNewRequestNotification::query()->where('driver_id', $driver_id)->where('request_id', $request_id)->where('service_cat_id', $service_category_id)->first();
        if ($driver_request_notify_check != Null) {
            DriverNewRequestNotification::query()->where('driver_id', $driver_id)->where('request_id', $request_id)->where('service_cat_id', $service_category_id)->delete();
            return response()->json([
                "status" => 1,
//                "message" => "success!",
                "message" => __('driver_messages.1'),
                "message_code" => 1,
            ]);
        } else {
            return response()->json([
                "status" => 0,
//                "message" => "ride not found!",
                "message" => __('driver_messages.26'),
                "message_code" => 26,
            ]);
        }
    }

    //driver accept request
    public function postDriverAcceptRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "driver_id" => "required|numeric",
            "access_token" => "required",
            "service_category_id" => "required|numeric|in:1,2,4,5,6,7,8,9,10",
            "request_id" => "required|numeric",
            "driver_service_id" => "required|numeric"
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
        $driver_currency = WorldCurrency::query()->where('symbol', $driver_check->currency)->first();
        if ($driver_currency == Null) {
            $driver_currency = WorldCurrency::query()->where('default_currency', 1)->first();
        }
        $currency = $driver_currency->ratio;
        $service_category_id = $request->get('service_category_id');
        if (in_array($service_category_id, [1, 2, 4])) {
            $ride = TransportRideBook::query()->where('id', $request->get('request_id'))->where('service_cat_id', $service_category_id)->first();
            if ($ride != Null) {
                if ($ride->status == 0) {
                    //send notification for driver accepting this ride
                    return $this->notificationClass->driverAcceptedTransportRequestNotification($request->get('request_id'), $request->get('driver_service_id'), $driver_id, $currency);
                } elseif ($ride->status == 4) {
                    return response()->json([
                        "status" => 0,
//                        "message" => "Sorry, Your ride request is cancelled by user",
                        "message" => __('driver_messages.24'),
                        "message_code" => 24,
                    ]);
                } else {
                    return response()->json([
                        "status" => 0,
//                        "message" => "Sorry, You are too late to the accept ride",
                        "message" => __('driver_messages.23'),
                        "message_code" => 23,
                    ]);
                }
            } else {
                return response()->json([
                    "status" => 0,
//                    "message" => "ride not found!",
                    "message" => __('driver_messages.26'),
                    "message_code" => 26,
                ]);
            }
        }
        elseif (in_array($service_category_id, [5, 6, 7, 8, 9, 10])) {
            $delivery = UserProductBooking::query()->where('id', $request->get('request_id'))->where('service_cat_id', $service_category_id)->first();
            if ($delivery != Null) {
                if ($delivery->status == 3) {
                    return response()->json([
                        "status" => 0,
//                        "message" => "Sorry, Your delivery request is cancelled by store",
                        "message" => __('driver_messages.29'),
                        "message_code" => 29,
                    ]);
                } elseif ($delivery->status == 4) {
                    return response()->json([
                        "status" => 0,
//                        "message" => "Sorry, Your delivery request is cancelled by user",
                        "message" => __('driver_messages.24'),
                        "message_code" => 24,
                    ]);
                } elseif ($delivery->status == 10) {
                    return response()->json([
                        "status" => 0,
//                        "message" => "Sorry, Your delivery request is failed",
                        "message" => __('driver_messages.25'),
                        "message_code" => 25,
                    ]);
                } elseif ($delivery->delivery_people_id != Null) {
                    return response()->json([
                        "status" => 0,
//                        "message" => "Sorry, You are too late to the accept ride",
                        "message" => __('driver_messages.23'),
                        "message_code" => 23,
                    ]);
                } elseif ($delivery->delivery_people_id == Null) {
                    //send notification for driver accepting this ride
                    return $this->notificationClass->driverAcceptedDeliveryRequestNotification($request->get('request_id'), $request->get('driver_service_id'), $driver_id, $currency);
                } else {
                    return response()->json([
                        "status" => 0,
//                        "message" => "something went to wrong!",
                        "message" => __('driver_messages.9'),
                        "message_code" => 9,
                    ]);
                }
            } else {
                return response()->json([
                    "status" => 0,
//                    "message" => "ride not found!",
                    "message" => __('driver_messages.26'),
                    "message_code" => 26,
                ]);
            }
        } else {
            return response()->json([
                "status" => 0,
//                "message" => "something went to wrong!",
                "message" => __('driver_messages.9'),
                "message_code" => 9,
            ]);
        }
    }

    public function postDriverRejectRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
                "driver_id" => "required|numeric",
                "access_token" => "required",
                "service_category_id" => "required|numeric|in:1,2,4,5,6,7,8,9,10,31,32",
                "request_id" => "required|numeric",
                "driver_service_id" => "required|numeric"
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

        $service_category_id = $request->get('service_category_id');
        $request_id = $request->get('request_id');

        try {
            DriverNewRequestNotification::query()->where('driver_id', $driver_id)->where('request_id', $request_id)->where('service_cat_id', $service_category_id)->delete();
        } catch (\Exception $e){}

        if (in_array($service_category_id, $this->delivery_service_id_array)){
            $ride_driver_token = TransportRideDriverToken::query()->where("delivery_id", $request_id)->where("service_cat_id", $service_category_id)->first();
        }else{
            $ride_driver_token = TransportRideDriverToken::query()->where("ride_id", $request_id)->where("service_cat_id", $service_category_id)->first();
        }

        if ($ride_driver_token != null && ($ride_driver_token->android_tokens == null || $ride_driver_token->android_tokens == '')){
            $ride_driver_token->expire_date_time = date('Y-m-d H:i:s');
            $ride_driver_token->save();
        }

        if ($ride_driver_token != null && $ride_driver_token->android_tokens != Null){
            if ($service_category_id == $this->courier_service) {
                $ride_request = TransportRideBook::query()->select('user_ride_booking.id as request_id')
                    ->where('user_ride_booking.id', $request_id)
                    ->where('user_ride_booking.driver_algorithm', 1)
                    ->where('user_ride_booking.status',0)
                    ->whereNull("driver_id")
                    ->first();
                if ($ride_request != Null) {
                    $this->notificationClass->DriverNewRequestNotification($ride_driver_token->id,$reject_notification = 1);
                }
            }
            else if (in_array($service_category_id, [$this->bike_ride, $this->taxi_ride])) {
                $ride_request = TransportRideBook::query()->select('user_ride_booking.id as request_id')
                    ->where('user_ride_booking.id', $request_id)
                    ->where('user_ride_booking.driver_algorithm', 1)
                    ->where('user_ride_booking.status',0)
                    ->whereNull("driver_id")
                    ->first();
                if ($ride_request != Null) {
                    $this->notificationClass->DriverNewRequestNotification($ride_driver_token->id,$reject_notification = 1);
                }
            }
            else if (in_array($service_category_id, $this->transport_rental_service_id_array)) {
                $ride_request = TransportRentalRideBooking::query()->select('user_rental_ride_booking.id as request_id')
                    ->where('user_rental_ride_booking.id', $request_id)
                    ->where('user_rental_ride_booking.driver_algorithm', 1)
                    ->where('user_rental_ride_booking.status',0)
                    ->whereNull("driver_id")
                    ->first();
                if ($ride_request != Null) {
                    $this->notificationClass->DriverNewRequestNotification($ride_driver_token->id,$reject_notification = 1);
                }
            }
            else if (in_array($service_category_id, $this->delivery_service_id_array)) {
                $order_details = UserProductBooking::query()->select('user_store_product_booking.id as request_id')
                    ->where('user_store_product_booking.id', $request_id)
                    ->where('user_store_product_booking.driver_algorithm', 1)
                    ->where('user_store_product_booking.order_delivery_status', 0)
                    ->whereNull("delivery_people_id")
                    ->first();
                if ($order_details != Null) {
                    $this->notificationClass->DriverNewRequestNotification($ride_driver_token->id,$reject_notification = 1);
                }
            }
        }

        return response()->json([
            "status" => 1,
//                "message" => "success!",
            'message' => __('driver_messages.1'),
            "message_code" => 1,
        ]);
    }

    //driver ride details
    public function postDriverRideDetails(Request $request)
    {
        $this->notificationClass->ApiLogDetail($logger_type = 2, $request->get('driver_id'), "postDriverRideDetails", $request->all());

        $validator = Validator::make($request->all(), [
            "driver_id" => "required|numeric",
            "access_token" => "required",
            "driver_service_id" => "required|numeric",
            "service_category_id" => "required|numeric",
            "ride_id" => "required|numeric",
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }

        $driver_id = $request->get('driver_id');
        $service_category_id = $request->get('service_category_id');
        $driver_check = $this->driverClassApi->checkDriverRegisterAllow($driver_id, $request->get('access_token'), $request->get('driver_service_id'));
        if ($decoded['status'] = json_decode($driver_check) == false) {
            return $driver_check;
        }
        $driver_currency = WorldCurrency::query()->where('symbol', $driver_check->currency)->first();
        if ($driver_currency == Null) {
            $driver_currency = WorldCurrency::query()->where('default_currency', 1)->first();
        }
        $currency = $driver_currency->ratio;
        $user_profile_url = url('/assets/images/profile-images/customer');
        $store_banner_url = url('/assets/images/store-images');
        if (in_array($service_category_id, $this->transport_service_id_array) || in_array($service_category_id, $this->courier_service_id_array)) {
            $ride_details = TransportRideBook::query()->select('user_ride_booking.id as ride_id',
                'user_ride_booking.ride_no as booking_no',
                'user_ride_booking.service_cat_id as service_category_id',
                DB::raw("(CASE WHEN users.avatar != '' THEN (CASE WHEN CHAR_LENGTH(users.avatar) >= 25 THEN users.avatar ELSE concat('$user_profile_url','/',users.avatar) END) ELSE '' END) as user_profile_image"),
                DB::raw("(concat(users.first_name,'')) as user_name"),
                'users.contact_number',
                'users.id as user_id',
                'users.device_token as user_fcm_token',
                'user_ride_booking.pickup_address as pickup_address',
                'user_ride_booking.additional_request',
                'user_ride_booking.pickup_latlong as pickup_latlong',
                'user_ride_booking.destination_address as destination_address',
                'user_ride_booking.destination_latlong as destination_latlong',
                'user_ride_booking.pickup_datetime as pickup_datetime',
                'user_ride_booking.created_at',
                'user_ride_booking.total_pay as total_amount',
                'user_ride_booking.payment_type as payment_type',
                'user_ride_booking.is_way_point as is_way_point',
                'user_ride_booking.way_point_status as way_point_status',
                'user_ride_booking.promo_code',
                'user_ride_booking.otp',
                'user_ride_booking.surcharge_cost',
                'user_ride_booking.status as ride_status')
                ->join('users', 'users.id', '=', 'user_ride_booking.user_id')
                ->where('user_ride_booking.id', $request->get('ride_id'))->first();

            if ($ride_details != Null) {
                $courier_details = Null;
                if (in_array($ride_details->service_category_id, $this->courier_service_id_array)) {
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
                        ->where('ride_id', $ride_details->ride_id)
                        ->first();
                }
                $pickup = array_map('trim', explode(",", $ride_details->pickup_latlong));
                $destination = array_map('trim', explode(",", $ride_details->destination_latlong));
                $way_points[] = [
                    "address" => $ride_details->pickup_address,
                    "address_lat" => trim($pickup[0]),
                    "address_long" => trim($pickup[1])
                ];
                if ($ride_details->is_way_point == 1) {
                    $ride_way_point = UserRideWayPoint::query()->where('ride_id', $ride_details->ride_id)->first();
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
                    "address" => $ride_details->destination_address,
                    "address_lat" => trim($destination[0]),
                    "address_long" => trim($destination[1])
                ];
                $promocode_details = UsedPromocodeDetails::query()->where('id', $ride_details->promo_code)->first();
                $ride_details_arr = [
                    "ride_id" => $ride_details->ride_id,
                    "user_id" => $ride_details->user_id,
                    'additional_remark' => "" . $ride_details->additional_request,
                    "user_fcm_token" => $ride_details->user_fcm_token,
                    "booking_no" => $ride_details->booking_no,
                    "service_category_id" => $ride_details->service_category_id,
                    "user_profile_image" => $ride_details->user_profile_image,
                    "user_name" => $ride_details->user_name,
                    "contact_number" => $ride_details->contact_number,
                    "address_list" => $way_points,
                    "flat_no" => '',
                    "landmark" => '',
                    "pickup_datetime" => $ride_details->pickup_datetime,
                    "service_date_time" => date('Y-m-d H:i:s',strtotime($ride_details->created_at)),
                    "total_amount" => round($ride_details->total_amount * $currency, 2),
                    "payment_type" => $ride_details->payment_type,
                    "ride_status" => $ride_details->ride_status,
                    "promocode_discount" => $promocode_details != Null ? round($promocode_details->discount_amount * $currency, 2) : 0,
                    "promocode_name" => $promocode_details != Null ? $promocode_details->promocode_name : '',
                    "way_point_status" => $ride_details->way_point_status,
                    "surcharge_cost" => $ride_details->surcharge_cost,
                    "otp" => $ride_details->otp,
                ];
                return response()->json([
                    "status" => 1,
//                    "message" => "success!",
                    "message" => __('driver_messages.1'),
                    "message_code" => 1,
                    "ride_details" => $ride_details_arr,
                    "courier_details" => $courier_details
                ]);
            } else {
                return response()->json([
                    "status" => 0,
//                    "message" => "ride details not found!",
                    "message" => __('driver_messages.26'),
                    "message_code" => 26,
                ]);
            }
        }
        elseif (in_array($service_category_id, $this->delivery_service_id_array)) {
            $ride_details = UserProductBooking::query()->select('user_store_product_booking.id as ride_id',
                'user_store_product_booking.order_no as booking_no',
                'user_store_product_booking.service_cat_id as service_category_id',
                DB::raw("(CASE WHEN users.avatar != '' THEN (CASE WHEN CHAR_LENGTH(users.avatar) >= 25 THEN users.avatar ELSE concat('$user_profile_url','/',users.avatar) END) ELSE '' END) as user_profile_image"),
                DB::raw("(concat(users.first_name,'')) as user_name"),
                'users.country_code',
                'users.contact_number',
                'users.id as user_id',
                'providers.device_token as store_fcm_token',
                'users.device_token as user_fcm_token',
                'store_details.address as pickup_address',
                'store_details.name as store_name',
                'providers.contact_number as store_contact_number',
                'providers.country_code as store_country_code',
                'providers.id as store_id',
                'store_details.image as store_banner',
                'user_store_product_booking.flat_no',
                'user_store_product_booking.landmark',
                'user_store_product_booking.additional_request',
                'user_store_product_booking.promo_code',
                DB::raw("(concat(store_details.address_lat,',',store_details.address_long)) as pickup_latlong"),
                'user_store_product_booking.delivery_address as destination_address',
                'user_store_product_booking.lat_long as destination_latlong',
                'user_store_product_booking.created_at as pickup_datetime',
                'user_store_product_booking.order_date_time',
                'user_store_product_booking.total_pay as total_amount',
                'user_store_product_booking.payment_type as payment_type',
                'user_store_product_booking.otp',
//                DB::raw("(CASE WHEN user_store_product_booking.payment_type != 1 THEN (CASE WHEN user_store_product_booking.payment_type != 2 THEN (CASE WHEN user_store_product_booking.payment_type != 3 THEN '' ELSE 'wallet' END) ELSE 'card' END) ELSE 'cash' END) as payment_type"),
                'user_store_product_booking.order_delivery_status as ride_status')
                ->join('users', 'users.id', '=', 'user_store_product_booking.user_id')
                ->join('store_details', 'store_details.id', '=', 'user_store_product_booking.store_id')
                ->join('provider_services', 'provider_services.id', '=', 'store_details.provider_service_id')
                ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
                ->where('user_store_product_booking.id', $request->get('ride_id'))->first();
            if ($ride_details != Null) {
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
                $promocode_details = UsedPromocodeDetails::query()->where('id', $ride_details->promo_code)->first();
                $lan_prefix = $this->adminClass->get_langugae_fields($driver_check->language);
                $service_name = $lan_prefix.'name';
                $service = ServiceCategory::query()->select($service_name)->where('id',$ride_details->service_category_id)->first();
                $ride_details_arr = [
                    "ride_id" => $ride_details->ride_id,
                    "required_otp" => 0,
                    "user_id" => $ride_details->user_id,
                    "additional_remark" => $ride_details->additional_request,
                    "user_fcm_token" => $ride_details->user_fcm_token,
                    "booking_no" => $ride_details->booking_no,
                    "service_category_id" => $ride_details->service_category_id,
                    "user_profile_image" => $ride_details->user_profile_image,
                    "user_name" => $ride_details->user_name,
                    "contact_number" => $ride_details->country_code.$ride_details->contact_number,
                    "address_list" => $way_points,
                    "flat_no" => $ride_details->flat_no != Null ? $ride_details->flat_no : '',
                    "landmark" => $ride_details->landmark != Null ? $ride_details->landmark : '',
                    "pickup_datetime" => date('Y-m-d H:i:s',strtotime($ride_details->pickup_datetime)),
                    "schedule_order_date_time" => $ride_details->order_date_time,
                    "total_amount" => round($ride_details->total_amount * $currency, 2),
                    "payment_type" => $ride_details->payment_type,
                    "store_name" => $ride_details->store_name,
                    "store_id" => $ride_details->store_id,
                    "store_fcm_token" =>$ride_details->store_fcm_token,
                    "promocode_discount" => $promocode_details != Null ? round($promocode_details->discount_amount * $currency, 2) : 0,
                    "promocode_name" => $promocode_details != Null ? $promocode_details->promocode_name : '',
                    "store_contact_number" => $ride_details->store_country_code.$ride_details->store_contact_number,
                    "store_banner" => url('/assets/images/store-images/', $ride_details->store_banner),
                    "ride_status" => $ride_details->ride_status,
                    'category_name' => $service->$service_name,
                    "way_point_status" => 0,
                    "otp" => $ride_details->otp,
                ];
                return response()->json([
                    "status" => 1,
//                    "message" => "success!",
                    "message" => __('driver_messages.1'),
                    "message_code" => 1,
                    "ride_details" => $ride_details_arr,
                    "courier_details" => Null
                ]);
            } else {
                return response()->json([
                    "status" => 0,
//                    "message" => "ride details not found!",
                    "message" => __('driver_messages.26'),
                    "message_code" => 26,
                ]);
            }
        }
        else {
            return response()->json([
                "status" => 0,
//                "message" => "something went to wrong!",
                "message" => __('driver_messages.9'),
                "message_code" => 9,
            ]);
        }
    }

    //driver ride history
    public function postDriverRideHistory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "driver_id" => "required|numeric",
            "access_token" => "required",
            "driver_service_id" => "required|numeric",
            "filter_type" => "nullable|in:0,1,2,3,4,5,6",// 6 for running delivery ride [ for multi delivery module ]
            "timezone" => "required"
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

        $user_currency = WorldCurrency::query()->where('symbol', $driver_check->currency)->first();
        if ($user_currency == Null) {
            $user_currency = WorldCurrency::query()->where('default_currency', 1)->first();
        }
        $currency = $user_currency->ratio;
        $driver_details = TransportDriverDetails::query()->where('provider_service_id', $request->get('driver_service_id'))->first();
        if ($driver_details != Null) {

            $service_category_icon_url = url('/assets/images/service-category/');
            $ride_history = [];

            if ($driver_check->language != "en") {
                $lang_prefix = $driver_check->language."_";
            }else {
                $lang_prefix = "";
            }

            date_default_timezone_set($request->get('timezone'));

            $filter_type = $request->get('filter_type');
            $date = date('Y-m-d');
            $transport_running_ride = [];
            $transport_rental_running_ride = [];
            $store_running_ride = [];

            if ($filter_type == 1) {
                //today
                $start_date = $date . " 00:00:01";
                $end_date = $date . " 23:59:59";
            } elseif ($filter_type == 2) {
                //last 7 day
                $start_date = date('Y-m-d', strtotime('-7 days', strtotime($date)));
                $end_date = $date;
                $start_date = $start_date . " 00:00:01";
                $end_date = $end_date . " 23:59:59";
            } elseif ($filter_type == 4) {
                //this year
                $start_date = date("Y-01-01", strtotime($date));
                $end_date = date("Y-m-d", strtotime($date));
                $start_date = $start_date . " 00:00:01";
                $end_date = $end_date . " 23:59:59";
            } elseif ($filter_type == 5) {
                //upcomming rides
                $start_date = date('Y-m-d', strtotime('+1 days', strtotime($date)));
                $start_date = $start_date . " 00:00:01";
            } elseif ($filter_type == 6) {
                //running rides
                $transport_running_ride = ProviderUserRunningService::query()->where("provider_id", "=", $driver_id)->whereIn("service_cat_id", [1, 2, 4])->get()->pluck("booking_id");
                $transport_rental_running_ride = ProviderUserRunningService::query()->where("provider_id", "=", $driver_id)->whereIn("service_cat_id", $this->transport_rental_service_id_array)->get()->pluck("booking_id");
                $store_running_ride = ProviderUserRunningService::query()->where("provider_id", "=", $driver_id)->whereIn("service_cat_id", $this->delivery_service_id_array)->get()->pluck("booking_id");
            } else {
                //last 30 day
                $start_date = date('Y-m-d', strtotime('-30 days', strtotime($date)));
                $end_date = $date;
                $start_date = $start_date . " 00:00:01";
                $end_date = $end_date . " 23:59:59";
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

            // for filter type all fire query
            $temp_transport_rides = TransportRideBook::query()->select('user_ride_booking.id as ride_id',
                'service_category.id as service_category_id','user_ride_booking.ride_no as order_no',
                //'service_category.' . $lang_prefix . 'name as service_category_name',
                DB::raw("( CASE WHEN service_category." . $lang_prefix . "name != '' THEN service_category." . $lang_prefix . "name ELSE service_category.name END) as service_category_name"),
                DB::raw("(CASE WHEN service_category.icon_name!='' THEN concat('$service_category_icon_url','/',service_category.icon_name) ELSE '' END) as service_category_icon"),
                'user_ride_booking.status as ride_status','user_ride_booking.created_at','user_ride_booking.pickup_datetime as schedule_service_date_time',
                'user_ride_booking.pickup_datetime as ride_date_time','user_ride_booking.driver_pay_settle_status',
                DB::raw('ROUND(user_ride_booking.driver_amount * ' . $currency . ',2) As ride_amount'),
                'user_ride_booking.ride_type',
                (DB::raw('DATE_FORMAT(user_ride_booking.pickup_datetime, "%a %d %b,%Y") as ride_date')),
                (DB::raw('DATE_FORMAT(user_ride_booking.pickup_datetime, "%H:%i") as ride_time')))
                ->join('service_category', 'service_category.id', '=', 'user_ride_booking.service_cat_id')
                ->where('user_ride_booking.driver_id', $driver_details->id);

            $temp_transport_rental_rides = TransportRentalRideBooking::query()->select('user_rental_ride_booking.id as ride_id',
                'service_category.id as service_category_id','user_rental_ride_booking.ride_no as order_no',
                //'service_category.' . $lang_prefix . 'name as service_category_name',
                DB::raw("( CASE WHEN service_category." . $lang_prefix . "name != '' THEN service_category." . $lang_prefix . "name ELSE service_category.name END) as service_category_name"),
                DB::raw("(CASE WHEN service_category.icon_name!='' THEN concat('$service_category_icon_url','/',service_category.icon_name) ELSE '' END) as service_category_icon"),
                'user_rental_ride_booking.status as ride_status','user_rental_ride_booking.created_at','user_rental_ride_booking.pickup_date_time as schedule_service_date_time',
                'user_rental_ride_booking.pickup_date_time as ride_date_time','user_rental_ride_booking.driver_pay_settle_status',
                DB::raw('ROUND(user_rental_ride_booking.driver_amount * ' . $currency . ',2) As ride_amount'),
                (DB::raw("1 as ride_type")),
                (DB::raw('DATE_FORMAT(user_rental_ride_booking.pickup_date_time, "%a %d %b,%Y") as ride_date')),
                (DB::raw('DATE_FORMAT(user_rental_ride_booking.pickup_date_time, "%H:%i") as ride_time')))
                ->join('service_category', 'service_category.id', '=', 'user_rental_ride_booking.service_cat_id')
                ->where('user_rental_ride_booking.driver_id', $driver_details->id);

            $temp_delivery_rides = UserProductBooking::query()->select('user_store_product_booking.id as ride_id',
                'service_category.id as service_category_id','user_store_product_booking.order_no',
                //'service_category.' . $lang_prefix . 'name as service_category_name',
                DB::raw("( CASE WHEN service_category." . $lang_prefix . "name != '' THEN service_category." . $lang_prefix . "name ELSE service_category.name END) as service_category_name"),
                DB::raw("(CASE WHEN service_category.icon_name!='' THEN concat('$service_category_icon_url','/',service_category.icon_name) ELSE '' END) as service_category_icon"),
                'user_store_product_booking.order_delivery_status as ride_status','user_store_product_booking.created_at','user_store_product_booking.order_date_time as schedule_service_date_time',
                'user_store_product_booking.created_at as ride_date_time','user_store_product_booking.driver_pay_settle_status',
                DB::raw('ROUND((user_store_product_booking.delivery_cost - user_store_product_booking.delivery_admin_commission) * ' . $currency . ',2) As ride_amount'),
                (DB::raw("0 as ride_type")),
                (DB::raw('DATE_FORMAT(user_store_product_booking.created_at, "%a %d %b,%Y") as ride_date')),
                (DB::raw('DATE_FORMAT(user_store_product_booking.created_at, "%H:%i") as ride_time')))
                ->join('service_category', 'service_category.id', '=', 'user_store_product_booking.service_cat_id')
                ->where('user_store_product_booking.delivery_people_id', $driver_details->id);

            if ($filter_type == 1 || $filter_type == 2 || $filter_type == 3 || $filter_type == 4) {
                $transport_rides = $temp_transport_rides->where('user_ride_booking.pickup_datetime', '>=', $start_date)
                    ->where('user_ride_booking.pickup_datetime', '<=', $end_date);

                $transport_rental_rides = $temp_transport_rental_rides->where('user_rental_ride_booking.pickup_date_time', '>=', $start_date)
                    ->where('user_rental_ride_booking.pickup_date_time', '<=', $end_date);

                $delivery_rides = $temp_delivery_rides->where('user_store_product_booking.order_date_time', '>=', $start_date)
                    ->where('user_store_product_booking.order_date_time', '<=', $end_date);
            } elseif ($filter_type == 5) {
                $transport_rides = $temp_transport_rides->where('user_ride_booking.pickup_datetime', '>', $start_date);
                $transport_rental_rides = $temp_transport_rental_rides->where('user_rental_ride_booking.pickup_date_time', '>', $start_date);
                $delivery_rides = $temp_delivery_rides->where('user_store_product_booking.order_date_time', '>', $start_date);
            } elseif ($filter_type == 6) {
                $transport_rides = $temp_transport_rides->whereIn('user_ride_booking.id', $transport_running_ride);
                $transport_rental_rides = $temp_transport_rental_rides->whereIn('user_rental_ride_booking.id', $transport_rental_running_ride);
                $delivery_rides = $temp_delivery_rides->whereIn('user_store_product_booking.id', $store_running_ride);
            } else {
                // take default
                $transport_rides = $temp_transport_rides;
                $transport_rental_rides = $temp_transport_rental_rides;
                $delivery_rides = $temp_delivery_rides;
            }

            $total_count = $transport_rides->count() + $transport_rental_rides->count() + $delivery_rides->count();
            $transport_rides = $transport_rides->get()->toArray();
            $transport_rental_rides = $transport_rental_rides->get()->toArray();
            $delivery_rides = $delivery_rides->get()->toArray();

            $ride_history = array_merge($ride_history, $transport_rides);
            $ride_history = array_merge($ride_history, $transport_rental_rides);
            $ride_history = array_merge($ride_history, $delivery_rides);

            //$ride_history = array_slice($ride_history,$offset,$per_page);

            if ($ride_history != Null) {
                usort($ride_history, function ($a, $b) {
                    if ($a['ride_date_time'] == $b['ride_date_time']) return 0;
                    return $a['ride_date_time'] < $b['ride_date_time'] ? 1 : -1;
                });
            }
            $complete_ride = array_filter($ride_history, function ($var) {
                return ($var['ride_status'] == 9);
            });
            $pending_ride = array_filter($ride_history, function ($var) {
                return ($var['ride_status'] == 1);
            });
            $cancelled_ride = array_filter($ride_history, function ($var) {
                return ($var['ride_status'] == 4 || $var['ride_status'] == 10);
            });
            //$total_revenues = array_sum(array_column($complete_ride, 'ride_amount'));
            $total_revenues = round(array_sum(array_column((array_filter($complete_ride, function ($var) {
                if($var['ride_status'] == 9 && $var['driver_pay_settle_status'] == 0){
                    return  $var['ride_amount'];
                }
            })
            ),'ride_amount')),2);

            $ride_history_count = count($ride_history);

            $ride_history = array_slice($ride_history,$offset,$per_page);

            $ride_history_new = [];
            foreach ($ride_history as $key=> $singel){
                $ride_history_new[$key]['ride_id'] = $singel['ride_id'];
                $ride_history_new[$key]['driver_pay_settle_status'] = $singel['driver_pay_settle_status'];
                $ride_history_new[$key]['service_category_id'] = $singel['service_category_id'];
                $ride_history_new[$key]['service_category_name'] = $singel['service_category_name'];
                //$ride_history_new[$key]['store_name'] = $singel['store_name'];
                $ride_history_new[$key]['service_category_icon'] = $singel['service_category_icon'];
                $ride_history_new[$key]['ride_status'] = $singel['ride_status'];
                $ride_history_new[$key]['ride_date_time'] = $singel['ride_date_time'];
                $ride_history_new[$key]['ride_amount'] = $singel['ride_amount'];
                $ride_history_new[$key]['ride_type'] = $singel['ride_type'];
                $ride_history_new[$key]['ride_date'] = $this->notificationClass->dateLangConvert($singel['ride_date'],$driver_check->language);
                $ride_history_new[$key]['ride_time'] = $singel['ride_time'];
                $ride_history_new[$key]['service_date_time'] = date('Y-m-d H:i:s',strtotime($singel['created_at']));
                $ride_history_new[$key]['schedule_service_date_time'] = $singel['schedule_service_date_time'];

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
//                "message" => "success!",
                "message" => __('driver_messages.1'),
                "message_code" => 1,
                'current_page' => $current_page - 0,
                'last_page' => $last_page - 0,
                'total' => $total_count - 0,
                "total_revenues" => round($total_revenues * $currency, 2),
                "completed_ride" => count($complete_ride),
//                "total_ride" => count($pending_ride),
                "total_ride" => $ride_history_count,
                "cancelled_ride" => count($cancelled_ride),
                "ride_history" => $ride_history_new
            ]);
        } else {
            return response()->json([
                "status" => 0,
//                "message" => "driver details not found",
                "message" => __('driver_messages.15'),
                "message_code" => 15,
            ]);
        }
    }

    public function postGetServiceTypeDocumentList(Request $request)
    {
        $validator = Validator::make($request->all(), [
                "driver_id" => "required|numeric",
                "access_token" => "required",
                "service_category_id" => "required|numeric|in:1,2,4"
            ]
        );
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }

        $driver_details = Provider::query()->where('id', $request->get('driver_id'))->first();
        if ($driver_details != Null) {
            if ($driver_details->status == 2) {
                return response()->json([
                    'status' => 3,
//                    'message' => 'Your account is currently blocked, so not authorised to allow any activity!',
                    'message' => __('driver_messages.3'),
                    "message_code" => 3,
                ]);
            }
            $vehicle_type_list = [];
            $required_document_list = [];

            $get_vehicle_types = TransportVehicleType::query()->select('transport_vehicle_type.id as vehicle_type_id', 'transport_vehicle_type.name as vehicle_type_name')
                ->where('transport_vehicle_type.service_cat_id', $request->get('service_category_id'));
            if ($request->get('service_category_id') == 4){
                $get_vehicle_types = $get_vehicle_types->whereNotIn("transport_vehicle_type.id", [10,14]);
            }
            $get_vehicle_types = $get_vehicle_types->where('transport_vehicle_type.status', 1)->get();
            if (!$get_vehicle_types->isEmpty()) {
                $vehicle_type_list = $get_vehicle_types;
            }
            $get_required_documents = RequiredDocuments::query()->select('required_documents.id as document_id', 'required_documents.name as document_name')
                ->where('required_documents.service_cat_id', $request->get('service_category_id'))
                ->where('required_documents.status', 1)->get();
            if (!$get_required_documents->isEmpty()) {
                $required_document_list = $get_required_documents;
            }
            return response()->json([
                'status' => 1,
//                'message' => 'success!',
                'message' => __('driver_messages.1'),
                "message_code" => 1,
                'vehicle_type_list' => $vehicle_type_list,
                'required_document_list' => $required_document_list,
            ]);
        } else {
            return response()->json([
                'status' => 5,
//                'message' => 'Driver Not Found!',
                'message' => __('driver_messages.5'),
                "message_code" => 5,
            ]);
        }
    }

    public function postDriverUpdateCurrentStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
                "driver_id" => "required|numeric",
                "access_token" => "required",
                "driver_service_id" => "required|numeric",
                "update_status" => "required|numeric|in:0,1"
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

        $driver_service = ProviderServices::query()->where('id', $request->get('driver_service_id'))->first();
        if ($driver_service != Null) {
            $driver_details = TransportDriverDetails::query()->where('provider_service_id', $driver_service->id)->first();
            if ($driver_details != Null) {

                $check_rental_running = ProviderUserRunningService::query()->where('provider_id',$driver_id)->whereIn('service_cat_id',$this->transport_rental_service_id_array)->first();
                if($check_rental_running != Null)
                {
                        return response()->json([
                            "status" => 0,
//                            "message" => 'Sorry, Currently you have running a rental ride service',
                            "message" => __('driver_messages.275'),
                            "message_code" => 275,
                        ]);

                }else{
                    $driver_service->current_status = $request->get('update_status');
                    $driver_service->save();
                    if ($request->get('update_status') == 1) {
                        $driver_details->last_online_date_time = date('Y-m-d H:i:s');
                        $driver_details->save();
                    }
                    return response()->json([
                        "status" => 1,
//                        "message" => 'success!',
                        "message" => __('driver_messages.1'),
                        "message_code" => 1,
                        "driver_current_status" => $driver_service->current_status - 0
                    ]);
                }

            } else {
                return response()->json([
                    "status" => 0,
//                    "message" => 'driver service not found!',
                    "message" => __('driver_messages.9'),
                    "message_code" => 9,
                ]);
            }
        } else {
            return response()->json([
                "status" => 0,
//                "message" => 'driver service not found!',
                "message" => __('driver_messages.9'),
                "message_code" => 9,
            ]);
        }
    }

    public function postUpdateDriverBankDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
                "driver_id" => "required|numeric",
                "access_token" => "required",
                "driver_service_id" => "required|numeric",
                "account_number" => "required|numeric",
                "holder_name" => "required",
                "bank_name" => "required",
                "bank_location" => "required",
                "payment_email" => "required|email",
                "bic_swift_code" => "required"
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

        $bank_details = ProviderBankDetails::query()->where('provider_id', $driver_id)->first();
        if ($bank_details == Null) {
            $bank_details = new ProviderBankDetails();
        }
        $bank_details->provider_id = $driver_id;
        $bank_details->account_number = $request->get('account_number');
        $bank_details->holder_name = $request->get('holder_name');
        $bank_details->bank_name = $request->get('bank_name');
        $bank_details->bank_location = $request->get('bank_location');
        $bank_details->payment_email = $request->get('payment_email');
        $bank_details->bic_swift_code = $request->get('bic_swift_code');
        $bank_details->save();

        return response()->json([
            "status" => 1,
//            "message" => "success!",
            "message" => __('driver_messages.1'),
            "message_code" => 1,
        ]);
    }

    public function postGetDriverBankDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
                "driver_id" => "required|numeric",
                "access_token" => "required",
                "driver_service_id" => "required|numeric",
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

        $bank_details = ProviderBankDetails::query()->where('provider_id', $driver_id)->first();
        if ($bank_details == Null) {
            return response()->json([
                "status" => 0,
//                "message" => "driver bank details not found!",
                "message" => __('driver_messages.79'),
                "message_code" => 79,
            ]);
        }
        return response()->json([
            "status" => 1,
//            "message" => "success!",
            "message" => __('driver_messages.1'),
            "message_code" => 1,
            "account_number" => $bank_details->account_number,
            "holder_name" => $bank_details->holder_name,
            "bank_name" => $bank_details->bank_name,
            "bank_location" => $bank_details->bank_location,
            "payment_email" => $bank_details->payment_email,
            "bic_swift_code" => $bank_details->bic_swift_code,
        ]);
    }

    public function postDriverHome(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "driver_id" => "required|numeric",
            "access_token" => "required",
            "driver_service_id" => "required|numeric",
            "app_version" => "required",
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

        $driver_check = Provider::query()->where('id', $driver_id)->first();
        if ($driver_check == Null){
            return response()->json([
                "status" => 5,
//                "message" => 'Driver not found!',
                "message" => __('driver_messages.5'),
                "message_code" => 5
            ]);
        }
        $driver_check->app_version = $request->get("app_version");
        $driver_check->save();

        $vehicle_icon_url = url('/assets/images/service-category/transport-service-type');

        $driver_details = TransportDriverDetails::query()->select(
            'providers.id as driver_id',
            'providers.language as language',
            'provider_services.id as driver_service_id',
//            DB::raw("CONCAT(COALESCE(providers.first_name,''),' ',COALESCE(providers.last_name,'')) as driver_name"),
            DB::raw("CONCAT(COALESCE(providers.first_name,'')) as driver_name"),
            'providers.avatar as driver_profile',
            'provider_services.current_status as driver_current_status',
            'transport_driver_details.id as driver_details_id',
            'transport_driver_details.driver_service',
            'transport_driver_details.rental_service',
            'transport_driver_details.delivery_boy',
            'transport_driver_details.courier_boy',
            'provider_services.service_cat_id',
            'transport_vehicle_type.name as vehicle_type_name',
            DB::raw("(CASE WHEN transport_vehicle_type.icon_name!='' THEN concat('$vehicle_icon_url','/',transport_vehicle_type.icon_name) ELSE '' END) as vehicle_type_icon"))
            ->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
            ->join('transport_vehicle_type', 'transport_vehicle_type.id', '=', 'transport_driver_details.vehicle_type_id')
            ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
            ->where('transport_driver_details.provider_service_id', $request->get('driver_service_id'))
            ->first();
        if ($driver_details == Null) {
            return response()->json([
                "status" => 0,
//                "message" => "driver details not found!",
                "message" => __('driver_messages.15'),
                "message_code" => 15,
            ]);
        }
        $driver_details_id = $driver_details->driver_details_id - 0;
        $driver_service = $driver_details->driver_service;
        $delivery_boy = $driver_details->delivery_boy;
        $courier_boy = $driver_details->courier_boy;
        if ($driver_details->driver_profile != Null) {
            if (filter_var($driver_details->driver_profile, FILTER_VALIDATE_URL) == true) {
                $avatar = $driver_details->driver_profile;
            } else {
                $avatar = url('/assets/images/profile-images/provider/' . $driver_details->driver_profile);
            }
        } else {
            $avatar = "";
        }
        $lang = $driver_details->language;
        if($lang !="en" && $lang != "" && $lang != "Null" ){
            $lang = $lang."_";
        } else{
            $lang ="";
        }

        $ride_service =  (config('global.lang_constant.RIDE_SERVICE.'.$lang.'value') !="")?config('global.lang_constant.RIDE_SERVICE.'.$lang.'value') ." ":config('global.lang_constant.RIDE_SERVICE.value')." ";
        $courier_delivery = (config('global.lang_constant.COURIER_DELIVERY.'.$lang.'value') !="")?config('global.lang_constant.COURIER_DELIVERY.'.$lang.'value')." ":config('global.lang_constant.COURIER_DELIVERY.value')." " ;
        $store_delivery = (config('global.lang_constant.STORE_DELIVERY.'.$lang.'value') !="")?config('global.lang_constant.STORE_DELIVERY.'.$lang.'value')." ":config('global.lang_constant.STORE_DELIVERY.value')." " ;
        $rental_service = (config('global.lang_constant.RENTAL_SERVICE.'.$lang.'value')!="")?config('global.lang_constant.RENTAL_SERVICE.'.$lang.'value')." ":config('global.lang_constant.RENTAL_SERVICE.value') ;

        if ($driver_details->service_cat_id == 1) {
            $service = trim(($driver_service == 1 ? $ride_service : '') . ($courier_boy == 1 ? ", ".$courier_delivery : '') . ($delivery_boy == 1 ? ", ".$store_delivery : ''). ($driver_details->rental_service == 1 ? ", ".$rental_service : ''),', ');
            //$service = trim(($driver_service == 1 ? "Bike Riding" : '') . ($courier_boy == 1 ? ", Courier Delivery" : '') . ($delivery_boy == 1 ? ", ".$store_delivery : ''), ', ');
        }
        elseif ($driver_details->service_cat_id == 2) {
            //$service = "Taxi Ride";
            //$service = $ride_service;
            //if ($driver_details->rental_service == 1) {
            //    $service = trim("Ride Service, Rental Service");
            //}
            $service = trim(($driver_service == 1 ? $ride_service : '') . ($courier_boy == 1 ? ", ".$courier_delivery : '') . ($delivery_boy == 1 ? ", ".$store_delivery : '') . ($driver_details->rental_service == 1 ? ", ".$rental_service : ''), ', ');
        }
        elseif ($driver_details->service_cat_id == 4) {
            $service = $courier_delivery;
        }
        else {
            return response()->json([
                "status" => 0,
//                "message" => "something went to wrong!",
                "message" => __('driver_messages.9'),
                "message_code" => 9,
            ]);
        }


        $start_date = date('Y-m-d');
        $total_transport_sales = TransportRideBook::query()
            ->select(
                DB::raw( "count(id) as ride_total_order"),
                DB::raw('SUM(driver_amount) as ride_total_completed_order')
            )
            ->where('driver_id','=',$driver_details_id)
            ->where('status','=',9)
            ->whereDate('created_at', $start_date)->first();

        $total_rental_transport_sales = TransportRentalRideBooking::query()
            ->select(
                DB::raw( "count(id) as rental_ride_total_order"),
                DB::raw('SUM(driver_amount) as rental_ride_total_completed_order')
            )
            ->where('driver_id','=',$driver_details_id)
            ->where('status','=',9)
            ->whereDate('created_at', $start_date)->first();

        $total_delivery_sales = UserProductBooking::query()
            ->select(
                DB::raw( "count(id) as store_total_order"),
                DB::raw('SUM((delivery_cost - delivery_admin_commission)) as store_total_completed_order')
            )
            ->where('delivery_people_id','=',$driver_details_id)
            ->where('status','=',9)
            ->whereDate('created_at', $start_date)->first();

        $user_currency = WorldCurrency::query()->where('symbol', $driver_check->currency)->first();
        if ($user_currency == Null) {
            $user_currency = WorldCurrency::query()->where('default_currency', 1)->first();
        }
        $currency = $user_currency->ratio;

        $ride_total_order = (isset($total_transport_sales['ride_total_order']) ? $total_transport_sales['ride_total_order'] : 0);
        $ride_total_completed_order = (isset($total_transport_sales['ride_total_completed_order']) ? $total_transport_sales['ride_total_completed_order'] : 0);

        $store_total_order = (isset($total_delivery_sales['store_total_order']) ? $total_delivery_sales['store_total_order'] : 0);
        $store_total_completed_order = (isset($total_delivery_sales['store_total_completed_order']) ? $total_delivery_sales['store_total_completed_order'] : 0);

        $rental_ride_total_order = (isset($total_rental_transport_sales['rental_ride_total_order']) ? $total_rental_transport_sales['rental_ride_total_order'] : 0);
        $rental_ride_total_completed_order = (isset($total_rental_transport_sales['rental_ride_total_completed_order']) ? $total_rental_transport_sales['rental_ride_total_completed_order'] : 0);

        $total_order = ($ride_total_order + $store_total_order + $rental_ride_total_order);
        $today_earning = ($ride_total_completed_order + $store_total_completed_order + $rental_ride_total_completed_order);



        return response()->json([
            "status" => 1,
//            "message" => "success!",
            "message" => __('driver_messages.1'),
            "message_code" => 1,
            "service_category_id" => $driver_details->service_cat_id,
            "driver_id" => $driver_details->driver_id,
            "driver_service_id" => $driver_details->driver_service_id,
            "driver_name" => $driver_details->driver_name,
            "driver_profile" => $avatar,
            "driver_current_status" => $driver_details->driver_current_status,
            "driver_service" => $service,
            "vehicle_type_icon" => $driver_details->vehicle_type_icon,
            "vehicle_type_name" => $driver_details->vehicle_type_name,
            "today_order" => $total_order - 0,
            "today_earning" => round(($today_earning * $currency),2)
        ]);
    }

    public function postGetDriverFeedback(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "driver_id" => "required|numeric",
            "access_token" => "required",
            "driver_service_id" => "required|numeric"
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

        $driver_details = TransportDriverDetails::query()->where('provider_service_id', $request->get('driver_service_id'))->first();
        if ($driver_details != Null) {
            $feedback_list = [];
            if ($driver_check->language != "en") {
                $lang_prefix = $driver_check->language."_";
            } else {
                $lang_prefix = "";
            }

            $user_profile_url = url('/assets/images/profile-images/customer');
            $ride_ratings = TransportRatings::query()->select('transport_driver_rating.id as rating_id',
                'user_ride_booking.service_cat_id', 'transport_driver_rating.ride_id as ride_id', DB::raw("concat(users.first_name,'')as user_name"),
                DB::raw("(CASE WHEN users.avatar != '' THEN (CASE WHEN CHAR_LENGTH(users.avatar) >= 25 THEN users.avatar ELSE concat('$user_profile_url','/',users.avatar) END) ELSE '' END) as user_profile_image"),
                'transport_driver_rating.rating', DB::raw("(CASE WHEN transport_driver_rating.comment != '' THEN transport_driver_rating.comment ELSE '' END) as comment"),
                'service_category.' . $lang_prefix . 'name as service_category_name',
                (DB::raw('transport_driver_rating.created_at as datetime'))
                //(DB::raw('DATE_FORMAT(transport_driver_rating.created_at, "%a %d %b, %Y") as datetime'))
            )
                ->join('users', 'users.id', '=', 'transport_driver_rating.user_id')
                ->join('user_ride_booking', 'user_ride_booking.id', '=', 'transport_driver_rating.ride_id')
                ->join('service_category', 'service_category.id', '=', 'user_ride_booking.service_cat_id')
                ->where('transport_driver_rating.status', 1)
                ->where('transport_driver_rating.driver_id', $driver_details->id)
                ->whereNotNull('transport_driver_rating.ride_id')
                ->get()
                ->toArray();

            $ride_rental_ratings = TransportRatings::query()->select('transport_driver_rating.id as rating_id',
                'user_rental_ride_booking.service_cat_id', 'transport_driver_rating.rental_id as ride_id', DB::raw("concat(users.first_name,'')as user_name"),
                DB::raw("(CASE WHEN users.avatar != '' THEN (CASE WHEN CHAR_LENGTH(users.avatar) >= 25 THEN users.avatar ELSE concat('$user_profile_url','/',users.avatar) END) ELSE '' END) as user_profile_image"),
                'transport_driver_rating.rating', DB::raw("(CASE WHEN transport_driver_rating.comment != '' THEN transport_driver_rating.comment ELSE '' END) as comment"),
                'service_category.' . $lang_prefix . 'name as service_category_name',
                (DB::raw('transport_driver_rating.created_at as datetime'))
            //(DB::raw('DATE_FORMAT(transport_driver_rating.created_at, "%a %d %b, %Y") as datetime'))
            )
                ->join('users', 'users.id', '=', 'transport_driver_rating.user_id')
                ->join('user_rental_ride_booking', 'user_rental_ride_booking.id', '=', 'transport_driver_rating.rental_id')
                ->join('service_category', 'service_category.id', '=', 'user_rental_ride_booking.service_cat_id')
                ->where('transport_driver_rating.status', 1)
                ->where('transport_driver_rating.driver_id', $driver_details->id)
                ->whereNotNull('transport_driver_rating.rental_id')
                ->get()
                ->toArray();



            $delivery_ratings = TransportRatings::query()->select('transport_driver_rating.id as rating_id',
                'user_store_product_booking.service_cat_id', 'transport_driver_rating.delivery_id as ride_id', DB::raw("concat(users.first_name,'') as user_name"),
                DB::raw("(CASE WHEN users.avatar != '' THEN (CASE WHEN CHAR_LENGTH(users.avatar) >= 25 THEN users.avatar ELSE concat('$user_profile_url','/',users.avatar) END) ELSE '' END) as user_profile_image"),
                'transport_driver_rating.rating', DB::raw("(CASE WHEN transport_driver_rating.comment != '' THEN transport_driver_rating.comment ELSE '' END) as comment"),
                'service_category.' . $lang_prefix . 'name as service_category_name',
                //(DB::raw('DATE_FORMAT(transport_driver_rating.created_at, "%a %d %b, %Y") as datetime'))
                (DB::raw('transport_driver_rating.created_at as datetime'))
            )
                ->join('users', 'users.id', '=', 'transport_driver_rating.user_id')
                ->join('user_store_product_booking', 'user_store_product_booking.id', '=', 'transport_driver_rating.delivery_id')
                ->join('service_category', 'service_category.id', '=', 'user_store_product_booking.service_cat_id')
                ->where('transport_driver_rating.status', 1)
                ->where('transport_driver_rating.driver_id', $driver_details->id)
                ->whereNotNull('transport_driver_rating.delivery_id')
                ->get()
                ->toArray();
            $feedback_list = array_merge($feedback_list, $ride_ratings);
            $feedback_list = array_merge($feedback_list, $ride_rental_ratings);
            $feedback_list = array_merge($feedback_list, $delivery_ratings);
            if ($feedback_list != Null) {
                usort($feedback_list, function ($a, $b) {
                    if ($a['datetime'] == $b['datetime']) return 0;
                    return $a['datetime'] < $b['datetime'] ? 1 : -1;
                });
            }
            $feedback_data = [];
            foreach ($feedback_list as $feedback){
                $feedback_data[] = [
                    'rating_id' => $feedback['rating_id'],
                    'service_cat_id' => $feedback['service_cat_id'],
                    'ride_id' => $feedback['ride_id'],
                    'user_name' => $feedback['user_name'],
                    'user_profile_image' => $feedback['user_profile_image'],
                    'rating' => $feedback['rating'],
                    'comment' => $feedback['comment'],
                    'service_category_name' => $feedback['service_category_name'],
                    'datetime' => $this->notificationClass->dateLangConvert(Date('D d M, Y', strtotime($feedback['datetime'])),$driver_check->language),
                ];
            }
            return response()->json([
                "status" => 1,
//                "message" => "success!",
                "message" => __('driver_messages.1'),
                "message_code" => 1,
                "feedback_list" => $feedback_data
            ]);
        }
        else {
            return response()->json([
                "status" => 0,
//                "message" => "driver details not find!",
                "message" => __('driver_messages.15'),
                "message_code" => 15,
            ]);
        }
    }

    //pending user side cancel ride delete record from running service record
    public function postDriverUpdateRideStatus(Request $request)
    {
        $this->notificationClass->ApiLogDetail($logger_type = 2, $request->get('driver_id'), "postDriverUpdateRideStatus", $request->all());

        $validator = Validator::make($request->all(), [
            "driver_id" => "required|numeric",
            "access_token" => "required",
            "driver_service_id" => "required|numeric",
            "service_category_id" => "required|numeric|in:1,2,4,5,6,7,8,9,10",
            "ride_id" => "required|numeric",
            "ride_status" => "required|numeric|in:3,4,5,6,7,8,9",
            "way_point_status" => "required|in:0,1,2,3"
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }

        $driver_id = $request->get('driver_id');
        $request_status = $request->get('ride_status');
        $way_point_status = $request->get('way_point_status');
        $driver_check = $this->driverClassApi->checkDriverRegisterAllow($driver_id, $request->get('access_token'), $request->get('driver_service_id'));
        if ($decoded['status'] = json_decode($driver_check) == false) {
            return $driver_check;
        }

        $cancel_message = "";
        $service_category_id = $request->get('service_category_id');
        if (in_array($service_category_id, $this->transport_service_id_array) || in_array($service_category_id, $this->courier_service_id_array)) {
            $ride = TransportRideBook::query()->where('id', $request->get('ride_id'))->first();
            if ($ride != Null) {
                $ride_status = $ride->status;
                $driver_details = TransportDriverDetails::query()->select('transport_driver_details.id', 'provider_services.status', 'provider_services.current_status')
                    ->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
                    ->where('transport_driver_details.provider_service_id', $request->get('driver_service_id'))->first();
                if ($driver_details == Null) {
                    return response()->json([
                        "status" => 0,
//                        "message" => "driver details not found!",
                        "message" => __('driver_messages.15'),
                        "message_code" => 15,
                    ]);
                }
                $user = User::query()->where('id', $ride->user_id)->first();
                if ($user == Null) {
                    return response()->json([
                        "status" => 0,
//                        "message" => "something went to wrong!",
                        "message" => __('driver_messages.9'),
                        "message_code" => 9,
                    ]);
                }
                if ($driver_details->id != $ride->driver_id) {
                    return response()->json([
                        "status" => 0,
//                        "message" => "something went to wrong!",
                        "message" => __('driver_messages.9'),
                        "message_code" => 9,
                    ]);
                }
                if ($ride_status > 0) {
                    if ($ride_status == 9) {
                        $update_driver_status = ProviderServices::query()->where('id', $request->get('driver_service_id'))->first();
                        if ($update_driver_status != Null) {
                            $update_driver_status->current_status = 1;
                            $update_driver_status->save();
                        } else {
                            return response()->json([
                                "status" => 0,
//                                "message" => "something went to wrong!",
                                "message" => __('driver_messages.9'),
                                "message_code" => 9,
                            ]);
                        }
                        //ProviderUserRunningService::query()->where('provider_id', $driver_id)->where('user_id', $ride->user_id)->where('service_cat_id', $service_category_id)->where('booking_id', $ride->id)->delete();
                        return response()->json([
                            "status" => 1,
//                            "message" => "success!",
                            "message" => __('driver_messages.1'),
                            "message_code" => 1,
                            "service_category_id" => $ride->service_cat_id,
                            "ride_id" => $ride->id,
                            "ride_status" => $request_status - 0,
                            "way_point_status" => $way_point_status - 0,
                            "ride_completed_status" => 1,
                            "ride_cancelled_status" => 0,
                            "driver_current_status" => $update_driver_status->current_status - 0,
                            "cancel_by" => $cancel_message
                        ]);
                    }
                    elseif ($ride_status == 4) {
                        $update_driver_status = ProviderServices::query()->where('id', $request->get('driver_service_id'))->first();
                        if ($update_driver_status != Null) {
                            $update_driver_status->current_status = 1;
                            $update_driver_status->save();
                        } else {
                            return response()->json([
                                "status" => 0,
                                //"message" => "something went to wrong!",
                                "message" => __('driver_messages.9'),
                                "message_code" => 9,
                            ]);
                        }
                        $cancel_by = $ride->cancel_by;
                        if ($cancel_by != Null && strtolower($cancel_by) == "admin") {
//                            $cancel_message = "Ride Cancelled by Admin";
                            $cancel_message = __('driver_messages.113');
                            $message_code = 113;
                        } elseif ($cancel_by != Null && strtolower($cancel_by) == "driver"){
                            $message_code = 20;
//                            $cancel_message = "Driver cancel the ride.";
                            $cancel_message = __('driver_messages.20');
                        } else {
//                            $cancel_message = "Ride Cancelled by User";
                            $cancel_message = __('driver_messages.24');
                            $message_code = 24;
                        }
                        //ProviderUserRunningService::query()->where('provider_id', $driver_id)->where('user_id', $ride->user_id)->where('service_cat_id', $service_category_id)->where('booking_id', $ride->id)->delete();
                        return response()->json([
                            "status" => 1,
//                            "message" => "success!",
                            "message" => __('driver_messages.1'),
                            "message_code" => 1,
                            "service_category_id" => $ride->service_cat_id,
                            "ride_id" => $ride->id,
                            "ride_status" => $request_status - 0,
                            "way_point_status" => $way_point_status - 0,
                            "ride_completed_status" => 0,
                            "ride_cancelled_status" => 1,
                            "driver_current_status" => $update_driver_status->current_status - 0,
                            "cancel_by" => $cancel_message
                        ]);
                    }
                    elseif ($ride_status == 7 && $ride->completed_by == 1) {
                        if ($ride->payment_type == 2 || $ride->payment_type == 3) {
                            if ($ride->payment_status != 1)
                            return response()->json([
                                "status" => 0,
//                                "message" => "Payment Process Pending",
                                "message" => __('driver_messages.83'),
                                "message_code" => 83,
                            ]);
                        }
                        $update_driver_status = ProviderServices::query()->where('id', $request->get('driver_service_id'))->first();
                        if ($update_driver_status != Null) {
                            $update_driver_status->current_status = 1;
                            $update_driver_status->save();
                        } else {
                            return response()->json([
                                "status" => 0,
//                                "message" => "something went to wrong!",
                                "message" => __('driver_messages.9'),
                                "message_code" => 9,
                            ]);
                        }
                        ProviderUserRunningService::query()->where('provider_id', $driver_id)->where('user_id', $ride->user_id)->where('service_cat_id', $service_category_id)->where('booking_id', $ride->id)->delete();
                        return response()->json([
                            "status" => 1,
//                            "message" => "success!",
                            "message" => __('driver_messages.1'),
                            "message_code" => 1,
                            "service_category_id" => $ride->service_cat_id,
                            "ride_id" => $ride->id,
                            "ride_status" => $request_status - 0,
                            "way_point_status" => $way_point_status - 0,
                            "ride_completed_status" => 1,
                            "ride_cancelled_status" => 0,
                            "driver_current_status" => $update_driver_status->current_status - 0,
                            "cancel_by" => $cancel_message
                        ]);
                    }
                    else {
                        if ($request_status == 3) {
                            if ($ride_status == 1 || $ride_status == 2) {
                                $ride->status = $request_status;
                                $ride->save();
                                $this->notificationClass->userTransportNotification($ride->id, $user->device_token, $request_status, $user->login_device,$user->language);
                                if ($ride_status == 2) {
                                    $change_driver_status = ProviderServices::query()->where('id', $request->get('driver_service_id'))->first();
                                    if ($change_driver_status == Null) {
                                        return response()->json([
                                            "status" => 0,
//                                            "message" => "something went to wrong!",
                                            "message" => __('driver_messages.9'),
                                            "message_code" => 9,
                                        ]);
                                    }
                                    $change_driver_status->current_status = 0;
                                    $change_driver_status->save();
                                    ProviderUserRunningService::query()->where('provider_id', $driver_id)->where('user_id', $ride->user_id)->where('service_cat_id', $ride->service_cat_id)->where('booking_id', $ride->id)->delete();
                                    $provider_running_service = new ProviderUserRunningService();
                                    $provider_running_service->provider_id = $driver_id;
                                    $provider_running_service->user_id = $ride->user_id;
                                    $provider_running_service->service_cat_id = $ride->service_cat_id;
                                    $provider_running_service->booking_id = $ride->id;
                                    $provider_running_service->save();

                                    $user_running_ride = UserRunningRide::query()->where('service_cat_id', $ride->service_cat_id)->where('user_id', $ride->user_id)->where('booking_id', $ride->id)->first();
                                    if($user_running_ride == Null){
                                        $user_running_ride = new UserRunningRide();
                                        $user_running_ride->user_id = $ride->user_id;
                                        $user_running_ride->service_cat_id = $ride->service_cat_id;
                                        $user_running_ride->booking_id = $ride->id;
                                        $user_running_ride->save();
                                    }
                                }
                                return response()->json([
                                    "status" => 1,
//                                    "message" => "success!",
                                    "message" => __('driver_messages.1'),
                                    "message_code" => 1,
                                    "service_category_id" => $ride->service_cat_id,
                                    "ride_id" => $ride->id,
                                    "otp" => $ride->otp,
                                    "ride_status" => $request_status - 0,
                                    "way_point_status" => $way_point_status - 0,
                                    "ride_completed_status" => 0,
                                    "ride_cancelled_status" => 0,
                                    "driver_current_status" => $driver_details->current_status,
                                    "cancel_by" => $cancel_message
                                ]);
                            } else {
                                return response()->json([
                                    "status" => 0,
//                                    "message" => "something went to wrong!",
                                    "message" => __('driver_messages.9'),
                                    "message_code" => 9,
                                ]);
                            }
                        }
                        elseif ($request_status == 4) {
                            if (in_array($ride_status, [0, 1, 2, 3])) {
                                $validator = Validator::make($request->all(), [
                                    "cancel_reason" => "required",
                                ]);
                                if ($validator->fails()) {
                                    return response()->json([
                                        "status" => 0,
                                        "message" => $validator->errors()->first(),
                                        "message_code" => 9,
                                    ]);
                                }
                                $update_driver_status = ProviderServices::query()->where('id', $request->get('driver_service_id'))->first();
                                if ($update_driver_status == Null) {
                                    return response()->json([
                                        "status" => 0,
//                                        "message" => "something went to wrong!",
                                        "message" => __('driver_messages.9'),
                                        "message_code" => 9,
                                    ]);
                                }
                                $ride_status = $ride->status;
                                $ride->otp = null;
                                $ride->status = $request_status;
                                $ride->cancel_by = "driver";
                                $ride->cancel_reason = $request->get('cancel_reason');
                                $ride->save();

                                $update_driver_status->current_status = 1;
                                $update_driver_status->save();

                                if ($ride_status < 6){
                                    if ($ride->promo_code > 0){
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
                                $this->notificationClass->userTransportNotification($ride->id, $user->device_token, $request_status, $user->login_device,$user->language);
                                ProviderUserRunningService::query()->where('provider_id', $driver_id)->where('user_id', $ride->user_id)->where('service_cat_id', $ride->service_cat_id)->where('booking_id', $ride->id)->delete();
                                UserRunningRide::query()->where('service_cat_id', $ride->service_cat_id)->where('user_id', $ride->user_id)->where('booking_id', $ride->id)->delete();

                                $general_settings = request()->get("general_settings");
                                if ($general_settings !=  Null) {
                                    if ($general_settings->send_mail == 1) {
                                        $service_category = ServiceCategory::query()->where('id', $service_category_id)->first();
                                        if ($service_category != Null){
                                            $email = $user->email;
                                            $service_name = ucwords(strtolower($service_category->name));
                                            $driver_name = ucwords(strtolower($driver_check['first_name']." ". $driver_check['last_name']));
                                            try{
                                                $mail_type = "your_request_rejected/canceled";
                                                $to_mail=$email;
                                                $subject = "Your ".$general_settings->mail_site_name." ".$service_name ." Ride request Rejected/Canceled By ".$driver_name;
                                                $disp_data = array("##user_name##"=>$ride->user_name,"##service_name##"=>$service_name,"##driver_name##"=>$driver_name);

                                                $mail_return_data = $this->notificationClass->sendMail($subject,$to_mail,$mail_type,$disp_data);
                                            } catch (\Exception $e){}
                                        }
                                    }
                                }
                                return response()->json([
                                    "status" => 1,
                                    //"message" => "success!",
                                    "message" => __('driver_messages.1'),
                                    "message_code" => 1,
                                    "service_category_id" => $ride->service_cat_id,
                                    "ride_id" => $ride->id,
                                    "ride_status" => $request_status - 0,
                                    "way_point_status" => $way_point_status - 0,
                                    "ride_completed_status" => 0,
                                    "ride_cancelled_status" => 0,
                                    "driver_current_status" => $update_driver_status->current_status,
                                    "cancel_by" => $cancel_message
                                ]);
                            } else {
                                return response()->json([
                                    "status" => 0,
//                                    "message" => "something went to wrong!",
                                    "message" => __('driver_messages.9'),
                                    "message_code" => 9,
                                ]);
                            }
                        }
                        elseif ($request_status == 5 && in_array($way_point_status, [0, 1, 2, 3])) {
                            if ($ride_status == 4 || $ride_status == 10) {
                                return response()->json([
                                    "status" => 0,
                                    //"message" => "ride cancelled!",
                                    "message" => __('driver_messages.30'),
                                    "message_code" => 30,
                                ]);
                            } elseif ($ride_status >= 3 && $ride_status < 6) {
                                //check code for ride otp verificatino
                                if ($ride->otp != Null) {
                                    $validator = Validator::make($request->all(), [
                                        "otp" => "required|numeric|digits:4"
                                    ]);
                                    if ($validator->fails()) {
                                        return response()->json([
                                            "status" => 0,
                                            "message" => $validator->errors()->first(),
                                            "message_code" => 9,
                                        ]);
                                    }
                                    if($ride->otp != $request->get('otp')) {
                                        return response()->json([
                                            "status" => 0,
                                            //"message" => "Invalid otp!",
                                            "message" => __('driver_messages.89'),
                                            "message_code" => 89,
                                        ]);
                                    }
                                    $ride->otp = Null;
                                    $ride->save();
                                }
                                //End check code for ride otp verificatino

                                if ($ride_status != 5) {
                                    $ride->status = $request_status;
                                    $ride->save();
                                    $this->notificationClass->userTransportNotification($ride->id, $user->device_token, $request_status, $user->login_device,$user->language);
                                }
                                if ($ride->is_way_point == 1) {
                                    $ride_way_point = UserRideWayPoint::query()->where('ride_id', $ride->id)->first();
                                    if ($ride_way_point == Null) {
                                        return response()->json([
                                            "status" => 0,
//                                            "message" => "something went to wrong",
                                            "message" => __('driver_messages.9'),
                                            "message_code" => 9,
                                        ]);
                                    }
                                    $count_ride_way_point = 1;
                                    if ($ride_way_point->way_point_2 != Null && $ride_way_point->lat_long_2 != Null) {
                                        $count_ride_way_point = $count_ride_way_point + 1;
                                    }
                                    if ($ride_way_point->way_point_3 != Null && $ride_way_point->lat_long_3 != Null) {
                                        $count_ride_way_point = $count_ride_way_point + 1;
                                    }
                                    //if ($count_ride_way_point - 1 != $ride->way_point_status) {
                                    //    return response()->json([
                                    //        "status" => 0,
                                    //        "message" => "something went to wrong",
                                    //        "message_code" => 9,
                                    //    ]);
                                    //}
                                    if ($way_point_status <= $count_ride_way_point && $way_point_status > $ride->way_point_status) {
                                        $ride->way_point_status = $way_point_status;
                                        $ride->save();
                                        if($user->login_device > 2)
                                        {
                                            //code for only send notification to fullter for send multiway point notification
                                            $this->notificationClass->userTransportNotification($ride->id, $user->device_token, $request_status, $user->login_device,$user->language);
                                        }

                                    }
                                }
                                return response()->json([
                                    "status" => 1,
//                                    "message" => "success!",
                                    "message" => __('driver_messages.1'),
                                    "message_code" => 1,
                                    "service_category_id" => $ride->service_cat_id,
                                    "ride_id" => $ride->id,
                                    "ride_status" => $request_status - 0,
                                    "way_point_status" => $ride->way_point_status - 0,
                                    "ride_completed_status" => 0,
                                    "ride_cancelled_status" => 0,
                                    "driver_current_status" => $driver_details->current_status,
                                    "cancel_by" => $cancel_message
                                ]);
                            } else {
                                return response()->json([
                                    "status" => 0,
//                                    "message" => "something went to wrong!",
                                    "message" => __('driver_messages.9'),
                                    "message_code" => 9,
                                ]);
                            }

                        }
                        elseif ($request_status == 6) {
                            if ($ride_status == 4 || $ride_status == 10) {
                                return response()->json([
                                    "status" => 0,
//                                    "message" => "ride cancelled!",
                                    "message" => __('driver_messages.30'),
                                    "message_code" => 30,
                                ]);
                            } elseif ($ride_status == 5) {
                                $validator = Validator::make($request->all(), [
                                    "route_lat_long_list" => "required",
                                ]);
                                if ($validator->fails()) {
                                    return response()->json([
                                        "status" => 0,
                                        "message" => $validator->errors()->first(),
                                        "message_code" => 9,
                                    ]);
                                }
                                if ($ride->is_way_point == 1) {
                                    $ride_way_point = UserRideWayPoint::query()->where('ride_id', $ride->id)->first();
                                    if ($ride_way_point == Null) {
                                        return response()->json([
                                            "status" => 0,
//                                            "message" => "You need to drive and move location",
                                            "message" => __('driver_messages.103'),
                                            "message_code" => 103,
                                        ]);
                                    }
                                    $count_ride_way_point = 1;
                                    if ($ride_way_point->way_point_2 != Null && $ride_way_point->lat_long_2 != Null) {
                                        $count_ride_way_point = $count_ride_way_point + 1;
                                    }
                                    if ($ride_way_point->way_point_3 != Null && $ride_way_point->lat_long_3 != Null) {
                                        $count_ride_way_point = $count_ride_way_point + 1;
                                    }
                                    if ($count_ride_way_point != $ride->way_point_status) {
                                        return response()->json([
                                            "status" => 0,
//                                            "message" => "You need to drive and move location",
                                            "message" => __('driver_messages.103'),
                                            "message_code" => 103,
                                        ]);
                                    }

                                }
//                                $route_lat_long_list = json_decode($request->get('route_lat_long_list'), true);
//                                $count_route = count($route_lat_long_list);
//                                $distance = 0;
//                                if ($count_route > 1) {
//                                    try {
//                                        $ride_route = TransportRideRoute::query()->where('ride_id', $ride->id)->first();
//                                        if ($ride_route == Null) {
//                                            $ride_route = new TransportRideRoute();
//                                        }
//                                        $ride_route->ride_id = $ride->id;
//                                        $ride_route->ride_route = $request->get('route_lat_long_list');
//                                        $ride_route->save();
//
//                                    } catch (\Exception $e) {
//                                        if ($e != Null) {
//                                            $ride_route = new TransportRideRoute();
//                                            $ride_route->ride_id = $ride->id;
//                                            $ride_route->ride_route = $request->get('route_lat_long_list');
//                                            $ride_route->save();
//                                        }
//                                    }
//                                    for ($i = 0; $i < $count_route - 1; $i++) {
//                                        $get_distance = $this->driverClassApi->getRideDistance($route_lat_long_list[$i]['lat'], $route_lat_long_list[$i]['lng'], $route_lat_long_list[$i + 1]['lat'], $route_lat_long_list[$i + 1]['lng']);
//                                        $distance = $distance + $get_distance;
//                                    }
//
//                                }
////                                $route_lat_long_list = json_decode($request->get('route_lat_long_list'), true);
////                                if (count($route_lat_long_list) > 0) {
////                                    $ride_route = TransportRideRoute::where('ride_id', $ride->id)->first();
////                                    if ($ride_route == Null) {
////                                        $ride_route = new TransportRideRoute();
////                                    }
////                                    $ride_route->ride_id = $ride->id;
////                                    $ride_route->ride_route = $request->get('route_lat_long');
////                                    $ride_route->save();
////
////                                    $origin = urlencode($ride->pickup_address);
////                                    $destination_check = explode(",", $ride->destination_latlong);
////                                    if ($ride->destination_latlong != Null && $destination_check != Null) {
////                                        $destination = trim($destination_check[0]) . "," . trim($destination_check[1]);
////                                    } else {
////                                        $destination = [Null, Null];
////                                    }
////
//////                                $route = TransportRideRoute::where('ride_id', '=', $ride->id)->first();
////                                    $km_count = 0;
//////                                $eta = 0;
//////                                $eta_val = 0;
////                                    $lat_plus = Null;
////                                    $lat_long_get_array = [];
////                                    $lat_longs = json_decode($ride_route->ride_route, TRUE);
////                                    $lat_longs_count = count($lat_longs);
////                                    $get_value = 20;
////                                    $total_value = $lat_longs_count / $get_value;
////                                    if ($lat_longs_count > 0) {
////                                        $lat_plus = "";
////                                        for ($i = 0; $i <= $lat_longs_count; $i = $i + $total_value) {
////                                            if ($lat_longs_count > $i) {
////                                                $lat_plus = $lat_plus . "|via:" . $lat_longs[$i]['latitude'] . "," . $lat_longs[$i]['longitude'];
////                                            }
////                                        }
////                                    }
////                                    if ($lat_plus != Null) {
////                                        $origin_waypoint = $origin . "&waypoints=optimize:true" . $lat_plus;
////                                    } else {
////                                        $origin_waypoint = $origin;
////                                    }
////
////                                    $coordinates1 = $ride_route->get_coordinates($origin_waypoint, $destination);
////                                    $coord = json_decode($coordinates1, TRUE);
////                                    if ($coord['status'] == "OK") {
////                                        $km = preg_replace("/[^0-9.]/", "", $coord['routes'][0]['legs'][0]['distance']['text']);
//////                                    $eta = $coord['routes'][0]['legs'][0]['duration']['text'];
//////                                    $eta_val = $coord['routes'][0]['legs'][0]['duration']['value'];
////                                        $km_count = $km_count + $km;
////                                        $lat_long_get = $coord['routes'][0]['legs'][0]['steps'];
////                                        for ($i = 0; $i < count($lat_long_get); $i++) {
////                                            $lat_long_get_array[] = [
////                                                "latitude" => $coord['routes'][0]['legs'][0]['steps'][$i]['start_location']['lat'],
////                                                "longitude" => $coord['routes'][0]['legs'][0]['steps'][$i]['start_location']['lng']
////                                            ];
////                                        }
////
////                                        $get_vehicle_price = TransportVehicleType::where('id', $ride->vehicle_type_id)->first();
////                                        if ($get_vehicle_price == Null) {
////                                            return response()->json([
////                                                "status" => 0,
////                                                "message" => "Vehicle Type Not Found!"
////                                            ]);
////                                        }
////                                        $service_settings = ServiceSettings::where('service_cat_id', $ride->service_cat_id)->first();
////                                        if ($service_settings != Null) {
////                                            $admin_commission = $service_settings->admin_commission;
////                                            $get_tax = $service_settings->tax;
////                                        } else {
////                                            $admin_commission = 5;
////                                            $get_tax = 5;
////                                        }
////                                        $ride->total_distance = $km_count;
////                                        $ride->save();
////                                        $ride->BookingCost($ride->total_distance, $get_vehicle_price->cost_for_km, $get_tax, $admin_commission);
////                                    }
////                                }
//
//                                // Comment just for demo product start
//                                /*if ($distance == 0 || $distance == NAN) {
//                                    return response()->json([
//                                        "status" => 0,
//                                        "message" => "something went to wrong",
//                                        "message_code" => 103,
//                                    ]);
//                                }*/
//                                // Comment just for demo product end
//
//                                $get_vehicle_price = TransportVehicleType::query()->where('id', $ride->vehicle_type_id)->first();
//                                if ($get_vehicle_price == Null) {
//                                    \Log::info("vehicle type not found");
//                                    return response()->json([
//                                        "status" => 0,
//                                        "message" => "Vehicle Type Not Found!",
//                                        "message_code" => 9,
//                                    ]);
//                                }
//                                $service_settings = ServiceSettings::query()->where('service_cat_id', $ride->service_cat_id)->first();
//                                if ($service_settings != Null) {
//                                    $admin_commission = $service_settings->admin_commission;
//                                    $get_tax = $service_settings->tax;
//                                } else {
//                                    $admin_commission = 0;
//                                    $get_tax = 0;
//                                }
//
////                                    return $km_count;
//                                $ride->destination_datetime = date('y-m-d H:i:s');
//                                $first_date = ($ride->pickup_datetime);
//                                $second_date = ($ride->destination_datetime);
//                                $diff_date_time = abs(strtotime($second_date) - strtotime($first_date));
////                        $years   = floor($diff_date_time / (365*60*60*24));
////                        $months  = floor(($diff_date_time - $years * 365*60*60*24) / (30*60*60*24));
////                        $days    = floor(($diff_date_time - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
////                        $hours   = floor(($diff_date_time - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24)/ (60*60));
////                        $minutes  = floor(($diff_date_time - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24 - $hours*60*60)/ 60);
////                        $seconds = floor(($diff_date_time - $years * 365*60*60*24 - $months*30*60*60*24 - $days*60*60*24 - $hours*60*60 - $minuts*60));
//                                $count_minutes = round(($diff_date_time) / 60);
//                                $ride->eta = $count_minutes . " min";
//                                $ride->save();
//
//                                $get_ride_details = TransportRideBook::query()->where('id', $ride->id)->first();
//                                $get_ride_details->total_distance = round($distance, 2) . " km";
//                                $get_ride_details->save();
//
////                            if ($request['promo_code'] != 0) {
////                                $discount_on_amount = ($ride->base_fare + $ride->total_distance_amount);
////                                $promo_code = $this->checkPromoCodeValid($request['promo_code'], $discount_on_amount, $user_details->id, $request['service_category_id']);
////                                if ($promo_code != 0) {
////                                    $get_prmocode = PromocodeDetails::where('id', $request['promo_code'])->where('status', 1)->first();
////                                    if ($get_prmocode != Null) {
////                                        $promo_code_discount = $promo_code;
//////                            $place_order->total_pay = round($place_order->total_pay - $promo_code, 2);
////
////                                        $add_promocode_details = new UsedPromocodeDetails();
////                                        $add_promocode_details->service_cat_id = $request['service_category_id'];
////                                        $add_promocode_details->user_id = $user_details->id;
////                                        $add_promocode_details->promocode_id = $get_prmocode->id;
////                                        $add_promocode_details->promocode_name = $get_prmocode->promo_code;
////                                        $add_promocode_details->discount_amount = $promo_code;
////                                        $add_promocode_details->save();
////
//////                    $ride->promo_code = $add_promocode_details->id;
////                                        $ride->promo_code = $get_prmocode;
////                                        $ride->save();
////                                    }
////                                }
////                            }
//                                $get_ride_details->BookingCountDistanceAmount($get_ride_details->total_distance, $get_vehicle_price->cost_for_km, $get_vehicle_price->base_fare, $get_vehicle_price->time_fare, $get_ride_details->eta);
//                                $promo_code_discount = 0;
//                                if ($get_ride_details->promo_code != 0) {
//                                    $used_promocode_details = UsedPromocodeDetails::query()->where('id', $get_ride_details->promo_code)->first();
//                                    if ($used_promocode_details != Null) {
//                                        $get_promocode = PromocodeDetails::query()->where('id', $used_promocode_details->promocode_id)->first();
//                                        if ($get_promocode != Null) {
//                                            //$amount = ($ride->base_fare + $ride->total_distance_amount + $ride->time_fare_amount);
//
//                                            $get_discount_on_amount = ($ride->base_fare + $ride->total_distance_amount + $ride->time_fare_amount);
//                                            $min_fare_amount = ($get_vehicle_price->min_fare_amount);
//                                            $adjustment_amount = 0;
//                                            if ($get_discount_on_amount < $min_fare_amount) {
//                                                $adjustment_amount = round(($min_fare_amount - $get_discount_on_amount), 2); //adjust amount
//                                            }
//                                            $amount = ($get_discount_on_amount + $adjustment_amount);
//                                            if ($get_promocode->discount_type == 1) {
//                                                $discount_promo = round($amount - (($amount) - ($get_promocode->discount_amount)), 2);
//                                            } elseif ($get_promocode->discount_type == 2) {
//                                                $discount_promo = round((($amount) - (($amount) * ($get_promocode->discount_amount)) / (100)), 2);
//                                            } else {
//                                                $discount_promo = 0;
//                                            }
//                                            $used_promocode_details->discount_amount = $discount_promo;
//                                            $used_promocode_details->save();
//                                            $promo_code_discount = $discount_promo;
//                                        } else {
//                                            UsedPromocodeDetails::query()->where('id', $get_ride_details->promo_code)->delete();
//                                            $get_ride_details->promo_code = 0;
//                                            $get_ride_details->save();
//                                        }
//                                    } else {
//                                        $get_ride_details->promo_code = 0;
//                                        $get_ride_details->save();
//                                    }
//                                }
//                                $get_total_ride_details = TransportRideBook::query()->where('id', $ride->id)->first();
//                                $get_total_ride_details->BookingCostCount($get_total_ride_details->base_fare, $get_ride_details->total_distance_amount, $promo_code_discount, $get_tax, $admin_commission, $get_vehicle_price->min_fare_amount, $get_total_ride_details->time_fare_amount);
//
//                                if ($get_total_ride_details->refer_discount != 0) {
//                                    $total_pay = round($get_total_ride_details->total_pay - $get_total_ride_details->refer_discount, 2);
//                                    if ($total_pay < 0) {
//                                        $total_pay = 0;
//                                    }
//                                    $get_total_ride_details->total_pay = $total_pay;
//                                    $get_total_ride_details->save();
//                                }
                                if ($ride->promo_code > 0){
                                    $used_promocode_details = UsedPromocodeDetails::query()->where('id', $ride->promo_code)->first();
                                    if ($used_promocode_details != Null) {
                                        $used_promocode_details->status = 1;
                                        $used_promocode_details->save();
                                    }
                                }

                                //change ride status
                                $ride->status = $request_status;
                                $ride->save();
                                $this->notificationClass->userTransportNotification($ride->id, $user->device_token, $request_status, $user->login_device,$user->language);

                                return response()->json([
                                    "status" => 1,
//                                    "message" => "success!",
                                    "message" => __('driver_messages.1'),
                                    "message_code" => 1,
                                    "service_category_id" => $ride->service_cat_id,
                                    "ride_id" => $ride->id,
                                    "ride_status" => $request_status - 0,
                                    "way_point_status" => $way_point_status - 0,
                                    "ride_completed_status" => 0,
                                    "ride_cancelled_status" => 0,
                                    "driver_current_status" => $driver_details->current_status,
                                    "cancel_by" => $cancel_message
                                ]);
                            } else {
                                return response()->json([
                                    "status" => 0,
//                                    "message" => "something went to wrong!",
                                    "message" => __('driver_messages.9'),
                                    "message_code" => 9,
                                ]);
                            }
                        }
                        elseif ($request_status == 7) {
                            if ($ride_status == 4 || $ride_status == 10) {
                                return response()->json([
                                    "status" => 0,
//                                    "message" => "ride cancelled!",
                                    "message" => __('driver_messages.30'),
                                    "message_code" => 30,
                                ]);
                            } elseif ($ride_status == 6) {
                                if ($ride->payment_status == 0) {
                                    $validator = Validator::make($request->all(), [
                                        "total_amount" => "required",
                                    ]);
                                    if ($validator->fails()) {
                                        return response()->json([
                                            "status" => 0,
                                            "message" => $validator->errors()->first(),
                                            "message_code" => 9,
                                        ]);
                                    }
                                    //if ($ride->total_pay == $request->get('total_amount')) {
                                    //    if ($ride->payment_type == 1 || $ride->payment_type == 2 || $ride->payment_type == 3) {
                                    //        return response()->json([
                                    //            "status" => 0,
                                    //            "message" => "Payment Process Pending",
                                    //            "message_code" => 83,
                                    //        ]);
                                    //    }
                                    $ride->status = $request_status;
                                    //$ride->payment_status = 1;
                                    $ride->save();
                                    return response()->json([
                                        "status" => 1,
//                                        "message" => "success!",
                                        "message" => __('driver_messages.1'),
                                        "message_code" => 1,
                                        "service_category_id" => $ride->service_cat_id,
                                        "ride_id" => $ride->id,
                                        "ride_status" => $request_status - 0,
                                        "ride_completed_status" => 0,
                                        "ride_cancelled_status" => 0,
                                        "driver_current_status" => $driver_details->current_status,
                                        "cancel_by" => $cancel_message
                                    ]);
                                } else {
                                    $ride->status = $request_status;
                                    $ride->save();
                                    return response()->json([
                                        "status" => 1,
//                                        "message" => "success!",
                                        "message" => __('driver_messages.1'),
                                        "message_code" => 1,
                                        "service_category_id" => $ride->service_cat_id,
                                        "ride_id" => $ride->id,
                                        "ride_status" => $request_status - 0,
                                        "way_point_status" => $way_point_status - 0,
                                        "ride_completed_status" => 0,
                                        "ride_cancelled_status" => 0,
                                        "driver_current_status" => $driver_details->current_status,
                                        "cancel_by" => $cancel_message
                                    ]);
                                }
                            } else {
                                return response()->json([
                                    "status" => 0,
//                                    "message" => "something went to wrong!",
                                    "message" => __('driver_messages.9'),
                                    "message_code" => 9,
                                ]);
                            }
                        }
                        elseif ($request_status == 8) {
                            if ($ride_status == 4 || $ride_status == 10) {
                                return response()->json([
                                    "status" => 0,
//                                    "message" => "ride cancelled!",
                                    "message" => __('driver_messages.65'),
                                    "message_code" => 65,
                                ]);
                            } elseif ($ride_status == 6 || $ride_status == 7) {
                                $validator = Validator::make($request->all(), [
                                    "rating" => "numeric",
                                    "comment" => "nullable"
                                ]);
                                if ($validator->fails()) {
                                    return response()->json([
                                        "status" => 0,
                                        "message" => $validator->errors()->first(),
                                        "message_code" => 9,
                                    ]);
                                }
                                //if ($ride->payment_status == 0) {
                                //    return response()->json([
                                //        "status" => 0,
                                //        "message" => "payment process pending!",
                                //        "message_code" => 83,
                                //    ]);
                                //}
                                $ride->status = $request_status;
                                $ride->save();
                                if ($request->get('rating') != Null || $request->get('comment') != Null) {
                                    if ($request->get('rating') != Null) {
                                        $user_rating = new UserRatings();
                                        $user_rating->user_id = $ride->user_id;
                                        $user_rating->provider_id = $driver_id;
                                        $user_rating->ride_book_id = $ride->id;
                                        $user_rating->rating = round($request->get('rating'), 2);
                                        $user_rating->comment = $request->get('comment') != Null ? trim($request->get('comment')) : Null;
                                        $user_rating->save();

                                        $user_details = User::query()->where('id', $ride->user_id)->first();
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
                                return response()->json([
                                    "status" => 1,
//                                    "message" => "success!",
                                    "message" => __('driver_messages.1'),
                                    "message_code" => 1,
                                    "service_category_id" => $ride->service_cat_id,
                                    "ride_id" => $ride->id,
                                    "ride_status" => $request_status - 0,
                                    "way_point_status" => $way_point_status - 0,
                                    "ride_completed_status" => 0,
                                    "ride_cancelled_status" => 0,
                                    "driver_current_status" => $driver_details->current_status,
                                    "cancel_by" => $cancel_message
                                ]);
                            } else {
                                return response()->json([
                                    "status" => 0,
//                                    "message" => "something went to wrong!",
                                    "message" => __('driver_messages.9'),
                                    "message_code" => 9,
                                ]);
                            }
                        }
                        elseif ($request_status == 9) {
                            if ($ride_status == 4 || $ride_status == 10) {
                                return response()->json([
                                    "status" => 0,
//                                    "message" => "ride cancelled!",
                                    "message" => __('driver_messages.65'),
                                    "message_code" => 65,
                                ]);
                            } elseif ($ride_status == 6 || $ride_status == 7 || $ride_status == 8) {
                                //if ($ride->payment_status == 0) {
                                //    return response()->json([
                                //        "status" => 0,
                                //        "message" => "payment process pending!",
                                //        "message_code" => 83,
                                //    ]);
                                //}
                                $update_driver_status = ProviderServices::query()->where('id', $request->get('driver_service_id'))->first();
                                if ($update_driver_status == Null) {
                                    return response()->json([
                                        "status" => 0,
//                                        "message" => "something went to wrong!",
                                        "message" => __('driver_messages.9'),
                                        "message_code" => 9,
                                    ]);
                                }
                                $ride->status = $request_status;
                                $ride->save();

                                $update_driver_status->current_status = 1;
                                $update_driver_status->save();
                                $this->notificationClass->userTransportNotification($ride->id, $user->device_token, $request_status, $user->login_device,$user->language);

                                ProviderUserRunningService::query()->where('provider_id', $driver_id)->where('user_id', $ride->user_id)->where('service_cat_id', $service_category_id)->where('booking_id', $ride->id)->delete();

                                $general_settings = request()->get("general_settings");
                                if ($general_settings !=  Null){
                                    if ($general_settings->send_mail == 1) {
                                        $service_category = ServiceCategory::query()->where('id', $service_category_id)->first();
                                        if ($service_category != Null) {
                                            //sending mail user
                                            $email = $user->email;
                                            $service_name = ucwords(strtolower($service_category->name));
                                            $ride_id =  "#" . $ride->ride_no;
                                            $date_time = date("Y-m-d h:i:s",strtotime($ride->updated_at));
                                            try{
                                                $mail_type = "request_completed";
                                                $to_mail = $email;
                                                $subject = "Your ".$general_settings->mail_site_name." ".$service_name ." Ride Request Completed";
                                                $disp_data = array("##user_name##"=>$ride->user_name,"##service_name##"=>$service_name,"##ride_id##"=>$ride_id );
                                                $mail_return_data = $this->notificationClass->sendMail($subject,$to_mail,$mail_type,$disp_data);
                                            }
                                            catch (\Exception $e){}

                                            //sending mail driver
                                            try{
                                                $email = $driver_check['email'];
                                                $driver_name = ucwords($driver_check['first_name']);
                                                $mail_type = "driver_ride_completed_-_transport";
                                                $to_mail = $email;
                                                $subject = "".$service_name ." Ride Completed";
                                                $disp_data = array("##driver_name##"=>$driver_name,"##service_name##"=>$service_name,"##ride_id##"=>$ride_id,"##date_time##"=>$date_time );
                                                $mail_return_data = $this->notificationClass->sendMail($subject,$to_mail,$mail_type,$disp_data);
                                            }
                                            catch (\Exception $e){}
                                        }
                                    }
                                }
                                return response()->json([
                                    "status" => 1,
//                                    "message" => "success!",
                                    "message" => __('driver_messages.1'),
                                    "message_code" => 1,
                                    "service_category_id" => $ride->service_cat_id,
                                    "ride_id" => $ride->id,
                                    "ride_status" => $request_status - 0,
                                    "way_point_status" => $way_point_status - 0,
                                    "ride_completed_status" => 0,
                                    "ride_cancelled_status" => 0,
                                    "driver_current_status" => $update_driver_status->current_status - 0,
                                    "cancel_by" => $cancel_message
                                ]);
                            } else {
                                return response()->json([
                                    "status" => 0,
//                                    "message" => "something went to wrong!",
                                    "message" => __('driver_messages.9'),
                                    "message_code" => 9,
                                ]);
                            }
                        } else {
                            return response()->json([
                                "status" => 0,
//                                "message" => "something went to wrong!",
                                "message" => __('driver_messages.9'),
                                "message_code" => 9,
                            ]);
                        }
                    }
                } else {
                    return response()->json([
                        "status" => 0,
//                        "message" => "ride not accepting by any driver!",
                        "message" => __('driver_messages.84'),
                        "message_code" => 84,
                    ]);
                }
            }
            else {
                return response()->json([
                    "status" => 0,
//                    "message" => "ride not found!",
                    "message" => __('driver_messages.26'),
                    "message_code" => 26,
                ]);
            }
        }
        elseif (in_array($service_category_id, $this->delivery_service_id_array)) {
            $ride = UserProductBooking::query()->where('id', $request->get('ride_id'))->first();
            if ($ride != Null) {
                $ride_status = $ride->order_delivery_status;
                $driver_details = TransportDriverDetails::query()->select('transport_driver_details.id', 'provider_services.status', 'provider_services.current_status')
                    ->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
                    ->where('transport_driver_details.provider_service_id', $request->get('driver_service_id'))->first();
                if ($driver_details == Null) {
                    return response()->json([
                        "status" => 0,
//                        "message" => "driver details not found!",
                        "message" => __('driver_messages.15'),
                        "message_code" => 15,
                    ]);
                }
                $store = StoreDetails::query()->select('providers.id', 'providers.device_token', 'providers.login_device', 'providers.language')
                    ->join('provider_services', 'provider_services.id', 'store_details.provider_service_id')
                    ->join('providers', 'providers.id', 'provider_services.provider_id')
                    ->where('store_details.id', $ride->store_id)->first();
                if ($store == Null) {
                    return response()->json([
                        "status" => 0,
//                        "message" => "something went to wrong!",
                        "message" => __('driver_messages.9'),
                        "message_code" => 9,
                    ]);
                }
                if ($driver_details->id != $ride->delivery_people_id) {
                    return response()->json([
                        "status" => 0,
//                        "message" => "something went to wrong!",
                        "message" => __('driver_messages.9'),
                        "message_code" => 9,
                    ]);
                }
                if ($ride_status > 0) {

                    if ($ride_status == 9 && $ride->status == 9) {
                        return response()->json([
                            "status" => 1,
//                            "message" => "success!",
                            "message" => __('driver_messages.1'),
                            "message_code" => 1,
                            "service_category_id" => $ride->service_cat_id,
                            "ride_id" => $ride->id,
                            "ride_status" => $ride_status,
                            "driver_current_status" => $driver_details->current_status,
                            "ride_completed_status" => 1,
                            "ride_cancelled_status" => 0,
                            "cancel_by" => $cancel_message
                        ]);
                    } elseif ($ride->status == 4) {
                        $update_driver_status = ProviderServices::query()->where('id', $request->get('driver_service_id'))->first();
                        if ($update_driver_status != Null) {
                            $update_driver_status->current_status = 1;
                            $update_driver_status->save();
                        } else {
                            return response()->json([
                                "status" => 0,
                                //"message" => "something went to wrong!",
                                "message" => __('driver_messages.9'),
                                "message_code" => 9,
                            ]);
                        }
                        $cancel_by = $ride->cancel_by;
                        if ($cancel_by != Null && strtolower($cancel_by) == "admin") {
//                            $cancel_message = "Ride Cancelled by Admin";
                            $cancel_message = __('driver_messages.111');
                            $message_code = 111;
                        } elseif ($cancel_by != Null && strtolower($cancel_by) == "driver"){
                            $message_code = 20;
//                            $cancel_message = "Driver cancel the ride.";
                            $cancel_message = __('driver_messages.20');
                        } else {
//                            $cancel_message = "Ride Cancelled by User";
                            $cancel_message = __('driver_messages.36');
                            $message_code = 36;
                        }
                        return response()->json([
                            "status" => 1,
//                            "message" => "success!",
                            "message" => __('driver_messages.1'),
                            "service_category_id" => $ride->service_cat_id,
                            "ride_id" => $ride->id,
                            "ride_status" => $ride_status,
                            "driver_current_status" => $driver_details->current_status,
                            "ride_completed_status" => 0,
                            "ride_cancelled_status" => 1,
                            "message_code" => 1,
                            "cancel_by" => $cancel_message
                        ]);
                    } else {
                        if ($request_status == 3) {
                            if ($ride_status == 1 || $ride_status == 2) {
                                $user = User::query()->where('id', $ride->user_id)->first();
                                if ($user == Null) {
                                    return response()->json([
                                        "status" => 0,
//                                        "message" => "something went to wrong!",
                                        "message" => __('driver_messages.9'),
                                        "message_code" => 9,
                                    ]);
                                }
                                if (in_array($ride->status, [2, 5]) && $ride->status < 7) {
                                    $ride->status = 7;
                                    $ride->save();

                                    $check_duplicate = UserProductBookingStatus::query()->where('order_id', $ride->id)->where('status', 7)->first();
                                    if ($check_duplicate == Null) {
                                        $store_delivery_status = new UserProductBookingStatus();
                                        $store_delivery_status->order_id = $ride->id;
                                        $store_delivery_status->status = 7;
                                        $store_delivery_status->save();
                                    }
                                }

                                $ride->order_delivery_status = $request_status;
                                $ride->save();

                                $this->notificationClass->storeDeliveryDriverNotification($ride->id, $store->device_token, $ride->order_delivery_status, $store->login_device,$store->language);

                                return response()->json([
                                    "status" => 1,
//                                    "message" => "success!",
                                    "message" => __('driver_messages.1'),
                                    "message_code" => 1,
                                    "service_category_id" => $ride->service_cat_id,
                                    "ride_id" => $ride->id,
                                    "otp" => $ride->otp,
                                    "ride_status" => $request_status - 0,
                                    "way_point_status" => 0,
                                    "ride_completed_status" => 0,
                                    "ride_cancelled_status" => 0,
                                    "driver_current_status" => $driver_details->current_status,
                                    "cancel_by" => $cancel_message
                                ]);
                            } else {
                                return response()->json([
                                    "status" => 0,
//                                    "message" => "something went to wrong!",
                                    "message" => __('driver_messages.9'),
                                    "message_code" => 9,
                                ]);
                            }
                        } elseif ($request_status == 4) {
                            if ($ride_status == 0 || $ride_status == 1 || $ride_status == 2 || $ride_status == 3) {
//                        $validator = Validator::make($request->all(), [
//                                "cancel_reason" => "required",
//                            ]
//                        );
//                        if ($validator->fails()) {
//                            return response()->json([
//                                "status" => 0,
//                                "message" => $validator->errors()->first()
//                            ]);
//                        }

                                $ride->order_delivery_status = $request_status;
//                        $ride->cancel_by = "driver";
//                        $ride->cancel_reason = $request->get('cancel_reason');
                                $ride->save();

                                $update_driver_status = ProviderServices::query()->where('id', $request->get('driver_service_id'))->first();
                                if ($update_driver_status != Null) {
                                    $update_driver_status->current_status = 1;
                                    $update_driver_status->save();
                                } else {
                                    return response()->json([
                                        "status" => 0,
//                                        "message" => "something went to wrong!",
                                        "message" => __('driver_messages.9'),
                                        "message_code" => 9,
                                    ]);
                                }
                                ProviderUserRunningService::query()->where('provider_id', $driver_id)->where('user_id', $ride->user_id)->delete();
                                return response()->json([
                                    "status" => 1,
//                                    "message" => "success!",
                                    "message" => __('driver_messages.1'),
                                    "message_code" => 1,
                                    "service_category_id" => $ride->service_cat_id,
                                    "ride_id" => $ride->id,
                                    "ride_status" => $request_status - 0,
                                    "way_point_status" => 0,
                                    "ride_completed_status" => 0,
                                    "ride_cancelled_status" => 0,
                                    "driver_current_status" => $update_driver_status->current_status,
                                    "cancel_by" => $cancel_message
                                ]);
                            } else {
                                return response()->json([
                                    "status" => 0,
//                                    "message" => "something went to wrong!",
                                    "message" => __('driver_messages.9'),
                                    "message_code" => 9,
                                ]);
                            }
                        } elseif ($request_status == 5) {
                            if ($ride_status == 4 || $ride_status == 10) {
                                return response()->json([
                                    "status" => 0,
//                                    "message" => "ride cancelled!",
                                    "message" => __('driver_messages.65'),
                                    "message_code" => 65,
                                ]);
                            } elseif ($ride_status == 3) {
                                $ride->order_delivery_status = $request_status;
                                $ride->save();

                                $user = User::query()->where('id', $ride->user_id)->first();
                                if ($user == Null) {
                                    return response()->json([
                                        "status" => 0,
//                                        "message" => "something went to wrong!",
                                        "message" => __('driver_messages.9'),
                                        "message_code" => 9,
                                    ]);
                                }
                                if (in_array($ride->status, [2, 5, 6, 7]) && $ride->status < 8) {
                                    $ride->status = 8;
                                    $ride->save();

                                    $check_duplicate = UserProductBookingStatus::query()->where('order_id', $ride->id)->where('status', 8)->first();
                                    if ($check_duplicate == Null) {
                                        $store_delivery_status = new UserProductBookingStatus();
                                        $store_delivery_status->order_id = $ride->id;
                                        $store_delivery_status->status = 8;
                                        $store_delivery_status->save();
                                    }

                                    $this->notificationClass->userStoreDeliveryNotification($ride->id, $user->device_token, $ride->status, $user->login_device, $user->language);
                                }

                                return response()->json([
                                    "status" => 1,
//                                    "message" => "success!",
                                    "message" => __('driver_messages.1'),
                                    "message_code" => 1,
                                    "service_category_id" => $ride->service_cat_id,
                                    "ride_id" => $ride->id,
                                    "otp" => $ride->otp,
                                    "ride_status" => $request_status - 0,
                                    "way_point_status" => 0,
                                    "ride_completed_status" => 0,
                                    "ride_cancelled_status" => 0,
                                    "driver_current_status" => $driver_details->current_status,
                                    "cancel_by" => $cancel_message
                                ]);
                            } else {
                                return response()->json([
                                    "status" => 0,
//                                    "message" => "order process is pending!",
                                    "message" => __('driver_messages.85'),
                                    "message_code" => 85,
                                ]);
                            }
                        } elseif ($request_status == 6) {
                            if ($ride_status == 4 || $ride_status == 10) {
                                return response()->json([
                                    "status" => 0,
//                                    "message" => "ride cancelled!",
                                    "message" => __('driver_messages.65'),
                                    "message_code" => 65,
                                ]);
                            } elseif ($ride_status == 5) {

                                //otp verification before drop order
                                    $validator = Validator::make($request->all(), [
                                        "otp" => "required|numeric|digits:4"
                                    ]);
                                    if ($validator->fails()) {
                                        return response()->json([
                                            "status" => 0,
                                            "message" => $validator->errors()->first(),
                                            "message_code" => 9,
                                        ]);
                                    }
                                    if($ride->otp != $request->get('otp')) {

                                        return response()->json([
                                            "status" => 0,
//                                            "message" => "Invalid otp!",
                                            "message" => __('driver_messages.89'),
                                            "message_code" => 89,
                                        ]);
                                    }


                                //End otp verification before drop order
                                $ride->otp = Null;
                                $ride->order_delivery_status = $request_status;
                                $ride->save();

                                return response()->json([
                                    "status" => 1,
//                                    "message" => "success!",
                                    "message" => __('driver_messages.1'),
                                    "message_code" => 1,
                                    "service_category_id" => $ride->service_cat_id,
                                    "ride_id" => $ride->id,
                                    "ride_status" => $request_status - 0,
                                    "way_point_status" => 0,
                                    "ride_completed_status" => 0,
                                    "ride_cancelled_status" => 0,
                                    "driver_current_status" => $driver_details->current_status,
                                    "cancel_by" => $cancel_message
                                ]);
                            } else {
                                return response()->json([
                                    "status" => 0,
//                                    "message" => "something went to wrong!",
                                    "message" => __('driver_messages.9'),
                                    "message_code" => 9,
                                ]);
                            }
                        } elseif ($request_status == 7) {
                            if ($ride_status == 4 || $ride_status == 10) {
                                return response()->json([
                                    "status" => 0,
//                                    "message" => "ride cancelled!",
                                    "message" => __('driver_messages.65'),
                                    "message_code" => 65,
                                ]);
                            } elseif ($ride_status == 6) {
                                if ($ride->payment_status == 0) {
                                    $validator = Validator::make($request->all(), [
                                            "total_amount" => "required",
                                        ]
                                    );
                                    if ($validator->fails()) {
                                        return response()->json([
                                            "status" => 0,
                                            "message" => $validator->errors()->first(),
                                            "message_code" => 9,
                                        ]);
                                    }
//                                if ($ride->total_pay == $request->get('total_amount')) {
                                    $ride->order_delivery_status = $request_status;
                                    $ride->payment_status = 1;
                                    $ride->save();
                                    $this->notificationClass->storeDeliveryDriverNotification($ride->id, $store->device_token, $ride->order_delivery_status, $store->login_device,$store->language);
                                    return response()->json([
                                        "status" => 1,
//                                        "message" => "success!",
                                        "message" => __('driver_messages.1'),
                                        "message_code" => 1,
                                        "service_category_id" => $ride->service_cat_id,
                                        "ride_id" => $ride->id,
                                        "ride_status" => $request_status - 0,
                                        "way_point_status" => 0,
                                        "ride_completed_status" => 0,
                                        "ride_cancelled_status" => 0,
                                        "driver_current_status" => $driver_details->current_status,
                                        "cancel_by" => $cancel_message
                                    ]);
//                                } else {
//                                    return response()->json([
//                                        "status" => 0,
//                                        "message" => "amount not match!",
//                                        "message_code" => 82,
//                                    ]);
//                                }
                                } else {
                                    $ride->order_delivery_status = $request_status;
                                    $ride->save();
                                    return response()->json([
                                        "status" => 1,
//                                        "message" => "success!",
                                        "message" => __('driver_messages.1'),
                                        "message_code" => 1,
                                        "service_category_id" => $ride->service_cat_id,
                                        "ride_id" => $ride->id,
                                        "ride_status" => $request_status,
                                        "way_point_status" => 0,
                                        "ride_completed_status" => 0,
                                        "ride_cancelled_status" => 0,
                                        "driver_current_status" => $driver_details->current_status,
                                        "cancel_by" => $cancel_message
                                    ]);
//                                return response()->json([
//                                    "status" => 0,
//                                    "message" => "something went to wrong!"
//                                ]);
                                }
                            } else {
                                return response()->json([
                                    "status" => 0,
//                                    "message" => "something went to wrong!",
                                    "message" => __('driver_messages.9'),
                                    "message_code" => 9,
                                ]);
                            }
                        } elseif ($request_status == 8) {
                            if ($ride_status == 4 || $ride_status == 10) {
                                return response()->json([
                                    "status" => 0,
//                                    "message" => "ride cancelled!",
                                    "message" => __('driver_messages.65'),
                                    "message_code" => 65,
                                ]);
                            } elseif ($ride_status == 6 || $ride_status == 7) {
                                $validator = Validator::make($request->all(), [
                                    "rating" => "numeric",
                                    "comment" => "nullable"
                                ]);
                                if ($validator->fails()) {
                                    return response()->json([
                                        "status" => 0,
                                        "message" => $validator->errors()->first(),
                                        "message_code" => 9,
                                    ]);
                                }
                                if ($ride->payment_status == 0) {
                                    return response()->json([
                                        "status" => 0,
//                                        "message" => "payment process pending!",
                                        "message" => __('driver_messages.83'),
                                        "message_code" => 83,
                                    ]);
                                }
                                $ride->order_delivery_status = $request_status;
                                $ride->save();
                                if ($request->get('rating') != Null || $request->get('comment') != Null) {
                                    if ($request->get('rating') != Null) {
                                        $user_rating = new UserRatings();
                                        $user_rating->user_id = $ride->user_id;
                                        $user_rating->provider_id = $driver_id;
                                        $user_rating->product_book_id = $ride->id;
                                        $user_rating->rating = round($request->get('rating'), 2);
                                        $user_rating->comment = $request->get('comment') != Null ? trim($request->get('comment')) : Null;
                                        $user_rating->save();

                                        $user_details = User::query()->where('id', $ride->user_id)->first();
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
                                return response()->json([
                                    "status" => 1,
//                                    "message" => "success!",
                                    "message" => __('driver_messages.1'),
                                    "message_code" => 1,
                                    "service_category_id" => $ride->service_cat_id,
                                    "ride_id" => $ride->id,
                                    "ride_status" => $request_status - 0,
                                    "way_point_status" => 0,
                                    "ride_completed_status" => 0,
                                    "ride_cancelled_status" => 0,
                                    "driver_current_status" => $driver_details->current_status,
                                    "cancel_by" => $cancel_message
                                ]);
                            } else {
                                return response()->json([
                                    "status" => 0,
//                                    "message" => "something went to wrong!",
                                    "message" => __('driver_messages.9'),
                                    "message_code" => 9,
                                ]);
                            }
                        } elseif ($request_status == 9) {
                            if ($ride_status == 4 || $ride_status == 10) {
                                return response()->json([
                                    "status" => 0,
//                                    "message" => "ride cancelled!",
                                    "message" => __('driver_messages.65'),
                                    "message_code" => 65,
                                ]);
                            } elseif ($ride_status == 6 || $ride_status == 7 || $ride_status == 8) {
                                if ($ride->payment_status == 0) {
                                    return response()->json([
                                        "status" => 0,
//                                        "message" => "payment process pending!",
                                        "message" => __('driver_messages.83'),
                                        "message_code" => 83,
                                    ]);
                                }
                                $user = User::query()->where('id', $ride->user_id)->first();
                                if ($user == Null) {
                                    return response()->json([
                                        "status" => 0,
//                                        "message" => "something went to wrong!",
                                        "message" => __('driver_messages.9'),
                                        "message_code" => 9,
                                    ]);
                                }
                                if (in_array($ride->status, [5, 6, 7, 8]) && $ride->status < 9) {
                                    $ride->status = 9;
                                    $ride->save();

                                    $check_duplicate = UserProductBookingStatus::query()->where('order_id', $ride->id)->where('status', 9)->first();
                                    if ($check_duplicate == Null) {
                                        $store_delivery_status = new UserProductBookingStatus();
                                        $store_delivery_status->order_id = $ride->id;
                                        $store_delivery_status->status = 9;
                                        $store_delivery_status->save();
                                    }

                                    $this->notificationClass->userStoreDeliveryNotification($ride->id, $user->device_token, $ride->status, $user->login_device, $user->language);
                                    $this->notificationClass->storeDeliveryDriverNotification($ride->id, $store->device_token, $ride->order_delivery_status, $store->login_device,$store->language);
                                }
                                $ride->order_delivery_status = $request_status;
                                $ride->save();

                                $update_driver_status = ProviderServices::query()->where('id', $request->get('driver_service_id'))->first();
                                if ($update_driver_status != Null) {
                                    $update_driver_status->current_status = 1;
                                    $update_driver_status->save();
                                } else {
                                    return response()->json([
                                        "status" => 0,
//                                        "message" => "something went to wrong!",
                                        "message" => __('driver_messages.9'),
                                        "message_code" => 9,
                                    ]);
                                }

                                ProviderUserRunningService::query()->where('provider_id', $driver_id)->where('user_id', $ride->user_id)->where('service_cat_id', $service_category_id)->where('booking_id', $ride->id)->delete();

                                $general_settings = request()->get("general_settings");
                                if ($general_settings !=  Null) {
                                    if ($general_settings->send_mail == 1) {
                                        $service_category = ServiceCategory::query()->where('id', $ride->service_cat_id)->first();
                                        if ($service_category != Null) {
                                            $service_name = ucwords(strtolower($service_category->name));
                                            $order_id =($ride->order_no !="")?$ride->order_no:0;
                                            $driver_name = ucwords($driver_check['first_name'] . " ". $driver_check['last_name']);

                                            //send mail for user
                                            $user_details = User::query()->where('id', $ride->user_id)->first();
                                            if ($user_details != Null){
                                                $email = $user_details->email;
                                                $time =  date("Y-m-d H:i:s",strtotime($ride->updated_at));
                                                $address = ($ride->delivery_address!='')?$ride->delivery_address:"";
                                                try{
                                                    $mail_type = "order_delivered_-_delivery";
                                                    $to_mail = $email;
                                                    $subject = "Your ".$general_settings->mail_site_name." ".$service_name ." Order Completed.";
                                                    $disp_data = array("##user_name##"=>$ride->user_name,"##service_name##"=>$service_name,"##order_id##"=>$order_id,
                                                        "##address##"=>$address,"##driver_name##"=>$driver_name,"##time##"=>$time );
                                                    $mail_return_data = $this->notificationClass->sendMail($subject,$to_mail,$mail_type,$disp_data);
                                                }
                                                catch (\Exception $e){}
                                            }
                                            //send mail for driver
                                            $email_driver = $driver_check['email'];
                                            try{
                                                $mail_type = "driver_order_completed_-_delivery";
                                                $to_mail = $email_driver;
                                                $subject = $service_name ." Order Completed.";
                                                $disp_data = array("##order_id##"=>$order_id,"##driver_name##"=>$driver_name,"##service_name##"=>$service_name);
                                                $mail_return_data = $this->notificationClass->sendMail($subject,$to_mail,$mail_type,$disp_data);
                                            }
                                            catch (\Exception $e){}
                                        }
                                    }
                                }

                                return response()->json([
                                    "status" => 1,
//                                    "message" => "success!",
                                    "message" => __('driver_messages.1'),
                                    "message_code" => 1,
                                    "service_category_id" => $ride->service_cat_id,
                                    "ride_id" => $ride->id,
                                    "ride_status" => $request_status - 0,
                                    "way_point_status" => 0,
                                    "ride_completed_status" => 0,
                                    "ride_cancelled_status" => 0,
                                    "driver_current_status" => $update_driver_status->current_status,
                                    "cancel_by" => $cancel_message
                                ]);
                            } else {
                                return response()->json([
                                    "status" => 0,
//                                    "message" => "something went to wrong!",
                                    "message" => __('driver_messages.9'),
                                    "message_code" => 9,
                                ]);
                            }
                        } else {
                            return response()->json([
                                "status" => 0,
//                                "message" => "something went to wrong!",
                                "message" => __('driver_messages.9'),
                                "message_code" => 9,
                            ]);
                        }
                    }
                } else {
                    return response()->json([
                        "status" => 0,
//                        "message" => "ride not accepting by any driver!",
                        "message" => __('driver_messages.84'),
                        "message_code" => 84,
                    ]);
                }
            } else {
                return response()->json([
                    "status" => 0,
//                    "message" => "ride not found!",
                    "message" => __('driver_messages.26'),
                    "message_code" => 26,
                ]);
            }
        }
        else {
            return response()->json([
                "status" => 0,
//                "message" => "something went to wrong!",
                "message" => __('driver_messages.9'),
                "message_code" => 9,
            ]);
        }
    }

    //pending user side cancel ride delete record from running service record
    //not used
    public function postDriverUpdateDeliveryStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
                "driver_id" => "required|numeric",
                "access_token" => "required",
                "driver_service_id" => "required|numeric",
                "ride_id" => "required|numeric",
                "ride_status" => "required|numeric|in:3,4,5,6,7,8,9",
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
        $request_status = $request->get('ride_status');
        $driver_check = $this->driverClassApi->checkDriverRegisterAllow($driver_id, $request->get('access_token'), $request->get('driver_service_id'));
        if ($decoded['status'] = json_decode($driver_check) == false) {
            return $driver_check;
        }

        $ride = UserProductBooking::query()->where('id', $request->get('ride_id'))->first();
        if ($ride != Null) {
            $ride_status = $ride->order_delivery_status;
            $driver_details = TransportDriverDetails::query()->select('transport_driver_details.id', 'provider_services.status', 'provider_services.current_status')
                ->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
                ->where('transport_driver_details.provider_service_id', $request->get('driver_service_id'))->first();
            if ($driver_details == Null) {
                return response()->json([
                    "status" => 0,
                    "message" => "driver details not found!",
                    "message_code" => 15,
                ]);
            }
            $store = StoreDetails::query()->select('providers.id', 'providers.device_token')
                ->join('provider_services', 'provider_services.id', 'store_details.provider_service_id')
                ->join('providers', 'providers.id', 'provider_services.provider_id')
                ->where('id', $ride->store_id)->first();
            if ($store == Null) {
                return response()->json([
                    "status" => 0,
                    "message" => "something went to wrong!",
                    "message_code" => 9,
                ]);
            }
            if ($driver_details->id != $ride->delivery_people_id) {
                return response()->json([
                    "status" => 0,
                    "message" => "something went to wrong!",
                    "message_code" => 9,
                ]);
            }
            if ($ride_status > 0) {
                if ($request_status == 3) {
                    if ($ride_status == 1 || $ride_status == 2) {
                        $user = User::query()->where('id', $ride->user_id)->first();
                        if ($user == Null) {
                            return response()->json([
                                "status" => 0,
                                "message" => "something went to wrong!",
                                "message_code" => 9,
                            ]);
                        }
                        if (in_array($ride->status, [2, 5]) && $ride->status < 7) {
                            $ride->status = 7;
                            $ride->save();

                            $check_duplicate = UserProductBookingStatus::query()->where('order_id', $ride->id)->where('status', 7)->first();
                            if ($check_duplicate == Null) {
                                $store_delivery_status = new UserProductBookingStatus();
                                $store_delivery_status->order_id = $ride->id;
                                $store_delivery_status->status = 7;
                                $store_delivery_status->save();
                            }
                        }

                        $ride->order_delivery_status = $request_status;
                        $ride->save();

                        return response()->json([
                            "status" => 1,
                            "message" => "success!",
                            "message_code" => 1,
                            "service_category_id" => $ride->service_cat_id,
                            "ride_id" => $ride->id,
                            "ride_status" => $request_status,
                            "driver_current_status" => $driver_details->current_status
                        ]);
                    } else {
                        return response()->json([
                            "status" => 0,
                            "message" => "something went to wrong!",
                            "message_code" => 9,
                        ]);
                    }
                } elseif ($request_status == 4) {
                    if ($ride_status == 0 || $ride_status == 1 || $ride_status == 2 || $ride_status == 3) {
//                        $validator = Validator::make($request->all(), [
//                                "cancel_reason" => "required",
//                            ]
//                        );
//                        if ($validator->fails()) {
//                            return response()->json([
//                                "status" => 0,
//                                "message" => $validator->errors()->first()
//                            ]);
//                        }

                        $ride->order_delivery_status = $request_status;
//                        $ride->cancel_by = "driver";
//                        $ride->cancel_reason = $request->get('cancel_reason');
                        $ride->save();

                        $update_driver_status = ProviderServices::query()->where('id', $request->get('driver_service_id'))->first();
                        if ($update_driver_status != Null) {
                            $update_driver_status->current_status = 1;
                            $update_driver_status->save();
                        } else {
                            return response()->json([
                                "status" => 0,
                                "message" => "something went to wrong!",
                                "message_code" => 9,
                            ]);
                        }

                        ProviderUserRunningService::query()->where('provider_id', $driver_id)->where('user_id', $ride->user_id)->delete();
                        return response()->json([
                            "status" => 1,
                            "message" => "success!",
                            "message_code" => 1,
                            "service_category_id" => $ride->service_cat_id,
                            "ride_id" => $ride->id,
                            "ride_status" => $request_status,
                            "driver_current_status" => $update_driver_status->current_status
                        ]);
                    } else {
                        return response()->json([
                            "status" => 0,
                            "message" => "something went to wrong!",
                            "message_code" => 9,
                        ]);
                    }
                } elseif ($request_status == 5) {
                    if ($ride_status == 4 || $ride_status == 10) {
                        return response()->json([
                            "status" => 0,
                            "message" => "ride cancelled!",
                            "message_code" => 65,
                        ]);
                    } elseif ($ride_status == 3) {
                        $ride->order_delivery_status = $request_status;
                        $ride->save();

                        $user = User::query()->where('id', $ride->user_id)->first();
                        if ($user == Null) {
                            return response()->json([
                                "status" => 0,
                                "message" => "something went to wrong!",
                                "message_code" => 9,
                            ]);
                        }
                        if (in_array($ride->status, [2, 5, 6, 7]) && $ride->status < 8) {
                            $ride->status = 8;
                            $ride->save();

                            $check_duplicate = UserProductBookingStatus::query()->where('order_id', $ride->id)->where('status', 8)->first();
                            if ($check_duplicate == Null) {
                                $store_delivery_status = new UserProductBookingStatus();
                                $store_delivery_status->order_id = $ride->id;
                                $store_delivery_status->status = 8;
                                $store_delivery_status->save();
                            }

//                            $notification = $this->notificationClass->userStoreDeliveryNotification($ride->id, $user->device_token, $ride->status);
//                            if ($decoded['status'] = json_decode($notification) == false) {
//                                return $notification;
//                            }
                        }

                        return response()->json([
                            "status" => 1,
                            "message" => "success!",
                            "message_code" => 1,
                            "service_category_id" => $ride->service_cat_id,
                            "ride_id" => $ride->id,
                            "ride_status" => $request_status,
                            "driver_current_status" => $driver_details->current_status
                        ]);
                    } else {
                        return response()->json([
                            "status" => 0,
                            "message" => "something went to wrong!",
                            "message_code" => 9,
                        ]);
                    }
                } elseif ($request_status == 6) {
                    if ($ride_status == 4 || $ride_status == 10) {
                        return response()->json([
                            "status" => 0,
                            "message" => "ride cancelled!",
                            "message_code" => 65,
                        ]);
                    } elseif ($ride_status == 5) {
                        $ride->order_delivery_status = $request_status;
                        $ride->save();

                        return response()->json([
                            "status" => 1,
                            "message" => "success!",
                            "message_code" => 1,
                            "service_category_id" => $ride->service_cat_id,
                            "ride_id" => $ride->id,
                            "ride_status" => $request_status,
                            "driver_current_status" => $driver_details->current_status
                        ]);
                    } else {
                        return response()->json([
                            "status" => 0,
                            "message" => "something went to wrong!",
                            "message_code" => 9,
                        ]);
                    }
                } elseif ($request_status == 7) {
                    if ($ride_status == 4 || $ride_status == 10) {
                        return response()->json([
                            "status" => 0,
                            "message" => "ride cancelled!",
                            "message_code" => 65,
                        ]);
                    } elseif ($ride_status == 6) {
                        if ($ride->payment_status == 0) {
                            $validator = Validator::make($request->all(), [
                                    "total_amount" => "required",
                                ]
                            );
                            if ($validator->fails()) {
                                return response()->json([
                                    "status" => 0,
                                    "message" => $validator->errors()->first(),
                                    "message_code" => 9,
                                ]);
                            }
                            if ($ride->total_pay == $request->get('total_amount')) {
                                $ride->order_delivery_status = $request_status;
                                $ride->payment_status = 1;
                                $ride->save();

                                return response()->json([
                                    "status" => 1,
                                    "message" => "success!",
                                    "message_code" => 1,
                                    "service_category_id" => $ride->service_cat_id,
                                    "ride_id" => $ride->id,
                                    "ride_status" => $request_status,
                                    "driver_current_status" => $driver_details->current_status
                                ]);
                            } else {
                                return response()->json([
                                    "status" => 0,
                                    "message" => "amount not match!",
                                    "message_code" => 82,
                                ]);
                            }
                        } else {
                            return response()->json([
                                "status" => 0,
                                "message" => "something went to wrong!",
                                "message_code" => 9,
                            ]);
                        }
                    } else {
                        return response()->json([
                            "status" => 0,
                            "message" => "something went to wrong!",
                            "message_code" => 9,
                        ]);
                    }
                } elseif ($request_status == 8) {
                    if ($ride_status == 4 || $ride_status == 10) {
                        return response()->json([
                            "status" => 0,
                            "message" => "ride cancelled!",
                            "message_code" => 65,
                        ]);
                    } elseif ($ride_status == 6 || $ride_status == 7) {
                        $validator = Validator::make($request->all(), [
                                "rating" => "numeric",
                                "comment" => "nullable"
                            ]
                        );
                        if ($validator->fails()) {
                            return response()->json([
                                "status" => 0,
                                "message" => $validator->errors()->first(),
                                "message_code" => 9,
                            ]);
                        }
                        if ($ride->payment_status == 0) {
                            return response()->json([
                                "status" => 0,
                                "message" => "payment process pending!",
                                "message_code" => 83,
                            ]);
                        }
                        $ride->order_delivery_status = $request_status;
                        $ride->save();
                        if ($request->get('rating') != Null || $request->get('comment') != Null) {
                            if ($request->get('rating') != Null) {
                                $user_rating = new UserRatings();
                                $user_rating->user_id = $ride->user_id;
                                $user_rating->provider_id = $driver_id;
                                $user_rating->package_book_id = $ride->id;
                                $user_rating->rating = round($request->get('rating'), 2);
                                $user_rating->comment = $request->get('comment') != Null ? trim($request->get('comment')) : Null;
                                $user_rating->save();
                            }
                        }
                        return response()->json([
                            "status" => 1,
                            "message" => "success!",
                            "message_code" => 1,
                            "service_category_id" => $ride->service_cat_id,
                            "ride_id" => $ride->id,
                            "ride_status" => $request_status,
                            "driver_current_status" => $driver_details->current_status
                        ]);
                    } else {
                        return response()->json([
                            "status" => 0,
                            "message" => "something went to wrong!",
                            "message_code" => 9,
                        ]);
                    }
                } elseif ($request_status == 9) {
                    if ($ride_status == 4 || $ride_status == 10) {
                        return response()->json([
                            "status" => 0,
                            "message" => "ride cancelled!",
                            "message_code" => 65,
                        ]);
                    } elseif ($ride_status == 6 || $ride_status == 7 || $ride_status == 8) {
                        if ($ride->payment_status == 0) {
                            return response()->json([
                                "status" => 0,
                                "message" => "payment process pending!",
                                "message_code" => 83,
                            ]);
                        }
                        $user = User::query()->where('id', $ride->user_id)->first();
                        if ($user == Null) {
                            return response()->json([
                                "status" => 0,
                                "message" => "something went to wrong!",
                                "message_code" => 9,
                            ]);
                        }
                        if (in_array($ride->status, [5, 6, 7, 8]) && $ride->status < 9) {
                            $ride->status = 9;
                            $ride->save();

                            $check_duplicate = UserProductBookingStatus::query()->where('order_id', $ride->id)->where('status', 9)->first();
                            if ($check_duplicate == Null) {
                                $store_delivery_status = new UserProductBookingStatus();
                                $store_delivery_status->order_id = $ride->id;
                                $store_delivery_status->status = 9;
                                $store_delivery_status->save();
                            }

//                            $notification = $this->notificationClass->userStoreDeliveryNotification($ride->id, $user->device_token, $ride->status);
//                            if ($decoded['status'] = json_decode($notification) == false) {
//                                return $notification;
//                            }
                        }
                        $ride->order_delivery_status = $request_status;
                        $ride->save();
                        $update_driver_status = ProviderServices::query()->where('id', $request->get('driver_service_id'))->first();
                        if ($update_driver_status != Null) {
                            $update_driver_status->current_status = 1;
                            $update_driver_status->save();
                        } else {
                            return response()->json([
                                "status" => 0,
                                "message" => "something went to wrong!",
                                "message_code" => 9,
                            ]);
                        }

                        return response()->json([
                            "status" => 1,
                            "message" => "success!",
                            "message_code" => 1,
                            "service_category_id" => $ride->service_cat_id,
                            "ride_id" => $ride->id,
                            "ride_status" => $request_status,
                            "driver_current_status" => $driver_details->current_status
                        ]);
                    } else {
                        return response()->json([
                            "status" => 0,
                            "message" => "something went to wrong!",
                            "message_code" => 9,
                        ]);
                    }
                }
            } else {
                return response()->json([
                    "status" => 0,
                    "message" => "ride not accepting by any driver!",
                    "message_code" => 84,
                ]);
            }
        } else {
            return response()->json([
                "status" => 0,
                "message" => "ride not found!",
                "message_code" => 26,
            ]);
        }
        return response()->json([
            "status" => 0,
            "message" => "something went to wrong!",
            "message_code" => 9,
        ]);
    }

    public function postDriverUpdateCurrentLatLong(Request $request)
    {
        $validator = Validator::make($request->all(), [
                "driver_id" => "required|numeric",
                "access_token" => "required",
                "driver_service_id" => "required|numeric",
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

        $driver_id = $request->get('driver_id');
        $driver_check = $this->driverClassApi->checkDriverRegisterAllow($driver_id, $request->get('access_token'), $request->get('driver_service_id'));
        if ($decoded['status'] = json_decode($driver_check) == false) {
            return $driver_check;
        }

        $update_driver_lat_long = TransportDriverDetails::query()->where('provider_service_id', $request->get('driver_service_id'))->first();
        if ($update_driver_lat_long != Null) {
            $update_driver_lat_long->current_lat = $request->get('current_lat');
            $update_driver_lat_long->current_long = $request->get('current_long');
            $update_driver_lat_long->last_online_date_time = date('Y-m-d H:i:s');
            $update_driver_lat_long->save();

            return response()->json([
                "status" => 1,
//                "message" => "success!",
                "message" => __('driver_messages.1'),
                "message_code" => 1,
            ]);
        } else {
            return response()->json([
                "status" => 0,
//                "message" => "driver details not find!",
                "message" => __('driver_messages.15'),
                "message_code" => 15,
            ]);
        }
    }

    public function postDriverCheckServiceStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
                "driver_id" => "required|numeric",
                "access_token" => "required",
                "driver_service_id" => "required|numeric"
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
        $access_token = $request->get('access_token');
        $driver_service_id = $request->get('driver_service_id');

        $driver_details = Provider::query()->where('id', $driver_id)->first();
        if ($driver_details != Null) {
            if ($driver_details->status == 2) {
                return response()->json([
                    "status" => 3,
//                    "message" => 'Your account is currently blocked, so not authorised to allow any activity!',
                    "message" => __('driver_messages.3'),
                    "message_code" => 3,
                ]);
            }
            if ($driver_details->access_token != $access_token) {
                return response()->json([
                    'status' => 4,
//                    'message' => "Access Token Not Match!",
                    'message' => __('driver_messages.4'),
                    "message_code" => 4,
                ]);
            }
            $driver_service = ProviderServices::query()->where('id', $driver_service_id)->where('provider_id', $driver_details->id)->first();
            if ($driver_service != Null) {
                if ($driver_service->status == 2) {
                    return response()->json([
                        "status" => 3,
//                        "message" => 'Your account is currently blocked, so not authorised to allow any activity!',
                        "message" => __('driver_messages.3'),
                        "message_code" => 3,
                    ]);
                }
//                if ($driver_service->status == 3) {
//                    return response()->json([
//                        "status" => 7,
//                        "message" => 'Your service request rejected by admin!'
//                    ]);
//                }
//                if ($driver_service->status == 0) {
//                    return response()->json([
//                        "status" => 8,
//                        "message" => 'Your register service status is pending!'
//                    ]);
//                }
//                return response()->json([
//                    "status" => 1,
//                    "message" => 'success!'
//                ]);
                return response()->json([
                    'status' => 1,
//                    'message' => 'Success!',
                    'message' => __('driver_messages.1'),
                    "message_code" => 1,
                    'service_status' => $driver_service->status,
                ]);
            } else {
                return response()->json([
                    "status" => 6,
//                    "message" => 'driver service not register!',
                    "message" => __('driver_messages.6'),
                    "message_code" => 6,
                ]);
            }
        } else {
            return response()->json([
                "status" => 5,
//                "message" => 'driver not found!',
                "message" => __('driver_messages.5'),
                "message_code" => 5,
            ]);
        }
    }

    public function postDriverManageDocumentList(Request $request)
    {
        $validator = Validator::make($request->all(), [
                "driver_id" => "required|numeric",
                "access_token" => "required",
                "driver_service_id" => "required|numeric"
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
//        $driver_check = $this->driverClassApi->checkDriverRegisterAllow($driver_id, $request->get('access_token'), $request->get('driver_service_id'));
//        if ($decoded['status'] = json_decode($driver_check) == false) {
//            return $driver_check;
//        }
        $driver_details = Provider::query()->where('id', $driver_id)->first();
        if ($driver_details != Null) {
            if ($driver_details->status == 2) {
                return response()->json([
                    "status" => 3,
//                    "message" => 'Your account is currently blocked, so not authorised to allow any activity!',
                    "message" => __('driver_messages.3'),
                    "message_code" => 3
                ]);
            }
            if ($driver_details->access_token != $request->get('access_token')) {
                return response()->json([
                    'status' => 4,
//                    'message' => "Access Token Not Match!",
                    'message' => __('driver_messages.4'),
                    'message_code' => 4
                ]);
            }
            if ($driver_details->verified_at == Null) {
                return response()->json([
                    'status' => 2,
//                    'message' => "Driver Not Verified!",
                    'message' => __('driver_messages.2'),
                    "message_code" => 2,
                ]);
            }
        } else {
            return response()->json([
                "status" => 5,
//                "message" => 'driver not found!',
                "message" => __('driver_messages.5'),
                "message_code" => 5
            ]);
        }
        $driver_service_cat = ProviderServices::query()->where('id', $request->get('driver_service_id'))->where('provider_id', $driver_details->id)->first();
        if ($driver_service_cat != Null) {
            if ($driver_service_cat->status == 2) {
                return response()->json([
                    "status" => 3,
//                    "message" => 'Your account is currently blocked, so not authorised to allow any activity!',
                    "message" => __('driver_messages.3'),
                    "message_code" => 3,
                ]);
            } elseif ($driver_service_cat->status == 0 || $driver_service_cat->status == 1 || $driver_service_cat->status == 3) {
                $doc_status = 0;
                $transport_driver_details = TransportDriverDetails::query()->select('doc_status','courier_boy')->where('provider_service_id', $request->get('driver_service_id'))->first();
                if ($transport_driver_details != Null) {
                    $doc_status = $transport_driver_details->doc_status - 0;
                } else {
                    return response()->json([
                        "status" => 0,
//                        "message" => "driver vehicle details not found",
                        "message" => __('driver_messages.15'),
                        "message_code" => 15,
                    ]);
                }
                return $this->driverClassApi->getDriverRequiredDocumentList($driver_service_cat->service_cat_id, $driver_service_cat->id , $doc_status);
            } else {
                return response()->json([
                    "status" => 0,
//                    "message" => 'something went to wrong!',
                    "message" => __('driver_messages.9'),
                    "message_code" => 9,
                ]);
            }
        } else {
            return response()->json([
                "status" => 6,
//                "message" => 'driver service not register!',
                "message" => __('driver_messages.6'),
                "message_code" => 6,
            ]);
        }
    }

    public function postDriverManageDocumentList_old(Request $request)
    {
        $validator = Validator::make($request->all(), [
                "driver_id" => "required|numeric",
                "access_token" => "required",
                "driver_service_id" => "required|numeric"
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
        $driver_service_cat = ProviderServices::query()->where('id', $request->get('driver_service_id'))->first();
        if ($driver_service_cat != Null) {
            if ($driver_service_cat->status == 2) {
                return response()->json([
                    "status" => 3,
                    "message" => 'Your account is currently blocked, so not authorised to allow any activity!',
                    "message_code" => 3,
                ]);
            } elseif ($driver_service_cat->status == 0 || $driver_service_cat->status == 1 || $driver_service_cat->status == 3) {
                return $this->driverClassApi->getDriverRequiredDocumentList($driver_service_cat->service_cat_id, $driver_service_cat->id);
            } else {
                return response()->json([
                    "status" => 0,
                    "message" => 'something went to wrong!',
                    "message_code" => 9,
                ]);
            }
        } else {
            return response()->json([
                "status" => 6,
                "message" => 'driver service not register!',
                "message_code" => 6,
            ]);
        }
    }

    public function postDriverUploadSingleDocument_OLD(Request $request)
    {
        $validator = Validator::make($request->all(), [
                "driver_id" => "required|numeric",
                "access_token" => "required|numeric",
                "driver_service_id" => "required|integer",
                "document_id" => "required|integer",
                "document_file" => "required|file"
            ]
        );
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 0,
            ]);
        }

        $driver_id = $request->get('driver_id');
        $driver_check = $this->driverClassApi->checkDriverRegisterAllow($driver_id, $request->get('access_token'), $request->get('driver_service_id'));
        if ($decoded['status'] = json_decode($driver_check) == false) {
            return $driver_check;
        }
        $driver_service_cat = ProviderServices::query()->where('id', $request->get('driver_service_id'))->first();
        if ($driver_service_cat != Null) {
            if ($driver_service_cat->status == 2) {
                return response()->json([
                    "status" => 3,
                    "message" => 'Your account is currently blocked, so not authorised to allow any activity!',
                    "message_code" => 3,
                ]);
            } elseif ($driver_service_cat->status == 0 || $driver_service_cat->status == 1 || $driver_service_cat->status == 3) {
                $req_document = RequiredDocuments::query()->where('service_cat_id', $driver_service_cat->service_cat_id)->where('id', $request->get('document_id'))->where('status', 1)->first();
                if ($req_document != Null) {
                    $upload_document = ProviderDocuments::query()->where('provider_service_id', $driver_service_cat->id)->where('req_document_id', $req_document->id)->first();
                    if ($request->file('document_file') != Null) {
                        if ($upload_document != Null) {
                            if (\File::exists(public_path('/assets/images/provider-documents/' . $upload_document->document_file))) {
                                \File::delete(public_path('/assets/images/provider-documents/' . $upload_document->document_file));
                            }
                        }
                        $file = $request->file('document_file');
                        $file_new = rand(1, 9) . date('sihYdm') . rand(1, 9) . '.' . $file->getClientOriginalExtension();
                        $file->move(public_path() . '/assets/images/provider-documents/', $file_new);
                    } else {
                        return response()->json([
                            "status" => 0,
                            "message" => 'something went to wrong!',
                            "message_code" => 9,
                        ]);
                    }
                    if ($upload_document == Null) {
                        $upload_document = new ProviderDocuments();
                    }
                    $upload_document->provider_service_id = $driver_service_cat->id;
                    $upload_document->req_document_id = $req_document->id;
                    $upload_document->document_file = $file_new;
                    $upload_document->status = 0;
                    $upload_document->save();
                    return $this->driverClassApi->getDriverRequiredDocumentList($driver_service_cat->service_cat_id, $driver_service_cat->id);
                } else {
                    return response()->json([
                        "status" => 0,
                        "message" => 'something went to wrong!',
                        "message_code" => 9,
                    ]);
                }
            } else {
                return response()->json([
                    "status" => 0,
                    "message" => 'something went to wrong!',
                    "message_code" => 9,
                ]);
            }
        } else {
            return response()->json([
                "status" => 6,
                "message" => 'driver service not register!',
                "message_code" => 9,
            ]);
        }
    }

    public function postDriverUploadSingleDocument(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "driver_id" => "required|numeric",
            "access_token" => "required|numeric",
            "driver_service_id" => "required|integer",
            "document_id" => "required|integer",
            "document_file" => "required|file",
            "service_category_id" => "nullable",
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 0,
            ]);
        }

        $driver_id = $request->get('driver_id');
        $driver_details = Provider::query()->where('id', $driver_id)->first();
        if ($driver_details != Null) {
            if ($driver_details->status == 2) {
                return response()->json([
                    "status" => 3,
                    "message" => /*'Your account is currently blocked, so not authorised to allow any activity!'*/ __('driver_messages.3'),
                    "message_code" => 3
                ]);
            }
            if ($driver_details->access_token != $request->get('access_token')) {
                return response()->json([
                    'status' => 4,
                    'message' => /*"Access Token Not Match!"*/ __('driver_messages.4'),
                    'message_code' => 4
                ]);
            }
            if ($driver_details->verified_at == Null) {
                return response()->json([
                    'status' => 2,
//                    'message' => "Driver Not Verified!",
                    'message' => __('driver_messages.2'),
                    "message_code" => 2,
                ]);
            }
        } else {
            return response()->json([
                "status" => 5,
//                "message" => 'driver not found!',
                "message" => __('driver_messages.5'),
                "message_code" => 5
            ]);
        }
        $driver_service_cat = ProviderServices::query()->where('id', $request->get('driver_service_id'))->first();
        if ($driver_service_cat != Null) {
            if ($driver_service_cat->status == 2) {
                return response()->json([
                    "status" => 3,
//                    "message" => 'Your account is currently blocked, so not authorised to allow any activity!',
                    "message" => __('driver_messages.3'),
                    "message_code" => 3,
                ]);
            } elseif ($driver_service_cat->status == 0 || $driver_service_cat->status == 1 || $driver_service_cat->status == 3) {
//                $service_category_id = $request->get('service_category_id');
                $doc_status = 0;
                $transport_driver_details = TransportDriverDetails::query()->where('provider_service_id', $request->get('driver_service_id'))->first();
                if ($transport_driver_details != Null) {
                    $doc_status = $transport_driver_details->doc_status - 0;
                } else {
                    return response()->json([
                        "status" => 0,
//                        "message" => "driver vehicle details not found",
                        "message" => __('driver_messages.15'),
                        "message_code" => 15,
                    ]);
                }
                $req_document = RequiredDocuments::query()->where('service_cat_id', $driver_service_cat->service_cat_id)->where('id', $request->get('document_id'))->where('status', 1)->first();
                if ($req_document != Null) {
                    $upload_document = ProviderDocuments::query()->where('provider_service_id', $driver_service_cat->id)->where('req_document_id', $req_document->id)->first();
                    if ($request->file('document_file') != Null) {
                        if ($upload_document != Null) {
                            if (\File::exists(public_path('/assets/images/provider-documents/' . $upload_document->document_file))) {
                                \File::delete(public_path('/assets/images/provider-documents/' . $upload_document->document_file));
                            }
                        }
                        $file = $request->file('document_file');
                        $file_new = rand(1, 9) . date('sihYdm') . rand(1, 9) . '.' . $file->getClientOriginalExtension();
                        $file->move(public_path() . '/assets/images/provider-documents/', $file_new);
                    } else {
                        return response()->json([
                            "status" => 0,
//                            "message" => 'something went to wrong!',
                            "message" => __('driver_messages.9'),
                            "message_code" => 9,
                        ]);
                    }
                    if ($upload_document == Null) {
                        $upload_document = new ProviderDocuments();
                    }
                    $upload_document->provider_service_id = $driver_service_cat->id;
                    $upload_document->req_document_id = $req_document->id;
                    $upload_document->document_file = $file_new;
                    $upload_document->status = 0;
                    $upload_document->save();
                    if ($transport_driver_details->doc_status == 1) {
                        $doc_status = 1;
                    } else {
                        $doc_cat_id = [$driver_service_cat->service_cat_id];
//                        if ($transport_driver_details->courier_boy == 1) {
//                            $doc_cat_id = [$driver_service_cat->service_cat_id, $this->courier_service];
//                        }
                        $req_document_count = RequiredDocuments::query()->whereIn('service_cat_id', $doc_cat_id)->where('status', 1)->count();
                        $uploaded_document_count = ProviderDocuments::query()->where('provider_service_id', $driver_service_cat->id)->count();
                        if ($req_document_count == $uploaded_document_count) {
                            $transport_driver_details->doc_status = 1;
                            $transport_driver_details->save();
                            $doc_status = 1;

                            $general_settings = request()->get("general_settings");
                            if ($general_settings != Null && $general_settings->send_receive_email != Null) {
                                $email = $general_settings->send_receive_email;
                                $driver_name = ($driver_details->first_name != "")?$driver_details->first_name:"";
                                try{
                                    $mail_type = "admin_new_driver_signup";
                                    $to_mail=$email;
                                    $subject = "New Driver Registered";
                                    $disp_data = array("##driver_name##"=>$driver_name );
                                    $mail_return_data = $this->notificationClass->sendMail($subject,$to_mail,$mail_type,$disp_data);
                                }catch (\Exception $e){}
                            }
                        }
                    }
                    return $this->driverClassApi->getDriverRequiredDocumentList($driver_service_cat->service_cat_id, $driver_service_cat->id ,$doc_status);
                } else {
                    return response()->json([
                        "status" => 0,
//                        "message" => 'something went to wrong!',
                        "message" => __('driver_messages.9'),
                        "message_code" => 9,
                    ]);
                }
            } else {
                return response()->json([
                    "status" => 0,
//                    "message" => 'something went to wrong!',
                    "message" => __('driver_messages.9'),
                    "message_code" => 9,
                ]);
            }
        } else {
            return response()->json([
                "status" => 6,
//                "message" => 'driver service not register!',
                "message" => __('driver_messages.9'),
                "message_code" => 9,
            ]);
        }
    }

    public function postDriverGetRunningService(Request $request)
    {
        $validator = Validator::make($request->all(), [
                "driver_id" => "required|numeric",
                "access_token" => "required",
                "driver_service_id" => "required|numeric"
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

        $driver_service_cat = ProviderServices::query()->where('id', $request->get('driver_service_id'))->first();
        if ($driver_service_cat != Null) {
           $get_running_service = ProviderUserRunningService::query()->where('provider_id', $driver_id)->get();

            if (!$get_running_service->isEmpty()) {
                $running_ride = [];
                foreach ($get_running_service as $running_services) {
                    if (in_array($running_services->service_cat_id, $this->transport_service_id_array) || in_array($running_services->service_cat_id, $this->courier_service_id_array)) {
                        $ride_details = TransportRideBook::query()->where('id', $running_services->booking_id)->first();
                        if ($ride_details != Null) {
                            if ($ride_details->status >= 1 && $ride_details->status <= 9) {
                                $running_ride[] = [
                                    "ride_id" => $ride_details->id,
                                    "ride_service_cat_id" => $ride_details->service_cat_id,
                                    "ride_status" => $ride_details->status,
                                    "way_point_status" => $ride_details->way_point_status,
                                ];
                            }
                        }
                    }
                    elseif (in_array($running_services->service_cat_id, $this->delivery_service_id_array)) {
                        $ride_details = UserProductBooking::query()->where('id', $running_services->booking_id)->first();
                        if ($ride_details != Null) {
                            if ($ride_details->order_delivery_status >= 1 && $ride_details->order_delivery_status <= 9) {
                                $running_ride[] = [
                                    "ride_id" => $ride_details->id,
                                    "ride_service_cat_id" => $ride_details->service_cat_id,
                                    "ride_status" => $ride_details->order_delivery_status,
                                    "way_point_status" => 0,
                                ];
                            }
                        }
                    }
                }
                return response()->json([
                    "status" => 1,
//                    "message" => 'success!',
                    "message" => __('driver_messages.1'),
                    "message_code" => 1,
                    "running_rides" => $running_ride
                ]);
            } else {
                return response()->json([
                    "status" => 0,
//                    "message" => 'driver service not running!',
                    "message" => __('driver_messages.1'),
                    "message_code" => 1,
                ]);
            }
        } else {
            return response()->json([
                "status" => 6,
//                    "message" => 'driver service not register!',
                "message" => __('driver_messages.6'),
                "message_code" => 6,
            ]);
        }
    }

    public function postDriverGetVehicleDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
                "driver_id" => "required|numeric",
                "access_token" => "required",
                "driver_service_id" => "required|numeric"
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

        $driver_service_cat = ProviderServices::query()->where('id', $request->get('driver_service_id'))->first();
        if ($driver_service_cat != Null) {
            $driver_details = TransportDriverDetails::query()->select('vehicle_type_id', 'vehicle_company', 'model_name', 'model_year', 'plat_no', 'driver_service', 'delivery_boy', 'courier_boy', 'no_of_seat', 'child_seat', 'vehicle_color', 'handicap', 'support_weight', 'rental_service')->where('provider_service_id', $request->get('driver_service_id'))->first();
            if ($driver_details != Null) {
                $vehicle_type_list = [];
                if ($driver_service_cat->service_cat_id == 4){
                    $get_vehicle_types = TransportVehicleType::query()->select('transport_vehicle_type.id as vehicle_type_id', 'transport_vehicle_type.name as vehicle_type_name')
                        ->where('transport_vehicle_type.service_cat_id', $driver_service_cat->service_cat_id)
                        ->whereNotIn('transport_vehicle_type.id', [10,14])
                        ->where('transport_vehicle_type.status', 1)->get();
                }
                else{
                    $get_vehicle_types = TransportVehicleType::query()->select('transport_vehicle_type.id as vehicle_type_id', 'transport_vehicle_type.name as vehicle_type_name')
                        ->where('transport_vehicle_type.service_cat_id', $driver_service_cat->service_cat_id)
                        ->where('transport_vehicle_type.status', 1)->get();
                }
                if (!$get_vehicle_types->isEmpty()) {
                    $vehicle_type_list = $get_vehicle_types;
                }
                return response()->json([
                    "status" => 1,
//                    "message" => "success!",
                    "message" => __('driver_messages.1'),
                    "message_code" => 1,
                    "vehicle_type_id" => $driver_details->vehicle_type_id,
                    "manufacture_name" => $driver_details->vehicle_company,
                    "model_name" => $driver_details->model_name,
                    "model_year" => $driver_details->model_year,
                    "vehicle_plat_no" => $driver_details->plat_no,
                    "no_of_seat" => $driver_details->no_of_seat,
                    "is_driver_service" => $driver_details->driver_service,
                    "is_rental" => $driver_details->rental_service,
                    "vehicle_color" => $driver_details->vehicle_color != Null ? $driver_details->vehicle_color : '',
                    "handicap_accessibility" => $driver_details->handicap,
                    "child_seat_accessibility" => $driver_details->child_seat,
                    "support_weight" => $driver_details->support_weight,
                    "vehicle_type_list" => $vehicle_type_list
                ]);
            } else {
                return response()->json([
                    "status" => 0,
//                    "message" => "driver vehicle details not found",
                    "message" => __('driver_messages.15'),
                    "message_code" => 15,
                ]);
            }
        } else {
            return response()->json([
                "status" => 6,
//                "message" => "driver service not register!",
                "message" => __('driver_messages.6'),
                "message_code" => 6,
            ]);
        }
    }

    public function postDriverUpdateVehicleDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
                "driver_id" => "required|numeric",
                "access_token" => "required",
                "driver_service_id" => "required|numeric"
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

        $driver_service_cat = ProviderServices::query()->where('id', $request->get('driver_service_id'))->first();
        if ($driver_service_cat != Null) {
            $validator = Validator::make($request->all(), [
                    "vehicle_type_id" => "required|numeric",
                    "manufacture_name" => "required",
                    "model_name" => "required",
                    "model_year" => "required",
                    "vehicle_plat_no" => "required",
                ]
            );
            if ($validator->fails()) {
                return response()->json([
                    "status" => 0,
                    "message" => $validator->errors()->first(),
                    "message_code" => 9,
                ]);
            }
            if ($driver_service_cat->service_cat_id == $this->bike_ride) {
                $validator = Validator::make($request->all(), [
                        "is_rental" => "required|numeric|in:0,1"
                    ]
                );
                if ($validator->fails()) {
                    return response()->json([
                        "status" => 0,
                        "message" => $validator->errors()->first(),
                        "message_code" => 9,
                    ]);
                }
            }
            if ($driver_service_cat->service_cat_id == $this->taxi_ride) {
                $validator = Validator::make($request->all(), [
                        "no_of_seat" => "required|numeric",
                        "vehicle_color" => "required",
                        "handicap_accessibility" => "required|in:0,1",
                        "child_seat_accessibility" => "required|numeric|in:0,1",
                        "is_rental" => "required|numeric|in:0,1"
                    ]
                );
                if ($validator->fails()) {
                    return response()->json([
                        "status" => 0,
                        "message" => $validator->errors()->first(),
                        "message_code" => 9,
                    ]);
                }
            }
            if ($driver_service_cat->service_cat_id == $this->courier_service) {
                $validator = Validator::make($request->all(), [
                        "support_weight" => "nullable",
                    ]
                );
                if ($validator->fails()) {
                    return response()->json([
                        "status" => 0,
                        "message" => $validator->errors()->first(),
                        "message_code" => 9,
                    ]);
                }
            }
            $driver_details = TransportDriverDetails::query()->select('transport_driver_details.*','provider_services.service_cat_id')
                                ->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
                                ->where('transport_driver_details.provider_service_id', $driver_service_cat->id)->first();
            if ($driver_details != Null) {
                $vehicle_details = TransportVehicleType::query()->where('id', $request->get('vehicle_type_id'))->first();
                if ($vehicle_details == Null) {
                    return response()->json([
                        "status" => 0,
//                        "message" => "vehicle type not found!",
                        "message" => __('driver_messages.9'),
                        "message_code" => 9,
                    ]);
                }
                $driver_details->vehicle_type_id = $request->get('vehicle_type_id');
                $driver_details->vehicle_company = $request->get('manufacture_name');
                $driver_details->model_name = $request->get('model_name');
                $driver_details->model_year = $request->get('model_year');
                $driver_details->plat_no = $request->get('vehicle_plat_no');
                $driver_service_category_id = $driver_service_cat->service_cat_id;
                if ($driver_service_cat->service_cat_id == $this->bike_ride) {
                    $driver_details->rental_service = $request->get('is_rental');
                }

                if ($driver_service_cat->service_cat_id == $this->taxi_ride) {
                    $driver_details->rental_service = $request->get('is_rental');
                    $driver_details->no_of_seat = $request->get('no_of_seat');
                    $driver_details->vehicle_color = $request->get('vehicle_color');
                    $driver_details->handicap = $request->get('handicap_accessibility');
                    $driver_details->child_seat = $request->get('child_seat_accessibility');
                }
                if ($driver_service_cat->service_cat_id == $this->courier_service) {
//                    $driver_details->support_weight = $request->get('support_weight');
                }
                $driver_details->save();


                $driver_service = $driver_details->driver_service;
                $delivery_boy = $driver_details->delivery_boy;
                $courier_boy = $driver_details->courier_boy;
//                $service_lang_converted = $this->notificationClass->serviceLangConvert();
                $lang = $driver_check->language;
                if($lang !="en" && $lang != "" && $lang != "Null" ){
                    $lang = $lang."_";
                }else{
                    $lang ="";
                }


                $ride_service =  (config('global.lang_constant.RIDE_SERVICE.'.$lang.'value') !="")?config('global.lang_constant.RIDE_SERVICE.'.$lang.'value') ." ":config('global.lang_constant.RIDE_SERVICE.value')." ";
                $courier_delivery = (config('global.lang_constant.COURIER_DELIVERY.'.$lang.'value') !="")?config('global.lang_constant.COURIER_DELIVERY.'.$lang.'value')." ":config('global.lang_constant.COURIER_DELIVERY.value')." " ;
                $store_delivery = (config('global.lang_constant.STORE_DELIVERY.'.$lang.'value') !="")?config('global.lang_constant.STORE_DELIVERY.'.$lang.'value')." ":config('global.lang_constant.STORE_DELIVERY.value')." " ;
                $rental_service = (config('global.lang_constant.RENTAL_SERVICE.'.$lang.'value')!="")?config('global.lang_constant.RENTAL_SERVICE.'.$lang.'value')." ":config('global.lang_constant.RENTAL_SERVICE.value');
                if ($driver_details->service_cat_id == 1) {
//                    $service = trim(($driver_service == 1 ? $service_lang_converted['Ride Service'][$lang] : '') . ($courier_boy == 1 ? ", ".$service_lang_converted['Courier Delivery'][$lang] : '') . ($delivery_boy == 1 ? ", ".$service_lang_converted['Store Delivery'][$lang] : ''). ($driver_details->rental_service == 1 ? ", ".$service_lang_converted['Rental Service'][$lang] : ''), ', ');
                    $service = trim(($driver_service == 1 ? $ride_service : '') . ($courier_boy == 1 ? ", ".$courier_delivery : '') . ($delivery_boy == 1 ? ", ".$store_delivery : ''). ($driver_details->rental_service == 1 ? ", ".$rental_service : ''), ', ');

                } elseif ($driver_details->service_cat_id == 2) {

//                    $service = trim(($driver_service == 1 ? $service_lang_converted['Ride Service'][$lang] : '') . ($courier_boy == 1 ? ", ".$service_lang_converted['Courier Delivery'][$lang] : '') . ($delivery_boy == 1 ?  ", ".$service_lang_converted['Store Delivery'][$lang] : '') . ($driver_details->rental_service == 1 ? ", ".$service_lang_converted['Rental Service'][$lang] : ''), ', ');
                    $service = trim(($driver_service == 1 ? $ride_service: '') . ($courier_boy == 1 ? ", ".$courier_delivery : '') . ($delivery_boy == 1 ?  ", ".$store_delivery : '') . ($driver_details->rental_service == 1 ? ", ".$rental_service : ''), ', ');
                } elseif ($driver_details->service_cat_id == 4) {
//                    $service = $service_lang_converted['Courier Delivery'][$lang];
                    $service = $courier_delivery;
                } else {
                    $service = "";
                }

                return response()->json([
                    "status" => 1,
//                    "message" => "success!",
                    "message" => __('driver_messages.1'),
                    "message_code" => 1,
                    "vehicle_type_id" => $driver_details->vehicle_type_id,
                    "manufacture_name" => $driver_details->vehicle_company,
                    "model_name" => $driver_details->model_name,
                    "model_year" => $driver_details->model_year,
                    "vehicle_plat_no" => $driver_details->plat_no,
                    "no_of_seat" => $driver_details->no_of_seat,
                    "is_rental" => $driver_details->rental_service,
                    "vehicle_color" => $driver_details->vehicle_color != Null ? $driver_details->vehicle_color : '',
                    "handicap_accessibility" => $driver_details->handicap,
                    "child_seat_accessibility" => $driver_details->chile_seat,
                    "support_weight" => $driver_details->support_weight,
                    "vehicle_type_name" => $vehicle_details->name,
                    "vehicle_type_icon" => $vehicle_details->icon_name != Null ? url('/assets/images/service-category/transport-service-type/' . $vehicle_details->icon_name) : '',
                    'driver_service' => $service,
                    'is_ride_service' => (isset($driver_service) )?$driver_service:0,

                ]);
            } else {
                return response()->json([
                    "status" => 0,
//                    "message" => "driver service not register!",
                    "message" => __('driver_messages.15'),
                    "message_code" => 15,
                ]);
            }
        } else {
            return response()->json([
                "status" => 6,
//                "message" => "driver service not register!",
                "message" => __('driver_messages.6'),
                "message_code" => 6,
            ]);
        }
    }

    public function postDriverTransportRideInvoice(Request $request)
    {
        $this->notificationClass->ApiLogDetail($logger_type = 2, $request->get('driver_id'), "postDriverTransportRideInvoice", $request->all());

        $validator = Validator::make($request->all(), [
            "driver_id" => "required|numeric",
            "access_token" => "required",
            "driver_service_id" => "required|numeric",
            "ride_id" => "required|numeric"
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

        $driver_currency = WorldCurrency::query()->where('symbol', $driver_check->currency)->first();
        if ($driver_currency == Null) {
            $driver_currency = WorldCurrency::query()->where('default_currency', 1)->first();
        }
        $currency = $driver_currency->ratio;

        $driver_details = TransportDriverDetails::query()->where('provider_service_id', $request->get('driver_service_id'))->first();
        if ($driver_details != Null) {
            $ride_details = TransportRideBook::query()->where('id', $request->get('ride_id'))->where('driver_id', $driver_details->id)->first();
            if ($ride_details != Null) {
                $user_rating = 0;
                $avatar = "";
                $user_contact_number = "";
                $user_details = User::query()->where('id', $ride_details->user_id)->first();
                if ($user_details != Null) {
                    $user_rating = $user_details->rating;
                    $user_contact_number = $user_details->country_code.$user_details->contact_number;
                    if ($user_details->avatar != Null) {
                        if (filter_var($user_details->avatar, FILTER_VALIDATE_URL) == true) {
                            $avatar = $user_details->avatar;
                        } else {
                            $avatar = url('/assets/images/profile-images/customer/' . $user_details->avatar);
                        }
                    }
                } else {
                    $avatar = "";
                }
                $service_category = ServiceCategory::query()->select('service_category.name')
                    //->join('user_ride_booking', 'user_ride_booking.service_cat_id', '=', 'service_category.id')
                    //->where('user_ride_booking.service_cat_id', $ride_details->service_cat_id)
                    ->where('id', $ride_details->service_cat_id)
                    ->first();
                $courier_details = Null;
                if (in_array($ride_details->service_cat_id, $this->courier_service_id_array)) {
                    $courier_details = TransportCourierDetails::query()->select('house_name',
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
                        ->where('ride_id', $ride_details->id)
                        ->first();
                }

                $pickup = array_map('trim', explode(",", $ride_details->pickup_latlong));
                $way_points[] = [
                    "address" => $ride_details->pickup_address,
                    "address_lat" => trim($pickup[0]),
                    "address_long" => trim($pickup[1])
                ];
                if ($ride_details->is_way_point == 1) {
                    $ride_way_point = UserRideWayPoint::query()->where('ride_id', $ride_details->id)->first();
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

                $destination = array_map('trim', explode(",", $ride_details->destination_latlong));
                $way_points[] = [
                    "address" => $ride_details->destination_address,
                    "address_lat" => trim($destination[0]),
                    "address_long" => trim($destination[1])
                ];

                $promocode_details = UsedPromocodeDetails::query()->where('id', $ride_details->promo_code)->first();
                return response()->json([
                    "status" => 1,
//                    "message" => "success!",
                    "message" => __('driver_messages.1'),
                    "message_code" => 1,
                    "ride_id" => $ride_details->id,
                    "ride_no" => $ride_details->ride_no,
                    "additional_remark" => $ride_details->additional_request,
                    "user_id" => $ride_details->user_id,
                    "service_category_id" =>$ride_details->service_cat_id,
                    "service_name" => $service_category != Null ? ucwords(strtolower($service_category->name)) : '',
                    "ride_date" => $this->notificationClass->dateLangConvert(Date('D, d M Y', strtotime($ride_details->pickup_datetime)),$driver_check->language),
                    "address_list" => $way_points,
                    "total_distance" => $ride_details->total_distance,
                    "payment_type" => $ride_details->payment_type,
                    "eta_time" => $ride_details->eta,
                    "flat_no" => '',
                    "landmark" => '',
                    "cost_per_km" => round($ride_details->vehicle_cost_for_km * $currency, 2),

                    "cost_per_min" => round($ride_details->vehicle_cost_for_min * $currency, 2),
                    "time_fare_amount" => round($ride_details->time_fare_amount * $currency, 2),

                    "tip" => round($ride_details->tip * $currency, 2),
                    "base_fare" => round($ride_details->base_fare * $currency, 2),
                    "tax" => round($ride_details->tax * $currency, 2),
                    "sub_total" => round(($ride_details->total_pay - $ride_details->tax) * $currency, 2),
                    "ride_fare" => round($ride_details->total_distance_amount * $currency, 2),
                    "min_adjustment_amount" => round($ride_details->adjustment_amount * $currency, 2),
                    "promocode_name" => $promocode_details != Null ? $promocode_details->promocode_name : '',
                    "discount" => $promocode_details != Null ? round($promocode_details->discount_amount * $currency, 2) : 0,
                    "total_pay" => round($ride_details->total_pay * $currency, 2),
                    'service_date_time' => date('Y-m-d H:i:s',strtotime($ride_details->created_at)),
                    'schedule_order_date_time' => $ride_details->pickup_datetime,
                    "ride_status" => $ride_details->status,
                    "user_profile_image" => $avatar,
                    "user_contact_number" => $user_contact_number,
                    "user_name" => $ride_details->user_name,
                    "user_rating" => round($user_rating, 1),
                    "cancel_by" => $ride_details->cancel_by != Null ? $ride_details->cancel_by : '',
                    "cancel_reason" => $ride_details->cancel_reason != Null ? $ride_details->cancel_reason : '',
                    "driver_pay_settle_status"=>$ride_details->driver_pay_settle_status,
                    "courier_details" => $courier_details,
                    "surcharge_cost" => $ride_details->surcharge_cost,

                ]);
            }
            else {
                return response()->json([
                    "status" => 0,
//                    "message" => "ride not found!",
                    "message" => __('driver_messages.26'),
                    "message_code" => 26,
                ]);
            }
        } else {
            return response()->json([
                "status" => 0,
//                "message" => "driver details not found!",
                "message" => __('driver_messages.15'),
                "message_code" => 15,
            ]);
        }
    }

    public function postDriverDeliveryRideInvoice(Request $request)
    {
        $this->notificationClass->ApiLogDetail($logger_type = 2, $request->get('driver_id'), "postDriverDeliveryRideInvoice", $request->all());

        $validator = Validator::make($request->all(), [
            "driver_id" => "required|numeric",
            "access_token" => "required",
            "driver_service_id" => "required|numeric",
            "ride_id" => "required|numeric"
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
        $driver_currency = WorldCurrency::query()->where('symbol', $driver_check->currency)->first();
        if ($driver_currency == Null) {
            $driver_currency = WorldCurrency::query()->where('default_currency', 1)->first();
        }
        $currency = $driver_currency->ratio;

        $driver_details = TransportDriverDetails::query()->where('provider_service_id', $request->get('driver_service_id'))->first();
        if ($driver_details != Null) {
            $ride_details = UserProductBooking::query()->where('id', '=', $request->get('ride_id'))->where('delivery_people_id','=', $driver_details->id)->first();
            if ($ride_details != Null) {
                $store_details = StoreDetails::query()->where('id', $ride_details->store_id)->first();
                if ($store_details == Null) {
                    return response()->json([
                        "status" => 0,
//                        "message" => "something went to wrong!",
                        "message" => __('driver_messages.9'),
                        "message_code" => 9,
                    ]);
                }
                $pickup = array_map('trim', explode(",", $store_details->address));
                $destination = array_map('trim', explode(",", $ride_details->delivery_address));
                $way_points[] = [
                    "address" => $store_details->address,
                    "address_lat" => trim($pickup[0]),
                    "address_long" => trim($pickup[1])
                ];
                $way_points[] = [
                    "address" => $ride_details->delivery_address,
                    "address_lat" => trim($destination[0]),
                    "address_long" => trim($destination[1])
                ];
                return response()->json([
                    "status" => 1,
//                    "message" => "success!",
                    "message" => __('driver_messages.1'),
                    "message_code" => 1,
                    "ride_id" => $ride_details->id,
                    "ride_no" => $ride_details->order_no,
                    "driver_pay_settle_status" => $ride_details->driver_pay_settle_status,
                    "ride_date" => $this->notificationClass->dateLangConvert(Date('D, d M Y', strtotime($ride_details->created_at)),$driver_check->language),
                    'service_date_time' => date('Y-m-d H:i:s',strtotime($ride_details->created_at)),
                    'schedule_order_date_time' => $ride_details->order_date_time,
                    "address_list" => $way_points,
                    "flat_no" => $ride_details->flat_no != Null ? $ride_details->flat_no : '',
                    "landmark" => $ride_details->landmark != Null ? $ride_details->landmark : '',
                    "delivery_cost" => round(($ride_details->delivery_cost - $ride_details->delivery_admin_commission) * $currency, 2),
                    "payment_type" => $ride_details->payment_type,
                    "total_pay" => round($ride_details->total_pay * $currency, 2)
                ]);
            }
            else {
                return response()->json([
                    "status" => 0,
//                    "message" => "ride not found!",
                    "message" => __('driver_messages.26'),
                    "message_code" => 26,
                ]);
            }
        }
        else {
            return response()->json([
                "status" => 0,
//                "message" => "driver details not found!",
                "message" => __('driver_messages.15'),
                "message_code" => 15,
            ]);
        }
    }

    public function postDriverDeliveryOrderDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
                "driver_id" => "required|numeric",
                "access_token" => "required",
                "driver_service_id" => "required|numeric",
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

        $driver_id = $request->get('driver_id');
        $driver_check = $this->driverClassApi->checkDriverRegisterAllow($driver_id, $request->get('access_token'), $request->get('driver_service_id'));
        if ($decoded['status'] = json_decode($driver_check) == false) {
            return $driver_check;
        }
        $driver_currency = WorldCurrency::query()->where('symbol', $driver_check->currency)->first();
        if ($driver_currency == Null) {
            $driver_currency = WorldCurrency::query()->where('default_currency', 1)->first();
        }
        $currency = $driver_currency->ratio;
        $driver_details = TransportDriverDetails::query()->where('provider_service_id', $request->get('driver_service_id'))->first();
        if ($driver_details != Null) {
            $order_Details = UserProductBooking::query()->where('id', $request->get('ride_id'))->first();
            if ($order_Details != Null) {
                if ($order_Details->delivery_people_id == $driver_details->id) {
                    $product_list = [];
                    $product_details = UserProductBookingQuantity::query()->where('order_id', $order_Details->id)->get();
                    if ($product_details->isEmpty()) {
                        return response()->json([
                            "status" => 0,
//                            "message" => "product details not found!",
                            "message" => __('driver_messages.9'),
                            "message_code" => 9,
                        ]);
                    }
                    foreach ($product_details as $detail) {
                        $product_list[] = [
                            "product_name" => $detail->product_name,
                            "num_of_items" => $detail->num_of_items,
                            "total_product_amount" => round((($detail->price_for_one * $detail->num_of_items) * $currency), 2)
                        ];
                    }
                    $store_name = "";
                    $store_details = StoreDetails::where('id', $order_Details->store_id)->first();
                    if ($store_details != Null) {
                        $store_name = $store_details->name;
                    }
                    return response()->json([
                        "status" => 1,
//                        "message" => "success!",
                        "message" => __('driver_messages.1'),
                        "message_code" => 1,
                        "booking_no" => $order_Details->order_no,
                        "order_date_time" => $this->notificationClass->dateLangConvert(Date('D d M, Y', strtotime($order_Details->created_at)),$driver_check->language),
                        "total_pay" => round($order_Details->total_pay * $currency, 2),
                        "payment_type" => $order_Details->payment_type - 0,
                        'service_date_time' => date('Y-m-d H:i:s',strtotime($order_Details->created_at)),
                        'schedule_order_date_time' => $order_Details->order_date_time,
                        "store_name" => $store_name,
                        "product_details" => $product_list
                    ]);
                } else {
                    return response()->json([
                        "status" => 0,
//                        "message" => "something went to wrong!",
                        "message" => __('driver_messages.9'),
                        "message_code" => 9,
                    ]);
                }
            } else {
                return response()->json([
                    "status" => 0,
//                    "message" => "order details not found!",
                    "message" => __('driver_messages.59'),
                    "message_code" => 59,
                ]);
            }
        } else {
            return response()->json([
                "status" => 0,
//                "message" => "driver details not found!",
                "message" => __('driver_messages.15'),
                "message_code" => 15,
            ]);
        }
    }

    //ride start request for schedule order
    public function postDriverStartRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
                "driver_id" => "required|numeric",
                "access_token" => "required",
                "driver_service_id" => "required|numeric",
                "service_category_id" => "required|numeric|in:1,2,4,5,6,7,8,9,10",
                "request_id" => "required|numeric",
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
        $driver_currency = WorldCurrency::query()->where('symbol', $driver_check->currency)->first();
        if ($driver_currency == Null) {
            $driver_currency = WorldCurrency::query()->where('default_currency', 1)->first();
        }
        $currency = $driver_currency->ratio;
        $service_category_id = $request->get('service_category_id');
        if (in_array($service_category_id, [1, 2, 4])) {
            $ride = TransportRideBook::query()->where('id', $request->get('request_id'))->where('service_cat_id', $service_category_id)->first();
            if ($ride != Null) {
                if ($ride->status == 1 || $ride->status == 2) {
                    if ($ride->status == 10) {
                        return response()->json([
                            "status" => 0,
//                            "message" => "Sorry, Your ride request is failed",
                            "message" => __('driver_messages.26'),
                            "message_code" => 25,
                        ]);
                    }
                    if ($ride->ride_type == 1 || $ride->pickup_datetime != Null) {
                        $pickup_date_time = (date('Y-m-d H:i:s', strtotime('-1 hour', strtotime($ride->pickup_datetime))));
                        $current_date_time = (date('Y-m-d H:i:s'));
                        if ($current_date_time >= $pickup_date_time) {

                        } else {
                            return response()->json([
                                "status" => 0,
//                                "message" => "You can start the service before 60 minutes of requested time.",
                                "message" => __('driver_messages.118'),
                                "message_code" => 118,
                            ]);
                        }
                    }
                    $driver_service_id = $request->get('driver_service_id');
                    $get_driver_id = TransportDriverDetails::query()->select('transport_driver_details.id', 'providers.first_name as driver_name', 'providers.email')
                        ->join('provider_services', 'provider_services.id', 'transport_driver_details.provider_service_id')
                        ->join('providers', 'providers.id', 'provider_services.provider_id')
                        ->where('transport_driver_details.provider_service_id', $driver_service_id)
                        ->where('providers.status', 1)
                        ->where('provider_services.status', 1)
                        ->where('provider_services.current_status', 1)
                        ->first();
                    if ($get_driver_id != Null) {
                        if ($get_driver_id->id == $ride->driver_id) {
                            $driver_service = ProviderServices::query()->where('id', $driver_service_id)->first();
                            if ($driver_service != Null) {
                                $user = User::query()->where('id', $ride->user_id)->first();
                                if ($user == Null) {
                                    return response()->json([
                                        "status" => 0,
//                                        "message" => "user not found",
                                        "message" => __('driver_messages.9'),
                                        "message_code" => 9,
                                    ]);
                                }

                                $user_profile_url = url('/assets/images/profile-images/customer');
//                                $user_profile_url = url(\App\Classes\NotificationClass::getWebCompanyWiseImage("user_avatar", ""));
                                $ride_details = TransportRideBook::query()->select('user_ride_booking.id as ride_id',
                                    'user_ride_booking.ride_no as booking_no',
                                    'user_ride_booking.service_cat_id as service_category_id',
                                    DB::raw("(CASE WHEN users.avatar != '' THEN (CASE WHEN CHAR_LENGTH(users.avatar) >= 25 THEN users.avatar ELSE concat('$user_profile_url','/',users.avatar) END) ELSE '' END) as user_profile_image"),
                                    DB::raw("(concat(users.first_name,' ',users.last_name)) as user_name"),
                                    'users.contact_number',
                                    'user_ride_booking.pickup_address as pickup_address',
                                    'user_ride_booking.pickup_latlong as pickup_latlong',
                                    'user_ride_booking.destination_address as destination_address',
                                    'user_ride_booking.destination_latlong as destination_latlong',
                                    'user_ride_booking.pickup_datetime as pickup_datetime',
                                    'user_ride_booking.total_pay as total_amount',
                                    'user_ride_booking.payment_type as payment_type',
                                    'user_ride_booking.is_way_point as is_way_point',
                                    'user_ride_booking.way_point_status as way_point_status',
                                    'user_ride_booking.status as ride_status')
                                    ->join('users', 'users.id', '=', 'user_ride_booking.user_id')
                                    ->where('user_ride_booking.id', $ride->id)->first();
                                if ($ride_details != Null) {
                                    if ($ride->ride_type == 0) {
                                        $running_service = ProviderUserRunningService::query()->where('provider_id', $driver_id)->where('user_id', $ride->user_id)->where('service_cat_id', $ride->service_cat_id)->where('booking_id', $ride->id)->first();
                                        if ($running_service == Null) {
                                            //ProviderUserRunningService::query()->where('provider_id', $driver_id)->where('user_id', $ride->user_id)->where('service_cat_id', $ride_details->service_category_id)->where('booking_id', $ride_details->ride_id)->delete();
                                            $provider_running_service = new ProviderUserRunningService();
                                            $provider_running_service->provider_id = $driver_id;
                                            $provider_running_service->user_id = $ride->user_id;
                                            $provider_running_service->service_cat_id = $ride->service_cat_id;
                                            $provider_running_service->booking_id = $ride->id;
                                            $provider_running_service->save();
                                        }
//                                        ProviderUserPendingService::query()->where('provider_id', $driver_id)->where('user_id', $ride->user_id)->where('service_cat_id', $ride->service_cat_id)->where('booking_id', $ride->id)->delete();
                                    }
                                    $pickup = array_map('trim', explode(",", $ride_details->pickup_latlong));
                                    $destination = array_map('trim', explode(",", $ride_details->destination_latlong));
                                    $way_points[] = [
                                        "address" => $ride_details->pickup_address,
                                        "address_lat" => trim($pickup[0]),
                                        "address_long" => trim($pickup[1])
                                    ];
                                    if ($ride_details->is_way_point == 1) {
                                        $ride_way_point = UserRideWayPoint::query()->where('ride_id', $ride_details->id)->first();
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
                                    if ($ride_details->destination_address != null) {
                                        $way_points[] = [
                                            "address" => $ride_details->destination_address,
                                            "address_lat" => trim($destination[0]),
                                            "address_long" => trim($destination[1])
                                        ];
                                    }
                                    $ride_details_arr = [
                                        "ride_id" => $ride_details->ride_id,
                                        "user_id" => $user->id,
                                        "user_fcm_token" => $user->device_token,
                                        "booking_no" => $ride_details->booking_no,
                                        "service_category_id" => $ride_details->service_category_id,
                                        "user_profile_image" => $ride_details->user_profile_image,
                                        "user_name" => $ride_details->user_name,
                                        "contact_number" => $ride_details->contact_number,
                                        "address_list" => $way_points,
                                        "flat_no" => '',
                                        "landmark" => '',
                                        "pickup_datetime" => $ride_details->pickup_datetime,
                                        "total_amount" => round($ride_details->total_amount * $currency, 1),
                                        "payment_type" => $ride_details->payment_type,
                                        "ride_status" => $ride_details->ride_status,
                                        "way_point_status" => $ride_details->way_point_status,
                                    ];

                                    return response()->json([
                                        "status" => 1,
//                                        "message" => "success!",
                                        "message" => __('driver_messages.1'),
                                        "message_code" => 1,
                                        "driver_current_status" => $driver_service->current_status,
                                        "ride_details" => $ride_details_arr,
                                    ]);
                                } else {
                                    return response()->json([
                                        "status" => 0,
//                                        "message" => "ride details not found!",
                                        "message" => __('driver_messages.26'),
                                        "message_code" => 26,
                                    ]);
                                }
                            } else {
                                return response()->json([
                                    "status" => 0,
//                                    "message" => "driver service not found!",
                                    "message" => __('driver_messages.9'),
                                    "message_code" => 9,
                                ]);
                            }
                        } else {
                            return response()->json([
                                "status" => 0,
//                                "message" => "something went to wrong!",
                                "message" => __('driver_messages.9'),
                                "message_code" => 9,
                            ]);
                        }

                    } else {
                        return response()->json([
                            "status" => 0,
//                            "message" => "ride details not found!",
                            "message" => __('driver_messages.26'),
                            "message_code" => 26,
                        ]);
                    }
                } elseif ($ride->status == 4) {
                    return response()->json([
                        "status" => 0,
//                        "message" => "Sorry, Your ride request is cancelled by user",
                        "message" => __('driver_messages.24'),
                        "message_code" => 24,
                    ]);
                } else {
                    return response()->json([
                        "status" => 0,
//                        "message" => "something went to wrong!",
                        "message" => __('driver_messages.9'),
                        "message_code" => 9,
                    ]);
                }
            } else {
                return response()->json([
                    "status" => 0,
//                    "message" => "ride not found!",
                    "message" => __('driver_messages.26'),
                    "message_code" => 26,
                ]);
            }
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
