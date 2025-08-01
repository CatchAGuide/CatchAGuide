<?php

namespace App\Services;

use App\Models\ICalFeed;
use App\Models\CalendarSchedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Sabre\VObject\Reader;

class ICalService
{
    /**
     * Sync an iCal feed
     */
    public function syncFeed(ICalFeed $feed): array
    {
        try {
            // Fetch the iCal feed
            $response = Http::timeout(30)->get($feed->feed_url);
            
            if (!$response->successful()) {
                $error = "Failed to fetch iCal feed: HTTP {$response->status()}";
                $feed->setError($error);
                return ['success' => false, 'error' => $error];
            }

            $icalContent = $response->body();
            
            // Parse the iCal content
            $events = $this->parseICalContent($icalContent, $feed);
            
            if (empty($events)) {
                $feed->updateLastSync(true);
                return ['success' => true, 'events_count' => 0, 'message' => 'No events found in feed'];
            }

            // Sync events to calendar
            $syncedCount = $this->syncEventsToCalendar($events, $feed);
            
            $feed->updateLastSync(true);
            
            return [
                'success' => true,
                'events_count' => count($events),
                'synced_count' => $syncedCount,
                'message' => "Successfully synced {$syncedCount} events"
            ];

        } catch (\Exception $e) {
            $error = "iCal sync failed: " . $e->getMessage();
            Log::error('iCal sync error', [
                'feed_id' => $feed->id,
                'user_id' => $feed->user_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $feed->setError($error);
            return ['success' => false, 'error' => $error];
        }
    }

    /**
     * Parse iCal content and extract events
     */
    private function parseICalContent(string $icalContent, ICalFeed $feed): array
    {
        try {
            $vcalendar = Reader::read($icalContent);
            $events = [];

            foreach ($vcalendar->VEVENT as $vevent) {
                $event = $this->parseVEvent($vevent, $feed);
                
                if ($event) {
                    $events[] = $event;
                }
            }

            return $events;

        } catch (\Exception $e) {
            Log::error('iCal parsing error', [
                'feed_id' => $feed->id,
                'error' => $e->getMessage()
            ]);
            
            throw new \Exception("Failed to parse iCal content: " . $e->getMessage());
        }
    }

    /**
     * Parse a single VEVENT
     */
    private function parseVEvent($vevent, ICalFeed $feed): ?array
    {
        try {
            // Get event start time
            $startTime = $this->getEventDateTime($vevent->DTSTART);
            if (!$startTime) {
                return null;
            }

            // Get event end time (optional)
            $endTime = $this->getEventDateTime($vevent->DTEND);
            
            // Get event summary/title
            $summary = (string) ($vevent->SUMMARY ?? 'Untitled Event');
            
            // Get event description
            $description = (string) ($vevent->DESCRIPTION ?? '');
            
            // Get event location
            $location = (string) ($vevent->LOCATION ?? '');
            
            // Get event UID
            $uid = (string) ($vevent->UID ?? uniqid());

            // Filter events based on sync type
            if ($feed->sync_type === 'bookings_only') {
                if (!$this->isBookingEvent($summary, $description)) {
                    return null;
                }
            }

            return [
                'uid' => $uid,
                'summary' => $summary,
                'description' => $description,
                'location' => $location,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'feed_id' => $feed->id,
                'user_id' => $feed->user_id,
            ];

        } catch (\Exception $e) {
            Log::warning('Failed to parse VEVENT', [
                'feed_id' => $feed->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Get datetime from iCal datetime field
     */
    private function getEventDateTime($dtField): ?Carbon
    {
        if (!$dtField) {
            return null;
        }

        try {
            $value = (string) $dtField;
            
            // Handle different datetime formats
            if (str_contains($value, 'T')) {
                // ISO format: 20231201T120000Z or 20231201T120000
                $value = str_replace('Z', '', $value);
                return Carbon::createFromFormat('Ymd\THis', $value);
            } else {
                // Date only format: 20231201
                return Carbon::createFromFormat('Ymd', $value);
            }
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Check if an event is a booking event
     */
    private function isBookingEvent(string $summary, string $description): bool
    {
        $bookingKeywords = [
            'booking', 'reservation', 'appointment', 'meeting', 'session',
            'tour', 'trip', 'fishing', 'guide', 'client', 'customer',
            'booked', 'confirmed', 'scheduled'
        ];

        $text = strtolower($summary . ' ' . $description);
        
        foreach ($bookingKeywords as $keyword) {
            if (str_contains($text, $keyword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Sync events to calendar schedule
     */
    private function syncEventsToCalendar(array $events, ICalFeed $feed): int
    {
        $syncedCount = 0;

        foreach ($events as $event) {
            try {
                // Check if event already exists
                $existingEvent = CalendarSchedule::where('user_id', $feed->user_id)
                    ->where('date', $event['start_time']->format('Y-m-d'))
                    ->where('note', 'like', "%{$event['uid']}%")
                    ->first();

                if ($existingEvent) {
                    // Update existing event
                    $existingEvent->update([
                        'note' => "iCal: {$event['summary']} - {$event['description']} [UID: {$event['uid']}]",
                    ]);
                } else {
                    // Create new event
                    CalendarSchedule::create([
                        'type' => 'custom_schedule',
                        'date' => $event['start_time']->format('Y-m-d'),
                        'note' => "iCal: {$event['summary']} - {$event['description']} [UID: {$event['uid']}]",
                        'user_id' => $feed->user_id,
                        'guiding_id' => null,
                        'vacation_id' => null,
                        'booking_id' => null,
                    ]);
                }

                $syncedCount++;

            } catch (\Exception $e) {
                Log::error('Failed to sync event to calendar', [
                    'feed_id' => $feed->id,
                    'event' => $event,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $syncedCount;
    }

    /**
     * Sync all feeds for a user
     */
    public function syncUserFeeds(User $user): array
    {
        $feeds = $user->icalFeeds()->active()->get();
        $results = [];

        foreach ($feeds as $feed) {
            if ($feed->needsSync()) {
                $results[$feed->id] = $this->syncFeed($feed);
            }
        }

        return $results;
    }

    /**
     * Sync all feeds that need syncing
     */
    public function syncAllFeeds(): array
    {
        $feeds = ICalFeed::active()->needsSync()->get();
        $results = [];

        foreach ($feeds as $feed) {
            $results[$feed->id] = $this->syncFeed($feed);
        }

        return $results;
    }

    /**
     * Validate iCal feed URL
     */
    public function validateFeedUrl(string $url): array
    {
        try {
            $response = Http::timeout(10)->head($url);
            
            if (!$response->successful()) {
                return ['valid' => false, 'error' => "HTTP {$response->status()} response"];
            }

            // Try to fetch a small portion to check if it's valid iCal
            $response = Http::timeout(10)->get($url);
            
            if (!$response->successful()) {
                return ['valid' => false, 'error' => "Failed to fetch feed content"];
            }

            $content = $response->body();
            
            // Check if it looks like iCal content
            if (!str_contains($content, 'BEGIN:VCALENDAR') || !str_contains($content, 'END:VCALENDAR')) {
                return ['valid' => false, 'error' => 'URL does not contain valid iCal content'];
            }

            return ['valid' => true, 'message' => 'Valid iCal feed'];

        } catch (\Exception $e) {
            return ['valid' => false, 'error' => $e->getMessage()];
        }
    }
} 