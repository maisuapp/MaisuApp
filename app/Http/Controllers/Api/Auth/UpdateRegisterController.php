<?php

namespace App\Http\Controllers\Api\Auth;

use App\Classes\AdminClass;
use App\Classes\DriverClassApi;
use App\Classes\NotificationClass;
use App\Classes\OnDemandClassApi;
use App\Classes\ShopperClassApi;
use App\Classes\StoreClassApi;
use App\Classes\TokenClassApi;
use App\Classes\UserClassApi;
use App\model\GeneralSettings;
use App\model\OtherServiceProviderDetails;
use App\model\Provider;
use App\model\ProviderServices;
use App\model\ProviderVerification;
use App\model\ServiceCategory;
use App\model\ShopperProviderDetails;
use App\model\TransportDriverDetails;
use App\model\User;
use App\model\UserVerification;
use App\model\WorldCurrency;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Intervention\Image\Facades\Image;
use Twilio\Rest\Client;

class UpdateRegisterController extends Controller
{
//        json response status [
//            0 => false,
//            1 => true,
//            2 => registration pending,
//            3 => app user blocked,
//            4 => app user access token not match,
//            5 => app user not found
//          ]

    private $userClassApi;
    private $storeClassApi;
    private $driverClassApi;
    private $onDemandClassApi;
    private $tokenClassApi;
    private $on_demand_service_id_array;
    private $notificationClass;
    private $adminClass;

    public function __construct(TokenClassApi $tokenClassApi, UserClassApi $userClassApi, DriverClassApi $driverClassApi, storeClassApi $storeClassApi, OnDemandClassApi $onDemandClassApi, NotificationClass $notificationClass, AdminClass $adminClass)
    {
        $this->userClassApi = $userClassApi;
        $this->storeClassApi = $storeClassApi;
        $this->driverClassApi = $driverClassApi;
        $this->onDemandClassApi = $onDemandClassApi;
        $this->notificationClass = $notificationClass;
        $this->tokenClassApi = $tokenClassApi;
        $this->adminClass = $adminClass;
//        $this->on_demand_service_id_array = [11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30];
        $service_cat = ServiceCategory::query()->whereIN('category_type', [3, 4])->where('status', 1)->get()->pluck('id')->toArray();
        $this->on_demand_service_id_array = $service_cat;
    }

    //customer
    public function postCustomerSocialRequiredField(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "user_id" => "required|numeric",
            "access_token" => "required",
//            "email" => "required|email|unique:users,email," . $request->get('user_id'),
            "email" => [
                "required",
                "email",
                Rule::unique('users','email')->where(function ($query) use ($request) {
                    $query->where('email', '=', $request->get('email'));
                    $query->where('id', '!=', $request->get('user_id'));
                    $query->where('deleted_at', '=', null);
                }),
            ],
            "select_country_code" => "required",
//            "contact_number" => "required|numeric|unique:users,contact_number," . $request->get('user_id')
            'contact_number' => [
                'required','numeric',
                Rule::unique('users')->where(function($query) use($request) {
                    $query->where('contact_number', '=', $request->get('contact_number'));
                    $query->where('country_code', '=', $request->get('select_country_code'));
                    $query->where('id', '!=', $request->get('user_id'));
                    $query->where('deleted_at', '=', null);
                })
            ],
        ]);
        if ($validator->fails()) {
            $failedRules = $validator->failed();
            if (isset($failedRules['email']['Unique'])) {
                return response()->json([
                    "status" => 0,
                    "message" => $validator->errors()->first(),
                    "message_code" => 11,
                ]);
            }
            if (isset($failedRules['contact_number']['Unique'])) {
                return response()->json([
                    "status" => 0,
                    "message" => $validator->errors()->first(),
                    "message_code" => 12,
                ]);
            }
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }

        $user_details = User::query()->select('id', 'first_name', 'country_code', 'last_name', 'email', 'verified_at', 'contact_number', 'login_type', 'login_id', 'password', 'avatar', 'invite_code', 'access_token', 'device_token', 'status as user_status')
            ->where('id', $request->get('user_id'))->whereNull('users.deleted_at')->first();
        if ($user_details != Null) {
            if ($user_details->access_token != $request->get('access_token')) {
                return response()->json([
                    'status' => 4,
//                    'message' => "Access Token Not Match!",
                    'message' => __('user_messages.4'),
                    "message_code" => 4,
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
            $user_details->email = $request->get('email');
            $user_details->contact_number = $request->get('contact_number');
            $user_details->country_code = $request->get('select_country_code');
            $user_details->save();
            $this->tokenClassApi->sendUserSmsVerification($user_details->id);
            return response()->json([
                'status' => 1,
//                'message' => 'Success!',
                'message' => __('user_messages.1'),
                "message_code" => 1,
                'email' => $user_details->email,
                'contact_number' => $user_details->contact_number,
                'select_country_code' => $user_details->country_code,
            ]);
        } else {
            return response()->json([
                'status' => 5,
//                'message' => "User not found!",
                'message' => __('user_messages.5'),
                "message_code" => 5,
            ]);
        }
    }

    public function postUpdateCustomerDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "user_id" => "required|numeric",
            "access_token" => "required",
            "full_name" => "required",
            "last_name" => "nullable",
            "emergency_contact" => "nullable",
            //"gender" => "required|in:1,2"
//            "email" => "required|email|unique:users,email," . $request->get('user_id'),
            "email" => [
                "required",
                "email",
                Rule::unique('users','email')->where(function ($query) use ($request) {
                    $query->where('email', '=', $request->get('email'));
                    $query->where('id', '!=', $request->get('user_id'));
                    $query->where('deleted_at', '=', null);
                }),
            ],
            "select_country_code" => "required",
//            "contact_number" => "required",
            'contact_number' => [
                'required','numeric',
                Rule::unique('users')->where(function($query) use($request) {
                    $query->where('contact_number', '=', $request->get('contact_number'));
                    $query->where('country_code', '=', $request->get('select_country_code'));
                    $query->where('id', '!=', $request->get('user_id'));
                    $query->where('deleted_at', '=', null);
                })
            ],
            //"contact_number" => "required|numeric|unique:users,contact_number," . $request->get('user_id'),
        ],
            [ 'contact_number.unique' => __('user_messages.305')]);
        if ($validator->fails()) {
            $failedRules = $validator->failed();
            if (isset($failedRules['email']['Unique'])) {
                return response()->json([
                    "status" => 0,
                    "message" => __('user_messages.332'),
                    "message_code" => 332,
                ]);
            }
            return response()->json([
                "status" => 0,
                "message" => __('user_messages.305'),
                "message_code" => 305,
            ]);
        }

        $check_contact = User::query()->where('contact_number',$request->get('contact_number'))
            ->where('country_code',$request->get('select_country_code'))
            ->whereNotIn('id',[$request->get('user_id')])
            ->whereNull('users.deleted_at')
            ->count();
        if($check_contact > 0){
            return response()->json([
                "status" => 0,
//                "message" => "Contact Number already been taken!",
                'message' => __('user_messages.12'),
                "message_code" => 12,
            ]);
        }

        $user_details = $this->userClassApi->checkUserAllow($request->get('user_id'), $request->get('access_token'));
        if ($decoded['status'] = json_decode($user_details) == false) {
            return $user_details;
        }
        $user_details->first_name = $request->get('full_name');
        if ($request->get('last_name') != Null) {
            $user_details->last_name = $request->get('last_name');
        }
        $user_details->gender = $request->get('gender');
        $user_details->contact_number = $request->get('contact_number');
        $user_details->country_code = $request->get('select_country_code');
        $user_details->email = $request->get('email');
        //$user_details->contact_number = $request->get('contact_number');
        if ($request->file('profile_image') != Null) {
            if (\File::exists(public_path('/assets/images/profile-images/customer/' . $user_details->avatar))) {
                \File::delete(public_path('/assets/images/profile-images/customer/' . $user_details->avatar));
            }
            $destinationPath = public_path('/assets/images/profile-images/customer/');
            $file = $request->file('profile_image');
            $img = Image::make($file->getRealPath());
            $img->orientate();
            $file_new = rand(1, 9) . date('siHYdm') . rand(1, 9) . '.' . $file->getClientOriginalExtension();
            $img->resize(300, 300, function ($constraint) {
                $constraint->aspectRatio();
            })->save($destinationPath . $file_new);
            $user_details->avatar = $file_new;
        }
        $user_details->emergency_contact = $request->get('emergency_contact') != Null ? $request->get('emergency_contact') : Null;
        $user_details->save();

        if ($user_details->avatar != Null) {
            if (filter_var($user_details->avatar, FILTER_VALIDATE_URL) == true) {
                $avatar = $user_details->avatar;
            } else {
                $avatar = url('/assets/images/profile-images/customer/' . $user_details->avatar);
            }
        } else {
            $avatar = Null;
        }
        return $this->userClassApi->userLoginRegisterUpdateDetails($user_details);
    }

    public function postCustomerUpdateCountryAndCurrency(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "user_id" => "required|numeric",
            "access_token" => "required",
            'select_language' => "required",
            'select_country_code' => "required",
            'select_currency' => "required"
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }

        $user_details = $this->userClassApi->checkUserAllow($request->get('user_id'), $request->get('access_token'));
        if ($decoded['status'] = json_decode($user_details) == false) {
            return $user_details;
        }
        $user_details->country_code = $request->get('select_country_code');
        $user_details->currency = $request->get('select_currency');
        $user_details->language = $request->get('select_language');
        $user_details->save();
        return response()->json([
            'status' => 1,
//            'message' => "success!",
            'message' => __('user_messages.1'),
            "message_code" => 1,
        ]);

    }

    //driver
    public function postDriverSocialRequiredField(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "driver_id" => "required|numeric",
            "access_token" => "required",
//            "email" => "required|email|unique:providers,email," . $request->get('driver_id'),
            "email" => [
                "required",
                "email",
                Rule::unique('providers','email')->where(function ($query) use ($request) {
                    $query->where('email', '=', $request->get('email'));
                    $query->where('id', '!=', $request->get('driver_id'));
                    $query->where('deleted_at', '=', null);
                }),
            ],
            "select_country_code" => "required",
            //"contact_number" => "required|numeric|unique:providers,contact_number," . $request->get('driver_id')
//            "contact_number" => "required|numeric",
            'contact_number' => [
                'required','numeric',
                Rule::unique('providers')->where(function($query) use($request) {
                    $query->where('contact_number', '=', $request->get('contact_number'));
                    $query->where('country_code', '=', $request->get('select_country_code'));
                    $query->where('id', '!=', $request->get('driver_id'));
                    $query->where('deleted_at', '=', null);
                })
            ],
        ]);
        if ($validator->fails()) {
            $failedRules = $validator->failed();
            if (isset($failedRules['email']['Unique'])) {
                return response()->json([
                    "status" => 0,
                    "message" => $validator->errors()->first(),
                    "message_code" => 11,
                ]);
            }
            if (isset($failedRules['contact_number']['Unique'])) {
                return response()->json([
                    "status" => 0,
                    "message" => $validator->errors()->first(),
                    "message_code" => 12,
                ]);
            }
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }
        $driver_id = $request->get('driver_id');
        $check_contact = Provider::query()->where('contact_number',$request->get('contact_number'))
            ->where('country_code',$request->get('select_country_code'))
            ->where('provider_type', '=',2)
            ->whereNull('providers.deleted_at')
            ->count();
        if($check_contact > 0) {
            return response()->json([
                "status" => 0,
//                "message" => "Contact Number already been taken!",
                "message" => __('driver_messages.12'),
                "message_code" => 12,
            ]);
        }
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
        $driver_details->email = $request->get('email');
        $driver_details->contact_number = $request->get('contact_number');
        $driver_details->country_code = $request->get('select_country_code');
        $driver_details->save();
        $this->tokenClassApi->sendProviderSmsVerification($driver_details->id);
        return $this->driverClassApi->DriverLoginRegisterResponse($driver_details);
    }

    public function postUpdateDriverDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "driver_id" => "required|numeric",
            "access_token" => "required",
            "driver_service_id" => "required|numeric",
            "full_name" => "required",
            //"last_name" => "required",
            "profile_image" => "nullable",
            "select_country_code" => "required",
