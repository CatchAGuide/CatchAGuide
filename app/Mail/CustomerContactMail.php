<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CustomerContactMail extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $email;
    public $description;
    public $language;
    public $target;
    public $type = 'customer_contact_mail';

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name, $email, $description)
    {
        $this->name = $name;
        $this->email = $email;
        $this->description = $description;
        $this->language = app()->getLocale();
        $this->target = 'customer_contact_mail';
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('mails.customercontactmail')
            ->to($this->email)->cc(env('TO_CEO'))
            ->subject("Deine Kontaktanfrage");
    }
}
