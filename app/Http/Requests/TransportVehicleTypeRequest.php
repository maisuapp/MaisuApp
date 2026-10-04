<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransportVehicleTypeRequest extends FormRequest
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
            'service_cat_id' => 'required',
            'name' => 'required|unique:transport_vehicle_type,name,' . $this->get('id'),
            'icon' => 'required|dimensions:min_width=50,min_height=50,max_width=100,max_height=100|image|max:100|unique:transport_vehicle_type,icon_name,' . $this->get('id'),
            'base_fare' => "required|numeric",
            'time_fare' => "required|numeric",
            'min_fare_amount' => "required|numeric",
            'cost_for_km' => 'required|numeric',
            'weight_limit' => 'required|numeric',
            'status' => 'required|integer',
        ];
        if (!empty($this->get('id'))) {
            $rules = [
                'icon' => 'nullable|dimensions:min_width=50,min_height=50,max_width=100,max_height=100|max:100',
            ];
        }
        return $rules;
    }

    public function messages()
    {
        return [
            'service_cat_id.required' => 'Select Vehicle Type For',
            'icon.max' => 'Icon max size 100kb',
            'icon.dimensions' => 'Icon Dimension between 50*50 to 100*100',
            'icon.unique' => 'Icon name unique',
        ];
    }
}
