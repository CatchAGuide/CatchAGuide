<?php

namespace App\Services\GuestBooking;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/**
 * "Add to calendar" for one booking: a single all-day event (bookings store dates, not times),
 * spanning every day of a multi-day tour, camp stay or trip.
 */
class GuestBookingCalendar
{
    public function ics(string $uid, string $title, CarbonInterface $from, CarbonInterface $to, string $location = '', string $description = '', string $url = ''): string
    {
        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//CatchAGuide//Booking//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'BEGIN:VEVENT',
            'UID:'.$uid.'@catchaguide',
            'DTSTAMP:'.Carbon::now('UTC')->format('Ymd\THis\Z'),
            'DTSTART;VALUE=DATE:'.$from->format('Ymd'),
            // DTEND of an all-day event is exclusive: the day after the last day.
            'DTEND;VALUE=DATE:'.$to->copy()->addDay()->format('Ymd'),
            'SUMMARY:'.$this->escape($title),
        ];
        if ($location !== '') {
            $lines[] = 'LOCATION:'.$this->escape($location);
        }
        if ($description !== '') {
            $lines[] = 'DESCRIPTION:'.$this->escape($description);
        }
        if ($url !== '') {
            $lines[] = 'URL:'.$url;
        }
        $lines[] = 'END:VEVENT';
        $lines[] = 'END:VCALENDAR';

        return implode("\r\n", array_map($this->fold(...), $lines))."\r\n";
    }

    private function escape(string $value): string
    {
        $value = str_replace(["\r\n", "\r"], "\n", $value);

        return str_replace(['\\', ';', ',', "\n"], ['\\\\', '\\;', '\\,', '\\n'], $value);
    }

    /** RFC 5545: lines longer than 75 octets continue on the next line after a space. */
    private function fold(string $line): string
    {
        if (strlen($line) <= 75) {
            return $line;
        }

        $out = [];
        $current = '';
        foreach (mb_str_split($line) as $char) {
            if (strlen($current) + strlen($char) > ($out === [] ? 75 : 74)) {
                $out[] = $current;
                $current = '';
            }
            $current .= $char;
        }
        $out[] = $current;

        return implode("\r\n ", $out);
    }
}
