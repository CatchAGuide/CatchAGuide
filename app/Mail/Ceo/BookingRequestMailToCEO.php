<?php

namespace App\Mail\Ceo;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use App\Models\Booking;

class BookingRequestMailToCEO extends Mailable
{
    use Queueable, SerializesModels;


    protected $booking;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;

    }

    public function build()
    {
        return $this->view('mails.ceo.request_mail_to_ceo')->with([
            'booking' => $this->booking,
            'user' => $this->booking->user,
            'guiding' => $this->booking->guiding,
            'guide' => $this->booking->guiding->user,
        ])->subject(__('profile.gn-request')." – Catch A Guide");
    }
}
