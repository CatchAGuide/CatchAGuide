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
    public $language;
    public $target;
    public $type = 'vacation_booking_notification';

    public function __construct(VacationBooking $booking)
    {
        $this->booking = $booking;
        $this->language = $booking->user->language;
        $this->target = 'booking_' . $booking->id;
    }

    public function build()
    {
        return $this->subject('New Vacation Booking Request')
                    ->markdown('mails.ceo.vacation_booking_notification');
    }
}