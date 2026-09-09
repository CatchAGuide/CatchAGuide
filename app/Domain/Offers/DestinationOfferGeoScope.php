<?php

namespace App\Domain\Offers;

use App\Domain\Vacation\CountrySlug;
use App\Models\CategoryEntity;
use Illuminate\Database\Eloquent\Builder;

/**
 * Constrains listing queries to a destination country, and optionally region/city.
 */
final class DestinationOfferGeoScope
{
    public static function apply(
        Builder $query,
        CategoryEntity $country,
        ?CategoryEntity $region = null,
        ?CategoryEntity $city = null,
        bool $includeCountryIso = false,
    ): Builder {
        self::constrainCountry($query, $country, $includeCountryIso);

        if ($city !== null) {
            self::constrainColumn($query, 'city', self::placeNames(
                $city->name,
                $city->filters['city'] ?? null,
                $city->filters['place'] ?? null,
            ));
        } elseif ($region !== null) {
            self::constrainColumn($query, 'region', self::placeNames(
                $region->name,
                $region->filters['region'] ?? null,
                $region->filters['place'] ?? null,
            ));
        }

        return $query;
    }

    private static function constrainCountry(Builder $query, CategoryEntity $country, bool $includeCountryIso): void
    {
        $variants = CountrySlug::storageVariants($country->slug, $country->countrycode);

        $query->where(function (Builder $q) use ($variants, $includeCountryIso) {
            foreach ($variants as $variant) {
                $lower = mb_strtolower($variant, 'UTF-8');
                $q->orWhereRaw('LOWER(country) = ?', [$lower]);
                if ($includeCountryIso) {
                    $q->orWhereRaw('LOWER(country_iso) = ?', [$lower]);
                }
            }
        });
    }

    /**
     * @param  list<string>  $names
     */
    private static function constrainColumn(Builder $query, string $column, array $names): void
    {
        if ($names === []) {
            return;
        }

        $query->where(function (Builder $q) use ($column, $names) {
            foreach ($names as $name) {
                $q->orWhereRaw('LOWER('.$column.') = ?', [mb_strtolower($name, 'UTF-8')]);
            }
        });
    }

    /**
     * @return list<string>
     */
    private static function placeNames(mixed ...$values): array
    {
        $names = [];
        foreach ($values as $value) {
            if (! is_string($value)) {
                continue;
            }
            $trimmed = trim($value);
            if ($trimmed === '') {
                continue;
            }
            $names[] = $trimmed;
        }

        return array_values(array_unique($names));
    }
}
