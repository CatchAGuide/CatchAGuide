<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
            'firstname' => ['required', 'string'],
            'image' => ['image:jpeg,png,jpg,gif,svg|max:2048'],
            'lastname'=> ['required', 'string'],
            'phone' => ['string', 'required'],
            'bar_allowed' => ['required', 'boolean', 'nullable'],
            'banktransfer_allowed' => ['required', 'boolean', 'nullable'],
            'paypal_allowed' => ['required', 'boolean', 'nullable'],
            'banktransferdetails' => ['string', 'nullable'],
            'paypaldetails' => ['string', 'nullable'],
            'information.birthday' => ['nullable', 'date'],
            'information.address' =>['nullable', 'string'],
            'information.address_number' =>['nullable', 'string'],
            'information.postal' =>['nullable', 'string'],
            'information.city' =>['nullable', 'string'],
            'information.country' =>['nullable', 'string'],
            'information.about_me' =>['nullable', 'string'],
            'information.languages' =>['nullable', 'string'],
            'information.favorite_fish' =>['nullable', 'string'],
            'information.fishing_start_year' =>['nullable', 'integer'],
            'information.request_as_guide' =>['nullable', 'boolean'],
            'information.tax_id' =>['nullable'],
        ];
    }
}
