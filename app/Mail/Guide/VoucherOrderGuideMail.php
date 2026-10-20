<?php

namespace App\Mail\Guide;

use App\Enums\VoucherDesign;
use App\Mail\Guest\VoucherOrderGuestMail;
use App\Models\VoucherOrder;
use App\Presenters\Voucher\VoucherOrderPresenter;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * New gift voucher order for the guide: buyer contact, recipient(s), price and voucher code.
 * The guide contacts the buyer, collects the payment and confirms it with the link in this
 * email (or in Profile › Gift vouchers). Nothing is attached: CaG emails the voucher to the
 * buyer only after that confirmation (VoucherPaymentService). A BCC goes to CaG operations.
 */
class VoucherOrderGuideMail extends Mailable
{
    use Queueable, SerializesModels;

    // Properties for email logging
    public $type = 'guide_voucher_order';
    public $language;
    public $target;

    public function __construct(
        public VoucherOrder $order,
    ) {
        $this->language = $order->language;
        $this->target = 'voucher_order_'.$order->id;
    }

    public static function sample(VoucherDesign $design = VoucherDesign::CagStandard): self
    {
        return new self(VoucherOrderGuestMail::sample($design)->order);
    }

    public function build()
    {
        $data = $this->viewData();

        return $this->view('mails.guide.voucher_order')
            ->subject(__('emails.voucher_order_guide.subject', ['tour' => $data['tourTitle']]))
            ->replyTo($this->order->buyer_email, $data['buyerName'])
            ->with($data);
    }

    /**
     * Variables for mails.guide.voucher_order in the current locale (also used by the admin
     * email preview).
     *
     * @return array<string, mixed>
     */
    public function viewData(): array
    {
        $order = $this->order;

        return app(VoucherOrderPresenter::class)->details($order) + [
            'ownDesign' => $order->design_type === VoucherDesign::GuideOwn,
            // The preview's unsaved sample has no token.
            'confirmUrl' => $order->payment_token ? route('voucher-payment.show', ['token' => $order->payment_token]) : url('/'),
            'contactEmail' => (string) config('mail.admin_email'),
            'site' => preg_replace('/^www\./', '', (string) parse_url(url('/'), PHP_URL_HOST)),
        ];
    }
}
