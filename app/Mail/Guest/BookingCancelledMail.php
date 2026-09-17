<?php

namespace App\Mail\Guest;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use App\Models\Booking;

class BookingCancelledMail extends Mailable
{
    use SerializesModels;

    public $booking;
    public $language;
    public $target;
    public $type = 'booking_cancelled_mail';

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
        $this->language = $booking->customerLocale();
        $this->target = 'booking_' . $booking->id;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $guide = $this->booking->guiding->user;
        $guideName = $guide->firstname;

        $text = __('emails.guest_booking_confirmed_cancelled_text_1');
        $text = str_replace('[Guide Name]', $guideName, $text);

        $formattedDate = date('F j, Y', strtotime($this->booking->book_date));
        $text = str_replace('[Date]', $formattedDate, $text);

        $text = str_replace('[Location]', $this->booking->guiding->location, $text);

        return $this->view('mails.guest.cancelled_mail', [
            'user' => $this->booking->user,
            'booking' => $this->booking,
            'guiding' => $this->booking->guiding,
            'guide' => $guide,
            'textNote' => $text,
        ])->subject(__('emails.guest_booking_confirmed_cancelled_title') . ' – Catch A Guide');
    }
}
