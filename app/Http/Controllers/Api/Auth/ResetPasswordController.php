<?php

namespace App\Http\Controllers\Api\Auth;

use App\Classes\DriverClassApi;
use App\Classes\OnDemandClassApi;
use App\Classes\ShopperClassApi;
use App\Classes\StoreClassApi;
use App\Classes\TokenClassApi;
use App\Classes\UserClassApi;
use App\model\GeneralSettings;
use App\model\Provider;
use App\model\ProviderVerification;
use App\model\User;
use App\model\UserVerification;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Twilio\Rest\Client;

class ResetPasswordController extends Controller
{
//        json response status [
//            0 => false,
//            1 => true,
//            2 => registration pending,
//            3 => app user blocked,
//            4 => app user access token not match,
//            5 => app user not found
//          ]

    private $userClassapi;
    private $driverClassApi;
    private $storeClassApi;
    private $onDemandClassApi;
    private $tokenClassApi;
    private $shopperClassApi;

    public function __construct(TokenClassApi $tokenClassApi, UserClassApi $userClassapi, DriverClassApi $driverClassApi, StoreClassApi $storeClassApi, OnDemandClassApi $onDemandClassApi, ShopperClassApi $shopperClassApi)
    {
        $this->userClassapi = $userClassapi;
        $this->tokenClassApi = $tokenClassApi;
        $this->driverClassApi = $driverClassApi;
        $this->storeClassApi = $storeClassApi;
        $this->onDemandClassApi = $onDemandClassApi;
        $this->shopperClassApi = $shopperClassApi;
    }

