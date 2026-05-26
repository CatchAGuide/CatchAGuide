<?php

namespace App\Http\Requests\Guide;

use App\Enums\GuideType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GuideVerificationSubmitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isCompany = $this->input('guide_type') === GuideType::COMPANY
            && config('guide_onboarding.company_onboarding_enabled');

        $rules = [
            'guide_type' => ['required', Rule::in(GuideType::all())],
            'information.address' => 'required|string|max:255',
            'information.address_number' => 'required|string|max:50',
            'information.postal' => 'required|string|max:20',
            'information.city' => 'required|string|max:255',
            'information.country' => 'nullable|string|max:3',
            'information.phone' => 'required|string|max:50',
            'information.birthday' => 'nullable|date|before_or_equal:today',
            'information.taxId' => 'nullable|string|max:100',
            'information.tax_number' => 'nullable|string|max:100',
            'lawcard' => 'accepted',
            'lawcard_nature' => 'accepted',
            'lawcard_truthful' => 'accepted',
            'email' => 'nullable|email',
            'password' => 'nullable',
        ];

        if ($isCompany) {
            $rules['information.company_name'] = 'required|string|max:255';
            $rules['information.legal_form'] = 'required|string|max:100';
            $rules['information.founded_year'] = 'required|integer|min:1800|max:' . date('Y');
            $rules['information.contact_position'] = 'required|string|max:255';
        } else {
            $rules['information.birthday'] = 'nullable|date|before_or_equal:today';
        }

        if ($this->boolean('is_fast_lane')) {
            return array_merge($rules, [
                'firstname' => 'required|string|max:255',
                'lastname' => 'required|string|max:255',
                'email' => 'required|email',
                'password' => ['required', 'string', 'min:8', 'confirmed'],
                'terms' => 'accepted',
                'privacy' => 'accepted',
            ]);
        }

        if ($this->user('web')) {
            $rules['firstname'] = 'prohibited';
            $rules['lastname'] = 'prohibited';
            $rules['email'] = 'prohibited';
            $rules['password'] = 'prohibited';
        }

        return $rules;
    }
}
