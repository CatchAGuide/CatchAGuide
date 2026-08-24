<?php

namespace App\Domain\Offers\ViewModels;

use App\Domain\Offers\OfferListingFilter;
use App\Domain\Vacation\CountrySlug;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

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
        public readonly Collection $methodOptions,
        public readonly Collection $waterOptions,
        public readonly Collection $tourDurationOptions,
        public readonly Collection $tripDurationOptions,
        public readonly Collection $accommodationTypeOptions,
        public readonly Collection $faq,
        public readonly array $mapMarkers,
        public readonly Collection $suggestedCards,
        public readonly ?string $catalogUrl = null,
        public readonly bool $lockDestinationScope = false,
        public readonly bool $lockSpeciesScope = false,
        public readonly bool $lockTourScope = false,
        public readonly bool $lockVacationScope = false,
        public readonly bool $lockMethodScope = false,
    ) {}

    public function pageTitle(): string
    {
        $type = $this->typeTitle();
        $place = $this->locationLabel();
        $species = $this->speciesLabel();

        return match (true) {
            $place !== null && $species !== null => __('offers.title_in_for', [
                'type' => $type,
                'place' => $place,
                'species' => $species,
            ]),
            $place !== null => __('offers.title_in', [
                'type' => $type,
                'place' => $place,
            ]),
            $species !== null => __('offers.title_for', [
                'type' => $type,
                'species' => $species,
            ]),
            default => $type,
        };
    }

    private function typeTitle(): string
    {
        return match (true) {
            $this->filter->type === 'tour' => __('offers.title_tours'),
            $this->filter->type === 'vacation' && $this->filter->vacation === 'trip' => __('offers.title_trips'),
            $this->filter->type === 'vacation' && $this->filter->vacation === 'camp' => __('offers.title_camps'),
            $this->filter->type === 'vacation' => __('offers.title_vacations'),
            default => __('offers.title'),
        };
    }

    /**
     * Place name for the nearby-suggestions heading (country dropdown included).
     */
    public function suggestedPlaceLabel(): string
    {
        return $this->locationLabel() ?? __('offers.breadcrumb');
    }

    /**
     * Most specific location for the H1: search place, then city, region, country.
     */
    private function locationLabel(): ?string
    {
        foreach ([$this->filter->place, $this->filter->city, $this->filter->region] as $value) {
            $label = $this->trimmedLabel($value);
            if ($label !== null) {
                return $label;
            }
        }

        return $this->countryLabel();
    }

    private function countryLabel(): ?string
    {
        $slug = $this->filter->country;
        if ($slug === null || $slug === '') {
            return null;
        }

        $needles = [$slug];
        foreach (CountrySlug::storageVariants($slug, $this->filter->countryShort) as $variant) {
            $canonical = CountrySlug::canonicalize($variant);
            if ($canonical !== null) {
                $needles[] = $canonical;
            }
        }
        $needles = array_values(array_unique($needles));

        foreach ($this->countries as $row) {
            $rowSlug = CountrySlug::canonicalize($row['slug'] ?? null);
            if ($rowSlug !== null && in_array($rowSlug, $needles, true)) {
                $name = $this->trimmedLabel($row['name'] ?? null);
                if ($name !== null) {
                    return $name;
                }
            }
        }

        return Str::title(str_replace('-', ' ', $slug));
    }

    /**
     * Include target fish only when a single species is selected.
     */
    private function speciesLabel(): ?string
    {
        $idCount = count($this->filter->speciesIds);
        $nameCount = count($this->filter->speciesNames);

        if ($idCount + $nameCount !== 1) {
            return null;
        }

        if ($idCount === 1) {
            $id = $this->filter->speciesIds[0];
            $option = $this->speciesOptions->first(
                fn ($row) => is_numeric($row['id'] ?? null) && (int) $row['id'] === $id
            );

            return $this->trimmedLabel($option['name'] ?? null);
        }

        $name = $this->filter->speciesNames[0];
        $option = $this->speciesOptions->first(
            fn ($row) => (string) ($row['id'] ?? '') === $name
                || (string) ($row['name'] ?? '') === $name
        );

        return $this->trimmedLabel($option['name'] ?? $name);
    }

    private function trimmedLabel(mixed $value): ?string
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return null;
        }

        $label = trim((string) $value);

        return $label === '' ? null : $label;
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
        return $this->catalogUrl ?: route('offers.index');
    }

    public function vacationsTotal(): int
    {
        return $this->tripsTotal + $this->campsTotal;
    }

    /**
     * Params that stay locked on destination- or species-scoped catalogs.
     *
     * @return array<string, mixed>
     */
    public function lockedScopeParams(): array
    {
        $params = [];

        if ($this->lockSpeciesScope) {
            $params['species'] = $this->filter->speciesIds !== []
                ? $this->filter->speciesIds
                : ($this->filter->speciesNames !== [] ? $this->filter->speciesNames : null);
        }

        if ($this->lockDestinationScope) {
            $params = array_merge($params, [
                'country' => $this->filter->country,
                'country_short' => $this->filter->countryShort,
                'place' => $this->filter->place,
                'placeLat' => $this->filter->placeLat,
                'placeLng' => $this->filter->placeLng,
                'city' => $this->filter->city,
                'region' => $this->filter->region,
                'bounds_ne_lat' => $this->filter->boundsNeLat,
                'bounds_ne_lng' => $this->filter->boundsNeLng,
                'bounds_sw_lat' => $this->filter->boundsSwLat,
                'bounds_sw_lng' => $this->filter->boundsSwLng,
                'place_types' => $this->filter->placeTypes !== []
                    ? json_encode($this->filter->placeTypes)
                    : null,
            ]);
        }

        if ($this->lockTourScope) {
            $params['type'] = 'tour';
        }

        if ($this->lockVacationScope) {
            $params['type'] = 'vacation';
        }

        if ($this->lockMethodScope) {
            $params['methods'] = $this->filter->methodIds !== [] ? $this->filter->methodIds : null;
        }

        return array_filter($params, fn ($v) => $v !== null && $v !== '');
    }

    /**
     * Shared params preserved across type/vacation toggles (product facets intentionally omitted).
     *
     * @return array<string, mixed>
     */
    public function sharedQueryParams(): array
    {
        return array_filter([
            'species' => $this->filter->speciesIds !== []
                ? $this->filter->speciesIds
                : ($this->filter->speciesNames !== [] ? $this->filter->speciesNames : null),
            'country' => $this->filter->country,
            'sortby' => $this->filter->sortBy,
            'place' => $this->filter->place,
            'placeLat' => $this->filter->placeLat,
            'placeLng' => $this->filter->placeLng,
            'city' => $this->filter->city,
            'region' => $this->filter->region,
            'num_guests' => $this->filter->numGuests,
            'country_short' => $this->filter->countryShort,
            'user_lat' => $this->filter->userLat,
            'user_lng' => $this->filter->userLng,
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

            return $this->catalogUrlWithQuery($params);
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

            return $this->catalogUrlWithQuery($params);
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

    /**
     * @param  array<string, mixed>  $params
     */
    private function catalogUrlWithQuery(array $params): string
    {
        $base = $this->filterAction();

        if ($params === []) {
            return $base;
        }

        $separator = str_contains($base, '?') ? '&' : '?';

        return $base.$separator.http_build_query($params);
    }
}
