<?php

namespace App\Listeners;
use App\Events\BookingStatusChanged;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Bus\Queueable;

use Illuminate\Support\Facades\Mail;

use App\Mail\Guest\BookingAcceptMail;
use App\Mail\Guest\BookingRejectMail;
use App\Mail\Guide\GuideBookingAcceptedMail;
use App\Mail\Ceo\BookingAcceptMailToCEO;
use App\Mail\Ceo\BookingRejectMailToCEO;

class BookingAcceptedListener  implements ShouldQueue
{ 
    use InteractsWithQueue,Queueable;

    public function handle(BookingStatusChanged $event)
    {   
        if ($event->status === 'accepted') {
            if($event->booking->user->language == 'en'){
                \App::setLocale('en');
            }
             Mail::to($event->booking->user->email)->send(new BookingAcceptMail($event->booking));

            if($event->booking->guiding->user->language == 'en'){
                \App::setLocale('en');
            }
             Mail::to($event->booking->guiding->user->email)->send(new GuideBookingAcceptedMail($event->booking));

            \App::setLocale('de');

             Mail::to(env('TO_CEO','info@catchaguide.com'))->send(new BookingAcceptMailToCEO($event->booking));
        }

        if ($event->status === 'rejected') {
            if($event->booking->user->language == 'en'){
                \App::setLocale('en');
            }
            Mail::to($event->booking->user->email)->send(new BookingRejectMail($event->booking));

            \App::setLocale('de');
            Mail::to(env('TO_CEO','info@catchaguide.com'))->send(new BookingRejectMailToCEO($event->booking));
       }
    }
}
