<?php

namespace App\Mail\Admin;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OfferSendoutMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $payload;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function build()
    {
        $subject = __('emails.offer_sendout_subject', ['name' => config('app.name')]);
        return $this->subject($subject)
            ->view('mails.admin.offer-sendout')
            ->with($this->payload);
    }
}
