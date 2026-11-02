<?php

namespace App\Services\Booking;

use App\Contracts\Booking\HasDoneReasonContract;
use App\Contracts\Booking\HasStatusHistoryContract;
use App\Enums\DoneReason;
use App\Models\BookingStatusChange;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Changes the status of an admin request (camp/vacation and trip requests, contact requests,
 * product reports) and logs who did it, so the admin lists can show "last changed by" and the
 * full history.
 */
class BookingStatusHistory
{
    /**
     * Returns false when the request already has that status (nothing is logged).
     *
     * For requests with a done reason (booked / no reply), "done" keeps the given reason, and
     * switching only the reason (done · no reply → done · booked) counts as a change. Leaving
     * "done" clears it.
     */
    public function change(HasStatusHistoryContract&Model $booking, string $status, ?Employee $actor, ?string $reason = null, ?DoneReason $doneReason = null): bool
    {
        $from = $booking->status;
        $tracksReason = $booking instanceof HasDoneReasonContract;
        $newDoneReason = $tracksReason && $status === 'done' ? $doneReason : null;

        if ($from === $status && (! $tracksReason || $booking->doneReason() === $newDoneReason)) {
            return false;
        }

        DB::transaction(function () use ($booking, $from, $status, $actor, $reason, $tracksReason, $newDoneReason) {
            $attributes = ['status' => $status];
            if ($tracksReason) {
                $attributes['done_reason'] = $newDoneReason?->value;
            }
            $booking->forceFill($attributes)->save();
            $this->log($booking, $from, $status, $actor, $reason ?? $newDoneReason?->value);
        });

        return true;
    }


    /**
     * First entry for a request an employee created by hand, with its initial status.
     */
    public function created(HasStatusHistoryContract&Model $booking, ?Employee $actor): void
    {
        $this->log($booking, null, (string) $booking->status, $actor, BookingStatusChange::REASON_CREATED);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function timeline(HasStatusHistoryContract&Model $booking): array
    {
        $labels = $booking::statusOptions();

        $changes = $booking->statusChanges()->with('employee')->get();

        $changes = $changes->values();

        $items = $changes
            ->map(function (BookingStatusChange $change, int $index) use ($changes, $labels) {
                $doneReason = $change->to_status === 'done' ? DoneReason::tryFrom((string) $change->reason) : null;
                // Newest first: the next entry is the one this change moved away from.
                $previous = $changes->get($index + 1);
                $fromDoneReason = $change->from_status === 'done' && $previous?->to_status === 'done'
                    ? DoneReason::tryFrom((string) $previous->reason)
                    : null;

                return [
                    'from' => $change->from_status,
                    'from_label' => $change->from_status ? $this->label($labels, $change->from_status, $fromDoneReason) : null,
                    'to' => $change->to_status,
                    'to_label' => $this->label($labels, $change->to_status, $doneReason),
                    'variant' => $doneReason ? 'done-'.$doneReason->value : $change->to_status,
                    'by' => $change->actorName(),
                    'reason' => $change->reason && ! $doneReason ? __('admin.booking_status_history.reason_'.$change->reason) : null,
                    'at' => $change->created_at?->format('M j, Y g:i A'),
                ];
            })
            ->all();

        // Requests from the site have no "created" row: show when the guest sent it.
        if (! $changes->contains('reason', BookingStatusChange::REASON_CREATED)) {
            $items[] = [
                'from' => null,
                'from_label' => null,
                'to' => BookingStatusChange::REASON_CREATED,
                'to_label' => __('admin.booking_status_history.received'),
                'variant' => BookingStatusChange::REASON_CREATED,
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
    public function summary(HasStatusHistoryContract&Model $booking): ?string
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

    /**
     * Label of a status in the history (the current one comes from the model's statusLabel()).
     *
     * @param  array<string, string>  $labels
     */
    private function label(array $labels, string $status, ?DoneReason $doneReason): string
    {
        $label = $labels[$status] ?? $status;

        return $doneReason ? $label.' · '.$doneReason->label() : $label;
    }

    private function log(HasStatusHistoryContract&Model $booking, ?string $from, string $to, ?Employee $actor, ?string $reason): void
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
