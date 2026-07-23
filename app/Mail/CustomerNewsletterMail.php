<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CustomerNewsletterMail extends Mailable
{
    use Queueable, SerializesModels;

    public $email;
    public $locale;
    public $language;
    public $target;
    public $copyNamespace = 'emails.newsletter_customer';
    public $type = 'customer_newsletter_mail';

    public function __construct($email, $locale)
    {
        $this->email = $email;
        $this->locale = $locale;
        $this->language = $locale;
        $this->target = 'customer_newsletter_mail';
    }

    public function build()
    {
        return $this->view('mails.customernewsletter')
            ->to($this->email)
            ->cc(config('mail.admin_email'))
            ->subject(__($this->copyNamespace . '.subject'));
    }
}
