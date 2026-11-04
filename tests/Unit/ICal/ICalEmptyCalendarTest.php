<?php

namespace Tests\Unit\ICal;

use App\Models\ICalFeed;
use App\Services\ICalService;
use ReflectionMethod;
use Tests\TestCase;

/**
 * Production log, 4 Nov 2026: a guide's feed with no bookings failed every sync with
 * "foreach() argument must be of type array|object, null given". A calendar without any
 * VEVENT is valid and must parse to zero events.
 */
class ICalEmptyCalendarTest extends TestCase
{
    public function test_a_calendar_without_events_parses_to_no_events(): void
    {
        $content = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//Test//EN\r\nEND:VCALENDAR\r\n";

        $this->assertSame([], $this->parse($content));
    }

    public function test_a_calendar_with_a_future_event_still_parses(): void
    {
        $start = now()->addDays(10)->format('Ymd');
        $end = now()->addDays(11)->format('Ymd');
        $content = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//Test//EN\r\n"
            ."BEGIN:VEVENT\r\nUID:test-1\r\nDTSTART;VALUE=DATE:{$start}\r\nDTEND;VALUE=DATE:{$end}\r\nSUMMARY:Booked\r\nEND:VEVENT\r\n"
            ."END:VCALENDAR\r\n";

        $this->assertCount(1, $this->parse($content));
    }

    private function parse(string $content): array
    {
        $feed = new ICalFeed(['name' => 'Test feed', 'feed_url' => 'https://calendar.test/feed.ics']);
        $feed->id = 0;

        $method = new ReflectionMethod(ICalService::class, 'parseICalContent');

        return $method->invoke(app(ICalService::class), $content, $feed);
    }
}
