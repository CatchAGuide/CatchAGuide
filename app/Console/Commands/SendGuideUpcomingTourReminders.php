<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use App\Models\Guide;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\Guide\GuideUpcomingTourMail;

class SendGuideUpcomingTourReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:send-guide-upcoming-tour-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminder emails to guides 48 hours before their booked fishing tours';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Sending guide upcoming tour reminders...');
        
        // Find bookings that are scheduled to happen in 48 hours and have been accepted
        $targetDate = Carbon::now()->addHours(48)->toDateString();
        
        $bookings = Booking::where('status', 'accepted')
            ->whereDate('book_date', $targetDate)
            ->get();
            
        $this->info("Found {$bookings->count()} upcoming tours to send reminders for.");
        
        foreach ($bookings as $booking) {
            $guide = $booking->guiding;
            
            if ($guide) {
                $this->info("Sending reminder to guide {$guide->user->firstname} {$guide->user->lastname} for booking #{$booking->id}");
                
                try {
                    if (!CheckEmailLog('guide_upcoming_tour_reminder', 'booking_' . $booking->id, $guide->user->email)) {
                        Mail::to($guide->user->email)->send(new GuideUpcomingTourMail($guide, $booking));
                        $this->info("Reminder sent successfully.");
                    }
                } catch (\Exception $e) {
                    $this->error("Failed to send reminder: {$e->getMessage()}");
                }
            }
        }
        
        $this->info('Guide upcoming tour reminders process completed.');
        return 0;
    }
} 