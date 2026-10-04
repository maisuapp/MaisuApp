<?php

namespace App\Http\Controllers\Api\Auth;

use App\Classes\DriverClassApi;
use App\Classes\NotificationClass;
use App\Classes\OnDemandClassApi;
use App\Classes\StoreClassApi;
use App\Classes\TokenClassApi;
use App\Classes\UserClassApi;
use App\model\Provider;
use App\model\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
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
    private $driverClassApi;
    private $userClassApi;
    private $storeClassApi;
    Private $onDemandClassApi;
    Private $tokenClassApi;
    private $notificationClass;

    public function __construct(TokenClassApi $tokenClassApi, UserClassApi $userClassApi, DriverClassApi $driverClassApi, StoreClassApi $storeClassApi, OnDemandClassApi $onDemandClassApi, NotificationClass $notificationClass)
    {
        $this->driverClassApi = $driverClassApi;
        $this->userClassApi = $userClassApi;
        $this->storeClassApi = $storeClassApi;
        $this->onDemandClassApi = $onDemandClassApi;
        $this->tokenClassApi = $tokenClassApi;
        $this->notificationClass = $notificationClass;
    }

    //customer login api
    public function postCustomerLogin(Request $request)
    {
        $validator = Validator::make($request->header(), [
            'select-time-zone' => 'required',
            'select-ip-address' => 'nullable',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()->first(),
                'message_code' => 9,
            ]);
        }

        $validator = Validator::make($request->all(), [
            'login_type' => 'required|in:facebook,google,email,apple',
            'device_token' => 'required',
            'select_language' => "required",
            'select_country_code' => "nullable",
            'select_currency' => "required",
            'login_device' => "nullable|in:1,2,3,4"
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }
        $login_type = $request->get('login_type');
        if ($login_type != "facebook" && $login_type != "google" && $login_type != "apple") {
            $validator = Validator::make($request->all(), [
                'email' => 'required',
                'password' => 'required',
                'select_country_code' => "required"
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => 0,
                    'message' => $validator->errors()->first(),
                    "message_code" => 9,
                ]);
            }
            if (is_numeric($request->get('email'))) {
                $user_details = User::query()->where('contact_number', $request->get('email'))->where('country_code', $request->get('select_country_code'))->whereNull('users.deleted_at')->first();
            } else {
                $user_details = User::query()->where('email', $request->get('email'))->whereNull('users.deleted_at')->first();
            }
            if ($user_details != Null) {
                if ($user_details->status == 0) {
                    return response()->json([
                        'status' => 3,
//                        'message' => 'Your account is currently blocked, so not authorised to allow any activity!',
                        'message' => __('user_messages.3'),
                        "message_code" => 3,
                    ]);
                }
                if (Hash::check($request->get('password'), $user_details->password)) {
                    $user_details->country_code = $request->get('select_country_code');
                    $user_details->currency = $request->get('select_currency');
                    $user_details->language = $request->get('select_language');
                    $user_details->device_token = $request->get('device_token');
                    $user_details->login_device = $request->get('login_device') != Null ? $request->get('login_device') : 0;
                    $user_details->time_zone = $request->header('select-time-zone')!= Null ? $request->header('select-time-zone') : Null;
                    $user_details->login_device = $request->get('login_device') != Null ? $request->get('login_device') : 0;
                    $user_details->save();
                    $user_details->generateAccessToken($user_details->id);
                    if ($user_details->avatar != Null) {
                        if (filter_var($user_details->avatar, FILTER_VALIDATE_URL) == true) {
                            $avatar = $user_details->avatar;
                        } else {
                            $avatar = url('/assets/images/profile-images/customer/' . $user_details->avatar);
                        }
                    } else {
                        $avatar = "";
                    }
                    return $this->userClassApi->userLoginRegisterUpdateDetails($user_details);
                } else {
                    return response()->json([
                        'status' => 0,
//                        'message' => 'You have entered an incorrect password',
                        'message' => __('user_messages.10'),
                        "message_code" => 10,
                    ]);
                }
            } else {
                return response()->json([
                    'status' => 5,
//                    'message' => 'User Not Found!',
                    'message' => __('user_messages.5'),
                    "message_code" => 5,
                ]);
            }
        }
        else {
            $validator = Validator::make($request->all(), [
                'full_name' => 'required',
                'email' => 'nullable|email',
                'login_id' => 'required',
                'profile_image' => 'nullable',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => 0,
                    'message' => $validator->errors()->first(),
                    "message_code" => 9,
                ]);
            }
            $check_email = null;

            $user_details = User::query()->where('login_type', $request->get('login_type'))->where('login_id', $request->get('login_id'))->whereNull('users.deleted_at')->first();
            if ($user_details == Null) {
                if ($request->get('email') <> null) {
                    $check_email = User::query()->select('id')->where('email', $request->get('email'))->whereNull('users.deleted_at')->first();
                }
                if ($check_email == Null) {
                    $user_details = new User();
                    if (filter_var($request->get('profile_image'), FILTER_VALIDATE_URL) == true) {
                        $user_details->avatar = $request->get('profile_image');
                    }
                    if(trim($request->get("full_name")) != "N/A")
                    {
                        $user_details->first_name = ucwords(strtolower(trim($request->get('full_name'))));
                    }

                    /*$name = array_map('trim', explode(" ", $request->get('name')));
                    if (count($name) > 1) {
                        $user_details->first_name = ucwords(strtolower(trim($name[0])));
//                        $user_details->last_name = ucwords(strtolower(trim($name[1])));
                    } else {
                        $user_details->first_name = ucwords(strtolower(trim($name[0])));
                    }*/
                    $user_details->verified_at = date('Y-m-d H:i:s');
                    if ($request->get('login_device') != Null && $request->get('login_device') == 2) {
                        if ($login_type == "apple") {
                            $user_details->contact_number = "+" . random_int(1, 99) . date('siHd') . random_int(1, 9);
//                            $user_details->verified_at = date('Y-m-d H:i:s');
//                            $user_details->email = "demo" . random_int(1, 9999) . "@gmail.com";
                        }
                    }
                    if ($request->get('email') != Null) {
                        $user_details->email = $request->get('email');
                    }
                    $user_details->login_type = $request->get('login_type');
                    $user_details->login_id = $request->get('login_id');
                    $user_details->country_code = $request->get('select_country_code');
                    $user_details->currency = $request->get('select_currency');
                    $user_details->language = $request->get('select_language');
                    $user_details->device_token = $request->get('device_token');
                    $user_details->login_device = $request->get('login_device') != Null ? $request->get('login_device') : 0;
                    $user_details->time_zone = $request->header('select-time-zone')!= Null ? $request->header('select-time-zone') : Null;
                    $user_details->login_device = $request->get('login_device') != Null ? $request->get('login_device') : 0;
                    $user_details->save();
                    $user_details->InviteCode($user_details->id, $user_details->first_name);
                }
                else {
                    return response()->json([
                        'status' => 0,
//                        'message' => 'Email Already Exist!',
                        'message' => __('user_messages.11'),
                        "message_code" => 11,
                    ]);
                }
            }
            else {
                if ($user_details->status == 0) {
                    return response()->json([
                        'status' => 3,
//                        'message' => 'Your account is currently blocked, so not authorised to allow any activity!',
                        'message' => __('user_messages.3'),
                        "message_code" => 3,
                    ]);
                }
                //$user_details->country_code = $request->get('select_country_code');
                $user_details->currency = $request->get('select_currency');
                $user_details->language = $request->get('select_language');
                $user_details->device_token = $request->get('device_token');
                $user_details->login_device = $request->get('login_device') != Null ? $request->get('login_device') : 0;
                $user_details->time_zone = $request->header('select-time-zone')!= Null ? $request->header('select-time-zone') : Null;
                $user_details->login_device = $request->get('login_device') != Null ? $request->get('login_device') : 0;
                $user_details->save();
            }
            $user_details->generateAccessToken($user_details->id);
            if ($user_details->avatar != Null) {
                if (filter_var($user_details->avatar, FILTER_VALIDATE_URL) == true) {
                    $avatar = $user_details->avatar;
                } else {
                    $avatar = url('/assets/images/profile-images/customer/' . $user_details->avatar);
                }
            } else {
                $avatar = "";
            }
            return $this->userClassApi->userLoginRegisterUpdateDetails($user_details);
        }
    }

    //email verification pending
    //driver login api
    public function postTransportDriverLogin(Request $request)
    {
        $this->notificationClass->ApiLogDetail($logger_type = 2, 0, "postTransportDriverLogin", $request->all());

        $validator = Validator::make($request->all(), [
            'login_type' => 'required|in:facebook,google,email,apple',
            'device_token' => 'required',
            'select_language' => "required",
            'select_country_code' => "nullable",
            'select_currency' => "required",
            'login_device' => "nullable|in:1,2,3,4"
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }
        $login_type = $request->get('login_type');
        if ($login_type != "facebook" && $login_type != "google" && $login_type != "apple") {
            $validator = Validator::make($request->all(), [
                'email' => 'required',
                'password' => 'required',
                'select_country_code' => "required"
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => 0,
                    'message' => $validator->errors()->first(),
                    "message_code" => 9,
                ]);
            }

            if (is_numeric($request->get('email'))) {
                $driver_details = Provider::query()->where('provider_type','=',2)->where('contact_number','=', $request->get('email'))->where('country_code','=', $request->get('select_country_code'))->whereNull('providers.deleted_at')->first();
            } else {
                $driver_details = Provider::query()->where('provider_type','=',2)->where('email','=', $request->get('email'))->whereNull('providers.deleted_at')->first();
            }
            if ($driver_details != Null) {
                if ($driver_details->status == 2) {
                    return response()->json([
                        'status' => 3,
//                        'message' => 'Your account is currently blocked, so not authorised to allow any activity!',
                        'message' => __('driver_messages.3'),
                        "message_code" => 3,
                    ]);
                }
                if (Hash::check($request->get('password'), $driver_details->password)) {
                    //$driver_details->device_token = $request->get('device_token');
                    //$driver_details->last_active = date('Y-m-d H:i:s');
                    //$driver_details->country_code = $request->get('select_country_code');
                    //$driver_details->currency = $request->get('select_currency');
                    //$driver_details->language = $request->get('select_language');
                    //$driver_details->login_device = $request->get('login_device') != Null ? $request->get('login_device') : 1;
                    //$driver_details->save();
                    //$driver_details->generateAccessToken($driver_details->id);
                    return $this->driverClassApi->DriverLoginRegisterResponse($driver_details,$request_type =1 ,$request);
                } else {
                    return response()->json([
                        'status' => 0,
//                        'message' => 'You have entered an incorrect password',
                        'message' => __('driver_messages.10'),
                        "message_code" => 10,
                    ]);
                }
            } else {
                return response()->json([
                    'status' => 5,
//                    'message' => 'Driver Not Found!',
                    'message' => __('driver_messages.5'),
                    "message_code" => 5,
                ]);
            }
        } else {
            $validator = Validator::make($request->all(), [
                'full_name' => 'required',
                //'last_name' => 'required',
                'email' => 'nullable|email',
                'login_id' => 'required',
                'gender' => 'nullable',
                'profile_image' => 'nullable'
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => 0,
                    'message' => $validator->errors()->first(),
                    "message_code" => 9,
                ]);
            }
            $driver_details = Provider::query()->where('provider_type','=',2)->where('login_type','=', $request->get('login_type'))->where('login_id','=', $request->get('login_id'))->whereNull('providers.deleted_at')->first();

            if ($driver_details == Null) {
                if ($request->get('email') != Null) {
                    $check_email = Provider::query()->select('id')->where('email','=', $request->get('email'))->whereNull('providers.deleted_at')->first();

                    if ($check_email != Null) {
                        return response()->json([
                            'status' => 0,
//                            'message' => 'This email address is already used with a social account. Please Signup with other email address.',
                            'message' => __('driver_messages.11'),
                            /*"message_code" => 103,*/
                            "message_code" => 11,
                        ]);
                    }

                }else{
                    //check for apple login
                    $check_email = Provider::query()->select('id')->where('provider_type','!=',2)->where('login_id','=', $request->get('login_id'))->whereNull('providers.deleted_at')->first();
                    if ($check_email != Null) {
                        return response()->json([
                            'status' => 0,
//                            'message' => 'This email address is already used with a social account. Please Signup with other email address.',
                            'message' => __('driver_messages.11'),
                            /*"message_code" => 103,*/
                            "message_code" => 11,
                        ]);
                    }
                }
                $driver_details = new Provider();
                if ($request->get('profile_image') != Null || $request->get('profile_image') != "null") {
                    $driver_details->avatar = $request->get('profile_image');
                }
                //$name = array_map('trim', explode(" ", $request->get('name')));
                //if (count($name) > 1) {
                //    $driver_details->first_name = ucwords(strtolower(trim($name[0])));
                //    $driver_details->last_name = ucwords(strtolower(trim($name[1])));
                //} else {
                //    $driver_details->first_name = ucwords(strtolower(trim($name[0])));
                //}
                if(trim($request->get("full_name")) != "N/A")
                {
                    $driver_details->first_name = ucwords(strtolower(trim($request->get("full_name"))));
                }

                if ($request->get('email') != Null) {
                    $driver_details->email = $request->get('email');
                }
                if ($request->get('gender') != Null) {
                    if ($request->get('gender') == "male") {
                        $driver_details->gender = 1;
                    } elseif ($request->get('gender') == "female") {
                        $driver_details->gender = 2;
                    } else {
                        $driver_details->gender = 0;
                    }
                }

                $driver_details->provider_type = 2;
                $driver_details->verified_at = date('Y-m-d h:i:s');
                $driver_details->login_type = $request->get('login_type');
                $driver_details->login_id = $request->get('login_id');
                $driver_details->device_token = $request->get('device_token');
                $driver_details->status = 1;
                $driver_details->last_active = date('Y-m-d H:i:s');
                $driver_details->device_token = $request->get('device_token');
                $driver_details->status = 3;
                $driver_details->country_code = $request->get('select_country_code');
                $driver_details->currency = $request->get('select_currency');
                $driver_details->language = $request->get('select_language');
                $driver_details->login_device = $request->get('login_device') != Null ? $request->get('login_device') : 0;
                $driver_details->save();
            /*} else {
                return response()->json([
                    'status' => 0,
                    'message' => 'Email Already Exist!',
                    "message_code" => 11,
                ]);
            }*/
            } else {
                if ($driver_details->status == 2) {
                    return response()->json([
                        'status' => 3,
//                        'message' => 'Your account is currently blocked, so not authorised to allow any activity!',
                        'message' => __('driver_messages.3'),
                        "message_code" => 3,
                    ]);
                }
                //$driver_details->device_token = $request->get('device_token');
                //$driver_details->country_code = $request->get('select_country_code');
                //$driver_details->currency = $request->get('select_currency');
                //$driver_details->language = $request->get('select_language');
                //$driver_details->login_device = $request->get('login_device') != Null ? $request->get('login_device') : 1;
                //$driver_details->save();
            }
            return $this->driverClassApi->DriverLoginRegisterResponse($driver_details, $request_type =1 ,$request);
        }
    }

    public function postStoreLogin(Request $request)
    {
        $this->notificationClass->ApiLogDetail($logger_type = 1, 0, "postStoreLogin", $request->all());
        $validator = Validator::make($request->all(), [
            'login_type' => 'required|in:facebook,google,email',
            'device_token' => 'required',
            'select_language' => "required",
            'select_country_code' => "nullable",
            'select_currency' => "required",
            'login_device' => "nullable|in:1,2,3,4"
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }
        $login_type = $request->get('login_type');
        if ($login_type != "facebook" && $login_type != "google") {
            $validator = Validator::make($request->all(), [
                'email' => 'required',
                'password' => 'required',
                'select_country_code' => "required"
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => 0,
                    'message' => $validator->errors()->first(),
                    "message_code" => 9,
                ]);
            }
            if (is_numeric($request->get('email'))) {
                $provider_details = Provider::query()->where('provider_type','=',1)->where('contact_number', $request->get('email'))->where('country_code', $request->get('select_country_code'))->whereNull('providers.deleted_at')->first();
            } else {
                $provider_details = Provider::query()->where('provider_type','=',1)->where('email', $request->get('email'))->whereNull('providers.deleted_at')->first();
            }
            if ($provider_details != Null) {
                if ($provider_details->status == 2) {
                    return response()->json([
                        'status' => 3,
//                        'message' => 'Your account is currently blocked, so not authorised to allow any activity!',
                        'message' => __('store_messages.3'),
                        "message_code" => 3,
                    ]);
                }
                if (Hash::check($request->get('password'), $provider_details->password)) {
                    //$provider_details->last_active = date('Y-m-d H:i:s');
                    //$provider_details->device_token = $request->get('device_token');
                    //$provider_details->country_code = $request->get('select_country_code');
                    //$provider_details->currency = $request->get('select_currency');
                    //$provider_details->language = $request->get('select_language');
                    //$provider_details->login_device = $request->get('login_device') != Null ? $request->get('login_device') : 1;
                    //$provider_details->save();
                    //$provider_details->generateAccessToken($provider_details->id);
                    return $this->storeClassApi->storeLoginRegisterResponse($provider_details, $request_type =1 ,$request);
                } else {
                    return response()->json([
                        'status' => 0,
//                        'message' => 'You have entered an incorrect password',
                        'message' => __('store_messages.10'),
                        "message_code" => 10,
                    ]);
                }
            } else {
                return response()->json([
                    'status' => 5,
//                    'message' => 'User Not Found!',
                    'message' => __('store_messages.5'),
                    "message_code" => 5,
                ]);
            }
        } else {
            $validator = Validator::make($request->all(), [
                'first_name' => 'required',
//                "last_name" => "required",
                'email' => 'nullable|email',
                'login_id' => 'required',
                'profile_image' => 'nullable',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => 0,
                    'message' => $validator->errors()->first(),
                    "message_code" => 9,
                ]);
            }
            $provider_details = Provider::query()->where('provider_type','=',1)->where('login_type', $request->get('login_type'))->where('login_id', $request->get('login_id'))->whereNull('providers.deleted_at')->first();
            if ($provider_details == Null) {
                $check_email = Provider::query()->select('id')->where('login_type','!=', $request->get('login_type'))->where('email', $request->get('email'))->whereNull('providers.deleted_at')->first();
                if ($check_email == Null) {
                    $provider_details = new Provider();
                    $provider_details->first_name = ucwords(strtolower(trim($request->get('first_name'))));
//                    $provider_details->last_name = ucwords(strtolower(trim($request->get('last_name'))));
                    if (filter_var($request->get('profile_image'), FILTER_VALIDATE_URL) == true) {
                        $provider_details->avatar = $request->get('profile_image');
                    }
                    if ($request->get('email') != Null) {
                        $provider_details->email = $request->get('email');
                    }
                    $provider_details->verified_at = date('Y-m-d H:i:s');
                    $provider_details->provider_type = 1;
                    $provider_details->login_type = $request->get('login_type');
                    $provider_details->login_id = $request->get('login_id');
                    $provider_details->device_token = $request->get('device_token');
                    $provider_details->last_active = date('Y-m-d H:i:s');
                    $provider_details->device_token = $request->get('device_token');
                    $provider_details->status = 3;
                    $provider_details->country_code = $request->get('select_country_code');
                    $provider_details->currency = $request->get('select_currency');
                    $provider_details->language = $request->get('select_language');
                    $provider_details->login_device = $request->get('login_device') != Null ? $request->get('login_device') : 0;
                    $provider_details->save();
                } else {
                    return response()->json([
                        'status' => 0,
//                        'message' => 'Email Already Exist!',
                        'message' => __('store_messages.11'),
                        "message_code" => 11,
                    ]);
                }
            } else {
                if ($provider_details->status == 2) {
                    return response()->json([
                        'status' => 3,
//                        'message' => 'Your account is currently blocked, so not authorised to allow any activity!',
                        'message' => __('store_messages.3'),
                        "message_code" => 3,
                    ]);
                }
                //$provider_details->last_active = date('Y-m-d H:i:s');
                //$provider_details->device_token = $request->get('device_token');
                //$provider_details->country_code = $request->get('select_country_code');
                //$provider_details->currency = $request->get('select_currency');
                //$provider_details->language = $request->get('select_language');
                //$provider_details->login_device = $request->get('login_device') != Null ? $request->get('login_device') : 1;
                //$provider_details->save();
            }
            //$provider_details->generateAccessToken($provider_details->id);

            return $this->storeClassApi->storeLoginRegisterResponse($provider_details, $request_type =1 ,$request);
        }
    }

    public function postOnDemandLogin(Request $request)
    {
        $this->notificationClass->ApiLogDetail($logger_type = 3, 0, "postOnDemandLogin", $request->all());

        $validator = Validator::make($request->header(), [
            'select-time-zone' => 'required',
            'select-ip-address' => 'nullable',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()->first(),
                'message_code' => 9,
            ]);
        }
        $validator = Validator::make($request->all(), [
            'login_type' => 'required|in:facebook,google,email,apple',
            'device_token' => 'required',
            'select_language' => "required",
            'select_country_code' => "nullable",
            'select_currency' => "required",
            'login_device' => "nullable|in:1,2,3,4"
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }

        $login_type = $request->get('login_type');
        if ($login_type != "facebook" && $login_type != "google" && $login_type != "apple") {
            $validator = Validator::make($request->all(), [
                'email' => 'required',
                'password' => 'required',
                'select_country_code' => "required"
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => 0,
                    'message' => $validator->errors()->first(),
                    "message_code" => 9,
                ]);
            }
            if (is_numeric($request->get('email'))) {
                $provider_details = Provider::query()->where('provider_type','=',3)->where('contact_number', $request->get('email'))->where('country_code', $request->get('select_country_code'))->whereNull('providers.deleted_at')->first();
            } else {
                $provider_details = Provider::query()->where('provider_type','=',3)->where('email', $request->get('email'))->whereNull('providers.deleted_at')->first();
            }
            if ($provider_details != Null) {
                if ($provider_details->status == 2) {
                    return response()->json([
                        'status' => 3,
//                        'message' => 'Your account is currently blocked, so not authorised to allow any activity!',
                        'message' =>  __('provider_messages.3'),
                        "message_code" => 3,
                    ]);
                }
                if (Hash::check($request->get('password'), $provider_details->password)) {
                    //$provider_details->last_active = date('Y-m-d h:i:s');
                    //$provider_details->device_token = $request->get('device_token');
                    //$provider_details->country_code = $request->get('select_country_code');
                    //$provider_details->currency = $request->get('select_currency');
                    //$provider_details->language = $request->get('select_language');
                    //$provider_details->login_device = $request->get('login_device') != Null ? $request->get('login_device') : 1;
                    //$provider_details->save();
                    //$provider_details->generateAccessToken($provider_details->id);
                    return $this->onDemandClassApi->onDemandLoginRegisterResponse($provider_details, $request_type =1 ,$request);
                } else {
                    return response()->json([
                        'status' => 0,
//                        'message' => 'You have entered an incorrect password',
                        'message' => __('provider_messages.10'),
                        "message_code" => 10,
                    ]);
                }
            } else {
                return response()->json([
                    'status' => 5,
//                    'message' => 'User Not Found!',
                    'message' => __('provider_messages.5'),
                    "message_code" => 5,
                ]);
            }
        }
        else {
            $validator = Validator::make($request->all(), [
                'full_name' => 'required',
                //"last_name" => "required",
                'email' => 'nullable|email',
                'login_id' => 'required',
                'profile_image' => 'nullable',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => 0,
                    'message' => $validator->errors()->first(),
                    "message_code" => 9,
                ]);
            }

            $check_email = null;
            $provider_details = Provider::query()->where('provider_type','=',3)->where('login_type', $request->get('login_type'))->where('login_id', $request->get('login_id'))->whereNull('providers.deleted_at')->first();
            if ($provider_details == Null) {

                if ($request->get('email') <> null){
                    $check_email = Provider::query()->select('id')->where('email', $request->get('email'))->whereNull('providers.deleted_at')->first();
                }
                if ($check_email == Null) {

                    $provider_details = new Provider();
                    if(trim($request->get("full_name")) != "N/A") {
                        $provider_details->first_name = ucwords(strtolower(trim($request->get('full_name'))));
                    }
                    /*$name = array_map('trim', explode(" ", $request->get('name')));
                    if (count($name) > 1) {
                        $provider_details->first_name = ucwords(strtolower(trim($name[0])))." ".ucwords(strtolower(trim($name[1])));
                        $provider_details->last_name = "";
                    } else {
                        $provider_details->first_name = ucwords(strtolower(trim($name[0])));
                    }*/
                    if (filter_var($request->get('profile_image'), FILTER_VALIDATE_URL) == true) {
                        $provider_details->avatar = $request->get('profile_image');
                    }
                    if ($request->get('email') != Null) {
                        $provider_details->email = $request->get('email');
                    }
                    //if ($request->get('login_device') != Null && $request->get('login_device') == 2) {
                    //    $provider_details->contact_number = "+" . random_int(1, 99) . date('siHd') . random_int(1, 9);
                    //    $provider_details->verified_at = date('Y-m-d H:i:s');
                    //}
                    $provider_details->verified_at = date('Y-m-d h:i:s');
                    $provider_details->provider_type = 3;
                    $provider_details->login_type = $request->get('login_type');
                    $provider_details->login_id = $request->get('login_id');
                    $provider_details->device_token = $request->get('device_token');
                    $provider_details->last_active = date('Y-m-d h:i:s');
                    $provider_details->device_token = $request->get('device_token');
                    $provider_details->status = 3;
                    $provider_details->country_code = $request->get('select_country_code');
                    $provider_details->currency = $request->get('select_currency');
                    $provider_details->language = $request->get('select_language');
                    $provider_details->login_device = $request->get('login_device') != Null ? $request->get('login_device') : 0;
                    $provider_details->ip_address = $request->header('select-ip-address')!= Null ? $request->header('select-ip-address') : Null;
                    $provider_details->time_zone = $request->header('select-time-zone')!= Null ? $request->header('select-time-zone') : Null;
                    $provider_details->save();
                } else {
                    return response()->json([
                        'status' => 0,
//                        'message' => 'Email Already Exist!',
                        'message' =>  __('provider_messages.11'),
                        "message_code" => 11,
                    ]);
                }
            } else {
                if ($provider_details->status == 2) {
                    return response()->json([
                        'status' => 3,
//                        'message' => 'Your account is currently blocked, so not authorised to allow any activity!',
                        'message' =>  __('provider_messages.3'),
                        "message_code" => 3,
                    ]);
                }
                //$provider_details->last_active = date('Y-m-d h:i:s');
                //$provider_details->device_token = $request->get('device_token');
                //$provider_details->country_code = $request->get('select_country_code');
                //$provider_details->currency = $request->get('select_currency');
                //$provider_details->language = $request->get('select_language');
                //$provider_details->login_device = $request->get('login_device') != Null ? $request->get('login_device') : 0;
                //$provider_details->save();
            }
            //$provider_details->generateAccessToken($provider_details->id);
            return $this->onDemandClassApi->onDemandLoginRegisterResponse($provider_details, $request_type =1 ,$request);
            //return $this->onDemandClassApi->onDemandLoginRegisterResponse($provider_details);
        }
    }

}
