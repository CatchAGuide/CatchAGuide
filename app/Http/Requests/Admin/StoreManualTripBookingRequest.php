<?php

namespace App\Http\Requests\Admin;

use App\Models\TripBooking;
use Illuminate\Foundation\Http\FormRequest;

class StoreManualTripBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('employees')->check();
    }

    public function rules(): array
    {
        return [
            'trip_id' => ['required', 'integer', 'exists:trips,id'],

            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone_country_code' => ['nullable', 'string', 'max:10'],
            'phone' => ['nullable', 'string', 'max:30'],

            'preferred_date' => ['required', 'date'],
            'number_of_persons' => ['required', 'integer', 'min:1', 'max:99'],
            'message' => ['nullable', 'string'],

            'status' => ['nullable', 'string', 'in:' . implode(',', [
                TripBooking::STATUS_OPEN,
                TripBooking::STATUS_IN_PROCESS,
                TripBooking::STATUS_DONE,
            ])],
        ];
    }
}