    public function postCustomerChangePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
                "user_id" => "required|numeric",
                "access_token" => "required",
                "old_password" => "required",
                "new_password" => "required"
            ]
        );
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 0,
            ]);
        }

        $user_details = $this->userClassapi->checkUserAllow($request->get('user_id'), $request->get('access_token'));
        if ($decoded['status'] = json_decode($user_details) == false) {
            return $user_details;
        }
        if ($user_details->login_type != "facebook" && $user_details->login_type != "google") {
            if (Hash::check($request->get('old_password'), $user_details->password)) {
                $user_details->password = Hash::make($request->get('new_password'));
                $user_details->save();
                return response()->json([
                    'status' => 1,
//                    'message' => "Your Password Changed Successfully!",
                    'message' => __('user_messages.1'),
                    "message_code" => 1,
                ]);
            } else {
                return response()->json([
                    'status' => 0,
//                    'message' => "You have entered an incorrect password",
                    'message' => __('user_messages.10'),
                    "message_code" => 10,
                ]);
            }
        } else {
            return response()->json([
                'status' => 0,
//                'message' => "login with social parameters",
                'message' => __('user_messages.13'),
                "message_code" => 13,
            ]);
        }
    }

    public function postDriverChangePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
                "driver_id" => "required|numeric",
                "access_token" => "required",
                "old_password" => "required",
                "new_password" => "required"
            ]
        );
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }

        $provider_details = Provider::where('id', $request->get('driver_id'))->whereNull('providers.deleted_at')->first();
        if ($provider_details != Null) {
            if ($provider_details->login_type != "facebook" && $provider_details->login_type != "google") {
                if (Hash::check($request->get('old_password'), $provider_details->password)) {
                    $provider_details->password = Hash::make($request->get('new_password'));
                    $provider_details->save();
                    return response()->json([
                        'status' => 1,
//                        'message' => "Your Password Changed Successfully!",
                        'message' => __('driver_messages.1'),
                        "message_code" => 1,
                    ]);
                } else {
                    return response()->json([
                        'status' => 0,
//                        'message' => "You have entered an incorrect password",
                        'message' => __('driver_messages.10'),
                        "message_code" => 10,
                    ]);
                }
            } else {
                return response()->json([
                    'status' => 0,
//                    'message' => "login with social parameters",
                    'message' => __('driver_messages.13'),
                    "message_code" => 13,
                ]);
            }
        } else {
            return response()->json([
                'status' => 5,
//                'message' => "driver not found!",
                'message' => __('driver_messages.5'),
                "message_code" => 5,
            ]);
        }
    }

    public function postStoreChangePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
                "store_id" => "required|numeric",
                "access_token" => "required",
                "old_password" => "required",
                "new_password" => "required"
            ]
        );
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }

        $provider_details = Provider::query()->where('id', $request->get('store_id'))->whereNull('providers.deleted_at')->first();
        if ($provider_details != Null) {
            if ($provider_details->login_type != "facebook" && $provider_details->login_type != "google") {
                if (Hash::check($request->get('old_password'), $provider_details->password)) {
                    $provider_details->password = Hash::make($request->get('new_password'));
                    $provider_details->save();
                    return response()->json([
                        'status' => 1,
//                        'message' => "Your Password Changed Successfully!",
                        'message' => __('store_messages.1'),
                        "message_code" => 1,
                    ]);
                } else {
                    return response()->json([
                        'status' => 0,
//                        'message' => "You have entered an incorrect password",
                        'message' => __('store_messages.10'),
                        "message_code" => 10,
                    ]);
                }
            } else {
                return response()->json([
                    'status' => 0,
//                    'message' => "login with social parameters",
                    'message' => __('store_messages.13'),
                    "message_code" => 13,
                ]);
            }
        } else {
            return response()->json([
                'status' => 5,
//                'message' => "store not found!",
                'message' => __('store_messages.5'),
                "message_code" => 5,
            ]);
        }
    }

    public function postProviderChangePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
                "provider_id" => "required|integer",
                "access_token" => "required",
                "old_password" => "required",
                "new_password" => "required"
            ]
        );
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }

        $provider_details = Provider::query()->where('id', $request->get('provider_id'))->whereNull('providers.deleted_at')->first();
        if ($provider_details != Null) {
            if ($provider_details->login_type != "facebook" && $provider_details->login_type != "google") {
                if (Hash::check($request->get('old_password'), $provider_details->password)) {
                    $provider_details->password = Hash::make($request->get('new_password'));
                    $provider_details->save();
                    return response()->json([
                        'status' => 1,
//                        'message' => "Your Password Changed Successfully!",
                        'message' =>  __('provider_messages.1'),
                        "message_code" => 1,
                    ]);
                } else {
                    return response()->json([
                        'status' => 0,
//                        'message' => "You have entered an incorrect password",
                        'message' => __('provider_messages.10'),
                        "message_code" => 10,
                    ]);
                }
            } else {
                return response()->json([
                    'status' => 0,
//                    'message' => "login with social parameters",
                    'message' => __('provider_messages.13'),
                    "message_code" => 13,
                ]);
            }
        } else {
            return response()->json([
                'status' => 5,
//                'message' => "store not found!",
                'message' => __('provider_messages.5'),
                "message_code" => 5,
            ]);
        }
    }


    //forgot password customer
    public function postCustomerForgotPasswordRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
                "contact_number" => "required|numeric",
                'select_country_code' =>"required"
            ]
        );
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }

        $get_user = User::query()->where('contact_number', $request->get('contact_number'))->where('country_code', $request->get('select_country_code'))->whereNull('users.deleted_at')->first();
        if ($get_user == Null) {
            return response()->json([
                "status" => 0,
//                "message" => "Not Registered.",
                'message' => __('user_messages.5'),
                "message_code" => 5,
            ]);
        }
        if ($get_user->status == 0) {
            return response()->json([
                "status" => 0,
//                "message" => "User blocked.",
                'message' => __('user_messages.3'),
                "message_code" => 3,
            ]);
        } elseif ($get_user->status == 2) {
            return response()->json([
                "status" => 0,
//                "message" => "User registration pending.",
                'message' => __('user_messages.88'),
                "message_code" => 88,
            ]);
        } elseif ($get_user->status == 1) {
            $this->tokenClassApi->sendUserSmsVerification($get_user->id);
            return response()->json([
                "status" => 1,
//                "message" => "OTP has been sent to your registered Contact Number!",
                'message' => __('user_messages.1'),
                "user_id" => $get_user->id,
                "message_code" => 1,
            ]);
        } else {
            return response()->json([
                "status" => 0,
//                "message" => "something went to wrong.",
                'message' => __('user_messages.9'),
                "message_code" => 9,
            ]);
        }
    }

    public function postCustomerForgotChangePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "user_id" => "required|integer",
            "otp" => "required|numeric|digits:4",
            "new_password" => "required",
            "login_device" => "required|in:1,2,3,4"
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

        $get_user = User::query()->where('id', $request->get('user_id'))->whereNull('users.deleted_at')->first();
        $user_details = $get_user;
        if ($get_user != Null) {
            if ($get_user->status == 0) {
                return response()->json([
                    "status" => 0,
//                    "message" => "User blocked.",
                    'message' => __('user_messages.3'),
                    "message_code" => 3,
                ]);
            } elseif ($get_user->status == 2) {
                return response()->json([
                    "status" => 0,
//                    "message" => "User registration pending.",
                    'message' => __('user_messages.88'),
                    "message_code" => 88,
                ]);
            } elseif ($get_user->status == 1) {

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
//                if ($request->get('otp') == $get_otp->token) {
                    $get_user->login_device = $request->get('login_device');
                    $get_user->password = Hash::make($request->get('new_password'));
                    $get_user->save();

                    $get_user->generateAccessToken($get_user->id);

                    return $this->userClassapi->userLoginRegisterUpdateDetails($get_user);
                } else {
                    return response()->json([
                        "status" => 0,
//                        "message" => "Invalid Otp!",
                        'message' => __('user_messages.89'),
                        "message_code" => 89,
                    ]);
                }
            } else {
                return response()->json([
                    "status" => 0,
//                    "message" => "something went to wrong.",
                    'message' => __('user_messages.9'),
                    "message_code" => 9,
                ]);
            }
        } else {
            return response()->json([
                "status" => 0,
//                "message" => 'user not found',
                'message' => __('user_messages.5'),
                "message_code" => 5,
            ]);
        }
    }

    //forgot password Driver
    public function postTransportDriverForgotPasswordRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "contact_number" => "required|numeric",
            'select_country_code' =>"required"
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }
        $get_provider = Provider::query()->select('providers.*')
            ->join('provider_services','provider_services.provider_id','=','providers.id')
            ->whereIn('provider_services.service_cat_id', [1,2,4])
            ->where('providers.contact_number', $request->get('contact_number'))
            ->where('providers.country_code', $request->get('select_country_code'))
            ->where('providers.provider_type', '=',2)
            ->whereNull('providers.deleted_at')
            ->first();
        if ($get_provider == Null) {
            return response()->json([
                'status' => 0,
//                'message' => 'App User Not Found',
                'message' => __('driver_messages.5'),
                'message_code' => 5
            ]);
        }
        if ($get_provider->status == 2) {
            return response()->json([
                "status" => 3,
//                "message" => "Driver blocked.",
                "message" => __('driver_messages.3'),
                "message_code" => 3,
            ]);
        } else {
            $this->tokenClassApi->sendProviderSmsVerification($get_provider->id);

            return response()->json([
                "status" => 1,
//                "message" => "OTP has been sent to your registered Contact Number!",
                "message" => __('driver_messages.1'),
                "driver_id" => $get_provider->id,
                "message_code" => 1,
            ]);
        }
    }

    public function postTransportDriverForgotChangePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "driver_id" => "required|integer",
            "otp" => "required|numeric|digits:4",
            "new_password" => "required",
            "login_device" => "required|in:1,2,3,4"
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
        $get_driver = Provider::query()->where('id', $request->get('driver_id'))->whereNull('providers.deleted_at')->first();
        if ($get_driver != Null) {
            if ($get_driver->status == 2) {
                return response()->json([
                    "status" => 3,
//                    "message" => "Driver blocked.",
                    "message" => __('driver_messages.3'),
                    "message_code" => 3,
                ]);
            }
            if ($request->get('otp') == "1234") {
                $get_driver->password = Hash::make($request->get('new_password'));
                $get_driver->login_device = $request->get('login_device');
                $get_driver->save();

                $get_driver->generateAccessToken($get_driver->id);

                return $this->driverClassApi->DriverLoginRegisterResponse($get_driver);
            } else {
                return response()->json([
                    "status" => 0,
//                    "message" => "Invalid Otp!",
                    "message" => __('driver_messages.89'),
                    "message_code" => 89,
                ]);
            }
            /*$get_otp = ProviderVerification::where('provider_id', $get_driver->id)->first();
            if ($get_otp == Null) {
                return response()->json([
                    "status" => 0,
//                    "message" => "something went to wrong.",
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
                    'To' => $get_driver->contact_number,
                    'VerificationSid' => $get_otp->token,
                ];
                $verification_check = $twilio->verify->v2->services($settings->twilio_verify_service_key)
                    ->verificationChecks
                    ->create($request->get('otp'), $option);
                if ($verification_check->status == "approved") {
                    $verification_sid = $verification_check->sid;

                    if ($verification_sid != $get_otp->token) {
                        return response()->json([
                            "status" => 0,
//                            "message" => "something went to wrong!",
                            "message" => __('driver_messages.9'),
                            "message_code" => 9,
                        ]);
                    }
                    ProviderVerification::where('provider_id', $get_driver->id)->delete();
                    $get_driver->password = Hash::make($request->get('new_password'));
                    $get_driver->login_device = $request->get('login_device');
                    $get_driver->save();
                    $get_driver->generateAccessToken($get_driver->id);

                    return $this->driverClassApi->DriverLoginRegisterResponse($get_driver);
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
//                    "message" => "Entered OTP is Incorrect!",
                    "message" => __('driver_messages.89'),
                    "message_code" => 89,
                ]);
            }*/
        } else {
            return response()->json([
                "status" => 0,
//                "message" => 'user not found',
                "message" => __('driver_messages.5'),
                "message_code" => 5,
            ]);
        }
    }

    //forgot password Store
    public function postStoreForgotPasswordRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "contact_number" => "required|numeric",
            'select_country_code' =>"required"
        ]);
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }

        $get_provider = Provider::query()->select('providers.*')
            ->join('provider_services','provider_services.provider_id','=','providers.id')
            ->whereIn('provider_services.service_cat_id', [5,6,7,8,9,10])
            ->where('providers.contact_number', $request->get('contact_number'))
            ->where('providers.country_code', $request->get('select_country_code'))
            ->where('providers.provider_type', '=',1)
            ->whereNull('providers.deleted_at')
            ->first();
        if ($get_provider == Null) {
            return response()->json([
                'status' => 0,
//                'message' => 'App User Not Found',
                'message' => __('store_messages.5'),
                "message_code" => 5,
            ]);
//            return response()->json([
//                "status" => 0,
//                "message" => "Not Registered.",
//                "message_code" => 5,
//            ]);
        }
        if ($get_provider->status == 2) {
            return response()->json([
                "status" => 3,
//                "message" => "Store blocked.",
                "message" => __('store_messages.3'),
                "message_code" => 3,
            ]);
        } else {
            $this->tokenClassApi->sendProviderSmsVerification($get_provider->id);
            return response()->json([
                "status" => 1,
//                "message" => "OTP has been sent to your registered Contact Number!",
                "message" => __('store_messages.162'),
                "store_id" => $get_provider->id,
                "message_code" => 1,
            ]);
        }
    }

    public function postStoreForgotChangePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "store_id" => "required|integer",
            "otp" => "required|numeric|digits:4",
            "new_password" => "required",
            "login_device" => "required|in:1,2,3,4"
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
        $get_store = Provider::query()->where('id', $request->get('store_id'))->whereNull('providers.deleted_at')->first();
        if ($get_store != Null) {
            if ($get_store->status == 2) {
                return response()->json([
                    "status" => 0,
//                    "message" => "Store blocked.",
                    "message" => __('store_messages.3'),
                    "message_code" => 3,
                ]);
            }
            if ($request->get('otp') == "1234") {
                $get_store->password = Hash::make($request->get('new_password'));
                $get_store->login_device = $request->get('login_device');
                $get_store->save();

                $get_store->generateAccessToken($get_store->id);

                return $this->storeClassApi->storeLoginRegisterResponse($get_store);
            } else {
                return response()->json([
                    "status" => 0,
//                    "message" => "Invalid Otp!",
                    "message" => __('store_messages.89'),
                    "message_code" => 89,
                ]);
            }
            /*$get_token = ProviderVerification::where('provider_id', $get_store->id)->first();
            if ($get_token == Null) {
                return response()->json([
                    "status" => 0,
                    "message" => "something went to wrong!",
                    "message_code" => 9,
                ]);
            }
            $settings = GeneralSettings::first();
            if ($settings == Null || $settings->twilio_service_key == Null || $settings->twilio_auth_token == Null || $settings->twilio_verify_service_key == Null) {
                return response()->json([
                    "status" => 0,
                    "message" => "something went to wrong!",
                    "message_code" => 9,
                ]);
            }
            try {
                $twilio = new Client($settings->twilio_service_key, $settings->twilio_auth_token);
                $option = [
                    'To' => $get_store->contact_number,
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
                            "message" => "something went to wrong!",
                            "message_code" => 9,
                        ]);
                    }
                    ProviderVerification::where('provider_id', $get_store->id)->delete();
                    $get_store->password = Hash::make($request->get('new_password'));
                    $get_store->login_device = $request->get('login_device');
                    $get_store->save();
                    $get_store->generateAccessToken($get_store->id);

                    return $this->storeClassApi->storeLoginRegisterResponse($get_store);
                } else {
                    return response()->json([
                        "status" => 0,
                        "message" => "Entered OTP is Incorrect!",
                        "message_code" => 89,
                    ]);
                }
            } catch (\Exception $e) {
                return response()->json([
                    "status" => 0,
                    "message" => "Entered OTP is Incorrect!",
                    "message_code" => 89,
                ]);
            }*/
        } else {
            return response()->json([
                "status" => 0,
//                "message" => 'user not found',
                "message" => __('store_messages.5'),
                "message_code" => 5,
            ]);
        }
    }

    //forgot password On-demand Provider
    public function postOnDemandForgotPasswordRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
                "contact_number" => "required|numeric",
                'select_country_code' =>"required"
            ]
        );
        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }
        $get_provider = Provider::query()->select('providers.*')
            ->join('provider_services','provider_services.provider_id','=','providers.id')
            ->where('provider_services.service_cat_id', '>', 10)
            ->where('providers.contact_number', $request->get('contact_number'))
            ->where('providers.country_code', $request->get('select_country_code'))
            ->where('providers.provider_type', '=',3)
            ->whereNull('providers.deleted_at')
            ->first();
        if ($get_provider == Null) {
//            return response()->json([
//                "status" => 0,
//                "message" => "Not Registered.",
//                "message_code" => 5,
//            ]);
            return response()->json([
                'status' => 0,
//                'message' => 'App User Not Found',
                'message' => __('provider_messages.5'),
                "message_code" => 5
            ]);
        }
        if ($get_provider->status == 2) {
            return response()->json([
                "status" => 3,
//                "message" => "Store blocked.",
                "message" => __('provider_messages.3'),
                "message_code" => 3,
            ]);
        } else {
            $this->tokenClassApi->sendProviderSmsVerification($get_provider->id);
            return response()->json([
                "status" => 1,
//                "message" => "OTP has been sent to your registered Contact Number!",
                "message" => __('provider_messages.1'),
                "provider_id" => $get_provider->id,
                "message_code" => 1,
            ]);
        }
    }

    public function postOnDemandForgotChangePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
                "provider_id" => "required|integer",
                "otp" => "required|numeric|digits:4",
                "new_password" => "required",
                "login_device" => "required|in:1,2,3,4"
            ]
        );
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
        $get_provider = Provider::query()->where('id', $request->get('provider_id'))->whereNull('providers.deleted_at')->first();
        $provider_details = $get_provider;
        if ($get_provider != Null) {
            if ($get_provider->status == 2) {
                return response()->json([
                    "status" => 0,
//                    "message" => "Provider blocked.",
                    "message" => __('provider_messages.3'),
                    "message_code" => 3,
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
                $get_provider->password = Hash::make($request->get('new_password'));
                $get_provider->login_device = $request->get('login_device');
                $get_provider->save();

                $get_provider->generateAccessToken($get_provider->id);

                return $this->onDemandClassApi->onDemandLoginRegisterResponse($get_provider);
            } else {
                return response()->json([
                    "status" => 0,
//                    "message" => "Invalid Otp!",
                    "message" => __('provider_messages.89'),
                    "message_code" => 89,
                ]);
            }
        } else {
            return response()->json([
                "status" => 0,
//                "message" => 'user not found',
                "message" => __('provider_messages.5'),
                "message_code" => 5,
            ]);
        }
    }


}
