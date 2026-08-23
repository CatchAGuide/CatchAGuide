<?php

namespace App\Domain\Vacation\ViewModels;

use App\Domain\Vacation\CountrySlug;
use App\Domain\Vacation\VacationListingFilter;
use App\Domain\Vacation\VacationPillar;
use App\Models\CategoryEntity;
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
        public readonly Collection $accommodationTypeOptions,
        public readonly int $tripsTotal,
        public readonly int $campsTotal,
        public readonly Collection $faq,
        public readonly ?CategoryEntity $destination = null,
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
            $cmsTitle = $this->cmsField('title');
            if (filled($cmsTitle)) {
                return strip_tags($cmsTitle);
            }

            return __($this->pillar->countryTitleKey(), ['country' => $this->countryName()]);
        }

        return __($this->pillar->indexTitleKey());
    }

    public function headerSubtitle(): string
    {
        if ($this->isCountryPage()) {
            $subtitle = $this->cmsField('sub_title');
            if (filled($subtitle)) {
                return strip_tags($subtitle);
            }

            return __($this->pillar->descriptionKey());
        }

        return __($this->pillar->descriptionKey());
    }

    public function introductionHtml(): string
    {
        if (! $this->isCountryPage()) {
            return '';
        }

        return (string) ($this->cmsField('introduction') ?? '');
    }

    public function bodyContentHtml(): string
    {
        if (! $this->isCountryPage()) {
            return '';
        }

        return (string) ($this->cmsField('content') ?? '');
    }

    public function faqTitle(): string
    {
        $cmsFaqTitle = $this->isCountryPage() ? $this->cmsField('faq_title') : null;
        if (filled($cmsFaqTitle)) {
            return strip_tags($cmsFaqTitle);
        }

        return __('vacations.hub_faq_title');
    }

    public function metaDescription(): string
    {
        $intro = strip_tags($this->introductionHtml());

        return Str::limit($intro !== '' ? $intro : $this->headerSubtitle(), 155);
    }

    private function cmsField(string $field): ?string
    {
        if ($this->destination === null) {
            return null;
        }

        if (method_exists($this->destination, 'scopedCmsValue')) {
            return $this->destination->scopedCmsValue($field);
        }

        $value = $this->destination->{$field} ?? null;

        return filled($value) ? (string) $value : null;
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

        if ($this->isCountryPage()) {
            $countrySlug = CountrySlug::canonicalize($this->destination->slug)
                ?? strtolower((string) $this->destination->slug);

            return [
                'all' => $withQuery(route('vacations.country', $countrySlug), $query),
                'trips' => $withQuery(route('vacations.trips.show', $countrySlug), $tripsQuery),
                'camps' => $withQuery(route('vacations.camps.show', $countrySlug), $campsQuery),
            ];
        }

        return [
            'all' => $withQuery(route('vacations.all-offers'), $query),
            'trips' => $withQuery(route('vacations.trips.index'), $tripsQuery),
            'camps' => $withQuery(route('vacations.camps.index'), $campsQuery),
        ];
    }
}
