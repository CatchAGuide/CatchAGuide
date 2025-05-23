<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CustomerNewsletterMail extends Mailable
{
    use Queueable, SerializesModels;

    public $email;
    public $language;
    public $target;
    public $type = 'customer_newsletter_mail';

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($email, $locale)
    {
        $this->email = $email;
        $this->locale = $locale;
        $this->language = $locale;
        $this->target = 'customer_newsletter_mail';
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('mails.customernewsletter')->to($this->email)->cc('info@catchaguide.com')
            ->subject("Danke für Dein Interesse");
    }
}
