<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Mail\Guest\GuestBookingRequestMail;
use App\Mail\Guide\GuideBookingRequestMail;
use App\Mail\Ceo\BookingRequestMailToCEO;

use Mail;

class SendCheckoutEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $booking;
    protected $user;
    protected $guiding;
    protected $guide;

    public function __construct($booking,$user,$guiding,$guide)
    {
        $this->booking = $booking;
        $this->user = $user;
        $this->guiding = $guiding;
        $this->guide = $guide;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
  
        if($this->user->language == 'en'){
            \App::setLocale('en');
        }
        Mail::to($this->user->email)->queue(new GuestBookingRequestMail($this->booking,$this->user,$this->guiding,$this->guide));

        if($this->guide->language == 'en'){
            \App::setLocale('en');
        }
        Mail::to($this->guide->email)->queue(new GuideBookingRequestMail($this->booking,$this->user,$this->guiding,$this->guide));

        \App::setLocale('de');
        Mail::to(env('TO_CEO'))->queue(new BookingRequestMailToCEO($this->booking));
       
    }
}
