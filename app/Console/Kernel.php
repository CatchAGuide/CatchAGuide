<?php

namespace App\Console;

use App\Services\ScheduledTaskService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * All tasks are configured in config/scheduled_tasks.php and managed from
     * Admin → Guiding attributes → Scheduled tasks.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('update:booking-status')->hourly();
        $schedule->command('bookings:send-guest-reviews')->hourly();
        $schedule->command('bookings:create-automatic-reviews')->dailyAt('02:15');
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

        // Purge threat_intelligence rows older than retention window (default 7 days)
        $schedule->command('threat-intelligence:cleanup')
                ->dailyAt('03:30')
                ->withoutOverlapping()
                ->runInBackground()
                ->appendOutputTo(storage_path('logs/threat-intelligence-cleanup.log'));
        
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

        // Finance: auto-send guide invoices after tour date (3/7/10-day retries)
        // $schedule->command('finance:auto-send-guide-invoices')
        //         ->dailyAt('03:10')
        //         ->withoutOverlapping()
        //         ->runInBackground()
        //         ->appendOutputTo(storage_path('logs/finance-invoices.log'));



        /// FOR ADMIN SIDE SCHEDULER CONFIGURATION
        // app(ScheduledTaskService::class)->register($schedule);
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
