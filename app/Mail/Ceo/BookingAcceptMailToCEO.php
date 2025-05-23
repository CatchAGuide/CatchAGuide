<?php

namespace App\Mail\Ceo;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use App\Models\Booking;

class BookingAcceptMailToCEO extends Mailable
{
    use Queueable, SerializesModels;

    protected $booking;
    public $language;
    public $target;
    public $type = 'booking_accept';

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
        $this->language = $booking->user?->language ?? app()->getLocale();
        $this->target = 'booking_' . $booking->id;
    }

    public function build()
    {
        return $this->view('mails.ceo.accept_mail_to_ceo')->with([
            'booking' => $this->booking,
            'user' => $this->booking->user,
            'guiding' => $this->booking->guiding,
            'guide' => $this->booking->guiding->user,
        ])->subject(__('profile.gt-accepted')." – Catch A Guide");
    }
}
