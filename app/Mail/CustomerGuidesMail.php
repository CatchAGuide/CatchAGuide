<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CustomerGuidesMail extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $email;
    public $language;
    public $target;
    public $type = 'customerguidesmail';
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($name, $email)
    {
        $this->name = $name;
        $this->email = $email;
        $this->language = app()->getLocale();
        $this->target = 'customerguidesmail';   
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('mails.customerguidesmail')
            ->to($this->email)->cc(env('CC_MAIL','info@catchaguide.com'))
            ->subject("Deine Anfrage zur Guide Verifizierung");
    }
}
