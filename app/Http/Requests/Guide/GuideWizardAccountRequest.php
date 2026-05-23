<?php

namespace App\Http\Requests\Guide;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class GuideWizardAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::defaults()],
            'terms' => 'accepted',
            'privacy' => 'accepted',
            'g-recaptcha-response' => config('recaptcha.enabled') ? 'required' : 'nullable',
        ];
    }
}
