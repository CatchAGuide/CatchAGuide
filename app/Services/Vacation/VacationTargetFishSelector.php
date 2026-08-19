<?php

namespace App\Services\Vacation;

use App\Domain\Vacation\BookableListingPolicy;
use App\Models\Camp;
use App\Models\Trip;
use App\Services\CategoryPage\FavoriteTargetSpeciesResolver;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class VacationTargetFishSelector
{
    public function __construct(
        private FavoriteTargetSpeciesResolver $favoriteTargetSpecies,
        private VacationFilterApplicator $filterApplicator,
        private BookableListingPolicy $policy,
    ) {}

    /**
     * Favorite target-fish tiles with real active camp+trip counts, vacation-scoped links.
     * Species with zero active listings are dropped.
     *
     * @return Collection<int, array{name: string, slug: string, thumbnail: ?string, count: int, url: string}>
     */
    public function forHub(int $limit): Collection
    {
        return Cache::remember(
            "vacation_hub_target_fish_v1_{$limit}_".app()->getLocale(),
            now()->addMinutes(30),
            function () use ($limit) {
                return $this->favoriteTargetSpecies->resolve($limit)
                    ->map(fn (array $card) => [
                        'name' => $card['name'],
                        'slug' => $card['slug'],
                        'thumbnail' => $card['thumbnail'],
                        'count' => $this->countActiveListings($card['source_id'], $card['name']),
                        'url' => route('vacations.targets', ['slug' => $card['slug']]),
                    ])
                    ->filter(fn (array $card) => $card['count'] > 0)
                    ->values();
            }
        );
    }

    private function countActiveListings(int $targetId, string $targetName): int
    {
        $speciesIds = $targetId > 0 ? [$targetId] : [];
        $speciesNames = $targetId > 0 ? [] : [$targetName];

        $campQuery = Camp::query()->where('status', $this->policy->activeStatus());
        $campQuery = $this->filterApplicator->applySpeciesColumnFilter($campQuery, 'target_fish', $speciesIds, $speciesNames);

        $tripQuery = Trip::query()->where('status', $this->policy->activeStatus());
        $tripQuery = $this->filterApplicator->applySpeciesColumnFilter($tripQuery, 'target_species', $speciesIds, $speciesNames);

        return $campQuery->count() + $tripQuery->count();
    }
}
