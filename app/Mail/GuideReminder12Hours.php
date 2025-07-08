<?php

namespace App\Mail;

use App\Models\Booking;
use App\Models\User;
use App\Models\EmailLog;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

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
     * Check if this email has already been sent (duplicate check)
     *
     * @return bool
     */
    public function hasBeenSent()
    {
        return CheckEmailLog($this->type, $this->target, $this->guide->email);
    }

    /**
     * Send the email only if it hasn't been sent before
     *
     * @return bool True if sent, false if skipped due to duplicate
     */
    public function sendIfNotSent()
    {
        if ($this->hasBeenSent()) {
            return false; // Email already sent, skip
        }

        // Set locale and send email
        app()->setLocale($this->language);
        Mail::send($this);
        
        return true; // Email sent successfully
    }

    /**
     * Static helper to create and send (if not duplicate)
     *
     * @param Booking $booking
     * @param User $guide
     * @return bool True if sent, false if skipped
     */
    public static function sendReminder(Booking $booking, User $guide)
    {
        $reminder = new static($booking, $guide);
        return $reminder->sendIfNotSent();
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
                'language' => $this->language,
                'type' => $this->type,
                'target' => $this->target,
            ]);
    }
} 