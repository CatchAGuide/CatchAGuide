<?php

namespace App\Mail;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GuideReminder12Hours extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;
    public $guide;
    public $language;
    public $target;
    public $type = 'guide_reminder_12hrs';

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Booking $booking, User $guide)
    {
        $this->booking = $booking;
        $this->guide = $guide;
        $this->language = $guide?->language ?? app()->getLocale();
        $this->target = 'booking_' . $booking->id;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject(__('emails.guide_reminder_to_respond_12hrs_title'))
            ->to($this->guide->email)
            ->view('mails.guide.guide_reminder_12hrs')
            ->with([
                'booking' => $this->booking,
                'guide' => $this->guide,
            ]);
    }
} 