<?php

namespace App\Mail\Guide;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GuideApplicationReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user) {}

    public function build()
    {
        return $this->view('mails.guide.application_received')
            ->to($this->user->email)
            ->subject(__('emails.guide_application_received_subject'));
    }
}
