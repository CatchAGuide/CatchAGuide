<?php

namespace App\Mail\Guest;

use App\Enums\VoucherDesign;
use App\Models\VoucherOrder;
use App\Presenters\Voucher\VoucherOrderPresenter;
use App\Services\Voucher\VoucherPdfGenerator;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Sent to the buyer once the guide has confirmed the payment (VoucherPaymentService). With the
 * CaG standard design the voucher PDF is attached; with the guide's own design the email says
 * the guide sends it. The voucher has no date: the recipient books any day with the guide.
 */
class VoucherPaidGuestMail extends Mailable
{
    use Queueable, SerializesModels;

    // Properties for email logging
    public $type = 'guest_voucher_paid';
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
        $mail = $this->view('mails.guest.voucher_paid')
            ->subject(__('emails.voucher_paid_guest.subject', ['tour' => $data['tourTitle']]))
            ->with($data);

        $pdfs = app(VoucherPdfGenerator::class);
        if ($data['withPdf'] && ($pdf = $pdfs->contents($this->order)) !== null) {
            $mail->attachData($pdf, $pdfs->fileName($this->order), ['mime' => 'application/pdf']);
        }

        return $mail;
    }

    /**
     * Variables for mails.guest.voucher_paid in the current locale (also used by the admin
     * email preview). withPdf: the CaG voucher is attached.
     *
     * @return array<string, mixed>
     */
    public function viewData(): array
    {
        $order = $this->order;

        return app(VoucherOrderPresenter::class)->details($order) + [
            // The preview's unsaved sample shows the standard-design text without a real file.
            'withPdf' => $order->exists ? $order->hasPdf() : $order->design_type === VoucherDesign::CagStandard,
            'contactEmail' => (string) config('mail.admin_email'),
            'site' => preg_replace('/^www\./', '', (string) parse_url(url('/'), PHP_URL_HOST)),
        ];
    }
}
