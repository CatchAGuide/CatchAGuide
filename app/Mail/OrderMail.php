<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($order)
    {
        $this->order = $order;

    }

    /**
     * Build the message.
     *
     * @return $this
     */


    public function build()
    {

        return $this->view('mails.ordermail')
            ->to($this->order->user->email)->cc(env('CC_MAIL','info@catchaguide.com'))
            ->subject("Bestellbestätigung");
    }
}
