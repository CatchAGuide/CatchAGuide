<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;

use App\Models\Booking;
use App\Mail\GuideReminder12Hours;

class SendGuideReminders12Hours extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:send-guide-reminders-12hrs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminders to guides 12 hours before booking requests expire';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Find bookings that will expire in 12 hours
        // Assuming expires_at is set to 72 hours after creation
        $bookingsToRemind = Booking::whereNotNull('expires_at')
            ->where('status', 'pending') // Adjust status as needed for your application
            ->where('expires_at', '>', Carbon::now())
            ->where('expires_at', '<=', Carbon::now()->addHours(12))
            ->get();

        $count = 0;
        $skipped = 0;
        
        foreach ($bookingsToRemind as $booking) {
            // Send reminder email to guide using the mailable's built-in duplicate check
            $guide = $booking->guiding->user;
            
            // Use the mailable's sendReminder method which includes duplicate checking
            if (GuideReminder12Hours::sendReminder($booking, $guide)) {
                $this->info("Sent 12-hour guide reminder email to {$guide->email} for booking #{$booking->id}");
                $count++;
            } else {
                $this->info("Skipping duplicate 12-hour reminder for booking #{$booking->id} to {$guide->email}");
                $skipped++;
            }
        }

        $this->info("Sent {$count} 12-hour reminders to guides. Skipped {$skipped} duplicates.");
        return 0;
    }
} 