<?php

namespace App\Mail\Guide;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use App\Models\Booking;

class GuideBookingExpiredMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    protected $booking;
    protected $user;
    protected $guiding;
    protected $guide;
    public $language;
    public $target;
    public $type = 'guide_booking_expired';

    public function __construct(Booking $booking,$user,$guiding,$guide)
    {
        $this->booking = $booking;
        $this->user = $user;
        $this->guiding = $guiding;
        $this->guide = $guide;
        $this->language = $guide->user->language;
        $this->target = 'booking_' . $booking->id;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('mails.guide.guide_expired_booking')
        ->with([
            'booking' => $this->booking,
            'user' => $this->user,
            'guiding' => $this->guiding,
            'guide' => $this->guide,
        ])
        ->subject(__('profile.gd-cancelled')." – Catch A Guide");
    }
}
