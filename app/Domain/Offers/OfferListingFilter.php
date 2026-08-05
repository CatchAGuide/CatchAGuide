<?php

namespace App\Domain\Offers;

use App\Domain\Vacation\CountrySlug;
use App\Domain\Vacation\VacationListingFilter;

final class OfferListingFilter
{
    public const TYPES = ['all', 'tour', 'trip', 'camp'];

    public function __construct(
        public readonly string $type = 'all',
        public readonly ?string $species = null,
        public readonly ?string $country = null,
        public readonly ?string $sortBy = null,
        public readonly ?string $place = null,
        public readonly ?float $placeLat = null,
        public readonly ?float $placeLng = null,
        public readonly ?string $city = null,
        public readonly ?string $region = null,
    ) {}

    public static function fromRequest(array $input): self
    {
        $type = strtolower((string) ($input['type'] ?? 'all'));
        if (! in_array($type, self::TYPES, true)) {
            $type = 'all';
        }

        $sortBy = self::nullableString($input['sortby'] ?? null);
        if ($sortBy !== null && ! in_array($sortBy, ['newest', 'price-asc', 'price-desc'], true)) {
            $sortBy = null;
        }

        $lat = self::nullableFloat($input['placeLat'] ?? $input['place_lat'] ?? null);
        $lng = self::nullableFloat($input['placeLng'] ?? $input['place_lng'] ?? null);

        return new self(
            type: $type,
            species: self::nullableString($input['species'] ?? null),
            country: CountrySlug::canonicalize(self::nullableString($input['country'] ?? null)),
            sortBy: $sortBy,
            place: self::nullableString($input['place'] ?? null),
            placeLat: $lat,
            placeLng: $lng,
            city: self::nullableString($input['city'] ?? null),
            region: self::nullableString($input['region'] ?? null),
        );
    }

    public function showsTours(): bool
    {
        return $this->type === 'all' || $this->type === 'tour';
    }

    public function showsTrips(): bool
    {
        return $this->type === 'all' || $this->type === 'trip';
    }

    public function showsCamps(): bool
    {
        return $this->type === 'all' || $this->type === 'camp';
    }

    public function toVacationFilter(): VacationListingFilter
    {
        $pillar = match ($this->type) {
            'trip' => 'trips',
            'camp' => 'camps',
            default => 'all',
        };

        return new VacationListingFilter(
            pillar: $pillar,
            species: $this->species,
            country: $this->country,
            sortBy: $this->sortBy,
        );
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
}
