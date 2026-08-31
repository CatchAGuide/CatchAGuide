<?php

namespace Tests\Unit\Services\Maps;

use App\Services\Maps\LandmarkService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LandmarkServiceTest extends TestCase
{
    public function test_returns_empty_below_min_zoom(): void
    {
        config(['services.maps.landmarks.min_zoom' => 10]);

        $service = new LandmarkService();
        $result = $service->forBounds(48.1, 11.5, 48.2, 11.7, 8);

        $this->assertSame([], $result);
    }

    public function test_rejects_oversized_bounds(): void
    {
        config(['services.maps.landmarks.min_zoom' => 10]);

        $service = new LandmarkService();
        $result = $service->forBounds(40.0, 0.0, 55.0, 20.0, 11);

        $this->assertSame([], $result);
    }

    public function test_normalizes_overpass_payload_and_caches(): void
    {
        Cache::flush();
        config([
            'services.maps.landmarks.enabled' => true,
            'services.maps.landmarks.min_zoom' => 10,
            'services.maps.landmarks.max_features' => 30,
            'services.maps.landmarks.cache_ttl' => 60,
            'services.maps.landmarks.timeout' => 5,
            'services.maps.landmarks.overpass_url' => 'https://overpass.test/api/interpreter',
        ]);

        Http::fake([
            'overpass.test/*' => Http::response([
                'elements' => [
                    [
                        'type' => 'node',
                        'id' => 1,
                        'lat' => 48.1351,
                        'lon' => 11.582,
                        'tags' => [
                            'aeroway' => 'aerodrome',
                            'name' => 'Munich Airport',
                        ],
                    ],
                    [
                        'type' => 'node',
                        'id' => 2,
                        'lat' => 48.14,
                        'lon' => 11.58,
                        'tags' => [
                            'leisure' => 'park',
                            'name' => 'Englischer Garten',
                        ],
                    ],
                    [
                        'type' => 'node',
                        'id' => 3,
                        'lat' => 48.15,
                        'lon' => 11.59,
                        'tags' => [
                            'amenity' => 'cafe',
                            'name' => 'Should Skip',
                        ],
                    ],
                ],
            ], 200),
        ]);

        $service = new LandmarkService();
        $first = $service->forBounds(48.10, 11.50, 48.20, 11.70, 12);
        $second = $service->forBounds(48.10, 11.50, 48.20, 11.70, 12);

        $this->assertCount(2, $first);
        $this->assertSame('airport', $first[0]['category']);
        $this->assertSame('Munich Airport', $first[0]['name']);
        $this->assertSame('park', $first[1]['category']);
        $this->assertSame($first, $second);

        Http::assertSentCount(1);
    }

    public function test_fails_soft_when_overpass_errors(): void
    {
        Cache::flush();
        config([
            'services.maps.landmarks.enabled' => true,
            'services.maps.landmarks.min_zoom' => 10,
            'services.maps.landmarks.overpass_url' => 'https://overpass.test/api/interpreter',
            'services.maps.landmarks.timeout' => 2,
        ]);

        Http::fake([
            'overpass.test/*' => Http::response('fail', 500),
        ]);

        $service = new LandmarkService();
        $result = $service->forBounds(48.10, 11.50, 48.20, 11.70, 12);

        $this->assertSame([], $result);
    }
}
