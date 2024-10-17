<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\Booking;
use App\Mail\ReminderBooking;

use Mail;
use App\Models\User;
use App\Models\Guiding;

use Carbon\Carbon;


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
    protected $description = 'Command description';

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

        $nearExpireBookings = Booking::where('status', 'pending')
        ->where('expires_at', '>', now())
        ->get();

        foreach ($nearExpireBookings as $booking) {

            $timeDifferenceHours = now()->diffInHours($booking->expires_at);
 
            if ($timeDifferenceHours == 24) {

                $user = User::where('id',$booking->user_id)->first();
                $guiding = Guiding::where('id',$booking->guiding_id)->first();
                $guide = $guiding->user;
                
                if($guide->language == 'en'){
                    \App::setLocale('en');
                }
                Mail::to($guide->email)->send(new ReminderBooking($booking,$user,$guiding,$guide));
            }

        }
    }
}
