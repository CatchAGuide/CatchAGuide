<?php

namespace App\Domain\Vacation\ViewModels;

use App\Domain\Vacation\CountrySlug;
use App\Domain\Vacation\VacationListingFilter;
use App\Domain\Vacation\VacationPillar;
use App\Models\Destination;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

final class VacationPillarIndexViewModel
{
    public function __construct(
        public readonly VacationPillar $pillar,
        public readonly VacationListingFilter $filter,
        public readonly LengthAwarePaginator $listings,
        public readonly Collection $cards,
        public readonly Collection $countries,
        public readonly Collection $speciesOptions,
        public readonly int $tripsTotal,
        public readonly int $campsTotal,
        public readonly Collection $faq,
        public readonly ?Destination $destination = null,
        public readonly array $mapMarkers = [],
    ) {}

    public function isCountryPage(): bool
    {
        return $this->destination !== null;
    }

    public function countryName(): ?string
    {
        return $this->destination ? translate($this->destination->name) : null;
    }

    public function pageTitle(): string
    {
        if ($this->isCountryPage()) {
            return __($this->pillar->countryTitleKey(), ['country' => $this->countryName()]);
        }

        return __($this->pillar->indexTitleKey());
    }

    public function headerSubtitle(): string
    {
        if ($this->isCountryPage()) {
            $subtitle = $this->destination->sub_title
                ?? __($this->pillar->descriptionKey());

            return strip_tags(translate($subtitle));
        }

        return __($this->pillar->descriptionKey());
    }

    public function metaDescription(): string
    {
        return Str::limit($this->headerSubtitle(), 155);
    }

    public function filterAction(): string
    {
        if ($this->isCountryPage()) {
            return route($this->pillar->showRouteName(), $this->destination->slug);
        }

        return route($this->pillar->indexRouteName());
    }

    public function emptyStateMessage(): string
    {
        $country = $this->countryName() ?? __('vacations.all_region');

        return __($this->pillar->emptyStateKey(), ['country' => $country]);
    }

    public function filterCountries(): Collection
    {
        return $this->isCountryPage() ? collect() : $this->countries;
    }

    /**
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

        if ($this->isCountryPage()) {
            $countrySlug = CountrySlug::canonicalize($this->destination->slug)
                ?? strtolower((string) $this->destination->slug);

            return [
                'all' => $withQuery(route('vacations.country', $countrySlug)),
                'trips' => $withQuery(route('vacations.trips.show', $countrySlug)),
                'camps' => $withQuery(route('vacations.camps.show', $countrySlug)),
            ];
        }

        return [
            'all' => $withQuery(route('vacations.all-offers')),
            'trips' => $withQuery(route('vacations.trips.index')),
            'camps' => $withQuery(route('vacations.camps.index')),
        ];
    }
}
