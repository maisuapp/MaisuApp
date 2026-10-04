<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProviderStoreRequest extends FormRequest
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
            'first_name' => 'nullable',
            'last_name' => 'nullable',
            'name' => 'nullable',
            'lname' => 'nullable',
//            'email' => 'required|email:rfc,dns|unique:providers,email,' . $this->get('id'),
//            'email' => 'required|'.(($this->getHost() == "fox-jek.startuptrinity.com")? 'email|' : '' ).'unique:providers,email,' . $this->get('id'),
            'avatar' => 'nullable|image|max:250',
//            'contact_number' => 'required|unique:providers,contact_number,' . $this->get('id'),
            'full_number' => 'required|unique:providers,contact_number,' . $this->get('id'),
            'email' => [
                'required',
                Rule::unique('providers','email')->where(function($query) {
                    $query->where('email', '=', $this->get('email'));
                    $query->where('id', '!=', $this->get('id'));
                    $query->where('deleted_at', '=', null);
                })
            ],
//            'contact_numbers' => 'required|unique:providers,contact_number,' . $this->get('id'),
            'pass' => 'required|min:6|max:16',
            'confirm_password' => 'required|same:pass',
//            'service_radius' => 'required',
            'gender' => 'required',

            'vehicle_type_id' => 'required',
            'vehicle_company' => 'required',
            'model_name' => 'required',
            'model_year' => 'required',
            'plat_no' => 'required',

        ];
        if (!empty($this->get('id'))) {
            unset($rules['pass']);
            unset($rules['confirm_password']);
        }
        return $rules;
    }
    public function messages()
    {
        return [
            'full_number.required' => 'please enter valid contact number!',
            'full_number.unique' => 'The contact number has already been taken!',
        ];
    }
}
