<?php

namespace App\Mail\Guest;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use App\Models\Booking;


class BookingRejectMail extends Mailable
{
    use SerializesModels;

    public $booking;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $guideName = $this->booking->guiding->user->firstname;
        $text = __('emails.guest_booking_request_cancelled_text_1');
        $text = str_replace('[Guide Name]', $guideName, $text);

        $formattedDate = $this->formatDate($this->booking->book_date);
        $text = str_replace('[Date]', $formattedDate, $text);

        $text = str_replace('[Location]', $this->booking->guiding->location, $text);

        $this->booking->guideName = $guideName;
        $this->booking->textNote = $text;
        
        $textProvide = __('emails.guest_booking_request_cancelled_text_4');
        $textProvide = str_replace('[Guide Name]', $guideName, $textProvide);

        $this->booking->alternativeText = $textProvide;
        $this->booking->alternativeDates = json_decode($this->booking->alternative_dates);

        return $this->view('mails.guest.rejected_mail',['user' => $this->booking->user,'booking' => $this->booking,'guiding' => $this->booking->guiding,'guide'=>$this->booking->guiding->user])
        ->subject(__('profile.gr-rejected')." – Catch A Guide");
    }

    public function formatDate($date){
        $date = date('F j, Y', strtotime($date));
        return $date;
    }
}
