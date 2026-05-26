<?php

namespace App\Mail\Guide;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GuideApplicationRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public ?string $rejectionReason = null,
    ) {}

    public function build()
    {
        $name = trim($this->user->firstname.' '.$this->user->lastname);

        return $this->view('mails.guide.application_rejected')
            ->to($this->user->email)
            ->subject(__('emails.guide_application_rejected_subject', ['name' => $name ?: $this->user->email]));
    }
}
