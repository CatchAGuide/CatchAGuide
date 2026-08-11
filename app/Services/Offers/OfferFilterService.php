<?php

namespace App\Services\Offers;

use App\Domain\Vacation\CountrySlug;
use App\Models\Target;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class OfferFilterService
{
    private ?array $filterData = null;

    private string $cacheKey = 'offer_filter_data';

    private int $cacheTimeout = 3600;

    public function refresh(): array
    {
        $builder = app(OfferFilterMapBuilder::class);
        $map = $builder->build();

        $this->clearCache();
        Cache::put($this->cacheKey.':'.$builder->fingerprint(), $map, $this->cacheTimeout);
        $this->filterData = $map;

        return $map;
    }

    public function clearCache(): void
    {
        $this->filterData = null;
        $fingerprint = app(OfferFilterMapBuilder::class)->fingerprint();
        Cache::forget($this->cacheKey.':'.$fingerprint);
    }

    /**
     * Locale-labeled species options, optionally limited to a country.
     *
     * @return Collection<int, array{id: int, name: string}>
     */
    public function speciesOptions(?string $country = null, ?string $countryShort = null): Collection
    {
        $this->ensureDataLoaded();
        $targetIds = $country
            ? $this->targetIdsForCountry($country, $countryShort)
            : $this->usedTargetIds();

        if ($targetIds === []) {
            return collect();
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
            ->values();
    }

    /**
     * Fast listing IDs for a pillar that match any selected species.
     *
     * @param  'tours'|'trips'|'camps'  $pillar
     * @param  list<int>  $speciesIds
     * @return list<int>|null  null when map unavailable / empty selection
     */
    public function listingIdsForSpecies(string $pillar, array $speciesIds): ?array
    {
        if ($speciesIds === []) {
            return null;
        }

        $this->ensureDataLoaded();
        if ($this->filterData === null) {
            return null;
        }

        $targets = $this->filterData[$pillar]['targets'] ?? [];
        $ids = [];

        foreach ($speciesIds as $speciesId) {
            $key = $speciesId;
            if (! isset($targets[$key])) {
                $key = (string) $speciesId;
            }
            if (! isset($targets[$key])) {
                continue;
            }
            $ids = array_merge($ids, $targets[$key]);
        }

        return array_values(array_unique(array_map('intval', $ids)));
    }

    /**
     * @return list<int>
     */
    public function targetIdsForCountry(string $country, ?string $countryShort = null): array
    {
        $this->ensureDataLoaded();
        $byCountry = $this->filterData['targets_by_country'] ?? [];
        if ($byCountry === []) {
            return $this->usedTargetIds();
        }

        $ids = [];
        foreach (CountrySlug::storageVariants($country, $countryShort) as $variant) {
            $key = mb_strtolower(trim($variant), 'UTF-8');
            foreach ($byCountry[$key] ?? [] as $targetId) {
                $ids[(int) $targetId] = (int) $targetId;
            }
        }

        return array_values($ids);
    }

    /**
     * @return list<int>
     */
    private function usedTargetIds(): array
    {
        $ids = [];
        foreach (['tours', 'trips', 'camps'] as $pillar) {
            foreach (array_keys($this->filterData[$pillar]['targets'] ?? []) as $targetId) {
                $ids[(int) $targetId] = (int) $targetId;
            }
        }

        return array_values($ids);
    }

    private function ensureDataLoaded(): void
    {
        if ($this->filterData !== null) {
            return;
        }

        $builder = app(OfferFilterMapBuilder::class);
        $this->filterData = Cache::remember(
            $this->cacheKey.':'.$builder->fingerprint(),
            $this->cacheTimeout,
            function () use ($builder) {
                $map = $builder->build();
                Storage::disk('local')->put(
                    'cache/offer-filters.json',
                    json_encode($map, JSON_PRETTY_PRINT)
                );

                return $map;
            }
        ) ?: $this->emptyStructure();
    }

    private function emptyStructure(): array
    {
        return [
            'tours' => ['targets' => []],
            'trips' => ['targets' => []],
            'camps' => ['targets' => []],
            'targets_by_country' => [],
            'metadata' => [
                'generated_at' => null,
                'total_tours' => 0,
                'total_trips' => 0,
                'total_camps' => 0,
                'counts' => [
                    'tours' => [],
                    'trips' => [],
                    'camps' => [],
                    'countries' => 0,
                ],
            ],
        ];
    }
}
