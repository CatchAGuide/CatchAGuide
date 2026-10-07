<?php

namespace App\Services\Sales;

use App\Enums\Sales\SalesDocumentStatus;
use App\Enums\Sales\SalesEventType;
use App\Mail\Sales\SalesOfferAcceptedMail;
use App\Models\Employee;
use App\Models\SalesDocument;
use App\Models\SalesDocumentEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * What happens on the customer page and in the list's row actions: first view, acceptance,
 * expiry, decline and cancel, each with its status-log event.
 */
class SalesDocumentCustomerActions
{
    public function __construct(
        private readonly SalesDocumentStatusFlow $flow,
        private readonly SalesDocumentWriter $writer,
    ) {}

    public function markViewed(SalesDocument $document): void
    {
        if (! $this->flow->shouldMarkViewed($document)) {
            return;
        }

        $document->forceFill(['status' => SalesDocumentStatus::Viewed, 'viewed_at' => $document->viewed_at ?? now()])->save();
        $this->writer->event($document, SalesEventType::Viewed, null, [], SalesDocumentEvent::ACTOR_CUSTOMER);
    }

    /**
     * Records the acceptance and notifies the team. False when the offer can no longer be accepted.
     */
    public function accept(SalesDocument $document, ?string $ip = null): bool
    {
        if (! $this->flow->canAccept($document)) {
            return false;
        }

        DB::transaction(function () use ($document, $ip) {
            $document->forceFill([
                'status' => SalesDocumentStatus::Accepted,
                'accepted_at' => now(),
                'acceptance_seen_at' => null,
            ])->save();

            $this->writer->event($document, SalesEventType::Accepted, null, array_filter([
                'revision' => $document->revisions()->max('revision_no'),
                'total' => (string) $document->total_amount,
                'ip' => $ip,
            ]), SalesDocumentEvent::ACTOR_CUSTOMER);
        });

        $recipients = array_values(array_unique(array_filter([
            $document->creator?->email,
            config('sales_documents.sales_inbox'),
        ])));

        if ($recipients !== []) {
            try {
                Mail::to($recipients)->send(new SalesOfferAcceptedMail($document));
            } catch (Throwable $exception) {
                // The acceptance is stored and shows as a badge in the list; a failed notice must not lose it.
                Log::warning('Sales offer acceptance notice failed', ['document' => $document->id, 'message' => $exception->getMessage()]);
            }
        }

        return true;
    }

    public function expire(SalesDocument $document): bool
    {
        if (! $this->flow->shouldExpire($document)) {
            return false;
        }

        $document->forceFill(['status' => SalesDocumentStatus::Expired])->save();
        $this->writer->event($document, SalesEventType::Expired, null, ['valid_until' => $document->valid_until?->toDateString()], SalesDocumentEvent::ACTOR_SYSTEM);

        return true;
    }

    public function decline(SalesDocument $document, Employee $actor): bool
    {
        if (! $this->flow->canDecline($document)) {
            return false;
        }

        $document->forceFill(['status' => SalesDocumentStatus::Declined, 'updated_by' => $actor->id])->save();
        $this->writer->event($document, SalesEventType::Declined, $actor);

        return true;
    }

    public function cancel(SalesDocument $document, Employee $actor): bool
    {
        if (! $this->flow->canCancel($document)) {
            return false;
        }

        $document->forceFill(['status' => SalesDocumentStatus::Cancelled, 'updated_by' => $actor->id])->save();
        $this->writer->event($document, SalesEventType::Cancelled, $actor);

        return true;
    }
}
