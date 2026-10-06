<?php

namespace App\Services\Sales;

use App\Enums\Sales\SalesEventType;
use App\Enums\Sales\SalesItemType;
use App\Models\Employee;
use App\Models\SalesDocument;
use App\Models\SalesDocumentEvent;
use App\Models\SalesDocumentItem;
use Illuminate\Support\Facades\DB;

/**
 * Persists the builder state: header columns, the priced items (card rows with their child
 * lines) and the derived total and travel period. Saving never emails the customer.
 */
class SalesDocumentWriter
{
    public function __construct(
        private readonly SalesDocumentCalculator $calculator,
        private readonly SalesDocumentStateMapper $mapper,
    ) {}

    /**
     * @param  array<string, mixed>  $header
     * @param  list<array<string, mixed>>  $cards
     */
    public function save(SalesDocument $document, array $header, array $cards, ?Employee $actor): SalesDocument
    {
        return DB::transaction(function () use ($document, $header, $cards, $actor) {
            $isNew = ! $document->exists;
            $attributes = $this->mapper->headerAttributes($header);
            $quote = $this->calculator->calculate($cards, $attributes['language']);
            $previousAdjustments = $isNew ? [] : $this->adjustments($document);

            $document->fill($attributes + [
                'total_amount' => $quote->total,
                'travel_from' => $quote->travelFrom,
                'travel_to' => $quote->travelTo,
                'updated_by' => $actor?->id,
            ]);
            if ($isNew) {
                $document->created_by = $actor?->id;
            }
            $document->save();

            // Child lines first, then the cards: MySQL stops a multi-row delete partway when the
            // self-referencing ON DELETE CASCADE hits the same table, which left old cards behind.
            SalesDocumentItem::query()->where('document_id', $document->id)->whereNotNull('parent_item_id')->delete();
            SalesDocumentItem::query()->where('document_id', $document->id)->delete();
            $this->writeItems($document, array_values($cards), $quote);

            if ($isNew) {
                $this->event($document, SalesEventType::Created, $actor);
            }

            foreach ($this->adjustments($document) as $key => $adjustment) {
                if (($previousAdjustments[$key]['total'] ?? null) !== $adjustment['total']) {
                    $this->event($document, SalesEventType::PriceAdjusted, $actor, $adjustment);
                }
            }

            return $document->refresh();
        });
    }

    public function event(SalesDocument $document, SalesEventType $type, ?Employee $actor, array $payload = [], string $actorType = SalesDocumentEvent::ACTOR_EMPLOYEE): SalesDocumentEvent
    {
        return $document->events()->create([
            'type' => $type,
            'employee_id' => $actor?->id,
            'actor' => $actor === null && $actorType === SalesDocumentEvent::ACTOR_EMPLOYEE ? SalesDocumentEvent::ACTOR_SYSTEM : $actorType,
            'payload' => $payload ?: null,
        ]);
    }

    /**
     * @param  list<array<string, mixed>>  $cards
     */
    private function writeItems(SalesDocument $document, array $cards, SalesQuote $quote): void
    {
        $groups = [];
        foreach ($quote->groups as $group) {
            $groups[$group['key']] = $group;
        }

        foreach ($cards as $position => $card) {
            $group = $groups[(string) ($card['key'] ?? '')] ?? null;
            if ($group === null) {
                continue;
            }

            $type = SalesItemType::from($group['type']);
            $lines = $group['lines'];
            // Tour, trip and custom cards are their own first line; a camp card only groups its options.
            $own = $type === SalesItemType::Camp ? null : array_shift($lines);

            $cardItem = $document->items()->create($this->lineAttributes($own, [
                'item_type' => $type,
                'listing_type' => $own['listing_type'] ?? ($type === SalesItemType::Camp ? 'camp' : null),
                'listing_id' => $group['listing_id'],
                'partner_id' => $group['partner']['id'] ?? null,
                'title_snapshot' => $group['title'] !== '' ? $group['title'] : null,
                'location_snapshot' => $group['location'] !== '' ? $group['location'] : null,
                'listing_url_snapshot' => $group['url'],
                'description' => $type === SalesItemType::Custom ? (trim((string) ($card['description'] ?? '')) ?: null) : null,
                'unit_label' => $type === SalesItemType::Custom ? (trim((string) ($card['unit_label'] ?? '')) ?: null) : null,
                'sort_order' => $position,
                'meta' => ['state' => $card],
            ]));

            foreach ($lines as $index => $line) {
                $document->items()->create($this->lineAttributes($line, [
                    'parent_item_id' => $cardItem->id,
                    'item_type' => $line['item_type'],
                    'listing_type' => $line['listing_type'],
                    'listing_id' => $line['listing_id'],
                    'partner_id' => $group['partner']['id'] ?? null,
                    'title_snapshot' => $line['title'],
                    'sort_order' => $index,
                    'meta' => array_filter(['key' => $line['key'], 'extra_key' => $line['extra_key']]),
                ]));
            }
        }
    }

    /**
     * @param  array<string, mixed>|null  $line
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function lineAttributes(?array $line, array $attributes): array
    {
        if ($line === null) {
            return $attributes + ['calculated_price' => 0, 'line_total' => 0, 'quantity' => 1];
        }

        if (! isset($attributes['meta']['key'])) {
            $attributes['meta'] = ($attributes['meta'] ?? []) + ['key' => $line['key']];
        }

        return $attributes + [
            'date_from' => $line['from'],
            'date_to' => $line['to'],
            'persons' => $line['persons'],
            'persons_follow_parent' => $line['persons_follow_parent'],
            'days' => $line['days'],
            'quantity' => max(0, (int) $line['quantity']),
            'price_unit' => $line['price_unit'] ?? null,
            'unit_price' => $line['unit_price'] ?? null,
            'calculated_price' => $line['calculated'],
            'line_total' => $line['total'],
            'is_adjusted' => $line['adjusted'],
        ];
    }

    /**
     * Manually priced lines by line key, for the price_adjusted log.
     *
     * @return array<string, array{line: string, calculated: string, total: string}>
     */
    private function adjustments(SalesDocument $document): array
    {
        return SalesDocumentItem::query()
            ->where('document_id', $document->id)
            ->where('is_adjusted', true)
            ->get()
            ->mapWithKeys(fn (SalesDocumentItem $item) => [(string) ($item->meta['key'] ?? $item->id) => [
                'line' => (string) $item->title_snapshot,
                'calculated' => (string) $item->calculated_price,
                'total' => (string) $item->line_total,
            ]])
            ->all();
    }
}
