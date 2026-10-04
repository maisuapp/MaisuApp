<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PromocodeDetailsRequest extends FormRequest
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
        return [
            "service_cat_id" => "required",
            "code_name" => "required",
            "expiry_date_time" => "required",
            "discount_type" => "required",
            "discount_amount" => "required",
            "min_order_amount" => "nullable",
            "max_discount_amount" => "nullable",
            "coupon_limit" => "nullable",
            "usage_limit" => "required",
            "description" => "required"
        ];
    }
}
