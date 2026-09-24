<?php

namespace App\Services\Seo;

use App\Domain\Offers\DestinationOfferScope;
use App\Domain\Seo\FacetInsights;
use App\Domain\Vacation\CountrySlug;
use App\Domain\Vacation\VacationListingFilter;
use App\Models\CategoryEntity;
use App\Models\CategoryPage;
use App\Models\Method;
use App\Models\Target;
use App\Models\Water;
use App\Repositories\Vacation\CampListingRepository;
use App\Repositories\Vacation\TripListingRepository;
use App\Services\Homepage\HomepageCountrySelector;
use App\Services\Homepage\HomepageMixedOfferSelector;
use App\Services\Offers\OfferCatalogPageService;
use App\Services\Sitemap\CategoryPageSitemapSource;
use App\Services\Vacation\TargetFishNameResolver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Aggregates the listings a facet page shows into FacetInsights: counts, providers, price range,
 * top species/methods/waters, season and (for species/method facets) countries. Queries are the
 * same ones the page lists from, so every generated fact is true of what the visitor sees.
 * Results are cached per facet and locale; below MIN_LISTINGS nothing is generated.
 */
class FacetInsightsBuilder
{
    public const MIN_LISTINGS = 3;

    private const CACHE_MINUTES = 360;

    private const TOP_SPECIES = 6;

    private const TOP_METHODS = 5;

    private const TOP_WATERS = 4;

    private const TOP_COUNTRIES = 6;

    /** Season is only stated when at least this many listings carry season data. */
    private const MIN_SEASON_LISTINGS = 3;

    private const MONTHS = ['january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december'];

    /** Link targets per scope: [species route, country route]. */
    private const SCOPE_ROUTES = [
        'tours' => ['guidings.targets', 'guidings.destination'],
        'vacations' => ['vacations.targets', 'vacations.country'],
        'all' => ['targets.show', 'destination.country'],
    ];

    /** @var array<string, mixed> */
    private array $maps = [];

    public function __construct(
        private readonly OfferCatalogPageService $offerCatalog,
        private readonly HomepageMixedOfferSelector $mixedOffers,
        private readonly CampListingRepository $camps,
        private readonly TripListingRepository $trips,
        private readonly HomepageCountrySelector $countryLabels,
        private readonly CategoryPageSitemapSource $categoryPages,
        private readonly CatalogInventoryGate $gate,
        private readonly TargetFishNameResolver $speciesResolver,
    ) {}

    /** /guidings/{country}/{region?}/{city?} */
    public function forTourDestination(CategoryEntity $country, ?CategoryEntity $region = null, ?CategoryEntity $city = null): ?FacetInsights
    {
        $place = $city ?? $region ?? $country;

        return $this->cached(['tour-destination', $country->id, $region?->id, $city?->id], function () use ($country, $region, $city, $place) {
            $queries = $this->offerCatalog->listingQueries(
                ['type' => 'tour'] + DestinationOfferScope::mergeIntoRequest([], $country, $region, $city)
            );

            return $this->aggregate(FacetInsights::KIND_PLACE, $this->placeLabel($place, $country), 'tours', $queries, $this->placeType($region, $city), $country->countrycode);
        });
    }

    /** /destination/{country}/{region?}/{city?} — tours, camps and trips. */
    public function forDestination(CategoryEntity $country, ?CategoryEntity $region = null, ?CategoryEntity $city = null): ?FacetInsights
    {
        $place = $city ?? $region ?? $country;

        return $this->cached(['destination', $country->id, $region?->id, $city?->id], fn () => $this->aggregate(
            FacetInsights::KIND_PLACE,
            $this->placeLabel($place, $country),
            'all',
            $this->mixedOffers->destinationQueries($country, $region, $city),
            $this->placeType($region, $city),
            $country->countrycode,
        ));
    }

