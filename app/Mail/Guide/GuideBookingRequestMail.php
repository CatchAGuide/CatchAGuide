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

    public $booking;
    public $user;
    public $guiding;
    public $guide;
    
    // Properties for email logging
    public $type = 'guide_booking_request';
    public $language;
    public $target;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Booking $booking,$user,$guiding,$guide)
    {
        $this->booking = $booking;
        $this->user = $user;
        $this->guiding = $guiding;
        $this->guide = $guide;
        
        // Set properties for email logging
        $this->language = $guide->language ?? app()->getLocale();
        $this->target = 'guide_' . $guide->id . '_booking_' . $booking->id;
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
