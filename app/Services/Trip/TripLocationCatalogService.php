<?php

namespace App\Services\Trip;

use App\Domain\CategoryPage\CategoryPageEntityType;
use App\Domain\CategoryPage\CategoryPageScope;
use App\Domain\Vacation\CountrySlug;
use App\Models\CategoryEntity;
use App\Models\Trip;
use App\Services\CategoryPage\CategoryPageContentService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Public catalog: trip location categories using c_countries + trip listings by country slug.
 */
class TripLocationCatalogService
{
    public function __construct(
        private CategoryPageContentService $categoryContent,
    ) {}

    public function listLocationsForLocale(?string $locale = null): Collection
    {
        return CategoryEntity::countries()
            ->orderBy('name')
            ->get();
    }

    /**
     * @return array{
     *   row_data: CategoryEntity,
     *   faq: \Illuminate\Support\Collection,
     *   fish_chart: \Illuminate\Support\Collection,
     *   fish_size_limit: \Illuminate\Support\Collection,
     *   fish_time_limit: \Illuminate\Support\Collection,
     *   trips: LengthAwarePaginator,
     *   trips_total: int
     * }
     */
    public function buildCategoryPageData(Request $request, string $locationSlug, ?string $locale = null): array
    {
        $locale = $locale ?? app()->getLocale();

        $row_data = CategoryEntity::countries()
            ->where('slug', $locationSlug)
            ->first();

        if ($row_data === null) {
            abort(404);
        }

        if ($row_data->id) {
            $row_data = $this->categoryContent->applyScopedContentToModel(
                $row_data,
                CategoryPageEntityType::GEO_COUNTRY,
                CategoryPageScope::TRIPS,
                $locale,
                null,
            );
        }

        $faq = $this->categoryContent->resolveFaqsForEntityDisplay(
            CategoryPageEntityType::GEO_COUNTRY,
            $row_data->id,
            CategoryPageScope::TRIPS,
            $locale,
            null,
        );

        $base = Trip::query()
            ->where('status', 'active')
            ->where(function (Builder $q) use ($locationSlug) {
                foreach (CountrySlug::storageVariants($locationSlug) as $variant) {
                    $q->orWhereRaw('LOWER(country) = ?', [mb_strtolower($variant, 'UTF-8')]);
                }
            });

        $trips_total = (clone $base)->count();

        $trips = $this->applyListingSort($base, $request)->paginate(9)->appends($request->except('page'));

        return [
            'row_data' => $row_data,
            'faq' => $faq,
            'fish_chart' => $row_data->fish_charts(),
            'fish_size_limit' => $row_data->fish_size_limits(),
            'fish_time_limit' => $row_data->fish_time_limits(),
            'trips' => $trips,
            'trips_total' => $trips_total,
        ];
    }

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
