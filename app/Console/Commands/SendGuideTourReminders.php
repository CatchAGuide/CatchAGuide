<?php

namespace App\Console\Commands;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

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
    protected $description = 'Send reminder emails to guides 24 hours before their booking requests expire';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Find bookings that will expire in 24 hours
        // (bookings that were created 48 hours ago and have an expiration of 72 hours)
        $now = Carbon::now();
        $twentyFourHoursLater = Carbon::now()->addHours(24);
        
        $query = Booking::where('status', 'pending')
            ->whereNotNull('expires_at')
            ->where('expires_at', '>', $now)
            ->where('expires_at', '<=', $twentyFourHoursLater);
        
        $bookings = $query->get();
        
        $count = 0;
        foreach ($bookings as $booking) {
            // Get the guide from the guiding relationship
            $guide = $booking->guiding->user;
            
            // Define language and type for logging
            $language = $guide->language ?? config('app.locale');
            $type = 'guide_reminder';
            $target = 'booking_' . $booking->id;
            
            // Send the reminder email
            Mail::send('mails.guide.guide_reminder', [
                'guide' => $guide,
                'booking' => $booking,
                'guideName' => $guide->name,
                'language' => $language,
                'type' => $type,
                'target' => $target,
            ], function ($message) use ($guide, $language, $type, $target) {
                $message->to($guide->email)
                    ->subject(__('emails.guide_reminder_to_respond_24hrs_title'))
                    ->with([
                        'language' => $language,
                        'type' => $type,
                        'target' => $target,
                    ]);
            });
            
            // Log the email
            $this->info("Sent guide reminder email to {$guide->email} for booking #{$booking->id} in {$language}");
            
            $count++;
        }

        $this->info("Sent {$count} reminder emails to guides.");
        return 0;
    }
} 