<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransportManualRideBookRequest extends FormRequest
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

//            'phone' => 'required|min:9|max:10|regex:/[0-9]{10}/|exists:users,phone,name,'.$this->get('name'),
//            'name' => 'required|exists:users,name,phone,'.$this->get('phone'),
            'vehicle_type_id' => 'required',
            'pickup_datetime' => 'required',
            'user_id' => 'required',
            'driver_id' => 'required',
            'pickup_address' => 'required',
            'destination_address' => 'nullable|Different:pickup_address',
            'service_cat_id' => 'required',
        ];
        if (!empty($this->get('id'))) {
            $rules = [
                'icon' => 'nullable|dimensions:min_width=50,min_height=50,max_width=100,max_height=100|max:100',
            ];
        }
        return $rules;
    }
}
