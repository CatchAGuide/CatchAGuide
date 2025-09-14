<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

use App\Models\Booking;
use App\Mail\GuideReminder;

class SendGuideReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:send-guide-reminders-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminder emails to guides at all configured time periods before booking requests expire';

    /**
     * Reminder time periods in hours
     *
     * @var array
     */
    protected $reminderPeriods;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Load reminder periods from config
        $this->reminderPeriods = config('booking.reminder_periods', [24, 12, 6, 3, 1]);
        
        $totalSent = 0;
        $totalSkipped = 0;

        foreach ($this->reminderPeriods as $hours) {
            $this->info("Processing {$hours}-hour reminders...");
            
            $result = $this->processRemindersForPeriod($hours);
            $totalSent += $result['sent'];
            $totalSkipped += $result['skipped'];
        }

        $this->info("Total: Sent {$totalSent} reminders, skipped {$totalSkipped} duplicates across all periods.");
        return 0;
    }

    /**
     * Process reminders for a specific time period
     *
     * @param int $hours
     * @return array
     */
    private function processRemindersForPeriod(int $hours): array
    {
        $now = Carbon::now();
        
        // Create a precise time window for this specific reminder period
        // Only send reminders when the booking is EXACTLY within the target time range
        // This prevents duplicate emails when the command runs multiple times
        // 
        // Example for 12h reminder:
        // - windowStart: now + 11 hours (booking expires in 11-12 hours)
        // - windowEnd: now + 12 hours (booking expires in 11-12 hours)
        // This ensures 12h reminder is sent only once when booking is 11-12 hours from expiration
        $windowStart = $now->copy()->addHours($hours - 1);
        $windowEnd = $now->copy()->addHours($hours);
        
        // Get bookings that will expire within the precise time window
        $bookings = Booking::where('status', 'pending')
            ->whereNotNull('expires_at')
            ->where('expires_at', '>', $windowStart)
            ->where('expires_at', '<=', $windowEnd)
            ->with(['guiding.user'])
            ->get();

        $sent = 0;
        $skipped = 0;

        foreach ($bookings as $booking) {
            $guide = $booking->guiding->user;
            
            // Additional safety check: Ensure booking is old enough to warrant this reminder
            // This prevents sending reminders too early after booking creation
            $bookingAge = $now->diffInHours($booking->created_at);
            $maxExpirationHours = config('booking.extended_expiration_hours', 48);
            if ($bookingAge < ($maxExpirationHours - $hours)) {
                $this->info("Skipping {$hours}-hour reminder for booking #{$booking->id} - booking too new (age: {$bookingAge}h)");
                $skipped++;
                continue;
            }
            
            // Double-check email log to prevent race conditions using new naming convention
            $emailType = config('booking.reminder_email_type_prefix', 'guide_booking_reminder_') . $hours;
            $target = 'booking_' . $booking->id;
            
            if (CheckEmailLog($emailType, $target, $guide->email)) {
                $this->info("Skipping duplicate {$hours}-hour reminder for booking #{$booking->id} to {$guide->email}");
                $skipped++;
                continue;
            }

            // Send the reminder email
            if (GuideReminder::sendReminder($booking, $guide, $hours)) {
                $this->info("Sent {$hours}-hour guide reminder email to {$guide->email} for booking #{$booking->id}");
                $sent++;
            } else {
                $this->info("Failed to send {$hours}-hour reminder for booking #{$booking->id} to {$guide->email}");
                $skipped++;
            }
        }

        $this->info("{$hours}-hour period: Sent {$sent} reminders, skipped {$skipped} duplicates.");
        return ['sent' => $sent, 'skipped' => $skipped];
    }
}
