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
    public $language;
    public $target;
    public $copyNamespace = 'emails.newsletter_admin';
    public $viewSubscribersUrl;
    public $type = 'newsletter';

    public function __construct($email, $locale, ?string $viewSubscribersUrl = null)
    {
        $this->email = $email;
        $this->locale = $locale;
        $this->language = $locale;
        $this->target = 'newsletter';
        $this->viewSubscribersUrl = $viewSubscribersUrl;
    }

    public function build()
    {
        return $this->view('mails.newsletter')
            ->to(config('mail.admin_email'))
            ->subject(__($this->copyNamespace . '.subject'));
    }
}
