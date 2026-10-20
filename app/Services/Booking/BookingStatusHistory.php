<?php

namespace App\Services\Booking;

use App\Models\BookingStatusChange;
use App\Models\CampVacationBooking;
use App\Models\Employee;
use App\Models\TripBooking;
use Illuminate\Support\Facades\DB;

/**
 * Changes the status of a camp/vacation or trip request and logs who did it, so the admin lists
 * can show "last changed by" and the full history.
 */
class BookingStatusHistory
{
    /**
     * Returns false when the request already has that status (nothing is logged).
     */
    public function change(CampVacationBooking|TripBooking $booking, string $status, ?Employee $actor, ?string $reason = null): bool
    {
        $from = $booking->status;
        if ($from === $status) {
            return false;
        }

        DB::transaction(function () use ($booking, $from, $status, $actor, $reason) {
            $booking->forceFill(['status' => $status])->save();
            $this->log($booking, $from, $status, $actor, $reason);
        });

        return true;
    }

    /**
     * First entry for a request an employee created by hand, with its initial status.
     */
    public function created(CampVacationBooking|TripBooking $booking, ?Employee $actor): void
    {
        $this->log($booking, null, (string) $booking->status, $actor, BookingStatusChange::REASON_CREATED);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function timeline(CampVacationBooking|TripBooking $booking): array
    {
        $labels = $booking::statusOptions();

        $changes = $booking->statusChanges()->with('employee')->get();

        $items = $changes
            ->map(fn (BookingStatusChange $change) => [
                'from' => $change->from_status,
                'from_label' => $change->from_status ? ($labels[$change->from_status] ?? $change->from_status) : null,
                'to' => $change->to_status,
                'to_label' => $labels[$change->to_status] ?? $change->to_status,
                'by' => $change->actorName(),
                'reason' => $change->reason ? __('admin.booking_status_history.reason_'.$change->reason) : null,
                'at' => $change->created_at?->format('M j, Y g:i A'),
            ])
            ->values()
            ->all();

        // Requests from the site have no "created" row: show when the guest sent it.
        if (! $changes->contains('reason', BookingStatusChange::REASON_CREATED)) {
            $items[] = [
                'from' => null,
                'from_label' => null,
                'to' => BookingStatusChange::REASON_CREATED,
                'to_label' => __('admin.booking_status_history.received'),
                'by' => __('admin.booking_status_history.guest'),
                'reason' => null,
                'at' => $booking->created_at?->format('M j, Y g:i A'),
            ];
        }

        return $items;
    }

    /**
     * "Changed by X · date" line for the list's status cell, or null before any change.
     */
    public function summary(CampVacationBooking|TripBooking $booking): ?string
    {
        $change = $booking->latestStatusChange;
        if (! $change) {
            return null;
        }

        return __('admin.booking_status_history.last_changed', [
            'name' => $change->actorName(),
            'date' => $change->created_at?->format('M j, Y g:i A'),
        ]);
    }

    private function log(CampVacationBooking|TripBooking $booking, ?string $from, string $to, ?Employee $actor, ?string $reason): void
    {
        $booking->statusChanges()->create([
            'from_status' => $from,
            'to_status' => $to,
            'employee_id' => $actor?->id,
            'reason' => $reason,
        ]);
        $booking->unsetRelation('latestStatusChange')->unsetRelation('statusChanges');
    }
}
