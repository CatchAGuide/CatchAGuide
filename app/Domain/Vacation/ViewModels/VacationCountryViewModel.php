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

    public function isAllOffers(): bool
    {
        return $this->destination->slug === 'all-offers';
    }

    /**
     * Pillar toggles navigate to dedicated landing pages so titles/subtitles update.
     *
     * @return array{all: string, trips: string, camps: string}
     */
    public function pillarToggleUrls(): array
    {
        $query = array_filter([
            'species' => $this->filter->species,
            'sortby' => $this->filter->sortBy,
        ]);

        $withQuery = fn (string $url) => $query === []
            ? $url
            : $url.'?'.http_build_query($query);

        if ($this->isAllOffers()) {
            return [
                'all' => $withQuery(route('vacations.all-offers')),
                'trips' => $withQuery(route('vacations.trips.index')),
                'camps' => $withQuery(route('vacations.camps.index')),
            ];
        }

        $countrySlug = $this->destination->slug;

        return [
            'all' => $withQuery(route('vacations.country', $countrySlug)),
            'trips' => $withQuery(route('vacations.trips.show', $countrySlug)),
            'camps' => $withQuery(route('vacations.camps.show', $countrySlug)),
        ];
    }
}
