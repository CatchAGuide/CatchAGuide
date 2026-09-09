<?php

namespace App\Services\Maps;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LandmarkService
{
    /**
     * Fetch sparse OSM landmarks for a map viewport (cached, fail-soft).
     *
     * @return array<int, array{id:string,name:string,lat:float,lng:float,category:string}>
     */
    public function forBounds(
        float $swLat,
        float $swLng,
        float $neLat,
        float $neLng,
        int $zoom
    ): array {
        $config = config('services.maps.landmarks', []);

        if (! ($config['enabled'] ?? true)) {
            return [];
        }

        $minZoom = (int) ($config['min_zoom'] ?? 10);
        if ($zoom < $minZoom) {
            return [];
        }

        if (! $this->isValidBounds($swLat, $swLng, $neLat, $neLng)) {
            return [];
        }

        $rounded = $this->roundBounds($swLat, $swLng, $neLat, $neLng, $zoom);
        $cacheKey = 'maps.landmarks.'.md5(implode('|', $rounded).'|'.$zoom);
        $ttl = (int) ($config['cache_ttl'] ?? 3600);

        return Cache::remember($cacheKey, $ttl, function () use ($rounded, $config) {
            return $this->fetchFromOverpass(
                $rounded['sw_lat'],
                $rounded['sw_lng'],
                $rounded['ne_lat'],
                $rounded['ne_lng'],
                $config
            );
        });
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<int, array{id:string,name:string,lat:float,lng:float,category:string}>
     */
    protected function fetchFromOverpass(
        float $swLat,
        float $swLng,
        float $neLat,
        float $neLng,
        array $config
    ): array {
        $max = (int) ($config['max_features'] ?? 30);
        $timeout = (int) ($config['timeout'] ?? 8);
        $url = (string) ($config['overpass_url'] ?? 'https://overpass-api.de/api/interpreter');

        $bbox = sprintf('%F,%F,%F,%F', $swLat, $swLng, $neLat, $neLng);
        $query = <<<OVERPASS
[out:json][timeout:{$timeout}];
(
  node["aeroway"="aerodrome"]({$bbox});
  node["harbour"="yes"]({$bbox});
  node["leisure"="marina"]({$bbox});
  node["leisure"="park"]["name"]({$bbox});
  node["tourism"="attraction"]["name"]({$bbox});
  node["place"~"city|town"]["name"]({$bbox});
);
out body {$max};
OVERPASS;

        try {
            $response = Http::timeout($timeout)
                ->asForm()
                ->post($url, ['data' => $query]);

            if (! $response->successful()) {
                return [];
            }

            $elements = $response->json('elements') ?? [];
            if (! is_array($elements)) {
                return [];
            }

            return $this->normalizeElements($elements, $max);
        } catch (\Throwable $e) {
            Log::debug('LandmarkService Overpass failed', ['message' => $e->getMessage()]);

            return [];
        }
    }

    /**
     * @param  array<int, mixed>  $elements
     * @return array<int, array{id:string,name:string,lat:float,lng:float,category:string}>
     */
    protected function normalizeElements(array $elements, int $max): array
    {
        $out = [];

        foreach ($elements as $el) {
            if (! is_array($el)) {
                continue;
            }

            $lat = isset($el['lat']) ? (float) $el['lat'] : null;
            $lng = isset($el['lon']) ? (float) $el['lon'] : null;
            if ($lat === null || $lng === null) {
                continue;
            }

            $tags = is_array($el['tags'] ?? null) ? $el['tags'] : [];
            $category = $this->resolveCategory($tags);
            if ($category === null) {
                continue;
            }

            $name = (string) ($tags['name'] ?? $tags['name:en'] ?? '');
            if ($name === '' && $category !== 'airport' && $category !== 'harbour') {
                continue;
            }

            $out[] = [
                'id' => (string) ($el['type'] ?? 'n').'/'.(string) ($el['id'] ?? count($out)),
                'name' => $name !== '' ? $name : $this->fallbackName($category),
                'lat' => $lat,
                'lng' => $lng,
                'category' => $category,
            ];

            if (count($out) >= $max) {
                break;
            }
        }

        return $out;
    }

    /**
     * @param  array<string, mixed>  $tags
     */
    protected function resolveCategory(array $tags): ?string
    {
        if (($tags['aeroway'] ?? null) === 'aerodrome') {
            return 'airport';
        }
        if (($tags['harbour'] ?? null) === 'yes' || ($tags['leisure'] ?? null) === 'marina') {
            return 'harbour';
        }
        if (($tags['leisure'] ?? null) === 'park') {
            return 'park';
        }
        if (($tags['tourism'] ?? null) === 'attraction') {
            return 'attraction';
        }
        $place = $tags['place'] ?? null;
        if ($place === 'city' || $place === 'town') {
            return 'town';
        }

        return null;
    }

    protected function fallbackName(string $category): string
    {
        return match ($category) {
            'airport' => __('destination.landmark_airport'),
            'harbour' => __('destination.landmark_harbour'),
            'park' => __('destination.landmark_park'),
            'town' => __('destination.landmark_town'),
            default => __('destination.landmark_attraction'),
        };
    }

    protected function isValidBounds(float $swLat, float $swLng, float $neLat, float $neLng): bool
    {
        if ($swLat < -90 || $swLat > 90 || $neLat < -90 || $neLat > 90) {
            return false;
        }
        if ($swLng < -180 || $swLng > 180 || $neLng < -180 || $neLng > 180) {
            return false;
        }
        if ($swLat >= $neLat) {
            return false;
        }

        // Reject oversized boxes (whole continents)
        if (($neLat - $swLat) > 8 || abs($neLng - $swLng) > 12) {
            return false;
        }

        return true;
    }

    /**
     * @return array{sw_lat:float,sw_lng:float,ne_lat:float,ne_lng:float}
     */
    protected function roundBounds(float $swLat, float $swLng, float $neLat, float $neLng, int $zoom): array
    {
        $precision = $zoom >= 13 ? 2 : 1;

        return [
            'sw_lat' => round($swLat, $precision),
            'sw_lng' => round($swLng, $precision),
            'ne_lat' => round($neLat, $precision),
            'ne_lng' => round($neLng, $precision),
        ];
    }
}
