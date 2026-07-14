<?php

namespace App\Repositories\Vacation;

use App\Domain\Vacation\BookableListingPolicy;
use App\Domain\Vacation\CountrySlug;
use App\Domain\Vacation\VacationListingFilter;
use App\Models\Trip;
use App\Repositories\Vacation\Contracts\ListingRepositoryInterface;
use App\Services\Vacation\VacationFilterApplicator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class TripListingRepository implements ListingRepositoryInterface
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
        return (int) Trip::query()
            ->where('status', $this->policy->activeStatus())
            ->whereNotNull('country')
            ->where('country', '!=', '')
            ->distinct()
            ->count('country');
    }

    public function minEntryPrice(?string $country = null): ?float
    {
        $min = $this->baseQuery($country)->min('price_per_person');

        return $min !== null ? (float) $min : null;
    }

    public function paginateForCountry(VacationListingFilter $filter, int $perPage): LengthAwarePaginator
    {
        $query = $this->filterApplicator->applyToTripQuery($this->baseQuery($filter->country), $filter);

        return $this->filterApplicator->applyTripSort($query, $filter)->paginate($perPage)->appends(request()->except('page'));
    }

    public function queryForCountry(VacationListingFilter $filter): Builder
    {
        return $this->filterApplicator->applyToTripQuery($this->baseQuery($filter->country), $filter);
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
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    private function baseQuery(?string $country): Builder
    {
        $query = Trip::query()->where('status', $this->policy->activeStatus());

        if ($country !== null && $country !== '') {
            $variants = CountrySlug::storageVariants($country);
            $query->where(function (Builder $q) use ($variants) {
                foreach ($variants as $variant) {
                    $q->orWhereRaw('LOWER(country) = ?', [mb_strtolower($variant, 'UTF-8')]);
                }
            });
        }

        return $query;
    }
}
