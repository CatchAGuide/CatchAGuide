<?php

namespace App\Mail\Guide;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Booking;

class GuideBookingRequestMail extends Mailable
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
        return $this->view('mails.guide.guide_booking_request')
        ->with([
            'booking' => $this->booking,
            'user' => $this->user,
            'guiding' => $this->guiding,
            'guide' => $this->guide,
        ])
        ->subject(__('profile.gn-request')." – Catch A Guide");
    }
}
