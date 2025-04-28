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
        // Find bookings that ended exactly 24 hours ago and were accepted/completed
        $bookings = Booking::where('status', 'accepted')
            ->whereDate('book_date', Carbon::now()->subDay()->toDateString()) // start of the day time to 0000
            ->whereTime('book_date', '<=', Carbon::now()->subDay()->toTimeString()) // end of the day
            ->where('is_reviewed', 0)
            ->get();

        $count = 0;
        foreach ($bookings as $booking) {
            Mail::to($booking->user->email)->send(new GuestReviewMail($booking));
            $count++;
        }

        $this->info("Sent {$count} guest review emails.");
        return 0;
    }
} 