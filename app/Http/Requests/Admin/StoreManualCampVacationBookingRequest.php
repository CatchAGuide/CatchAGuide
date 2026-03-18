<?php

namespace App\Http\Requests\Admin;

use App\Models\CampVacationBooking;
use Illuminate\Foundation\Http\FormRequest;

class StoreManualCampVacationBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth('employees')->check();
    }

    public function rules(): array
    {
        return [
            'source_type' => ['required', 'string', 'in:' . implode(',', [
                CampVacationBooking::SOURCE_CAMP,
            ])],
            'source_id' => ['required', 'integer', 'min:1'],

            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone_country_code' => ['nullable', 'string', 'max:10'],
            'phone' => ['nullable', 'string', 'max:30'],

            'preferred_date' => ['required', 'date'],
            'number_of_persons' => ['required', 'integer', 'min:1', 'max:99'],
            'message' => ['nullable', 'string'],

            'status' => ['nullable', 'string', 'in:' . implode(',', [
                CampVacationBooking::STATUS_OPEN,
                CampVacationBooking::STATUS_IN_PROCESS,
                CampVacationBooking::STATUS_DONE,
            ])],
        ];
    }
}

