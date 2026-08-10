<?php

namespace App\Domain\Offers;

use App\Domain\Vacation\CountrySlug;
use App\Domain\Vacation\VacationListingFilter;

final class OfferListingFilter
{
    public const TYPES = ['all', 'tour', 'vacation'];

    public const VACATION_FILTERS = ['all', 'trip', 'camp'];

    public const MAX_GUESTS = 20;

    /**
     * @param  array<int, string>  $placeTypes
     */
    public function __construct(
        public readonly string $type = 'all',
        public readonly string $vacation = 'all',
        public readonly ?string $species = null,
        public readonly ?string $country = null,
        public readonly ?string $sortBy = null,
        public readonly ?string $place = null,
        public readonly ?float $placeLat = null,
        public readonly ?float $placeLng = null,
        public readonly ?string $city = null,
        public readonly ?string $region = null,
        public readonly ?int $numGuests = null,
        public readonly array $placeTypes = [],
        public readonly ?float $boundsNeLat = null,
        public readonly ?float $boundsNeLng = null,
        public readonly ?float $boundsSwLat = null,
        public readonly ?float $boundsSwLng = null,
        public readonly ?string $countryShort = null,
    ) {}

    public static function fromRequest(array $input): self
    {
        $rawType = strtolower((string) ($input['type'] ?? 'all'));
        $vacation = strtolower((string) ($input['vacation'] ?? 'all'));

        // Legacy URLs: ?type=trip|camp → vacation primary + subfilter.
        if (in_array($rawType, ['trip', 'camp'], true)) {
            $vacation = $rawType;
            $rawType = 'vacation';
        }

        if (! in_array($rawType, self::TYPES, true)) {
            $rawType = 'all';
        }

        if ($rawType !== 'vacation' || ! in_array($vacation, self::VACATION_FILTERS, true)) {
            $vacation = 'all';
        }

        $sortBy = self::nullableString($input['sortby'] ?? null);
        if ($sortBy !== null && ! in_array($sortBy, ['newest', 'price-asc', 'price-desc'], true)) {
            $sortBy = null;
        }

        $lat = self::nullableFloat($input['placeLat'] ?? $input['place_lat'] ?? null);
        $lng = self::nullableFloat($input['placeLng'] ?? $input['place_lng'] ?? null);

        return new self(
            type: $rawType,
            vacation: $vacation,
            species: self::nullableString($input['species'] ?? null),
            country: CountrySlug::canonicalize(self::nullableString($input['country'] ?? null)),
            sortBy: $sortBy,
            place: self::nullableString($input['place'] ?? null),
            placeLat: $lat,
            placeLng: $lng,
            city: self::nullableString($input['city'] ?? null),
            region: self::nullableString($input['region'] ?? null),
            numGuests: self::nullableGuests($input['num_guests'] ?? $input['num_persons'] ?? null),
            placeTypes: self::normalizePlaceTypes($input['place_types'] ?? null),
            boundsNeLat: self::nullableFloat($input['bounds_ne_lat'] ?? null),
            boundsNeLng: self::nullableFloat($input['bounds_ne_lng'] ?? null),
            boundsSwLat: self::nullableFloat($input['bounds_sw_lat'] ?? null),
            boundsSwLng: self::nullableFloat($input['bounds_sw_lng'] ?? null),
            countryShort: self::nullableString($input['country_short'] ?? null),
        );
    }

    public function isVacation(): bool
    {
        return $this->type === 'vacation';
    }

    public function showsTours(): bool
    {
        return $this->type === 'all' || $this->type === 'tour';
    }

    public function showsTrips(): bool
    {
        if ($this->type === 'all') {
            return true;
        }

        if ($this->type !== 'vacation') {
            return false;
        }

        return $this->vacation === 'all' || $this->vacation === 'trip';
    }

    public function showsCamps(): bool
    {
        if ($this->type === 'all') {
            return true;
        }

        if ($this->type !== 'vacation') {
            return false;
        }

        return $this->vacation === 'all' || $this->vacation === 'camp';
    }

    public function toVacationFilter(): VacationListingFilter
    {
        $pillar = match (true) {
            $this->type === 'vacation' && $this->vacation === 'trip' => 'trips',
            $this->type === 'vacation' && $this->vacation === 'camp' => 'camps',
            default => 'all',
        };

        return new VacationListingFilter(
            pillar: $pillar,
            species: $this->species,
            country: $this->country,
            sortBy: $this->sortBy,
            countryShort: $this->countryShort,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function geoSearchParams(): array
    {
        return array_filter([
            'city' => $this->city,
            'country' => $this->country,
            'region' => $this->region,
            'placeLat' => $this->placeLat,
            'placeLng' => $this->placeLng,
            'place_types' => $this->placeTypes !== [] ? $this->placeTypes : null,
            'bounds_ne_lat' => $this->boundsNeLat,
            'bounds_ne_lng' => $this->boundsNeLng,
            'bounds_sw_lat' => $this->boundsSwLat,
            'bounds_sw_lng' => $this->boundsSwLng,
            'country_short' => $this->countryShort,
        ], fn ($v) => $v !== null && $v !== '');
    }

    private static function nullableString(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (string) $value;
    }

    private static function nullableFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }

    private static function nullableGuests(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_numeric($value)) {
            return null;
        }

        $guests = (int) $value;
        if ($guests < 1) {
            return null;
        }

        return min($guests, self::MAX_GUESTS);
    }

    /**
     * @return array<int, string>
     */
    private static function normalizePlaceTypes(mixed $placeTypes): array
    {
        if (is_string($placeTypes) && $placeTypes !== '') {
            $decoded = json_decode($placeTypes, true);
            $placeTypes = is_array($decoded) ? $decoded : [];
        }

        if (! is_array($placeTypes)) {
            return [];
        }

        return array_values(array_filter(array_map(
            static fn ($type) => is_string($type) ? $type : null,
            $placeTypes
        )));
    }
}