//            "email" => "required|email|unique:providers,email," . $request->get('driver_id'),
            "email" => [
                "required",
                "email",
                Rule::unique('providers','email')->where(function ($query) use ($request) {
                    $query->where('email', '=', $request->get('email'));
                    $query->where('id', '!=', $request->get('driver_id'));
                    $query->where('deleted_at', '=', null);
                }),
            ],
            //"contact_number" => "required|numeric|unique:providers,contact_number," . $request->get('driver_id'),
//            "contact_number" => "required",
            "contact_number" => [
                "required",
                "numeric",
                Rule::unique('providers')->where(function ($query) use ($request) {
                    $query->where('contact_number', '=', $request->get('contact_number'));
                    $query->where('id', '!=', $request->get('driver_id'));
                    $query->where('deleted_at', '=', null);
                }),
            ],
            "gender" => "required|numeric|in:1,2"
        ]);
        if ($validator->fails()) {
            $failedRules = $validator->failed();
            /*if (isset($failedRules['contact_number']['Unique'])) {
                return response()->json([
                    "status" => 0,
                    "message" => $validator->errors()->first(),
                    "message_code" => 12,
                ]);
            }*/
            if (isset($failedRules['email']['Unique'])) {
                return response()->json([
                    "status" => 0,
                    "message" => $validator->errors()->first(),
                    "message_code" => 11,
                ]);
            }
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }
        $check_contact = Provider::query()->where('contact_number',$request->get('contact_number'))
            ->where('country_code',$request->get('select_country_code'))
            ->whereNotIn('id',[$request->get('driver_id')])
//            ->where('provider_type', '=',2)
            ->whereNull('providers.deleted_at')
            ->count();
        if($check_contact > 0){
            return response()->json([
                "status" => 0,
//                "message" => "Contact Number already been taken!",
                "message" => __('driver_messages.12'),
                "message_code" => 12,
            ]);
        }

        $driver_details = $this->driverClassApi->checkDriverRegisterAllow($request->get('driver_id'), $request->get('access_token'), $request->get('driver_service_id'));
        if ($decoded['status'] = json_decode($driver_details) == false) {
            return $driver_details;
        }
        $driver_details = Provider::query()->where('id', $request->get('driver_id'))->whereNull('providers.deleted_at')->first();
        if ($driver_details == Null) {
            return response()->json([
                "status" => 0,
                "message" => __('driver_messages.15'),
                "message_code" => 15,
            ]);
        }
        $driver_details->first_name = ucwords(strtolower(trim($request->get('full_name'))));
        //$driver_details->last_name = ucwords(strtolower(trim($request->get('last_name'))));
        $driver_details->email = $request->get('email');
        $driver_details->gender = $request->get('gender');
        $driver_details->contact_number = $request->get('contact_number');
        $driver_details->country_code = $request->get('select_country_code');
        if ($request->file('profile_image') != Null) {
            if (\File::exists(public_path('/assets/images/profile-images/provider/' . $driver_details->avatar))) {
                \File::delete(public_path('/assets/images/profile-images/provider/' . $driver_details->avatar));
            }
            $destinationPath = public_path('/assets/images/profile-images/provider/');
            $file = $request->file('profile_image');
            $img = Image::make($file->getRealPath());
            $img->orientate();
            $file_new = rand(1, 9) . date('siHYdm') . rand(1, 9) . '.' . $file->getClientOriginalExtension();
            $img->resize(300, 300, function ($constraint) {
                $constraint->aspectRatio();
            })->save($destinationPath . $file_new);
            $driver_details->avatar = $file_new;
        }
        $driver_details->save();

        if ($driver_details->avatar != Null) {
            if (filter_var($driver_details->avatar, FILTER_VALIDATE_URL) == true) {
                $avatar = $driver_details->avatar;
            } else {
                $avatar = url('/assets/images/profile-images/provider/' . $driver_details->avatar);
            }
        } else {
            $avatar = '';
        }
        return response()->json([
            'status' => 1,
//            'message' => 'Success!',
            'message' => __('driver_messages.1'),
            "message_code" => 1,
            'driver_name' => $driver_details->first_name,
            //'driver_last_name' => $driver_details->last_name,
            'profile_image' => $avatar,
            'gender' => $driver_details->gender - 0,
            "contact_number" => $driver_details->contact_number,
            "select_country_code" => $driver_details->country_code,
            "email" => $driver_details->email
        ]);
    }

    public function postDriverUpdateCountryAndCurrency(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "driver_id" => "required|numeric",
            "access_token" => "required",
            "driver_service_id" => "required|numeric",
            'select_language' => "required",
            'select_country_code' => "required",
            'select_currency' => "required"
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }

        $driver_details = $this->driverClassApi->checkDriverRegisterAllow($request->get('driver_id'), $request->get('access_token'), $request->get('driver_service_id'));
        if ($decoded['status'] = json_decode($driver_details) == false) {
            return $driver_details;
        }
        $driver_details = Provider::query()->where('id', $request->get('driver_id'))->where('provider_type', '=',2)->whereNull('providers.deleted_at')->first();
        if ($driver_details == Null) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }
        //$driver_details->country_code = $request->get('select_country_code');
        $driver_details->currency = $request->get('select_currency');
        $driver_details->language = $request->get('select_language');
        $driver_details->save();

        $driver_vehicle_details = TransportDriverDetails::query()->select(
            'transport_driver_details.driver_service',
            'transport_driver_details.delivery_boy',
            'transport_driver_details.courier_boy',
            'transport_driver_details.rental_service',
            'provider_services.service_cat_id')
            ->join('provider_services', 'provider_services.id', '=', 'transport_driver_details.provider_service_id')
            ->join('providers', 'providers.id', '=', 'provider_services.provider_id')
            ->where('providers.id', $driver_details->id)
            ->whereNull('providers.deleted_at')
            ->first();
        if ($driver_vehicle_details == Null) {
            return response()->json([
                "status" => 0,
//                "message" => "driver details not found!",
                "message" => __('driver_messages.15'),
                "message_code" => 15
            ]);
        }

        $driver_service = $driver_vehicle_details->driver_service;
        $delivery_boy = $driver_vehicle_details->delivery_boy;
        $courier_boy = $driver_vehicle_details->courier_boy;
