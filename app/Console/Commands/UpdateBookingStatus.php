<?php

namespace App\Console\Commands;

use Mail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

use App\Models\Booking;
use App\Mail\Ceo\BookingExpireMailToCEO;
use App\Mail\Guest\GuestBookingExpiredMail;
use App\Mail\Guide\GuideBookingExpiredMail;

class UpdateBookingStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:booking-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Booking Status';

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
        $expiredBookings = Booking::with(['guiding.user', 'registeredUser', 'guestUser'])
            ->where('status', 'pending')
            ->where('expires_at', '<', now())
            ->get();

        foreach ($expiredBookings as $booking) {
            // Resolve via is_guest: users.id OR user_guests.id — never User::find(user_id) alone
            $user = $booking->user;
            $guiding = $booking->guiding;
            $guide = $guiding?->user;

            $booking->status = 'cancelled';
            $booking->save();

            if (!$user || !$guiding || !$guide) {
                Log::warning('update:booking-status skipped emails — missing customer/guide', [
                    'booking_id' => $booking->id,
                    'is_guest' => (bool) $booking->is_guest,
                    'user_id' => $booking->user_id,
                    'has_user' => (bool) $user,
                    'has_guiding' => (bool) $guiding,
                    'has_guide' => (bool) $guide,
                ]);
                continue;
            }

            $guestEmail = $booking->customerEmail();
            $guestLocale = $booking->customerLocale();

            if ($guestEmail && !CheckEmailLog('guest_booking_expired', 'booking_' . $booking->id, $guestEmail)) {
                Mail::to($guestEmail)->locale($guestLocale)->send(new GuestBookingExpiredMail($booking, $user, $guiding, $guide));
            }

            if (!CheckEmailLog('guide_booking_expired', 'booking_' . $booking->id, $guide->email)) {
                Mail::to($guide->email)->locale($guide->language ?? app()->getLocale())->send(new GuideBookingExpiredMail($booking, $user, $guiding, $guide));
            }

            // Constructor order: ($booking, $guiding, $guide, $user)
            if (!CheckEmailLog('booking_expire_to_ceo', 'booking_' . $booking->id, config('mail.admin_email'))) {
                Mail::to(config('mail.admin_email'))->locale('de')->send(new BookingExpireMailToCEO($booking, $guiding, $guide, $user));
            }
        }

        return 0;
    }
}
