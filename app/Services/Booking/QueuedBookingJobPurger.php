<?php

namespace App\Services\Booking;

use App\Models\Booking;
use Illuminate\Contracts\Database\ModelIdentifier;
use Illuminate\Support\Facades\DB;
use ReflectionObject;
use Throwable;

/**
 * Removes rows from the database queue's `jobs` table that still reference a given
 * booking, so a booking that gets rejected/cancelled right after being accepted can't
 * have its stale queued acceptance emails/listeners fire later.
 */
class QueuedBookingJobPurger
{
    public function purge(Booking $booking): int
    {
        $deleted = 0;

        DB::table('jobs')
            ->select('id', 'payload')
            ->orderBy('id')
            ->chunkById(100, function ($rows) use ($booking, &$deleted) {
                foreach ($rows as $row) {
                    if ($this->payloadReferencesBooking($row->payload, (int) $booking->id)) {
                        DB::table('jobs')->where('id', $row->id)->delete();
                        $deleted++;
                    }
                }
            });

        return $deleted;
    }

    private function payloadReferencesBooking(string $payload, int $bookingId): bool
    {
        $decoded = json_decode($payload, true);
        $command = $decoded['data']['command'] ?? null;

        if (!is_string($command)) {
            return false;
        }

        try {
            $job = @unserialize($command);
        } catch (Throwable $e) {
            return false;
        }

        if (!is_object($job)) {
            return false;
        }

        return $this->objectReferencesBooking($job, $bookingId, []);
    }

    private function objectReferencesBooking(mixed $value, int $bookingId, array $visited, int $depth = 0): bool
    {
        if ($depth > 5 || !is_object($value)) {
            return false;
        }

        $hash = spl_object_id($value);
        if (isset($visited[$hash])) {
            return false;
        }
        $visited[$hash] = true;

        if ($value instanceof Booking) {
            return (int) $value->getKey() === $bookingId;
        }

        if ($value instanceof ModelIdentifier) {
            if ($value->getClass() !== Booking::class) {
                return false;
            }

            foreach ((array) $value->id as $id) {
                if ((int) $id === $bookingId) {
                    return true;
                }
            }

            return false;
        }

        // Some queue jobs keep their serialized event/mailable data as a nested
        // serialized string (e.g. Illuminate\Events\CallQueuedListener::$data).
        if ($value instanceof \Illuminate\Events\CallQueuedListener && is_string($value->data)) {
            try {
                $nested = @unserialize($value->data);
            } catch (Throwable $e) {
                $nested = false;
            }

            if (is_array($nested)) {
                foreach ($nested as $item) {
                    if (is_object($item) && $this->objectReferencesBooking($item, $bookingId, $visited, $depth + 1)) {
                        return true;
                    }
                }
            }
        }

        $reflection = new ReflectionObject($value);
        foreach ($reflection->getProperties() as $property) {
            $property->setAccessible(true);

            if (!$property->isInitialized($value)) {
                continue;
            }

            $propertyValue = $property->getValue($value);

            if (is_object($propertyValue) && $this->objectReferencesBooking($propertyValue, $bookingId, $visited, $depth + 1)) {
                return true;
            }

            if (is_array($propertyValue)) {
                foreach ($propertyValue as $item) {
                    if (is_object($item) && $this->objectReferencesBooking($item, $bookingId, $visited, $depth + 1)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
