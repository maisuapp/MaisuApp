<?php

namespace App\model;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Auth;

class Provider extends Authenticatable
{
    use Notifiable;

    protected $table = 'providers';

    protected $guard = ['store', 'on_demand'];

    Private $store_array_id = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

    public function generateAccessToken($provider_id)
    {
        $this->access_token = random_int(1, 99) . date('siHYdm') . random_int(1, 99);
        $this->save();
        return $this->access_token;
    }

    public function checkStoreProviderId($guard = "store")
    {
        $id = Auth::guard($guard)->user()->id;
        if ($id != Null) {
            $provider_service = ProviderServices::query()->where('provider_id', $id)->whereNotIn('service_cat_id', $this->store_array_id)->first();
        } else {
            $provider_service = Null;
        }
        $store_service = [
            "id" => $provider_service != Null ? $provider_service->id : 0,
            "status" => $provider_service != Null ? $provider_service->status : 0
        ];
        return $store_service;
    }

    public function checkStoreId()
    {
        $id = Auth::guard('store')->user()->id;
        if ($id != Null) {
            $provider_service = ProviderServices::where('provider_id', $id)->whereIN('service_cat_id', $this->store_array_id)->first();
            if ($provider_service != Null) {
                $store_details = StoreDetails::where('provider_service_id', $provider_service->id)->first();
                if ($store_details != Null) {
                    $store_id = $store_details->id;
                } else {
                    $store_id = 0;
                }
            } else {
                $store_id = 0;
            }
        } else {
            $store_id = 0;
        }
        return $store_id;
    }

    public function checkStoreCatId()
    {
        $id = Auth::guard('store')->user()->id;
        if ($id != Null) {
            $provider_service = ProviderServices::where('provider_id', $id)->whereIN('service_cat_id', $this->store_array_id)->first();
            if ($provider_service != Null) {
                $store_cat_id = $provider_service->service_cat_id;
            } else {
                $store_cat_id = 0;
            }
        } else {
            $store_cat_id = 0;
        }
        return $store_cat_id;
    }
}
