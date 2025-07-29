<?php

namespace App\Mail\Guest;

use App\Mail\BookingConfirmationMail;
use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

class BookingAcceptMail extends BookingConfirmationMail
{
    use Queueable, SerializesModels;

    public $booking;
    public $icsContent;

    /**
     * Create a new message instance.
     */
    public function __construct(Booking $booking)
    {
        parent::__construct($booking);
        $this->booking = $booking;
        $this->icsContent = null; // ICS generation removed as per user request
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $mail = $this->subject('Booking Confirmation - ' . $this->booking->guiding->title)
                    ->view('mails.guest.accepted_mail');

        // ICS attachment removed as per user request
        
        return $mail;
    }
}
