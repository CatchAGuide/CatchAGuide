<?php

namespace App\Domain\Vacation;

final class VacationListingFilter
{
    public const TRIP_DURATION_BUCKETS = ['1-3', '4-7', '8+'];

    public const MAX_GUESTS = 20;

    public const DEFAULT_GUESTS = 1;

    /**
     * Camp-only sidebar facets that must not leak onto trips / mixed views.
     *
     * @var list<string>
     */
    public const CAMP_FACET_KEYS = [
        'accommodation_type',
        'has_guiding',
        'has_rental_boat',
    ];

    /**
     * @param  list<int>  $speciesIds
     * @param  list<string>  $speciesNames
     */
    public function __construct(
        public readonly string $pillar = 'all',
        public readonly array $speciesIds = [],
        public readonly array $speciesNames = [],
        public readonly ?string $country = null,
        public readonly ?string $sortBy = null,
        public readonly ?string $countryShort = null,
        public readonly ?string $tripDuration = null,
        public readonly ?int $accommodationTypeId = null,
        public readonly ?bool $hasGuiding = null,
        public readonly ?bool $hasRentalBoat = null,
        public readonly ?int $numGuests = null,
    ) {}

    public static function fromRequest(array $input, ?string $country = null): self
    {
        $pillar = (string) ($input['pillar'] ?? 'all');
        if (! in_array($pillar, ['all', 'trips', 'camps'], true)) {
            $pillar = 'all';
        }

        $resolvedCountry = $country ?? self::nullableString($input['country'] ?? null);
        [$speciesIds, $speciesNames] = self::normalizeSpecies($input['species'] ?? null);

        $accommodationTypeId = null;
        $hasGuiding = null;
        $hasRentalBoat = null;
        if ($pillar === 'camps') {
            $accommodationTypeId = self::nullablePositiveInt($input['accommodation_type'] ?? null);
            $hasGuiding = self::nullableBool($input['has_guiding'] ?? null);
            $hasRentalBoat = self::nullableBool($input['has_rental_boat'] ?? null);
        }

        return new self(
            pillar: $pillar,
            speciesIds: $speciesIds,
            speciesNames: $speciesNames,
            country: self::normalizeCountry($resolvedCountry),
            sortBy: self::nullableString($input['sortby'] ?? null),
            countryShort: self::nullableString($input['country_short'] ?? null),
            tripDuration: self::normalizeTripDuration($input['duration'] ?? null),
            accommodationTypeId: $accommodationTypeId,
            hasGuiding: $hasGuiding,
            hasRentalBoat: $hasRentalBoat,
            numGuests: self::nullableGuests($input['num_guests'] ?? $input['num_persons'] ?? null),
        );
    }

    public static function normalizeTripDuration(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $duration = (string) $value;

        return in_array($duration, self::TRIP_DURATION_BUCKETS, true) ? $duration : null;
    }

    /**
     * @return array{0: list<int>, 1: list<string>}
     */
    public static function normalizeSpecies(mixed $value): array
    {
        if ($value === null || $value === '') {
            return [[], []];
        }

        $items = is_array($value) ? $value : [$value];
        $ids = [];
        $names = [];

        foreach ($items as $item) {
            if ($item === null || $item === '') {
                continue;
            }

            if (is_numeric($item) && (int) $item > 0) {
                $ids[] = (int) $item;
                continue;
            }

            if (is_string($item)) {
                $trimmed = trim($item);
                if ($trimmed !== '') {
                    $names[] = $trimmed;
                }
            }
        }

        return [
            array_values(array_unique($ids)),
            array_values(array_unique($names)),
        ];
    }

    /**
     * Legacy single species label (first unresolved name, else null).
     */
    public function species(): ?string
    {
        return $this->speciesNames[0] ?? null;
    }

    private static function normalizeCountry(?string $country): ?string
    {
        if ($country === null || strtolower($country) === 'all-offers') {
            return null;
        }

        return CountrySlug::canonicalize($country);
    }

    public function showsTrips(): bool
    {
        return $this->pillar === 'all' || $this->pillar === 'trips';
    }

    public function showsCamps(): bool
    {
        return $this->pillar === 'all' || $this->pillar === 'camps';
    }

    public function showsTripDurationFilter(): bool
    {
        return $this->pillar === 'trips';
    }

    public function showsCampFacets(): bool
    {
        return $this->pillar === 'camps';
    }

    /**
     * Query params to keep when navigating to a camps listing.
     *
     * @return array<string, int|string>
     */
    public function campFacetQueryParams(): array
    {
        return array_filter([
            'accommodation_type' => $this->accommodationTypeId,
            'has_guiding' => $this->hasGuiding === null ? null : ($this->hasGuiding ? '1' : '0'),
            'has_rental_boat' => $this->hasRentalBoat === null ? null : ($this->hasRentalBoat ? '1' : '0'),
        ], fn ($v) => $v !== null && $v !== '');
    }

    private static function nullableString(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (string) $value;
    }

    private static function nullablePositiveInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_numeric($value)) {
            return null;
        }

        $int = (int) $value;

        return $int > 0 ? $int : null;
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

    private static function nullableBool(mixed $value): ?bool
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_bool($value)) {
            return $value;
        }

        $normalized = strtolower((string) $value);

        return match ($normalized) {
            '1', 'true', 'yes' => true,
            '0', 'false', 'no' => false,
            default => null,
        };
    }
}
