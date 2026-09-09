<?php

namespace Tests\Feature\Search;

use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class SearchControllerInjectionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://localhost']);
        URL::forceRootUrl('http://localhost');

        $this->withoutMiddleware([
            \Illuminate\Routing\Middleware\ThrottleRequests::class,
            \App\Http\Middleware\DDoSProtectionMiddleware::class,
        ]);
    }

    public function test_malicious_place_lat_does_not_break_the_distance_query(): void
    {
        $response = $this->post('/search', [
            'placeLat' => "0) OR SLEEP(5)-- -",
            'placeLng' => '13.4050',
        ]);

        $response->assertOk();
    }

    public function test_malicious_place_lng_does_not_break_the_distance_query(): void
    {
        $response = $this->post('/search', [
            'placeLat' => '52.5200',
            'placeLng' => "1)); DROP TABLE guidings;-- -",
        ]);

        $response->assertOk();
    }

    public function test_valid_coordinates_still_produce_a_parameterised_distance_query(): void
    {
        $response = $this->post('/search', [
            'placeLat' => '52.5200',
            'placeLng' => '13.4050',
            'radius' => '250',
        ]);

        $response->assertOk();
    }
}
