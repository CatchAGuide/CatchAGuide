<?php

namespace App\Mail\Guest;

use App\Models\Booking;
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
    public $language;
    public $target;
    public $type = 'guest_tour_reminder';

    /**
     * Create a new message instance.
     *
     * @param Booking $booking
     * @return void
     */
    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
        
        // Set properties for email logging
        $this->language = $booking->customerLocale();
        $this->target = 'booking_' . $booking->id;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $guestName = $this->booking->user->firstname ?? __('emails.guest_name');

        $guideName = $this->booking->guiding->user->firstname;
        $location = $this->booking->guiding->location;

        // calendar_schedule is the current system; blocked_event is a legacy fallback
        // (see Booking::getBookingDate()) — going straight to blocked_event->from crashed
        // the reminder for any booking created after the calendar migration.
        $bookingDate = $this->booking->getBookingDate();
        $eventDate = $bookingDate ? $bookingDate->format('F j, Y') : '';

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