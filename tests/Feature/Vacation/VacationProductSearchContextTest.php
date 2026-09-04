<?php

namespace Tests\Feature\Vacation;

use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class VacationProductSearchContextTest extends TestCase
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

    public function test_trip_product_page_keeps_search_country_and_preselects_guests(): void
    {
        $trip = $this->createPublishedTrip();

        $response = $this->get(route('vacations.trips.show', [
            'slug' => $trip->slug,
            'country' => 'spain',
            'num_guests' => '3',
        ]));

        $response->assertOk();
        $html = $response->getContent();

        $this->assertStringContainsString('name="num_guests"', $html);
        $this->assertMatchesRegularExpression(
            '/name="num_guests"[^>]*value="3"|value="3"[^>]*name="num_guests"/',
            $html
        );
        $this->assertMatchesRegularExpression(
            '/id="trip_number_of_persons"[^>]*value="3"|value="3"[^>]*id="trip_number_of_persons"/',
            $html
        );
    }

    private function createPublishedTrip(): Trip
    {
        $user = User::factory()->create();

        return Trip::query()->create([
            'title' => 'Search Context Trip '.uniqid(),
            'slug' => 'search-context-trip-'.uniqid(),
            'location' => 'Valencia',
            'country' => 'spain',
            'status' => 'active',
            'user_id' => $user->id,
            'group_size_min' => 1,
            'group_size_max' => 6,
            'price_per_person' => 1500,
            'currency' => 'EUR',
        ]);
    }
}
