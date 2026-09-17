<?php

namespace App\Listeners;
use App\Events\BookingStatusChanged;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Bus\Queueable;

use Illuminate\Support\Facades\Mail;

use App\Mail\Guest\BookingAcceptMail;
use App\Mail\Guest\BookingRejectMail;
use App\Mail\Guest\BookingCancelledMail;
use App\Mail\Guide\GuideBookingAcceptedMail;
use App\Mail\Guide\GuideBookingCancelledMail;
use App\Mail\Ceo\BookingAcceptMailToCEO;
use App\Mail\Ceo\BookingRejectMailToCEO;
use App\Mail\Ceo\BookingCancelMailToCEO;

class BookingAcceptedListener  implements ShouldQueue
{ 
    use InteractsWithQueue,Queueable;

    public function handle(BookingStatusChanged $event)
    {   
        if (app()->environment('local')) {
            return;
        }

        $bookingUserEmail = $event->booking->customerEmail();
        $guestLocale = $event->booking->customerLocale();
        if ($event->status === 'accepted') {
            if ($bookingUserEmail && !CheckEmailLog('booking_accept_mail', 'booking_' . $event->booking->id, $bookingUserEmail)) {
                Mail::to($bookingUserEmail)->locale($guestLocale)->send(new BookingAcceptMail($event->booking));
            }

            if (!CheckEmailLog('guide_booking_accepted_mail', 'booking_' . $event->booking->id, $event->booking->guiding->user->email)) {
                Mail::to($event->booking->guiding->user->email)->locale($event->booking->guiding->user->language ?? app()->getLocale())->send(new GuideBookingAcceptedMail($event->booking));
            }

            $email = config('mail.admin_email');
            if (!CheckEmailLog('booking_accept', 'booking_' . $event->booking->id, $email)) {
                Mail::to($email)->locale('de')->send(new BookingAcceptMailToCEO($event->booking));
            }
        }

        if ($event->status === 'rejected') {
            if ($bookingUserEmail && !CheckEmailLog('booking_reject_mail', 'booking_' . $event->booking->id, $bookingUserEmail)) {
                Mail::to($bookingUserEmail)->locale($guestLocale)->send(new BookingRejectMail($event->booking));
            }

            $email = config('mail.admin_email');
            if (!CheckEmailLog('booking_reject_mail_to_ceo', 'admin_booking_' . $event->booking->id, $email)) {
                Mail::to($email)->locale('de')->send(new BookingRejectMailToCEO($event->booking));
            }
       }

        if ($event->status === 'cancelled') {
            if ($bookingUserEmail && !CheckEmailLog('booking_cancelled_mail', 'booking_' . $event->booking->id, $bookingUserEmail)) {
                Mail::to($bookingUserEmail)->locale($guestLocale)->send(new BookingCancelledMail($event->booking));
            }

            $guide = $event->booking->guiding->user;
            if (!CheckEmailLog('guide_booking_cancelled_mail', 'booking_' . $event->booking->id, $guide->email)) {
                Mail::to($guide->email)->locale($guide->language ?? app()->getLocale())->send(new GuideBookingCancelledMail($event->booking));
            }

            $email = config('mail.admin_email');
            if (!CheckEmailLog('booking_cancel_mail_to_ceo', 'admin_booking_' . $event->booking->id, $email)) {
                Mail::to($email)->locale('de')->send(new BookingCancelMailToCEO(
                    $event->booking,
                    $event->booking->guiding,
                    $guide,
                    $event->booking->user
                ));
            }
        }
    }
}
