<?php

namespace Tests\Feature\Seo;

use App\Models\Camp;
use App\Models\User;
use App\Services\Catalog\TripCatalogService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Old URLs still linked from articles or external sites must 301 in one hop to their canonical
 * page instead of 404ing (found by the crawl simulation).
 */
class LegacyRedirectsTest extends TestCase
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
            ->assertRedirect('/contact');

        $this->get('/category/target-fish/Atlantischer-Lachs')
            ->assertStatus(301)
            ->assertRedirect(route('targets.show', ['slug' => 'atlantischer-lachs']));
    }

    public function test_retired_search_request_page_redirects_to_the_contact_form(): void
    {
        $this->get('/searchrequest')->assertStatus(301)->assertRedirect('/contact');
        $this->get('/booking-request/thank-you')->assertNotFound();
    }

    public function test_old_camp_id_url_redirects_and_is_no_longer_generated(): void
    {
        $user = User::query()->first();
        if (! $user) {
            $this->markTestSkipped('No user available to own a test camp.');
        }

        $camp = Camp::query()->create([
            'title' => 'Legacy Id Camp',
            'slug' => 'legacy-id-camp-'.uniqid(),
            'description_camp' => 'Camp description',
            'description_area' => 'Area description',
            'description_fishing' => 'Fishing description',
            'location' => 'Test Location',
            'city' => 'Riba-Roja',
            'region' => 'Catalonia',
            'country' => 'Spain',
            'status' => 'active',
            'user_id' => $user->id,
        ]);

        $this->get('/vacations-v2/'.$camp->id)
            ->assertStatus(301)
            ->assertRedirect(route('vacations.camps.show', ['slug' => $camp->slug]));

        // The booking assistant's camp catalog links the camp page directly, not the old id URL.
        Cache::forget('catalog_camps_'.app()->getLocale());
        $urls = array_column(app(TripCatalogService::class)->getCampTrips(), 'url');

        $this->assertContains(route('vacations.camps.show', ['slug' => $camp->slug]), $urls);
        $this->assertEmpty(preg_grep('#/vacations-v2/#', $urls));
    }

    public function test_all_countries_redirects_to_the_destination_hub(): void
    {
        $this->get('/all-countries')->assertStatus(301)->assertRedirect('/destination');
    }
}
