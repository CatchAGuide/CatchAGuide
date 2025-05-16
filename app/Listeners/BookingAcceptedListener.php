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
        $bookingUserEmail = $event->booking->email ? $event->booking->email : $event->booking->user->email;
        if ($event->status === 'accepted') {
            if($event->booking->user->language == 'en'){
                \App::setLocale('en');
            }
            if (!CheckEmailLog('booking_accept_mail', 'booking_' . $event->booking->id, $bookingUserEmail)) {
                Mail::to($bookingUserEmail)->send(new BookingAcceptMail($event->booking));
            }

            if($event->booking->guiding->user->language == 'en'){
                \App::setLocale('en');
            }
            if (!CheckEmailLog('guide_booking_accepted_mail', 'booking_' . $event->booking->id, $event->booking->guiding->user->email)) {
                Mail::to($event->booking->guiding->user->email)->send(new GuideBookingAcceptedMail($event->booking));
            }

            \App::setLocale('de');
            $email = env('TO_CEO','info@catchaguide.com');
            if (!CheckEmailLog('booking_accept', 'booking_' . $event->booking->id, $email)) {
                Mail::to($email)->send(new BookingAcceptMailToCEO($event->booking));
            }
        }

        if ($event->status === 'rejected') {
            if($event->booking->user->language == 'en'){
                \App::setLocale('en');
            }
            if (!CheckEmailLog('booking_reject_mail', 'booking_' . $event->booking->id, $bookingUserEmail)) {
                Mail::to($bookingUserEmail)->send(new BookingRejectMail($event->booking));
            }

            \App::setLocale('de');
            $email = env('TO_CEO','info@catchaguide.com');
            if (!CheckEmailLog('booking_reject_mail_to_ceo', 'admin_booking_' . $event->booking->id, $email)) {
                Mail::to($email)->send(new BookingRejectMailToCEO($event->booking));
            }
       }
    }
}
