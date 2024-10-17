<?php

namespace App\Mail\Guest;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
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
        return $this->view('mails.guest.rejected_mail',['user' => $this->booking->user,'booking' => $this->booking,'guiding' => $this->booking->guiding,'guide'=>$this->booking->guiding->user])
        ->subject(__('profile.gr-rejected')." – Catch A Guide");
    }
}
