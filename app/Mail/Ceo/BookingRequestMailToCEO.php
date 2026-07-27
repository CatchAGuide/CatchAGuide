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

    public $booking;
    public $user;
    public $guiding;
    public $guide;

    // Properties for email logging
    public $type = 'ceo_booking_notification';
    public $language = 'de'; // As specified in your SendCheckoutEmail job
    public $target;

    /**
     * Create a new message instance.
     *
     * Pass customer/guide explicitly — never re-resolve via $booking->user after
     * queue restore (conditional user relation breaks under SerializesModels).
     *
     * @param  \App\Models\User|\App\Models\UserGuest  $user
     * @return void
     */
    public function __construct(Booking $booking, $user, $guiding, $guide)
    {
        $this->booking = $booking;
        $this->user = $user;
        $this->guiding = $guiding;
        $this->guide = $guide;

        // Set properties for email logging
        $this->target = 'admin_booking_' . $booking->id;
    }

    public function build()
    {
        return $this->view('mails.ceo.request_mail_to_ceo')->with([
            'booking' => $this->booking,
            'user' => $this->user,
            'guiding' => $this->guiding,
            'guide' => $this->guide,
        ])->subject(__('profile.gn-request')." – Catch A Guide");
    }
}
