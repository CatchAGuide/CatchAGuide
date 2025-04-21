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
        $schedule->command('bookings:send-guide-reminders')->dailyAt('06:00');
        $schedule->command('update:booking-status')->dailyAt('06:00');
        // $schedule->command('bookings:send-guest-reviews')->dailyAt('06:00');
        $schedule->command('bookings:send-guest-tour-reminders')->dailyAt('06:00');
        $schedule->command('bookings:send-guide-upcoming-tour-reminders')->dailyAt('06:00');
        // $schedule->command('bookings:send-guide-reminders-12hrs')->hourly('06:00');
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
