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
        // $schedule->command('bookings:send-guide-upcoming-tour-reminders')->hourly();
        // $schedule->command('bookings:send-guide-reminders-12hrs')->hourly();
      
        // Generate guiding filter mappings every hour
        $schedule->command('guidings:generate-filters')
                ->hourly()
                ->withoutOverlapping()
                ->runInBackground();
                
        // Warm file existence cache every 2 hours
        $schedule->command('cache:warm-files')
                ->everyTwoHours()
                ->withoutOverlapping()
                ->runInBackground();
                
        $schedule->command('generate:sitemap')->daily()->runInBackground();
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
