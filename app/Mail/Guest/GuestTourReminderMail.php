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
        
        $guideName = $this->booking->guiding->user->firstname;
        $location = $this->booking->guiding->location;
        
        $eventDate = Carbon::parse($this->booking->blocked_event->from)->format('F j, Y');

        return $this->subject(__('emails.guest_tour_reminder_title'))
            ->view('mails.guest.guest_tour_reminder')
            ->with([
                'guestName' => $guestName,
                'guideName' => $guideName,
                'location' => $location,
                'date' => $eventDate,
            ]);
    }
} 