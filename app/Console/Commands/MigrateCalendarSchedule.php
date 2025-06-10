<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\CalendarSchedule;
use App\Models\Guiding;
use App\Models\Booking;
use App\Services\CalendarScheduleService;
use Carbon\Carbon;

/**
 * Calendar Schedule Migration Command
 * 
 * This command handles two main functions:
 * 1. Creates "blocked" calendar entries (tour_schedule) for dates when guidings are NOT available
 *    based on their month and weekday availability settings
 * 2. Migrates existing bookings to calendar entries (tour_request)
 *    - Handles both regular User bookings and UserGuest bookings
 *    - Preserves user_id but note field indicates which table it refers to
 *    - Regular bookings: user_id → users table
 *    - Guest bookings: user_id → user_guests table
 * 
 * Usage Examples:
 * - php artisan migrate:calendar-schedule                    (Generate 24 months of blocked schedules)
 * - php artisan migrate:calendar-schedule --months=12        (Generate 12 months ahead)
 * - php artisan migrate:calendar-schedule --bookings         (Only migrate existing bookings)
 * - php artisan migrate:calendar-schedule --bookings --force (Re-migrate all bookings)
 * 
 * Future-Proof Features:
 * - Automatically scheduled to run daily via Kernel.php
 * - BookingObserver automatically creates calendar entries for new bookings
 * - Avoids duplicates by checking existing data
 * - Uses --force flag to regenerate if needed
 */

class MigrateCalendarSchedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:calendar-schedule 
                            {--months=24 : Number of months to generate ahead (default: 24)}
                            {--bookings : Also migrate existing bookings}
                            {--force : Force regenerate even if data exists}
                            {--cleanup : Remove old calendar entries older than 30 days}
                            {--debug : Show detailed debug information}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate calendar schedule - creates blocked dates based on guiding availability and optionally migrates bookings';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting calendar schedule migration...');
        
        // Migrate existing bookings if requested
        if ($this->option('bookings')) {
            $this->info('Migrating existing bookings...');
            $this->migrateBookings();
        }
        
        // Create blocked schedules
        $this->info('Creating blocked schedules based on guiding availability...');
        $this->createBlockedSchedules();
        
        // Cleanup old entries if requested
        if ($this->option('cleanup')) {
            $this->info('Cleaning up old calendar entries...');
            $this->cleanupOldEntries();
        }
        
        $this->info('Calendar schedule migration completed!');
        return 0;
    }

    private function createBlockedSchedules()
    {
        $guidings = Guiding::all();
        $startDate = now();
        $months = $this->option('months');
        $endDate = now()->addMonths($months);
        
        $this->info("Processing {$guidings->count()} guidings for {$months} months ahead...");
        
        foreach($guidings as $guiding) {
            $this->processGuidingSchedule($guiding, $months);
        }
    }

    private function processGuidingSchedule($guiding, $monthsAhead)
    {
        $availableMonths = json_decode($guiding->months, true) ?? [];
        $availableWeekdays = json_decode($guiding->weekdays, true) ?? [];
        
        // Use the service to generate calendar schedules
        CalendarScheduleService::generateCalendarSchedulesForGuiding(
            $guiding, 
            $availableMonths, 
            $availableWeekdays, 
            $monthsAhead
        );
        
        $this->info("Processed guiding: {$guiding->title}");
    }
    
    private function blockAllDatesForGuiding($guiding, $startDate, $endDate)
    {
        $current = $startDate->copy();
        
        while ($current->lte($endDate)) {
            $this->createBlockedSchedule($guiding, $current->format('Y-m-d'));
            $current->addDay();
        }
    }
    
    private function createBlockedSchedule($guiding, $date)
    {
        // Check if schedule already exists for this date and guiding
        $existingSchedule = CalendarSchedule::where('guiding_id', $guiding->id)
            ->where('date', $date)
            ->where('type', 'tour_schedule')
            ->first();
            
        if (!$existingSchedule || $this->option('force')) {
            if ($existingSchedule && $this->option('force')) {
                $existingSchedule->delete();
            }
            
            CalendarSchedule::create([
                'type' => 'tour_schedule',
                'date' => $date,
                'note' => 'Blocked - Not available based on guiding availability settings',
                'guiding_id' => $guiding->id,
                'user_id' => $guiding->user_id,
                'vacation_id' => null,
                'booking_id' => null,
            ]);
        }
    }

    private function migrateBookings()
    {
        $bookings = Booking::whereNotNull('book_date')->get();
        $userBookings = $bookings->where('is_guest', false)->count();
        $guestBookings = $bookings->where('is_guest', true)->count();
        
        $this->info("Processing {$bookings->count()} bookings...");
        $this->info("- Regular user bookings: {$userBookings}");
        $this->info("- Guest bookings: {$guestBookings}");
        
        $processedCount = 0;
        foreach($bookings as $booking) {
            $this->createBookingSchedule($booking);
            $processedCount++;
            
            // Show progress every 100 bookings
            if ($processedCount % 100 === 0) {
                $this->info("Processed {$processedCount}/{$bookings->count()} bookings...");
            }
        }
        
        $this->info("Finished processing all {$bookings->count()} bookings.");
    }
    
    private function createBookingSchedule($booking)
    {
        // Check if schedule already exists for this booking
        $existingSchedule = CalendarSchedule::where('booking_id', $booking->id)
            ->where('type', 'tour_request')
            ->first();
            
        if (!$existingSchedule || $this->option('force')) {
            if ($existingSchedule && $this->option('force')) {
                $existingSchedule->delete();
            }
            
            // The booking->user relationship automatically handles User vs UserGuest based on is_guest field
            $userId = $booking->user_id;
            $note = 'Booking request for ' . $booking->count_of_users . ' person(s)';
            
            try {
                $user = $booking->user; // Automatically gets User or UserGuest model based on is_guest
                if ($user) {
                    $userType = $booking->is_guest ? 'Guest' : 'User';
                    $tableRef = $booking->is_guest ? 'user_guests' : 'users';
                    $note .= " ({$userType}: {$user->firstname} {$user->lastname} - {$user->email}) [{$tableRef}.id={$booking->user_id}]";
                } else {
                    $userType = $booking->is_guest ? 'Guest' : 'User';
                    $tableRef = $booking->is_guest ? 'user_guests' : 'users';
                    $note .= " ({$userType} not found) [{$tableRef}.id={$booking->user_id}]";
                }
            } catch (\Exception $e) {
                $userType = $booking->is_guest ? 'Guest' : 'User';
                $tableRef = $booking->is_guest ? 'user_guests' : 'users';
                $note .= " ({$userType} error: {$e->getMessage()}) [{$tableRef}.id={$booking->user_id}]";
            }
            
            $calendarSchedule = CalendarSchedule::create([
                'type' => 'tour_request',
                'date' => Carbon::parse($booking->book_date)->format('Y-m-d'),
                'note' => $note,
                'guiding_id' => $booking->guiding_id,
                'user_id' => $userId, // null for guests, user_id for regular users
                'vacation_id' => null,
                'booking_id' => $booking->id,
            ]);

            $booking->blocked_event_id = $calendarSchedule->id;
            $booking->save();

            $bookingType = $booking->is_guest ? 'Guest' : 'User';
            $this->info("Created schedule for {$bookingType} booking ID: {$booking->id}");
            
            if ($this->option('debug')) {
                $this->line("  - Date: {$booking->book_date}");
                $this->line("  - User ID: " . ($userId ?? 'null'));
                $this->line("  - Booking User ID: {$booking->user_id}");
                $this->line("  - Is Guest: " . ($booking->is_guest ? 'true' : 'false'));
            }
        }
    }
    
    private function cleanupOldEntries()
    {
        $deletedCount = CalendarScheduleService::cleanupOldEntries(30);
        $this->info("Cleaned up {$deletedCount} old blocked schedule entries.");
    }
}
