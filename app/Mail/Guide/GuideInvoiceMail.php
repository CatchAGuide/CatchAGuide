<?php

namespace App\Mail\Guide;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GuideInvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;
    public $user;
    public $guiding;
    public $guide;

    public $type = 'guide_booking_invoice';
    public $language;
    public $target;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
        $this->user = $booking->user;
        $this->guiding = $booking->guiding;
        $this->guide = $booking->guiding->user;
        $this->language = $this->guide->language ?? app()->getLocale();
        $this->target = 'booking_' . $booking->id;
    }

    public function build()
    {
        return $this->view('mails.guide.guide_invoice')
            ->with([
                'booking' => $this->booking,
                'user' => $this->user,
                'guiding' => $this->guiding,
                'guide' => $this->guide,
            ])
            ->subject(__('emails.guide_invoice_subject', ['bookingId' => $this->booking->id]));
    }
}
