<?php

namespace App\Services\Booking;

use App\Events\BookingStatusChanged;
use App\Models\Booking;
use Illuminate\Support\Facades\DB;

/**
 * Guide declines a pending booking request with a message and alternative dates. Locked and
 * status-checked in one transaction, so a double click can't send the customer two emails.
 */
class BookingRejectionService
{
    /**
     * @param  list<string>  $alternativeDates  Y-m-d, sorted
     * @return bool False when the request was no longer pending (already answered).
     */
    public function reject(Booking $booking, string $reason, array $alternativeDates): bool
    {
        $rejected = DB::transaction(function () use ($booking, $reason, $alternativeDates) {
            $locked = Booking::whereKey($booking->getKey())->lockForUpdate()->first();

            if ($locked === null || $locked->status !== 'pending') {
                return null;
            }

            $locked->status = 'rejected';
            $locked->additional_information = $reason;
            $locked->alternative_dates = json_encode(array_values($alternativeDates));
            $locked->save();

            return $locked;
        });

        if ($rejected === null) {
            return false;
        }

        event(new BookingStatusChanged($rejected, 'rejected'));

        return true;
    }
}
