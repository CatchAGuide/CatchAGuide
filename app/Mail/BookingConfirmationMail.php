<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Mail;

class BookingConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $guide;
    public $guiding;
    public $booking;
    public $language;
    public $target;
    public $type = 'booking_confirmation_mail';
    private $phoneFromUser;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($guiding, $guide, $user, $booking, $phoneFromUser)
    {
        $this->guiding = $guiding;
        $this->user = $user;
        $this->guide = $guide;
        $this->booking = $booking;
        $this->phoneFromUser = $phoneFromUser;
        $this->language = $guide?->language ?? app()->getLocale();
        $this->target = 'booking_' . $booking->id;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('mails.bookingconfirmationmail', ['phoneFromUser' => $this->phoneFromUser])
            ->to($this->guide->email)->cc(env('CC_MAIL','info@catchaguide.com'))
            ->subject("Neue Buchung - Catch A Guide");
    }
}
