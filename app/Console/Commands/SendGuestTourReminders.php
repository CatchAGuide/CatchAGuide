<?php

namespace App\Console\Commands;

use App\Mail\Guest\GuestTourReminderMail;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendGuestTourReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:send-guest-tour-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminder emails to guests 48 hours before their fishing tour';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $bookings = Booking::where('book_date', '>=', Carbon::now()->addDays(2)->startOfDay())
            ->where('book_date', '<', Carbon::now()->addDays(3)->startOfDay())
            ->where('status', 'accepted')
            ->get();

        $count = 0;
        foreach ($bookings as $booking) {
            try {
                // Send reminder email
                Mail::to($booking->is_guest ? $booking->email : $booking->user->email)
                    ->send(new GuestTourReminderMail($booking));
                
                $count++;
                $this->info("Sent reminder for booking #{$booking->id}");
            } catch (\Exception $e) {
                $this->error("Failed to send reminder for booking #{$booking->id}: {$e->getMessage()}");
            }
        }

        $this->info("Sent {$count} tour reminder emails");
        return 0;
    }
} 