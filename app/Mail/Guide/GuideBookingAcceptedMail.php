<?php

namespace App\Mail\Guide;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use App\Models\Booking;


class GuideBookingAcceptedMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $booking;

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
        return $this->view('mails.guide.guide_accepted_mail',[
        'booking' => $this->booking,
        'user' => $this->booking->user,
        'guiding' => $this->booking->guiding,
        'guide' => $this->booking->guiding->user,
    ])
        ->subject(__('profile.gt-accepted')." – Catch A Guide");
    }
}
