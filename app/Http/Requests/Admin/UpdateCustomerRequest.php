<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'firstname' => ['sometimes', 'required', 'string'],
            'lastname' => ['sometimes', 'required', 'string'],
            'email' => ['sometimes', 'required', 'email', Rule::unique('users', 'email')->ignore($this->route('customer'))],
            'language' => ['sometimes', 'required', 'string'],
            'tax_id' => ['sometimes', 'nullable', 'string'],
            'profile_image' => ['sometimes', 'nullable', 'image', 'max:2048'],
            'information' => ['sometimes', 'array'],
            'information.birthday' => ['sometimes', 'nullable', 'date'],
            'information.address' => ['sometimes', 'required', 'string'],
            'information.address_number' => ['sometimes', 'required', 'string'],
            'information.postal' => ['sometimes', 'required', 'string'],
            'information.city' => ['sometimes', 'required', 'string'],
            'information.phone' => ['sometimes', 'required', 'string'],
            'information.languages' => ['sometimes', 'nullable', 'string'],
            'information.about_me' => ['sometimes', 'nullable', 'string'],
            'information.favorite_fish' => ['sometimes', 'nullable', 'string'],
            'information.fishing_start_year' => ['sometimes', 'nullable', 'string'],
            'banktransferdetails' => ['sometimes', 'nullable', 'string'],
            'paypaldetails' => ['sometimes', 'nullable', 'string'],
            'bar_allowed' => ['sometimes', 'boolean'],
            'banktransfer_allowed' => ['sometimes', 'boolean'],
            'paypal_allowed' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (!$this->has('information') || !is_array($this->input('information'))) {
            return;
        }

        $information = $this->input('information');

        if (array_key_exists('birthday', $information) && $information['birthday'] === '') {
            $information['birthday'] = null;
        }

        if (array_key_exists('fishing_experience', $information)) {
            $information['fishing_start_year'] = $information['fishing_experience'] !== ''
                ? $information['fishing_experience']
                : null;
            unset($information['fishing_experience']);
        }

        $this->merge(['information' => $information]);
    }

    public function authorize(): bool
    {
        return true;
    }
}
