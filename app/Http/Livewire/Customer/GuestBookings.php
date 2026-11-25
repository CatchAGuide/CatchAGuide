<?php

namespace App\Http\Livewire\Customer;

use App\Enums\GuestBookingType;
use App\Models\User;
use App\Presenters\Booking\CustomerBookingPresenter;
use App\Presenters\Booking\GuestBookingPresenter;
use App\Services\Customer\BookingOwner;
use App\Services\GuestBooking\GuestBookingEmailSession;
use Illuminate\Database\Eloquent\Model;

/**
 * "Find my booking" (/my-bookings/list): the customer area's booking tabs for an email the
 * guest proved they read, with the guest link's rules on each card (contact details only
 * until the tour day, reduced view after 90 days). No account menu, no favourites.
 */
class GuestBookings extends Bookings
{
    protected function owner(): User|BookingOwner
    {
        $owner = app(GuestBookingEmailSession::class)->owner();
        abort_if($owner === null, 403);

        return $owner;
    }

    protected function isGuest(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    protected function card(Model $row, CustomerBookingPresenter $presenter): array
    {
        return app(GuestBookingPresenter::class)->card($row) + [
            'guest_url' => route('guest-bookings.open', ['type' => GuestBookingType::forModel($row)->value, 'id' => $row->getKey()]),
        ];
    }
}
