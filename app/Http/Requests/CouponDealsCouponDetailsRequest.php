<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CouponDealsCouponDetailsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
//            "id" => "required|integer",
            "category_id" => "required|integer",
            "coupon_name" => "required",
            "coupon_brand_name" => "nullable",
            "coupon_amount" => "required|numeric",
            "coupon_code" => "nullable",
            "link" => "nullable",
            "status" => "required",
            "coupon_image" => "nullable|file",
            "active_date" => "nullable",
            "expiry_date" => "nullable",
            "terms_condition" => "required",
            "how_to_redeem" => "required"
        ];
        if (!empty($this->get('id'))) {
            unset($rules['coupon_image']);
        }
        return $rules;
    }
}
