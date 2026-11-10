<?php

namespace App\Mail\Admin;

use App\Mail\Guest\VoucherRequestGuestMail;
use App\Models\VoucherOrder;
use App\Presenters\Voucher\VoucherOrderPresenter;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * A new /guidings/voucher request for CaG (mail.admin_email): amount, destination, the guest's
 * message and contact details, with Reply-To the buyer. CaG follows up and issues the voucher.
 */
class VoucherRequestAdminMail extends Mailable
{
    use Queueable, SerializesModels;

    // Properties for email logging
    public $type = 'admin_voucher_request';
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
        return new self(VoucherRequestGuestMail::sample()->order);
    }

    public function build()
    {
        $data = $this->viewData();

        return $this->view('mails.admin.voucher_request')
            ->replyTo($this->order->buyer_email, $this->order->buyerName())
            ->subject(__('emails.voucher_request_admin.subject', ['amount' => $data['amount'], 'where' => $data['where']]))
            ->with($data);
    }

    /**
     * @return array<string, mixed>
     */
    public function viewData(): array
    {
        return app(VoucherOrderPresenter::class)->request($this->order) + [
            'language' => strtoupper((string) $this->order->language),
            'adminUrl' => route('admin.voucher-orders.index', ['q' => $this->order->voucher_code]),
            'site' => preg_replace('/^www\./', '', (string) parse_url(url('/'), PHP_URL_HOST)),
        ];
    }
}
