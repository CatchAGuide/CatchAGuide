<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('bookings:send-guide-reminders')->hourly();
        $schedule->command('update:booking-status')->hourly();
        $schedule->command('bookings:send-guest-reviews')->hourly();
        $schedule->command('bookings:send-guest-tour-reminders')->hourly();
        $schedule->command('bookings:send-guide-upcoming-tour-reminders')->hourly();
        $schedule->command('bookings:send-guide-reminders-12hrs')->hourly();
        
        // Update calendar schedules daily - extends availability for next 24 months and cleanup old entries
        // $schedule->command('migrate:calendar-schedule --months=24 --cleanup')->daily();
        
        // Generate guiding filter mappings every hour
        $schedule->command('guidings:generate-filters')
                ->hourly()
                ->withoutOverlapping()
                ->runInBackground();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
