<?php

namespace App\Mail\Guide;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use App\Models\Booking;

class GuideBookingCancelledMail extends Mailable
{
    use SerializesModels;

    public $booking;
    public $language;
    public $target;
    public $type = 'guide_booking_cancelled_mail';

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
        $this->language = $booking->guiding->user->language ?? app()->getLocale();
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
        $user = $this->booking->user ?? (object) [
            'firstname' => $this->booking->firstname,
            'lastname' => $this->booking->lastname,
            'email' => $this->booking->email,
        ];

        return $this->view('mails.guide.guide_booking_cancelled_mail', [
            'user' => $user,
            'guide' => $guide,
            'guiding' => $this->booking->guiding,
            'booking' => $this->booking,
        ])
        ->to($guide->email)
        ->subject(__('profile.gdc-cancelled') . ' – Catch A Guide');
    }
}
