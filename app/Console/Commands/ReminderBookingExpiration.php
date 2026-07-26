<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

use App\Models\Booking;

class ReminderBookingExpiration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:bookreminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send 24h expiry reminders to guides for pending booking requests';

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
        $nearExpireBookings = Booking::with(['guiding.user', 'registeredUser', 'guestUser'])
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->get();

        foreach ($nearExpireBookings as $booking) {
            $timeDifferenceHours = now()->diffInHours($booking->expires_at);

            if ($timeDifferenceHours != 24) {
                continue;
            }

            // Resolve customer via is_guest (users vs user_guests) — never User::find(user_id) alone
            $user = $booking->user;
            $guiding = $booking->guiding;
            $guide = $guiding?->user;

            if (!$user || !$guiding || !$guide) {
                Log::warning('run:bookreminders skipped — missing customer/guide', [
                    'booking_id' => $booking->id,
                    'is_guest' => (bool) $booking->is_guest,
                    'user_id' => $booking->user_id,
                ]);
                continue;
            }

            $language = $guide->language ?? config('app.locale');
            $type = 'guide_reminder';
            $target = 'booking_' . $booking->id;

            app()->setLocale($language);

            if (!CheckEmailLog('guide_reminder', $target, $guide->email)) {
                Mail::send('mails.guide.guide_reminder', [
                    'guide' => $guide,
                    'booking' => $booking,
                    'user' => $user,
                    'guiding' => $guiding,
                    'guideName' => $guide->firstname ?? $guide->name,
                    'language' => $language,
                    'type' => $type,
                    'target' => $target,
                ], function ($message) use ($guide) {
                    $message->to($guide->email)
                        ->subject(__('emails.guide_reminder_to_respond_24hrs_title'));
                });
            }
        }

        return 0;
    }
}
