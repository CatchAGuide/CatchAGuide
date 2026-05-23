<?php

namespace App\Mail\Guide;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
class GuideAdminNewRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user) {}

    public function build()
    {
        $name = trim($this->user->firstname.' '.$this->user->lastname);

        $recipients = collect(explode(',', (string) config('guide_onboarding.admin_notification_email')))
            ->map(fn ($email) => trim($email))
            ->filter(fn ($email) => $email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL))
            ->unique()
            ->values()
            ->all();

        if ($recipients === []) {
            $recipients = [config('mail.from.address', 'info@catchaguide.com')];
        }

        $mail = $this->view('mails.guide.admin_new_request')
            ->subject(__('emails.guide_admin_new_request_subject', ['name' => $name ?: $this->user->email]));

        foreach ($recipients as $index => $recipient) {
            if ($index === 0) {
                $mail->to($recipient);
            } else {
                $mail->cc($recipient);
            }
        }

        return $mail;
    }
}
