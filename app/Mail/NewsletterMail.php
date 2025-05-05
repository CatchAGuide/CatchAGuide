<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewsletterMail extends Mailable
{
    use Queueable, SerializesModels;

    public $email;
    public $locale;
    public $target;
    public $language;
    public $type = 'newsletter';

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($email, $locale)
    {
        $this->email = $email;
        $this->locale = $locale;
        $this->target = 'newsletter';
        $this->language = $locale;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('mails.newsletter')->to('info@catchaguide.com')
            ->subject("Neuer Newsletterabonnent");
    }
}
