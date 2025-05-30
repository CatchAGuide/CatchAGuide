<?php

namespace App\Mail\Ceo;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use App\Models\Booking;

class BookingRejectMailToCEO extends Mailable
{
    use Queueable, SerializesModels;

    protected $booking;
    public $target;
    public $type = 'booking_reject_mail_to_ceo';
    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
        $this->target = 'admin_booking_' . $booking->id;    
    }

    public function build()
    {
        return $this->view('mails.ceo.reject_mail_to_ceo')->with([
            'booking' => $this->booking,
            'user' => $this->booking->user,
            'guiding' => $this->booking->guiding,
            'guide' => $this->booking->guiding->user,
        ])->subject(__('profile.gr-rejected')." – Catch A Guide");
    }
}
