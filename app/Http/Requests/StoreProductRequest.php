<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
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
            "store_id" => "required|integer",
            "service_cat_id" => "required|integer",
            "product_name" => "required",
            "product_category" => "required|integer",
            "product_amount" => "required|numeric",
            "discount_amount" => "nullable|required_with:discount_type",
            "discount_type" => "nullable|required_with:discount_amount",
            "description" => "nullable",
            "status" => "required",
            "product_image" => "required|file"
        ];
        if (!empty($this->get('id'))) {
            unset($rules['product_image']);
        }
        return $rules;
    }
}
