<?php

namespace Tests\Feature\Destination;

use App\Domain\CategoryPage\CategoryPageEntityType;
use App\Domain\CategoryPage\CategoryPageScope;
use App\Models\Country;
use App\Models\CountryTranslation;
use App\Models\Language;
use App\Services\Homepage\HomepageMixedOfferSelector;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\URL;
use Mockery;
use Tests\TestCase;

class DestinationOffersCatalogTest extends TestCase
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

    public function test_destination_country_renders_popular_offers_rail_instead_of_catalog(): void
    {
        $country = $this->createCountry('spanien-offers-rail');

        $this->bindDestinationOffers([
            'tour' => collect([$this->card('tour', 'Spain Tour Rail')]),
            'trip' => collect([$this->card('trip', 'Spain Trip Rail')]),
            'camp' => collect([$this->card('camp', 'Spain Camp Rail')]),
        ]);

        $response = $this->get(route('destination.country', ['country' => $country->slug]));

        $response->assertOk();
        $response->assertSee(__('destination.popular_title', ['place' => $country->name]), false);
        $response->assertSee('data-dest-offers', false);
        $response->assertSee('data-offer-rail="tour"', false);
        $response->assertSee('data-offer-rail="trip"', false);
        $response->assertSee('data-offer-rail="camp"', false);
        $response->assertSee('Spain Tour Rail', false);
        $response->assertSee('Spain Trip Rail', false);
        $response->assertSee('Spain Camp Rail', false);
        $response->assertSee(route('guidings.destination', ['country' => $country->slug], false), false);
        $response->assertDontSee('data-offers-type-filter', false);
        $response->assertDontSee('offers-catalog-page', false);
    }

    public function test_destination_country_shows_empty_state_when_no_local_offers(): void
    {
        $country = $this->createCountry('spanien-offers-empty');

        $this->bindDestinationOffers();

        $response = $this->get(route('destination.country', ['country' => $country->slug]));

        $response->assertOk();
        $response->assertSee(__('destination.popular_title', ['place' => $country->name]), false);
        $response->assertSee(__('destination.popular_empty', ['place' => $country->name]), false);
        $response->assertSee('cag-dest-offers--empty', false);
        $response->assertDontSee('data-dest-offers', false);
        $response->assertDontSee('data-offers-type-filter', false);
    }

    public function test_destination_page_uses_global_content_not_tours(): void
    {
        $country = $this->createCountry('spanien-global-only');

        Language::query()->create([
            'source_id' => (string) $country->id,
            'type' => CategoryPageEntityType::GEO_COUNTRY,
            'scope' => CategoryPageScope::TOURS,
            'language' => app()->getLocale(),
            'title' => 'Tours Only Spain Title',
            'sub_title' => 'Tours subtitle',
            'introduction' => 'Tours introduction',
            'content' => 'Tours body',
            'faq_title' => '',
        ]);

        Language::query()->create([
            'source_id' => (string) $country->id,
            'type' => CategoryPageEntityType::GEO_COUNTRY,
            'scope' => CategoryPageScope::GLOBAL,
            'language' => app()->getLocale(),
            'title' => 'Global Spain Destination Title',
            'sub_title' => 'Global subtitle',
            'introduction' => 'Global introduction',
            'content' => 'Global body',
            'faq_title' => '',
        ]);

        $this->bindDestinationOffers();

        $response = $this->get(route('destination.country', ['country' => $country->slug]));

        $response->assertOk();
        $response->assertSee('Global Spain Destination Title', false);
        $response->assertDontSee('Tours Only Spain Title', false);
    }

    private function createCountry(string $slugPrefix, array $filters = []): Country
    {
        $country = Country::query()->create([
            'name' => 'Spanien',
            'slug' => $slugPrefix.'-'.uniqid(),
            'countrycode' => 'ES',
            'filters' => $filters !== [] ? $filters : [
                'place' => 'Spain',
                'placeLat' => '40.4',
                'placeLng' => '-3.7',
                'country' => 'Spain',
            ],
        ]);

        CountryTranslation::query()->create([
            'country_id' => $country->id,
            'language' => app()->getLocale(),
            'title' => 'Fishing in Spain',
            'sub_title' => 'Discover Spanish waters',
            'introduction' => 'Intro text for Spain.',
            'content' => 'Body content for Spain.',
        ]);

        return $country->fresh(['translations']);
    }

    /**
     * @param  array{tour?: \Illuminate\Support\Collection, camp?: \Illuminate\Support\Collection, trip?: \Illuminate\Support\Collection}  $modules
     */
    private function bindDestinationOffers(array $modules = []): void
    {
        $mock = Mockery::mock(HomepageMixedOfferSelector::class);
        $mock->shouldReceive('byModuleForDestination')->andReturn([
            'tour' => $modules['tour'] ?? collect(),
            'camp' => $modules['camp'] ?? collect(),
            'trip' => $modules['trip'] ?? collect(),
        ]);
        $this->app->instance(HomepageMixedOfferSelector::class, $mock);
    }

    /**
     * @return array<string, mixed>
     */
    private function card(string $type, string $title): array
    {
        return [
            'type' => $type,
            'id' => crc32($title),
            'title' => $title,
            'url' => '/offers/'.$type,
            'image' => '/images/placeholder_guide.jpg',
            'gallery_images' => ['/images/placeholder_guide.jpg'],
            'badge' => ucfirst($type === 'tour' ? 'Tour' : $type),
            'location' => 'Spain',
            'price_amount' => '€100',
            'price_unit' => 'person',
        ];
    }
}
