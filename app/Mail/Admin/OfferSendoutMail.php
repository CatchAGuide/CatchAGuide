<?php

namespace App\Mail\Admin;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class OfferSendoutMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $payload;
    public string $messageId;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
        // Generate a unique Message-ID for email threading
        $this->messageId = '<' . Str::uuid()->toString() . '@' . parse_url(config('app.url'), PHP_URL_HOST) . '>';
    }

    public function build()
    {
        $subject = __('emails.offer_sendout_subject', ['name' => config('app.name')]);
        $messageId = $this->messageId;
        
        // Build the mailable - keep it simple to avoid sending issues
        // Laravel will automatically generate a Message-ID header for threading
        return $this->subject($subject)
            ->view('mails.admin.offer-sendout')
            ->with(array_merge($this->payload, ['message_id' => $messageId]));
    }
}
