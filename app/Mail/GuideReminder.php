<?php

namespace App\Mail;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class GuideReminder extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;
    public $guide;
    public $language;
    public $target;
    public $type;
    public $hours;
    public $timeText;

    /**
     * Create a new message instance.
     *
     * @param Booking $booking
     * @param User $guide
     * @param int $hours Number of hours before expiration (24, 12, 6, 3, 1)
     */
    public function __construct(Booking $booking, User $guide, int $hours)
    {
        $this->booking = $booking;
        $this->guide = $guide;
        $this->hours = $hours;
        $this->language = $guide?->language ?? app()->getLocale();
        $this->target = 'booking_' . $booking->id;
        $this->type = config('booking.reminder_email_type_prefix', 'guide_booking_reminder_') . $hours;
        $this->timeText = $this->formatTimeText($hours);
    }

    /**
     * Format time text based on hours
     *
     * @param int $hours
     * @return string
     */
    private function formatTimeText(int $hours): string
    {
        if ($hours === 1) {
            return '1 hour';
        }
        
        return $hours . ' hours';
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
     * @param int $hours
     * @return bool True if sent, false if skipped
     */
    public static function sendReminder(Booking $booking, User $guide, int $hours)
    {
        $reminder = new static($booking, $guide, $hours);
        return $reminder->sendIfNotSent();
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject(__('emails.guide_reminder_to_respond_title', ['time' => $this->timeText]))
            ->to($this->guide->email)
            ->view('mails.guide.guide_reminder')
            ->with([
                'booking' => $this->booking,
                'guide' => $this->guide,
                'language' => $this->language,
                'type' => $this->type,
                'target' => $this->target,
                'hours' => $this->hours,
                'timeText' => $this->timeText,
            ]);
    }
}
