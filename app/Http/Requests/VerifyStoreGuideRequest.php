<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyStoreGuideRequest extends FormRequest
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
            'firstname' => 'required',
            'lastname' => 'required',
            'information.birthday' => 'nullable',
            'information.address' => 'required',
            'information.address_number' => 'required',
            'information.postal' => 'required',
            'information.city' => 'required',
            'information.phone' => 'required',
            'information.languages' => 'required',
            'information.about_me' => 'required',
            'information.favorite_fish' => 'required',
            'information.fishing_start_year' => 'required',
            'lawcard' => 'required',
            'taxId' => 'nullable'
            ];
    }
}
