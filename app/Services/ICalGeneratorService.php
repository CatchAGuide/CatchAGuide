<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserICalFeed;
use App\Models\CalendarSchedule;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ICalGeneratorService
{
    /**
     * Generate iCal content for a user feed
     */
    public function generateICalContent(UserICalFeed $feed): string
    {
        try {
            $user = $feed->user;
            $events = $this->getEventsForFeed($feed);
            
            $icalContent = "BEGIN:VCALENDAR\r\n";
            $icalContent .= "VERSION:2.0\r\n";
            $icalContent .= "PRODID:-//CatchAGuide//Calendar//EN\r\n";
            $icalContent .= "CALSCALE:GREGORIAN\r\n";
            $icalContent .= "METHOD:PUBLISH\r\n";
            $icalContent .= "X-WR-CALNAME:{$feed->name}\r\n";
            $icalContent .= "X-WR-CALDESC:CatchAGuide Calendar Feed\r\n";
            
            foreach ($events as $event) {
                $icalContent .= $this->generateEventContent($event, $feed);
            }
            
            $icalContent .= "END:VCALENDAR\r\n";
            
            return $icalContent;
            
        } catch (\Exception $e) {
            Log::error('Failed to generate iCal content', [
                'feed_id' => $feed->id,
                'user_id' => $feed->user_id,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Get events based on feed type
     */
    private function getEventsForFeed(UserICalFeed $feed): array
    {
        $user = $feed->user;
        $events = [];
        
        switch ($feed->feed_type) {
            case 'bookings_only':
                $events = $this->getBookingEvents($user);
                break;
                
            case 'all_events':
                $events = $this->getAllEvents($user);
                break;
                
            case 'custom_schedule':
                $events = $this->getCustomScheduleEvents($user, $feed->feed_settings);
                break;
        }
        
        return $events;
    }

    /**
     * Get booking events
     */
    private function getBookingEvents(User $user): array
    {
        $events = [];
        
        // Get user's guiding IDs
        $guidingIds = $user->guidings()->pluck('id')->toArray();
        
        // Get bookings as guide
        $guideBookings = Booking::whereIn('guiding_id', $guidingIds)
            ->with(['user', 'guiding', 'calendar_schedule'])
            ->get();
        
        foreach ($guideBookings as $booking) {
            // Skip if related models are null
            if (!$booking->user || !$booking->guiding) {
                continue;
            }
            
            // Get booking date from calendar_schedule if available, otherwise use book_date
            $bookingDate = $booking->calendar_schedule 
                ? Carbon::parse($booking->calendar_schedule->date)
                : Carbon::parse($booking->book_date);
            
            $events[] = [
                'uid' => "booking_{$booking->id}@catchaguide.com",
                'summary' => "Fishing Trip: {$booking->user->firstname} {$booking->user->lastname}",
                'description' => "Fishing trip with {$booking->count_of_users} person(s)",
                'start_time' => $bookingDate,
                'end_time' => $bookingDate->copy()->addHours($booking->guiding->duration ?? 4),
                'location' => $booking->guiding->location ?? 'TBD',
                'status' => $booking->status,
                'type' => 'booking'
            ];
        }
        
        // Get bookings as client
        $clientBookings = Booking::where('user_id', $user->id)
            ->with(['guiding', 'guiding.user', 'calendar_schedule'])
            ->get();
            
        foreach ($clientBookings as $booking) {
            // Skip if related models are null
            if (!$booking->guiding || !$booking->guiding->user) {
                continue;
            }
            
            // Get booking date from calendar_schedule if available, otherwise use book_date
            $bookingDate = $booking->calendar_schedule 
                ? Carbon::parse($booking->calendar_schedule->date)
                : Carbon::parse($booking->book_date);
            
            $events[] = [
                'uid' => "client_booking_{$booking->id}@catchaguide.com",
                'summary' => "Fishing Trip with {$booking->guiding->user->firstname} {$booking->guiding->user->lastname}",
                'description' => "Fishing trip for {$booking->count_of_users} person(s)",
                'start_time' => $bookingDate,
                'end_time' => $bookingDate->copy()->addHours($booking->guiding->duration ?? 4),
                'location' => $booking->guiding->location ?? 'TBD',
                'status' => $booking->status,
                'type' => 'client_booking'
            ];
        }
        
        return $events;
    }

    /**
     * Get all calendar events
     */
    private function getAllEvents(User $user): array
    {
        $events = [];
        
        // Get calendar schedules
        $schedules = CalendarSchedule::where('user_id', $user->id)
            ->with(['booking', 'guiding'])
            ->get();
            
        foreach ($schedules as $schedule) {
            $event = [
                'uid' => "schedule_{$schedule->id}@catchaguide.com",
                'start_time' => Carbon::parse($schedule->date),
                'end_time' => Carbon::parse($schedule->date)->addDay(),
                'type' => 'schedule'
            ];
            
            switch ($schedule->type) {
                case 'tour_request':
                    if ($schedule->booking && $schedule->booking->user) {
                        $event['summary'] = "Booking Request: {$schedule->booking->user->firstname} {$schedule->booking->user->lastname}";
                        $event['description'] = "Booking request for {$schedule->booking->count_of_users} person(s)";
                        $event['status'] = $schedule->booking->status;
                    } else {
                        $event['summary'] = "Booking Request";
                        $event['description'] = "Booking request";
                    }
                    break;
                    
                case 'tour_schedule':
                    $event['summary'] = "Blocked - Not Available";
                    $event['description'] = "Date blocked based on availability settings";
                    break;
                    
                case 'vacation_schedule':
                    $event['summary'] = "Vacation";
                    $event['description'] = $schedule->note ?: "Vacation day";
                    break;
                    
                case 'custom_schedule':
                    $event['summary'] = "Custom Event";
                    $event['description'] = $schedule->note ?: "Custom scheduled event";
                    break;
            }
            
            $events[] = $event;
        }
        
        // Add booking events
        $events = array_merge($events, $this->getBookingEvents($user));
        
        return $events;
    }

    /**
     * Get custom schedule events
     */
    private function getCustomScheduleEvents(User $user, array $settings = []): array
    {
        $events = [];
        
        // Get custom schedule events
        $schedules = CalendarSchedule::where('user_id', $user->id)
            ->where('type', 'custom_schedule')
            ->get();
            
        foreach ($schedules as $schedule) {
            $events[] = [
                'uid' => "custom_{$schedule->id}@catchaguide.com",
                'summary' => "Custom Event",
                'description' => $schedule->note ?: "Custom scheduled event",
                'start_time' => Carbon::parse($schedule->date),
                'end_time' => Carbon::parse($schedule->date)->addDay(),
                'type' => 'custom_schedule'
            ];
        }
        
        return $events;
    }

    /**
     * Generate iCal event content
     */
    private function generateEventContent(array $event, ?UserICalFeed $feed = null): string
    {
        $content = "BEGIN:VEVENT\r\n";
        $content .= "UID:{$event['uid']}\r\n";
        $content .= "DTSTART:" . $event['start_time']->format('Ymd\THis\Z') . "\r\n";
        $content .= "DTEND:" . $event['end_time']->format('Ymd\THis\Z') . "\r\n";
        $content .= "DTSTAMP:" . now()->format('Ymd\THis\Z') . "\r\n";
        $content .= "SUMMARY:" . $this->escapeICalText($event['summary'] ?? 'Event') . "\r\n";
        
        if (isset($event['description'])) {
            $content .= "DESCRIPTION:" . $this->escapeICalText($event['description']) . "\r\n";
        }
        
        if (isset($event['location'])) {
            $content .= "LOCATION:" . $this->escapeICalText($event['location']) . "\r\n";
        }
        
        // Set status based on booking status
        if (isset($event['status'])) {
            switch ($event['status']) {
                case 'accepted':
                    $content .= "STATUS:CONFIRMED\r\n";
                    break;
                case 'cancelled':
                    $content .= "STATUS:CANCELLED\r\n";
                    break;
                case 'pending':
                    $content .= "STATUS:TENTATIVE\r\n";
                    break;
            }
        }
        
        $content .= "SEQUENCE:0\r\n";
        $content .= "END:VEVENT\r\n";
        
        return $content;
    }

    /**
     * Escape text for iCal format
     */
    private function escapeICalText(string $text): string
    {
        $text = str_replace(['\\', ';', ',', "\n", "\r"], ['\\\\', '\\;', '\\,', '\\n', '\\r'], $text);
        return $text;
    }

    /**
     * Create a new user iCal feed
     */
    public function createUserFeed(User $user, array $data): UserICalFeed
    {
        return UserICalFeed::create([
            'user_id' => $user->id,
            'name' => $data['name'],
            'feed_token' => UserICalFeed::generateFeedToken(),
            'otp_secret' => UserICalFeed::generateOTPSecret(),
            'feed_type' => $data['feed_type'] ?? 'bookings_only',
            'feed_settings' => $data['feed_settings'] ?? null,
            'is_active' => true,
            'expires_at' => isset($data['expires_at']) ? Carbon::parse($data['expires_at']) : null,
        ]);
    }

    /**
     * Regenerate feed token
     */
    public function regenerateFeedToken(UserICalFeed $feed): void
    {
        $feed->update([
            'feed_token' => UserICalFeed::generateFeedToken(),
            'otp_secret' => UserICalFeed::generateOTPSecret(),
        ]);
    }

    /**
     * Generate ICS content for a single booking
     */
    public function generateSingleBookingICS(Booking $booking): string
    {
        try {
            $user = $booking->user;
            $guiding = $booking->guiding;
            $guide = $guiding->user;
            
            // Validate required data
            if (!$user || !$guiding || !$guide) {
                return '';
            }
            
            // Get booking date from calendar_schedule if available, otherwise use book_date
            $bookingDate = $booking->calendar_schedule 
                ? Carbon::parse($booking->calendar_schedule->date)
                : Carbon::parse($booking->book_date);
            
            // Create event data using calendar_schedule information
            $event = [
                'uid' => "booking_{$booking->id}@catchaguide.com",
                'summary' => "Fishing Trip: {$guiding->title}",
                'description' => "Fishing trip with {$guide->firstname} {$guide->lastname}\\nLocation: {$guiding->location}\\nNumber of guests: {$booking->count_of_users}\\nPrice: " . number_format($booking->price, 2, ',', '.') . " €\\nContact: {$guide->phone} | {$guide->email}",
                'start_time' => $bookingDate,
                'end_time' => $bookingDate->copy()->addHours($guiding->duration ?? 4),
                'location' => $guiding->location ?? 'TBD',
                'status' => $booking->status,
                'type' => 'booking'
            ];
            
            // Generate ICS content using existing methods
            $icalContent = "BEGIN:VCALENDAR\r\n";
            $icalContent .= "VERSION:2.0\r\n";
            $icalContent .= "PRODID:-//CatchAGuide//Calendar//EN\r\n";
            $icalContent .= "CALSCALE:GREGORIAN\r\n";
            $icalContent .= "METHOD:PUBLISH\r\n";
            $icalContent .= "X-WR-CALNAME:Fishing Trip - {$guiding->title}\r\n";
            $icalContent .= "X-WR-CALDESC:Fishing trip with {$guide->firstname} {$guide->lastname}\r\n";
            
            // Generate event content using existing method
            $icalContent .= $this->generateEventContent($event, null); // Pass null as feed is not needed for single event
            
            $icalContent .= "END:VCALENDAR\r\n";
            
            return $icalContent;
            
        } catch (\Exception $e) {
            Log::error('Failed to generate single booking ICS', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage()
            ]);
            
            return '';
        }
    }

    /**
     * Get or create iCal feed for a booking (for either user or guide)
     * 
     * @param Booking $booking
     * @param bool $forGuide Whether this is for the guide (true) or user (false)
     * @return UserICalFeed|null
     */
    public function getOrCreateUserICalFeedForBooking(Booking $booking, bool $forGuide = false): ?UserICalFeed
    {
        // Determine which user to get the feed for
        $user = $forGuide ? $booking->guiding?->user : $booking->user;
        
        if (!$user) {
            $userType = $forGuide ? 'guide' : 'user';
            Log::warning("No {$userType} found for booking", ['booking_id' => $booking->id]);
            return null;
        }
        
        // For guest users (UserGuest model), skip feed creation
        if (!$forGuide && $booking->is_guest) {
            Log::info('Guest user detected - skipping iCal feed creation', [
                'booking_id' => $booking->id,
                'user_type' => get_class($user)
            ]);
            return null;
        }
        
        $userType = $forGuide ? 'guide' : 'user';
        Log::info("Getting iCal feed for {$userType}", [
            'user_id' => $user->id,
            'booking_id' => $booking->id,
            'user_type' => get_class($user)
        ]);
        
        // Always try to find existing active feed first (reuse existing feeds)
        $existingFeed = UserICalFeed::where('user_id', $user->id)
            ->where('feed_type', 'bookings_only')
            ->where('is_active', true)
            ->first();
            
        if ($existingFeed) {
            Log::info("Reusing existing iCal feed for {$userType}", ['feed_id' => $existingFeed->id]);
            return $existingFeed;
        }
        
        // Only create new feed if no existing feed found
        try {
            $feedName = $forGuide ? 'My Fishing Tours' : 'My Fishing Trips';
            $feed = $this->createUserFeed($user, [
                'name' => $feedName,
                'feed_type' => 'bookings_only',
                'expires_at' => null // No expiration
            ]);
            
            Log::info("Created new iCal feed for {$userType} (first time)", ['feed_id' => $feed->id]);
            return $feed;
        } catch (\Exception $e) {
            // Log error but don't fail the email
            Log::error("Failed to create iCal feed for {$userType}", [
                'user_id' => $user->id,
                'booking_id' => $booking->id,
                'error' => $e->getMessage()
            ]);
            
            return null;
        }
    }

    /**
     * Generate ICS content and get/create feed for a booking in one call
     * 
     * @param Booking $booking
     * @param bool $forGuide Whether this is for the guide (true) or user (false)
     * @return array ['icsContent' => string, 'userICalFeed' => UserICalFeed|null]
     */
    public function generateBookingICSAndFeed(Booking $booking, bool $forGuide = false): array
    {
        return [
            'icsContent' => $this->generateSingleBookingICS($booking),
            'userICalFeed' => $this->getOrCreateUserICalFeedForBooking($booking, $forGuide)
        ];
    }
} 