//        $service_lang_converted = $this->notificationClass->serviceLangConvert();
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
            //$service = trim(($driver_service == 1 ? $service_lang_converted['Bike Riding'][$lang] : '') . ($courier_boy == 1 ? ", ".$service_lang_converted['Courier Delivery'][$lang] : '') . ($delivery_boy == 1 ? ", ".$service_lang_converted['Store Delivery'][$lang] : ''), ', ');
            $service = trim(($driver_service == 1 ? $ride_service : '') . ($courier_boy == 1 ? ", ".$courier_delivery : '') . ($delivery_boy == 1 ? ", ".$store_delivery : '').($driver_vehicle_details->rental_service == 1 ? ", ".$rental_service : ''), ', ');
        } elseif ($driver_vehicle_details->service_cat_id == 2){
            //$service = $service_lang_converted['Taxi Riding'][$lang];
            //$service = $ride_service;
            //if ($driver_vehicle_details->rental_service == 1){
            //    $service = $service . ", " . trim($service_lang_converted['Rental Service'][$lang]);
            //}
            //$service = trim(($driver_service == 1 ? "Ride Service" : '') . ($courier_boy == 1 ? ", Courier Service" : '') . ($delivery_boy == 1 ? ", Store Delivery" : '') . ($driver_vehicle_details->rental_service == 1 ? ", Rental Service" : ''), ', ');
            $service = trim(($driver_service == 1 ? $ride_service : '') . ($courier_boy == 1 ? ", ".$courier_delivery : '') . ($delivery_boy == 1 ? ", ".$store_delivery : '').($driver_vehicle_details->rental_service == 1 ? ", ".$rental_service : ''), ', ');
        } elseif ($driver_vehicle_details->service_cat_id == 4) {
            $service = $courier_delivery;
        } else {
            return response()->json([
                "status" => 0,
//                "message" => "something went to wrong!",
                "message" => __('driver_messages.9'),
                "message_code" => 9
            ]);
        }

        return response()->json([
            'status' => 1,
//            'message' => "success!",
            'message' => __('driver_messages.1'),
            "message_code" => 1,
            'driver_service' => $service,
        ]);
    }

    //store
    public function postStoreSocialRequiredField(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "store_id" => "required|numeric",
            "access_token" => "required",
//            "email" => "required|email|unique:providers,email," . $request->get('store_id'),
            "email" => [
                "required",
                "email",
                Rule::unique('providers','email')->where(function ($query) use ($request) {
                    $query->where('email', '=', $request->get('email'));
                    $query->where('id', '!=', $request->get('store_id'));
                    $query->where('deleted_at', '=', null);
                }),
            ],
            "select_country_code" => "required",
            //"contact_number" => "required|numeric|unique:providers,contact_number," . $request->get('store_id')
//            "contact_number" => "required|numeric",
            'contact_number' => [
                'required','numeric',
                Rule::unique('providers')->where(function($query) use($request) {
                    $query->where('contact_number', '=', $request->get('contact_number'));
                    $query->where('country_code', '=', $request->get('select_country_code'));
                    $query->where('id', '!=', $request->get('store_id'));
                    $query->where('deleted_at', '=', null);
                })
            ],
        ]);
        if ($validator->fails()) {
            $failedRules = $validator->failed();
            if (isset($failedRules['email']['Unique'])) {
                return response()->json([
                    "status" => 0,
                    "message" => $validator->errors()->first(),
                    "message_code" => 11,
                ]);
            }
            if (isset($failedRules['contact_number']['Unique'])) {
                return response()->json([
                    "status" => 0,
                    "message" => $validator->errors()->first(),
                    "message_code" => 12,
                ]);
            }
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }
        $check_contact = Provider::query()->where('contact_number',$request->get('contact_number'))
            ->where('country_code',$request->get('select_country_code'))
            ->whereNotIn('id',[$request->get('store_id')])
            ->where('provider_type', '=',1)
            ->whereNull('providers.deleted_at')
            ->count();
        if($check_contact > 0){
            return response()->json([
                "status" => 0,
//                "message" => "Contact Number already been taken!",
                "message" => __('store_messages.12'),
                "message_code" => 12,
            ]);
        }

        $store_id = $request->get('store_id');
        $provider_details = Provider::query()->where('id', $store_id)->where('provider_type', '=',1)->whereNull('providers.deleted_at')->first();
        if ($provider_details != Null) {
            if ($provider_details->status == 2) {
                return response()->json([
                    "status" => 3,
//                    "message" => 'Your account is currently blocked, so not authorised to allow any activity!',
                    "message" => __('store_messages.3'),
                    "message_code" => 3,
                ]);
            }
            if ($provider_details->access_token != $request->get('access_token')) {
                return response()->json([
                    'status' => 4,
//                    'message' => "Access Token Not Match!",
                    'message' => __('store_messages.4'),
                    "message_code" => 4,
                ]);
            }
        } else {
            return response()->json([
                "status" => 5,
//                "message" => 'driver not found!',
                "message" => __('store_messages.5'),
                "message_code" => 5,
            ]);
        }
        $provider_details->email = $request->get('email');
        $provider_details->contact_number = $request->get('contact_number');
        $provider_details->country_code = $request->get('select_country_code');
        $provider_details->save();
        $this->tokenClassApi->sendProviderSmsVerification($provider_details->id);
        return $this->storeClassApi->storeLoginRegisterResponse($provider_details);
    }

    public function postUpdateStoreDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "store_id" => "required|numeric",
            "access_token" => "required",
            "store_service_id" => "required|numeric",
            "full_name" => "required",
            //"last_name" => "required",
            "profile_image" => "nullable",
            "gender" => "required|numeric|in:1,2",
            "select_country_code" => "required",
//            "email" => "required|email|unique:providers,email," . $request->get('store_id'),
            //"contact_number" => "required|numeric|unique:providers,contact_number," . $request->get('store_id'),
