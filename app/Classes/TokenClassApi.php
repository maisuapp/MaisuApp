<?php
/**
 * Created by PhpStorm.
 * User: Froyo Khyati
 * Date: 27-May-19
 * Time: 10:43 AM
 */

namespace App\Classes;


use App\model\GeneralSettings;
use App\model\Provider;
use App\model\ProviderVerification;
use App\model\User;
use App\model\UserVerification;
use Illuminate\Support\Facades\Mail;
use Twilio\Rest\Client;

class TokenClassApi
{
    public function sendUserSmsVerification($user_id)
    {
        $user_details = User::query()->where('id',$user_id)->first();
        $settings = request()->get('general_settings');
        if ($settings == null){
            return response()->json([
                "status" => 0,
                "message" => "something went to wrong!",
                "message_code" => 9,
            ]);
        }

        if ($settings->is_otp_verification == 1)
        {
            if ($settings->vonage_api_key == null || $settings->vonage_api_secret_key == null){
                return response()->json([
                    "status" => 0,
                    "message" => "something went to wrong!",
                    "message_code" => 9,
                ]);
            }

            try {
                $basic  = new \Vonage\Client\Credentials\Basic(key: $settings->vonage_api_key, secret: $settings->vonage_api_secret_key);
                $client = new \Vonage\Client(new \Vonage\Client\Credentials\Container($basic));

                $get_old_request = UserVerification::query()->where('user_id',$user_details->id)->latest()->first();
                if ($get_old_request <> null){
                    try {
                        $cancel_request = $client->verify()->cancel($get_old_request->token);
                    } catch(\Exception $e) {
                        \Log::info($e);
                    }
                }

                $request = new \Vonage\Verify\Request($user_details->country_code.$user_details->contact_number, "Maisu");
                $response = $client->verify()->start($request);

//                echo "Started verification, `request_id` is " . $response->getRequestId();
                $requestId = $response->getRequestId();
                UserVerification::query()->where('user_id', $user_details->id)->delete();
                $verification_token = new UserVerification();
                $verification_token->user_id  = $user_details->id;
                $verification_token->token = $requestId;
                $verification_token->save();

            } catch (\Exception $exception){
                \Log::info($exception);
                return response()->json([
                    "status" => 0,
                    "message" => $exception->getMessage(),
                    "message_code" => 9,
                ]);
            }
        }
    }

    public function sendProviderSmsVerification($provider_id)
    {
        $provider_details = Provider::query()->where('id',$provider_id)->first();
        $settings = request()->get('general_settings');
        if ($settings == null){
            return response()->json([
                "status" => 0,
                "message" => "something went to wrong!",
                "message_code" => 9,
            ]);
        }

        if ($settings->is_otp_verification == 1)
        {
            if ($settings->vonage_api_key == null || $settings->vonage_api_secret_key == null){
                return response()->json([
                    "status" => 0,
                    "message" => "something went to wrong!",
                    "message_code" => 9,
                ]);
            }

            try {
                $basic  = new \Vonage\Client\Credentials\Basic(key: $settings->vonage_api_key, secret: $settings->vonage_api_secret_key);
                $client = new \Vonage\Client(new \Vonage\Client\Credentials\Container($basic));

                $get_old_request = ProviderVerification::query()->where('provider_id',$provider_details->id)->latest()->first();
                if ($get_old_request <> null){
                    try {
                        $cancel_request = $client->verify()->cancel($get_old_request->token);
                    } catch(\Exception $e) {
                        \Log::info($e);
                    }
                }

                $request = new \Vonage\Verify\Request($provider_details->country_code.$provider_details->contact_number, "Maisu");
                $response = $client->verify()->start($request);

                $requestId = $response->getRequestId();
                ProviderVerification::query()->where('provider_id', $provider_details->id)->delete();
                $verification_token = new ProviderVerification();
                $verification_token->provider_id  = $provider_details->id;
                $verification_token->token = $requestId;
                $verification_token->save();

            } catch (\Exception $exception){
                \Log::info($exception);
                return response()->json([
                    "status" => 0,
                    "message" => $exception->getMessage(),
                    "message_code" => 9,
                ]);
            }
        }
    }
}
