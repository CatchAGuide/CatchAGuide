<?php

namespace App\Mail\Guide;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Guiding;
use App\Models\Booking;

class GuideUpcomingTourMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The guide instance.
     *
     * @var \App\Models\Guide
     */
    public $guide;

    /**
     * The booking instance.
     *
     * @var \App\Models\Booking
     */
    public $booking;
    public $language;
    public $target;
    public $type = 'guide_reminder_upcoming_tour';
    /**
     * Create a new message instance.
     *
     * @param  \App\Models\Guide  $guide
     * @param  \App\Models\Booking  $booking
     * @return void
     */
    public function __construct(Guiding $guide, Booking $booking)
    {
        $this->guide = $guide;
        $this->booking = $booking;
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
        return $this->subject(str_replace('[Guest Name]', $this->booking->user->firstname, __('emails.guide_reminder_upcoming_tour_title')))
                    ->view('mails.guide.guide_upcoming_tour');
    }
} 