    /**
     * /vacations/{country} (pillar null) and /vacations/{camps|trips}/{country}.
     */
    public function forVacationCountry(string $countrySlug, ?string $pillar = null): ?FacetInsights
    {
        $slug = CountrySlug::canonicalize($countrySlug) ?? $countrySlug;

        return $this->cached(['vacation-country', $slug, $pillar], function () use ($slug, $pillar) {
            $entity = $this->countryEntities()->first(fn (CategoryEntity $c) => (CountrySlug::canonicalize($c->slug) ?? $c->slug) === $slug);
            $filter = VacationListingFilter::fromRequest(['country' => $slug, 'country_short' => $entity?->countrycode]);
            $queries = [
                'tour' => null,
                'trip' => $pillar === 'camps' ? null : $this->trips->queryForCountry($filter),
                'camp' => $pillar === 'trips' ? null : $this->camps->queryForCountry($filter),
            ];
            $label = $entity ? $this->countryLabels->labelFor($entity) : ucfirst(str_replace('-', ' ', $slug));

            return $this->aggregate(FacetInsights::KIND_PLACE, $label, 'vacations', $queries, 'country', $entity?->countrycode);
        });
    }

    /**
     * Species pages: scope tours (/guidings/targets), vacations (/vacations[/pillar]/targets) or
     * global (/targets). $vacation narrows to 'camp' or 'trip'.
     */
    public function forSpecies(CategoryPage $page, string $scope, ?string $vacation = null): ?FacetInsights
    {
        $speciesId = (int) $page->source_id;

        return $this->cached(['species', $speciesId, $scope, $vacation], function () use ($page, $speciesId, $scope, $vacation) {
            $input = ['species' => [$speciesId]] + match ($scope) {
                'tours' => ['type' => 'tour'],
                'vacations' => array_filter(['type' => 'vacation', 'vacation' => $vacation]),
                default => [],
            };
            $name = $this->nameFor('targets', $speciesId) ?? (string) $page->name;
            $routeScope = $scope === 'tours' ? 'tours' : ($scope === 'vacations' ? 'vacations' : 'all');

            return $this->aggregate(FacetInsights::KIND_SPECIES, $name, $routeScope, $this->offerCatalog->listingQueries($input));
        });
    }

    /** /guidings/methods/{slug} */
    public function forMethod(CategoryPage $page): ?FacetInsights
    {
        $methodId = (int) $page->source_id;

        return $this->cached(['method', $methodId], function () use ($page, $methodId) {
            $name = $this->maps()['methods']->get($methodId)?->name ?? (string) $page->name;

            return $this->aggregate(FacetInsights::KIND_METHOD, $name, 'tours', $this->offerCatalog->listingQueries(['type' => 'tour', 'methods' => [$methodId]]));
        });
    }

