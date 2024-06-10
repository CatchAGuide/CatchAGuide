<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest as BaseFormRequest;

class FormRequest extends BaseFormRequest
{
    public function rules()
    {
        return [
            'name'=>'required|min:5',
            'manufacturer'=>'required|min:2',
            'model'=>'required|min:1',
            'max_person'=>'required|integer|min:1',
            'description'=>'required|max:1000|min:30',
            'files.*' => ['file'],
            'check' => ['array'],
            'price' =>'required|integer|min:1',
            'mileage' =>'required|integer|min:1',
            'power' =>'required|integer|min:1',
            'fuel_type' =>'required|min:3',
            'gearbox' =>'required|min:3',
            'emission_class' =>'required|integer|min:1',
            'eco_badge' =>'required|integer|min:1',
            'first_registration' =>'required|date|min:1',
            'vehicle_owners' =>'required|integer|min:1',
            'total_weight' =>'required|integer|min:1',
            'main_exam' =>'required|date|min:1',
            'sleeping_places' =>'required|integer|min:1',
            'length' =>'required|integer|min:1',
            'width' =>'required|integer|min:1',
            'heigth' =>'required|integer|min:1',
            'seats' => 'required',
            'bed_alcove' => 'required',
            'rear_sleeping_places' => 'required',
            'dinette_sleeping_places' => 'required',
            'lift_bed'=> 'required',
            'heating' => 'required',
            'fresh_water_tank' => 'required',
            'waste_water_tank' =>'required',
            'rear_garage' => 'required',
            'lend' => 'required'
        ];
    }

    public function authorize()
    {
        return true;
    }
}
