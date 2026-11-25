<?php

namespace App\Mail\Guide;

use App\Models\GuideCertificate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/** Internal: a guide uploaded or replaced a certificate that needs review. */
class GuideCertificateSubmittedAdminMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public GuideCertificate $certificate) {}

    /** Same ops inbox as new guide applications. @return array<int, string> */
    public static function recipients(): array
    {
        $recipients = collect(explode(',', (string) config('guide_onboarding.admin_notification_email')))
            ->map(fn ($email) => trim($email))
            ->filter(fn ($email) => $email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->values()
            ->all();

        return $recipients ?: [(string) config('mail.from.address', 'info@catchaguide.com')];
    }

    public function build()
    {
        $user = $this->certificate->user;
        $name = trim($user->firstname.' '.$user->lastname) ?: $user->email;

        return $this->view('mails.guide.certificate_submitted_admin', ['user' => $user, 'name' => $name])
            ->subject(__('guide_area.mail.admin_submitted_subject', ['name' => $name]));
    }
}
