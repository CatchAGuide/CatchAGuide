<?php

namespace App\Domain\Vacation\ViewModels;

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
        public readonly ?Destination $destination = null,
        public readonly array $mapMarkers = [],
        public readonly Collection $faq,
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
}
