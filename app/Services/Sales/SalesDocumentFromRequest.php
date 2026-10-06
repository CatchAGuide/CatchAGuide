<?php

namespace App\Services\Sales;

use App\Models\CampVacationBooking;
use App\Models\Employee;
use App\Models\SalesDocument;
use App\Models\TripBooking;
use Illuminate\Support\Facades\DB;

/**
 * Turns a camp or trip checkout request into a draft sales document: the guest becomes the
 * recipient, the request language the document language, and the selection the product card
 * (camp: accommodation with the stay, rental boat for the nights, guiding on the arrival day;
 * trip: start date and persons). Prices are the listing's current ones, as for any new card —
 * the request's estimate stays on the request. One document per request: converting again
 * returns the existing one. An open request moves to "in process".
 */
class SalesDocumentFromRequest
{
    public function __construct(
        private readonly SalesCatalog $catalog,
        private readonly SalesDocumentWriter $writer,
        private readonly SalesDocumentStateMapper $mapper,
    ) {}

    public function existing(string $sourceType, int $requestId): ?SalesDocument
    {
        return SalesDocument::query()
            ->where('source_type', $sourceType)
            ->where('source_id', $requestId)
            ->orderBy('id')
            ->first();
    }

    public function fromCampRequest(CampVacationBooking $request, ?Employee $actor): SalesDocument
    {
        if ($document = $this->existing(SalesDocument::SOURCE_CAMP_REQUEST, $request->id)) {
            return $document;
        }

        $locale = $this->locale($request->customerLocale());
        $cards = $request->source_type === CampVacationBooking::SOURCE_CAMP && $request->source_id
            ? $this->campCards($request, $locale)
            : [];

        return $this->create($request, SalesDocument::SOURCE_CAMP_REQUEST, $locale, $cards, $actor);
    }

    public function fromTripRequest(TripBooking $request, ?Employee $actor): SalesDocument
    {
        if ($document = $this->existing(SalesDocument::SOURCE_TRIP_REQUEST, $request->id)) {
            return $document;
        }

        $locale = $this->locale($request->customerLocale());
        $product = $request->source_id ? $this->catalog->trip((int) $request->source_id, $locale) : null;
        $cards = $product === null ? [] : [[
            'key' => 'c1',
            'type' => 'trip',
            'listing_id' => $product['id'],
            'product' => $product,
            // A wish window starts the trip on its first day; the employee picks the departure.
            'date' => $request->preferred_date?->toDateString() ?? '',
            'persons' => max(1, (int) $request->number_of_persons),
            'override' => '',
        ]];

        return $this->create($request, SalesDocument::SOURCE_TRIP_REQUEST, $locale, $cards, $actor);
    }

    /**
     * @param  list<array<string, mixed>>  $cards
     */
    private function create(CampVacationBooking|TripBooking $request, string $sourceType, string $locale, array $cards, ?Employee $actor): SalesDocument
    {
        $header = array_merge($this->mapper->blankHeader(), [
            'recipient_mode' => $request->user_id ? 'customer' : 'contact',
            'customer_id' => $request->user_id,
            'first_name' => (string) ($request->first_name ?: strtok((string) $request->name, ' ')),
            'last_name' => (string) ($request->last_name ?: trim((string) strstr((string) $request->name, ' '))),
            'email' => (string) $request->email,
            'phone' => trim($request->phone_country_code.' '.$request->phone),
            'language' => $locale,
        ]);

        return DB::transaction(function () use ($request, $sourceType, $header, $cards, $actor) {
            $document = $this->writer->save(new SalesDocument, $header, $cards, $actor);
            $document->forceFill(['source_type' => $sourceType, 'source_id' => $request->id])->save();

            if ($request->status === $request::STATUS_OPEN) {
                $request->forceFill(['status' => $request::STATUS_IN_PROCESS])->save();
            }

            return $document;
        });
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function campCards(CampVacationBooking $request, string $locale): array
    {
        $product = $this->catalog->camp((int) $request->source_id, $locale);
        if ($product === null) {
            return [];
        }

        $arrival = $request->preferred_date?->toDateString() ?? '';
        $nights = max(1, (int) $request->nights);
        $departure = $request->preferred_date?->copy()->addDays($nights)->toDateString() ?? '';
        $breakdown = collect((array) ($request->price_breakdown ?? []));
        $subs = [];
        $custom = [];

        $selection = [
            ['accommodation', 'accommodations', $request->accommodation_id, 'accommodation'],
            ['boat', 'boats', $request->rental_boat_id, 'boat'],
            ['guiding', 'guidings', $request->guiding_id, 'tour'],
        ];

        foreach ($selection as [$kind, $group, $optionId, $breakdownType]) {
            if (! $optionId) {
                continue;
            }

            // An option the camp no longer offers keeps the request's price as a custom line.
            if (! isset($product[$group][$optionId])) {
                $line = $breakdown->first(fn ($row) => ($row['type'] ?? null) === $breakdownType);
                if ($line !== null) {
                    $custom[] = $this->customLine(count($custom) + 2, (string) $line['name'], (float) $line['amount'], $arrival);
                }

                continue;
            }

            $subs[] = [
                'key' => 's'.(count($subs) + 1),
                'kind' => $kind,
                'option_id' => (int) $optionId,
                'from' => $kind === 'accommodation' ? $arrival : '',
                'to' => $kind === 'accommodation' ? $departure : '',
                'date' => $kind === 'guiding' ? $arrival : '',
                // The camp checkout rents the boat for every night of the stay.
                'days' => $kind === 'boat' ? $nights : 1,
                'qty' => 1,
                'override' => '',
            ];
        }

        // Special offers have no card type in the builder; they keep their requested price.
        if ($request->special_offer_id && ($line = $breakdown->first(fn ($row) => ($row['type'] ?? null) === 'special'))) {
            $custom[] = $this->customLine(count($custom) + 2, (string) $line['name'], (float) $line['amount'], $arrival);
        }

        return [[
            'key' => 'c1',
            'type' => 'camp',
            'listing_id' => $product['id'],
            'product' => $product,
            'date' => '',
            'persons' => max(1, (int) $request->number_of_persons),
            'override' => '',
            'extras' => [],
            'subs' => $subs,
        ], ...$custom];
    }

    /**
     * @return array<string, mixed>
     */
    private function customLine(int $key, string $title, float $amount, string $date): array
    {
        return [
            'key' => 'c'.$key,
            'type' => 'custom',
            'title' => $title,
            'description' => '',
            'date' => $date,
            'quantity' => 1,
            'unit_label' => '',
            'unit_price' => number_format($amount, 2, '.', ''),
        ];
    }

    private function locale(string $locale): string
    {
        return $locale === 'en' ? 'en' : 'de';
    }
}
