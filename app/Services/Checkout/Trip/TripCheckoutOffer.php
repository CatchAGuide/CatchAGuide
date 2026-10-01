<?php

namespace App\Services\Checkout\Trip;

use App\Models\Trip;
use App\Models\TripAvailabilityDate;
use Carbon\CarbonImmutable;

/**
 * What the trip checkout can offer for one trip: its requestable departures and the estimate.
 *
 * A trip with upcoming departures that still have room is requested for one of those dates
 * (the dropdown). Year-round trips, and trips whose departures have all passed or are fully
 * booked, are requested for a preferred travel window instead.
 */
final class TripCheckoutOffer
{
    public const CURRENCY = 'EUR';

    /** Party size cap when the trip doesn't set a maximum group size. */
    public const DEFAULT_MAX_PERSONS = 20;

    /** "Only N spots left" from this many free spots down. */
    public const FEW_SPOTS = 2;

    /** @var array<string, array{date: string, end: ?string, spots: ?int}>|null */
    private ?array $departures = null;

    private function __construct(
        private readonly Trip $trip,
    ) {}

    public static function for(Trip $trip): self
    {
        return new self($trip);
    }

    public function trip(): Trip
    {
        return $this->trip;
    }

    /**
     * Upcoming departures that can still be requested, keyed and ordered by date (Y-m-d).
     *
     * @return array<string, array{date: string, end: ?string, spots: ?int}>
     */
    public function departures(): array
    {
        if ($this->departures !== null) {
            return $this->departures;
        }

        if ($this->trip->year_round_availability) {
            return $this->departures = [];
        }

        $today = CarbonImmutable::today();
        $nights = $this->tripNights();

        return $this->departures = $this->trip->availabilityDates
            ->filter(fn (TripAvailabilityDate $date) => $date->departure_date !== null
                && $date->departure_date->gte($today)
                && $date->spots_available !== 0)
            ->sortBy(fn (TripAvailabilityDate $date) => $date->departure_date->toDateString())
            ->mapWithKeys(function (TripAvailabilityDate $date) use ($nights) {
                $start = CarbonImmutable::parse($date->departure_date->toDateString());

                return [$start->toDateString() => [
                    'date' => $start->toDateString(),
                    'end' => $nights > 0 ? $start->addDays($nights)->toDateString() : null,
                    'spots' => $date->spots_available,
                ]];
            })
            ->all();
    }

    /**
     * Fixed departures to pick from; otherwise the guest names a preferred travel window.
     */
    public function usesFixedDates(): bool
    {
        return $this->departures() !== [];
    }

    /**
     * @return array{date: string, end: ?string, spots: ?int}|null
     */
    public function departure(?string $date): ?array
    {
        return $date !== null ? ($this->departures()[$date] ?? null) : null;
    }

    public function pricePerPerson(): ?float
    {
        $price = (float) $this->trip->price_per_person;

        return $price > 0 ? $price : null;
    }

    public function maxPersons(): int
    {
        $max = (int) $this->trip->group_size_max;

        return $max > 0 ? min($max, self::DEFAULT_MAX_PERSONS) : self::DEFAULT_MAX_PERSONS;
    }

    /**
     * Price per person × party size; null when the trip has no published price.
     */
    public function estimate(int $persons): ?float
    {
        $price = $this->pricePerPerson();

        return $price !== null ? round($price * $persons, 2) : null;
    }

    public function durationDays(): int
    {
        return max(0, (int) $this->trip->duration_days);
    }

    public function durationNights(): int
    {
        return max(0, (int) $this->trip->duration_nights);
    }

    /**
     * Nights between departure and return. Falls back to days − 1 when only days are set.
     */
    private function tripNights(): int
    {
        return $this->durationNights() ?: max(0, $this->durationDays() - 1);
    }
}
