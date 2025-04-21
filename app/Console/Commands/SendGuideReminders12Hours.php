<?php

namespace App\Console\Commands;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

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
        foreach ($bookingsToRemind as $booking) {
            // Send reminder email to guide
            $guide = $booking->guiding->user;
            
            // Create a mailable class for the 12-hour reminder
            Mail::send(new \App\Mail\GuideReminder12Hours($booking, $guide));
            
            $count++;
        }

        $this->info("Sent {$count} 12-hour reminders to guides.");
        return 0;
    }
} 