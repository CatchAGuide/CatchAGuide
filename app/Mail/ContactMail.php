<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactMail extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $email;
    public $description;
    public $phone;
    public $phone_country_code;
    public $language;
    public $target;
    public $type = 'contact_mail';

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name, $email, $description, $phone = null, $phone_country_code = null)
    {
        $this->name = $name;
        $this->email = $email;
        $this->description = $description;
        $this->phone = $phone;
        $this->phone_country_code = $phone_country_code;
        $this->language = app()->getLocale();
        $this->target = 'contact_mail';
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('mails.contactmail')
            ->to(env('TO_CEO'))
            ->subject("Neue Kontaktanfrage");
    }
}
