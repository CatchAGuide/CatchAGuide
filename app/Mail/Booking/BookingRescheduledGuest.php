<?php

namespace App\Mail\Booking;

use App\Models\Booking;
use App\Models\Guiding;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingRescheduledGuest extends Mailable
{
    use Queueable, SerializesModels;

    public $newBooking;
    public $originalBooking;
    public $guiding;
    public $guide;
    public $user;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Booking $newBooking, Booking $originalBooking, Guiding $guiding, User $guide, $user)
    {
        $this->newBooking = $newBooking;
        $this->originalBooking = $originalBooking;
        $this->guiding = $guiding;
        $this->guide = $guide;
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = 'Your Booking Has Been Rescheduled: ' . $this->guiding->title;
        
        return $this->subject($subject)
            ->to($this->user->email)
            ->view('emails.booking.rescheduled-guest');
    }
} 