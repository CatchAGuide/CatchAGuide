<?php

namespace App\Http\Controllers\Admin;

use App\Enums\GuestBookingType;
use App\Http\Controllers\Controller;
use App\Services\GuestBooking\GuestBookingLinkSender;
use Illuminate\Http\RedirectResponse;

/**
 * Admin buttons on the tour, camp, trip and voucher lists: send the guest the link to their
 * booking, or turn the link off and send a new one.
 */
class GuestBookingLinksController extends Controller
{
    public function __construct(private readonly GuestBookingLinkSender $sender) {}

    public function send(string $type, int $id): RedirectResponse
    {
        $email = $this->sender->send($this->bookable($type, $id), auth('employees')->id());

        return back()->with(
            $email ? 'success' : 'error',
            $email ? __('guest_bookings.admin.sent', ['email' => $email]) : __('guest_bookings.admin.no_email'),
        );
    }

    public function revoke(string $type, int $id): RedirectResponse
    {
        $email = $this->sender->revokeAndResend($this->bookable($type, $id), auth('employees')->id());

        return back()->with(
            $email ? 'success' : 'error',
            $email ? __('guest_bookings.admin.revoked', ['email' => $email]) : __('guest_bookings.admin.no_email'),
        );
    }

    private function bookable(string $type, int $id)
    {
        $class = (GuestBookingType::tryFrom($type) ?? abort(404))->modelClass();

        return $class::query()->findOrFail($id);
    }
}
