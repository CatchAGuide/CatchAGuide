<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use App\Mail\Guest\GuestBookingExpiredMail;
use App\Mail\Guide\GuideBookingExpiredMail;
use App\Mail\Ceo\BookingExpireMailToCEO;
use Mail;
use App\Models\User;
use App\Models\Guiding;

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
            $booking->status = 'cancelled';
            $booking->save();

            $user = User::where('id',$booking->user_id)->first();
            $guiding = Guiding::where('id',$booking->guiding_id)->first();
            $guide = $guiding->user;
            

            // Send an email notification to the guest and guide
            if($user->language == 'en'){
                \App::setLocale('en');
            }
            Mail::to($user->email)->send(new GuestBookingExpiredMail($booking,$user,$guiding,$guide));

            if($guide->language == 'en'){
                \App::setLocale('en');
            }
            Mail::to($guide->email)->send(new GuideBookingExpiredMail($booking,$user,$guiding,$guide));

            \App::setLocale('de');
            Mail::to(env('TO_CEO','info@catchaguide.com'))->send(new BookingExpireMailToCEO($booking,$user,$guiding,$guide));
        }
    }
}
