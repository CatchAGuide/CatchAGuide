<?php

namespace App\Domain\Checkout\ViewModels;

use App\Models\Guiding;
use App\Models\User;
use App\Presenters\Offers\TourCardPresenter;
use App\Presenters\Voucher\VoucherOrderPresenter;
use App\Services\Checkout\TourCheckoutPricing;

/**
 * Page data for the gift voucher checkout: the tour checkout without the calendar and extras,
 * plus recipient / sender / message fields. Reuses the tour checkout partials, so it exposes
 * the same methods they call (product, persons, maxGuests, extras, contact helpers).
 */
final class VoucherCheckoutViewModel
{
    public const RECIPIENTS_MAX = 100;

    public const SENDER_MAX = 100;

    /** The standard voucher PDF prints the message in at most three lines at this length. */
    public const MESSAGE_MAX = 100;

    public const MESSAGE_MAX_LINES = 3;

    private readonly TourCheckoutPricing $pricing;

    public function __construct(
        private readonly Guiding $guiding,
        private readonly TourCardPresenter $presenter,
        private readonly ?User $user = null,
        private readonly ?int $requestedPersons = null,
    ) {
        $this->pricing = TourCheckoutPricing::for($guiding);
    }

    /**
     * @return array{title: string, image: string, location: string, duration: string|null, url: string}
     */
    public function product(): array
    {
        return $this->presenter->presentCheckoutSummary($this->guiding);
    }

    /** First name only on the checkout; the full name / company stays on the voucher itself. */
    public function guideName(): string
    {
        $firstName = trim((string) ($this->guiding->user?->firstname ?? ''));

        return $firstName !== '' ? $firstName : app(VoucherOrderPresenter::class)->guideName($this->guiding->user);
    }

    public function isReschedule(): bool
    {
        return false;
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
     * Vouchers cover the tour price only; extras are arranged with the guide when redeeming.
     *
     * @return list<array{index: int, name: string, price: float}>
     */
    public function extras(): array
    {
        return [];
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
     * Boot config for resources/js/checkout/voucher-checkout.js (rendered with @json).
     *
     * @return array<string, mixed>
     */
    public function clientConfig(): array
    {
        $countryCode = (string) ($this->user?->phone_country_code ?: TourCheckoutViewModel::DEFAULT_COUNTRY_CODE);
        $buyerName = trim((string) ($this->user?->firstname ?? ''));

        return [
            'persons' => $this->persons(),
            'maxGuests' => $this->pricing->maxGuests(),
            'locale' => app()->getLocale(),
            'pricing' => [
                'perPerson' => $this->pricing->isPerPerson(),
                'table' => $this->pricing->priceTable(),
                'extras' => [],
            ],
            'contact' => [
                'firstName' => $buyerName,
                'lastName' => (string) ($this->user?->lastname ?? ''),
                'email' => (string) ($this->user?->email ?? ''),
                'countryCode' => array_key_exists($countryCode, $this->countryCodes()) ? $countryCode : TourCheckoutViewModel::DEFAULT_COUNTRY_CODE,
                'phone' => (string) ($this->user?->phone ?? ''),
            ],
            'voucher' => [
                'recipients' => '',
                'sender' => $buyerName,
                'message' => '',
            ],
            'limits' => [
                'recipients' => self::RECIPIENTS_MAX,
                'sender' => self::SENDER_MAX,
                'message' => self::MESSAGE_MAX,
            ],
            'submitUrl' => route('checkout.voucher.store', ['slug' => $this->guiding->slug]),
            'i18n' => [
                'person' => __('checkout.tour.person'),
                'persons' => __('checkout.tour.persons'),
                'participants' => __('checkout.tour.participants_count'),
                'perPersonLine' => __('checkout.tour.per_person_line'),
                'fixedLine' => __('checkout.tour.fixed_price_line'),
                'errors' => [
                    'recipients' => __('checkout.voucher.errors.recipients_required'),
                    'firstName' => __('checkout.tour.errors.first_name_required'),
                    'lastName' => __('checkout.tour.errors.last_name_required'),
                    'emailRequired' => __('checkout.tour.errors.email_required'),
                    'emailInvalid' => __('checkout.tour.errors.email_invalid'),
                    'phoneRequired' => __('checkout.tour.errors.phone_required'),
                    'phoneInvalid' => __('checkout.tour.errors.phone_invalid'),
                    'captcha' => __('checkout.tour.errors.captcha_required'),
                    'tooManyRequests' => __('checkout.too_many_requests'),
                    'unexpected' => __('checkout.unexpected_error'),
                ],
            ],
        ];
    }
}
