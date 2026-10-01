<?php

namespace App\Http\Requests;

use App\Models\Trip;
use App\Rules\Recaptcha;
use App\Services\Checkout\Trip\BookableTripFinder;
use App\Services\Checkout\Trip\TripCheckoutOffer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

/**
 * Request submitted from the trip checkout (pages/trip-checkout). Trips with open departures
 * are requested for one of them; the others for a preferred travel window. Prices are never
 * read from the client: TripCheckoutOffer re-estimates them on the server.
 */
class TripCheckoutRequest extends FormRequest
{
    private const PHONE_PATTERN = '/^[0-9][0-9 ()\/.-]{2,24}$/';

    private Trip|false|null $trip = null;

    private ?TripCheckoutOffer $offer = null;

    /**
     * A trip that went offline (draft, deleted) while the page was open can't be requested.
     */
    public function authorize(): bool
    {
        return $this->trip() !== null;
    }

    protected function failedAuthorization(): never
    {
        // 410 so BookingClient shows the message instead of treating it as field errors.
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => __('checkout.trip.errors.trip_unavailable'),
        ], 410));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $offer = $this->offer();
        $latest = now()->addYears(2)->toDateString();

        $dates = $offer?->usesFixedDates()
            ? [
                'departure_date' => ['required', 'date_format:Y-m-d', Rule::in(array_keys($offer->departures()))],
            ]
            : [
                'wish_start' => ['required', 'date_format:Y-m-d', 'after:today', 'before:'.$latest],
                'wish_end' => ['required', 'date_format:Y-m-d', 'after_or_equal:wish_start', 'before:'.$latest],
            ];

        return $dates + [
            'persons' => ['required', 'integer', 'min:1', 'max:'.($offer?->maxPersons() ?? TripCheckoutOffer::DEFAULT_MAX_PERSONS)],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'country_code' => ['required', 'string', Rule::in(array_keys((array) config('phone_country_codes', [])))],
            'phone' => ['required', 'string', 'regex:'.self::PHONE_PATTERN],
            'message' => ['nullable', 'string', 'max:2000'],
            'g-recaptcha-response' => Recaptcha::production(invisible: true),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'departure_date.required' => __('checkout.trip.errors.date_required'),
            'departure_date.date_format' => __('checkout.trip.errors.date_required'),
            'departure_date.in' => __('checkout.trip.errors.date_invalid'),
            'wish_start.required' => __('checkout.trip.errors.wish_required'),
            'wish_end.required' => __('checkout.trip.errors.wish_required'),
            'wish_start.date_format' => __('checkout.trip.errors.wish_required'),
            'wish_end.date_format' => __('checkout.trip.errors.wish_required'),
            'wish_start.after' => __('checkout.trip.errors.wish_invalid'),
            'wish_start.before' => __('checkout.trip.errors.wish_invalid'),
            'wish_end.before' => __('checkout.trip.errors.wish_invalid'),
            'wish_end.after_or_equal' => __('checkout.trip.errors.wish_order'),
            'first_name.required' => __('checkout.tour.errors.first_name_required'),
            'last_name.required' => __('checkout.tour.errors.last_name_required'),
            'email.required' => __('checkout.tour.errors.email_required'),
            'email.email' => __('checkout.tour.errors.email_invalid'),
            'phone.required' => __('checkout.tour.errors.phone_required'),
            'phone.regex' => __('checkout.tour.errors.phone_invalid'),
        ];
    }

    /**
     * The trip from the route, or null when it isn't bookable.
     */
    public function trip(): ?Trip
    {
        if ($this->trip === null) {
            $this->trip = app(BookableTripFinder::class)->findBySlug((string) $this->route('slug')) ?? false;
        }

        return $this->trip ?: null;
    }

    public function offer(): ?TripCheckoutOffer
    {
        if ($this->offer === null && $trip = $this->trip()) {
            $this->offer = TripCheckoutOffer::for($trip);
        }

        return $this->offer;
    }

    public function persons(): int
    {
        return (int) $this->validated('persons');
    }

    public function departureDate(): ?string
    {
        return $this->validatedDate('departure_date');
    }

    public function wishStart(): ?string
    {
        return $this->validatedDate('wish_start');
    }

    public function wishEnd(): ?string
    {
        return $this->validatedDate('wish_end');
    }

    /**
     * @return array{first_name: string, last_name: string, email: string, country_code: string, phone: string}
     */
    public function contact(): array
    {
        return [
            'first_name' => (string) $this->validated('first_name'),
            'last_name' => (string) $this->validated('last_name'),
            'email' => (string) $this->validated('email'),
            'country_code' => (string) $this->validated('country_code'),
            'phone' => (string) $this->validated('phone'),
        ];
    }

    public function guestMessage(): string
    {
        return (string) ($this->validated('message') ?? '');
    }

    protected function prepareForValidation(): void
    {
        $this->merge(array_map(
            fn ($value) => is_string($value) ? trim($value) : $value,
            $this->only(['first_name', 'last_name', 'email', 'phone', 'message'])
        ));
    }

    private function validatedDate(string $key): ?string
    {
        $value = $this->validated($key);

        return is_string($value) && $value !== '' ? $value : null;
    }
}
