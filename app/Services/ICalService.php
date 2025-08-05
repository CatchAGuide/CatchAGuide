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
    public function syncFeed(ICalFeed $feed, bool $cleanup = true): array
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
            
            // Log parsed events for debugging
            Log::info('iCal feed parsed events', [
                'feed_id' => $feed->id,
                'feed_name' => $feed->name,
                'events_count' => count($events),
                'events_sample' => array_slice($events, 0, 3) // Log first 3 events for debugging
            ]);
            
            if (empty($events)) {
                $feed->updateLastSync(true);
                return ['success' => true, 'events_count' => 0, 'message' => 'No events found in feed'];
            }

            // Sync events to calendar
            $syncedCount = $this->syncEventsToCalendar($events, $feed, $cleanup);
            
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

            // Filter out past events - only sync events from today forward
            $today = Carbon::today();
            if ($startTime->lt($today)) {
                Log::info('Skipping past event', [
                    'feed_id' => $feed->id,
                    'event_date' => $startTime->format('Y-m-d'),
                    'today' => $today->format('Y-m-d')
                ]);
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

            // Get additional iCal properties for better context
            $organizer = (string) ($vevent->ORGANIZER ?? '');
            $attendee = (string) ($vevent->ATTENDEE ?? '');
            $status = (string) ($vevent->STATUS ?? '');
            $categories = (string) ($vevent->CATEGORIES ?? '');
            $priority = (string) ($vevent->PRIORITY ?? '');
            $transp = (string) ($vevent->TRANSP ?? 'OPAQUE'); // OPAQUE = busy, TRANSPARENT = free

            // Enhanced event processing
            $enhancedSummary = $this->enhanceEventSummary($summary, $description, $location, $status, $transp);
            $enhancedDescription = $this->enhanceEventDescription($summary, $description, $location, $organizer, $attendee, $categories, $status, $transp);
            $eventType = $this->determineEventType($summary, $description, $status, $transp, $categories);

            // Filter events based on sync type
            if ($feed->sync_type === 'bookings_only') {
                if (!$this->isBookingEvent($summary, $description)) {
                    return null;
                }
            }

            return [
                'uid' => $uid,
                'summary' => $enhancedSummary,
                'description' => $enhancedDescription,
                'location' => $location,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'feed_id' => $feed->id,
                'user_id' => $feed->user_id,
                'event_type' => $eventType,
                'status' => $status,
                'transp' => $transp,
                'organizer' => $organizer,
                'attendee' => $attendee,
                'categories' => $categories,
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
     * Enhance event summary with more meaningful information
     */
    private function enhanceEventSummary(string $summary, string $description, string $location, string $status, string $transp): string
    {
        // Don't modify the summary if it's explicitly set to "Busy" - preserve it as is
        if (trim(strtolower($summary)) === 'busy') {
            return $summary;
        }

        // If summary is generic, try to extract better info from description
        $genericPatterns = [
            '/blocked/i',
            '/not available/i',
            '/unavailable/i',
            '/occupied/i',
            '/scheduled/i'
        ];

        $isGeneric = false;
        foreach ($genericPatterns as $pattern) {
            if (preg_match($pattern, $summary)) {
                $isGeneric = true;
                break;
            }
        }

        if ($isGeneric && !empty($description)) {
            // Try to extract meaningful information from description
            $lines = explode("\n", $description);
            foreach ($lines as $line) {
                $line = trim($line);
                if (!empty($line) && strlen($line) > 5 && !preg_match('/^[A-Z\s]+$/', $line)) {
                    return $line;
                }
            }
        }

        // If still generic, create a more descriptive summary
        if ($isGeneric) {
            if ($transp === 'TRANSPARENT') {
                return 'Available Time';
            } elseif ($transp === 'OPAQUE') {
                return 'Busy/Blocked Time';
            } else {
                return 'Scheduled Event';
            }
        }

        return $summary;
    }

    /**
     * Enhance event description with additional context
     */
    private function enhanceEventDescription(string $summary, string $description, string $location, string $organizer, string $attendee, string $categories, string $status, string $transp): string
    {
        $parts = [];

        // Add original description if meaningful
        if (!empty($description) && !preg_match('/blocked|not available|unavailable/i', $description)) {
            $parts[] = $description;
        }

        // Add location if available
        if (!empty($location)) {
            $parts[] = "Location: {$location}";
        }

        // Add organizer if available
        if (!empty($organizer)) {
            $organizer = preg_replace('/mailto:/', '', $organizer);
            $parts[] = "Organizer: {$organizer}";
        }

        // Add attendee if available
        if (!empty($attendee)) {
            $attendee = preg_replace('/mailto:/', '', $attendee);
            $parts[] = "Attendee: {$attendee}";
        }

        // Add categories if available
        if (!empty($categories)) {
            $parts[] = "Category: {$categories}";
        }

        // Add status if meaningful
        if (!empty($status) && $status !== 'CONFIRMED') {
            $parts[] = "Status: {$status}";
        }

        // Add availability status
        if ($transp === 'TRANSPARENT') {
            $parts[] = "Availability: Free";
        } elseif ($transp === 'OPAQUE') {
            $parts[] = "Availability: Busy";
        }

        return implode(' | ', $parts);
    }

    /**
     * Determine the type of event for better categorization
     */
    private function determineEventType(string $summary, string $description, string $status, string $transp, string $categories): string
    {
        $text = strtolower($summary . ' ' . $description . ' ' . $categories);

        // Check for vacation/time off
        if (preg_match('/vacation|time off|holiday|leave|off duty/i', $text)) {
            return 'vacation_schedule';
        }

        // Check for blocked/unavailable time
        if (preg_match('/blocked|unavailable|not available|busy|occupied|appointment|meeting|personal|private/i', $text)) {
            return 'custom_schedule';
        }

        // Check for bookings/appointments
        if (preg_match('/booking|reservation|appointment|meeting|session|tour|trip|fishing|guide|client|customer/i', $text)) {
            return 'tour_request';
        }

        // Check for transparent (free) time
        if ($transp === 'TRANSPARENT') {
            return 'custom_schedule';
        }

        // Default to custom schedule
        return 'custom_schedule';
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
    private function syncEventsToCalendar(array $events, ICalFeed $feed, bool $cleanup = true): int
    {
        $syncedCount = 0;

        // Clean up past events from this feed (optional)
        if ($cleanup) {
            $this->cleanupPastEvents($feed);
        }

        foreach ($events as $event) {
            try {
                // Check if event already exists (by date and summary since we no longer store UID in note)
                $existingEvent = CalendarSchedule::where('user_id', $feed->user_id)
                    ->where('date', $event['start_time']->format('Y-m-d'))
                    ->where('note', $event['summary'])
                    ->first();

                // Create enhanced note with more context
                $note = $this->createEnhancedNote($event);

                if ($existingEvent) {
                    // Update existing event
                    $existingEvent->update([
                        'type' => $event['event_type'] ?? 'custom_schedule',
                        'note' => $note,
                    ]);
                } else {
                    // Create new event
                    CalendarSchedule::create([
                        'type' => $event['event_type'] ?? 'custom_schedule',
                        'date' => $event['start_time']->format('Y-m-d'),
                        'note' => $note,
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
     * Clean up past events from a specific feed
     */
    private function cleanupPastEvents(ICalFeed $feed): void
    {
        try {
            $today = Carbon::today();
            
            // Delete past events that were imported from this feed
            // Since we no longer store UID in notes, we'll clean up based on feed sync patterns
            $deletedCount = CalendarSchedule::where('user_id', $feed->user_id)
                ->where('date', '<', $today->format('Y-m-d'))
                ->where('type', 'custom_schedule') // Most imported events are custom_schedule type
                ->delete();

            if ($deletedCount > 0) {
                Log::info('Cleaned up past imported events', [
                    'feed_id' => $feed->id,
                    'feed_name' => $feed->name,
                    'deleted_count' => $deletedCount,
                    'cutoff_date' => $today->format('Y-m-d')
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Failed to cleanup past events', [
                'feed_id' => $feed->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    private function createEnhancedNote(array $event): string
    {
        $parts = [];
        
        // Add the enhanced summary
        if (!empty($event['summary'])) {
            $parts[] = $event['summary'];
        }
        
        // // Add enhanced description if it has meaningful content
        // if (!empty($event['description']) && $event['description'] !== $event['summary']) {
        //     $parts[] = $event['description'];
        // }
        
        // // Add location if available
        // if (!empty($event['location'])) {
        //     $parts[] = "📍 {$event['location']}";
        // }
        
        // // Add organizer if available
        // if (!empty($event['organizer'])) {
        //     $parts[] = "👤 {$event['organizer']}";
        // }
        
        // // Add categories if available
        // if (!empty($event['categories'])) {
        //     $parts[] = "🏷️ {$event['categories']}";
        // }
        
        // // Add status if meaningful
        // if (!empty($event['status']) && $event['status'] !== 'CONFIRMED') {
        //     $parts[] = "📊 {$event['status']}";
        // }
        
        // // Add availability status
        // if ($event['transp'] === 'TRANSPARENT') {
        //     $parts[] = "✅ Available";
        // } elseif ($event['transp'] === 'OPAQUE') {
        //     $parts[] = "🚫 Busy";
        // }
        
        // // Add UID for tracking
        // $parts[] = "[UID: {$event['uid']}]";
        
        return implode(' | ', $parts);
    }

    /**
     * Sync all feeds for a user
     */
    public function syncUserFeeds(User $user, bool $cleanup = true): array
    {
        $feeds = $user->icalFeeds()->active()->get();
        $results = [];

        foreach ($feeds as $feed) {
            if ($feed->needsSync()) {
                $results[$feed->id] = $this->syncFeed($feed, $cleanup);
            }
        }

        return $results;
    }

    /**
     * Sync all feeds that need syncing
     */
    public function syncAllFeeds(bool $cleanup = true): array
    {
        $feeds = ICalFeed::active()->needsSync()->get();
        $results = [];

        foreach ($feeds as $feed) {
            $results[$feed->id] = $this->syncFeed($feed, $cleanup);
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