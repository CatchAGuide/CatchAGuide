<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MailToCEO extends Mailable
{
    use Queueable, SerializesModels;

    private $booking;
    private $guiding;
    private $user;
    private $guide;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($booking, $guiding, $guide, $user)
    {
        $this->booking = $booking;
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
        return $this->view('mails.mailtoceo', [
            'booking' => $this->booking,
            'guiding' => $this->guiding,
            'user' => $this->user,
            'guide' => $this->guide
        ])
            ->to(env('TO_CEO','info@catchaguide.com'))
            ->subject("Eine neue Buchung!");
    }
}
