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

    public function attributes(): array
    {
        return [
            'guide_type' => __('validation.attributes.guide_type'),
            'information.address' => __('validation.attributes.information.address'),
            'information.address_number' => __('validation.attributes.information.address_number'),
            'information.postal' => __('validation.attributes.information.postal'),
            'information.city' => __('validation.attributes.information.city'),
            'information.country' => __('validation.attributes.information.country'),
            'information.phone' => __('validation.attributes.information.phone'),
            'information.birthday' => __('validation.attributes.information.birthday'),
            'information.company_name' => __('validation.attributes.information.company_name'),
            'information.legal_form' => __('validation.attributes.information.legal_form'),
            'information.taxId' => __('validation.attributes.information.taxId'),
            'information.tax_number' => __('validation.attributes.information.tax_number'),
            'lawcard' => __('validation.attributes.lawcard'),
            'lawcard_nature' => __('validation.attributes.lawcard_nature'),
            'lawcard_truthful' => __('validation.attributes.lawcard_truthful'),
            'firstname' => __('validation.attributes.firstname'),
            'lastname' => __('validation.attributes.lastname'),
            'email' => __('validation.attributes.email'),
            'password' => __('validation.attributes.password'),
            'terms' => __('validation.attributes.terms'),
            'privacy' => __('validation.attributes.privacy'),
        ];
    }

    protected function prepareForValidation(): void
    {
        $country = $this->input('information.country');
        if (is_string($country)) {
            $country = strtoupper(trim($country));
            // Prefill from older profiles may store full names ("Deutschland") — keep ISO codes only.
            if (strlen($country) > 3) {
                $country = null;
            }
            $this->merge([
                'information' => array_merge($this->input('information', []), [
                    'country' => $country,
                ]),
            ]);
        }
    }
}
