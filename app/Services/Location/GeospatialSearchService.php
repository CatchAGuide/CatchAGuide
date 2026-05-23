<?php

namespace App\Services\Location;

use App\Models\Guiding;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class GeospatialSearchService
{
    public function __construct(
        private readonly ?CountryResolver $countryResolver = null,
    ) {}
    public const SCOPE_CITY = 'city';

    public const SCOPE_REGION = 'region';

    public const SCOPE_COUNTRY = 'country';

    /**
     * @param  array<string, mixed>  $params
     * @return array{ids: Collection, scope: string, area_type: string, message_level: int}
     */
    public function search(array $params): array
    {
        $lat = $this->toFloat($params['placeLat'] ?? null);
        $lng = $this->toFloat($params['placeLng'] ?? null);

        if ($lat === null || $lng === null) {
            return [
                'ids' => collect(),
                'scope' => self::SCOPE_CITY,
                'area_type' => 'none',
                'message_level' => 3,
            ];
        }

        $placeTypes = $this->normalizePlaceTypes($params['place_types'] ?? null);
        $scope = $this->detectScope($placeTypes, $params);
        $scopeConfig = config("location_search.scopes.{$scope}", config('location_search.scopes.city'));
        $maxResults = (int) ($params['max_results'] ?? $scopeConfig['max_results'] ?? 100);
        $radiusOverride = isset($params['radius']) && is_numeric($params['radius'])
            ? (int) $params['radius']
            : null;

        $bounds = $this->normalizeBounds($params);
        $areaType = 'radius';
        $query = Guiding::query()->select('id')->publiclyVisible()
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->where('lat', '!=', 0)
            ->where('lng', '!=', 0);

        $this->applyCountryConstraint($query, $params);

        if ($bounds !== null) {
            $areaType = 'bbox';
            $query->whereBetween('lat', [$bounds['sw_lat'], $bounds['ne_lat']])
                ->whereBetween('lng', [$bounds['sw_lng'], $bounds['ne_lng']]);
        } else {
            $radiusKm = $radiusOverride ?? (int) ($scopeConfig['radius_fallback_km'] ?? 20);
            $query->whereRaw(
                'ST_Distance_Sphere(point(lng, lat), point(?, ?)) <= ?',
                [$lng, $lat, $radiusKm * 1000]
            );
        }

        $ids = $query
            ->selectRaw(
                'id, ST_Distance_Sphere(point(lng, lat), point(?, ?)) as distance',
                [$lng, $lat]
            )
            ->orderByRaw('CASE WHEN distance IS NULL THEN 1 ELSE 0 END')
            ->orderBy('distance')
            ->limit($maxResults)
            ->pluck('id');

        $messageLevel = $ids->isNotEmpty() ? 1 : 2;

        if ($ids->isEmpty()) {
            $ids = $this->nearestGuidings(
                $lat,
                $lng,
                (int) config('location_search.nearest_fallback_limit', 50),
                $this->resolveSearchCountryIso($params)
            );
            $messageLevel = 3;
        }

        return [
            'ids' => $ids,
            'scope' => $scope,
            'area_type' => $areaType,
            'message_level' => $messageLevel,
        ];
    }

    public function detectScope(array $placeTypes, array $params): string
    {
        if (in_array('country', $placeTypes, true)) {
            return self::SCOPE_COUNTRY;
        }

        $city = trim((string) ($params['city'] ?? ''));
        $country = trim((string) ($params['country'] ?? ''));

        if ($city !== '' && $country !== '' && strcasecmp($city, $country) === 0) {
            return self::SCOPE_COUNTRY;
        }

        if (in_array('administrative_area_level_1', $placeTypes, true)) {
            return self::SCOPE_REGION;
        }

        $region = trim((string) ($params['region'] ?? ''));
        if ($city !== '' && $region !== '' && strcasecmp($city, $region) === 0) {
            return self::SCOPE_REGION;
        }

        return self::SCOPE_CITY;
    }

    /**
     * @return array{ne_lat: float, ne_lng: float, sw_lat: float, sw_lng: float}|null
     */
    public function normalizeBounds(array $params): ?array
    {
        $neLat = $this->toFloat($params['bounds_ne_lat'] ?? null);
        $neLng = $this->toFloat($params['bounds_ne_lng'] ?? null);
        $swLat = $this->toFloat($params['bounds_sw_lat'] ?? null);
        $swLng = $this->toFloat($params['bounds_sw_lng'] ?? null);

        if ($neLat === null || $neLng === null || $swLat === null || $swLng === null) {
            return null;
        }

        return [
            'ne_lat' => max($neLat, $swLat),
            'ne_lng' => max($neLng, $swLng),
            'sw_lat' => min($neLat, $swLat),
            'sw_lng' => min($neLng, $swLng),
        ];
    }

    /**
     * @param  array<int, string>|string|null  $placeTypes
     * @return array<int, string>
     */
    public function normalizePlaceTypes(array|string|null $placeTypes): array
    {
        if (is_string($placeTypes)) {
            $decoded = json_decode($placeTypes, true);

            return is_array($decoded) ? array_values($decoded) : [];
        }

        return is_array($placeTypes) ? array_values($placeTypes) : [];
    }

    /**
     * ISO code for the searched place, when enforce_search_country is enabled.
     */
    public function resolveSearchCountryIso(array $params): ?string
    {
        if (! $this->shouldEnforceSearchCountry()) {
            return null;
        }

        return $this->resolver()->resolveIso(
            isset($params['country_short']) ? (string) $params['country_short'] : null,
            isset($params['country']) ? (string) $params['country'] : null,
        );
    }

    /**
     * @param  Builder<Guiding>  $query
     */
    public function applyCountryConstraint(Builder $query, array $params): void
    {
        $iso = $this->resolveSearchCountryIso($params);
        if ($iso === null) {
            return;
        }

        $names = $this->resolver()->localizedNames($iso);

        $query->where(function (Builder $q) use ($iso, $names) {
            $q->where('country_iso', $iso);

            if ($names !== []) {
                $q->orWhere(function (Builder $legacy) use ($names) {
                    $legacy->where(function (Builder $missingIso) {
                        $missingIso->whereNull('country_iso')->orWhere('country_iso', '');
                    })->whereIn('country', $names);
                });
            }
        });
    }

    private function resolver(): CountryResolver
    {
        return $this->countryResolver ?? app(CountryResolver::class);
    }

    private function shouldEnforceSearchCountry(): bool
    {
        if (! function_exists('app')) {
            return true;
        }

        try {
            return (bool) app('config')->get('location_search.enforce_search_country', true);
        } catch (\Throwable) {
            return true;
        }
    }

    private function nearestGuidings(float $lat, float $lng, int $limit, ?string $countryIso = null): Collection
    {
        try {
            $query = Guiding::query()
                ->select('id')
                ->selectRaw(
                    'ST_Distance_Sphere(point(lng, lat), point(?, ?)) as distance',
                    [$lng, $lat]
                )
                ->publiclyVisible()
                ->whereNotNull('lat')
                ->whereNotNull('lng')
                ->where('lat', '!=', 0)
                ->where('lng', '!=', 0);

            if ($countryIso !== null) {
                $this->applyCountryConstraint($query, [
                    'country_short' => $countryIso,
                    'country' => $this->resolver()->englishName($countryIso),
                ]);
            }

            return $query
                ->orderByRaw('CASE WHEN distance IS NULL THEN 1 ELSE 0 END')
                ->orderBy('distance')
                ->limit($limit)
                ->pluck('id');
        } catch (\Throwable) {
            return Guiding::query()
                ->publiclyVisible()
                ->orderByDesc('created_at')
                ->limit(min($limit, 20))
                ->pluck('id');
        }
    }

    private function toFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_numeric($value)) {
            return null;
        }

        $float = (float) $value;

        return is_finite($float) ? $float : null;
    }
}
