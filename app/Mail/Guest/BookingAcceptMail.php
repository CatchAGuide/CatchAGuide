<?php

namespace App\Mail\Guest;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

use App\Models\Booking;
use App\Services\CalendlyService;


class BookingAcceptMail extends Mailable
{
    use SerializesModels;

    public $booking;
    public $language;
    public $target;
    public $type = 'booking_accept_mail';
    public $icsContent;

    public function __construct(Booking $booking)
    {
          $this->booking = $booking;
          $this->language = $booking->user?->language ?? app()->getLocale();
          $this->target = 'booking_' . $booking->id;
          
          // Generate ICS content for calendar integration
          try {
              $calendlyService = app(CalendlyService::class);
              $this->icsContent = $calendlyService->generateICSContent($booking, $booking->guiding);
          } catch (\Exception $e) {
              $this->icsContent = null;
          }
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $mail = $this->view('mails.guest.accepted_mail',[
            'user' => $this->booking->user,
            'booking' => $this->booking,
            'guiding' => $this->booking->guiding,
            'guide' => $this->booking->guiding->user,
            'icsContent' => $this->icsContent
        ])->subject(__('profile.br-accepted')." – Catch A Guide");
        
        // Add ICS file attachment if content is available
        if ($this->icsContent) {
            $mail->attachData(
                $this->icsContent,
                'fishing-trip-' . $this->booking->id . '.ics',
                [
                    'mime' => 'text/calendar',
                    'Content-Type' => 'text/calendar; method=PUBLISH; charset=UTF-8',
                ]
            );
        }
        
        return $mail;
    }
}
