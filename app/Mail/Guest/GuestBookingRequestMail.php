<?php

namespace App\Mail\Guest;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Booking;

class GuestBookingRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $booking;
    protected $user;
    protected $guiding;
    protected $guide;


    public function __construct(Booking $booking,$user,$guiding,$guide)
    {
        $this->booking = $booking;
        $this->user = $user;
        $this->guiding = $guiding;
        $this->guide = $guide;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $guideName = $this->booking->guiding->user->firstname;
        $text = __('emails.guest_booking_request_text_1');
        $text = str_replace('[Guide Name]', $guideName, $text);

        $formattedDate = date('F j, Y', strtotime($this->booking->book_date));
        $text = str_replace('[Date]', $formattedDate, $text);

        $text = str_replace('[Location]', $this->booking->guiding->location, $text);

        $this->booking->guideName = $guideName;
        
        $textProvide = __('emails.guest_booking_request_text_5');
        $textProvide = str_replace('[Guide Name]', $guideName, $textProvide);

        return $this->view('mails.guest.guest_booking_request')
        ->with([
            'booking' => $this->booking,
            'user' => $this->user,
            'guiding' => $this->guiding,
            'guide' => $this->guide,
            'alternativeText' => $textProvide,
            'textNote' => $text,
        ])
        ->subject(__('profile.br-confirmation')." – Catch A Guide");
    }
}
