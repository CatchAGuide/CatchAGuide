<?php

namespace App\Services\Guidings;

use App\Domain\CategoryPage\CategoryPageScope;
use App\Models\CategoryPage;
use App\Models\Guiding;
use App\Models\Method;
use App\Presenters\Guiding\GuidingCardPresenter;
use App\Repositories\Guiding\GuidingCategoryAvailabilityRepository;
use App\Services\CategoryPage\CategoryListingThumbnailFallback;
use App\Services\CategoryPage\FavoriteTargetSpeciesResolver;
use App\Services\Homepage\HomepageCountrySelector;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class GuidingsLandingService
{
    /**
     * Methods/target-species rails render an infinite-scroll clone of their own
     * items (see <x-vacation.country-slider>) — too few distinct tiles makes the
     * clone visible without scrolling on wide desktop viewports, so pad up to
     * enough favourites-first items to fill even large screens before looping.
     */
    private const TILE_RAIL_LIMIT = 12;

    /**
     * How far back to count bookings for the "frequently booked" rail, so it
     * reflects recent demand rather than a tour's all-time booking total.
     */
    public const MOST_BOOKED_WINDOW_DAYS = 50;

    public function __construct(
        private HomepageCountrySelector $countries,
        private FavoriteTargetSpeciesResolver $favoriteTargetSpecies,
        private GuidingCardPresenter $cardPresenter,
        private GuidingCategoryAvailabilityRepository $guidingAvailability,
        private CategoryListingThumbnailFallback $thumbnails,
    ) {}

    /**
     * @return array{
     *     featuredCountries: Collection,
     *     countryCount: int,
     *     tourCount: int,
     *     pills: Collection,
     *     mostBooked: Collection,
     *     newTours: Collection,
     *     methods: Collection,
     *     targetSpecies: Collection
     * }
     */
    public function build(): array
    {
        $locale = app()->getLocale();

        return [
            'featuredCountries' => $this->featuredCountries(),
            'countryCount' => $this->countries->totalCount(),
            'tourCount' => $this->tourCount(),
            'pills' => $this->pills($locale),
            'mostBooked' => $this->mostBooked($locale),
            'newTours' => $this->newTours($locale),
            'methods' => $this->methodTiles($locale),
            'targetSpecies' => $this->speciesTiles($locale),
        ];
    }

    /**
     * Country carousel: category-page copy is not enough — a country with zero
     * publicly visible tours has no destination page to link to (it 404s).
     */
    private function featuredCountries(): Collection
    {
        return $this->countries->featured(null, CategoryPageScope::TOURS)
            ->filter(fn (array $country) => $this->guidingAvailability->hasGuidingsForCountry($country['slug'], $country['countrycode']))
            ->values();
    }

    private function tourCount(): int
    {
        return Cache::remember('guidings_landing_tour_count_v1', now()->addHour(), function () {
            return Guiding::query()->publiclyVisible()->count();
        });
    }

    private function mostBooked(string $locale): Collection
    {
        return Cache::remember("guidings_landing_most_booked_v2_{$locale}", now()->addMinutes(30), function () {
            return Guiding::withCount(['bookings' => function ($query) {
                    $query->where('created_at', '>=', now()->subDays(self::MOST_BOOKED_WINDOW_DAYS));
                }])
                ->publiclyVisible()
                ->orderByDesc('bookings_count')
                ->limit(8)
                ->get()
                ->map(fn (Guiding $g) => $this->cardPresenter->present($g));
        });
    }

    private function newTours(string $locale): Collection
    {
        return Cache::remember("guidings_landing_new_tours_v1_{$locale}", now()->addMinutes(15), function () {
            return Guiding::query()
                ->publiclyVisible()
                ->orderByDesc('created_at')
                ->limit(8)
                ->get()
                ->map(fn (Guiding $g) => $this->cardPresenter->present($g));
        });
    }

    /**
     * The mockup's 5 "type of tour" pills, each backed by an existing tour-classification query.
     */
    private function pills(string $locale): Collection
    {
        $definitions = [
            'action' => fn () => Guiding::whereHas('fishingTypes', fn ($q) => $q->where('id', 1)),
            'sea' => fn () => Guiding::whereHas('guidingWaters', fn ($q) => $q->where('water_id', 2)),
            'family' => fn () => Guiding::where('max_guests', '>=', 4),
            'relaxed' => fn () => Guiding::where('duration', '>=', 10),
            'fly' => fn () => Guiding::whereHas('guidingMethods', fn ($q) => $q->where('method_id', 4)),
        ];

        $labels = [
            'action' => __('homepage.landing_pill_action'),
            'sea' => __('homepage.landing_pill_sea'),
            'family' => __('homepage.landing_pill_family'),
            'relaxed' => __('homepage.landing_pill_relaxed'),
            'fly' => __('homepage.landing_pill_fly'),
        ];

        // Stampede-protected: 5 separate inRandomOrder() queries make this the
        // priciest block to rebuild, so a bot burst hitting a cold cache must not
        // trigger it concurrently on every request. flexible() serves the last
        // known value while a single locked request refreshes it in the background.
        return Cache::flexible("guidings_landing_pills_v1_{$locale}", [now()->addMinutes(30), now()->addHour()], function () use ($definitions, $labels) {
            return collect($definitions)->map(function ($query, $key) use ($labels) {
                $cards = $query()
                    ->publiclyVisible()
                    ->inRandomOrder()
                    ->limit(6)
                    ->get()
                    ->map(fn (Guiding $g) => $this->cardPresenter->present($g));

                return [
                    'key' => $key,
                    'label' => $labels[$key],
                    'cards' => $cards,
                ];
            })->values();
        });
    }

    /**
     * @return Collection<int, array{name: string, slug: string, thumbnail: ?string, count: int, url: string}>
     */
    private function methodTiles(string $locale): Collection
    {
        return Cache::remember("guidings_landing_methods_v4_{$locale}", now()->addMinutes(30), function () {
            $pages = $this->favoriteCategoryPages(
                'Methods',
                $this->guidingAvailability->methodIdsWithGuidings(),
            );

            $methods = Method::query()
                ->whereIn('id', $pages->pluck('source_id')->filter()->unique())
                ->get()
                ->keyBy('id');

            return $pages->map(function (CategoryPage $page) use ($methods) {
                $method = $methods->get($page->source_id) ?? $methods->get((int) $page->source_id);

                return [
                    'name' => $method?->name ?? $page->name,
                    'slug' => $page->slug,
                    'thumbnail' => $this->thumbnails->url(
                        $page->thumbnail_path,
                        CategoryListingThumbnailFallback::KIND_METHOD,
                        (int) $page->source_id,
                    ),
                    'count' => 1,
                    'url' => route('guidings.methods.show', ['slug' => $page->slug]),
                ];
            })->values();
        });
    }

    /**
     * @return Collection<int, array{name: string, slug: string, thumbnail: ?string, count: int, url: string}>
     */
    private function speciesTiles(string $locale): Collection
    {
        return Cache::remember("guidings_landing_species_v4_{$locale}", now()->addMinutes(30), function () {
            return $this->favoriteTargetSpecies
                ->resolve(self::TILE_RAIL_LIMIT, $this->guidingAvailability->targetIdsWithGuidings())
                ->map(function (array $card) {
                    return [
                        'name' => $card['name'],
                        'slug' => $card['slug'],
                        'thumbnail' => $card['thumbnail'],
                        'count' => 1,
                        'url' => route('guidings.targets', ['slug' => $card['slug']]),
                    ];
                })
                ->values();
        });
    }

    /**
     * Favorite-first category pages whose source already has publicly visible tours.
     *
     * @param  list<int>  $availableSourceIds
     * @return Collection<int, CategoryPage>
     */
    private function favoriteCategoryPages(string $type, array $availableSourceIds): Collection
    {
        if ($availableSourceIds === []) {
            return collect();
        }

        $sourceIds = array_map('strval', $availableSourceIds);

        $favorites = CategoryPage::query()
            ->where('type', $type)
            ->where('is_favorite', 1)
            ->whereIn('source_id', $sourceIds)
            ->orderBy('name')
            ->limit(self::TILE_RAIL_LIMIT)
            ->get();

        if ($favorites->count() >= self::TILE_RAIL_LIMIT) {
            return $favorites;
        }

        $extra = CategoryPage::query()
            ->where('type', $type)
            ->whereIn('source_id', $sourceIds)
            ->whereNotIn('id', $favorites->pluck('id'))
            ->orderBy('name')
            ->limit(self::TILE_RAIL_LIMIT - $favorites->count())
            ->get();

        return $favorites->concat($extra);
    }
}
