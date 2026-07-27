<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

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
        // expires_at is typically 24–48 hours after creation depending on lead time
        $bookingsToRemind = Booking::with(['guiding.user'])
            ->whereNotNull('expires_at')
            ->where('status', 'pending')
            ->where('expires_at', '>', Carbon::now())
            ->where('expires_at', '<=', Carbon::now()->addHours(12))
            ->get();

        $count = 0;
        $skipped = 0;

        foreach ($bookingsToRemind as $booking) {
            try {
                $guide = $booking->guiding?->user;
                if (! $guide || ! $guide->email) {
                    $this->warn("Skipping booking #{$booking->id}: missing guide.");
                    $skipped++;
                    continue;
                }

                if (GuideReminder12Hours::sendReminder($booking, $guide)) {
                    $this->info("Sent 12-hour guide reminder email to {$guide->email} for booking #{$booking->id}");
                    $count++;
                } else {
                    $this->info("Skipping duplicate 12-hour reminder for booking #{$booking->id} to {$guide->email}");
                    $skipped++;
                }
            } catch (\Throwable $e) {
                Log::error('bookings:send-guide-reminders-12hrs failed', [
                    'booking_id' => $booking->id,
                    'error' => $e->getMessage(),
                ]);
                $this->error("Failed to send 12h reminder for booking #{$booking->id}: {$e->getMessage()}");
            }
        }

        $this->info("Sent {$count} 12-hour reminders to guides. Skipped {$skipped} duplicates/missing.");
        return 0;
    }
}
