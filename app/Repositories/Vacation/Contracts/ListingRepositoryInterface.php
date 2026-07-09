<?php

namespace App\Repositories\Vacation\Contracts;

use App\Domain\Vacation\VacationListingFilter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

interface ListingRepositoryInterface
{
    public function countActive(?string $country = null): int;

    public function countCountriesWithListings(): int;

    public function minEntryPrice(?string $country = null): ?float;

    public function paginateForCountry(VacationListingFilter $filter, int $perPage): LengthAwarePaginator;

    public function queryForCountry(VacationListingFilter $filter): Builder;

    public function listNewest(int $limit, ?string $country = null): Collection;

    public function listForHub(int $limit): Collection;
}
