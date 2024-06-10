<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGuideRequest extends FormRequest
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
            'lastname' => ['required', 'string'],
            'email' => ['required', 'string'],
            'information.address' => ['required', 'string'],
            'information.address_number' => ['required', 'string'],
            'information.city' => ['required', 'string'],
            'information.postal' => ['required', 'string'],
            'information.phone' => ['required', 'string'],
            'information.taxId' => ['nullable'],
        ];
    }
}
