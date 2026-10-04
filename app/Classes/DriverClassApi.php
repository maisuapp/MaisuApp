<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 13-12-2018
 * Time: 03:48 PM
 */

namespace App\Classes;


use App\model\GeneralSettings;
use App\model\Provider;
use App\model\ProviderDocuments;
use App\model\ProviderServices;
use App\model\RequiredDocuments;
use App\model\TransportDriverDetails;
use App\model\TransportVehicleType;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DriverClassApi
{
//        json response status [
//            0 => false,
//            1 => true,
//            2 => registration pending,
//            3 => app user blocked,
//            4 => app user access token not match,
//            5 => app user not found,
//            6 => user service not register,
//            7 => user service requested,
//            8 => user service rejected,
//          ]

    private $bike_ride;
    private $taxi_ride;
    private $courier_service;
    private $transport_service;
    private $notificationClass;

    public function __construct(NotificationClass $notificationClass)
    {
        $this->transport_service = [1, 2, 4];
        $this->bike_ride = 1;
        $this->taxi_ride = 2;
        $this->courier_service = 4;
        $this->notificationClass = $notificationClass;
    }

    //public function DriverLoginRegisterResponse($driver_details)
    public function DriverLoginRegisterResponse($driver_details,$request_type=0,$request="")
    {
        $driver_details = Provider::query()->where('id', $driver_details['id'])->first();
        if ($driver_details == Null) {
            return response()->json([
                'status' => 5,
//                'message' => 'Driver Not Found!',
                'message' => __('driver_messages.5'),
                "message_code" => 5,
            ]);
        }
        if ($driver_details['avatar'] != Null) {
            if (filter_var($driver_details['avatar'], FILTER_VALIDATE_URL) == true) {
                $avatar = $driver_details['avatar'];
            } else {
                $avatar = url('/assets/images/profile-images/provider/' . $driver_details['avatar']);
            }
        } else {
            $avatar = "";
        }
        $driver_service_id = 0;
        $driver_service_category = 0;
        if ($driver_details['status'] == 0 || $driver_details['status'] == 1) {
            $check_driver_approved_status = ProviderServices::query()->where('provider_id','=', $driver_details['id'])->whereIN('service_cat_id', $this->transport_service)->first();
            if ($check_driver_approved_status != Null) {
                $driver_service_id = $check_driver_approved_status->id;
                $driver_register_status = $check_driver_approved_status->status;
                $driver_service_category = $check_driver_approved_status->service_cat_id;
                //$driver_current_status = $check_driver_approved_status->current_status;
                //$service_rejected_reason = $check_driver_approved_status->rejected_reason;
            } else {
                return response()->json([
                    'status' => 0,
//                    'message' => 'You Are Not Register As Transport Service!',
                    'message' => __('driver_messages.287'),
                    'message_code' => 287
                ]);
            }
            if ($driver_register_status == 2) {
                return response()->json([
                    'status' => 3,
//                    'message' => 'Your account is currently blocked, so not authorised to allow any activity!',
                    'message' => __('driver_messages.3'),
                    'message_code' => 3
                ]);
            }
            $vehicle_icon_url = url('/assets/images/service-category/transport-service-type');

            $driver_vehicle_details = TransportDriverDetails::query()->select(
                'provider_services.current_status as driver_current_status',
                'transport_driver_details.driver_service',
                'transport_driver_details.delivery_boy',
                'transport_driver_details.courier_boy',
                'transport_driver_details.rental_service',
                'transport_driver_details.doc_status',
                'provider_services.service_cat_id',
                'transport_vehicle_type.name as vehicle_type_name',
                DB::raw("(CASE WHEN transport_vehicle_type.icon_name!='' THEN concat('$vehicle_icon_url','/',transport_vehicle_type.icon_name) ELSE '' END) as vehicle_type_icon"))
                ->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
                ->join('transport_vehicle_type', 'transport_vehicle_type.id', '=', 'transport_driver_details.vehicle_type_id')
                //->join('providers', 'providers.id', '=', 'provider_services.provider_id')
                ->where('transport_driver_details.provider_service_id', $driver_service_id)
                ->first();

            if ($driver_vehicle_details == Null) {
                return response()->json([
                    "status" => 0,
//                    "message" => "driver details not found!",
                    "message" => __('driver_messages.15'),
                    "message_code" => 15
                ]);
            }


            if ($request_type == 1){
                if ($request != Null) {
                    $login_type = $request['login_type'];
                    if ($login_type != "facebook" && $login_type != "google" && $login_type != "apple") {
                        $driver_details->country_code = $request['select_country_code'];
                    }
                    $driver_details->last_active = date('Y-m-d H:i:s');
                    $driver_details->currency = $request['select_currency'];
                    $driver_details->language = $request['select_language'];
                    $driver_details->device_token = $request['device_token'];
                    $driver_details->login_device = $request['login_device'];
                    $driver_details->save();
                }
                $driver_details->generateAccessToken($driver_details->id);
            }

            $driver_service = $driver_vehicle_details->driver_service;
            $delivery_boy = $driver_vehicle_details->delivery_boy;
            $courier_boy = $driver_vehicle_details->courier_boy;
            //$service_lang_converted = $this->notificationClass->serviceLangConvert();
            $lang = $driver_details['language'];
            if($lang !="en" && $lang != "" && $lang != "Null" ){
                $lang = $lang."_";
            }else{
                $lang ="";
            }
            $ride_service =  (config('global.lang_constant.RIDE_SERVICE.'.$lang.'value') !="")?config('global.lang_constant.RIDE_SERVICE.'.$lang.'value') ." ":config('global.lang_constant.RIDE_SERVICE.value')." ";
            $courier_delivery = (config('global.lang_constant.COURIER_DELIVERY.'.$lang.'value') !="")?config('global.lang_constant.COURIER_DELIVERY.'.$lang.'value')." ":config('global.lang_constant.COURIER_DELIVERY.value')." " ;
            $store_delivery = (config('global.lang_constant.STORE_DELIVERY.'.$lang.'value') !="")?config('global.lang_constant.STORE_DELIVERY.'.$lang.'value')." ":config('global.lang_constant.STORE_DELIVERY.value')." " ;
            $rental_service = (config('global.lang_constant.RENTAL_SERVICE.'.$lang.'value')!="")?config('global.lang_constant.RENTAL_SERVICE.'.$lang.'value')." ":config('global.lang_constant.RENTAL_SERVICE.value') ;
            if ($driver_vehicle_details->service_cat_id == 1) {
                $service = trim(($driver_service == 1 ? $ride_service : '') . ($courier_boy == 1 ? ", ".$courier_delivery : '') . ($delivery_boy == 1 ?  ", ".$store_delivery : '') . ($driver_vehicle_details->rental_service == 1 ? ", ".$rental_service : ''), ', ');
            } elseif ($driver_vehicle_details->service_cat_id == 2) {
                $service = trim(($driver_service == 1 ? $ride_service : '') . ($courier_boy == 1 ? ", ".$courier_delivery : '') . ($delivery_boy == 1 ?  ", ".$store_delivery : '') . ($driver_vehicle_details->rental_service == 1 ? ", ".$rental_service : ''), ', ');
            } elseif ($driver_vehicle_details->service_cat_id == 4) {
                $service = $courier_delivery ;
            } else {
                return response()->json([
                    "status" => 0,
//                    "message" => "something went to wrong!",
                    "message" => __('driver_messages.9'),
                    "message_code" => 9
                ]);
            }
            if ($driver_vehicle_details->doc_status == 0){
                $driver_register_status = 5;
            }
            return response()->json([
                'status' => 1,
//                'message' => 'Success!',
                'message' => __('driver_messages.1'),
                'message_code' => 1,
                'driver_id' => $driver_details['id'],
                'driver_verified' => $driver_details['verified_at'] != Null ? 1 : 0,
                'driver_service_id' => $driver_service_id,
                'service_status' => $driver_register_status,
                'service_category_id' => $driver_service_category,
                'driver_name' => $driver_details['first_name'],
                //'driver_last_name' => $driver_details['last_name'],
                'access_token' => $driver_details['access_token'],
                'email' => $driver_details['email'] != Null ? $driver_details['email'] : '',
                'login_type' => $driver_details['login_type'],
                'profile_image' => $avatar,
                'gender' => in_array($driver_details['gender'], [1, 2]) ? $driver_details['gender'] : 0,
                'contact_number' => $driver_details['contact_number'] != Null ? $driver_details['contact_number'] : "",
                'driver_current_status' => $driver_vehicle_details->driver_current_status,
                'driver_service' => $service,
                'vehicle_type_icon' => $driver_vehicle_details->vehicle_type_icon,
                'vehicle_type_name' => $driver_vehicle_details->vehicle_type_name,
                'select_country_code' => $driver_details['country_code'] != Null ? $driver_details['country_code'] : "",
                'select_currency' => $driver_details['currency'] != Null ? $driver_details['currency'] : "",
                'select_language' => $driver_details['language'] != Null ? $driver_details['language'] : "",
                'server_time_zone' => config('app.timezone'),
                'is_ride_service' => (isset($driver_service)  )?$driver_service:0,
            ]);
        } elseif ($driver_details['status'] == 2) {
            return response()->json([
                'status' => 3,
//                'message' => 'Your account is currently blocked, so not authorised to allow any activity!',
                'message' => __('driver_messages.3'),
                'message_code' => 3
            ]);
        } elseif ($driver_details['status'] == 3) {
            $driver_register_status = 4;
            if ($request_type == 1){
                if ($request != Null) {
                    $login_type = $request['login_type'];
                    if ($login_type != "facebook" && $login_type != "google" && $login_type != "apple") {
                        $driver_details->country_code = $request['select_country_code'];
                    }
                    $driver_details->last_active = date('Y-m-d H:i:s');
                    //$driver_details->country_code = $request['select_country_code'];
                    $driver_details->currency = $request['select_currency'];
                    $driver_details->language = $request['select_language'];
                    $driver_details->device_token = $request['device_token'];
                    $driver_details->login_device = $request['login_device'];
                    $driver_details->save();
                }
                $driver_details->generateAccessToken($driver_details->id);
            }
            return response()->json([
                'status' => 1,
//                'message' => 'Success!',
                'message' => __('driver_messages.1'),
                'message_code' => 1,
                'driver_id' => $driver_details['id'],
                'driver_verified' => $driver_details['verified_at'] != Null ? 1 : 0,
                'driver_service_id' => $driver_service_id,
                'service_status' => $driver_register_status,
                'service_category_id' => $driver_service_category,
                'driver_name' => $driver_details['first_name'],
                //'driver_last_name' => $driver_details['last_name'],
                'access_token' => $driver_details['access_token'],
                'email' => $driver_details['email'] != Null ? $driver_details['email'] : '',
                'login_type' => $driver_details['login_type'],
                'profile_image' => $avatar,
                'gender' => in_array($driver_details['gender'], [1, 2]) ? $driver_details['gender'] : 0,
                'contact_number' => $driver_details['contact_number'] != Null ? $driver_details['contact_number'] : "",
                'driver_current_status' => 0,
                'driver_service' => '',
                'vehicle_type_icon' => '',
                'vehicle_type_name' => '',
                'select_country_code' => $driver_details['country_code'] != Null ? $driver_details['country_code'] : "",
                'select_currency' => $driver_details['currency'] != Null ? $driver_details['currency'] : "",
                'select_language' => $driver_details['language'] != Null ? $driver_details['language'] : "",
                'server_time_zone' => config('app.timezone'),
                'is_ride_service' => 0,
            ]);
        } else {
            return response()->json([
                'status' => 0,
//                'message' => 'something went to wrong!',
                'message' => __('driver_messages.9'),
                'message_code' => 9
            ]);
        }
    }

    public function checkDriverRegisterAllow($driver_id, $access_token, $driver_service_id)
    {
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
            if ($driver_details->access_token != $access_token) {
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
            $driver_service = ProviderServices::query()->where('id', $driver_service_id)->where('provider_id', $driver_details->id)->first();
            if ($driver_service != Null) {
                if ($driver_service->status == 2) {
                    return response()->json([
                        "status" => 3,
//                        "message" => 'Your account is currently blocked, so not authorised to allow any activity!',
                        "message" => __('driver_messages.3'),
                        "message_code" => 3
                    ]);
                }
                if ($driver_service->status == 3) {
                    return response()->json([
                        "status" => 8,
//                        "message" => 'Your service request rejected by admin!',
                        "message" => __('driver_messages.8'),
                        "message_code" => 8
                    ]);
                }
                if ($driver_service->status == 0) {
                    return response()->json([
                        "status" => 7,
//                        "message" => 'Your service request is pending!',
                        "message" => __('driver_messages.7'),
                        "message_code" => 7
                    ]);
                }
                return $driver_details;
            } else {
                return response()->json([
                    "status" => 6,
//                    "message" => 'driver service not register!',
                    "message" => __('driver_messages.6'),
                    "message_code" => 6
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
    }

    //transport service start

    // send driver current ride status notification
    public function sendDriverCurrentRideStatusNotification($ride_id, $service_cat_id, $current_ride_status, $token)
    {
        if ($ride_id == Null && $current_ride_status == Null && $token == Null) {
            return response()->json([
                'status' => 0,
                'message' => "Something want wrong!"
            ]);
        }
        $general_settings = GeneralSettings::query()->select('fcm_server_key')->first();
        if ($general_settings == Null || $general_settings->fcm_server_key == Null) {
            return response()->json([
                'status' => 0,
                'message' => "Something want wrong!"
            ]);
        }
        $server_key = trim($general_settings->fcm_server_key);
        if ($current_ride_status == 2) {
            $message = "Driver Accepted Ride.";
        } elseif ($current_ride_status == 3) {
            $message = "Enroute to the Driver.";
        } elseif ($current_ride_status == 4) {
            $message = "Driver arrived to the user.";
        } elseif ($current_ride_status == 5) {
            $message = "Driver start the ride.";
        } elseif ($current_ride_status == 6) {
            $message = "Driver cancel the ride.";
        } elseif ($current_ride_status == 1) {
            $message = "Driver completed the ride.";
        } else {
            $ride_status = null;
            $message = "";
        }

        $title = "Rider";
        $fcmUrl = 'https://fcm.googleapis.com/fcm/send';
        $extraNotificationData = [
            'title' => $title,
            'title_code' => $title,
            'sound' => true,
//            'message' => $notification,
            'ride_id' => $ride_id,
            'driver_ride_status' => $current_ride_status,
            'message' => $message,
            'service_category_id' => $service_cat_id
        ];
        $fcmNotification = [
//            'registration_ids' => $token, //multple token array
            'to' => $token, //single token
            'data' => $extraNotificationData
        ];
        $headers = [
            'Authorization: key=' . $server_key,
//            'Authorization: key=AAAA2M4_lns:APA91bHILWv7_h1K0vIKL3xX2txzRNE0aoGTr_ovFrX9UTKMwe9AR_J9N4Z2zQIKU1qpUip8eSGTgAQDexZbA05LhcWp8Ew8NEciXSVO0TWRRj_zZbisr1M7waYeAG3cnYTZG5KLbd5X',
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

    public function getDriverRequiredDocumentList($service_cat_id, $driver_service_id , $doc_status = 0)
    {
        $required_documents = RequiredDocuments::query()->where('service_cat_id', $service_cat_id)->where('status', 1)->get();
        $document_list = [];
        if ($required_documents != Null) {
            foreach ($required_documents as $document) {
                $get_driver_document = ProviderDocuments::query()->where('provider_service_id', $driver_service_id)->where('req_document_id', $document->id)->first();
                $document_list[] = [
                    'document_id' => $document->id,
                    'service_category_id' => $document->service_cat_id,
                    'document_name' => $document->name,
                    'document_file' => $get_driver_document != Null ? url('/assets/images/provider-documents/' . $get_driver_document->document_file) : '',
                    'document_status' => $get_driver_document != Null ? $get_driver_document->status : 3
                ];
            }
            return response()->json([
                "status" => 1,
//                "message" => 'success!',
                "message" => __('driver_messages.1'),
                "message_code" => 1,
                "doc_status" => $doc_status - 0,
                "document_list" => $document_list
            ]);
        } else {
            return response()->json([
                "status" => 1,
//                "message" => 'success!',
                "message" => __('driver_messages.1'),
                "message_code" => 1,
                "doc_status" => $doc_status - 0,
                "document_list" => $document_list
            ]);
        }
    }

    public function getDriverRequiredDocumentList_old($service_cat_id, $driver_service_id, $courier_category_type, $doc_status = 0)
    {
        $required_documents = RequiredDocuments::query()->where('service_cat_id', $service_cat_id)->where('status', 1)->get();
        $document_list = [];
        if ($required_documents != Null) {
            foreach ($required_documents as $document) {
                $get_driver_document = ProviderDocuments::query()->where('provider_service_id', $driver_service_id)->where('req_document_id', $document->id)->first();
                $document_list[] = [
                    'document_id' => $document->id,
                    'document_name' => $document->name,
                    'document_file' => $get_driver_document != Null ? url('/assets/images/provider-documents/' . $get_driver_document->document_file) : '',
                    'document_status' => $get_driver_document != Null ? $get_driver_document->status : 3
                ];
            }
            return response()->json([
                "status" => 1,
                "message" => 'success!',
                "message_code" => 1,
                "document_list" => $document_list
            ]);
        } else {
            return response()->json([
                "status" => 1,
                "message" => 'success!',
                "message_code" => 1,
                "document_list" => $document_list
            ]);
        }
    }

    public function getRideDistance($lat1, $lon1, $lat2, $lon2)
    {
        $theta = $lon1 - $lon2;
        $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $miles = $dist * 60 * 1.1515;
        return round(($miles * 1.60934), 2);
//        $unit = strtoupper($unit);
//
//        if ($unit == "K") {
//            return ($miles * 1.60934);
//        } else if ($unit == "N") {
//            return ($miles * 0.8684);
//        } else {
//            return $miles;
//        }
    }

}
