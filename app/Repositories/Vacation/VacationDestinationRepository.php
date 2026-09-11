<?php

namespace App\Repositories\Vacation;

use App\Domain\CategoryPage\CategoryPageEntityType;
use App\Domain\CategoryPage\CategoryPageScope;
use App\Domain\Vacation\CountrySlug;
use App\Models\CategoryEntity;
use App\Services\CategoryPage\CategoryListingThumbnailFallback;
use App\Services\CategoryPage\CategoryPageContentService;
use App\Services\Homepage\HomepageCountrySelector;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class VacationDestinationRepository
{
    /** Per-request memo so repeated calls (middleware + controller + nav) don't even hit the cache store. */
    private static array $hubGridMemo = [];

    public function __construct(
        private CampListingRepository $camps,
        private TripListingRepository $trips,
        private HomepageCountrySelector $homepageCountries,
        private CategoryPageContentService $categoryContent,
        private CategoryListingThumbnailFallback $thumbnails,
    ) {}

    public function findCountryForLocale(string $slug, ?string $locale = null): ?CategoryEntity
    {
        $slug = CountrySlug::canonicalize($slug) ?? strtolower($slug);

        return CategoryEntity::countries()
            ->whereRaw('LOWER(slug) = ?', [$slug])
            ->first();
    }

    public function mergeCountryContent(string $slug, ?string $locale = null): ?CategoryEntity
    {
        return $this->findCountryForLocale($slug, $locale);
    }

    /**
     * @return array{destination: CategoryEntity, slug: string, name: string, sub_title: ?string, camps: int, trips: int, thumbnail_path: ?string, countrycode: ?string}|null
     */
    public function hubGridCountry(string $slug, ?string $locale = null): ?array
    {
        $slug = CountrySlug::canonicalize($slug) ?? strtolower($slug);

        return $this->countriesForHubGrid($locale)
            ->first(fn (array $row) => CountrySlug::canonicalize($row['slug']) === $slug);
    }

    public function isKnownCountrySlug(string $slug, ?string $pillar = null): bool
    {
        $slug = CountrySlug::canonicalize($slug) ?? strtolower($slug);

        if ($this->mergeCountryContent($slug) !== null) {
            return true;
        }

        $row = $this->hubGridCountry($slug);
        if ($row === null) {
            return false;
        }

        if ($pillar === 'trips') {
            return ($row['trips'] ?? 0) > 0;
        }

        if ($pillar === 'camps') {
            return ($row['camps'] ?? 0) > 0;
        }

        return ($row['trips'] ?? 0) > 0 || ($row['camps'] ?? 0) > 0;
    }

    /**
     * @return array{destination: CategoryEntity, slug: string}|null
     */
    public function resolveCountryPage(string $slug, ?string $pillar = null, ?string $locale = null): ?array
    {
        $slug = CountrySlug::canonicalize($slug) ?? strtolower($slug);
        $locale = $locale ?? app()->getLocale();

        if ($pillar !== null && ! $this->isKnownCountrySlug($slug, $pillar)) {
            return null;
        }

        if ($pillar === null && ! $this->isKnownCountrySlug($slug)) {
            return null;
        }

        $country = $this->mergeCountryContent($slug, $locale);
        $hubRow = $this->hubGridCountry($slug, $locale);

        if ($country === null && $hubRow !== null) {
            $country = new CategoryEntity([
                'type' => 'country',
                'slug' => $hubRow['slug'],
                'name' => $hubRow['name'],
                'thumbnail_path' => $hubRow['thumbnail_path'],
                'countrycode' => $hubRow['countrycode'],
            ]);
        }

        if ($country === null) {
            return null;
        }

        return [
            'destination' => $country,
            'slug' => CountrySlug::canonicalize($country->slug) ?? $slug,
        ];
    }

    /**
     * Vacation WHERE dropdown: countries with filled vacations category-page copy.
     * Tour/global destination pages are excluded so the list is not the guidings catalog.
     *
     * @return Collection<int, object{slug: string, name: string}>
     */
    public function countriesForSearch(?string $locale = null): Collection
    {
        $locale = $locale ?? app()->getLocale();

        return $this->homepageCountries->uniqueModelsForScope(CategoryPageScope::VACATIONS, $locale)
            ->map(fn (CategoryEntity $country) => (object) [
                'slug' => CountrySlug::canonicalize($country->slug) ?? strtolower((string) $country->slug),
                'name' => $this->homepageCountries->labelFor($country, $locale),
            ])
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }

    /**
     * @return Collection<int, array{destination: ?CategoryEntity, slug: string, name: string, sub_title: ?string, camps: int, trips: int, thumbnail_path: ?string, countrycode: ?string}>
     */
    public function countriesForHubGrid(?string $locale = null): Collection
    {
        $locale = $locale ?? app()->getLocale();

        if (isset(self::$hubGridMemo[$locale])) {
            return self::$hubGridMemo[$locale];
        }

        // This aggregate (country counts + per-country translation lookups) costs 100+ queries.
        // It backs isKnownCountrySlug(), which the redirect middleware, the pillar controller, and
        // the nav both call per request, so it must be cached rather than rebuilt each time.
        return self::$hubGridMemo[$locale] = Cache::remember(
            'vacation_hub_grid_countries_v1_'.$locale,
            now()->addMinutes(30),
            function () use ($locale) {
                $campCounts = $this->canonicalCountryCounts('camps');
                $tripCounts = $this->canonicalCountryCounts('trips');

                $allCountries = CategoryEntity::countries()->get();
                $bySlug = $allCountries->keyBy(
                    fn (CategoryEntity $c) => CountrySlug::canonicalize($c->slug) ?? strtolower((string) $c->slug)
                );
                $uniqueCountries = $this->homepageCountries->uniqueModels($locale, $allCountries);

                $seenSlugs = [];
                $seenIsos = [];
                $rows = collect();

                foreach ($uniqueCountries as $country) {
                    $slug = CountrySlug::canonicalize($country->slug) ?? strtolower((string) $country->slug);
                    $iso = strtoupper((string) ($country->countrycode ?? ''));
                    $rows->push($this->hubGridRow($country, $slug, $campCounts, $tripCounts, $locale));
                    $seenSlugs[$slug] = true;
                    if ($iso !== '') {
                        $seenIsos[$iso] = true;
                    }
                }

                foreach ($campCounts->keys()->merge($tripCounts->keys())->unique() as $slug) {
                    if (isset($seenSlugs[$slug])) {
                        continue;
                    }

                    $country = $bySlug->get($slug);
                    $iso = strtoupper((string) ($country?->countrycode ?? ''));

                    if ($iso !== '' && isset($seenIsos[$iso])) {
                        $rows = $rows->map(function (array $row) use ($iso, $slug, $campCounts, $tripCounts) {
                            if (strtoupper((string) ($row['countrycode'] ?? '')) !== $iso) {
                                return $row;
                            }

                            $row['camps'] += (int) ($campCounts[$slug] ?? 0);
                            $row['trips'] += (int) ($tripCounts[$slug] ?? 0);

                            return $row;
                        });

                        continue;
                    }

                    $rows->push($this->hubGridRow($country, $slug, $campCounts, $tripCounts, $locale));
                    $seenSlugs[$slug] = true;
                    if ($iso !== '') {
                        $seenIsos[$iso] = true;
                    }
                }

                return $rows
                    ->reject(fn (array $row) => ($row['camps'] + $row['trips']) === 0)
                    ->sortByDesc(fn (array $row) => $row['camps'] + $row['trips'])
                    ->values();
            }
        );
    }

    /**
     * @param  Collection<string, int>  $campCounts
     * @param  Collection<string, int>  $tripCounts
     * @return array{destination: ?CategoryEntity, slug: string, name: string, sub_title: ?string, camps: int, trips: int, thumbnail_path: ?string, countrycode: ?string}
     */
    private function hubGridRow(
        ?CategoryEntity $country,
        string $slug,
        Collection $campCounts,
        Collection $tripCounts,
        string $locale,
    ): array {
        $camps = (int) ($campCounts[$slug] ?? 0);
        $trips = (int) ($tripCounts[$slug] ?? 0);
        $thumbnailPath = $this->thumbnails->path(
            $country?->thumbnail_path,
            CategoryListingThumbnailFallback::KIND_COUNTRY,
            $slug,
            $country?->countrycode,
        );

        $translation = $country !== null
            ? $this->categoryContent->findForEntity(CategoryPageEntityType::GEO_COUNTRY, $country->id, CategoryPageScope::VACATIONS, $locale)
            : null;

        return [
            'destination' => $country,
            'slug' => $slug,
            'name' => $country
                ? $this->homepageCountries->labelFor($country, $locale)
                : ucfirst(str_replace('-', ' ', $slug)),
            'sub_title' => $translation?->sub_title,
            'camps' => $camps,
            'trips' => $trips,
            'thumbnail_path' => $thumbnailPath,
            'countrycode' => $country?->countrycode,
        ];
    }

    /**
     * @return Collection<string, int>
     */
    private function canonicalCountryCounts(string $table): Collection
    {
        return DB::table($table)
            ->select('country', DB::raw('COUNT(*) as total'))
            ->where('status', 'active')
            ->whereNotNull('country')
            ->where('country', '!=', '')
            ->groupBy('country')
            ->get()
            ->reduce(function (Collection $counts, object $row) {
                $slug = CountrySlug::canonicalize($row->country);
                if ($slug === null) {
                    return $counts;
                }

                $counts[$slug] = (int) ($counts[$slug] ?? 0) + (int) $row->total;

                return $counts;
            }, collect());
    }

    public function campRepository(): CampListingRepository
    {
        return $this->camps;
    }

    public function tripRepository(): TripListingRepository
    {
        return $this->trips;
    }
}