    /**
     * @param  array{tour: ?Builder, trip: ?Builder, camp: ?Builder}  $queries
     */
    private function aggregate(string $kind, string $subject, string $routeScope, array $queries, ?string $placeType = null, ?string $countryIso = null): ?FacetInsights
    {
        $tours = $queries['tour'] ? $queries['tour']->get() : collect();
        $trips = $queries['trip'] ? $queries['trip']->get() : collect();
        $camps = $queries['camp'] ? $queries['camp']->get() : collect();

        if ($tours->count() + $trips->count() + $camps->count() < self::MIN_LISTINGS) {
            return null;
        }

        $species = [];
        $methods = [];
        $waters = [];
        $months = array_fill(1, 12, 0);
        $countries = [];
        $seasonListings = 0;

        foreach ($tours as $tour) {
            $raw = $tour->getAttributes();
            $this->tallyIds($species, $this->decode($raw['target_fish'] ?? null), 'targets');
            $this->tallyIds($methods, $this->decode($raw['fishing_methods'] ?? null), 'methods');
            $this->tallyIds($waters, $this->decode($raw['water_types'] ?? null), 'waters');
            $tourMonths = $this->decode($raw['months'] ?? null);
            $seasonListings += $tourMonths !== [] ? 1 : 0;
            foreach ($tourMonths as $month) {
                $index = array_search(strtolower((string) $month), self::MONTHS, true);
                if ($index !== false) {
                    $months[$index + 1]++;
                }
            }
            $this->tallyCountry($countries, $raw['country'] ?? null, $raw['country_iso'] ?? null);
        }

        foreach ($trips as $trip) {
            $raw = $trip->getAttributes();
            $this->tallyNamedItems($species, $this->decode($raw['target_species'] ?? null), 'targets');
            $this->tallyNamedItems($methods, $this->decode($raw['fishing_methods'] ?? null), 'methods');
            $this->tallyNamedItems($waters, $this->decode($raw['water_types'] ?? null), 'waters');
            $tripMonths = $this->seasonRange($raw['best_season_from'] ?? null, $raw['best_season_to'] ?? null);
            $seasonListings += $tripMonths !== [] ? 1 : 0;
            foreach ($tripMonths as $month) {
                $months[$month]++;
            }
            $this->tallyCountry($countries, $raw['country'] ?? null);
        }

        foreach ($camps as $camp) {
            $raw = $camp->getAttributes();
            $this->tallyNamedItems($species, $this->speciesResolver->resolve($raw['target_fish'] ?? null, $this->maps()['targets']), 'targets');
            $this->tallyCountry($countries, $raw['country'] ?? null);
        }

        [$speciesRoute, $countryRoute] = self::SCOPE_ROUTES[$routeScope];

        return new FacetInsights(
            kind: $kind,
            subject: $subject,
            placeType: $placeType,
            countryIso: $countryIso ?: null,
            counts: ['tour' => $tours->count(), 'trip' => $trips->count(), 'camp' => $camps->count()],
            // Only tours carry their provider (the guide) in user_id; trip/camp rows hold whoever
            // entered them, so counting those would under-state vacation providers.
            providers: $tours->pluck('user_id')->filter()->unique()->count(),
            tourPrices: $this->priceRange($tours->map(fn ($tour) => $tour->getLowestPrice())),
            tripPrices: $this->priceRange($trips->map(fn ($trip) => $trip->getLowestPrice())),
            // On a species/method page, list what's offered alongside it — not the subject itself.
            species: $this->top($this->without($species, $kind === FacetInsights::KIND_SPECIES ? $subject : null), self::TOP_SPECIES, fn (?int $id) => $this->speciesUrl($id, $routeScope, $speciesRoute)),
            methods: $this->top($this->without($methods, $kind === FacetInsights::KIND_METHOD ? $subject : null), self::TOP_METHODS, fn (?int $id) => $this->methodUrl($id)),
            waters: array_map(fn ($row) => ['name' => $row['name'], 'count' => $row['count']], $this->top($waters, self::TOP_WATERS)),
            seasonMonths: $seasonListings >= self::MIN_SEASON_LISTINGS ? $this->seasonMonths($months) : [],
            countries: $kind === FacetInsights::KIND_PLACE ? [] : $this->topCountries($countries, $routeScope, $countryRoute),
        );
    }

    /**
     * @param  array<string, array{id: ?int, name: string, count: int}>  $tally
     */
    private function tallyIds(array &$tally, array $ids, string $map): void
    {
        foreach (array_unique(array_filter($ids, 'is_numeric')) as $id) {
            $name = $this->nameFor($map, (int) $id);
            if (filled($name)) {
                $this->bump($tally, (int) $id, $name);
            }
        }
    }

    /**
     * Items stored as {id, name} objects or plain names (trips, camps). Ids are re-localized.
     *
     * @param  array<string, array{id: ?int, name: string, count: int}>  $tally
     */
    private function tallyNamedItems(array &$tally, array $items, string $map): void
    {
        $seen = [];
        foreach ($items as $item) {
            $id = is_array($item) && is_numeric($item['id'] ?? null) ? (int) $item['id'] : (is_numeric($item) ? (int) $item : null);
            $name = $id !== null ? $this->nameFor($map, $id) : null;
            $name ??= is_array($item) ? ($item['name'] ?? null) : (is_string($item) && ! is_numeric($item) ? $item : null);
            if (! filled($name)) {
                continue;
            }
            $key = $id ?? mb_strtolower(trim((string) $name));
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $this->bump($tally, $id, trim((string) $name));
        }
    }

