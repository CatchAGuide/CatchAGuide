<?php

namespace App\Services\Sales;

use App\Enums\Sales\SalesDocumentStatus;
use App\Enums\Sales\SalesEventType;
use App\Models\CustomCampOffer;
use App\Models\Employee;
use App\Models\SalesDocument;
use App\Services\Sales\SalesDocumentStateMapper as Mapper;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/**
 * One-off migration of the former camps-only offer prototype (custom_camp_offers) into sales
 * documents. Each offer block becomes a camp card whose options carry the price the customer
 * was sent as a manual price, so imported totals match the old emails even where the old
 * prototype allowed options that are not linked to the camp. Idempotent via
 * sales_documents.legacy_custom_camp_offer_id.
 */
class CustomCampOfferImporter
{
    private const STATUS_MAP = [
        CustomCampOffer::STATUS_SENT => SalesDocumentStatus::Sent,
        CustomCampOffer::STATUS_PENDING => SalesDocumentStatus::Sent,
        CustomCampOffer::STATUS_FOLLOW_UP => SalesDocumentStatus::Sent,
        CustomCampOffer::STATUS_ACCEPTED => SalesDocumentStatus::Accepted,
        CustomCampOffer::STATUS_REJECTED => SalesDocumentStatus::Declined,
    ];

    private int $key = 1;

    public function __construct(
        private readonly SalesCatalog $catalog,
        private readonly SalesDocumentWriter $writer,
    ) {}

    public function alreadyImported(CustomCampOffer $offer): bool
    {
        return SalesDocument::query()->where('legacy_custom_camp_offer_id', $offer->id)->exists();
    }

    public function import(CustomCampOffer $offer): ?SalesDocument
    {
        if ($this->alreadyImported($offer)) {
            return null;
        }

        $locale = in_array($offer->locale, ['de', 'en'], true) ? $offer->locale : 'de';
        $blocks = array_values(array_filter((array) ($offer->offers ?? []), 'is_array'));
        $cards = [];
        $notes = [];

        foreach ($blocks as $block) {
            array_push($cards, ...$this->cards($block, $locale));
            if (filled($block['additional_info'] ?? null)) {
                $notes[] = trim((string) $block['additional_info']);
            }
        }

        [$firstName, $lastName] = $this->splitName((string) $offer->recipient_name, (string) $offer->recipient_email);
        $sentAt = $offer->sent_at ? CarbonImmutable::parse($offer->sent_at) : CarbonImmutable::parse($offer->created_at);
        $creator = $offer->created_by ? Employee::query()->find($offer->created_by) : null;

        $header = [
            'recipient_mode' => $offer->recipient_type === 'customer' && $offer->customer_id ? 'customer' : 'contact',
            'customer_id' => $offer->customer_id,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => (string) $offer->recipient_email,
            'phone' => (string) $offer->recipient_phone,
            'travellers' => '',
            'language' => $locale,
            'valid_until' => $sentAt->addDays((int) config('sales_documents.validity_days', 14))->toDateString(),
            'intro_offer' => '',
            'intro_confirmation' => '',
            'good_to_know' => implode("\n\n", array_unique(array_filter([trim((string) $offer->free_text), ...$notes]))),
            'not_included' => [],
            'payment_note' => '',
        ];

        return DB::transaction(function () use ($offer, $header, $cards, $creator, $sentAt) {
            $document = $this->writer->save(new SalesDocument, $header, $cards, $creator);
            $status = self::STATUS_MAP[$offer->status ?? CustomCampOffer::STATUS_SENT] ?? SalesDocumentStatus::Sent;

            $document->forceFill([
                'status' => $status,
                'legacy_custom_camp_offer_id' => $offer->id,
                'offer_sent_at' => $sentAt,
                'accepted_at' => $status === SalesDocumentStatus::Accepted ? $offer->updated_at : null,
                // Old acceptances were handled by the team already; no "new acceptance" badge.
                'acceptance_seen_at' => $status === SalesDocumentStatus::Accepted ? now() : null,
                'created_at' => $offer->created_at,
            ])->save();

            $this->writer->event($document, SalesEventType::Imported, null, [
                'custom_camp_offer_id' => $offer->id,
                'status' => $offer->status,
            ]);

            return $document;
        });
    }

