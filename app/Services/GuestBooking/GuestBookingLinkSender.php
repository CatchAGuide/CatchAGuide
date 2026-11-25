<?php

namespace App\Services\GuestBooking;

use App\Enums\GuestBookingType;
use App\Mail\Guest\GuestBookingLinkMail;
use App\Models\BookingAccessEvent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;

/**
 * Admin "Send booking link" (plan Q12) and "Turn link off". The link only ever goes to the
 * booking's own email address; admins never see or copy it. Every use is logged by admin.
 */
class GuestBookingLinkSender
{
    public function __construct(private readonly BookingAccessTokenService $tokens) {}

    /** Queues the email; returns the address it goes to, or null when the booking has none. */
    public function send(Model $bookable, ?int $employeeId): ?string
    {
        $type = GuestBookingType::forModel($bookable);
        $email = $type->email($bookable);
        if ($email === null || $this->tokens->plainTokenFor($bookable) === null) {
            return null;
        }

        Mail::to($email)->queue((new GuestBookingLinkMail($bookable))->locale($type->locale($bookable)));
        $this->tokens->log($this->tokens->active($bookable), BookingAccessEvent::SENT, $employeeId);

        return $email;
    }

    /** Turns the current link off and sends the guest the new one. */
    public function revokeAndResend(Model $bookable, ?int $employeeId): ?string
    {
        $this->tokens->revoke($bookable, $employeeId);

        return $this->send($bookable, $employeeId);
    }
}