    /**
     * Tallied by name so the same species stored as an id (tours) and as a plain name (camps)
     * counts once; the id is kept when any listing supplied one (it drives the link).
     */
    private function bump(array &$tally, ?int $id, string $name): void
    {
        $key = mb_strtolower($name);
        $tally[$key] ??= ['id' => $id, 'name' => $name, 'count' => 0];
        $tally[$key]['id'] ??= $id;
        $tally[$key]['count']++;
    }

    /**
     * Localized name for a species/method/water id. Species fall back to their category page's
     * name when the Target row is missing.
     */
    private function nameFor(string $map, int $id): ?string
    {
        $name = $this->maps()[$map]->get($id)?->name;
        if (! filled($name) && $map === 'targets') {
            $name = $this->maps()['speciesPages']->get($id)?->name;
        }

        return filled($name) ? (string) $name : null;
    }

    private function tallyCountry(array &$tally, mixed $country, mixed $iso = null): void
    {
        if (! is_string($country) || trim($country) === '') {
            return;
        }
        $entity = $this->countryByVariant()[mb_strtolower(trim($country))] ?? null;
        if ($entity === null && is_string($iso) && $iso !== '') {
            $entity = $this->countryEntities()->first(fn (CategoryEntity $c) => strtoupper((string) $c->countrycode) === strtoupper($iso));
        }
        if ($entity === null) {
            return;
        }
        $tally[$entity->id] ??= ['entity' => $entity, 'count' => 0];
        $tally[$entity->id]['count']++;
    }

    private function without(array $tally, ?string $name): array
    {
        return $name === null ? $tally : array_diff_key($tally, [mb_strtolower($name) => true]);
    }

    /**
     * @return list<array{name: string, count: int, url: ?string}>
     */
    private function top(array $tally, int $limit, ?callable $url = null): array
    {
        uasort($tally, fn ($a, $b) => [$b['count'], $a['name']] <=> [$a['count'], $b['name']]);

        return array_values(array_map(fn ($row) => [
            'name' => $row['name'],
            'count' => $row['count'],
            'url' => $url ? $url($row['id']) : null,
        ], array_slice($tally, 0, $limit)));
    }

    /**
     * @return list<array{name: string, count: int, url: ?string}>
     */
    private function topCountries(array $tally, string $routeScope, string $countryRoute): array
    {
        uasort($tally, fn ($a, $b) => $b['count'] <=> $a['count']);

        return array_values(array_map(function ($row) use ($routeScope, $countryRoute) {
            /** @var CategoryEntity $entity */
            $entity = $row['entity'];
            $slug = CountrySlug::canonicalize($entity->slug) ?? $entity->slug;
            $live = match ($routeScope) {
                'tours' => $this->gate->guidingDestinationIndexable($entity),
                'vacations' => $this->gate->vacationCountryIndexable($slug),
                default => $this->gate->destinationIndexable($entity),
            };

            return [
                'name' => $this->countryLabels->labelFor($entity),
                'count' => $row['count'],
                'url' => $live ? route($countryRoute, ['country' => $slug]) : null,
            ];
        }, array_slice($tally, 0, self::TOP_COUNTRIES, true)));
    }

    private function speciesUrl(?int $targetId, string $routeScope, string $route): ?string
    {
        $page = $targetId ? $this->maps()['speciesPages']->get($targetId) : null;
        if ($page === null) {
            return null;
        }
        $lang = app()->getLocale();
        $live = match ($routeScope) {
            'tours' => $this->categoryPages->tourTargetIsLive($page, $lang),
            'vacations' => $this->categoryPages->vacationTargetIsLive($page, $lang),
            default => $this->categoryPages->globalTargetIsLive($page, $lang),
        };

        return $live ? route($route, ['slug' => $page->slug]) : null;
    }

    private function methodUrl(?int $methodId): ?string
    {
        $page = $methodId ? $this->maps()['methodPages']->get($methodId) : null;

        return $page && $this->categoryPages->methodIsLive($page, app()->getLocale())
            ? route('guidings.methods.show', ['slug' => $page->slug])
            : null;
    }

