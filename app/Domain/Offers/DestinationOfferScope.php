<?php

namespace App\Domain\Offers;

use App\Domain\Vacation\CountrySlug;
use App\Models\City;
use App\Models\Country;
use App\Models\Region;

/**
 * Seeds / locks offer-catalog geo params from a destination country → region → city page.
 */
final class DestinationOfferScope
{
    /**
     * Merge destination context into offer listing request input.
     * Destination geo/country always win so filters cannot leave the page scope.
     *
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public static function mergeIntoRequest(
        array $input,
        Country $country,
        ?Region $region = null,
        ?City $city = null,
    ): array {
        $row = $city ?? $region ?? $country;
        $filters = self::normalizeFilters($row->filters ?? null);

        $input['country'] = CountrySlug::canonicalize($country->slug);
        $input['country_short'] = self::stringOrNull(
            $filters['country_short'] ?? $country->countrycode ?? null
        );

        $placeLat = self::floatOrNull($filters['placeLat'] ?? $filters['place_lat'] ?? null);
        $placeLng = self::floatOrNull($filters['placeLng'] ?? $filters['place_lng'] ?? null);
        if ($placeLat !== null) {
            $input['placeLat'] = $placeLat;
        }
        if ($placeLng !== null) {
            $input['placeLng'] = $placeLng;
        }

        foreach (['bounds_ne_lat', 'bounds_ne_lng', 'bounds_sw_lat', 'bounds_sw_lng'] as $boundKey) {
            $bound = self::floatOrNull($filters[$boundKey] ?? null);
            if ($bound !== null) {
                $input[$boundKey] = $bound;
            }
        }

        if ($city !== null) {
            $input['city'] = self::stringOrNull($filters['city'] ?? $city->name);
            $input['region'] = self::stringOrNull(
                $filters['region'] ?? $region?->name
            );
            $input['place'] = self::stringOrNull(
                $filters['place'] ?? $city->name
            );
            $input['place_types'] = self::placeTypesOrDefault(
                $filters['place_types'] ?? null,
                ['locality']
            );
        } elseif ($region !== null) {
            unset($input['city']);
            $input['region'] = self::stringOrNull($filters['region'] ?? $region->name);
            $input['place'] = self::stringOrNull(
                $filters['place'] ?? $region->name
            );
            $input['place_types'] = self::placeTypesOrDefault(
                $filters['place_types'] ?? null,
                ['administrative_area_level_1']
            );
        } else {
            unset($input['city'], $input['region']);
            $input['place'] = self::stringOrNull(
                $filters['place'] ?? $country->name
            );
            $input['place_types'] = self::placeTypesOrDefault(
                $filters['place_types'] ?? null,
                ['country']
            );
        }

        return $input;
    }

    /**
     * @return array<string, mixed>
     */
    private static function normalizeFilters(mixed $raw): array
    {
        if (is_array($raw)) {
            return $raw;
        }

        if (! is_string($raw) || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        if (is_string($decoded)) {
            $again = json_decode($decoded, true);
            if (is_array($again)) {
                return $again;
            }
        }

        return [];
    }

    /**
     * @param  list<string>  $default
     * @return list<string>|string
     */
    private static function placeTypesOrDefault(mixed $placeTypes, array $default): array|string
    {
        if (is_string($placeTypes) && $placeTypes !== '') {
            return $placeTypes;
        }

        if (is_array($placeTypes) && $placeTypes !== []) {
            return array_values(array_filter($placeTypes, 'is_string'));
        }

        return $default;
    }

    private static function stringOrNull(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (string) $value;
    }

    private static function floatOrNull(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }
}
