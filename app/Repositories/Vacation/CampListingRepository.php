<?php

namespace App\Repositories\Vacation;

use App\Domain\Vacation\BookableListingPolicy;
use App\Domain\Vacation\VacationListingFilter;
use App\Models\Camp;
use App\Repositories\Vacation\Contracts\ListingRepositoryInterface;
use App\Services\Vacation\VacationFilterApplicator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CampListingRepository implements ListingRepositoryInterface
{
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
        $query = $this->filterApplicator->applyToCampQuery(
            $this->baseQuery($filter->country)->with(['rentalBoats', 'facilities', 'guidings', 'accommodations']),
            $filter
        );

        return $this->filterApplicator->applyCampSort($query, $filter)->paginate($perPage)->appends(request()->except('page'));
    }

    public function queryForCountry(VacationListingFilter $filter): Builder
    {
        return $this->filterApplicator->applyToCampQuery($this->baseQuery($filter->country), $filter);
    }

    public function listNewest(int $limit, ?string $country = null): Collection
    {
        return $this->baseQuery($country)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function listForHub(int $limit): Collection
    {
        return $this->baseQuery(null)
            ->with(['rentalBoats', 'facilities', 'guidings'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    private function baseQuery(?string $country): Builder
    {
        $query = Camp::query()->where('status', $this->policy->activeStatus());

        if ($country !== null && $country !== '') {
            $query->where('country', $country);
        }

        return $query;
    }
}
