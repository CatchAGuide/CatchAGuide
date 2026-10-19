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
 * New gift voucher order for the guide: buyer contact, recipient(s), price and voucher code.
 * The guide contacts the buyer, collects the payment and only then sends the voucher: the
 * attached CaG standard voucher PDF, or (own design / no PDF) their own voucher filled in with
 * these details. A BCC goes to CaG operations (see VoucherOrderService).
 */
class VoucherOrderGuideMail extends Mailable
{
    use Queueable, SerializesModels;

    // Properties for email logging
    public $type = 'guide_voucher_order';
    public $language;
    public $target;

    /** Stored voucher PDF; false until read. */
    private string|false|null $pdfContents = false;

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

        $mail = $this->view('mails.guide.voucher_order')
            ->subject(__('emails.voucher_order_guide.subject', ['tour' => $data['tourTitle']]))
            ->replyTo($this->order->buyer_email, $data['buyerName'])
            ->with($data);

        if ($data['withPdf'] && $this->pdf() !== null) {
            $mail->attachData($this->pdf(), app(VoucherPdfGenerator::class)->fileName($this->order), ['mime' => 'application/pdf']);
        }

        return $mail;
    }

    private function pdf(): ?string
    {
        if ($this->pdfContents === false) {
            $this->pdfContents = app(VoucherPdfGenerator::class)->contents($this->order);
        }

        return $this->pdfContents;
    }

    /**
     * Variables for mails.guide.voucher_order in the current locale (also used by the admin
     * email preview). withPdf is true only when the standard voucher PDF exists to attach.
     *
     * @return array<string, mixed>
     */
    public function viewData(): array
    {
        $order = $this->order;
        // The preview's unsaved sample shows the standard-design text without a real file.
        $withPdf = $order->exists ? $this->pdf() !== null : $order->design_type === VoucherDesign::CagStandard;

        return app(VoucherOrderPresenter::class)->details($order) + [
            'withPdf' => $withPdf,
            'ownDesign' => $order->design_type === VoucherDesign::GuideOwn,
            'contactEmail' => (string) config('mail.admin_email'),
            'site' => preg_replace('/^www\./', '', (string) parse_url(url('/'), PHP_URL_HOST)),
        ];
    }
}
