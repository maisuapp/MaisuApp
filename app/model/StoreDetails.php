<?php

namespace App\model;

use Illuminate\Database\Eloquent\Model;

class StoreDetails extends Model
{
    protected $table = 'store_details';

    public function slug($store_name, $service_cat_name)
    {
        $store_name = mb_strtolower($store_name, "UTF-8");
        $service_cat_name = mb_strtolower($service_cat_name, "UTF-8");
        $store_name = preg_replace("/-$/", "", preg_replace('/[^a-z0-9]+/i', "-", $store_name));
        $service_cat_name = preg_replace("/-$/", "", preg_replace('/[^a-z0-9]+/i', "-", $service_cat_name));
        $slug = trim($store_name . '-' . $service_cat_name);
        $count = StoreDetails::where('slug', 'like', $slug . '%')->count();
    }
}