//            "contact_number" => "required",
            "email" => [
                "required",
                "email",
                Rule::unique('providers','email')->where(function ($query) use ($request) {
                    $query->where('email', '=', $request->get('email'));
                    $query->where('id', '!=', $request->get('store_id'));
                    $query->where('deleted_at', '=', null);
                }),
            ],
            "contact_number" => [
                "required",
                "numeric",
                Rule::unique('providers')->where(function ($query) use ($request) {
                    $query->where('contact_number', '=', $request->get('contact_number'));
                    $query->where('id', '!=', $request->get('store_id'));
                    $query->where('deleted_at', '=', null);
                }),
            ],
        ]);
        if ($validator->fails()) {
            $failedRules = $validator->failed();
            /*if (isset($failedRules['contact_number']['Unique'])) {
                return response()->json([
                    "status" => 0,
                    "message" => $validator->errors()->first(),
                    "message_code" => 12,
                ]);
            }*/
            if (isset($failedRules['email']['Unique'])) {
                return response()->json([
                    "status" => 0,
                    "message" => $validator->errors()->first(),
                    "message_code" => 11,
                ]);
            }
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }

        $check_contact = Provider::query()->where('contact_number',$request->get('contact_number'))
            ->where('country_code',$request->get('select_country_code'))
            ->whereNotIn('id',[$request->get('store_id')])
