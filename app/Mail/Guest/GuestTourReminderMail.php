<?php

namespace App\Mail\Guest;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GuestTourReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The booking instance.
     *
     * @var Booking
     */
    public $booking;

    /**
     * Create a new message instance.
     *
     * @param Booking $booking
     * @return void
     */
    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $guestName = $this->booking->is_guest 
            ? ($this->booking->user->firstname ?? 'Guest') 
            : $this->booking->user->firstname;
        
        $guideName = $this->booking->guiding->user->name;
        $location = $this->booking->guiding->location;
        
        $eventDate = Carbon::parse($this->booking->blocked_event->from)->format('d.m.Y');
        $eventTime = Carbon::parse($this->booking->blocked_event->from)->format('H:i');

        return $this->subject('Your Fishing Tour with Catch a Guide is in 2 Days!')
            ->view('mails.guest.guest_tour_reminder')
            ->with([
                'guestName' => $guestName,
                'guideName' => $guideName,
                'location' => $location,
                'date' => $eventDate,
                'time' => $eventTime,
            ]);
    }
} 