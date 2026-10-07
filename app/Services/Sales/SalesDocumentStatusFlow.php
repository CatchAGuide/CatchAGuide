<?php

namespace App\Services\Sales;

use App\Enums\Sales\SalesDocumentStatus;
use App\Models\SalesDocument;
use Carbon\CarbonImmutable;

/**
 * Status rules of a sales document (spec §7):
 * draft → sent (send offer) → viewed (customer opens) → accepted (customer accepts);
 * confirmed from anything but cancelled (send confirmation); sent/viewed → expired (nightly,
 * after valid_until) or declined (employee); cancelled from anything (employee).
 */
class SalesDocumentStatusFlow
{
    /**
     * Status after sending the offer: at least "sent". Resending revives a declined, expired
     * or cancelled offer; later stages (viewed, accepted, confirmed) are kept.
     */
    public function afterOfferSent(SalesDocumentStatus $status): SalesDocumentStatus
    {
        return match ($status) {
            SalesDocumentStatus::Draft,
            SalesDocumentStatus::Declined,
            SalesDocumentStatus::Expired,
            SalesDocumentStatus::Cancelled => SalesDocumentStatus::Sent,
            default => $status,
        };
    }

    public function canConfirm(SalesDocument $document): bool
    {
        return $document->status !== SalesDocumentStatus::Cancelled;
    }

    public function canAccept(SalesDocument $document, ?CarbonImmutable $today = null): bool
    {
        return $document->status->isAwaitingCustomer() && ! $this->isPastValidity($document, $today);
    }

    public function shouldMarkViewed(SalesDocument $document): bool
    {
        return $document->status === SalesDocumentStatus::Sent;
    }

    public function shouldExpire(SalesDocument $document, ?CarbonImmutable $today = null): bool
    {
        return $document->status->isAwaitingCustomer() && $this->isPastValidity($document, $today);
    }

    public function canDecline(SalesDocument $document): bool
    {
        return $document->status->isAwaitingCustomer();
    }

    public function canCancel(SalesDocument $document): bool
    {
        return $document->status !== SalesDocumentStatus::Cancelled;
    }

    private function isPastValidity(SalesDocument $document, ?CarbonImmutable $today): bool
    {
        $today ??= CarbonImmutable::today();

        return $document->valid_until !== null && $document->valid_until->lt($today);
    }
}
