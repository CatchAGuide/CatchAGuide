<?php

namespace App\Mail\Guest;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * "Find my booking": the single-use sign-in link, valid for 30 minutes. Sent only when the
 * email has bookings (GuestLoginLinkService::request()).
 */
class GuestFindBookingsMail extends Mailable
{
    use Queueable, SerializesModels;

    // Properties for email logging
    public $type = 'guest_find_bookings';
    public $language;
    public $target = 'find_bookings';

    public function __construct(public string $url, string $locale)
    {
        $this->language = $locale;
    }

    public function build()
    {
        return $this->view('mails.guest.find_bookings')
            ->subject(__('guest_bookings.find.mail.subject'));
    }
}
