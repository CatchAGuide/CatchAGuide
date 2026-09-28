<?php

namespace App\Services\Booking\Reschedule;

use App\Models\Booking;
use App\Models\Guiding;
use App\Services\Checkout\BlockedDateRanges;
use Carbon\CarbonImmutable;

/**
 * Turns the token from a guide's rejection email into a RescheduleOffer: which booking it
 * belongs to and which of the guide's suggested dates can still be requested.
 */
class RescheduleOfferResolver
{
    private const MAX_TOKEN_LENGTH = 255;

    public function resolve(?string $token): RescheduleOffer
    {
        if ($token === null || $token === '' || strlen($token) > self::MAX_TOKEN_LENGTH) {
            return RescheduleOffer::invalid();
        }

        $booking = Booking::with('guiding.user')->where('token', $token)->first();

        if ($booking === null) {
            return RescheduleOffer::invalid();
        }

        if ($booking->is_rescheduled) {
            return new RescheduleOffer(RescheduleOffer::USED, $booking);
        }

        // Only a booking the guide declined can be moved; any other token isn't a reschedule link.
        if ($booking->status !== 'rejected') {
            return RescheduleOffer::invalid();
        }

        if (! $booking->guiding || ! Guiding::publiclyVisible()->whereKey($booking->guiding_id)->exists()) {
            return new RescheduleOffer(RescheduleOffer::UNAVAILABLE, $booking);
        }

        $suggested = $this->suggestedDates($booking);
        if ($suggested === null) {
            return new RescheduleOffer(RescheduleOffer::AVAILABLE, $booking, null);
        }

        $dates = $this->stillBookable($booking->guiding, $suggested);

        return $dates === []
            ? new RescheduleOffer(RescheduleOffer::EXPIRED, $booking, [])
            : new RescheduleOffer(RescheduleOffer::AVAILABLE, $booking, $dates);
    }

    /**
     * @return list<string>|null Null when the guide suggested no dates.
     */
    private function suggestedDates(Booking $booking): ?array
    {
        $raw = decode_if_json($booking->alternative_dates, true);
        if (! is_array($raw) || $raw === []) {
            return null;
        }

        $dates = [];
        foreach ($raw as $value) {
            if (is_string($value) && preg_match('/^\d{4}-\d{2}-\d{2}/', $value)) {
                $dates[] = substr($value, 0, 10);
            }
        }

        $dates = array_values(array_unique($dates));
        sort($dates);

        return $dates === [] ? null : $dates;
    }

    /**
     * @param  list<string>  $dates
     * @return list<string>
     */
    private function stillBookable(Guiding $guiding, array $dates): array
    {
        $today = CarbonImmutable::today()->format('Y-m-d');
        $blocked = BlockedDateRanges::merge($guiding->getBlockedEvents());

        return array_values(array_filter($dates, function (string $date) use ($today, $blocked) {
            if ($date <= $today) {
                return false;
            }

            foreach ($blocked as $range) {
                if ($date >= $range['from'] && $date <= $range['due']) {
                    return false;
                }
            }

            return true;
        }));
    }
}
