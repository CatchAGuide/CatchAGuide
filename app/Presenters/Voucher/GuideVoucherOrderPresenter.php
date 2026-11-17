<?php

namespace App\Presenters\Voucher;

use App\Models\VoucherOrder;

/**
 * One gift voucher order as the guide sees it (Guide Area › Vouchers page and
 * /api/guide/voucher-orders).
 */
class GuideVoucherOrderPresenter
{
    /** @return array<string, mixed> */
    public function present(VoucherOrder $order): array
    {
        return [
            'id' => (int) $order->id,
            'public_id' => $order->public_id,
            'voucher_code' => $order->voucher_code,
            'tour_title' => (string) $order->tour_title_snapshot,
            'guiding_id' => $order->guiding_id ? (int) $order->guiding_id : null,
            'number_of_guests' => (int) $order->number_of_guests,
            'price_total' => $order->price_total !== null ? (float) $order->price_total : null,
            'currency' => $order->currency ?: 'EUR',
            'recipient_names' => $order->recipient_names,
            'buyer' => [
                'name' => $order->buyerName(),
                'email' => $order->buyer_email,
                'phone' => $order->buyerPhone(),
            ],
            'design' => $order->design_type?->value,
            'valid_until' => $order->valid_until?->toDateString(),
            'created_at' => $order->created_at?->toIso8601String(),
            'is_paid' => (bool) $order->is_paid,
            'paid_at' => $order->paid_at?->toIso8601String(),
            'is_sent' => (bool) $order->is_sent,
            'sent_at' => $order->sent_at?->toIso8601String(),
            'pdf_url' => $order->design_type?->generatesPdf() ? route('profile.vouchers.orders.pdf', $order->id) : null,
            // Display strings in the current locale (page only; API clients format themselves).
            'created_label' => $order->created_at?->translatedFormat('j M Y'),
            'valid_until_label' => $order->valid_until?->translatedFormat('j M Y'),
            'price_label' => $order->price_total !== null ? $this->money((float) $order->price_total, $order->currency ?: 'EUR') : null,
        ];
    }

    private function money(float $amount, string $currency): string
    {
        $de = app()->getLocale() === 'de';

        return number_format($amount, 2, $de ? ',' : '.', $de ? '.' : ',').' '.$currency;
    }
}
