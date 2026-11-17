<?php

namespace App\Mail\Guide;

use App\Models\GuideCertificate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GuideCertificateRejectedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public GuideCertificate $certificate) {}

    public function build()
    {
        return $this->view('mails.guide.certificate_rejected', ['user' => $this->certificate->user])
            ->subject(__('guide_area.mail.rejected_subject', ['certificate' => $this->certificate->label()]));
    }
}
