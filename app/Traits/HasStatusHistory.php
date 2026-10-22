<?php

namespace App\Traits;

use App\Models\BookingStatusChange;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * Status change log for a camp or trip booking (CampVacationBooking, TripBooking). Changes are
 * written through App\Services\Booking\BookingStatusHistory.
 */
trait HasStatusHistory
{
    public function statusChanges(): MorphMany
    {
        return $this->morphMany(BookingStatusChange::class, 'bookable')->latest('id');
    }

    public function latestStatusChange(): MorphOne
    {
        return $this->morphOne(BookingStatusChange::class, 'bookable')->latestOfMany();
    }
}
