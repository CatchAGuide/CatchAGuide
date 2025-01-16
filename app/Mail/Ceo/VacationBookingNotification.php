<?php

namespace App\Mail\Ceo;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\VacationBooking;

class VacationBookingNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;

    public function __construct(VacationBooking $booking)
    {
        $this->booking = $booking;
    }

    public function build()
    {
        return $this->subject('New Vacation Booking Request')
                    ->markdown('mails.ceo.vacation_booking_notification');
    }
}