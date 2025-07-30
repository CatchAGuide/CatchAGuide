<?php

namespace App\Mail\Guest;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

use App\Models\Booking;
use App\Models\UserICalFeed;
use App\Services\ICalGeneratorService;
use App\Traits\HasICalIntegration;

class BookingAcceptMail extends Mailable
{
    use Queueable, SerializesModels, HasICalIntegration;

    protected $booking;
    public $language;
    public $target;
    public $type = 'booking_accept_mail';
    public $icsContent;
    public $userICalFeed;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
        $this->language = $booking->user?->language ?? app()->getLocale();
        $this->target = 'booking_' . $booking->id;
        
        // Generate ICS content and get/create feed using the trait
        $icsData = $this->generateBookingICSAndFeed($this->booking, false); // false = for user, not guide
        $this->icsContent = $icsData['icsContent'];
        $this->userICalFeed = $icsData['userICalFeed'];
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // Log ICS integration using trait
        $this->logICSIntegration($this->booking, $this->icsContent, $this->userICalFeed, 'BookingAcceptMail');
        
        $mail = $this->view('mails.guest.accepted_mail', [
            'booking' => $this->booking,
            'user' => $this->booking->user,
            'guiding' => $this->booking->guiding,
            'guide' => $this->booking->guiding->user,
            'userICalFeed' => $this->userICalFeed
        ])
        ->subject(__('profile.gt-accepted')." – Catch A Guide");
        
        // Attach ICS file using trait
        $this->attachICSFile($mail, $this->icsContent, $this->booking);
        
        return $mail;
    }
}
