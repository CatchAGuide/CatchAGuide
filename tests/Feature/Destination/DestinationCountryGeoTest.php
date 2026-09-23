<?php

namespace Tests\Feature\Destination;

use App\Domain\CategoryPage\CategoryPageEntityType;
use App\Domain\CategoryPage\CategoryPageScope;
use App\Models\CategoryEntity;
use App\Models\Language;
use App\Services\Homepage\HomepageMixedOfferSelector;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\URL;
use Mockery;
use Tests\TestCase;

class DestinationCountryGeoTest extends TestCase
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

    public function test_destination_country_shows_region_and_city_carousels_when_present(): void
    {
        $country = $this->createCountry('spanien-no-geo');
        $region = CategoryEntity::regions()->create([
            'type' => 'region',
            'country_id' => $country->id,
            'name' => 'Unique Dest Region '.$country->slug,
            'slug' => 'dest-region-'.$country->slug,
        ]);
        CategoryEntity::cities()->create([
            'type' => 'city',
            'country_id' => $country->id,
            'region_id' => $region->id,
            'name' => 'Unique Dest City '.$country->slug,
            'slug' => 'dest-city-'.$country->slug,
        ]);

        $this->bindDestinationOffers();

        $response = $this->get(route('destination.country', ['country' => $country->slug]));

        $response->assertOk();
        $response->assertSee(__('destination.all_region'), false);
        $response->assertSee(__('destination.all_cities'), false);
        $response->assertSee($region->name, false);
        $response->assertSee('Unique Dest City '.$country->slug, false);
        $response->assertSee('Fishing in Spain', false);
    }

    public function test_destination_region_url_renders_region_page(): void
    {
        $country = $this->createCountry('spanien-legacy-region');
        $region = CategoryEntity::regions()->create([
            'type' => 'region',
            'country_id' => $country->id,
            'name' => 'Catalonia',
            'slug' => 'catalonia-'.$country->slug,
        ]);

        $this->bindDestinationOffers();

        $response = $this->get('/destination/'.$country->slug.'/'.$region->slug);

        $response->assertOk();
        // No scoped CMS content was seeded for the region, so the view falls back to the
        // entity's plain name (CategoryEntity::getTitleAttribute()) — still real content,
        // not a redirect to the country hub.
        $response->assertSee('Catalonia', false);
    }

    public function test_destination_city_url_renders_city_page(): void
    {
        $country = $this->createCountry('spanien-legacy-city');
        $region = CategoryEntity::regions()->create([
            'type' => 'region',
            'country_id' => $country->id,
            'name' => 'Catalonia',
            'slug' => 'catalonia-'.$country->slug,
        ]);
        $city = CategoryEntity::cities()->create([
            'type' => 'city',
            'country_id' => $country->id,
            'region_id' => $region->id,
            'name' => 'Barcelona',
            'slug' => 'barcelona-'.$country->slug,
        ]);

        $this->bindDestinationOffers();

        $response = $this->get('/destination/'.$country->slug.'/'.$region->slug.'/'.$city->slug.'?type=tour');

        $response->assertOk();
        $response->assertSee('Barcelona', false);
    }

    public function test_destination_city_url_404s_when_city_does_not_belong_to_region(): void
    {
        $country = $this->createCountry('spanien-mismatch');
        $region = CategoryEntity::regions()->create([
            'type' => 'region',
            'country_id' => $country->id,
            'name' => 'Catalonia',
            'slug' => 'catalonia-'.$country->slug,
        ]);
        $otherRegion = CategoryEntity::regions()->create([
            'type' => 'region',
            'country_id' => $country->id,
            'name' => 'Andalusia',
            'slug' => 'andalusia-'.$country->slug,
        ]);
        $city = CategoryEntity::cities()->create([
            'type' => 'city',
            'country_id' => $country->id,
            'region_id' => $otherRegion->id,
            'name' => 'Seville',
            'slug' => 'seville-'.$country->slug,
        ]);

        $response = $this->get('/destination/'.$country->slug.'/'.$region->slug.'/'.$city->slug);

        $response->assertNotFound();
    }

    public function test_destination_country_redirects_uppercase_umlaut_slug_to_canonical(): void
    {
        $response = $this->get('/destination/'.rawurlencode('Österreich'));

        $response->assertRedirect(route('destination.country', ['country' => 'österreich']));
        $response->assertStatus(301);
    }

    public function test_destination_country_redirect_preserves_region_and_city(): void
    {
        $response = $this->get('/destination/'.rawurlencode('Österreich').'/tirol/innsbruck');

        $response->assertRedirect(route('destination.country', [
            'country' => 'österreich',
            'region' => 'tirol',
            'city' => 'innsbruck',
        ]));
    }

    public function test_destination_country_route_supports_optional_region_and_city(): void
    {
        $this->assertSame(
            'destination/{country}/{region?}/{city?}',
            app('router')->getRoutes()->getByName('destination.country')->uri()
        );
        $this->assertSame(
            'guidings/{country}/{region?}/{city?}',
            app('router')->getRoutes()->getByName('guidings.destination')->uri()
        );
    }

    private function createCountry(string $slugPrefix): CategoryEntity
    {
        $country = CategoryEntity::countries()->create([
            'type' => 'country',
            'name' => 'Spanien',
            'slug' => $slugPrefix.'-'.uniqid(),
            'countrycode' => 'ES',
            'filters' => [
                'place' => 'Spain',
                'placeLat' => '40.4',
                'placeLng' => '-3.7',
                'country' => 'Spain',
            ],
        ]);

        Language::query()->create([
            'source_id' => (string) $country->id,
            'type' => CategoryPageEntityType::GEO_COUNTRY,
            'scope' => CategoryPageScope::GLOBAL,
            'language' => app()->getLocale(),
            'title' => 'Fishing in Spain',
            'sub_title' => 'Discover Spanish waters',
            'introduction' => 'Intro text for Spain.',
            'content' => 'Body content for Spain.',
            'faq_title' => '',
        ]);

        return $country;
    }

    private function bindDestinationOffers(): void
    {
        $mock = Mockery::mock(HomepageMixedOfferSelector::class);
        $mock->shouldReceive('byModuleForDestination')->andReturn([
            'tour' => collect(),
            'camp' => collect(),
            'trip' => collect(),
        ]);
        $this->app->instance(HomepageMixedOfferSelector::class, $mock);
    }
}