    /**
     * Cards for one offer block of the old prototype: a camp card, or custom lines when the
     * block had no camp.
     *
     * @param  array<string, mixed>  $block
     * @return list<array<string, mixed>>
     */
    private function cards(array $block, string $locale): array
    {
        $from = SalesFormat::parse($block['date_from'] ?? null)?->toDateString() ?? '';
        $to = SalesFormat::parse($block['date_to'] ?? null)?->toDateString() ?? '';
        $persons = max(1, (int) ($block['number_of_persons'] ?? 1));
        $stayDays = $from !== '' && $to !== '' ? max(1, (int) SalesFormat::parse($from)->diffInDays(SalesFormat::parse($to)) + 1) : 1;

        $components = [
            'accommodation' => (array) ($block['accommodation_prices'] ?? []),
            'boat' => (array) ($block['boat_prices'] ?? []),
            'guiding' => (array) ($block['guiding_prices'] ?? []),
        ];

        $product = ! empty($block['camp_id']) ? $this->catalog->camp((int) $block['camp_id'], $locale) : null;

        if ($product === null) {
            $cards = [];
            foreach ($components as $rows) {
                foreach ($rows as $row) {
                    $amount = $this->oldAmount($row);
                    $cards[] = [
                        'key' => 'c'.$this->key++,
                        'type' => 'custom',
                        'title' => trim((string) ($row['title'] ?? '')) ?: __('sales.untitled', [], $locale),
                        'description' => '',
                        'date' => $from,
                        'quantity' => 1,
                        'unit_label' => '',
                        'unit_price' => number_format($amount, 2, '.', ''),
                    ];
                }
            }

            return $cards;
        }

        $groups = ['accommodation' => 'accommodations', 'boat' => 'boats', 'guiding' => 'guidings'];
        $subs = [];
        foreach ($components as $kind => $rows) {
            foreach ($rows as $row) {
                $id = (int) ($row['id'] ?? 0);
                if ($id <= 0) {
                    continue;
                }

                // The old prototype offered any option, not only the camp's own: keep it priced.
                if (! isset($product[$groups[$kind]][$id])) {
                    $product[$groups[$kind]][$id] = $this->legacyOption($kind, $id, (string) ($row['title'] ?? ''));
                }

                $amount = $this->oldAmount($row);
                $subs[] = [
                    'key' => 's'.$this->key++,
                    'kind' => $kind,
                    'option_id' => $id,
                    'from' => $kind === 'accommodation' ? $from : '',
                    'to' => $kind === 'accommodation' ? $to : '',
                    'date' => $kind === 'guiding' ? $from : '',
                    'days' => $kind === 'boat' ? max(1, (int) ($row['days'] ?? $stayDays)) : 1,
                    'qty' => max(1, (int) ($row['qty'] ?? 1)),
                    // Always the amount that was sent (also 0), never a re-priced listing value.
                    'override' => number_format($amount, 2, '.', ''),
                ];
            }
        }

        return [[
            'key' => 'c'.$this->key++,
            'type' => 'camp',
            'listing_id' => $product['id'],
            'product' => $product,
            'date' => '',
            'persons' => $persons,
            'override' => '',
            'extras' => [],
            'subs' => $subs,
        ]];
    }

    /**
     * What the old prototype charged for a component: unit price × qty × days.
     *
     * @param  array<string, mixed>  $row
     */
    private function oldAmount(array $row): float
    {
        $qty = max(1.0, (float) ($row['qty'] ?? 1));
        $days = max(1.0, (float) ($row['days'] ?? 1));

        return round(max(0.0, (float) ($row['price'] ?? 0)) * $qty * $days, 2);
    }

    /**
     * @return array<string, mixed>
     */
    private function legacyOption(string $kind, int $id, string $title): array
    {
        $option = ['id' => $id, 'name' => trim($title) !== '' ? trim($title) : '#'.$id, 'capacity' => 99];

        return match ($kind) {
            'accommodation' => $option + ['tiers' => []],
            'boat' => $option + ['daily' => null, 'weekly' => null],
            default => $option + ['prices' => []],
        };
    }

    /**
     * "Lena Maria Schmidt" → ["Lena", "Maria Schmidt"]; the email's local part when no name.
     *
     * @return array{0: string, 1: string}
     */
    private function splitName(string $name, string $email): array
    {
        $name = trim($name);
        if ($name === '' || $name === $email) {
            return [Mapper::travellers(str_replace(['.', '_'], ',', strstr($email, '@', true) ?: ''))[0] ?? '', ''];
        }

        $parts = preg_split('/\s+/', $name) ?: [$name];

        return [array_shift($parts), implode(' ', $parts)];
    }
}
