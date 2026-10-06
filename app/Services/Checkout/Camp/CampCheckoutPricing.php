<?php

namespace App\Services\Checkout\Camp;

use App\Enums\AccommodationPriceUnit;
use App\Models\Accommodation;
use App\Models\Camp;
use App\Models\Guiding;
use App\Models\RentalBoat;
use App\Models\SpecialOffer;
use App\Services\Checkout\TourCheckoutPricing;

/**
 * Server-authoritative price estimate for one camp checkout: the bookable accommodations,
 * rental boats, guided tours and special offers of a camp, and the quote for a selection.
 *
 * Built once per request via for() from a camp whose relations were loaded by
 * CampCheckoutPricing::relations(), so no further queries run while quoting.
 */
final class CampCheckoutPricing
{
    public const MAX_NIGHTS = 30;

    public const MAX_PERSONS = 20;

    public const CURRENCY = 'EUR';

    /**
     * @param  array<int, array{id: int, name: string, capacity: int, min_nights: int, unit: string, tiers: list<array{persons: int, rate: StayRate}>}>  $accommodations
     * @param  array<int, array{id: int, name: string, capacity: int, rate: StayRate}>  $boats
     * @param  array<int, array{id: int, name: string, capacity: int, prices: array<int, float>}>  $tours
     * @param  array<int, array{id: int, name: string, price: float}>  $specials
     */
    private function __construct(
        private readonly array $accommodations,
        private readonly array $boats,
        private readonly array $tours,
        private readonly array $specials,
    ) {}

    /**
     * Eager loads for the camp passed to for(): only what a guest can actually book.
     *
     * @return array<string, \Closure>
     */
    public static function relations(): array
    {
        return [
            'accommodations' => fn ($query) => $query->where('accommodations.status', 'active'),
            'rentalBoats' => fn ($query) => $query->where('rental_boats.status', 'active'),
            'guidings' => fn ($query) => $query->publiclyVisible(),
            'specialOffers' => fn ($query) => $query->where('special_offers.status', 'active'),
        ];
    }

    public static function for(Camp $camp): self
    {
        $accommodations = [];
        foreach ($camp->accommodations as $accommodation) {
            $accommodations[(int) $accommodation->id] = self::mapAccommodation($accommodation);
        }

        $boats = [];
        foreach ($camp->rentalBoats as $boat) {
            $boats[(int) $boat->id] = self::mapBoat($boat);
        }

        $tours = [];
        foreach ($camp->guidings as $guiding) {
            $tours[(int) $guiding->id] = self::mapTour($guiding);
        }

        $specials = [];
        foreach ($camp->specialOffers as $offer) {
            $specials[(int) $offer->id] = self::mapSpecial($offer);
        }

        return new self($accommodations, $boats, $tours, $specials);
    }

    /**
     * @return array<int, array{id: int, name: string, capacity: int, min_nights: int, unit: string, tiers: list<array{persons: int, rate: StayRate}>}>
     */
    public function accommodations(): array
    {
        return $this->accommodations;
    }

    /**
     * @return array<int, array{id: int, name: string, capacity: int, rate: StayRate}>
     */
    public function boats(): array
    {
        return $this->boats;
    }

    /**
     * @return array<int, array{id: int, name: string, capacity: int, prices: array<int, float>}>
     */
    public function tours(): array
    {
        return $this->tours;
    }

    /**
     * @return array<int, array{id: int, name: string, price: float}>
     */
    public function specials(): array
    {
        return $this->specials;
    }

    public function hasAccommodations(): bool
    {
        return $this->accommodations !== [];
    }

    public function minNights(?int $accommodationId): int
    {
        return $this->accommodations[$accommodationId]['min_nights'] ?? 1;
    }

    /**
     * Nightly rate of an accommodation for a party size: the tier for exactly that many
     * guests, else the largest tier below it, else the smallest tier.
     */
    public function accommodationRate(int $accommodationId, int $persons): StayRate
    {
        $tiers = $this->accommodations[$accommodationId]['tiers'] ?? [];
        $match = $tiers[0]['rate'] ?? StayRate::from(null, null);

        foreach ($tiers as $tier) {
            if ($tier['persons'] <= $persons) {
                $match = $tier['rate'];
            }
        }

        return $match;
    }

    public function tourPrice(int $guidingId, int $persons): float
    {
        $prices = $this->tours[$guidingId]['prices'] ?? [];
        if ($prices === []) {
            return 0.0;
        }

        return (float) ($prices[min(max(1, $persons), max(array_keys($prices)))] ?? 0.0);
    }

    public function quote(CampCheckoutSelection $selection): CampCheckoutQuote
    {
        $nights = max(1, min(self::MAX_NIGHTS, $selection->nights));
        $persons = max(1, min(self::MAX_PERSONS, $selection->persons));
        $lines = [];

        if ($unit = $this->accommodations[$selection->accommodationId] ?? null) {
            $rate = $this->accommodationRate($unit['id'], $persons);
            // Per-person-night units charge the nightly price for every guest.
            $factor = AccommodationPriceUnit::fromListing($unit['unit'] ?? null)->guestFactor($persons);
            $lines[] = $this->line('accommodation', $unit['id'], $unit['name'], $nights, $rate->daily !== null ? $rate->daily * $factor : null, $rate->total($nights) * $factor);
        }

        if ($boat = $this->boats[$selection->rentalBoatId] ?? null) {
            $lines[] = $this->line('boat', $boat['id'], $boat['name'], $nights, $boat['rate']->daily, $boat['rate']->total($nights));
        }

        if ($tour = $this->tours[$selection->guidingId] ?? null) {
            $price = $this->tourPrice($tour['id'], $persons);
            $lines[] = $this->line('tour', $tour['id'], $tour['name'], 1, $price, $price);
        }

        if ($special = $this->specials[$selection->specialOfferId] ?? null) {
            $lines[] = $this->line('special', $special['id'], $special['name'], 1, $special['price'], $special['price']);
        }

        return new CampCheckoutQuote($nights, $persons, $lines, self::CURRENCY);
    }

