<?php

namespace App\Domain\Vacation;

final class VacationListingFilter
{
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
    ) {}

    public static function fromRequest(array $input, ?string $country = null): self
    {
        $pillar = (string) ($input['pillar'] ?? 'all');
        if (! in_array($pillar, ['all', 'trips', 'camps'], true)) {
            $pillar = 'all';
        }

        $resolvedCountry = $country ?? self::nullableString($input['country'] ?? null);
        [$speciesIds, $speciesNames] = self::normalizeSpecies($input['species'] ?? null);

        return new self(
            pillar: $pillar,
            speciesIds: $speciesIds,
            speciesNames: $speciesNames,
            country: self::normalizeCountry($resolvedCountry),
            sortBy: self::nullableString($input['sortby'] ?? null),
            countryShort: self::nullableString($input['country_short'] ?? null),
        );
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

    private static function nullableString(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (string) $value;
    }
}
