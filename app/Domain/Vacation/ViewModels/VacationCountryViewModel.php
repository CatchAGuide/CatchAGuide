<?php

namespace App\Domain\Vacation\ViewModels;

use App\Domain\Vacation\CountrySlug;
use App\Domain\Vacation\VacationListingFilter;
use App\Models\CategoryEntity;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

final class VacationCountryViewModel
{
    public function __construct(
        public readonly CategoryEntity $destination,
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
        public readonly Collection $accommodationTypeOptions,
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
            'species' => $this->filter->speciesIds !== []
                ? $this->filter->speciesIds
                : ($this->filter->speciesNames !== [] ? $this->filter->speciesNames : null),
            'sortby' => $this->filter->sortBy,
        ], fn ($v) => $v !== null && $v !== '');
        $tripsQuery = array_filter([
            ...$query,
            'duration' => $this->filter->tripDuration,
        ], fn ($v) => $v !== null && $v !== '');
        $campsQuery = array_filter([
            ...$query,
            ...$this->filter->campFacetQueryParams(),
        ], fn ($v) => $v !== null && $v !== '');

        $withQuery = fn (string $url, array $params = []) => $params === []
            ? $url
            : $url.'?'.http_build_query($params);

        if ($this->isAllOffers()) {
            return [
                'all' => $withQuery(route('vacations.all-offers'), $query),
                'trips' => $withQuery(route('vacations.trips.index'), $tripsQuery),
                'camps' => $withQuery(route('vacations.camps.index'), $campsQuery),
            ];
        }

        $countrySlug = CountrySlug::canonicalize($this->destination->slug)
            ?? strtolower((string) $this->destination->slug);

        return [
            'all' => $withQuery(route('vacations.country', $countrySlug), $query),
            'trips' => $withQuery(route('vacations.trips.show', $countrySlug), $tripsQuery),
            'camps' => $withQuery(route('vacations.camps.show', $countrySlug), $campsQuery),
        ];
    }
}
