<?php

namespace App\Console\Commands;

use Mail;
use Illuminate\Console\Command;

use App\Models\User;
use App\Models\Booking;
use App\Models\Guiding;
use App\Models\EmailLog;
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
        $expiredBookings = Booking::where('status', 'pending')
            ->where('expires_at', '<', now())
            ->get();

        foreach ($expiredBookings as $booking) {
            $user = User::where('id',$booking->user_id)->first();
            $guiding = Guiding::where('id',$booking->guiding_id)->first();
            $guide = $guiding->user;
            
            $booking->status = 'cancelled';
            $booking->save();

            // Send an email notification to the guest and guide
            if (!CheckEmailLog('guest_booking_expired', 'booking_' . $booking->id, $user->email)) {
                Mail::to($user->email)->locale($user->language ?? app()->getLocale())->send(new GuestBookingExpiredMail($booking,$user,$guiding,$guide));
            }

            if (!CheckEmailLog('guide_booking_expired', 'booking_' . $booking->id, $guide->email)) {
                Mail::to($guide->email)->locale($guide->language ?? app()->getLocale())->send(new GuideBookingExpiredMail($booking,$user,$guiding,$guide));
            }

            if (!CheckEmailLog('booking_expire_to_ceo', 'booking_' . $booking->id, env('TO_CEO','info@catchaguide.com'))) {
                Mail::to(env('TO_CEO','info@catchaguide.com'))->locale('de')->send(new BookingExpireMailToCEO($booking,$user,$guiding,$guide));
            }
        }
    }
}
