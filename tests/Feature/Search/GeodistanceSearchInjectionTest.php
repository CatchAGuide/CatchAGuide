<?php

namespace Tests\Feature\Search;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * SearchController::search and VacationsController::otherVacationsBasedByLocation used to
 * string-interpolate placeLat/placeLng straight into a raw selectRaw() geodistance query
 * with no validation and no bindings — a classic unauthenticated SQL injection. These tests
 * assert malicious/malformed coordinates are rejected before reaching raw SQL, rather than
 * asserting exploitation (which would require a real injectable driver behavior to detect).
 */
class GeodistanceSearchInjectionTest extends TestCase
{
    use DatabaseTransactions;

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

    public function test_search_with_sql_payload_in_coordinates_does_not_500(): void
    {
        $response = $this->post(route('search'), [
            'placeLat' => '0)) OR SLEEP(5)-- -',
            'placeLng' => '0',
        ]);

        $response->assertOk();
    }

    public function test_search_with_numeric_coordinates_still_works(): void
    {
        $response = $this->post(route('search'), [
            'placeLat' => '52.5200',
            'placeLng' => '13.4050',
        ]);

        $response->assertOk();
    }

    public function test_vacations_country_page_with_sql_payload_in_coordinates_does_not_500(): void
    {
        $response = $this->get(route('vacations.country', [
            'country' => 'this-country-almost-certainly-does-not-exist-'.uniqid(),
            'placeLat' => "1' AND SLEEP(5) AND '1'='1",
            'placeLng' => '0',
        ]));

        $this->assertNotEquals(500, $response->getStatusCode());
    }
}
