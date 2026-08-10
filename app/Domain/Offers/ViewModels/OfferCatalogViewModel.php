<?php

namespace App\Domain\Offers\ViewModels;

use App\Domain\Offers\OfferListingFilter;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

final class OfferCatalogViewModel
{
    public function __construct(
        public readonly OfferListingFilter $filter,
        /** @var LengthAwarePaginator<int, array{type: string, model: mixed}> */
        public readonly LengthAwarePaginator $listings,
        public readonly Collection $cards,
        public readonly int $toursTotal,
        public readonly int $tripsTotal,
        public readonly int $campsTotal,
        public readonly int $listingsTotal,
        public readonly Collection $speciesOptions,
        public readonly Collection $countries,
        public readonly Collection $faq,
        public readonly array $mapMarkers,
        public readonly Collection $suggestedCards,
    ) {}

    public function pageTitle(): string
    {
        return match (true) {
            $this->filter->type === 'tour' => __('offers.title_tours'),
            $this->filter->type === 'vacation' && $this->filter->vacation === 'trip' => __('offers.title_trips'),
            $this->filter->type === 'vacation' && $this->filter->vacation === 'camp' => __('offers.title_camps'),
            $this->filter->type === 'vacation' => __('offers.title_vacations'),
            default => __('offers.title'),
        };
    }

    public function pageSubtitle(): string
    {
        return match (true) {
            $this->filter->type === 'tour' => __('offers.subtitle_tours'),
            $this->filter->type === 'vacation' && $this->filter->vacation === 'trip' => __('offers.subtitle_trips'),
            $this->filter->type === 'vacation' && $this->filter->vacation === 'camp' => __('offers.subtitle_camps'),
            $this->filter->type === 'vacation' => __('offers.subtitle_vacations'),
            default => __('offers.subtitle'),
        };
    }

    public function emptyStateMessage(): string
    {
        return match (true) {
            $this->filter->type === 'tour' => __('offers.empty_tours'),
            $this->filter->type === 'vacation' && $this->filter->vacation === 'trip' => __('offers.empty_trips'),
            $this->filter->type === 'vacation' && $this->filter->vacation === 'camp' => __('offers.empty_camps'),
            $this->filter->type === 'vacation' => __('offers.empty_vacations'),
            default => __('offers.empty'),
        };
    }

    public function filterAction(): string
    {
        return route('offers.index');
    }

    public function vacationsTotal(): int
    {
        return $this->tripsTotal + $this->campsTotal;
    }

    /**
     * @return array<string, mixed>
     */
    public function sharedQueryParams(): array
    {
        return array_filter([
            'species' => $this->filter->species,
            'country' => $this->filter->country,
            'sortby' => $this->filter->sortBy,
            'place' => $this->filter->place,
            'placeLat' => $this->filter->placeLat,
            'placeLng' => $this->filter->placeLng,
            'city' => $this->filter->city,
            'region' => $this->filter->region,
            'num_guests' => $this->filter->numGuests,
            'country_short' => $this->filter->countryShort,
            'bounds_ne_lat' => $this->filter->boundsNeLat,
            'bounds_ne_lng' => $this->filter->boundsNeLng,
            'bounds_sw_lat' => $this->filter->boundsSwLat,
            'bounds_sw_lng' => $this->filter->boundsSwLng,
            'place_types' => $this->filter->placeTypes !== []
                ? json_encode($this->filter->placeTypes)
                : null,
        ], fn ($v) => $v !== null && $v !== '');
    }

    /**
     * @return array{all: string, tour: string, vacation: string}
     */
    public function typeToggleUrls(): array
    {
        $query = $this->sharedQueryParams();

        $withQuery = function (string $type) use ($query): string {
            $params = $query;
            unset($params['vacation']);
            if ($type !== 'all') {
                $params['type'] = $type;
            }

            return $params === []
                ? route('offers.index')
                : route('offers.index', $params);
        };

        return [
            'all' => $withQuery('all'),
            'tour' => $withQuery('tour'),
            'vacation' => $withQuery('vacation'),
        ];
    }

    /**
     * @return array{all: string, trip: string, camp: string}
     */
    public function vacationToggleUrls(): array
    {
        $query = $this->sharedQueryParams();
        $query['type'] = 'vacation';

        $withVacation = function (string $vacation) use ($query): string {
            $params = $query;
            if ($vacation !== 'all') {
                $params['vacation'] = $vacation;
            } else {
                unset($params['vacation']);
            }

            return route('offers.index', $params);
        };

        return [
            'all' => $withVacation('all'),
            'trip' => $withVacation('trip'),
            'camp' => $withVacation('camp'),
        ];
    }

    public function resultsLabel(): string
    {
        return __('offers.results_count', ['count' => $this->listingsTotal]);
    }
}
