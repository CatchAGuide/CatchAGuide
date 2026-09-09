<?php

namespace App\Services\Homepage;

use App\Domain\CategoryPage\CategoryPageEntityType;
use App\Domain\CategoryPage\CategoryPageScope;
use App\Models\CategoryEntity;
use App\Services\CategoryPage\CategoryPageContentService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class HomepageCountrySelector
{
    public function __construct(
        private CategoryPageContentService $categoryContent,
    ) {}

    /**
     * Destination-rail countries. Pass $limit only for tests; production rails show every unique ISO.
     * When $categoryScope is set, only countries with filled category-page copy for that scope are included.
     *
     * @return Collection<int, array{slug: string, name: string, thumbnail: string, countrycode: ?string}>
     */
    public function featured(?int $limit = null, ?string $categoryScope = null): Collection
    {
        $locale = app()->getLocale();
        $cacheKey = 'homepage_featured_countries_v8_'.$locale.'_'.($limit ?? 'all').'_'.($categoryScope ?? 'all');

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($limit, $locale, $categoryScope) {
            $unique = $categoryScope !== null
                ? $this->uniqueModelsForScope($categoryScope, $locale)
                : $this->uniqueModels($locale);

            $withThumb = $unique->filter(fn (CategoryEntity $c) => filled($c->thumbnail_path));
            $withoutThumb = $unique->reject(fn (CategoryEntity $c) => filled($c->thumbnail_path));
            $ordered = $withThumb->concat($withoutThumb);

            if ($limit !== null) {
                $ordered = $ordered->take($limit);
            }

            return $ordered->map(function (CategoryEntity $country) use ($locale) {
                return [
                    'slug' => $country->slug,
                    'name' => $this->displayName($country, $locale),
                    'thumbnail' => $country->getThumbnailPath(),
                    'countrycode' => $country->countrycode,
                ];
            })->values();
        });
    }

    public function totalCount(): int
    {
        return Cache::remember('homepage_country_total_count_v3', now()->addHour(), function () {
            $countries = CategoryEntity::countries()->get(['id', 'countrycode']);

            $withIso = $countries
                ->filter(fn (CategoryEntity $c) => filled($c->countrycode))
                ->unique(fn (CategoryEntity $c) => strtoupper((string) $c->countrycode));

            $withoutIso = $countries->reject(fn (CategoryEntity $c) => filled($c->countrycode));

            return $withIso->count() + $withoutIso->count();
        });
    }

    /**
     * Keep countries that have filled category-page copy for the given scope.
     * ISO duplicates share eligibility so content on either country row still surfaces the rail tile.
     *
     * @param  Collection<int, CategoryEntity>  $countries
     * @return Collection<int, CategoryEntity>
     */
    private function filterByCategoryPageScope(Collection $countries, string $scope): Collection
    {
        $sourceIds = $this->categoryContent->sourceIdsWithMeaningfulScope(
            CategoryPageEntityType::GEO_COUNTRY,
            $scope,
        );

        if ($sourceIds === []) {
            return collect();
        }

        $withScope = CategoryEntity::countries()
            ->whereIn('id', $sourceIds)
            ->get(['id', 'countrycode']);

        $isos = $withScope
            ->pluck('countrycode')
            ->filter()
            ->map(fn ($code) => strtoupper((string) $code))
            ->unique()
            ->all();

        $idsWithoutIso = $withScope
            ->reject(fn (CategoryEntity $country) => filled($country->countrycode))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        return $countries->filter(function (CategoryEntity $country) use ($isos, $idsWithoutIso) {
            $iso = strtoupper((string) ($country->countrycode ?? ''));

            if ($iso !== '') {
                return in_array($iso, $isos, true);
            }

            return in_array((int) $country->id, $idsWithoutIso, true);
        })->values();
    }

    /**
     * Unique country models (one row per ISO) for destination rails.
     *
     * @param  Collection<int, CategoryEntity>|null  $countries
     * @return Collection<int, CategoryEntity>
     */
    public function uniqueModels(?string $locale = null, ?Collection $countries = null): Collection
    {
        $locale = $locale ?? app()->getLocale();
        $countries ??= CategoryEntity::countries()
            ->orderBy('name')
            ->get();

        return $this->uniqueByCountryCode($countries, $locale);
    }

    /**
     * Unique countries that have filled category-page copy for the given scope.
     *
     * @param  Collection<int, CategoryEntity>|null  $countries
     * @return Collection<int, CategoryEntity>
     */
    public function uniqueModelsForScope(string $scope, ?string $locale = null, ?Collection $countries = null): Collection
    {
        return $this->filterByCategoryPageScope($this->uniqueModels($locale, $countries), $scope);
    }

    /**
     * Keep one row per ISO so EN/DE migration duplicates (Finland / Finnland) cannot both appear.
     *
     * @param  Collection<int, CategoryEntity>  $countries
     * @return Collection<int, CategoryEntity>
     */
    private function uniqueByCountryCode(Collection $countries, string $locale): Collection
    {
        $preferred = $countries->sort(function (CategoryEntity $a, CategoryEntity $b) use ($locale) {
            $thumbCmp = (filled($a->thumbnail_path) ? 0 : 1) <=> (filled($b->thumbnail_path) ? 0 : 1);
            if ($thumbCmp !== 0) {
                return $thumbCmp;
            }

            $localeCmp = ($this->hasLocaleTranslation($a, $locale) ? 0 : 1)
                <=> ($this->hasLocaleTranslation($b, $locale) ? 0 : 1);
            if ($localeCmp !== 0) {
                return $localeCmp;
            }

            return $a->id <=> $b->id;
        });

        $seen = [];
        $unique = collect();

        foreach ($preferred as $country) {
            $iso = strtoupper((string) ($country->countrycode ?? ''));

            if ($iso === '') {
                $unique->push($country);
                continue;
            }

            if (isset($seen[$iso])) {
                continue;
            }

            $seen[$iso] = true;
            $unique->push($country);
        }

        return $unique->sortBy(fn (CategoryEntity $c) => mb_strtolower($this->displayName($c, $locale)))->values();
    }

    public function labelFor(CategoryEntity $country, ?string $locale = null): string
    {
        return $this->displayName($country, $locale ?? app()->getLocale());
    }

    private function displayName(CategoryEntity $country, string $locale): string
    {
        // Prefer short locale labels (e.g. Finnland), not SEO translation titles.
        $iso = strtoupper((string) ($country->countrycode ?? ''));
        if ($iso !== '' && class_exists(\Symfony\Component\Intl\Countries::class)) {
            try {
                return \Symfony\Component\Intl\Countries::getName($iso, $locale);
            } catch (\Throwable) {
                // fall through
            }
        }

        $globalKey = 'global.'.$country->name;
        $translated = __($globalKey);
        if ($translated !== $globalKey) {
            return $translated;
        }

        return $country->name;
    }

    /**
     * Sort tiebreaker only (which of two same-ISO duplicate rows to prefer) — checks every scope
     * a Country page can carry content in, since this selector isn't scope-specific itself.
     */
    private function hasLocaleTranslation(CategoryEntity $country, string $locale): bool
    {
        foreach ([CategoryPageScope::GLOBAL, CategoryPageScope::TOURS, CategoryPageScope::VACATIONS, CategoryPageScope::TRIPS, CategoryPageScope::CAMPS] as $scope) {
            $language = $this->categoryContent->findForEntity(
                CategoryPageEntityType::GEO_COUNTRY,
                $country->id,
                $scope,
                $locale,
            );

            if ($language !== null && filled($language->title)) {
                return true;
            }
        }

        return false;
    }
}
