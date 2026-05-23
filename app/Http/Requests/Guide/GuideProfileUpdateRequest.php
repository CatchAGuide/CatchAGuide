<?php

namespace App\Http\Requests\Guide;

use Illuminate\Foundation\Http\FormRequest;

class GuideProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->canViewGuideTools();
    }

    public function rules(): array
    {
        return [
            'information.languages' => 'required|string|max:500',
            'information.about_me' => 'required|string|max:5000',
            'information.favorite_fish' => 'required|string|max:255',
            'information.fishing_start_year' => 'required|integer|min:1950|max:' . date('Y'),
            'bar_allowed' => 'nullable|boolean',
            'banktransfer_allowed' => 'nullable|boolean',
            'paypal_allowed' => 'nullable|boolean',
            'banktransferdetails' => 'required_if:banktransfer_allowed,1|nullable|string',
            'paypaldetails' => 'required_if:paypal_allowed,1|nullable|string',
            'profil_image' => 'nullable|image|max:5120',
        ];
    }
}
