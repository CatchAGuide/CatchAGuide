<?php

namespace App\Services\Voucher;

use App\Models\VoucherOrder;

/**
 * The guide confirms that the buyer has paid (emailed payment link or Profile › Gift vouchers).
 * Only then does Catch A Guide email the voucher to the buyer: the CaG standard PDF attached,
 * or, with the guide's own design, a note that the guide sends it. The guide gets a
 * confirmation too. Confirming is one-way and happens once; repeats change nothing.
 */
class VoucherPaymentService
{
    public function __construct(
        private readonly VoucherOrderService $orders,
        private readonly VoucherMailer $mailer,
    ) {}

    public function findByToken(string $token): ?VoucherOrder
    {
        return strlen($token) === 32 ? VoucherOrder::with(['guiding', 'guide.information'])->where('payment_token', $token)->first() : null;
    }

    /**
     * @return bool true when this call confirmed the payment, false when it already was
     */
    public function confirm(VoucherOrder $order): bool
    {
        // Conditional update: a double click or a second tab can't send the voucher twice.
        $confirmed = VoucherOrder::whereKey($order->getKey())->whereNull('paid_at')->update(['paid_at' => now()]) === 1;
        $order->refresh();

        if (! $confirmed) {
            return false;
        }

        if ($order->design_type?->generatesPdf() && ! $order->hasPdf()) {
            $this->orders->attachPdf($order);
        }

        if (! app()->environment('local')) {
            $this->mailer->paymentConfirmed($order);
        }

        return true;
    }
}
