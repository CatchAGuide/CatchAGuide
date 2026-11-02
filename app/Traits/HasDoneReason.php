<?php

namespace App\Traits;

use App\Enums\DoneReason;
use Illuminate\Database\Eloquent\Builder;

/**
 * Reads the `done_reason` column (booked / no reply) of a request whose status is done.
 * Written through App\Services\Booking\BookingStatusHistory::change().
 */
trait HasDoneReason
{
    public function doneReason(): ?DoneReason
    {
        if ($this->status !== 'done') {
            return null;
        }

        return DoneReason::tryFrom((string) $this->done_reason);
    }

    /**
     * Leaves out requests closed as "no reply": they were never booked, so they don't count as
     * revenue or get a review request. Done requests from before reasons existed (null) stay in.
     */
    public function scopeExcludingNoReply(Builder $query): Builder
    {
        return $query->where(fn (Builder $q) => $q
            ->whereNull($q->qualifyColumn('done_reason'))
            ->orWhere($q->qualifyColumn('done_reason'), '!=', DoneReason::NoReply->value));
    }
}
