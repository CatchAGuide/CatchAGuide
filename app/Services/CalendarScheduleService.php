<?php

namespace App\Services;

use App\Models\CalendarSchedule;
use App\Models\BlockedEvent;
use App\Models\Guiding;
use Carbon\Carbon;

class CalendarScheduleService
{
    /**
     * Generate comprehensive calendar schedules for a guiding based on availability settings
     * This creates blocked dates for unavailable months and weekdays
     * 
     * @param Guiding $guiding
     * @param array $availableMonths
     * @param array $availableWeekdays
     * @param int $monthsAhead
     * @return void
     */
    public static function generateCalendarSchedulesForGuiding(
        Guiding $guiding, 
        array $availableMonths, 
        array $availableWeekdays, 
        int $monthsAhead = 24
    ): void {
        // Validate that the guiding has an ID
        if (!$guiding->id) {
            \Log::error('CalendarScheduleService: Guiding ID is null or empty', [
                'guiding' => $guiding->toArray(),
                'availableMonths' => $availableMonths,
                'availableWeekdays' => $availableWeekdays
            ]);
            return;
        }

        $startDate = Carbon::now();
        $endDate = Carbon::now()->addMonths($monthsAhead);
        
        $current = $startDate->copy();
        
        while ($current->lte($endDate)) {
            $currentMonth = strtolower($current->format('F')); // january, february, etc.
            $currentWeekday = strtolower($current->format('l')); // monday, tuesday, etc.
            
            // Check month availability
            $monthAvailable = true;
            if (!empty($availableMonths)) {
                $monthAvailable = in_array($currentMonth, $availableMonths);
            }
            
            // Check weekday availability
            $weekdayAvailable = true;
            if (!empty($availableWeekdays)) {
                $weekdayAvailable = in_array($currentWeekday, $availableWeekdays);
            }
            
            // Block if EITHER month is unavailable OR weekday is unavailable
            // (Date is only available if BOTH month AND weekday are available)
            $shouldBlock = !$monthAvailable || !$weekdayAvailable;
            
            // Create blocked schedule if needed
            if ($shouldBlock) {
                CalendarSchedule::updateOrCreate(
                    [
                        'guiding_id' => $guiding->id,
                        'date' => $current->format('Y-m-d'),
                        'type' => 'tour_schedule'
                    ],
                    [
                        'note' => 'Blocked - Not available based on guiding availability settings',
                        'user_id' => $guiding->user_id,
                        'vacation_id' => null,
                        'booking_id' => null,
                    ]
                );
            }
            
            $current->addDay();
        }
    }

    /**
     * Create BlockedEvent entries for unavailable months (for backward compatibility)
     * 
     * @param Guiding $guiding
     * @param array $selectedMonths
     * @return void
     */
    public static function createBlockedEventsForUnavailableMonths(Guiding $guiding, array $selectedMonths): void
    {
        // Validate that the guiding has an ID
        if (!$guiding->id) {
            \Log::error('CalendarScheduleService: Guiding ID is null for BlockedEvent creation', [
                'guiding' => $guiding->toArray(),
                'selectedMonths' => $selectedMonths
            ]);
            return;
        }

        $allMonths = ['january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december'];

        foreach ($allMonths as $index => $month) {
            if (!in_array($month, $selectedMonths)) {
                $year = date('Y');
                $monthNumber = str_pad($index + 1, 2, '0', STR_PAD_LEFT);
                $currentMonth = date('m');

                if ($monthNumber < $currentMonth) {
                    $year++;
                }

                $blockedFrom = date('Y-m-d', strtotime("$year-$monthNumber-01"));
                $blockedTo = date('Y-m-t', strtotime($blockedFrom));

                BlockedEvent::updateOrCreate(
                    [
                        'user_id' => $guiding->user_id,
                        'guiding_id' => $guiding->id,
                        'type' => 'blockiert',
                        'from' => $blockedFrom,
                    ],
                    [
                        'due' => $blockedTo,
                    ]
                );
            }
        }
    }

    /**
     * Clean up old calendar schedules and blocked events for a guiding
     * 
     * @param Guiding $guiding
     * @return void
     */
    public static function cleanupOldSchedules(Guiding $guiding): void
    {
        // Validate that the guiding has an ID
        if (!$guiding->id) {
            \Log::error('CalendarScheduleService: Guiding ID is null for cleanup', [
                'guiding' => $guiding->toArray()
            ]);
            return;
        }

        // Clean up old blocked events
        BlockedEvent::where('guiding_id', $guiding->id)
            ->where('type', 'blockiert')
            ->delete();
            
        // Clean up old calendar schedules
        CalendarSchedule::where('guiding_id', $guiding->id)
            ->where('type', 'tour_schedule')
            ->delete();
    }

    /**
     * Complete calendar schedule generation including cleanup
     * 
     * @param Guiding $guiding
     * @param array $availableMonths
     * @param array $availableWeekdays
     * @param bool $shouldCleanup
     * @param int $monthsAhead
     * @return void
     */
    public static function generateCompleteSchedule(
        Guiding $guiding,
        array $availableMonths,
        array $availableWeekdays,
        bool $shouldCleanup = true,
        int $monthsAhead = 24
    ): void {
        if ($shouldCleanup) {
            self::cleanupOldSchedules($guiding);
        }

        // Generate comprehensive calendar schedules
        self::generateCalendarSchedulesForGuiding($guiding, $availableMonths, $availableWeekdays, $monthsAhead);
        
        // Also maintain the existing BlockedEvent creation for backward compatibility
        self::createBlockedEventsForUnavailableMonths($guiding, $availableMonths);
    }

    /**
     * Clean up old calendar schedule entries (for maintenance)
     * 
     * @param int $daysOld
     * @return int Number of deleted entries
     */
    public static function cleanupOldEntries(int $daysOld = 30): int
    {
        $cutoffDate = Carbon::now()->subDays($daysOld)->format('Y-m-d');
        
        // Only cleanup tour_schedule entries (blocked dates), keep tour_request (bookings) for historical data
        return CalendarSchedule::where('type', 'tour_schedule')
            ->where('date', '<', $cutoffDate)
            ->delete();
    }
} 