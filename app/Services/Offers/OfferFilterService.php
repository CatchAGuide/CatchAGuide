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

    private string $cacheKey = 'offer_filter_data_v2';

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
     * Locale-labeled species options (catalog targets + unmatched custom names),
     * optionally limited to a country.
     *
     * @return Collection<int, array{id: int|string, name: string}>
     */
    public function speciesOptions(?string $country = null, ?string $countryShort = null): Collection
    {
        $this->ensureDataLoaded();
        $targetIds = $country
            ? $this->targetIdsForCountry($country, $countryShort)
            : $this->usedTargetIds();
        $customKeys = $country
            ? $this->customKeysForCountry($country, $countryShort)
            : $this->usedCustomKeys();

        $options = collect();

        if ($targetIds !== []) {
            $options = $options->merge(
                Target::query()
                    ->whereIn('id', $targetIds)
                    ->get(['id', 'name', 'name_en'])
                    ->map(fn (Target $target) => [
                        'id' => (int) $target->id,
                        'name' => (string) $target->name,
                        'sort' => mb_strtolower((string) $target->name, 'UTF-8'),
                    ])
            );
        }

        $labels = $this->filterData['custom_target_labels'] ?? [];
        foreach ($customKeys as $key) {
            $label = (string) ($labels[$key] ?? $key);
            if ($label === '') {
                continue;
            }
            $options->push([
                'id' => $label,
                'name' => $label,
                'sort' => mb_strtolower($label, 'UTF-8'),
            ]);
        }

        return $options
            ->unique(fn (array $row) => is_int($row['id']) ? 'id:'.$row['id'] : 'name:'.$row['sort'])
            ->sortBy('sort', SORT_NATURAL | SORT_FLAG_CASE)
            ->map(fn (array $row) => [
                'id' => $row['id'],
                'name' => $row['name'],
            ])
            ->values();
    }

    /**
     * Fast listing IDs for a pillar that match any selected catalog species ids.
     *
     * @param  'tours'|'trips'|'camps'  $pillar
     * @param  list<int>  $speciesIds
     * @return list<int>|null  null when selection empty
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
     * Fast listing IDs for unmatched custom species names stored on listings.
     *
     * @param  'tours'|'trips'|'camps'  $pillar
     * @param  list<string>  $speciesNames
     * @return list<int>|null  null when selection empty
     */
    public function listingIdsForCustomSpecies(string $pillar, array $speciesNames): ?array
    {
        $keys = [];
        foreach ($speciesNames as $name) {
            $key = mb_strtolower(trim((string) $name), 'UTF-8');
            if ($key !== '') {
                $keys[$key] = $key;
            }
        }

        if ($keys === []) {
            return null;
        }

        $this->ensureDataLoaded();
        if ($this->filterData === null) {
            return null;
        }

        $customs = $this->filterData[$pillar]['custom_targets'] ?? [];
        $ids = [];

        foreach ($keys as $key) {
            if (! isset($customs[$key])) {
                continue;
            }
            $ids = array_merge($ids, $customs[$key]);
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
     * @return list<string>
     */
    public function customKeysForCountry(string $country, ?string $countryShort = null): array
    {
        $this->ensureDataLoaded();
        $byCountry = $this->filterData['custom_targets_by_country'] ?? [];
        if ($byCountry === []) {
            return $this->usedCustomKeys();
        }

        $keys = [];
        foreach (CountrySlug::storageVariants($country, $countryShort) as $variant) {
            $countryKey = mb_strtolower(trim($variant), 'UTF-8');
            foreach ($byCountry[$countryKey] ?? [] as $customKey) {
                $keys[(string) $customKey] = (string) $customKey;
            }
        }

        return array_values($keys);
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

    /**
     * @return list<string>
     */
    private function usedCustomKeys(): array
    {
        $keys = [];
        foreach (['tours', 'trips', 'camps'] as $pillar) {
            foreach (array_keys($this->filterData[$pillar]['custom_targets'] ?? []) as $key) {
                $keys[(string) $key] = (string) $key;
            }
        }

        return array_values($keys);
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
            'tours' => ['targets' => [], 'custom_targets' => []],
            'trips' => ['targets' => [], 'custom_targets' => []],
            'camps' => ['targets' => [], 'custom_targets' => []],
            'targets_by_country' => [],
            'custom_targets_by_country' => [],
            'custom_target_labels' => [],
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
