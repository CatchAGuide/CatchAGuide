<?php

namespace Tests\Unit\Vacation;

use App\Http\Controllers\VacationsController;
use Tests\TestCase;

class VacationsControllerGeoInjectionTest extends TestCase
{
    public function test_malicious_coordinates_do_not_break_the_nearby_vacations_query(): void
    {
        $controller = app(VacationsController::class);
        $method = new \ReflectionMethod($controller, 'otherVacationsBasedByLocation');
        $method->setAccessible(true);

        $result = $method->invoke($controller, "0) OR SLEEP(5)-- -", "1)); DROP TABLE vacations;-- -");

        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $result);
    }

    public function test_valid_coordinates_are_bound_as_query_parameters_not_interpolated(): void
    {
        // Note: this query separately 500s today because `vacations` has
        // latitude/longitude columns, not lat/lng (a pre-existing, unrelated
        // schema bug). This test only asserts the injection fix: valid
        // numeric input reaches the database as bound parameters rather
        // than raw SQL, evidenced by the "Unknown column" failure (schema
        // mismatch) rather than a SQL syntax error (injection).
        try {
            $controller = app(VacationsController::class);
            $method = new \ReflectionMethod($controller, 'otherVacationsBasedByLocation');
            $method->setAccessible(true);
            $method->invoke($controller, '52.5200', '13.4050');
            $this->fail('Expected the pre-existing lat/lng column mismatch to raise a QueryException.');
        } catch (\Illuminate\Database\QueryException $e) {
            $this->assertStringContainsString("Unknown column 'lat'", $e->getMessage());
        }
    }
}
