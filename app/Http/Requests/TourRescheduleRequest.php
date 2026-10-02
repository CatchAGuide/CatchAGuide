<?php

namespace App\Http\Requests;

use App\Services\Booking\Reschedule\RescheduleOffer;
use App\Services\Booking\Reschedule\RescheduleOfferResolver;
use App\Services\Booking\Reschedule\RescheduleSession;
use App\Services\Checkout\TourCheckoutPricing;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * New-date request for a declined tour booking. The booking comes from the reschedule token
 * held in the session (never from the request body). The guest count, date, extra positions
 * and contact details are client input — the price is re-quoted on the server.
 */
class TourRescheduleRequest extends FormRequest
{
    private const PHONE_PATTERN = '/^[0-9][0-9 ()\/.-]{2,24}$/';

    private ?RescheduleOffer $offer = null;

    private ?TourCheckoutPricing $pricing = null;

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
            'selected_date' => ['required', 'date_format:Y-m-d', 'after:today'],
            'persons' => ['required', 'integer', 'min:1', 'max:'.($this->pricing()?->maxGuests() ?? 1)],
            'extras' => ['sometimes', 'array', 'max:50'],
            'extras.*' => ['integer', 'min:0', 'distinct'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'country_code' => ['required', 'string', Rule::in(array_keys((array) config('phone_country_codes', [])))],
            'phone' => ['required', 'string', 'regex:'.self::PHONE_PATTERN],
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->has('selected_date') || ! $this->offer()->isAvailable()) {
                    return;
                }

                if (! $this->offer()->allowsDate((string) $this->input('selected_date'))) {
                    $validator->errors()->add('selected_date', __('checkout.reschedule.errors.date_not_offered'));
                }

                $known = array_column($this->pricing()->extras(), 'index');
                if (array_diff($this->extraIndexes(), $known) !== []) {
                    $validator->errors()->add('extras', __('checkout.tour.errors.extras_invalid'));
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
            'selected_date.required' => __('checkout.tour.errors.date_required'),
            'selected_date.after' => __('checkout.reschedule.errors.date_not_offered'),
            'first_name.required' => __('checkout.tour.errors.first_name_required'),
            'last_name.required' => __('checkout.tour.errors.last_name_required'),
            'email.required' => __('checkout.tour.errors.email_required'),
            'email.email' => __('checkout.tour.errors.email_invalid'),
            'phone.required' => __('checkout.tour.errors.phone_required'),
            'phone.regex' => __('checkout.tour.errors.phone_invalid'),
        ];
    }

    /**
     * @return array{first_name: string, last_name: string, email: string, country_code: string, phone: string}
     */
    public function contact(): array
    {
        return [
            'first_name' => trim((string) $this->validated('first_name')),
            'last_name' => trim((string) $this->validated('last_name')),
            'email' => trim((string) $this->validated('email')),
            'country_code' => (string) $this->validated('country_code'),
            'phone' => trim((string) $this->validated('phone')),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(array_map(
            fn ($value) => is_string($value) ? trim($value) : $value,
            $this->only(['first_name', 'last_name', 'email', 'phone'])
        ));
    }

    public function offer(): RescheduleOffer
    {
        return $this->offer ??= app(RescheduleOfferResolver::class)->resolve(app(RescheduleSession::class)->token());
    }

    public function pricing(): ?TourCheckoutPricing
    {
        if ($this->pricing === null && $this->offer()->isAvailable()) {
            $this->pricing = TourCheckoutPricing::for($this->offer()->booking->guiding);
        }

        return $this->pricing;
    }

    /**
     * @return list<int>
     */
    public function extraIndexes(): array
    {
        return array_values(array_map('intval', (array) $this->input('extras', [])));
    }
}
