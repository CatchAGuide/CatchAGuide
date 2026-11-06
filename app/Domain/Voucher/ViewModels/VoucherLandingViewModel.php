<?php

namespace App\Domain\Voucher\ViewModels;

use App\Models\Faq;
use App\Models\User;
use App\Models\VoucherOrder;
use App\Presenters\Voucher\VoucherOrderPresenter;
use App\Services\Voucher\VoucherLandingCatalog;
use App\Services\Voucher\VoucherRequestService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\ViewErrorBag;

/**
 * Page data for the /guidings/voucher landing page (pages/voucher-landing): live tiles, gallery,
 * reviews and admin-managed FAQs (Faq page "gift-voucher"), the request form's options and, on the
 * thank-you URL, the submitted request. The Christmas touches (eyebrow, sparkles, countdown) show
 * from 1 November until Christmas Eve.
 */
final class VoucherLandingViewModel
{
    public const FAQ_PAGE = 'gift-voucher';

    private const SEASON_START_MONTH = 11;

    private const COPY = 'guidings.voucher_landing.';

    private readonly CarbonImmutable $today;

    public function __construct(
        private readonly VoucherLandingCatalog $catalog,
        private readonly VoucherOrderPresenter $presenter,
        private readonly ?User $user = null,
        private readonly ?VoucherOrder $submitted = null,
        ?CarbonImmutable $today = null,
    ) {
        $this->today = ($today ?? CarbonImmutable::now())->startOfDay();
    }

    public function isChristmasSeason(): bool
    {
        return $this->today->month >= self::SEASON_START_MONTH && $this->daysToChristmasEve() >= 0;
    }

    public function daysToChristmasEve(): int
    {
        return (int) $this->today->diffInDays($this->today->setDate($this->today->year, 12, 24), false);
    }

    /**
     * @return list<array{country: string, region: string, image: ?string, from: ?string, species: ?string}>
     */
    public function tiles(): array
    {
        return array_map(fn (array $tile) => [
            'country' => $tile['country'],
            'region' => $tile['region'],
            'image' => $tile['image'],
            'from' => $tile['from_price'] ? __(self::COPY.'tile_from', ['price' => $this->money($tile['from_price'])]) : null,
            'species' => $tile['species'],
        ], $this->catalog->tiles());
    }

    /** A live region photo behind the closing call to action, or the stock photo as a fallback. */
    public function finalImage(): string
    {
        return $this->catalog->finalImage() ?? asset('assets/images/vouchers/landing-final.jpg');
    }

    /** @return list<array{url: string, alt: string}> */
    public function gallery(): array
    {
        return $this->catalog->gallery();
    }

    /** @return list<array{text: string, name: string, tour: string}> */
    public function reviews(): array
    {
        return $this->catalog->reviews();
    }

    /** @return Collection<int, Faq> */
    public function faqs(): Collection
    {
        return get_faqs_by_page(self::FAQ_PAGE);
    }

    /** @return array<string, string> */
    public function countryCodes(): array
    {
        return (array) config('phone_country_codes', []);
    }

    public function money(int|float $amount): string
    {
        return app()->getLocale() === 'de'
            ? number_format($amount, 0, ',', '.').' €'
            : '€'.number_format($amount, 0, '.', ',');
    }

    public function openValue(): string
    {
        return VoucherRequestService::OPEN;
    }

    public function defaultAmount(): string
    {
        return (string) VoucherRequestService::DEFAULT_AMOUNT;
    }

    public function minAmount(): int
    {
        return VoucherRequestService::MIN_AMOUNT;
    }

    public function maxAmount(): int
    {
        return VoucherRequestService::MAX_AMOUNT;
    }

    /** @return list<array{value: string, label: string}> */
    public function amountOptions(): array
    {
        return array_map(fn (int $amount) => ['value' => (string) $amount, 'label' => $this->money($amount)], VoucherRequestService::PRESET_AMOUNTS);
    }

