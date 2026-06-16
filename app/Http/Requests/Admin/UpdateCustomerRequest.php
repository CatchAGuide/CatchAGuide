<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'firstname' => ['required', 'string'],
            'lastname' => ['required', 'string'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($this->route('customer'))],
            'language' => ['required', 'string'],
            'tax_id' => ['nullable', 'string'],
            'profile_image' => ['nullable', 'image', 'max:2048'],
            'information.birthday' => ['nullable', 'date'],
            'information.address' => ['required', 'string'],
            'information.address_number' => ['required', 'string'],
            'information.postal' => ['required', 'string'],
            'information.city' => ['required', 'string'],
            'information.phone' => ['required', 'string'],
            'information.languages' => ['nullable', 'string'],
            'information.about_me' => ['nullable', 'string'],
            'information.favorite_fish' => ['nullable', 'string'],
            'information.fishing_start_year' => ['nullable', 'string'],
            'banktransferdetails' => ['nullable', 'string'],
            'paypaldetails' => ['nullable', 'string'],
            'bar_allowed' => ['nullable', 'boolean'],
            'banktransfer_allowed' => ['nullable', 'boolean'],
            'paypal_allowed' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $information = $this->input('information', []);

        if (array_key_exists('birthday', $information) && $information['birthday'] === '') {
            $information['birthday'] = null;
        }

        if (array_key_exists('fishing_experience', $information)) {
            $information['fishing_start_year'] = $information['fishing_experience'] !== ''
                ? $information['fishing_experience']
                : null;
            unset($information['fishing_experience']);
        }

        $this->merge([
            'information' => $information,
            'bar_allowed' => $this->has('bar_allowed'),
            'banktransfer_allowed' => $this->has('banktransfer_allowed'),
            'paypal_allowed' => $this->has('paypal_allowed'),
        ]);
    }

    public function authorize(): bool
    {
        return true;
    }
}
