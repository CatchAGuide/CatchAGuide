<?php

namespace App\Mail\Admin;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OfferFollowUpMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $payload;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function build()
    {
        $subject = __('emails.offer_followup_subject', ['name' => config('app.name')]);
        return $this->subject($subject)
            ->view('mails.admin.offer-follow-up')
            ->with($this->payload);
    }
}
