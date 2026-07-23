<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\Guest\GuestReviewMail;

class SendGuestReviews extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:send-guest-reviews';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send review emails to guests 24 hours after completed fishing tours';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Find bookings that occurred 24 hours ago and were accepted/completed
        $yesterday = Carbon::now()->subDay();
        $dayBeforeYesterday = Carbon::now()->subDays(2);
        
        $bookings = Booking::where('status', 'accepted')
            ->where('book_date', '>=', $dayBeforeYesterday)
            ->where('book_date', '<', $yesterday)
            ->where('is_reviewed', 0)
            ->get();
        
        $count = 0;
        foreach ($bookings as $booking) {
            $email = $booking->customerEmail();
            if (! $email) {
                $this->warn("Skipping booking #{$booking->id}: no customer email.");
                continue;
            }

            app()->setLocale($booking->customerLocale());

            if (! CheckEmailLog('guest_review', 'booking_' . $booking->id, $email)) {
                Mail::to($email)->send(new GuestReviewMail($booking));
                $count++;
            }
        }

        $this->info("Sent {$count} guest review emails.");
        return 0;
    }
} 