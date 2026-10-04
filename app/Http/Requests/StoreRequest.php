<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
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
            "store_name" => "required",
            "delivery_radius" => "required|numeric",
            "eta_delivery_time" => "required|numeric",
            "store_banner" => "required|image|max:250",
            "offer_type" => "required_with:offer_amount|required_with:offer_min_amount",
            "offer_amount" => "required_with:offer_min_amount|required_with:offer_type",
            "offer_min_amount" => "nullable|numeric",
            "order_min_amount" => "nullable|numeric",
            "packaging_charges" => "nullable|numeric",
            "description" => "nullable",
            "store_address" => "required",
            "address_lat" => "required",
            "address_long" => "required",
            "current_status" => "required",
//            "status" => "required",
            "first_name" => "required",
//            "last_name" => "required",
            "gender" => "required",
//            "email" => "required|email|unique:providers,email,".$this->get('provider_id'),
//            "email" => "required|".(($this->getHost() == 'fox-jek.startuptrinity.com')? "email|" : "" )."unique:providers,email,".$this->get('provider_id'),
            'email' => [
                'required',
                Rule::unique('providers','email')->where(function($query) {
                    $query->where('email', '=', $this->get('email'));
                    $query->where('id', '!=', $this->get('provider_id'));
                    $query->where('deleted_at', '=', null);
                })
            ],
            "password" => "required|min:6|max:16",
            "confirm_password" => "required|same:password",
            //"contact_number" => "required|numeric|unique:providers,contact_number,".$this->get('provider_id'),
//            "full_number" => "required|numeric|unique:providers,contact_number,".$this->get('provider_id'),
            'contact_number' => [
                'required','numeric',
                Rule::unique('providers')->where(function($query) {
                    $query->where('contact_number', '=', $this->get('contact_number'));
                    $query->where('country_code', '=', $this->get('country_code'));
                    $query->where('id', '!=', $this->get('provider_id'));
                    $query->where('deleted_at', '=', null);
                })
            ],
            //"contact_numbers" => "required|numeric|unique:providers,contact_number,".$this->get('provider_id'),
            "profile_image" => "required|image|max:250",
            "bank_name" => "required_with:account_number|required_with:payment_email|required_with:bank_location|required_with:holder_name|required_with:bic_swift_code",
            "account_number" => "required_with:bank_name",
            "payment_email" => "required_with:bank_name",
            "bank_location" => "required_with:bank_name",
            "holder_name" => "required_with:bank_name",
            "bic_swift_code" => "required_with:bank_name",
        ];
        if (!empty($this->get('id'))) {
            unset($rules['store_banner']);
            unset($rules['profile_image']);
            unset($rules['password']);
            //unset($rules['contact_number']);
            //unset($rules['full_number']);
            //unset($rules['contact_numbers']);
            unset($rules['confirm_password']);
        }
        return $rules;
    }

    public function messages()
    {
        return [
            //'contact_numbers.required' => 'please enter valid contact number!',
            'full_number.required' => 'please enter valid contact number!',
            'full_number.unique' => 'The contact number has already been taken!',
            'first_name.required' => 'Please enter full name!',
        ];
    }
}
