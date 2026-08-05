<?php

namespace Tests\Feature;

use App\Services\Homepage\HomepageLandingService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Mockery;
use Tests\TestCase;

class HomepageLandingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://localhost']);
        URL::forceRootUrl('http://localhost');

        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);

        Cache::flush();

        $landing = Mockery::mock(HomepageLandingService::class);
        $landing->shouldReceive('build')->andReturn([
            'featuredCountries' => collect([
                [
                    'slug' => 'deutschland',
                    'name' => 'Germany',
                    'thumbnail' => '/assets/images/300x300.png',
                    'countrycode' => 'DE',
                    'from_price' => 90,
                    'from_price_label' => 'from €90 / Person',
                ],
                [
                    'slug' => 'schweden',
                    'name' => 'Sweden',
                    'thumbnail' => '/assets/images/300x300.png',
                    'countrycode' => 'SE',
                    'from_price' => 120,
                    'from_price_label' => 'from €120 / Person',
                ],
            ]),
            'countryCount' => 12,
            'mixedOffers' => collect([
                [
                    'type' => 'tour',
                    'id' => 1,
                    'title' => 'Test Day Tour',
                    'url' => '/guidings/1/test-day-tour',
                    'image' => '/images/placeholder_guide.jpg',
                    'gallery_images' => ['/images/placeholder_guide.jpg'],
                    'badge' => 'Tour',
                    'location' => 'Berlin',
                    'price_amount' => '€120',
                    'price_unit' => 'person',
                ],
                [
                    'type' => 'trip',
                    'id' => 2,
                    'title' => 'Test Trip',
                    'url' => '/vacations/trips/test-trip',
                    'image' => '/images/placeholder_guide.jpg',
                    'gallery_images' => ['/images/placeholder_guide.jpg'],
                    'badge' => 'Trip',
                    'location' => 'Sweden',
                    'price_amount' => '€450',
                    'price_unit' => 'person',
                ],
                [
                    'type' => 'camp',
                    'id' => 3,
                    'title' => 'Test Camp',
                    'url' => '/vacations/camps/test-camp',
                    'image' => '/images/placeholder_guide.jpg',
                    'gallery_images' => ['/images/placeholder_guide.jpg'],
                    'badge' => 'Camp',
                    'location' => 'Norway',
                    'price_amount' => '€300',
                    'price_unit' => 'person',
                ],
            ]),
            'offerModules' => [
                'tour' => collect([
                    [
                        'type' => 'tour',
                        'id' => 1,
                        'title' => 'Test Day Tour',
                        'url' => '/guidings/1/test-day-tour',
                        'image' => '/images/placeholder_guide.jpg',
                        'gallery_images' => ['/images/placeholder_guide.jpg'],
                        'badge' => 'Tour',
                        'location' => 'Berlin',
                        'price_amount' => '€120',
                        'price_unit' => 'person',
                    ],
                ]),
                'trip' => collect([
                    [
                        'type' => 'trip',
                        'id' => 2,
                        'title' => 'Test Trip',
                        'url' => '/vacations/trips/test-trip',
                        'image' => '/images/placeholder_guide.jpg',
                        'gallery_images' => ['/images/placeholder_guide.jpg'],
                        'badge' => 'Trip',
                        'location' => 'Sweden',
                        'price_amount' => '€450',
                        'price_unit' => 'person',
                    ],
                ]),
                'camp' => collect([
                    [
                        'type' => 'camp',
                        'id' => 3,
                        'title' => 'Test Camp',
                        'url' => '/vacations/camps/test-camp',
                        'image' => '/images/placeholder_guide.jpg',
                        'gallery_images' => ['/images/placeholder_guide.jpg'],
                        'badge' => 'Camp',
                        'location' => 'Norway',
                        'price_amount' => '€300',
                        'price_unit' => 'person',
                    ],
                ]),
            ],
            'targetSpecies' => collect([
                [
                    'name' => 'Pike',
                    'slug' => 'pike',
                    'thumbnail' => '/assets/images/300x300.png',
                    'url' => '/category-page/targets/pike',
                ],
            ]),
            'featuredGuides' => collect(),
            'testimonials' => collect([
                [
                    'quote' => 'Amazing trip on the lake.',
                    'score' => 9.5,
                    'author' => 'Sam',
                    'date' => 'Mar 2026',
                    'tour_title' => 'Pike fishing day tour',
                ],
            ]),
            'magazineThreads' => collect(),
            'season' => [
                'month' => 'October',
                'title' => "What's biting in October?",
                'text' => 'Seasonal tips',
                'cta_url' => '/fishing-magazine',
                'species' => collect(),
            ],
            'trust' => [
                'rating' => '9.8/10',
                'bookings' => '10,000+',
                'reviews_count' => 2480,
                'reviews_label' => 'View 2,480+ reviews',
                'rating_label' => 'Average rating',
                'bookings_label' => 'Tours booked',
            ],
        ]);

        $this->app->instance(HomepageLandingService::class, $landing);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_homepage_renders_landing_structure(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('cag-home-nav', false);
        $response->assertSee('cag-home-hero__search', false);
        $response->assertSee('action="'.url('/offers').'"', false);
        $response->assertSee('cag-home-hero__doors', false);
        $response->assertSee(route('guidings.index', [], false), false);
        $response->assertSee(route('vacations.index', [], false), false);
        $response->assertSee(route('destination.country', ['country' => 'deutschland'], false), false);
        $response->assertSee('Germany', false);
        $response->assertSee('from €90 / Person', false);
        $response->assertDontSee('Fishing in Germany', false);
        $response->assertSee('cag-home-trust', false);
        $response->assertSee('Shopper Approved', false);
        $response->assertSee('View 2,480+ reviews', false);
        $response->assertSee('cag-home-season', false);
        $response->assertDontSee('cag-home-guides', false);
        $response->assertSee('cag-home-reviews', false);
        $response->assertSee('9.5', false);
        $response->assertSee('/10', false);
        $response->assertSee('cag-home-partner', false);
        $response->assertSee('cag-home-bottom-nav', false);
    }

    public function test_homepage_mixed_offers_include_type_markers(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Test Day Tour', false);
        $response->assertSee('Test Trip', false);
        $response->assertSee('Test Camp', false);
        $response->assertSee('data-product-type="tour"', false);
        $response->assertSee('data-product-type="trip"', false);
        $response->assertSee('data-product-type="camp"', false);
        $response->assertSee('data-offer-rail="tour"', false);
        $response->assertSee('data-offer-rail="trip"', false);
        $response->assertSee('data-offer-rail="camp"', false);
    }
}
