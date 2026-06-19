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

        return Destination::query()
            ->with(['faq', 'fish_chart', 'fish_size_limit', 'fish_time_limit'])
            ->whereRaw('LOWER(slug) = ?', [strtolower($slug)])
            ->where('language', $locale)
            ->where('type', DestinationCategoryType::VACATIONS)
            ->first();
    }

    public function mergeCountryContent(string $slug, ?string $locale = null): ?Destination
    {
        $primary = $this->findCountryForLocale($slug, $locale);

        if ($primary !== null) {
            return $primary;
        }

        $locale = $locale ?? app()->getLocale();

        return Destination::query()
            ->with(['faq', 'fish_chart', 'fish_size_limit', 'fish_time_limit'])
            ->whereRaw('LOWER(slug) = ?', [strtolower($slug)])
            ->where('language', $locale)
            ->where('type', DestinationCategoryType::TRIPS)
            ->first();
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
            ->where('language', $locale)
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

        return $group->firstWhere('type', DestinationCategoryType::VACATIONS)
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
