<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

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
        // Determine locales per recipient
        $guestLocale = $this->user->language ?? app()->getLocale();
        $guideLocale = $this->guide->language ?? app()->getLocale();
        $ceoLocale = 'de';

        // Guest email (locale from booking/domain)
        if (!CheckEmailLog('guest_booking_request', 'booking_' . $this->booking->id, $this->booking->email)) {
            Log::info('********************************************************************************** Sending guest booking request email to ' . $this->booking->email . ' with locale ' . app()->getLocale());
            Mail::to($this->booking->email)
                ->locale($guestLocale)
                ->queue(new GuestBookingRequestMail($this->booking, $this->user, $this->guiding, $this->guide));
        }

        // Guide email (locale from guide preference)
        if (!CheckEmailLog('guide_booking_request', 'guide_' . $this->guide->id . '_booking_' . $this->booking->id, $this->guide->email)) {
            Log::info('Sending guide booking request email to ' . $this->guide->email . ' with locale ' . $guideLocale);
            Mail::to($this->guide->email)
                ->locale($guideLocale)
                ->queue(new GuideBookingRequestMail($this->booking, $this->user, $this->guiding, $this->guide));
        }

        // CEO notification (default to DE)
        $email = env('TO_CEO', 'info@catchaguide.com');
        if (!CheckEmailLog('ceo_booking_notification', 'admin_booking_' . $this->booking->id, $email)) {
            Log::info('Sending CEO booking notification email to ' . $email . ' with locale ' . $ceoLocale . '*****************************************************************************************');
            Mail::to($email)
                ->locale($ceoLocale)
                ->queue(new BookingRequestMailToCEO($this->booking));
        }
    }
}
