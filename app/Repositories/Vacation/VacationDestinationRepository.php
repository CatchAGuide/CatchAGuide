<?php

namespace App\Repositories\Vacation;

use App\Domain\Vacation\CountrySlug;
use App\Models\Country;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class VacationDestinationRepository
{
    public function __construct(
        private CampListingRepository $camps,
        private TripListingRepository $trips,
    ) {}

    public function findCountryForLocale(string $slug, ?string $locale = null): ?Country
    {
        $slug = CountrySlug::canonicalize($slug) ?? strtolower($slug);

        return Country::with(['translations', 'faqs', 'fish_charts', 'fish_size_limits', 'fish_time_limits'])
            ->whereRaw('LOWER(slug) = ?', [$slug])
            ->first();
    }

    public function mergeCountryContent(string $slug, ?string $locale = null): ?Country
    {
        return $this->findCountryForLocale($slug, $locale);
    }

    /**
     * @return array{destination: Country, slug: string, name: string, sub_title: ?string, camps: int, trips: int, thumbnail_path: ?string, countrycode: ?string}|null
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
     * @return array{destination: Country, slug: string}|null
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
            $country = new Country([
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
     * @return Collection<int, object{slug: string, name: string}>
     */
    public function countriesForSearch(?string $locale = null): Collection
    {
        return $this->countriesForHubGrid($locale)
            ->map(fn (array $row) => (object) [
                'slug' => $row['slug'],
                'name' => $row['name'],
            ])
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }

    /**
     * @return Collection<int, array{destination: ?Country, slug: string, name: string, sub_title: ?string, camps: int, trips: int, thumbnail_path: ?string, countrycode: ?string}>
     */
    public function countriesForHubGrid(?string $locale = null): Collection
    {
        $locale = $locale ?? app()->getLocale();

        $campCounts = $this->canonicalCountryCounts('camps');
        $tripCounts = $this->canonicalCountryCounts('trips');

        $slugs = $campCounts->keys()->merge($tripCounts->keys())->unique()->values();

        $countries = Country::with('translations')->get()
            ->keyBy(fn (Country $c) => CountrySlug::canonicalize($c->slug) ?? strtolower($c->slug));

        return $slugs->map(function (string $slug) use ($countries, $campCounts, $tripCounts, $locale) {
            $country = $countries->get(CountrySlug::canonicalize($slug) ?? $slug);
            $thumbnailPath = $country?->thumbnail_path;

            if (empty($thumbnailPath)) {
                $thumbnailPath = $this->listingThumbnailForCountry($slug);
            }

            $canonicalSlug = CountrySlug::canonicalize($country?->slug) ?? $slug;
            $translation = $country?->translations?->firstWhere('language', $locale);
            $name = $country?->name ?? ucfirst(str_replace('-', ' ', $slug));

            return [
                'destination' => $country,
                'slug' => $canonicalSlug,
                'name' => $name,
                'sub_title' => $translation?->sub_title,
                'camps' => (int) ($campCounts[$slug] ?? 0),
                'trips' => (int) ($tripCounts[$slug] ?? 0),
                'thumbnail_path' => $thumbnailPath,
                'countrycode' => $country?->countrycode,
            ];
        })->sortByDesc(fn ($row) => $row['camps'] + $row['trips'])->values();
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

    private function listingThumbnailForCountry(string $slug): ?string
    {
        $variants = CountrySlug::storageVariants($slug);

        $campThumb = DB::table('camps')
            ->where('status', 'active')
            ->where(function ($q) use ($variants) {
                foreach ($variants as $variant) {
                    $q->orWhereRaw('LOWER(country) = ?', [mb_strtolower($variant, 'UTF-8')]);
                }
            })
            ->whereNotNull('thumbnail_path')
            ->where('thumbnail_path', '!=', '')
            ->orderByDesc('id')
            ->value('thumbnail_path');

        if (! empty($campThumb)) {
            return $campThumb;
        }

        return DB::table('trips')
            ->where('status', 'active')
            ->where(function ($q) use ($variants) {
                foreach ($variants as $variant) {
                    $q->orWhereRaw('LOWER(country) = ?', [mb_strtolower($variant, 'UTF-8')]);
                }
            })
            ->whereNotNull('thumbnail_path')
            ->where('thumbnail_path', '!=', '')
            ->orderByDesc('id')
            ->value('thumbnail_path');
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
