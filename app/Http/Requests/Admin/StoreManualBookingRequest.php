<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreManualBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('employees')->check();
    }

    public function rules(): array
    {
        return [
            'guest_name' => ['required', 'string', 'max:255'],
            'guest_email' => ['nullable', 'email', 'max:255'],
            'guest_phone' => ['nullable', 'string', 'max:255'],
            'guest_phone_country_code' => ['nullable', 'string', 'max:10'],
            'guiding_id' => ['required', 'exists:guidings,id'],
            'date' => ['required', 'date', 'after_or_equal:today'],
            'time' => ['nullable', 'date_format:H:i'],
            'number_of_guests' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
            'price_override' => ['nullable', 'numeric', 'min:0'],
            'status' => ['nullable', 'in:pending,accepted,rejected,cancelled'],
            'send_emails' => ['sometimes', 'boolean'],
        ];
    }
}

