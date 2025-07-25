<?php

namespace App\Services;

use App\Models\BlockedEvent;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use App\Models\CalendarSchedule;
use Illuminate\Support\Facades\Log;

class EventService {

    public function getAvailableEvents($duration, $date, User $user)
    {
        // one Event every Day
        $range = $this->range($lower = 0, $upper = 64800, $step = 64800, $format = 'H:i');

        return $range;
    }

    public function getEndOfEvent($duration, $dateStr)
    {
        $date = Carbon::parse($dateStr);

        $hours = floor($duration);
        $minutes = round(($duration - $hours) * 60);
        $timeInMinutes = 1439; #Only full day meetings

        return $date->addMinutes($timeInMinutes)->format('H:i');
    }

    public function createBlockedEvent($from, $date, $guiding, $type = 'booking')
    {
        $from .= ' ' . $date;
        $due = $this->getEndOfEvent($guiding->duration, $from) . ' ' . $date;

        $from = Carbon::parse($from);
        $due = Carbon::parse($due);

        BlockedEvent::create([
            'from' => $from,
            'due' => $due,
            'source' => 'global',
            'type' => 'booking',
            'user_id' => $guiding->user_id,
            'guiding_id' => $guiding->id ?? null
        ]);

        $checkSchedule = CalendarSchedule::where('date', $date)->where('guiding_id', $guiding->id)->where('user_id', $guiding->user->id)->first();
        if (!$checkSchedule) {
            return CalendarSchedule::create([
                'type' => $type,
                'date' => $date,
                'note' => 'Booking request',
                'guiding_id' => $guiding->id,
                'user_id' => $guiding->user->id
            ]);
        } else {
            return $checkSchedule;
        }
    }

    /**
     * @param int $lower
     * @param int $upper
     * @param int $step
     * @param string $format
     * @return array
     * @throws Exception
     */
    private function range(int $lower = 0, int $upper = 86400, int $step = 3600, string $format = ''): array
    {
        $times = array();

        if ( empty( $format ) ) {
            $format = 'g:i a';
        }

        foreach ( range( $lower, $upper, $step ) as $increment ) {
            $increment = gmdate( 'H:i', $increment );

            list( $hour, $minutes ) = explode( ':', $increment );

            $date = new \DateTime( $hour . ':' . $minutes );

            $times[(string) $increment] = $date->format( $format );
        }

        return $times;
    }

}
