<?php

namespace App\Services\Trip;

use App\Domain\Destination\DestinationCategoryType;
use App\Models\Destination;
use App\Models\Trip;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Public catalog: trip location categories (destinations.type=trips) + trip listings by category slug.
 */
class TripLocationCatalogService
{
    public function listLocationsForLocale(?string $locale = null): Collection
    {
        $locale = $locale ?? app()->getLocale();

        return Destination::query()
            ->where('type', DestinationCategoryType::TRIPS)
            ->where('language', $locale)
            ->orderBy('name')
            ->get();
    }

    /**
     * @return array{
     *   row_data: Destination,
     *   faq: \Illuminate\Database\Eloquent\Collection,
     *   fish_chart: \Illuminate\Database\Eloquent\Collection,
     *   fish_size_limit: \Illuminate\Database\Eloquent\Collection,
     *   fish_time_limit: \Illuminate\Database\Eloquent\Collection,
     *   trips: LengthAwarePaginator,
     *   trips_total: int
     * }
     */
    public function buildCategoryPageData(Request $request, string $locationSlug, ?string $locale = null): array
    {
        $locale = $locale ?? app()->getLocale();

        $row_data = Destination::query()
            ->with(['faq', 'fish_chart', 'fish_size_limit', 'fish_time_limit'])
            ->where('slug', $locationSlug)
            ->where('type', DestinationCategoryType::TRIPS)
            ->where('language', $locale)
            ->first();

        if ($row_data === null) {
            abort(404);
        }

        $base = Trip::query()
            ->where('status', 'active')
            ->where('country', $locationSlug);

        $trips_total = (clone $base)->count();

        $trips = $this->applyListingSort($base, $request)->paginate(9)->appends($request->except('page'));

        return [
            'row_data' => $row_data,
            'faq' => $row_data->faq,
            'fish_chart' => $row_data->fish_chart,
            'fish_size_limit' => $row_data->fish_size_limit,
            'fish_time_limit' => $row_data->fish_time_limit,
            'trips' => $trips,
            'trips_total' => $trips_total,
        ];
    }

    /**
     * @param Builder<Trip> $query
     * @return Builder<Trip>
     */
    private function applyListingSort(Builder $query, Request $request): Builder
    {
        $hasOnlyPage = count(array_diff(array_keys($request->all()), ['page'])) === 0;

        if ($request->filled('sortby')) {
            switch ($request->get('sortby')) {
                case 'newest':
                    return $query->orderByDesc('created_at');
                case 'price-asc':
                    return $query->orderBy('price_per_person')->orderBy('id');
                case 'price-desc':
                    return $query->orderByDesc('price_per_person')->orderByDesc('id');
                default:
                    return $query->orderBy('id');
            }
        }

        if ($hasOnlyPage) {
            return $query->inRandomOrder();
        }

        return $query->orderBy('id');
    }
}
