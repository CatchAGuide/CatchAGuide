<?php

namespace App\Services\Booking\Reschedule;

use App\Models\Booking;
use App\Services\BookingService;
use App\Services\Checkout\TourCheckoutQuote;
use Illuminate\Support\Facades\DB;

/**
 * Sends a declined tour request again for a new date: creates the follow-up booking request
 * and marks the declined one as used, atomically, so a double click or a replayed request
 * can never create two.
 */
class TourRescheduleService
{
    public function __construct(
        private readonly BookingService $bookings,
    ) {}

    /**
     * @throws RescheduleAlreadyUsedException When the link was used in the meantime.
     * @throws \InvalidArgumentException When the date was taken in the meantime.
     */
    public function submit(Booking $original, TourCheckoutQuote $quote, string $selectedDate): Booking
    {
        return DB::transaction(function () use ($original, $quote, $selectedDate) {
            $locked = Booking::whereKey($original->getKey())->lockForUpdate()->first();

            if ($locked === null || $locked->is_rescheduled || $locked->status !== 'rejected') {
                throw new RescheduleAlreadyUsedException();
            }

            $newBooking = $this->bookings->rescheduleGuidingBooking($locked, [
                'selected_date' => $selectedDate,
                'total_price' => $quote->total(),
                'total_extra_price' => $quote->extrasTotal(),
                'count_of_users' => $quote->persons,
                'extras_serialized' => $quote->serializedExtras(),
            ], sendEmails: ! app()->environment('local'));

            $locked->is_rescheduled = true;
            $locked->save();

            return $newBooking;
        });
    }
}
