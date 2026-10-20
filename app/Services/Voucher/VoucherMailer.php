<?php

namespace App\Services\Voucher;

use App\Mail\Guest\VoucherOrderGuestMail;
use App\Mail\Guest\VoucherPaidGuestMail;
use App\Mail\Guide\VoucherOrderGuideMail;
use App\Mail\Guide\VoucherPaidGuideMail;
use App\Models\EmailLog;
use App\Models\VoucherOrder;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * The two pairs of voucher emails: order placed (buyer + guide, no voucher attached) and payment
 * confirmed (the voucher to the buyer, a confirmation to the guide). The buyer gets the order
 * language, the guide their account language; guide mails BCC CaG operations. A failed mail is
 * logged and never undoes the order or the payment confirmation.
 */
class VoucherMailer
{
    public function orderPlaced(VoucherOrder $order): void
    {
        $this->send($order, new VoucherOrderGuestMail($order), new VoucherOrderGuideMail($order));
    }

    public function paymentConfirmed(VoucherOrder $order): void
    {
        $this->send($order, new VoucherPaidGuestMail($order), new VoucherPaidGuideMail($order));
    }

    private function send(VoucherOrder $order, Mailable $guestMail, Mailable $guideMail): void
    {
        $guide = $order->guide;
        $mails = [[$order->buyer_email, $guestMail->locale($order->language), null]];

        if ($guide?->email) {
            $mails[] = [$guide->email, $guideMail->locale($this->guideLocale($guide->language, $order->language)), config('mail.admin_email')];
        }

        foreach ($mails as [$recipient, $mail, $bcc]) {
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

    private function guideLocale(?string $guideLanguage, string $fallback): string
    {
        $locale = EmailLog::normalizeLanguage($guideLanguage);

        return in_array($locale, array_values(config('app.locales', [])), true) ? $locale : $fallback;
    }
}
