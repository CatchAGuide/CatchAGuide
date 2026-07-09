<?php

namespace App\Repositories\Vacation;

use App\Domain\Destination\DestinationCategoryType;
use App\Models\Destination;
use App\Repositories\Vacation\Contracts\ListingRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class VacationDestinationRepository
{
    public function __construct(
        private CampListingRepository $camps,
        private TripListingRepository $trips,
    ) {}

    public function findCountryForLocale(string $slug, ?string $locale = null): ?Destination
    {
        $locale = $locale ?? app()->getLocale();

        return $this->countryDestinationQuery()
            ->whereRaw('LOWER(slug) = ?', [strtolower($slug)])
            ->where('language', $locale)
            ->where('type', DestinationCategoryType::VACATIONS)
            ->first();
    }

    public function mergeCountryContent(string $slug, ?string $locale = null): ?Destination
    {
        $slug = strtolower($slug);
        $locale = $locale ?? app()->getLocale();

        $primary = $this->findCountryForLocale($slug, $locale);
        if ($primary !== null) {
            return $primary;
        }

        $vacationsFallback = $this->countryDestinationQuery()
            ->whereRaw('LOWER(slug) = ?', [$slug])
            ->where('type', DestinationCategoryType::VACATIONS)
            ->orderByRaw('CASE WHEN language = ? THEN 0 ELSE 1 END', [$locale])
            ->first();

        if ($vacationsFallback !== null) {
            return $vacationsFallback;
        }

        return $this->countryDestinationQuery()
            ->whereRaw('LOWER(slug) = ?', [$slug])
            ->where('type', DestinationCategoryType::TRIPS)
            ->orderByRaw('CASE WHEN language = ? THEN 0 ELSE 1 END', [$locale])
            ->first();
    }

    /**
     * @return array{destination: ?Destination, slug: string, name: string, sub_title: ?string, camps: int, trips: int, thumbnail_path: ?string, countrycode: ?string}|null
     */
    public function hubGridCountry(string $slug, ?string $locale = null): ?array
    {
        $slug = strtolower($slug);

        return $this->countriesForHubGrid($locale)
            ->first(fn (array $row) => strtolower($row['slug']) === $slug);
    }

    public function isKnownCountrySlug(string $slug, ?string $pillar = null): bool
    {
        $slug = strtolower($slug);

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
     * @return array{destination: Destination, slug: string}|null
     */
    public function resolveCountryPage(string $slug, ?string $pillar = null, ?string $locale = null): ?array
    {
        $slug = strtolower($slug);
        $locale = $locale ?? app()->getLocale();

        if ($pillar !== null && ! $this->isKnownCountrySlug($slug, $pillar)) {
            return null;
        }

        if ($pillar === null && ! $this->isKnownCountrySlug($slug)) {
            return null;
        }

        $destination = $this->mergeCountryContent($slug, $locale);
        $hubRow = $this->hubGridCountry($slug, $locale);

        if ($destination === null && $hubRow !== null) {
            $destination = new Destination([
                'slug' => $hubRow['slug'],
                'name' => $hubRow['name'],
                'sub_title' => $hubRow['sub_title'],
                'thumbnail_path' => $hubRow['thumbnail_path'],
                'countrycode' => $hubRow['countrycode'],
                'type' => DestinationCategoryType::VACATIONS,
                'language' => $locale,
            ]);
        }

        if ($destination === null) {
            return null;
        }

        return [
            'destination' => $destination,
            'slug' => $slug,
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<Destination>
     */
    private function countryDestinationQuery()
    {
        return Destination::query()
            ->with(['faq', 'fish_chart', 'fish_size_limit', 'fish_time_limit']);
    }

    /**
     * Countries for navbar/search dropdowns — same set as the hub grid, sorted A–Z.
     *
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
     * @return Collection<int, array{destination: ?Destination, slug: string, name: string, sub_title: ?string, camps: int, trips: int, thumbnail_path: ?string, countrycode: ?string}>
     */
    public function countriesForHubGrid(?string $locale = null): Collection
    {
        $locale = $locale ?? app()->getLocale();

        $campCounts = DB::table('camps')
            ->select(DB::raw('LOWER(country) as country_slug'), DB::raw('COUNT(*) as total'))
            ->where('status', 'active')
            ->whereNotNull('country')
            ->where('country', '!=', '')
            ->groupBy(DB::raw('LOWER(country)'))
            ->pluck('total', 'country_slug');

        $tripCounts = DB::table('trips')
            ->select(DB::raw('LOWER(country) as country_slug'), DB::raw('COUNT(*) as total'))
            ->where('status', 'active')
            ->whereNotNull('country')
            ->where('country', '!=', '')
            ->groupBy(DB::raw('LOWER(country)'))
            ->pluck('total', 'country_slug');

        $slugs = $campCounts->keys()->merge($tripCounts->keys())->unique()->values();

        $destinations = Destination::query()
            ->whereIn('type', [DestinationCategoryType::VACATIONS, DestinationCategoryType::TRIPS])
            ->get()
            ->groupBy(fn (Destination $destination) => strtolower($destination->slug));

        return $slugs->map(function (string $slug) use ($destinations, $campCounts, $tripCounts) {
            $destination = $this->resolveHubCountryDestination($destinations, $slug);
            $thumbnailPath = $destination?->thumbnail_path;

            if (empty($thumbnailPath)) {
                $thumbnailPath = $this->listingThumbnailForCountry($slug);
            }

            return [
                'destination' => $destination,
                'slug' => $destination?->slug ?? $slug,
                'name' => $destination?->name ?? ucfirst(str_replace('-', ' ', $slug)),
                'sub_title' => $destination?->sub_title,
                'camps' => (int) ($campCounts[$slug] ?? 0),
                'trips' => (int) ($tripCounts[$slug] ?? 0),
                'thumbnail_path' => $thumbnailPath,
                'countrycode' => $destination?->countrycode,
            ];
        })->sortByDesc(fn ($row) => $row['camps'] + $row['trips'])->values();
    }

    /**
     * @param  Collection<string, Collection<int, Destination>>  $destinations
     */
    private function resolveHubCountryDestination(Collection $destinations, string $slug): ?Destination
    {
        $group = $destinations->get($slug);

        if ($group === null) {
            return null;
        }

        $locale = app()->getLocale();

        return $group->first(fn (Destination $destination) => $destination->language === $locale && $destination->type === DestinationCategoryType::VACATIONS)
            ?? $group->first(fn (Destination $destination) => $destination->type === DestinationCategoryType::VACATIONS)
            ?? $group->first(fn (Destination $destination) => $destination->language === $locale && $destination->type === DestinationCategoryType::TRIPS)
            ?? $group->firstWhere('type', DestinationCategoryType::TRIPS);
    }

    private function listingThumbnailForCountry(string $slug): ?string
    {
        $campThumb = DB::table('camps')
            ->where('status', 'active')
            ->whereRaw('LOWER(country) = ?', [$slug])
            ->whereNotNull('thumbnail_path')
            ->where('thumbnail_path', '!=', '')
            ->orderByDesc('id')
            ->value('thumbnail_path');

        if (! empty($campThumb)) {
            return $campThumb;
        }

        return DB::table('trips')
            ->where('status', 'active')
            ->whereRaw('LOWER(country) = ?', [$slug])
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
