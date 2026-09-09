<?php

namespace App\Services\Vacation;

use App\Domain\CategoryPage\CategoryPageEntityType;
use App\Domain\CategoryPage\CategoryPageScope;
use App\Domain\Vacation\BookableListingPolicy;
use App\Models\Camp;
use App\Models\Trip;
use App\Services\CategoryPage\CategoryPageContentService;
use App\Services\CategoryPage\FavoriteTargetSpeciesResolver;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class VacationTargetFishSelector
{
    public function __construct(
        private FavoriteTargetSpeciesResolver $favoriteTargetSpecies,
        private VacationFilterApplicator $filterApplicator,
        private BookableListingPolicy $policy,
        private CategoryPageContentService $categoryContent,
    ) {}

    /**
     * Favorite target-fish tiles with real active camp+trip counts, vacation-scoped links.
     * Species with zero active listings, or with no vacations-scoped page content
     * (which would make the link 404), are dropped.
     *
     * @return Collection<int, array{name: string, slug: string, thumbnail: ?string, count: int, url: string}>
     */
    public function forHub(int $limit): Collection
    {
        return Cache::remember(
            "vacation_hub_target_fish_v2_{$limit}_".app()->getLocale(),
            now()->addMinutes(30),
            function () use ($limit) {
                return $this->favoriteTargetSpecies->resolve($limit)
                    ->map(fn (array $card) => [
                        'name' => $card['name'],
                        'slug' => $card['slug'],
                        'thumbnail' => $card['thumbnail'],
                        'source_id' => $card['source_id'],
                        'count' => $this->countActiveListings($card['source_id'], $card['name']),
                        'url' => route('vacations.targets', ['slug' => $card['slug']]),
                    ])
                    ->filter(fn (array $card) => $card['count'] > 0 && $this->hasVacationsContent($card['source_id']))
                    ->map(fn (array $card) => array_diff_key($card, ['source_id' => true]))
                    ->values();
            }
        );
    }

    /**
     * Whether this species has vacations-scoped page content (so the link
     * would not 404) and at least one active camp or trip. Used to gate the
     * vacations-pillar target-fish index the same way the hub rail is gated.
     */
    public function hasActiveVacationListings(int $sourceId, string $name): bool
    {
        return $sourceId > 0
            && $this->hasVacationsContent($sourceId)
            && $this->countActiveListings($sourceId, $name) > 0;
    }

    /**
     * Whether this species has at least one active camp or trip, regardless of
     * whether it has vacations-scoped page content. Unlike hasActiveVacationListings(),
     * this skips the vacations-link-safety check — for pages (e.g. the global
     * targets index) that link elsewhere and just need to know real listings exist.
     */
    public function hasActiveListings(int $sourceId, string $name): bool
    {
        return $sourceId > 0 && $this->countActiveListings($sourceId, $name) > 0;
    }

    private function hasVacationsContent(int $sourceId): bool
    {
        if ($sourceId <= 0) {
            return false;
        }

        return $this->categoryContent->resolveEntityForDisplay(
            CategoryPageEntityType::TARGET_FISH,
            $sourceId,
            CategoryPageScope::VACATIONS,
            app()->getLocale(),
            null,
            false,
        ) !== null;
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