    /**
     * Middle half of the listings' "from" prices — robust against a single mis-entered price.
     *
     * @param  Collection<int, int|float>  $prices
     * @return ?array{low: int, median: int, high: int}
     */
    private function priceRange(Collection $prices): ?array
    {
        $sorted = $prices->map(fn ($p) => (int) round((float) $p))->filter(fn ($p) => $p > 0)->sort()->values();
        if ($sorted->isEmpty()) {
            return null;
        }
        $at = fn (float $q) => $sorted[(int) floor(($sorted->count() - 1) * $q)];

        return ['low' => $at(0.25), 'median' => $at(0.5), 'high' => $at(0.75)];
    }

    /**
     * Months within 80% of the busiest month; all twelve means year-round.
     *
     * @param  array<int, int>  $months
     * @return list<int>
     */
    private function seasonMonths(array $months): array
    {
        $max = max($months);
        if ($max === 0) {
            return [];
        }

        return array_values(array_keys(array_filter($months, fn ($count) => $count >= 0.8 * $max)));
    }

    /**
     * @return list<int>
     */
    private function seasonRange(mixed $from, mixed $to): array
    {
        // A start month alone isn't a season — only complete from/to ranges count.
        $from = is_numeric($from) ? (int) $from : null;
        $to = is_numeric($to) ? (int) $to : null;
        if ($from === null || $to === null || $from < 1 || $from > 12 || $to < 1 || $to > 12) {
            return [];
        }
        $range = [];
        for ($m = $from, $i = 0; $i < 12; $i++, $m = $m % 12 + 1) {
            $range[] = $m;
            if ($m === $to) {
                break;
            }
        }

        return $range;
    }

    private function decode(mixed $raw): array
    {
        if (is_array($raw)) {
            return $raw;
        }
        $decoded = json_decode((string) $raw, true);
        if (is_string($decoded)) {
            $decoded = json_decode($decoded, true);
        }

        return is_array($decoded) ? $decoded : [];
    }

    private function placeLabel(CategoryEntity $place, CategoryEntity $country): string
    {
        return $place->is($country) ? $this->countryLabels->labelFor($country) : (string) $place->name;
    }

    private function placeType(?CategoryEntity $region, ?CategoryEntity $city): string
    {
        return $city ? 'city' : ($region ? 'region' : 'country');
    }

    /**
     * @return array{targets: Collection, methods: Collection, waters: Collection, speciesPages: Collection, methodPages: Collection}
     */
    private function maps(): array
    {
        $locale = app()->getLocale();

        return $this->maps[$locale] ??= [
            'targets' => Target::all()->keyBy('id'),
            'methods' => Method::all()->keyBy('id'),
            'waters' => Water::all()->keyBy('id'),
            'speciesPages' => $this->categoryPages->targetPages()->keyBy(fn (CategoryPage $page) => (int) $page->source_id),
            'methodPages' => $this->categoryPages->methodPages()->keyBy(fn (CategoryPage $page) => (int) $page->source_id),
        ];
    }

    /**
     * @return Collection<int, CategoryEntity>
     */
    private function countryEntities(): Collection
    {
        return $this->maps['countries'] ??= CategoryEntity::countries()->get();
    }

    /**
     * @return array<string, CategoryEntity>
     */
    private function countryByVariant(): array
    {
        if (isset($this->maps['countryVariants'])) {
            return $this->maps['countryVariants'];
        }
        $map = [];
        foreach ($this->countryEntities() as $country) {
            foreach (CountrySlug::storageVariants($country->slug, $country->countrycode) as $variant) {
                $map[mb_strtolower(trim($variant))] ??= $country;
            }
        }

        return $this->maps['countryVariants'] = $map;
    }

    private function cached(array $key, callable $build): ?FacetInsights
    {
        $cacheKey = 'facet_insights_v1_'.app()->getLocale().'_'.implode('_', array_map(fn ($part) => $part ?? '-', $key));
        $data = Cache::remember($cacheKey, now()->addMinutes(self::CACHE_MINUTES), fn () => $build()?->toArray() ?? false);

        return $data === false ? null : FacetInsights::fromArray($data);
    }
}
