<?php

namespace App\Http\Controllers\Api\Auth;

use App\model\Provider;
use App\model\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class LogoutController extends Controller
{
//        json response status [
//            0 => false,
//            1 => true,
//            2 => registration pending,
//            3 => app user blocked,
//            4 => app user access token not match,
//            5 => app user not found
//          ]
    public function postCustomerLogout(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "user_id" => "required|numeric",
            "access_token" => "required|numeric"
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }

        $user = User::where('id', $request->get('user_id'))->first();
        if ($user != Null) {
            $user->access_token = Null;
            $user->device_token = Null;
            $user->save();
        }
        return response()->json([
            "status" => 1,
//            "message" => "success!",
            'message' => __('user_messages.1'),
            "message_code" => 1,
        ]);
    }

    public function postDriverLogout(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "driver_id" => "required|numeric",
            "access_token" => "required|numeric"
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }

        $driver = Provider::where('id', $request->get('driver_id'))->first();
        if ($driver != Null) {
            $driver->access_token = Null;
            $driver->device_token = Null;
            $driver->save();
        }
        return response()->json([
            "status" => 1,
//            "message" => "success!",
            "message" => __('driver_messages.1'),
            "message_code" => 1,
        ]);
    }

    public function postProviderLogout(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "provider_id" => "required|integer",
            "access_token" => "required|numeric"
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }

        $driver = Provider::where('id', $request->get('provider_id'))->first();
        if ($driver != Null) {
            $driver->access_token = Null;
            $driver->device_token = Null;
            $driver->save();
        }
        return response()->json([
            "status" => 1,
//            "message" => "success!",
            "message" => __('provider_messages.1'),
            "message_code" => 1,
        ]);
    }

    public function postStoreLogout(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "store_id" => "required|integer",
            "access_token" => "required|numeric",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }

        $driver = Provider::where('id', $request->get('store_id'))->first();
        if ($driver != Null) {
            $driver->access_token = Null;
            $driver->device_token = Null;
            $driver->save();
        }
        return response()->json([
            "status" => 1,
//            "message" => "success!",
            "message" => __('store_messages.1'),
            "message_code" => 1,
        ]);
    }

    public function postShopperLogout(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "shopper_id" => "required|integer",
            "access_token" => "required|numeric"
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => 0,
                "message" => $validator->errors()->first(),
                "message_code" => 9,
            ]);
        }

        $provider = Provider::where('id', $request->get('shopper_id'))->first();
        if ($provider != Null) {
            $provider->access_token = Null;
            $provider->device_token = Null;
            $provider->save();
        }
        return response()->json([
            "status" => 1,
            "message" => "success!",
            "message_code" => 1,
        ]);
    }
}
