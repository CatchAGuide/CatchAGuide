<?php

namespace App\Domain\Checkout\ViewModels;

use App\Models\Camp;
use App\Models\User;
use App\Services\Checkout\Camp\CampCheckoutPricing;
use Carbon\CarbonImmutable;

/**
 * Page data for the camp checkout (pages/camp-checkout): the server-rendered header and
 * sidebar summary plus the JSON config the `campCheckout` Alpine component boots from.
 * Values carried over from the camp page (arrival date, nights, guests, accommodation) are
 * accepted only when they are valid for this camp.
 */
final class CampCheckoutViewModel
{
    private readonly CampCheckoutPricing $pricing;

    private readonly string $minDate;

    private readonly string $maxDate;

    /**
     * @param  array{date?: mixed, nights?: mixed, persons?: mixed, accommodation?: mixed}  $requested
     */
    public function __construct(
        private readonly Camp $camp,
        private readonly ?User $user = null,
        private readonly array $requested = [],
    ) {
        $this->pricing = CampCheckoutPricing::for($camp);
        // Submission validates `after:today` and `before:+2 years`.
        $this->minDate = CarbonImmutable::tomorrow()->toDateString();
        $this->maxDate = CarbonImmutable::today()->addYears(2)->subDay()->toDateString();
    }

    /**
     * @return array{title: string, location: string, image: ?string, url: string}
     */
    public function product(): array
    {
        $place = array_values(array_unique(array_filter(
            [trim((string) $this->camp->city), trim((string) $this->camp->country)],
            fn (string $part) => $part !== '',
        )));

        return [
            'title' => (string) $this->camp->title,
            'location' => $place !== [] ? implode(' · ', $place) : trim((string) $this->camp->location),
            'image' => $this->camp->thumbnail_path ? media_url($this->camp->thumbnail_path) : null,
            'url' => route('vacations.camps.show', $this->camp->slug),
        ];
    }

    public function pricing(): CampCheckoutPricing
    {
        return $this->pricing;
    }

    public function isLoggedIn(): bool
    {
        return $this->user !== null;
    }

    /**
     * @return array<string, string>
     */
    public function countryCodes(): array
    {
        return (array) config('phone_country_codes', []);
    }

    public function minDate(): string
    {
        return $this->minDate;
    }

    public function maxDate(): string
    {
        return $this->maxDate;
    }

    public function arrivalDate(): ?string
    {
        $date = $this->requested['date'] ?? null;

        return is_string($date)
            && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)
            && $date >= $this->minDate
            && $date <= $this->maxDate ? $date : null;
    }

    public function persons(): int
    {
        return $this->clamp($this->requested['persons'] ?? null, 1, CampCheckoutPricing::MAX_PERSONS, 2);
    }

    /**
     * The requested accommodation, else the first one that fits the party, else the first one.
     */
    public function accommodationId(): ?int
    {
        $units = $this->pricing->accommodations();
        $requested = filter_var($this->requested['accommodation'] ?? null, FILTER_VALIDATE_INT);

        if ($requested !== false && isset($units[$requested])) {
            return $requested;
        }

        foreach ($units as $id => $unit) {
            if ($unit['capacity'] >= $this->persons()) {
                return $id;
            }
        }

        return array_key_first($units);
    }

    public function nights(): int
    {
        $min = $this->pricing->minNights($this->accommodationId());

        return $this->clamp($this->requested['nights'] ?? null, $min, CampCheckoutPricing::MAX_NIGHTS, max(3, $min));
    }

    /**
     * Boot config for resources/js/checkout/camp-checkout.js. Rendered with @json, which
     * hex-escapes <, >, &, ' and " so values cannot break out of the script tag.
     *
     * @return array<string, mixed>
     */
    public function clientConfig(): array
    {
        $countryCode = (string) ($this->user?->phone_country_code ?: TourCheckoutViewModel::DEFAULT_COUNTRY_CODE);

        return [
            'locale' => app()->getLocale(),
            'currency' => CampCheckoutPricing::CURRENCY,
            'submitUrl' => route('checkout.camp.store', $this->camp->slug),
            'arrivalDate' => $this->arrivalDate(),
            'nights' => $this->nights(),
            'persons' => $this->persons(),
            'maxNights' => CampCheckoutPricing::MAX_NIGHTS,
            'maxPersons' => CampCheckoutPricing::MAX_PERSONS,
            'accommodationId' => $this->accommodationId(),
            'pricing' => $this->pricing->clientConfig(),
            'contact' => [
                'firstName' => (string) ($this->user?->firstname ?? ''),
                'lastName' => (string) ($this->user?->lastname ?? ''),
                'email' => (string) ($this->user?->email ?? ''),
                'countryCode' => array_key_exists($countryCode, $this->countryCodes()) ? $countryCode : TourCheckoutViewModel::DEFAULT_COUNTRY_CODE,
                'phone' => (string) ($this->user?->phone ?? ''),
            ],
            'i18n' => $this->translations(),
        ];
    }

    private function clamp(mixed $value, int $min, int $max, int $default): int
    {
        $value = filter_var($value, FILTER_VALIDATE_INT);

        return max($min, min($max, $value === false ? $default : $value));
    }

    /**
     * @return array<string, mixed>
     */
    private function translations(): array
    {
        $keys = [
            'unitOption' => 'unit_option', 'boatOption' => 'boat_option', 'tourOption' => 'tour_option',
            'specialOption' => 'special_option', 'optionOnRequest' => 'option_on_request',
            'specialOnRequest' => 'special_on_request', 'nightsCount' => 'nights_count', 'daysCount' => 'days_count',
            'personsCount' => 'persons_count', 'lineAccommodation' => 'line_accommodation', 'lineBoat' => 'line_boat',
            'lineTour' => 'line_tour', 'lineRate' => 'line_rate', 'approx' => 'approx', 'onRequest' => 'on_request',
            'overCapacity' => 'over_capacity', 'minNights' => 'min_nights',
        ];

        return array_map(fn (string $key) => __('checkout.camp.'.$key), $keys) + [
            'errors' => [
                'firstName' => __('checkout.tour.errors.first_name_required'),
                'lastName' => __('checkout.tour.errors.last_name_required'),
                'emailRequired' => __('checkout.tour.errors.email_required'),
                'emailInvalid' => __('checkout.tour.errors.email_invalid'),
                'phoneRequired' => __('checkout.tour.errors.phone_required'),
                'phoneInvalid' => __('checkout.tour.errors.phone_invalid'),
                'date' => __('checkout.camp.errors.date_required'),
                'accommodation' => __('checkout.camp.errors.accommodation_required'),
                'captcha' => __('checkout.tour.errors.captcha_required'),
                'tooManyRequests' => __('checkout.too_many_requests'),
                'unexpected' => __('checkout.unexpected_error'),
            ],
        ];
    }
}
