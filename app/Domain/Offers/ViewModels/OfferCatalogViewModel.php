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
        return match ($this->filter->type) {
            'tour' => __('offers.title_tours'),
            'trip' => __('offers.title_trips'),
            'camp' => __('offers.title_camps'),
            default => __('offers.title'),
        };
    }

    public function pageSubtitle(): string
    {
        return match ($this->filter->type) {
            'tour' => __('offers.subtitle_tours'),
            'trip' => __('offers.subtitle_trips'),
            'camp' => __('offers.subtitle_camps'),
            default => __('offers.subtitle'),
        };
    }

    public function emptyStateMessage(): string
    {
        return match ($this->filter->type) {
            'tour' => __('offers.empty_tours'),
            'trip' => __('offers.empty_trips'),
            'camp' => __('offers.empty_camps'),
            default => __('offers.empty'),
        };
    }

    public function filterAction(): string
    {
        return route('offers.index');
    }

    /**
     * @return array{all: string, tour: string, trip: string, camp: string}
     */
    public function typeToggleUrls(): array
    {
        $query = array_filter([
            'species' => $this->filter->species,
            'country' => $this->filter->country,
            'sortby' => $this->filter->sortBy,
            'place' => $this->filter->place,
            'placeLat' => $this->filter->placeLat,
            'placeLng' => $this->filter->placeLng,
            'city' => $this->filter->city,
            'region' => $this->filter->region,
        ], fn ($v) => $v !== null && $v !== '');

        $withQuery = function (string $type) use ($query): string {
            $params = $query;
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
            'trip' => $withQuery('trip'),
            'camp' => $withQuery('camp'),
        ];
    }

    public function resultsLabel(): string
    {
        return __('offers.results_count', ['count' => $this->listingsTotal]);
    }
}
