<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGuidingRequest extends FormRequest
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
            'title' => ['request', 'string'],
            'location'=> ['request', 'string'],
            'recommended_for'=> ['request', 'string'],
            'max_guests'=> ['request', 'integer'],
            'duration'=> ['request', 'float'],
            'required_special_license'=> ['string'],
            'fishing_type'=> ['request', 'string'],
            'fishing_from'=> ['request', 'string'],
            'description'=> ['longText'],
            'required_equipment'=> [ 'string'],
            'provided_equipment'=> ['string'],
            'additional_information'=> [ 'string'],
            'price'=> ['request', 'float'],
            'price_two_persons'=> ['request', 'float'],
            'price_three_persons'=> ['request', 'float'],
        ];
    }
}
