<?php

namespace App\Domain\Vacation;

final class VacationListingFilter
{
    public function __construct(
        public readonly string $pillar = 'all',
        public readonly ?string $species = null,
        public readonly ?string $duration = null,
        public readonly ?string $country = null,
        public readonly ?string $sortBy = null,
    ) {}

    public static function fromRequest(array $input, ?string $country = null): self
    {
        $pillar = (string) ($input['pillar'] ?? 'all');
        if (! in_array($pillar, ['all', 'trips', 'camps'], true)) {
            $pillar = 'all';
        }

        return new self(
            pillar: $pillar,
            species: self::nullableString($input['species'] ?? null),
            duration: self::nullableString($input['duration'] ?? null),
            country: $country ?? self::nullableString($input['country'] ?? null),
            sortBy: self::nullableString($input['sortby'] ?? null),
        );
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
