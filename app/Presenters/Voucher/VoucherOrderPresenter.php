<?php

namespace App\Presenters\Voucher;

use App\Models\User;
use App\Models\VoucherOrder;
use App\Presenters\Offers\TourCardPresenter;
use App\Presenters\Vacation\CampQuotePresenter;
use Carbon\CarbonInterface;

/**
 * Voucher order details in the current locale, shared by the standard voucher PDF, the buyer
 * email and the guide email so all three show the same values. Tour title, guests and price
 * come from the order's snapshot; location and duration are read from the live tour.
 */
class VoucherOrderPresenter
{
    public function __construct(
        private readonly TourCardPresenter $tours,
        private readonly CampQuotePresenter $money,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function details(VoucherOrder $order): array
    {
        $guiding = $order->guiding;
        $summary = $guiding ? $this->tours->presentCheckoutSummary($guiding) : null;
        $guide = $order->guide;
        $guests = (int) $order->number_of_guests;

        return [
            'code' => (string) $order->voucher_code,
            'tourTitle' => (string) $order->tour_title_snapshot,
            'tourLocation' => (string) ($summary['location'] ?? ''),
            'tourDuration' => $summary['duration'] ?? null,
            'tourUrl' => $summary['url'] ?? null,
            'guests' => trans_choice('checkout.voucher.guests_count', $guests, ['count' => $guests]),
            'price' => $this->money->money((float) $order->price_total),
            'recipients' => (string) $order->recipient_names,
            'sender' => (string) ($order->sender_name ?? ''),
            // Not 'message': mail views always get $message (the Illuminate\Mail\Message).
            'personalMessage' => (string) ($order->personal_message ?? ''),
            'buyerName' => $order->buyerName(),
            'buyerFirstName' => (string) $order->buyer_first_name,
            'buyerEmail' => (string) $order->buyer_email,
            'buyerPhone' => $order->buyerPhone(),
            'purchasedOn' => $this->date($order->created_at ?? now()),
            'validUntil' => $order->valid_until ? $this->date($order->valid_until) : '',
            'guideName' => $this->guideName($guide),
            'guideFirstName' => (string) ($guide?->firstname ?? ''),
            'guideEmail' => (string) ($guide?->email ?? ''),
            'guidePhone' => $this->guidePhone($guide),
        ];
    }

    public function guideName(?User $guide): string
    {
        if ($guide === null) {
            return '';
        }

        $company = trim((string) ($guide->information?->company_name ?? ''));

        return $company !== '' ? $company : trim($guide->firstname.' '.$guide->lastname);
    }

    private function guidePhone(?User $guide): string
    {
        if ($guide === null) {
            return '';
        }

        $phone = trim((string) ($guide->information?->phone ?: $guide->phone));
        $code = trim((string) ($guide->information?->phone ? $guide->information->phone_country_code : $guide->phone_country_code));

        return $phone === '' ? '' : trim($code.' '.$phone);
    }

    private function date(CarbonInterface $date): string
    {
        return $date->format(app()->getLocale() === 'de' ? 'd.m.Y' : 'M j, Y');
    }
}
