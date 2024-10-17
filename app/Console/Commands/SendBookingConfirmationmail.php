<?php

namespace App\Console\Commands;

use App\Models\Booking;
use Illuminate\Console\Command;

class SendBookingConfirmationmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:bookingconfirmationmail';

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
        $booking = Booking::find(15);
        $booking->sendBookingConfirmationMail();

    }
}
