<?php

namespace App\Domain\Vacation\ViewModels;

use App\Domain\Vacation\VacationListingFilter;
use App\Models\Destination;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

final class VacationCountryViewModel
{
    public function __construct(
        public readonly Destination $destination,
        public readonly VacationListingFilter $filter,
        public readonly PillarSectionViewModel $tripsSection,
        public readonly PillarSectionViewModel $campsSection,
        public readonly LengthAwarePaginator $trips,
        public readonly LengthAwarePaginator $camps,
        /** @var LengthAwarePaginator<int, array{type: string, model: mixed}> */
        public readonly LengthAwarePaginator $listings,
        public readonly int $tripsTotal,
        public readonly int $campsTotal,
        public readonly int $listingsTotal,
        public readonly Collection $faq,
        public readonly Collection $fishChart,
        public readonly Collection $speciesOptions,
        public readonly array $mapMarkers,
    ) {}
}
