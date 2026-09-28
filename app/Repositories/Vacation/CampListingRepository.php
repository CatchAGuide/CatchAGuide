<?php

namespace App\Repositories\Vacation;

use App\Domain\Vacation\BookableListingPolicy;
use App\Domain\Vacation\CountrySlug;
use App\Domain\Vacation\VacationListingFilter;
use App\Models\Camp;
use App\Repositories\Vacation\Contracts\ListingRepositoryInterface;
use App\Services\Vacation\VacationFilterApplicator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator as PaginationLengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

class CampListingRepository implements ListingRepositoryInterface
{
    private const LISTING_RELATIONS = ['rentalBoats', 'facilities', 'guidings.guidingMethods', 'accommodations'];

    public function __construct(
        private BookableListingPolicy $policy,
        private VacationFilterApplicator $filterApplicator,
    ) {}

    public function countActive(?string $country = null): int
    {
        return $this->baseQuery($country)->count();
    }

    public function countCountriesWithListings(): int
    {
        return (int) Camp::query()
            ->where('status', $this->policy->activeStatus())
            ->whereNotNull('country')
            ->where('country', '!=', '')
            ->distinct()
            ->count('country');
    }

    public function minEntryPrice(?string $country = null): ?float
    {
        $camps = $this->baseQuery($country)->get(['id']);
        $prices = $camps->map(fn (Camp $camp) => $camp->getLowestAccommodationOrOfferPrice())
            ->filter(fn ($p) => $p !== null && $p > 0);

        return $prices->isEmpty() ? null : (float) $prices->min();
    }

    public function paginateForCountry(VacationListingFilter $filter, int $perPage): LengthAwarePaginator
    {
        if (in_array($filter->sortBy, ['price-asc', 'price-desc'], true)) {
            return $this->paginateByPrice($filter, $perPage);
        }

        $query = $this->queryForCountry($filter)
            ->with(self::LISTING_RELATIONS);

        return $this->filterApplicator->applyCampSort($query, $filter)->paginate($perPage)->appends(request()->except('page'));
    }

    /**
     * A camp's price is derived from its accommodations and special offers (not a column), so price
     * sorts rank every matching camp in memory and hydrate only the current page.
     */
    private function paginateByPrice(VacationListingFilter $filter, int $perPage): LengthAwarePaginator
    {
        $prices = $this->queryForCountry($filter)
            ->with(['accommodations', 'specialOffers'])
            ->get()
            ->mapWithKeys(fn (Camp $camp) => [(int) $camp->id => $camp->getLowestAccommodationOrOfferPrice()]);

        // Camps without a price go last in both directions; ties keep a stable id order.
        $descending = $filter->sortBy === 'price-desc';
        $orderedIds = $prices->keys()
            ->sort(function (int $a, int $b) use ($prices, $descending) {
                $priceA = $prices[$a];
                $priceB = $prices[$b];
                if ($priceA === null || $priceB === null) {
                    return [$priceA === null, $a] <=> [$priceB === null, $b];
                }

                return ($descending ? $priceB <=> $priceA : $priceA <=> $priceB) ?: $a <=> $b;
            })
            ->values();

        $page = Paginator::resolveCurrentPage();
        $pageIds = $orderedIds->slice(($page - 1) * $perPage, $perPage)->values();
        $campsById = $pageIds->isEmpty()
            ? collect()
            : Camp::query()->with(self::LISTING_RELATIONS)->whereIn('id', $pageIds->all())->get()->keyBy('id');

        return new PaginationLengthAwarePaginator(
            $pageIds->map(fn (int $id) => $campsById->get($id))->filter()->values()->all(),
            $orderedIds->count(),
            $perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath(), 'query' => request()->except('page')],
        );
    }

    public function queryForCountry(VacationListingFilter $filter): Builder
    {
        return $this->filterApplicator->applyToCampQuery(
            $this->baseQuery($filter->country, $filter->countryShort),
            $filter
        );
    }

    public function listNewest(int $limit, ?string $country = null): Collection
    {
        return $this->baseQuery($country)
            ->with(['rentalBoats', 'facilities', 'guidings.guidingMethods', 'accommodations'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function listForHub(int $limit): Collection
    {
        return $this->baseQuery(null)
            ->with(['rentalBoats', 'facilities', 'guidings.guidingMethods', 'accommodations'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    private function baseQuery(?string $country, ?string $countryShort = null): Builder
    {
        $query = Camp::query()->where('status', $this->policy->activeStatus());

        if ($country !== null && $country !== '') {
            $variants = CountrySlug::storageVariants($country, $countryShort);
            $query->where(function (Builder $q) use ($variants) {
                foreach ($variants as $variant) {
                    $q->orWhereRaw('LOWER(country) = ?', [mb_strtolower($variant, 'UTF-8')]);
                }
            });
        }

        return $query;
    }
}