    /**
     * What the voucher cards show before Alpine takes over: the submitted request on the
     * thank-you URL, otherwise the default amount and "of your choice".
     *
     * @return array{amount: string, where: string, city: string, for: string}
     */
    public function previewDefaults(): array
    {
        if ($this->submitted) {
            $recipient = trim((string) $this->submitted->recipient_names);

            return [
                'amount' => $this->money((float) $this->submitted->price_total),
                'where' => $this->presenter->requestWhere($this->submitted),
                'city' => '',
                'for' => $recipient !== '' ? __(self::COPY.'card_for', ['name' => $recipient]) : __(self::COPY.'card_for_default'),
            ];
        }

        return [
            'amount' => $this->money(VoucherRequestService::DEFAULT_AMOUNT),
            'where' => __(self::COPY.'card_where_open'),
            'city' => '',
            'for' => __(self::COPY.'card_for_default'),
        ];
    }

    /** Summary of the request just sent (thank-you URL only). */
    public function success(): ?array
    {
        return $this->submitted ? $this->presenter->request($this->submitted) : null;
    }

    /** The submitted request as form input, so the live preview keeps showing it. */
    private function submittedValues(): array
    {
        $amount = (int) round((float) $this->submitted->price_total);
        $preset = in_array($amount, VoucherRequestService::PRESET_AMOUNTS, true);

        return [
            'country' => (string) $this->submitted->request_country,
            'region' => (string) $this->submitted->request_region,
            'city' => (string) $this->submitted->request_city,
            'amount' => $preset ? (string) $amount : 'custom',
            'custom_amount' => $preset ? '' : (string) $amount,
            'recipient' => (string) $this->submitted->recipient_names,
        ];
    }

    public function serial(): string
    {
        return $this->submitted?->voucher_code ?? 'CAG-'.Str::upper(Str::random(4));
    }

    /**
     * Boot data for the voucherLanding Alpine component: the destination tree, amount options,
     * the old input and server errors of a failed submit, and the copy the live preview needs.
     *
     * @return array<string, mixed>
     */
    public function clientConfig(?ViewErrorBag $errors, array $old): array
    {
        $copy = fn (string $key) => __(self::COPY.$key);
        $countryCode = (string) ($this->user?->phone_country_code ?: '+49');
        $old = $this->submitted ? $this->submittedValues() : $old;

        return [
            'open' => VoucherRequestService::OPEN,
            'destinations' => $this->catalog->destinations(),
            'submitted' => $this->submitted !== null,
            'amounts' => $this->amountOptions(),
            'minAmount' => VoucherRequestService::MIN_AMOUNT,
            'maxAmount' => VoucherRequestService::MAX_AMOUNT,
            'locale' => app()->getLocale(),
            'values' => [
                'country' => (string) ($old['country'] ?? ''),
                'region' => (string) ($old['region'] ?? ''),
                'city' => (string) ($old['city'] ?? ''),
                'amount' => (string) ($old['amount'] ?? VoucherRequestService::DEFAULT_AMOUNT),
                'customAmount' => (string) ($old['custom_amount'] ?? ''),
                'recipient' => (string) ($old['recipient'] ?? ''),
                'name' => (string) ($old['name'] ?? trim(($this->user?->firstname ?? '').' '.($this->user?->lastname ?? ''))),
                'countryCode' => (string) ($old['country_code'] ?? (array_key_exists($countryCode, $this->countryCodes()) ? $countryCode : '+49')),
                'phone' => (string) ($old['phone'] ?? ($this->user?->phone ?? '')),
                'email' => (string) ($old['email'] ?? ($this->user?->email ?? '')),
                'consent' => (bool) ($old['consent'] ?? false),
            ],
            'errors' => collect($errors?->getBag('default')->messages() ?? [])->map(fn (array $messages) => $messages[0])->all(),
            'i18n' => [
                'whereOpen' => $copy('card_where_open'),
                'whereCountry' => $copy('card_where_country'),
                'whereRegion' => $copy('card_where_region'),
                'city' => $copy('card_city'),
                'for' => $copy('card_for'),
                'forDefault' => $copy('card_for_default'),
                'flashRegion' => $copy('flash_region'),
                'flashOpen' => $copy('flash_open'),
                'errors' => __(self::COPY.'errors'),
                'customRange' => __(self::COPY.'errors.custom_amount_range', [
                    'min' => $this->money(VoucherRequestService::MIN_AMOUNT),
                    'max' => $this->money(VoucherRequestService::MAX_AMOUNT),
                ]),
            ],
        ];
    }
}
