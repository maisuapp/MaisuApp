<?php

namespace App\model;

use Illuminate\Database\Eloquent\Model;

class StoreProductDetails extends Model
{
    protected $table = 'store_product_details';
    protected $guarded = array();

    public function generateSlug($product_details_name,$store_id)
    {
        $store_id = strtolower(str_replace(' ','-',trim($store_id)));
        $product_details_name = strtolower(str_replace(' ','-',trim($product_details_name)));
        $new_category_name = $product_details_name.'-'.$store_id;
        return $new_category_name;
    }

    public static function getDiscountPrice($price,$discount_value,$discount_type)
    {
        $type = $discount_type;

        if($type == 2){
            $discount_value = number_format(($price * $discount_value) / 100,2);
            $newPrice  = ($price - $discount_value);
            return $newPrice;
        }elseif ($type == 1){
            $newPrice  = ($price - $discount_value);
            return $newPrice;
        }
        else{
//            $newPrice  = ($price - $discount_value);
            return $price;
        }

    }
}