    /**
     * Price data for resources/js/checkout/camp-pricing.js. Display only: submissions re-quote.
     *
     * @return array<string, list<array<string, mixed>>>
     */
    public function clientConfig(): array
    {
        return [
            'accommodations' => array_values(array_map(fn (array $unit) => [
                'id' => $unit['id'],
                'name' => $unit['name'],
                'capacity' => $unit['capacity'],
                'minNights' => $unit['min_nights'],
                'unit' => $unit['unit'],
                'tiers' => array_map(fn (array $tier) => ['persons' => $tier['persons'], ...$tier['rate']->toArray()], $unit['tiers']),
            ], $this->accommodations)),
            'boats' => array_values(array_map(fn (array $boat) => [
                'id' => $boat['id'],
                'name' => $boat['name'],
                'capacity' => $boat['capacity'],
                ...$boat['rate']->toArray(),
            ], $this->boats)),
            'tours' => array_values(array_map(fn (array $tour) => [
                'id' => $tour['id'],
                'name' => $tour['name'],
                'capacity' => $tour['capacity'],
                'prices' => $tour['prices'],
            ], $this->tours)),
            'specials' => array_values($this->specials),
        ];
    }

    /**
     * @return array{type: string, id: int, name: string, quantity: int, unit_price: ?float, amount: float}
     */
    private function line(string $type, int $id, string $name, int $quantity, ?float $unitPrice, float $amount): array
    {
        return [
            'type' => $type,
            'id' => $id,
            'name' => $name,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'amount' => round($amount, 2),
        ];
    }

    /**
     * @return array{id: int, name: string, capacity: int, min_nights: int, unit: string, tiers: list<array{persons: int, rate: StayRate}>}
     */
    private static function mapAccommodation(Accommodation $accommodation): array
    {
        $tiers = [];
        foreach ((array) (decode_if_json($accommodation->per_person_pricing, true) ?: []) as $tier) {
            if (! is_array($tier)) {
                continue;
            }

            $rate = StayRate::from($tier['price_per_night'] ?? null, $tier['price_per_week'] ?? null);
            if ($rate->isPriced()) {
                $tiers[] = ['persons' => max(1, (int) ($tier['person_count'] ?? 1)), 'rate' => $rate];
            }
        }
        usort($tiers, fn (array $a, array $b) => $a['persons'] <=> $b['persons']);

        return [
            'id' => (int) $accommodation->id,
            'name' => self::name($accommodation->title),
            'capacity' => max(1, (int) $accommodation->max_occupancy),
            'min_nights' => max(1, min(self::MAX_NIGHTS, (int) $accommodation->minimum_stay_nights)),
            'unit' => AccommodationPriceUnit::fromListing($accommodation->price_unit)->value,
            'tiers' => $tiers,
        ];
    }

    /**
     * Boats store either ['per_day' => 220, 'per_week' => 900] or [['amount' => 220]] with the
     * unit in price_type. Hourly-only prices can't be turned into a stay estimate.
     *
     * @return array{id: int, name: string, capacity: int, rate: StayRate}
     */
    private static function mapBoat(RentalBoat $boat): array
    {
        $prices = (array) (decode_if_json($boat->prices, true) ?: []);

        if (isset($prices[0]) && is_array($prices[0])) {
            $amount = $prices[0]['amount'] ?? null;
            $rate = $boat->price_type === 'per_week' ? StayRate::from(null, $amount) : StayRate::from(
                in_array($boat->price_type, ['per_day', 'per_night', null], true) ? $amount : null,
                null,
            );
        } else {
            $rate = StayRate::from($prices['per_day'] ?? null, $prices['per_week'] ?? null);
        }

        return [
            'id' => (int) $boat->id,
            'name' => self::name($boat->title),
            'capacity' => max(1, (int) $boat->max_persons),
            'rate' => $rate,
        ];
    }

    /**
     * @return array{id: int, name: string, capacity: int, prices: array<int, float>}
     */
    private static function mapTour(Guiding $guiding): array
    {
        $pricing = TourCheckoutPricing::for($guiding);

        return [
            'id' => (int) $guiding->id,
            'name' => self::name($guiding->title),
            'capacity' => $pricing->maxGuests(),
            'prices' => $pricing->priceTable(),
        ];
    }

    /**
     * @return array{id: int, name: string, price: float}
     */
    private static function mapSpecial(SpecialOffer $offer): array
    {
        $price = 0.0;
        foreach ((array) (decode_if_json($offer->pricing, true) ?: []) as $tier) {
            if (is_array($tier) && is_numeric($tier['amount'] ?? null) && (float) $tier['amount'] > 0) {
                $price = round((float) $tier['amount'], 2);
                break;
            }
        }

        return [
            'id' => (int) $offer->id,
            'name' => self::name($offer->title),
            'price' => $price,
        ];
    }

    /**
     * Titles arrive already localized (CampCheckoutController applies the stored listing
     * translations), so no live translation call runs here.
     */
    private static function name(mixed $title): string
    {
        return trim((string) $title);
    }
}
