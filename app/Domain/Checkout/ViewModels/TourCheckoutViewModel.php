<?php

namespace App\Domain\Checkout\ViewModels;

use App\Models\Guiding;
use App\Models\User;
use App\Presenters\Offers\TourCardPresenter;
use App\Services\Checkout\BlockedDateRanges;
use App\Services\Checkout\TourCheckoutPricing;
use Carbon\CarbonImmutable;

/**
 * Page data for the tour (guiding) checkout: the server-rendered product summary plus the
 * JSON config the Alpine component boots from. Everything the page needs is embedded here,
 * so the checkout renders without follow-up API calls.
 *
 * The reschedule page (declined request → new date) reuses it via forReschedule(): the
 * calendar is limited to the guide's suggested dates and the contact details are locked.
 */
final class TourCheckoutViewModel
{
    public const DEFAULT_COUNTRY_CODE = '+49';

    private readonly TourCheckoutPricing $pricing;

    /** @var list<array{from: string, due: string}> */
    private readonly array $blockedRanges;

    private readonly string $minDate;

    public function __construct(
        private readonly Guiding $guiding,
        private readonly TourCardPresenter $presenter,
        private readonly ?User $user = null,
        private readonly ?int $requestedPersons = null,
        private readonly ?string $requestedDate = null,
        /** @var list<string>|null Only these dates are selectable; null = any free date. */
        private readonly ?array $allowedDates = null,
        /** @var array{name: string, email: string, phone: string}|null Masked, read-only contact. */
        private readonly ?array $lockedContact = null,
        /** @var list<int> */
        private readonly array $initialExtras = [],
        private readonly ?string $submitUrl = null,
    ) {
        $this->pricing = TourCheckoutPricing::for($guiding);
        $this->blockedRanges = BlockedDateRanges::merge($guiding->getBlockedEvents());
        // Submission validates `after:today`, so the first bookable day is tomorrow.
        $this->minDate = CarbonImmutable::tomorrow()->format('Y-m-d');
    }

    /**
     * @param  list<string>|null  $allowedDates
     * @param  array{name: string, email: string, phone: string}  $lockedContact
     * @param  list<int>  $initialExtras
     */
    public static function forReschedule(
        Guiding $guiding,
        TourCardPresenter $presenter,
        int $persons,
        ?string $preferredDate,
        ?array $allowedDates,
        array $lockedContact,
        array $initialExtras,
    ): self {
        return new self(
            $guiding,
            $presenter,
            null,
            $persons,
            $preferredDate,
            $allowedDates,
            $lockedContact,
            $initialExtras,
            route('booking.reschedule.store'),
        );
    }

    public function isReschedule(): bool
    {
        return $this->lockedContact !== null;
    }

    /**
     * @return array{name: string, email: string, phone: string}|null
     */
    public function lockedContact(): ?array
    {
        return $this->lockedContact;
    }

    /**
     * @return array{title: string, image: string, location: string, duration: string|null, url: string}
     */
    public function product(): array
    {
        return $this->presenter->presentCheckoutSummary($this->guiding);
    }

    /**
     * Payment methods the guide accepts, as translation-ready keys.
     *
     * @return list<string> Subset of cash|transfer|paypal
     */
    public function paymentMethods(): array
    {
        $guide = $this->guiding->user;
        if ($guide === null) {
            return [];
        }

        return array_keys(array_filter([
            'cash' => (bool) $guide->bar_allowed,
            'transfer' => (bool) $guide->banktransfer_allowed,
            'paypal' => (bool) $guide->paypal_allowed,
        ]));
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

    /**
     * @return list<array{index: int, name: string, price: float}>
     */
    public function extras(): array
    {
        return $this->pricing->extras();
    }

    public function maxGuests(): int
    {
        return $this->pricing->maxGuests();
    }

    public function persons(): int
    {
        return max(1, min($this->pricing->maxGuests(), (int) ($this->requestedPersons ?: 1)));
    }

    /**
     * The date carried over from the product page (or the reschedule email link), kept only
     * while it is still bookable. With suggested dates, falls back to the first of them so the
     * visitor lands on a ready-to-send request.
     */
    public function selectedDate(): ?string
    {
        if ($this->isBookable($this->requestedDate)) {
            return $this->requestedDate;
        }

        foreach ($this->allowedDates ?? [] as $date) {
            if ($this->isBookable($date)) {
                return $date;
            }
        }

        return null;
    }

    private function isBookable(?string $date): bool
    {
        if (! is_string($date) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) || $date < $this->minDate) {
            return false;
        }

        if ($this->allowedDates !== null && ! in_array($date, $this->allowedDates, true)) {
            return false;
        }

        foreach ($this->blockedRanges as $range) {
            if ($date >= $range['from'] && $date <= $range['due']) {
                return false;
            }
        }

        return true;
    }

