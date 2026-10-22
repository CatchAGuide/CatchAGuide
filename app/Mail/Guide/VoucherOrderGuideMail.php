<?php

namespace App\Mail\Guide;

use App\Enums\VoucherDesign;
use App\Mail\Guest\VoucherOrderGuestMail;
use App\Models\VoucherOrder;
use App\Presenters\Voucher\VoucherOrderPresenter;
use App\Services\Voucher\VoucherPdfGenerator;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * New gift voucher order for the guide: buyer contact, recipient(s), price and voucher code,
 * with the CaG voucher PDF attached when the guide uses that design. The guide contacts the
 * buyer, collects the payment and sends them the voucher; CaG does nothing further. A BCC goes
 * to CaG operations (VoucherMailer).
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
        $this->language = $order->voucher_language ?: $order->language;
        $this->target = 'voucher_order_'.$order->id;
    }

    public static function sample(VoucherDesign $design = VoucherDesign::CagStandard): self
    {
        return new self(VoucherOrderGuestMail::sample($design)->order);
    }

    public function build()
    {
        $data = $this->viewData();
        $mail = $this->view('mails.guide.voucher_order')
            ->subject(__('emails.voucher_order_guide.subject', ['tour' => $data['tourTitle']]))
            ->replyTo($this->order->buyer_email, $data['buyerName'])
            ->with($data);

        $pdfs = app(VoucherPdfGenerator::class);
        if ($data['withPdf'] && ($pdf = $pdfs->contents($this->order)) !== null) {
            $mail->attachData($pdf, $pdfs->fileName($this->order), ['mime' => 'application/pdf']);
        }

        return $mail;
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
            // The preview's unsaved sample shows the PDF case.
            'withPdf' => $order->exists ? $order->hasPdf() : $order->design_type === VoucherDesign::CagStandard,
            'ownDesign' => $order->design_type === VoucherDesign::GuideOwn,
            'bookingsUrl' => route('profile.bookings'),
            'contactEmail' => (string) config('mail.admin_email'),
            'site' => preg_replace('/^www\./', '', (string) parse_url(url('/'), PHP_URL_HOST)),
        ];
    }
}
