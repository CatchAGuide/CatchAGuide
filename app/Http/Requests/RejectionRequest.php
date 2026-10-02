<?php

namespace App\Http\Requests;

use App\Models\Booking;
use App\Services\Checkout\BlockedDateRanges;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Guide declines a booking request (emailed link /booking-reject/{token}) and suggests
 * alternative dates. The suggested dates become the only dates the customer can pick on the
 * reschedule page, so they are validated as bookable here.
 */
class RejectionRequest extends FormRequest
{
    public const MAX_DATES = 5;

    public const MIN_MESSAGE = 50;

    public const MAX_MESSAGE = 2000;

    private Booking|false|null $booking = null;

    /**
     * Authorization for the booking token happens in the controller.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'reason' => ['required', 'string', 'min:'.self::MIN_MESSAGE, 'max:'.self::MAX_MESSAGE],
            'alternative_dates' => ['required', 'array', 'min:1', 'max:'.self::MAX_DATES],
            'alternative_dates.*' => ['required', 'date_format:Y-m-d', 'after:today', 'distinct'],
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty() || ! ($booking = $this->booking()) || ! $booking->guiding) {
                    return;
                }

                $requested = substr((string) $booking->book_date, 0, 10);
                $blocked = BlockedDateRanges::merge($booking->guiding->getBlockedEvents());

                foreach ($this->alternativeDates() as $date) {
                    $taken = $date === $requested;
                    foreach ($blocked as $range) {
                        $taken = $taken || ($date >= $range['from'] && $date <= $range['due']);
                    }

                    if ($taken) {
                        $validator->errors()->add('alternative_dates', __('checkout.reject.errors.date_unavailable'));

                        return;
                    }
                }
            },
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'reason.required' => __('checkout.reject.errors.message_min', ['min' => self::MIN_MESSAGE]),
            'reason.min' => __('checkout.reject.errors.message_min', ['min' => self::MIN_MESSAGE]),
            'alternative_dates.required' => __('checkout.reject.errors.dates_required'),
            'alternative_dates.min' => __('checkout.reject.errors.dates_required'),
            'alternative_dates.max' => __('checkout.reject.errors.dates_max', ['max' => self::MAX_DATES]),
            'alternative_dates.*.after' => __('checkout.reject.errors.date_unavailable'),
        ];
    }

    public function booking(): ?Booking
    {
        if ($this->booking === null) {
            $token = (string) $this->route('token');
            $this->booking = ($token !== '' ? Booking::with('guiding')->where('token', $token)->first() : null) ?? false;
        }

        return $this->booking ?: null;
    }

    /**
     * Validated dates, sorted ascending.
     *
     * @return list<string>
     */
    public function alternativeDates(): array
    {
        $dates = array_values(array_unique(array_map('strval', (array) $this->input('alternative_dates', []))));
        sort($dates);

        return $dates;
    }

    protected function prepareForValidation(): void
    {
        // Older form versions posted the dates as a JSON string.
        $dates = $this->input('alternative_dates');
        if (is_string($dates)) {
            $decoded = json_decode($dates, true);
            $this->merge(['alternative_dates' => is_array($decoded) ? $decoded : [$dates]]);
        }

        if (is_string($this->input('reason'))) {
            $this->merge(['reason' => trim($this->input('reason'))]);
        }
    }
}
