<?php

namespace App\Mail\Guest;

use App\Enums\GuestBookingType;
use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * "Here is the link to your booking", sent by an admin from the booking lists (plan Q12). Goes
 * only to the booking's own email; the link inside is the booking's active link.
 */
class GuestBookingLinkMail extends Mailable
{
    use Queueable, SerializesModels;

    // Properties for email logging
    public $type = 'guest_booking_link';
    public $language;
    public $target;

    public function __construct(public Model $bookable)
    {
        $type = GuestBookingType::forModel($bookable);
        $this->language = $type->locale($bookable);
        $this->target = $type->value.'_'.$bookable->getKey();
    }

    public function build()
    {
        $type = GuestBookingType::forModel($this->bookable);

        return $this->view('mails.guest.booking_link')
            ->subject(__('guest_bookings.mail.link_subject'))
            ->with([
                'typeLabel' => __($type->translationKey()),
                'title' => $this->title($type),
            ]);
    }

    private function title(GuestBookingType $type): string
    {
        $b = $this->bookable;

        return (string) match ($type) {
            GuestBookingType::Tour => $b->guiding?->title,
            GuestBookingType::Camp => $b->camp?->title,
            GuestBookingType::Trip => $b->trip?->title,
            GuestBookingType::Voucher => $b->guiding?->title ?: $b->tour_title_snapshot,
        };
    }
}
