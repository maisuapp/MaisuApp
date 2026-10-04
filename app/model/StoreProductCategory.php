<?php

namespace App\model;

use Illuminate\Database\Eloquent\Model;

class StoreProductCategory extends Model
{
    protected $table = 'store_product_category';

    public function generateSlug($category_name,$service_category_name)
    {
        $service_category_name = strtolower(str_replace(' ','-',trim($service_category_name)));
        $category_name = strtolower(str_replace(' ','-',trim($category_name)));
        $new_category_name = $category_name.'-'.$service_category_name;
        return $new_category_name;
    }


}
