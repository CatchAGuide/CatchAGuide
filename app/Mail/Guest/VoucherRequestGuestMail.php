<?php

namespace App\Mail\Guest;

use App\Enums\VoucherOrderKind;
use App\Models\VoucherOrder;
use App\Presenters\Voucher\VoucherOrderPresenter;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Confirmation to someone who requested a gift voucher on /guidings/voucher: the request summary,
 * including their message, and that CaG gets in touch. Nothing is charged.
 */
class VoucherRequestGuestMail extends Mailable
{
    use Queueable, SerializesModels;

    // Properties for email logging
    public $type = 'guest_voucher_request';
    public $language;
    public $target;

    public function __construct(
        public VoucherOrder $order,
    ) {
        $this->language = $order->language;
        $this->target = 'voucher_order_'.$order->id;
    }

    /**
     * Unsaved sample request for the admin email preview.
     */
    public static function sample(): self
    {
        $de = app()->getLocale() === 'de';

        return new self((new VoucherOrder)->forceFill([
            'voucher_code' => 'CAG-V-7KQ3PX',
            'kind' => VoucherOrderKind::Request,
            'status' => VoucherOrder::STATUS_OPEN,
            'request_country' => $de ? 'Deutschland' : 'Germany',
            'request_region' => 'Mecklenburg-Vorpommern',
            'request_city' => 'Rügen',
            'price_total' => 150,
            'currency' => 'EUR',
            'recipient_names' => $de ? "Geschenk für Papa.\nWunschtermine im Mai." : "Gift for Dad.\nPreferred dates in May.",
            'buyer_first_name' => 'Lena Schmidt',
            'buyer_email' => 'lena.schmidt@example.com',
            'buyer_phone_country_code' => '+49',
            'buyer_phone' => '151 2345678',
            'language' => app()->getLocale(),
            'created_at' => now(),
        ]));
    }

    public function build()
    {
        return $this->view('mails.guest.voucher_request')
            ->subject(__('emails.voucher_request_guest.subject'))
            ->with($this->viewData());
    }

    /**
     * Variables for mails.guest.voucher_request in the current locale (also used by the admin
     * email preview).
     *
     * @return array<string, mixed>
     */
    public function viewData(): array
    {
        return app(VoucherOrderPresenter::class)->request($this->order) + [
            'contactEmail' => (string) config('mail.admin_email'),
            'site' => preg_replace('/^www\./', '', (string) parse_url(url('/'), PHP_URL_HOST)),
        ];
    }
}
