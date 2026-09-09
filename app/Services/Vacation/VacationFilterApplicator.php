<?php

namespace App\Services\Vacation;

use App\Domain\Vacation\VacationListingFilter;
use App\Models\AccommodationType;
use App\Models\Camp;
use App\Models\Guiding;
use App\Models\Target;
use App\Models\Trip;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class VacationFilterApplicator
{
    public function applyToCampQuery(Builder $query, VacationListingFilter $filter): Builder
    {
        $query = $this->applySpeciesColumnFilter($query, 'target_fish', $filter->speciesIds, $filter->speciesNames);
        $query = $this->applyCampGuestsFilter($query, $filter->numGuests);

        return $this->applyCampFacets($query, $filter);
    }

    public function applyCampGuestsFilter(Builder $query, ?int $numGuests): Builder
    {
        if ($numGuests === null) {
            return $query;
        }

        return $query->whereHas('accommodations', function (Builder $q) use ($numGuests) {
            $q->where('accommodations.status', 'active')
                ->where(function (Builder $capacity) use ($numGuests) {
                    $capacity->whereNull('accommodations.max_occupancy')
                        ->orWhere('accommodations.max_occupancy', '>=', $numGuests);
                });
        });
    }

    public function applyCampFacets(Builder $query, VacationListingFilter $filter): Builder
    {
        if ($filter->accommodationTypeId !== null) {
            $typeId = $filter->accommodationTypeId;
            $query->whereHas('accommodations', function (Builder $q) use ($typeId) {
                $q->where('accommodations.status', 'active')
                    ->where('accommodations.accommodation_type', $typeId);
            });
        }

        if ($filter->hasGuiding === true) {
            $query->whereHas('guidings');
        } elseif ($filter->hasGuiding === false) {
            $query->whereDoesntHave('guidings');
        }

        if ($filter->hasRentalBoat === true) {
            $query->whereHas('rentalBoats');
        } elseif ($filter->hasRentalBoat === false) {
            $query->whereDoesntHave('rentalBoats');
        }

        return $query;
    }

    public function applyToTripQuery(Builder $query, VacationListingFilter $filter): Builder
    {
        $query = $this->applySpeciesColumnFilter($query, 'target_species', $filter->speciesIds, $filter->speciesNames);
        $query = $this->applyTripGuestsFilter($query, $filter->numGuests);

        return $this->applyTripDurationFilter($query, $filter->tripDuration);
    }

    public function applyTripGuestsFilter(Builder $query, ?int $numGuests): Builder
    {
        if ($numGuests === null) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($numGuests) {
            $q->whereNull('group_size_max')
                ->orWhere('group_size_max', '>=', $numGuests);
        });
    }

    public function applyTripDurationFilter(Builder $query, ?string $tripDuration): Builder
    {
        return match ($tripDuration) {
            '1-3' => $query->whereBetween('duration_days', [1, 3]),
            '4-7' => $query->whereBetween('duration_days', [4, 7]),
            '8+' => $query->where('duration_days', '>=', 8),
            default => $query,
        };
    }

    /**
     * @param  list<int>  $speciesIds
     * @param  list<string>  $speciesNames
     */
    public function applySpeciesColumnFilter(
        Builder $query,
        string $column,
        array $speciesIds,
        array $speciesNames = [],
    ): Builder {
        if (! in_array($column, ['target_fish', 'target_species'], true)) {
            return $query;
        }

        $needles = $this->speciesMatchNeedles($speciesIds, $speciesNames);
        if ($needles === []) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($column, $needles) {
            foreach ($needles as $needle) {
                $q->orWhereRaw('LOWER(CAST('.$column.' AS CHAR)) LIKE ?', ['%'.$needle.'%']);
            }
        });
    }

    public function applyCampSort(Builder $query, VacationListingFilter $filter): Builder
    {
        return match ($filter->sortBy) {
            'newest' => $query->orderByDesc('created_at'),
            'price-asc' => $query->orderBy('id'),
            'price-desc' => $query->orderByDesc('id'),
            default => $query->orderByDesc('created_at'),
        };
    }

    public function applyTripSort(Builder $query, VacationListingFilter $filter): Builder
    {
        return match ($filter->sortBy) {
            'newest' => $query->orderByDesc('created_at'),
            'price-asc' => $query->orderBy('price_per_person'),
            'price-desc' => $query->orderByDesc('price_per_person'),
            default => $query->orderByDesc('created_at'),
        };
    }

    /**
     * Locale-deduped target fish options (unique Target IDs, label in current locale).
     *
     * @return array<int, array{id: int, name: string}>
     */
    public function speciesOptionsForCountry(?string $country, bool $includeGuidings = false): array
    {
        $rawBuckets = collect();

        $campSpecies = Camp::query()
            ->where('status', 'active')
            ->when($country, fn ($q) => $q->whereRaw('LOWER(country) = ?', [strtolower($country)]))
            ->pluck('target_fish');
        $rawBuckets = $rawBuckets->merge($campSpecies);

        $tripSpecies = Trip::query()
            ->where('status', 'active')
            ->when($country, fn ($q) => $q->whereRaw('LOWER(country) = ?', [strtolower($country)]))
            ->pluck('target_species');
        $rawBuckets = $rawBuckets->merge($tripSpecies);

        if ($includeGuidings) {
            $guidingSpecies = Guiding::query()
                ->publiclyVisible()
                ->when($country, function (Builder $q) use ($country) {
                    $lower = strtolower($country);
                    $q->where(function (Builder $inner) use ($lower) {
                        $inner->whereRaw('LOWER(country) = ?', [$lower])
                            ->orWhereRaw('LOWER(country_iso) = ?', [$lower]);
                    });
                })
                ->pluck('target_fish');
            $rawBuckets = $rawBuckets->merge($guidingSpecies);
        }

        $targetIds = $this->resolveTargetIdsFromRawBuckets($rawBuckets);
        if ($targetIds === []) {
            return [];
        }

        $locale = app()->getLocale();

        return Target::query()
            ->whereIn('id', $targetIds)
            ->orderByRaw('CASE WHEN ? = \'en\' THEN name_en ELSE name END', [$locale])
            ->get(['id', 'name', 'name_en'])
            ->map(fn (Target $target) => [
                'id' => (int) $target->id,
                'name' => (string) $target->name,
            ])
            ->values()
            ->all();
    }

    /**
     * @param  list<int>  $speciesIds
     * @param  list<string>  $speciesNames
     * @return list<string>
     */
    public function speciesMatchNeedles(array $speciesIds, array $speciesNames = []): array
    {
        $needles = collect($speciesNames)
            ->map(fn ($name) => mb_strtolower(trim((string) $name), 'UTF-8'))
            ->filter();

        foreach ($speciesIds as $speciesId) {
            $needles->push((string) (int) $speciesId);
        }

        if ($speciesIds !== []) {
            $targets = Target::query()
                ->whereIn('id', $speciesIds)
                ->get(['id', 'name', 'name_en']);

            foreach ($targets as $target) {
                $attrs = $target->getAttributes();
                if (! empty($attrs['name'])) {
                    $needles->push(mb_strtolower((string) $attrs['name'], 'UTF-8'));
                }
                if (! empty($attrs['name_en'])) {
                    $needles->push(mb_strtolower((string) $attrs['name_en'], 'UTF-8'));
                }
            }
        }

        return $needles->unique()->values()->all();
    }

    /**
     * @return array<int, array{id: int, name: string}>
     */
    public function accommodationTypeOptions(): array
    {
        return AccommodationType::query()
            ->active()
            ->ordered()
            ->get(['id', 'name', 'name_en'])
            ->map(fn (AccommodationType $type) => [
                'id' => (int) $type->id,
                'name' => (string) $type->name,
            ])
            ->values()
            ->all();
    }

    /**
     * Accept JSON arrays, Tagify objects, and camp CSV strings ("Hecht,Wels,Zander").
     *
     * @param  Collection<int, mixed>  $rawBuckets
     * @return list<int>
     */
    private function resolveTargetIdsFromRawBuckets(Collection $rawBuckets): array
    {
        $idSet = [];
        $nameSet = [];

        foreach ($rawBuckets as $json) {
            $items = $this->normalizeRawSpeciesItems($json);
            foreach ($items as $item) {
                if (is_array($item)) {
                    if (isset($item['id']) && is_numeric($item['id']) && (int) $item['id'] > 0) {
                        $idSet[(int) $item['id']] = true;
                    }
                    $name = $item['name'] ?? $item['value'] ?? null;
                    if ($name === null || $name === '') {
                        continue;
                    }
                    if (is_numeric($name) && (int) $name > 0) {
                        $idSet[(int) $name] = true;
                    } else {
                        $nameSet[mb_strtolower(trim((string) $name), 'UTF-8')] = true;
                    }
                    continue;
                }

                if (is_numeric($item) && (int) $item > 0) {
                    $idSet[(int) $item] = true;
                    continue;
                }

                if (is_string($item) && trim($item) !== '') {
                    $nameSet[mb_strtolower(trim($item), 'UTF-8')] = true;
                }
            }
        }

        if ($nameSet !== []) {
            $names = array_keys($nameSet);
            Target::query()
                ->where(function (Builder $q) use ($names) {
                    foreach ($names as $name) {
                        $q->orWhereRaw('LOWER(name) = ?', [$name])
                            ->orWhereRaw('LOWER(name_en) = ?', [$name]);
                    }
                })
                ->pluck('id')
                ->each(function ($id) use (&$idSet) {
                    $idSet[(int) $id] = true;
                });
        }

        return array_keys($idSet);
    }

    /**
     * @return list<mixed>
     */
    private function normalizeRawSpeciesItems(mixed $raw): array
    {
        if ($raw === null || $raw === '') {
            return [];
        }

        if (is_array($raw)) {
            if (count($raw) === 1 && is_string($raw[0]) && str_contains($raw[0], ',')) {
                return array_values(array_filter(array_map('trim', explode(',', $raw[0]))));
            }

            return $raw;
        }

        if (! is_string($raw)) {
            return [];
        }

        $trimmed = trim($raw);
        if ($trimmed === '') {
            return [];
        }

        $decoded = json_decode($trimmed, true);
        if (is_array($decoded)) {
            return $decoded;
        }
        if (is_string($decoded) && $decoded !== '') {
            $trimmed = trim($decoded);
        }

        if (str_contains($trimmed, ',')) {
            return array_values(array_filter(array_map('trim', explode(',', $trimmed))));
        }

        return [$trimmed];
    }
}
