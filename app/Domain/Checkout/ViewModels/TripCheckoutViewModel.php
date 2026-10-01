<?php

namespace App\Domain\Checkout\ViewModels;

use App\Models\Trip;
use App\Models\User;
use App\Presenters\Vacation\TripQuotePresenter;
use App\Services\Checkout\Trip\TripCheckoutOffer;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;

/**
 * Page data for the trip checkout (pages/trip-checkout): the server-rendered header and sidebar
 * plus the JSON config the `tripCheckout` Alpine component boots from. The departure and party
 * size carried over from the trip page are accepted only when they are valid for this trip.
 */
final class TripCheckoutViewModel
{
    private readonly TripCheckoutOffer $offer;

    private readonly TripQuotePresenter $presenter;

    private readonly string $minDate;

    private readonly string $maxDate;

    /** @var list<array{date: string, label: string, short: string, spots: ?int, spotsLabel: string}>|null */
    private ?array $departures = null;

    /**
     * @param  array{date?: mixed, persons?: mixed}  $requested
     */
    public function __construct(
        private readonly Trip $trip,
        private readonly ?User $user = null,
        private readonly array $requested = [],
    ) {
        $this->offer = TripCheckoutOffer::for($trip);
        $this->presenter = app(TripQuotePresenter::class);
        // Same window TripCheckoutRequest accepts: `after:today` and `before:+2 years`.
        $this->minDate = CarbonImmutable::tomorrow()->toDateString();
        $this->maxDate = CarbonImmutable::today()->addYears(2)->subDay()->toDateString();
    }

    /**
     * @return array{title: string, subtitle: string, image: ?string, url: string}
     */
    public function product(): array
    {
        $parts = array_values(array_unique(array_filter(
            [Str::ucfirst(trim((string) $this->trip->region)), Str::ucfirst(trim((string) $this->trip->country)), $this->duration()],
            fn (string $part) => $part !== '',
        )));

        return [
            'title' => (string) $this->trip->title,
            'subtitle' => implode(' · ', $parts),
            'image' => $this->trip->thumbnail_path ? media_url($this->trip->thumbnail_path) : null,
            'url' => route('vacations.trips.show', $this->trip->slug),
        ];
    }

    public function usesFixedDates(): bool
    {
        return $this->offer->usesFixedDates();
    }

    public function duration(): string
    {
        return $this->presenter->duration($this->offer->durationDays(), $this->offer->durationNights());
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

    /**
     * The departure from the trip page, when it is still open for requests.
     */
    public function departureDate(): ?string
    {
        $date = $this->requested['date'] ?? null;

        return is_string($date) && $this->offer->departure($date) !== null ? $date : null;
    }

    public function persons(): int
    {
        $value = filter_var($this->requested['persons'] ?? null, FILTER_VALIDATE_INT);

        return max(1, min($this->maxPersons(), $value === false ? 2 : $value));
    }

    public function maxPersons(): int
    {
        return $this->offer->maxPersons();
    }

    /**
     * Departures with their display labels, for the date dropdown.
     *
     * @return list<array{date: string, label: string, short: string, spots: ?int, spotsLabel: string}>
     */
    public function departures(): array
    {
        return $this->departures ??= array_values(array_map(fn (array $departure) => [
            'date' => $departure['date'],
            'label' => $this->presenter->range($departure['date'], $departure['end']),
            'short' => $this->presenter->range($departure['date'], $departure['end'], withYear: false),
            'spots' => $departure['spots'],
            'spotsLabel' => $this->presenter->spots($departure['spots'], TripCheckoutOffer::FEW_SPOTS),
        ], $this->offer->departures()));
    }

    /**
     * Boot config for resources/js/checkout/trip-checkout.js. Rendered with @json, which
     * hex-escapes <, >, &, ' and " so values cannot break out of the script tag.
     *
     * @return array<string, mixed>
     */
    public function clientConfig(): array
    {
        $countryCode = (string) ($this->user?->phone_country_code ?: TourCheckoutViewModel::DEFAULT_COUNTRY_CODE);

        return [
            'locale' => app()->getLocale(),
            'currency' => TripCheckoutOffer::CURRENCY,
            'submitUrl' => route('checkout.trip.store', $this->trip->slug),
            'fixedDates' => $this->usesFixedDates(),
            'departures' => $this->departures(),
            'departureDate' => $this->departureDate(),
            'persons' => $this->persons(),
            'maxPersons' => $this->maxPersons(),
            'pricePerPerson' => $this->offer->pricePerPerson(),
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

    /**
     * @return array<string, mixed>
     */
    private function translations(): array
    {
        $keys = [
            'personsCount' => 'persons_count', 'line' => 'line', 'lineFrom' => 'line_from',
            'approx' => 'approx', 'from' => 'from', 'onRequest' => 'on_request',
            'noteFixed' => 'note_fixed', 'noteRequest' => 'note_request', 'chooseDate' => 'choose_date',
            'noDate' => 'no_date', 'wish' => 'wish', 'wishRange' => 'wish_range', 'wishOpen' => 'wish_open',
            'wishFrom' => 'wish_from', 'capacity' => 'capacity',
        ];

        return array_map(fn (string $key) => __('checkout.trip.'.$key), $keys) + [
            'errors' => [
                'firstName' => __('checkout.tour.errors.first_name_required'),
                'lastName' => __('checkout.tour.errors.last_name_required'),
                'emailRequired' => __('checkout.tour.errors.email_required'),
                'emailInvalid' => __('checkout.tour.errors.email_invalid'),
                'phoneRequired' => __('checkout.tour.errors.phone_required'),
                'phoneInvalid' => __('checkout.tour.errors.phone_invalid'),
                'date' => __('checkout.trip.errors.date_required'),
                'wishRequired' => __('checkout.trip.errors.wish_required'),
                'wishOrder' => __('checkout.trip.errors.wish_order'),
                'captcha' => __('checkout.tour.errors.captcha_required'),
                'tooManyRequests' => __('checkout.too_many_requests'),
                'unexpected' => __('checkout.unexpected_error'),
            ],
        ];
    }
}