//            ->where('provider_type', '=',1)
            ->whereNull('providers.deleted_at')
            ->count();
        if($check_contact > 0){
            return response()->json([
                "status" => 0,
//                "message" => "Contact Number already been taken!",
                "message" => __('store_messages.12'),
                "message_code" => 12,
            ]);
        }

        $store_details = $this->storeClassApi->checkStoreRegisterAllow($request->get('store_id'), $request->get('access_token'), $request->get('store_service_id'));
        if ($decoded['status'] = json_decode($store_details) == false) {
            return $store_details;
        }
        $provider_details = Provider::query()->where('id', $request->get('store_id'))->whereNull('providers.deleted_at')->first();
        if ($provider_details != Null) {
            $provider_details->first_name = ucwords(strtolower(trim($request->get('full_name'))));
            //$provider_details->last_name = ucwords(strtolower(trim($request->get('last_name'))));
            $provider_details->email = $request->get('email');
            $provider_details->gender = $request->get('gender');
            $provider_details->contact_number = $request->get('contact_number');
            $provider_details->country_code = $request->get('select_country_code');
            if ($request->file('profile_image') != Null) {
                if (\File::exists(public_path('/assets/images/profile-images/provider/' . $provider_details->avatar))) {
                    \File::delete(public_path('/assets/images/profile-images/provider/' . $provider_details->avatar));
                }
                //$file = $request->file('profile_image');
                //$file_new = rand(1,9) . date('sihYdm') .rand(1,9). '.' . $file->getClientOriginalExtension();
                //$file->move(public_path() . '/assets/images/profile-images/provider/', $file_new);
                //$provider_details->avatar = $file_new;

                $destinationPath = public_path('/assets/images/profile-images/provider/');
                $file = $request->file('profile_image');
                $img = Image::make($file->getRealPath());
                $img->orientate();
                $file_new = rand(1, 9) . date('siHYdm') . rand(1, 9) . '.' . $file->getClientOriginalExtension();
                $img->resize(300, 300, function ($constraint) {
                    $constraint->aspectRatio();
                })->save($destinationPath . $file_new);
                $provider_details->avatar = $file_new;
            }
            $provider_details->save();

            if ($provider_details->avatar != Null) {
                if (filter_var($provider_details->avatar, FILTER_VALIDATE_URL) == true) {
                    $avatar = $provider_details->avatar;
                } else {
                    $avatar = url('/assets/images/profile-images/provider/' . $provider_details->avatar);
                }
            } else {
                $avatar = '';
            }
            return response()->json([
                'status' => 1,
//                'message' => 'Success!',
                'message' => __('store_messages.1'),
                "message_code" => 1,
                'store_provider_name' => $provider_details->first_name,
                //'store_last_name' => $provider_details->last_name,
                'provider_profile_image' => $avatar,
                'provider_gender' => $provider_details->gender - 0,
                'contact_number' =>  $provider_details->contact_number,
                'select_country_code' => $provider_details->country_code,
                'email' =>  $provider_details->email
            ]);
        } else {
            return response()->json([
                "status" => 0,
//                "message" => "Store provider details not found!",
                "message" => __('store_messages.160'),
                "message_code" => 160,
            ]);
        }
    }

    public function postStoreUpdateCountryAndCurrency(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "store_id" => "required|numeric",
            "access_token" => "required",
            "store_service_id" => "required|numeric",
            'select_language' => "required",
            'select_country_code' => "required",
            'select_currency' => "required"
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }

        $store_details = $this->storeClassApi->checkStoreRegisterAllow($request->get('store_id'), $request->get('access_token'), $request->get('store_service_id'));
        if ($decoded['status'] = json_decode($store_details) == false) {
            return $store_details;
        }
        $provider_details = Provider::query()->where('id', $request->get('store_id'))->whereNull('providers.deleted_at')->first();
        if ($provider_details == Null) {
            return response()->json([
                "status" => 0,
                "message" => __('store_messages.160'),
                "message_code" => 160,
            ]);
        }

        //$provider_details->country_code = $request->get('select_country_code');
        $provider_details->currency = $request->get('select_currency');
        $provider_details->language = $request->get('select_language');
        $provider_details->save();

        return response()->json([
            'status' => 1,
//            'message' => "success!",
            'message' => __('store_messages.1'),
            'message_code' => 1,
        ]);
    }

    //on-demand
    public function postOnDemandSocialRequiredField(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "provider_id" => "required|numeric",
            "access_token" => "required",
//            "email" => "required|email|unique:providers,email," . $request->get('provider_id'),
            "email" => [
                "required",
                "email",
                Rule::unique('providers','email')->where(function ($query) use ($request) {
                    $query->where('email', '=', $request->get('email'));
                    $query->where('id', '!=', $request->get('provider_id'));
                    $query->where('deleted_at', '=', null);
                }),
            ],
            "select_country_code" => "required",
            //"contact_number" => "required|numeric|unique:providers,contact_number," . $request->get('provider_id')
//            "contact_number" => "required"
            'contact_number' => [
                'required','numeric',
                Rule::unique('providers')->where(function($query) use($request) {
                    $query->where('contact_number', '=', $request->get('contact_number'));
                    $query->where('country_code', '=', $request->get('select_country_code'));
                    $query->where('id', '!=', $request->get('provider_id'));
                    $query->where('deleted_at', '=', null);
                })
            ],
        ]);
        if ($validator->fails()) {
            if (isset($failedRules['email']['Unique'])) {
                return response()->json([
                    "status" => 0,
                    "message" => $validator->errors()->first(),
                    "message_code" => 11,
                ]);
            }
            if (isset($failedRules['contact_number']['Unique'])) {
                return response()->json([
                    "status" => 0,
                    "message" => $validator->errors()->first(),
                    "message_code" => 12,
                ]);
            }
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }

        $check_contact = Provider::query()->where('contact_number',$request->get('contact_number'))
            ->where('country_code',$request->get('select_country_code'))
            ->where('provider_type', '=',3)
            ->whereNull('providers.deleted_at')
            ->count();
        if($check_contact > 0){
            return response()->json([
                "status" => 0,
//                "message" => "Contact Number already been taken!",
                "message" =>  __('provider_messages.12'),
                "message_code" => 12,
            ]);
        }
        $provider_id = $request->get('provider_id');
        $provider_details = Provider::query()->where('id', $provider_id)->whereNull('providers.deleted_at')->first();
        if ($provider_details != Null) {
            if ($provider_details->status == 2) {
                return response()->json([
                    "status" => 3,
//                    "message" => 'Your account is currently blocked, so not authorised to allow any activity!',
                    "message" =>  __('provider_messages.3'),
                    "message_code" => 3
                ]);
            }
            if ($provider_details->access_token != $request->get('access_token')) {
                return response()->json([
                    'status' => 4,
//                    'message' => "Access Token Not Match!",
                    'message' =>  __('provider_messages.4'),
                    "message_code" => 4
                ]);
            }
            $provider_details = Provider::query()->where('id', $provider_id)->where('provider_type', '=',3)->whereNull('providers.deleted_at')->first();
            if ($provider_details == Null) {
                return response()->json([
                    "status" => 5,
//                    "message" => 'driver not found!',
                    "message" =>  __('provider_messages.5'),
                    "message_code" => 5,
                ]);
            }
            $provider_details->email = $request->get('email');
            $provider_details->contact_number = $request->get('contact_number');
            $provider_details->country_code = $request->get('select_country_code');
            $provider_details->save();
            $this->tokenClassApi->sendProviderSmsVerification($provider_details->id);
            return $this->onDemandClassApi->onDemandLoginRegisterResponse($provider_details);
        } else {
            return response()->json([
                "status" => 5,
//                "message" => 'provider not found!',
                "message" =>  __('provider_messages.5'),
                "message_code" => 5
            ]);
        }
    }

    public function postUpdateOnDemandProviderDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "provider_id" => "required|integer",
            "access_token" => "required|numeric",
            "is_update" => "required|numeric|in:0,1",
        ]);
        if ($validator->fails()) {
            /*if (isset($failedRules['contact_number']['Unique'])) {
                return response()->json([
                    "status" => 0,
                    "message" => $validator->errors()->first(),
                    "message_code" => 12,
                ]);
            }*/
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }

        $provider_id = $request->get('provider_id');
        $check_provider_details = $this->onDemandClassApi->providerRegisterAllow($provider_id, $request->get('access_token'));
        if ($decoded['status'] = json_decode($check_provider_details) == false) {
            return $check_provider_details;
        }

        $provider_details = Provider::query()->where('id', $request->get('provider_id'))->where('provider_type', '=',3)->whereNull('providers.deleted_at')->first();
        if ($provider_details != Null) {
            if($request->get('is_update') == 1)
            {
                $validator = Validator::make($request->all(), [
                    "full_name" => "required",
                    "last_name" => "nullable",
                    "profile_image" => "nullable",
                    "gender" => "required|numeric|in:1,2",
                    "address" => "required",
                    "lat" => "required|numeric",
                    "long" => "required|numeric",
                    //"landmark" => "required",
                    "landmark" => "nullable",
//                    "service_radius" => "required|integer",
                    "select_country_code" => "required",
//                    "email" => "required|email|unique:providers,email," . $request->get('provider_id'),
                    "email" => [
                        "required",
                        "email",
                        Rule::unique('providers')->where(function ($query) use ($request) {
                            $query->where('email', '=', $request->get('email'));
                            $query->where('id', '!=', $request->get('provider_id'));
                            $query->where('deleted_at', '=', null);
                        }),
                    ],
                    //"contact_number" => "required|numeric|unique:providers,contact_number," . $request->get('provider_id'),
//                    "contact_number" => "required",
                    "contact_number" => [
                        "required",
                        "numeric",
                        Rule::unique('providers')->where(function ($query) use ($request) {
                            $query->where('contact_number', '=', $request->get('contact_number'));
                            $query->where('id', '!=', $request->get('provider_id'));
                            $query->where('deleted_at', '=', null);
                        }),
                    ],
                    "min_order" => "required|numeric",

                ]);
                if ($validator->fails()) {
                    $failedRules = $validator->failed();
                    if (isset($failedRules['email']['Unique'])) {
                        return response()->json([
                            "status" => 0,
                            "message" => __('provider_messages.332'),
                            "message_code" => 332,
                        ]);
                    }
                    return response()->json([
                        "status" => 0,
                        "message" => __('provider_messages.305'),
                        "message_code" => 305,
                    ]);
                }
                $check_contact = Provider::query()->where('contact_number',$request->get('contact_number'))
                    ->where('country_code',$request->get('select_country_code'))
                    ->whereNotIn('id',[$request->get('provider_id')])
                    ->whereNull('providers.deleted_at')
                    ->count();
                if($check_contact > 0){
                    return response()->json([
                        "status" => 0,
//                        "message" => "Contact Number already been taken!",
                        "message" =>  __('provider_messages.12'),
                        "message_code" => 12,
                    ]);
                }

                $provider_details->first_name =ucwords(strtolower(trim($request->get('full_name'))));
                $provider_details->last_name = ucwords(strtolower(trim($request->get('last_name'))));
                $provider_details->email = $request->get('email');
                $provider_details->gender = $request->get('gender');
//                $provider_details->service_radius = $request->get('service_radius');
                $provider_details->contact_number = $request->get('contact_number');
                $provider_details->country_code = $request->get('select_country_code');
                //$provider_details->contact_number = $request->get('contact_number');
                if ($request->file('profile_image') != Null) {
                    if (\File::exists(public_path('/assets/images/profile-images/provider/' . $provider_details->avatar))) {
                        \File::delete(public_path('/assets/images/profile-images/provider/' . $provider_details->avatar));
                    }
                    $file = $request->file('profile_image');
                    $file_new = rand(1, 9) . date('sihYdm') . rand(1, 9) . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path() . '/assets/images/profile-images/provider/', $file_new);
                    $provider_details->avatar = $file_new;
                }
                $provider_details->save();

                $currency = WorldCurrency::query()->where('symbol', $provider_details->currency)->first();
                if ($currency == Null) {
                    $currency = WorldCurrency::query()->where('default_currency', 1)->first();
                }

                $provider_other_details = OtherServiceProviderDetails::query()->where('provider_id', $provider_details->id)->first();
                if ($provider_other_details == Null) {
                    $provider_other_details = new OtherServiceProviderDetails();
                    $provider_other_details->provider_id = $provider_details->id;
                }
                $provider_other_details->address = $request->get('address');
                $provider_other_details->landmark = $request->get('landmark');
                $provider_other_details->lat = $request->get('lat');
                $provider_other_details->long = $request->get('long');
                $provider_other_details->min_order = ($request->get('min_order') / ((isset($currency->ratio) && ($currency->ratio <> null)) ? $currency->ratio : 1));
//                $provider_other_details->min_order = $request->get('min_order');
                $provider_other_details->save();

//                $general_settings = request()->get("general_settings");
//                if ($general_settings !=  Null) {
//                    if ($general_settings->send_mail == 1) {
//                        try {
//                            if ($general_settings != Null && $general_settings->send_receive_email != Null) {
//                                $get_provider_service_list = ProviderServices::query()->select('service_category.name')
//                                    ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
//                                    ->where('provider_services.provider_id', $provider_details->id)
//                                    ->whereIN('provider_services.service_cat_id', $this->on_demand_service_id_array)->get()
//                                    ->pluck('name')->toArray();
//                                $get_provider_service_count = count($get_provider_service_list);
//                                if ($get_provider_service_count > 0) {
//                                    $mail_type = "admin_new_provider_signup";
//                                    $to_mail = $general_settings->send_receive_email;
//                                    $provider_service_list = implode(" , ", $get_provider_service_list);
//                                    $provider_email = $provider_details->email;
//                                    $provider_contact_number = $provider_details->contact_number;
//                                    $provider_name = ucwords($provider_details->first_name);
//                                    $subject = "New Provider Registered";
//                                    $disp_data = array("##provider_name##" => $provider_name, "##services_name##" => $provider_service_list, "##email##" => $provider_email, "##contact_no##" => $provider_contact_number);
//                                    $mail_return_data = $this->notificationClass->sendMail($subject, $to_mail, $mail_type, $disp_data);
//                                }
//                            }
//                        } catch (\Exception $e) {}
//                    }
//                }
            }

            return $this->onDemandClassApi->onDemandLoginRegisterResponse($provider_details);
        } else {
            return response()->json([
                "status" => 0,
//                "message" => "provider details not found!",
                "message" =>  __('provider_messages.73'),
                "message_code" => 73,
            ]);
        }
    }

    public function postProviderUpdateCountryAndCurrency(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "provider_id" => "required|numeric",
            "access_token" => "required",
            'select_language' => "required",
            'select_country_code' => "required",
            'select_currency' => "required"
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }
        $provider_details = $this->onDemandClassApi->providerRegisterAllow($request->get('provider_id'), $request->get('access_token'));
        if ($decoded['status'] = json_decode($provider_details) == false) {
            return $provider_details;
        }
        $provider_details = Provider::query()->where('id', $request->get('provider_id'))->where('provider_type', '=',3)->whereNull('providers.deleted_at')->first();
        if ($provider_details == Null) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }

        //$provider_details->country_code = $request->get('select_country_code');
        $provider_details->currency = $request->get('select_currency');
        $provider_details->language = $request->get('select_language');
        $provider_details->save();

        $provider_services_list= '';
        $lang_prefix = $this->adminClass->get_langugae_fields($provider_details->language);
        $provider_services = ProviderServices::query()->select('provider_services.id',
            'provider_services.service_cat_id', 'service_category.'.$lang_prefix.'name as service_cat_name', 'provider_services.status', 'provider_services.current_status')
            ->join('service_category', 'service_category.id', '=', 'provider_services.service_cat_id')
            ->where('provider_services.provider_id', $provider_details->id)
            ->whereIN('service_category.category_type', [3, 4])
            ->get();
        if ($provider_services != Null) {
            $provider_services_list = $provider_services->pluck('service_cat_name')->toArray();
            $provider_services_list = implode(', ', $provider_services_list);
        }

        return response()->json([
            'status' => 1,
//            'message' => "success!",
            'message' => __('provider_messages.1'),
            'provider_services_list' => $provider_services_list,
            "message_code" => 1,
        ]);
    }

    //verification link
    public function postCustomerResendVerificationLink(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "user_id" => "required|numeric",
            "access_token" => "required"
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }

        $user_details = $this->userClassApi->checkUserAllow($request->get('user_id'), $request->get('access_token'));
        if ($decoded['status'] = json_decode($user_details) == false) {
            return $user_details;
        }

        return response()->json([
            "status" => 1,
//            "message" => "success",
            'message' => __('user_messages.1'),
            "message_code" => 1,
        ]);
    }

    public function postDriverResendVerificationLink(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "driver_id" => "required|numeric",
            "access_token" => "required",
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }
        return response()->json([
            "status" => 1,
//            "message" => "success",
            "message" => __('driver_messages.1'),
            "message_code" => 1,
        ]);
    }

    public function postStoreResendVerificationLink(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "store_id" => "required|numeric",
            "access_token" => "required",
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }
        return response()->json([
            "status" => 1,
//            "message" => "success",
            "message" => __('store_messages.1'),
            "message_code" => 1,
        ]);
    }

    //user
    public function postCustomerResendOtpVerification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "user_id" => "required|numeric",
            "access_token" => "required"
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }
        $user = User::query()->where('id', $request->get('user_id'))->whereNull('users.deleted_at')->first();
        if ($user != Null) {
            if ($user->verified_at == Null) {
                $this->tokenClassApi->sendUserSmsVerification($user->id);
            }
        }
        return response()->json([
            "status" => 1,
//            "message" => "success",
            'message' => __('user_messages.1'),
            "message_code" => 1,
        ]);
    }

    public function postCustomerContactVerification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "user_id" => "required|numeric",
            "access_token" => "required|numeric",
            "otp" => "required|numeric|digits:4"
        ]);
        if ($validator->fails()) {
            $failedRules = $validator->failed();
            if (isset($failedRules['otp']['Digits'])) {
                return response()->json([
                    "status" => 0,
//                    "message" => "Invalid Otp!",
                    'message' => __('user_messages.89'),
                    "message_code" => 89,
                ]);
            }
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }

        $user_details = User::query()->where('id', $request->get('user_id'))->whereNull('users.deleted_at')->first();
        if ($user_details != Null) {
            if ($user_details->access_token != $request->get('access_token')) {
                return response()->json([
                    'status' => 4,
//                    'message' => "Access Token Not Match!",
                    'message' => __('user_messages.4'),
                    "message_code" => 4,
                ]);
            }
            $settings = \request()->get('general_settings');
            if ($settings == null){
                return response()->json([
                    "status" => 0,
                    "message" => "something went to wrong!",
                    "message_code" => 9,
                ]);
            }

            if ($settings->is_otp_verification == 1)
            {
                if ($settings->vonage_api_key == null || $settings->vonage_api_secret_key == null) {
                    return response()->json([
                        "status" => 0,
                        "message" => "something went to wrong!",
                        "message_code" => 9,
                    ]);
                }

                $basic  = new \Vonage\Client\Credentials\Basic(key: $settings->vonage_api_key,secret: $settings->vonage_api_secret_key);
                $client = new \Vonage\Client(new \Vonage\Client\Credentials\Container($basic));

                $get_request_id = UserVerification::query()->where('user_id',$user_details->id)->latest()->first();
                if ($get_request_id == null){
                    return response()->json([
                        "status" => 0,
//                    "message" => "User verified successfully!",
                        'message' => "Request Id not found",
                        "message_code" => 0,
                    ]);
                }
                try {
                    $result = $client->verify()->check($get_request_id->token,$request->get('otp'));

                    $vonage_response = collect($result->getResponseData())->toArray();
                    \Log::info($vonage_response);

                    if (($vonage_response['request_id'] == $get_request_id->token) && (isset($vonage_response['event_id']))){
                        if (isset($vonage_response['status']) && $vonage_response['status'] == 0){
                            $user_details->verified_at = date('Y-m-d H:i:s');
                            $user_details->save();
                            return response()->json([
                                "status" => 1,
//                    "message" => "User verified successfully!",
                                'message' => __('user_messages.1'),
                                "message_code" => 1,
                            ]);
                        }
                    }
                } catch (\Exception $e){
                    \Log::info($e);
                    if ($e->getCode() == 17 || $e->getCode() == 6){
                        return response()->json([
                            "status" => 0,
//                    "message" => "Invalid Otp!",
                            'message' => __('user_messages.334'),
                            "message_code" => 334,
                        ]);
                    }
                    return response()->json([
                        "status" => 0,
//                    "message" => "Invalid Otp!",
                        'message' => __('user_messages.89'),
//                        'message' => $e->getMessage(),
                        "message_code" => 89,
                    ]);
                }

                return response()->json([
                    "status" => 0,
//                    "message" => "Invalid Otp!",
                    'message' => __('user_messages.89'),
                    "message_code" => 89,
                ]);
                // wrong otp
//                {
//                    "request_id": "a8c889aef1114875a5f61e072d1581ae",
//                    "status": "16",
//                    "error_text": "The code provided does not match the expected value"
//                }

                // right otp
//                {
//                    "request_id": "a8c889aef1114875a5f61e072d1581ae",
//                    "status": "0",
//                    "event_id": "2ef4eb85-faeb-46f2-b48f-31ccead7f772",
//                    "price": "0.05000000",
//                    "currency": "EUR",
//                    "estimated_price_messages_sent": "0.00869"
//                }
            }
            if ($request->get('otp') == "1234") {
                $user_details->verified_at = date('Y-m-d H:i:s');
                $user_details->save();
                return response()->json([
                    "status" => 1,
//                    "message" => "User verified successfully!",
                    'message' => __('user_messages.1'),
                    "message_code" => 1,
                ]);
            } else {
                return response()->json([
                    "status" => 0,
//                    "message" => "Invalid Otp!",
                    'message' => __('user_messages.89'),
                    "message_code" => 89,
                ]);
            }
        } else {
            return response()->json([
                "status" => 5,
//                "message" => "user not found!",
                'message' => __('user_messages.5'),
                "message_code" => 5,
            ]);
        }
    }

    //driver
    public function postDriverResendOtpVerification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "driver_id" => "required|numeric",
            "access_token" => "required",
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }
        $provider = Provider::query()->where('id', $request->get('driver_id'))->where('provider_type', '=',2)->whereNull('providers.deleted_at')->first();
        if ($provider != Null) {
            if ($provider->verified_at == Null) {
                $this->tokenClassApi->sendProviderSmsVerification($provider->id);
            }
        }
        return response()->json([
            "status" => 1,
//            "message" => "success",
            "message" => __('driver_messages.1'),
            "message_code" => 1,
        ]);
    }

    public function postDriverContactVerification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "driver_id" => "required|numeric",
            "access_token" => "required|numeric",
            "otp" => "required|numeric|digits:4"
        ]);
        if ($validator->fails()) {
            $failedRules = $validator->failed();
            if (isset($failedRules['otp']['Digits'])) {
                return response()->json([
                    "status" => 0,
//                    "message" => "Invalid Otp!",
                    "message" => __('driver_messages.89'),
                    "message_code" => 89,
                ]);
            }
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }
        $driver_details = Provider::query()->where('id', $request->get('driver_id'))->where('provider_type', '=',2)->whereNull('providers.deleted_at')->first();
        if ($driver_details != Null) {
            if ($request->get('otp') == "1234") {
                $driver_details->verified_at = date('Y-m-d H:i:s');
                $driver_details->save();
                return response()->json([
                    "status" => 1,
//                    "message" => "User verified successfully!",
                    "message" => __('driver_messages.1'),
                    "message_code" => 1,
                ]);
            } else {
                return response()->json([
                    "status" => 0,
//                    "message" => "Invalid Otp!",
                    "message" => __('driver_messages.89'),
                    "message_code" => 89,
                ]);
            }
            /*$get_token = ProviderVerification::query()->where('provider_id', $driver_details->id)->first();
            if ($get_token == Null) {
                return response()->json([
                    "status" => 0,
//                    "message" => "something went to wrong!",
                    "message" => __('driver_messages.9'),
                    "message_code" => 9,
                ]);
            }
            $settings = GeneralSettings::first();
            if ($settings == Null || $settings->twilio_service_key == Null || $settings->twilio_auth_token == Null || $settings->twilio_verify_service_key == Null) {
                return response()->json([
                    "status" => 0,
//                    "message" => "something went to wrong!",
                    "message" => __('driver_messages.9'),
                    "message_code" => 9,
                ]);
            }
            try {
                $twilio = new Client($settings->twilio_service_key, $settings->twilio_auth_token);
                $option = [
                    'To' => $driver_details->contact_number,
                    'VerificationSid' => $get_token->token,
                ];
                $verification_check = $twilio->verify->v2->services($settings->twilio_verify_service_key)
                    ->verificationChecks
                    ->create($request->get('otp'), $option);
                if ($verification_check->status == "approved") {
                    $verification_sid = $verification_check->sid;

                    if ($verification_sid != $get_token->token) {
                        return response()->json([
                            "status" => 0,
//                            "message" => "something went to wrong!",
                            "message" => __('driver_messages.9'),
                            "message_code" => 9,
                        ]);
                    }
                    ProviderVerification::where('provider_id', $driver_details->id)->delete();

                    $driver_details->verified_at = date('Y-m-d H:i:s');
                    $driver_details->save();
                    return response()->json([
                        "status" => 1,
//                        "message" => "Driver verified successfully!",
                        "message" => __('driver_messages.1'),
                        "message_code" => 1,
                    ]);
                } else {
                    return response()->json([
                        "status" => 0,
//                        "message" => "Entered OTP is Incorrect!",
                        "message" => __('driver_messages.89'),
                        "message_code" => 89,
                    ]);
                }
            } catch (\Exception $e) {
                return response()->json([
                    "status" => 0,
//                    "message" => "something went to wrong!",
                    "message" => __('driver_messages.9'),
                    "message_code" => 9,
                ]);
            }*/
        } else {
            return response()->json([
                "status" => 5,
//                "message" => "driver not found!",
                "message" => __('driver_messages.5'),
                "message_code" => 5,
            ]);
        }
    }

    //store
    public function postStoreResendOtpVerification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "store_id" => "required|numeric",
            "access_token" => "required",
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }
        $provider = Provider::query()->where('id', $request->get('store_id'))->where('provider_type', '=',1)->whereNull('providers.deleted_at')->first();
        if ($provider != Null) {
            if ($provider->verified_at == Null) {
                $this->tokenClassApi->sendProviderSmsVerification($provider->id);
            }
        }
        return response()->json([
            "status" => 1,
//            "message" => "success",
            "message" => __('store_messages.1'),
            "message_code" => 1,
        ]);
    }

    public function postStoreContactVerification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "store_id" => "required|numeric",
            "access_token" => "required|numeric",
            "otp" => "required|numeric|digits:4"
        ]);
        if ($validator->fails()) {
            $failedRules = $validator->failed();
            if (isset($failedRules['otp']['Digits'])) {
                return response()->json([
                    "status" => 0,
//                    "message" => "Invalid Otp!",
                    "message" => __('store_messages.89'),
                    "message_code" => 89,
                ]);
            }
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }
        $store_details = Provider::query()->where('id', $request->get('store_id'))->where('provider_type', '=',1)->whereNull('providers.deleted_at')->first();
        if ($store_details != Null) {
            if ($request->get('otp') == "1234") {
                $store_details->verified_at = date('Y-m-d H:i:s');
                $store_details->save();
                $message = "<div style='padding: 0px 50px;' xmlns='http://www.w3.org/1999/html'>
                                <pre style='font-family: &quot;Courier New&quot;; font-size: 9pt;'>
                                    Thank you for signing up!
                                <br />
                                Please complete your registration by going to <a href='".route("get:store-admin:login")."'>".route("get:store-admin:login")."</a>
                                <br />
                                Enter Service Type, Store Details, Upload Required Documents and add your Product Lists.  Upon completion of entries, your application will be reviewed for approval.
                                <br />
                                Any questions, email us at  <a href='mailto:sales@whitelabelfox.com'>sales@whitelabelfox.com</a>
                                </span>
                                </pre>
                            </div>";
                return response()->json([
                    "status" => 1,
                    //"message" => "Your account not sent for approval request unless you add the store details from store web panel. Please add your all store details and sent your account request to approval!",
                    "message" => $message,
                    "message_code" => 1,
                ]);
            } else {
                return response()->json([
                    "status" => 0,
//                    "message" => "Invalid otp!",
                    "message" => __('store_messages.89'),
                    "message_code" => 89,
                ]);
            }
//            $get_token = ProviderVerification::query()->where('provider_id', $store_details->id)->first();
//            if ($get_token == Null) {
//                return response()->json([
//                    "status" => 0,
//                    "message" => "something went to wrong!",
//                    "message_code" => 9,
//                ]);
//            }
//            $settings = GeneralSettings::first();
//            if ($settings == Null || $settings->twilio_service_key == Null || $settings->twilio_auth_token == Null || $settings->twilio_verify_service_key == Null) {
//                return response()->json([
//                    "status" => 0,
//                    "message" => "something went to wrong!",
//                    "message_code" => 9,
//                ]);
//            }
//            try {
//                $twilio = new Client($settings->twilio_service_key, $settings->twilio_auth_token);
//                $option = [
//                    'To' => $store_details->contact_number,
//                    'VerificationSid' => $get_token->token,
//                ];
//                $verification_check = $twilio->verify->v2->services($settings->twilio_verify_service_key)
//                    ->verificationChecks
//                    ->create($request->get('otp'), $option);
//                if ($verification_check->status == "approved") {
//                    $verification_sid = $verification_check->sid;
//
//                    if ($verification_sid != $get_token->token) {
//                        return response()->json([
//                            "status" => 0,
//                            "message" => "something went to wrong!",
//                            "message_code" => 9,
//                        ]);
//                    }
//                    ProviderVerification::where('provider_id', $store_details->id)->delete();
//
//                    $store_details->verified_at = date('Y-m-d H:i:s');
//                    $store_details->save();
//                    $message = "<div style='padding: 0px 50px;' xmlns='http://www.w3.org/1999/html'>
//                                <pre style='font-family: &quot;Courier New&quot;; font-size: 9pt;'>
//                                    Thank you for signing up!
//                                <br />
//                                Please complete your registration by going to <a href='https://my-checkout.com/store-admin/login'>https://my-checkout.com/store-admin/login</a>
//                                <br />
//                                Enter Service Type, Store Details, Upload Required Documents and add your Product Lists.  Upon completion of entries, your application will be reviewed for approval.
//                                <br />
//                                Any questions, email us at  <a href='mailto:help@foxjek.com'>help@foxjek.com</a>
//                                </span>
//                                </pre>
//                            </div>";
//                    return response()->json([
//                        "status" => 1,
////                    "message" => "Your account not sent for approval request unless you add the store details from store web panel. Please add your all store details and sent your account request to approval!",
//                        "message" => $message,
//                        "message_code" => 1,
//                    ]);
//                } else {
//                    return response()->json([
//                        "status" => 0,
//                        "message" => "Entered OTP is Incorrect!",
//                        "message_code" => 89,
//                    ]);
//                }
//            } catch (\Exception $e) {
//                return response()->json([
//                    "status" => 0,
//                    "message" => "something went to wrong!",
//                    "message_code" => 9,
//                ]);
//            }
        } else {
            return response()->json([
                "status" => 5,
//                "message" => "store not found!",
                "message" => __('store_messages.5'),
                "message_code" => 5,
            ]);
        }
    }

    //on-demand provider
    public function postOnDemandResendOtpVerification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "provider_id" => "required|numeric",
            "access_token" => "required",
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }
        $provider = Provider::query()->where('id', $request->get('provider_id'))->where('provider_type', '=',3)->whereNull('providers.deleted_at')->first();
        if ($provider != Null) {
            if ($provider->verified_at == Null) {
                $this->tokenClassApi->sendProviderSmsVerification($provider->id);
            }
        }
        return response()->json([
            "status" => 1,
//            "message" => "success",
            "message" => __('provider_messages.1'),
            "message_code" => 1,
        ]);
    }

    public function postOnDemandContactVerification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "provider_id" => "required|numeric",
            "access_token" => "required|numeric",
            "otp" => "required|numeric|digits:4"
        ]);
        if ($validator->fails()) {
            $failedRules = $validator->failed();
            if (isset($failedRules['otp']['Digits'])) {
                return response()->json([
                    "status" => 0,
//                    "message" => "Invalid Otp!",
                    "message" => __('provider_messages.89'),
                    "message_code" => 89,
                ]);
            }
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }
        $provider_details = Provider::query()->where('id', $request->get('provider_id'))->where('provider_type', '=',3)->whereNull('providers.deleted_at')->first();
        if ($provider_details != Null) {
            $settings = \request()->get('general_settings');
            if ($settings == null){
                return response()->json([
                    "status" => 0,
                    "message" => "something went to wrong!",
                    "message_code" => 9,
                ]);
            }

            if ($settings->is_otp_verification == 1)
            {
                if ($settings->vonage_api_key == null || $settings->vonage_api_secret_key == null) {
                    return response()->json([
                        "status" => 0,
                        "message" => "something went to wrong!",
                        "message_code" => 9,
                    ]);
                }

                $basic  = new \Vonage\Client\Credentials\Basic(key: $settings->vonage_api_key,secret: $settings->vonage_api_secret_key);
                $client = new \Vonage\Client(new \Vonage\Client\Credentials\Container($basic));

                $get_request_id = ProviderVerification::query()->where('provider_id',$provider_details->id)->latest()->first();
                if ($get_request_id == null){
                    return response()->json([
                        "status" => 0,
//                    "message" => "User verified successfully!",
                        'message' => "Request Id not found",
                        "message_code" => 0,
                    ]);
                }
                try {
                    $result = $client->verify()->check($get_request_id->token,$request->get('otp'));

                    $vonage_response = collect($result->getResponseData())->toArray();

                    if (($vonage_response['request_id'] == $get_request_id->token) && (isset($vonage_response['event_id']))){
                        if (isset($vonage_response['status']) && $vonage_response['status'] == 0){
                            $provider_details->verified_at = date('Y-m-d H:i:s');
                            $provider_details->save();
                            return response()->json([
                                "status" => 1,
//                    "message" => "User verified successfully!",
                                'message' => __('provider_messages.1'),
                                "message_code" => 1,
                            ]);
                        }
                    }
                } catch (\Exception $e){
                    if ($e->getCode() == 17 || $e->getCode() == 6){
                        return response()->json([
                            "status" => 0,
//                    "message" => "Invalid Otp!",
                            'message' => __('provider_messages.334'),
                            "message_code" => 334,
                        ]);
                    }
                    return response()->json([
                        "status" => 0,
//                    "message" => "Invalid Otp!",
                        "message" => __('provider_messages.89'),
                        "message_code" => 89,
                    ]);
                }

                return response()->json([
                    "status" => 0,
//                    "message" => "Invalid Otp!",
                    "message" => __('provider_messages.89'),
                    "message_code" => 89,
                ]);
                // wrong otp
//                {
//                    "request_id": "a8c889aef1114875a5f61e072d1581ae",
//                    "status": "16",
//                    "error_text": "The code provided does not match the expected value"
//                }

                // right otp
//                {
//                    "request_id": "a8c889aef1114875a5f61e072d1581ae",
//                    "status": "0",
//                    "event_id": "2ef4eb85-faeb-46f2-b48f-31ccead7f772",
//                    "price": "0.05000000",
//                    "currency": "EUR",
//                    "estimated_price_messages_sent": "0.00869"
//                }
            }

            if ($request->get('otp') == "1234") {
                $provider_details->verified_at = date('Y-m-d H:i:s');
                $provider_details->save();
                return response()->json([
                    "status" => 1,
//                    "message" => "Provider verified successfully!",
                    "message" => __('provider_messages.1'),
                    "message_code" => 1,
                ]);
            } else {
                return response()->json([
                    "status" => 0,
//                    "message" => "Invalid otp",
                    "message" => __('provider_messages.89'),
                    "message_code" => 89,
                ]);
            }
        } else {
            return response()->json([
                "status" => 5,
//                "message" => "Provider not found!",
                "message" => __('provider_messages.5'),
                "message_code" => 5,
            ]);
        }
    }

    public function postCustomerChangeContactNumber(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "user_id" => "required|numeric",
            "access_token" => "required",
            "contact_number" => "required|numeric",
            "select_country_code" => "required"
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }

        $check_contact = User::query()->where('contact_number',$request->get('contact_number'))
            ->where('country_code',$request->get('select_country_code'))
            ->where('id', '!=',$request->get('user_id'))
            ->whereNull('users.deleted_at')
            ->count();
        if($check_contact > 0){
            return response()->json([
                "status" => 0,
//                "message" => "Contact Number already been taken!",
                'message' => __('user_messages.12'),
                "message_code" => 12,
            ]);
        }

        $user_details = User::query()->where('id', $request->get('user_id'))->whereNull('users.deleted_at')->first();
        if ($user_details != Null) {
            if ($user_details->access_token != $request->get('access_token')) {
                return response()->json([
                    'status' => 4,
//                    'message' => "Access Token Not Match!",
                    'message' => __('user_messages.4'),
                    "message_code" => 4,
                ]);
            }
            if ($user_details->status == 0) {
                return response()->json([
                    'status' => 3,
//                    'message' => 'Your account is currently blocked, so not authorised to allow any activity!',
                    'message' => __('user_messages.3'),
                    "message_code" => 3,
                ]);
            }
            $user_details->contact_number = $request->get('contact_number');
            $user_details->country_code = $request->get('select_country_code');
            $user_details->save();
            $this->tokenClassApi->sendUserSmsVerification($user_details->id);
            return response()->json([
                'status' => 1,
//                'message' => 'Success!',
                'message' => __('user_messages.1'),
                "message_code" => 1,
                'contact_number' => $user_details->contact_number,
                "select_country_code" => $user_details->country_code
            ]);
        } else {
            return response()->json([
                'status' => 5,
//                'message' => "User not found!",
                'message' => __('user_messages.5'),
                "message_code" => 5,
            ]);
        }
    }

    public function postProviderChangeContactNumber(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "provider_id" => "required|numeric",
            "access_token" => "required",
            "contact_number" => "required|numeric",
            "select_country_code" => "required"
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }

        $check_contact = Provider::query()
            ->where('contact_number',$request->get('contact_number'))
            ->where('country_code',$request->get('select_country_code'))
            ->where('id', '!=',$request->get('provider_id'))
            ->whereNull('providers.deleted_at')
            ->count();

        if($check_contact > 0){
            return response()->json([
                "status" => 0,
//                "message" => "Contact Number already been taken!",
                "message" => __('provider_messages.12'),
                "message_code" => 12,
            ]);
        }

        $provider_details = Provider::query()->where('id', $request->get('provider_id'))->whereNull('providers.deleted_at')->first();
        if ($provider_details != Null) {
            if ($provider_details->access_token != $request->get('access_token')) {
                return response()->json([
                    'status' => 4,
//                    'message' => "Access Token Not Match!",
                    'message' => __('driver_messages.4'),
                    "message_code" => 4,
                ]);
            }
            if ($provider_details->status == 2) {
                return response()->json([
                    'status' => 3,
//                    'message' => 'Your account is currently blocked, so not authorised to allow any activity!',
                    'message' => __('driver_messages.3'),
                    "message_code" => 3,
                ]);
            }
            $provider_details->contact_number = $request->get('contact_number');
            $provider_details->country_code = $request->get('select_country_code');
            $provider_details->save();
            $this->tokenClassApi->sendProviderSmsVerification($provider_details->id);
            return response()->json([
                'status' => 1,
//                'message' => 'Success!',
                'message' => __('driver_messages.1'),
                "message_code" => 1,
                'contact_number' => $provider_details->contact_number,
                "select_country_code" => $provider_details->country_code
            ]);
        } else {
            return response()->json([
                'status' => 5,
//                'message' => "User not found!",
                'message' => __('driver_messages.5'),
                "message_code" => 5,
            ]);
        }
    }
}
