<?php

namespace App\Services\Voucher;

use App\Enums\VoucherOrderKind;
use App\Models\User;
use App\Models\VoucherOrder;

/**
 * Stores a /guidings/voucher request as a voucher_orders row with status Requested (destination
 * and amount, no tour) and emails the buyer and CaG. CaG then contacts the buyer, takes the
 * payment and issues the voucher by hand; nothing here charges or produces a voucher PDF.
 */
class VoucherRequestService
{
    /** Value of the "still open" country/region option. */
    public const OPEN = '_open';

    /** @var list<int> Amount chips in €, in the order shown. */
    public const PRESET_AMOUNTS = [50, 100, 150, 250];

    public const DEFAULT_AMOUNT = 150;

    /** Bounds of a custom amount in €. */
    public const MIN_AMOUNT = 50;

    public const MAX_AMOUNT = 5000;

    public function __construct(
        private readonly VoucherCodeGenerator $codes,
        private readonly VoucherMailer $mailer,
    ) {}

    /**
     * @param  array{0: ?string, 1: ?string, 2: ?string}  $place  country, region, city (null = open)
     * @param  array{name: string, email: string, country_code: string, phone: string, recipient: ?string}  $contact
     */
    public function submit(array $place, int $amount, array $contact, ?User $currentUser, string $locale): VoucherOrder
    {
        [$country, $region, $city] = $place;

        $order = VoucherOrder::create([
            'voucher_code' => $this->codes->generate(),
            'kind' => VoucherOrderKind::Request,
            'status' => VoucherOrder::STATUS_OPEN,
            'user_id' => $currentUser?->getKey(),
            'request_country' => $country,
            'request_region' => $region,
            'request_city' => $city,
            'price_total' => $amount,
            'currency' => 'EUR',
            'recipient_names' => $contact['recipient'],
            'buyer_first_name' => $contact['name'],
            'buyer_email' => $contact['email'],
            'buyer_phone_country_code' => $contact['country_code'],
            'buyer_phone' => $contact['phone'],
            'language' => $locale,
            'voucher_language' => $locale,
        ]);

        if (! app()->environment('local')) {
            $this->mailer->requestReceived($order);
        }

        return $order;
    }
}
