<?php

namespace App\Contracts\Booking;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * An admin request whose open / in process / done status is logged by
 * App\Services\Booking\BookingStatusHistory. Implemented with App\Traits\HasStatusHistory.
 *
 * @property string|null $status
 * @property \Illuminate\Support\Carbon|null $created_at
 */
interface HasStatusHistoryContract
{
    public function statusChanges(): MorphMany;

    public function latestStatusChange(): MorphOne;

    /**
     * Label of the current status, e.g. "Done · Booked".
     */
    public function statusLabel(): string;

    /**
     * @return array<string, string> status value => label
     */
    public static function statusOptions(): array;
}
