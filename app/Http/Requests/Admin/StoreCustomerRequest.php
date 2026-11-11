<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'account_type' => ['required', Rule::in(['person', 'company'])],
            'role' => ['required', Rule::in(['customer', 'provider', 'guide'])],
            'firstname' => ['required_if:account_type,person', 'nullable', 'string', 'max:255'],
            'lastname' => ['required_if:account_type,person', 'nullable', 'string', 'max:255'],
            'company_name' => ['required_if:account_type,company', 'nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')],
            'language' => ['required', Rule::in(['de', 'en'])],
            'phone_country_code' => ['required', 'string', 'max:10'],
            'information' => ['required', 'array'],
            'information.phone' => ['required', 'string', 'max:50'],
            'information.address' => ['required', 'string', 'max:255'],
            'information.address_number' => ['required', 'string', 'max:50'],
            'information.postal' => ['required', 'string', 'max:20'],
            'information.city' => ['required', 'string', 'max:255'],
            'information.country' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'firstname.required_if' => __('admin.customers.validation.firstname_required'),
            'lastname.required_if' => __('admin.customers.validation.lastname_required'),
            'company_name.required_if' => __('admin.customers.validation.company_required'),
            'information.phone.required' => __('admin.customers.validation.phone_required'),
            'information.address.required' => __('admin.customers.validation.address_required'),
            'information.address_number.required' => __('admin.customers.validation.address_number_required'),
            'information.postal.required' => __('admin.customers.validation.postal_required'),
            'information.city.required' => __('admin.customers.validation.city_required'),
            'email.unique' => __('admin.customers.validation.email_unique'),
            'role.required' => __('admin.customers.validation.role_required'),
            'role.in' => __('admin.customers.validation.role_required'),
        ];
    }

    public function attributes(): array
    {
        return [
            'account_type' => __('admin.customers.fields.account_type'),
            'role' => __('admin.customers.fields.role'),
            'firstname' => __('admin.customers.fields.firstname'),
            'lastname' => __('admin.customers.fields.lastname'),
            'company_name' => __('admin.customers.fields.company_name'),
            'email' => __('admin.customers.fields.email'),
            'language' => __('admin.customers.fields.language'),
            'phone_country_code' => __('admin.customers.fields.phone_country_code'),
            'information.phone' => __('admin.customers.fields.phone'),
            'information.address' => __('admin.customers.fields.address'),
            'information.address_number' => __('admin.customers.fields.address_number'),
            'information.postal' => __('admin.customers.fields.postal'),
            'information.city' => __('admin.customers.fields.city'),
            'information.country' => __('admin.customers.fields.country'),
        ];
    }

    protected function prepareForValidation(): void
    {
        $email = $this->input('email');
        if (is_string($email) && trim($email) === '') {
            $this->merge(['email' => null]);
        }

        $code = trim((string) $this->input('phone_country_code', ''));
        if ($code !== '' && ! str_starts_with($code, '+')) {
            $code = '+'.$code;
        }

        $role = $this->input('role', 'provider');
        if (! in_array($role, ['customer', 'provider', 'guide'], true)) {
            $role = 'provider';
        }

        $this->merge([
            'account_type' => $this->input('account_type', 'person'),
            'role' => $role,
            'phone_country_code' => $code !== '' ? $code : '+49',
            'language' => in_array($this->input('language'), ['de', 'en'], true) ? $this->input('language') : 'de',
        ]);
    }
}
