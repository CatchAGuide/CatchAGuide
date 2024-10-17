<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class StorniGuidingMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $guide;
    public $guiding;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($guiding, $guide, $user)
    {
        $this->guiding = $guiding;
        $this->user = $user;
        $this->guide = $guide;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('mails.stornomail')
            ->to($this->guide->email)->cc(env('CC_MAIL','info@catchaguide.com'))
            ->subject("Stornierung einer Buchung");
    }
}