    /**
     * Boot config for resources/js/checkout/tour-checkout.js. Rendered with @json, which
     * hex-escapes <, >, &, ' and " so values cannot break out of the script tag.
     *
     * @return array<string, mixed>
     */
    public function clientConfig(): array
    {
        $countryCode = (string) ($this->user?->phone_country_code ?: self::DEFAULT_COUNTRY_CODE);

        return [
            'guidingId' => $this->guiding->id,
            'persons' => $this->persons(),
            'maxGuests' => $this->pricing->maxGuests(),
            'selectedDate' => $this->selectedDate(),
            'minDate' => $this->minDate,
            'blocked' => $this->blockedRanges,
            'allowedDates' => $this->allowedDates,
            'contactLocked' => $this->isReschedule(),
            'initialExtras' => array_values(array_intersect(
                array_map('intval', $this->initialExtras),
                array_column($this->pricing->extras(), 'index'),
            )),
            'locale' => app()->getLocale(),
            'pricing' => [
                'perPerson' => $this->pricing->isPerPerson(),
                'table' => $this->pricing->priceTable(),
                'extras' => $this->pricing->extras(),
            ],
            // A locked (reschedule) contact is never sent to the browser in clear text.
            'contact' => $this->isReschedule() ? null : [
                'firstName' => (string) ($this->user?->firstname ?? ''),
                'lastName' => (string) ($this->user?->lastname ?? ''),
                'email' => (string) ($this->user?->email ?? ''),
                'countryCode' => array_key_exists($countryCode, $this->countryCodes()) ? $countryCode : self::DEFAULT_COUNTRY_CODE,
                'phone' => (string) ($this->user?->phone ?? ''),
            ],
            'submitUrl' => $this->submitUrl ?? route('checkout.store'),
            'i18n' => $this->translations(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function translations(): array
    {
        return [
            'months' => array_map(fn (string $month) => __('checkout.calendar_'.$month), [
                'january', 'february', 'march', 'april', 'may', 'june',
                'july', 'august', 'september', 'october', 'november', 'december',
            ]),
            'person' => __('checkout.tour.person'),
            'persons' => __('checkout.tour.persons'),
            'participants' => __('checkout.tour.participants_count'),
            'perPersonLine' => __('checkout.tour.per_person_line'),
            'fixedLine' => __('checkout.tour.fixed_price_line'),
            'extraLine' => __('checkout.tour.extra_line'),
            'noDate' => __('checkout.tour.no_date_selected'),
            'errors' => [
                'firstName' => __('checkout.tour.errors.first_name_required'),
                'lastName' => __('checkout.tour.errors.last_name_required'),
                'emailRequired' => __('checkout.tour.errors.email_required'),
                'emailInvalid' => __('checkout.tour.errors.email_invalid'),
                'phoneRequired' => __('checkout.tour.errors.phone_required'),
                'phoneInvalid' => __('checkout.tour.errors.phone_invalid'),
                'date' => __('checkout.tour.errors.date_required'),
                'captcha' => __('checkout.tour.errors.captcha_required'),
                'tooManyRequests' => __('checkout.too_many_requests'),
                'unexpected' => __('checkout.unexpected_error'),
            ],
        ];
    }
}
