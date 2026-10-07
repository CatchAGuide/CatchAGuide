<?php

namespace App\Presenters\Sales;

use App\Models\SalesDocument;
use App\Services\Sales\SalesDocumentStatusFlow;
use App\Services\Sales\SalesFormat;
use App\Services\Sales\SalesLinks;

/**
 * One row of the admin offers list (spec §4.8).
 */
class SalesDocumentRowPresenter
{
    public function __construct(
        private readonly SalesLinks $links,
        private readonly SalesDocumentStatusFlow $flow,
    ) {}

    /**
     * Expects the document with `cards` and `creator` loaded.
     *
     * @return array<string, mixed>
     */
    public function present(SalesDocument $document): array
    {
        $locale = app()->getLocale();
        $lastSent = collect([$document->offer_sent_at, $document->confirmation_sent_at])->filter()->max();

        return [
            'document' => $document,
            'customer' => $document->fullName() ?: '–',
            'products' => $document->cards->pluck('title_snapshot')->filter()->implode(', ') ?: '–',
            'period' => $document->travel_from
                ? SalesFormat::date($document->travel_from->toDateString(), $locale).' – '.SalesFormat::date(($document->travel_to ?? $document->travel_from)->toDateString(), $locale)
                : '–',
            'total' => SalesFormat::money($document->total_amount, $locale),
            'last_sent' => $lastSent?->format('d.m.Y H:i') ?? '–',
            'url' => $this->links->customerUrl($document),
            'can_decline' => $this->flow->canDecline($document),
            'can_cancel' => $this->flow->canCancel($document),
        ];
    }
}
