<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyStoreGuideRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'firstname' => 'required',
            'lastname' => 'required',
            'information.birthday' => 'nullable|date',
            'information.address' => 'required',
            'information.address_number' => 'required',
            'information.postal' => 'required',
            'information.city' => 'required',
            'information.phone' => 'required',
            'information.country' => 'nullable|string|max:3',
            'lawcard' => 'required',
            'taxId' => 'nullable',
        ];
    }
}
