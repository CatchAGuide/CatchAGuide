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
    public $language;
    public $target;
    public $type = 'contact_mail';

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name, $email, $description, $phone = null)
    {
        $this->name = $name;
        $this->email = $email;
        $this->description = $description;
        $this->phone = $phone;
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
