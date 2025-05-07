<?php

namespace App\Mail\Guest;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\VacationBooking;

class GuestVacationBookingNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;
    public $vacation;
    public $language;
    public $target;
    public $type = 'guest_vacation_booking_notification';

    public function __construct(VacationBooking $booking)
    {
        $this->booking = $booking;
        $this->vacation = $booking->vacation;
        $this->language = app()->getLocale();
        $this->target = 'booking_' . $booking->id;
    }

    public function build()
    {
        return $this->subject('New Vacation Booking Request')
                    ->markdown('mails.guest.guest_vacation_booking_notification');
    }
}