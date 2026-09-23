<?php

namespace Tests\Feature\Seo;

use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Old URLs still linked from articles or external sites must 301 in one hop to their canonical
 * page instead of 404ing (found by the crawl simulation).
 */
class LegacyRedirectsTest extends TestCase
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

    public function test_legacy_vacation_location_urls_redirect_to_the_canonical_country_page(): void
    {
        $this->get('/vacations/location/Schweden?country=Schweden')
            ->assertStatus(301)
            ->assertRedirect(route('vacations.country', ['country' => 'schweden']));

        $this->get('/vacations/location/%C3%96sterreich')
            ->assertStatus(301)
            ->assertRedirect(route('vacations.country', ['country' => 'österreich']));
    }

    public function test_legacy_booking_request_and_species_urls_redirect(): void
    {
        $this->get('/guidings/bookingrequest?guiding=12')
            ->assertStatus(301)
            ->assertRedirect(route('guidings.request', ['guiding' => 12]));

        $this->get('/category/target-fish/Atlantischer-Lachs')
            ->assertStatus(301)
            ->assertRedirect(route('targets.show', ['slug' => 'atlantischer-lachs']));
    }

    public function test_all_countries_redirects_to_the_destination_hub(): void
    {
        $this->get('/all-countries')->assertStatus(301)->assertRedirect('/destination');
    }
}
