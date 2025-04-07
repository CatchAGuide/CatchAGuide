<?php

namespace App\Mail\Guest;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Booking;

class GuestReviewMail extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;
    public $guide;
    public $user;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
        $this->guide = $booking->guiding;
        $this->user = $booking->user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject(__('emails.guest_tour_review_title'))
            ->view('mails.guest.guest_review')
            ->with([
                'userName' => $this->user->firstname,
                'guideName' => $this->guide->user->firstname,
                'location' => $this->booking->location,
                'reviewUrl' => route('ratings.show', ['token' => $this->booking->token]),
            ]);
    }
} 