<?php

namespace App\Services\Sales;

use App\Models\SalesDocument;

/**
 * Converts between a saved sales document and the builder state (header + product cards).
 * Each card item keeps its full card state in meta['state'], so a reopened document is
 * priced from exactly the snapshots it was saved with.
 */
class SalesDocumentStateMapper
{
    /**
     * Header of a new document.
     *
     * @return array<string, mixed>
     */
    public function blankHeader(): array
    {
        return [
            'recipient_mode' => 'contact',
            'customer_id' => null,
            'first_name' => '',
            'last_name' => '',
            'email' => '',
            'phone' => '',
            'travellers' => '',
            'language' => 'de',
            'valid_until' => now()->addDays((int) config('sales_documents.validity_days', 14))->toDateString(),
            'intro_offer' => '',
            'intro_confirmation' => '',
            'good_to_know' => '',
            'not_included' => [],
            'payment_note' => '',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function toHeader(SalesDocument $document): array
    {
        return [
            'recipient_mode' => $document->customer_id ? 'customer' : 'contact',
            'customer_id' => $document->customer_id,
            'first_name' => (string) $document->first_name,
            'last_name' => (string) $document->last_name,
            'email' => (string) $document->email,
            'phone' => (string) $document->phone,
            'travellers' => implode(', ', $document->traveller_names ?? []),
            'language' => $document->locale(),
            'valid_until' => $document->valid_until?->toDateString() ?? '',
            'intro_offer' => (string) $document->intro_text_offer,
            'intro_confirmation' => (string) $document->intro_text_confirmation,
            'good_to_know' => (string) $document->good_to_know,
            'not_included' => array_values($document->not_included ?? []),
            'payment_note' => (string) $document->payment_note,
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function toCards(SalesDocument $document): array
    {
        $items = $document->relationLoaded('items') ? $document->items : $document->items()->get();

        return $items
            ->whereNull('parent_item_id')
            ->sortBy('sort_order')
            ->map(fn ($item) => $item->meta['state'] ?? null)
            ->filter(fn ($state) => is_array($state))
            ->values()
            ->all();
    }

    /**
     * Document columns for a header (validated values; empty lines and names dropped).
     *
     * @param  array<string, mixed>  $header
     * @return array<string, mixed>
     */
    public function headerAttributes(array $header): array
    {
        $text = fn (string $key): ?string => ($value = trim((string) ($header[$key] ?? ''))) !== '' ? $value : null;

        return [
            'customer_id' => ($header['recipient_mode'] ?? 'contact') === 'customer' && ! empty($header['customer_id']) ? (int) $header['customer_id'] : null,
            'first_name' => $text('first_name'),
            'last_name' => $text('last_name'),
            'email' => $text('email'),
            'phone' => $text('phone'),
            'traveller_names' => self::travellers((string) ($header['travellers'] ?? '')),
            'language' => in_array($header['language'] ?? null, ['de', 'en'], true) ? $header['language'] : 'de',
            'valid_until' => SalesFormat::parse($header['valid_until'] ?? null)?->toDateString(),
            'intro_text_offer' => $text('intro_offer'),
            'intro_text_confirmation' => $text('intro_confirmation'),
            'good_to_know' => $text('good_to_know'),
            'not_included' => self::lines((array) ($header['not_included'] ?? [])),
            'payment_note' => $text('payment_note'),
        ];
    }

    /**
     * "Leo, Max" → ['Leo', 'Max'].
     *
     * @return list<string>
     */
    public static function travellers(string $value): array
    {
        return array_values(array_filter(array_map('trim', explode(',', $value)), fn (string $name) => $name !== ''));
    }

    /**
     * @param  array<int, mixed>  $lines
     * @return list<string>
     */
    public static function lines(array $lines): array
    {
        return array_values(array_filter(array_map(fn ($line) => trim((string) $line), $lines), fn (string $line) => $line !== ''));
    }
}
