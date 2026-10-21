<?php

namespace App\Services\Voucher;

use App\Mail\Guest\VoucherOrderGuestMail;
use App\Mail\Guide\VoucherOrderGuideMail;
use App\Models\VoucherOrder;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * The two voucher order emails: next steps to the buyer (order language) and the order to the
 * guide (guide's language, the CaG voucher PDF attached when they use it, BCC to CaG
 * operations). A failed mail is logged and never undoes the order.
 */
class VoucherMailer
{
    public function orderPlaced(VoucherOrder $order): void
    {
        $guide = $order->guide;
        $mails = [[$order->buyer_email, (new VoucherOrderGuestMail($order))->locale($order->language), null]];

        if ($guide?->email) {
            $locale = $order->voucher_language ?: VoucherLanguage::forGuide($guide, $order->language);
            $mails[] = [$guide->email, (new VoucherOrderGuideMail($order))->locale($locale), config('mail.admin_email')];
        }

        foreach ($mails as [$recipient, $mail, $bcc]) {
            $this->send($order, $recipient, $mail, $bcc);
        }
    }

    private function send(VoucherOrder $order, string $recipient, Mailable $mail, ?string $bcc): void
    {
        try {
            $pending = Mail::to($recipient);
            if ($bcc) {
                $pending->bcc($bcc);
            }
            $pending->send($mail);
        } catch (Throwable $e) {
            Log::error('Voucher order mail failed', [
                'voucher_order_id' => $order->id,
                'mail' => $mail::class,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
