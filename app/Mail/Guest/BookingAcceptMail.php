<?php

namespace App\Mail\Guest;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use App\Models\Booking;


class BookingAcceptMail extends Mailable
{
    use SerializesModels;

    public $booking;
    public $language;
    public $target;
    public $type = 'booking_accept_mail';

    public function __construct(Booking $booking)
    {
          $this->booking = $booking;
          $this->language = $booking->user?->language ?? app()->getLocale();
          $this->target = 'booking_' . $booking->id;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('mails.guest.accepted_mail',['user' => $this->booking->user,'booking' => $this->booking,'guiding' => $this->booking->guiding,'guide'=>$this->booking->guiding->user])
        ->subject(__('profile.br-accepted')." – Catch A Guide");
    }
}
