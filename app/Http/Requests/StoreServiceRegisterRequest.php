<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreServiceRegisterRequest extends FormRequest
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
            "provider_id" => "required",
            "user_name" => "required",
//            "email" => "required|unique:providers,email," . $this->get('provider_id'),
//            "contact_number" => "required|numeric|unique:providers,contact_number," . $this->get('provider_id'),
            'email' => [
                'required',
                Rule::unique('providers','email')->where(function($query) {
                    $query->where('email', '=', $this->get('email'));
                    $query->where('id', '!=', $this->get('provider_id'));
                    $query->where('deleted_at', '=', null);
                })
            ],
            'contact_number' => [
                'required','numeric',
                Rule::unique('providers')->where(function($query) {
                    $query->where('contact_number', '=', $this->get('contact_number'));
                    $query->where('country_code', '=', $this->get('country_code'));
                    $query->where('id', '!=', $this->get('provider_id'));
                    $query->where('deleted_at', '=', null);
                })
            ],
            "gender" => "required|in:1,2",
            "service_category" => "required",
            "store_name" => "required",
            "delivery_radius" => "required|numeric",
            "eta_delivery_time" => "required|numeric",
            "order_min_amount" => "nullable|integer",
            "packaging_charges" => "nullable|integer",
            "description" => "nullable",
            "store_address" => "required",
            "address_lat" => "required",
            "address_long" => "required",
            "open_time" => "required",
            "close_time" => "required",
            "store_banner" => "required|image|max:250",
            "documents" => "required"
        ];
    }
}
