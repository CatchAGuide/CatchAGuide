<?php

namespace App\Mail\Ceo;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use App\Models\Booking;

class BookingExpireMailToCEO extends Mailable
{
    use Queueable, SerializesModels;

    protected $booking;
    public $guide;
    public $guiding;
    public $user;

    public function __construct(Booking $booking, $guiding, $guide, $user)
    {
        $this->booking = $booking;
        $this->guiding = $guiding;
        $this->user = $user;
        $this->guide = $guide;
    }


    public function build()
    {
        return $this->view('mails.ceo.expire_mail_to_ceo')->with([
            'booking' => $this->booking,
            'user' => $this->user,
            'guiding' => $this->guiding,
            'guide' => $this->guide,
        ])->subject(__('profile.gc-cancelled')." – Catch A Guide");
    }
}
