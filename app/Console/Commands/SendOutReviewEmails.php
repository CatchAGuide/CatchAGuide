<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\Guest\GuestReviewMail;

class SendOutReviewEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:send-review-emails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send out review emails to users since January 2025';

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
        // Find bookings that occurred 24 hours ago, were accepted/completed,
        // and have a book_date from January 2025 onwards
        $yesterday = Carbon::now()->subDay();
        $dayBeforeYesterday = Carbon::now()->subDays(2);
        
        $bookings = Booking::where('status', 'accepted')
            ->where('book_date', '>=', $dayBeforeYesterday)
            ->where('book_date', '<', $yesterday)
            ->where('is_reviewed', 0)
            ->where('book_date', '>=', '2025-01-01')
            ->get();
        
        $count = 0;
        foreach ($bookings as $booking) {
            app()->setLocale($booking?->user?->language ?? app()->getLocale());

            if (!CheckEmailLog('guest_review', 'booking_' . $booking->id, $booking->user->email)) {
                Mail::to($booking->user->email)->send(new GuestReviewMail($booking));
                $count++;
            }
        }

        $this->info("Sent {$count} guest review emails.");
        return 0;
    }
}
