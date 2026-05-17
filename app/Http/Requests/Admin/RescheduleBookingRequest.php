<?php

namespace App\Http\Requests\Admin;

use App\Services\BookingService;
use Illuminate\Foundation\Http\FormRequest;

class RescheduleBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'selected_date' => ['required', 'date', 'after_or_equal:today'],
            'send_emails' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $booking = $this->route('booking');

            if (!$booking || !in_array($booking->status, BookingService::adminReschedulableStatuses(), true)) {
                $validator->errors()->add('booking', 'This booking cannot be rescheduled in its current status.');
            }
        });
    }
}
