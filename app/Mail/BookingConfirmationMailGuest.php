<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingConfirmationMailGuest extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;
    public $user;
    public $guide;
    public $guiding;
    private $phoneFromUser;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($booking, $guiding, $guide, $user, $phoneFromUser)
    {
        $this->booking = $booking;
        $this->guiding = $guiding;
        $this->user = $user;
        $this->guide = $guide;
        $this->phoneFromUser = $phoneFromUser;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('mails.bookingconfirmationmailguest', ['phoneFromUser' => $this->phoneFromUser])
            ->to($this->user->email)->cc(env('CC_MAIL','info@catchaguide.com'))
            ->subject("Buchungsbestätigung – Catch A Guide");
    }
}
