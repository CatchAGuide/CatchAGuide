<?php

namespace App\Traits;

use App\Models\Booking;
use App\Models\UserICalFeed;
use App\Services\ICalGeneratorService;
use Illuminate\Support\Facades\Log;

trait HasICalIntegration
{
    /**
     * Generate ICS content and get/create feed for a booking
     * 
     * @param Booking $booking
     * @param bool $forGuide Whether this is for the guide (true) or user (false)
     * @return array ['icsContent' => string, 'userICalFeed' => UserICalFeed|null]
     */
    protected function generateBookingICSAndFeed(Booking $booking, bool $forGuide = false): array
    {
        $icalGeneratorService = app(ICalGeneratorService::class);
        
        return $icalGeneratorService->generateBookingICSAndFeed($booking, $forGuide);
    }

    /**
     * Attach ICS file to the mail if content exists
     * 
     * @param \Illuminate\Mail\Mailable $mail
     * @param string $icsContent
     * @param Booking $booking
     * @return \Illuminate\Mail\Mailable
     */
    protected function attachICSFile($mail, string $icsContent, Booking $booking)
    {
        if ($icsContent) {
            $filename = "fishing_tour_{$booking->id}_{$booking->book_date}.ics";
            $mail->attachData($icsContent, $filename, [
                'mime' => 'text/calendar',
            ]);
        }
        
        return $mail;
    }

    /**
     * Log ICS integration debug information
     * 
     * @param Booking $booking
     * @param string $icsContent
     * @param UserICalFeed|null $userICalFeed
     * @param string $mailType
     * @return void
     */
    protected function logICSIntegration(Booking $booking, string $icsContent, ?UserICalFeed $userICalFeed, string $mailType): void
    {
        Log::info("Building {$mailType}", [
            'booking_id' => $booking->id,
            'has_guide' => $booking->guiding?->user ? 'yes' : 'no',
            'has_ics_content' => $icsContent ? 'yes' : 'no',
            'has_user_ical_feed' => $userICalFeed ? 'yes' : 'no',
            'user_ical_feed_id' => $userICalFeed ? $userICalFeed->id : null
        ]);
    }
} 