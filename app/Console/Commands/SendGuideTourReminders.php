<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

use App\Models\Booking;
use App\Models\EmailLog;
use App\Mail\GuideReminder;

class SendGuideTourReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:send-guide-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminder emails to guides 24 hours before their booking requests expire (Legacy command - use bookings:send-guide-reminders-all instead)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Find bookings that will expire in 24 hours
        // (bookings that were created 24 hours ago and have an expiration of 48 hours)
        $now = Carbon::now();
        $twentyFourHoursLater = Carbon::now()->addHours(24);
        
        $query = Booking::where('status', 'pending')
            ->whereNotNull('expires_at')
            ->where('expires_at', '>', $now)
            ->where('expires_at', '<=', $twentyFourHoursLater);
        
        $bookings = $query->get();
        
        $count = 0;
        $skipped = 0;
        
        foreach ($bookings as $booking) {
            $guide = $booking->guiding->user;
            
            // Use the mailable's sendReminder method which includes duplicate checking
            if (GuideReminder::sendReminder($booking, $guide, 24)) {
                $this->info("Sent 24-hour guide reminder email to {$guide->email} for booking #{$booking->id}");
                $count++;
            } else {
                $this->info("Skipping duplicate 24-hour reminder for booking #{$booking->id} to {$guide->email}");
                $skipped++;
            }
        }

        $this->info("Sent {$count} 24-hour reminders to guides. Skipped {$skipped} duplicates.");
        return 0;
    }
} 