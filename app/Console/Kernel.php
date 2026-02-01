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
        $schedule->command('update:booking-status')->hourly();
        $schedule->command('bookings:send-guest-reviews')->hourly();
        $schedule->command('bookings:send-guest-tour-reminders')->hourly();
        $schedule->command('bookings:send-guide-reminders')->hourly();
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
                
        // Image cleanup: report + fix DB refs to missing files (no orphan deletion by default)
        // $schedule->command('images:cleanup --report-only')
        //         ->weeklyOn(0, '03:00')
        //         ->appendOutputTo(storage_path('logs/image-cleanup.log'));
        // To also fix DB: add --fix-db --no-dry-run. To delete orphans: --delete-orphans --backup --no-dry-run
                
        $schedule->command('generate:sitemap')->daily()->runInBackground();
        
        // Process vacation translations for admin changes daily (defaults to EN and DE languages)
        // $schedule->command('vacation:translate --admin-changes --relations')
        //         ->daily()
        //         ->withoutOverlapping()
        //         ->runInBackground();

        // $schedule->command('guiding:translate --detect-language')
        //                 ->daily()
        //                 ->withoutOverlapping()
        //                 ->runInBackground();

        // iCal Feed Sync Scheduling
        // Sync all iCal feeds every 2 hours (adjust frequency as needed)
        $schedule->command('ical:sync-feeds')
                ->everyTwoHours()
                ->withoutOverlapping()
                ->runInBackground()
                ->appendOutputTo(storage_path('logs/ical-sync.log'));
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
