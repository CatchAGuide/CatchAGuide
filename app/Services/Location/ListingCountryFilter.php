<?php

namespace App\Services\Location;

use App\Models\CategoryEntity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ListingCountryFilter
{
    public function __construct(private readonly CountryResolver $countryResolver) {}

    /**
     * Build all country string variants that should match a country page.
     *
     * @param  array<string, mixed>  $filterData
     * @return array<int, string>
     */
    public function valuesForCountry(
        CategoryEntity $country,
        string $countrySlug,
        array $filterData = []
    ): array {
        $baseValues = array_values(array_unique(array_filter([
            $countrySlug,
            $country->name,
            $filterData['country'] ?? null,
        ], fn ($value) => is_string($value) && $value !== '')));

        $iso = $this->countryResolver->resolveIso(
            $country->countrycode ?? ($filterData['country_short'] ?? null),
            $filterData['country'] ?? $country->name ?? $countrySlug
        );

        foreach ($baseValues as $value) {
            $iso = $iso ?? $this->countryResolver->resolveIso(null, $value);
        }

        if ($iso) {
            $baseValues = array_merge($baseValues, $this->countryResolver->localizedNames($iso));
            $english = $this->countryResolver->englishName($iso);
            if ($english) {
                $baseValues[] = $english;
            }
        }

        return array_values(array_unique(array_filter(
            $baseValues,
            fn ($value) => is_string($value) && $value !== ''
        )));
    }

    /**
     * @param  array<int, string>  $countryFilterValues
     */
    public function applyToQuery(Builder $query, array $countryFilterValues, string $column = 'country'): void
    {
        if ($countryFilterValues === []) {
            return;
        }

        $query->whereIn($column, $countryFilterValues);
    }
}
