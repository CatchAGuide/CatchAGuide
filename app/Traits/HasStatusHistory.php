<?php

namespace App\Traits;

use App\Contracts\Booking\HasDoneReasonContract;
use App\Models\BookingStatusChange;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * Status change log for an admin request (camp/trip bookings, contact requests, product reports).
 * Models using it implement App\Contracts\Booking\HasStatusHistoryContract; changes are written
 * through App\Services\Booking\BookingStatusHistory.
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

    /**
     * Label of the current status, with the done reason when there is one ("Done · Booked").
     */
    public function statusLabel(): string
    {
        $status = (string) ($this->status ?: 'open');
        $label = static::statusOptions()[$status] ?? $status;
        $doneReason = $this instanceof HasDoneReasonContract ? $this->doneReason() : null;

        return $doneReason ? $label.' · '.$doneReason->label() : $label;
    }
}
