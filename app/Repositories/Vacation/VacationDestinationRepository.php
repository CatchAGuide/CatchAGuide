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
     * @return Collection<int, array{destination: Destination, camps: int, trips: int}>
     */
    public function countriesForHubGrid(?string $locale = null): Collection
    {
        $locale = $locale ?? app()->getLocale();

        $campCounts = DB::table('camps')
            ->select('country', DB::raw('COUNT(*) as total'))
            ->where('status', 'active')
            ->whereNotNull('country')
            ->groupBy('country')
            ->pluck('total', 'country');

        $tripCounts = DB::table('trips')
            ->select('country', DB::raw('COUNT(*) as total'))
            ->where('status', 'active')
            ->whereNotNull('country')
            ->groupBy('country')
            ->pluck('total', 'country');

        $slugs = $campCounts->keys()->merge($tripCounts->keys())->unique()->values();

        $destinations = Destination::query()
            ->where('type', DestinationCategoryType::VACATIONS)
            ->where('language', $locale)
            ->whereIn('slug', $slugs)
            ->get()
            ->keyBy('slug');

        return $slugs->map(function (string $slug) use ($destinations, $campCounts, $tripCounts) {
            $destination = $destinations->get($slug);

            return [
                'destination' => $destination,
                'slug' => $slug,
                'name' => $destination?->name ?? ucfirst(str_replace('-', ' ', $slug)),
                'camps' => (int) ($campCounts[$slug] ?? 0),
                'trips' => (int) ($tripCounts[$slug] ?? 0),
                'thumbnail_path' => $destination?->thumbnail_path,
                'countrycode' => $destination?->countrycode,
            ];
        })->sortByDesc(fn ($row) => $row['camps'] + $row['trips'])->values();
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
