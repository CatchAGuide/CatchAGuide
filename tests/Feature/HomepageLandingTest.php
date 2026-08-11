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
                    'tour_url' => '/guidings/1/pike-fishing-day-tour',
                ],
            ]),
            'magazineThreads' => collect(),
            'season' => [
                'month' => 'October',
                'title' => "What's biting in October?",
                'text' => 'Seasonal tips',
                'cta_url' => '/fishing-magazine',
                'species' => collect([
                    [
                        'type' => 'pair',
                        'fish' => 'Pike',
                        'country' => 'Sweden',
                        'name' => 'Pike in Sweden',
                        'slug' => 'pike',
                        'thumbnail' => '/assets/images/300x300.png',
                        'url' => '/destination/sweden',
                    ],
                    [
                        'type' => 'pair',
                        'fish' => 'Atlantic Salmon',
                        'country' => 'Norway',
                        'name' => 'Atlantic Salmon in Norway',
                        'slug' => 'atlantic-salmon',
                        'thumbnail' => '/assets/images/300x300.png',
                        'url' => '/destination/norway',
                    ],
                ]),
            ],
            'trust' => [
                'rating' => '9.8/10',
                'bookings' => '10,000+',
                'offers' => '450+',
                'countries' => '12+',
                'reviews_count' => 2480,
                'reviews_label' => 'View 2,480+ reviews',
                'rating_label' => 'Average rating',
                'bookings_label' => 'Tours booked',
                'offers_label' => 'Offers',
                'countries_label' => 'Countries',
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
        $response->assertSee('cag-site-nav', false);
        $response->assertSee('cag-home-hero-shell', false);
        $response->assertSee('data-hero-carousel', false);
        $response->assertSee('assets/images/homepage/hero-tour.webp', false);
        $response->assertSee('assets/images/homepage/hero-camp.webp', false);
        $response->assertSee('assets/images/homepage/hero-trip.webp', false);
        $response->assertSee('assets/images/homepage/hero-vacation.webp', false);
        $response->assertSee('cag-home-hero__search', false);
        $response->assertSee('action="'.url('/offers').'"', false);
        $response->assertSee('cag-site-nav__lang', false);
        $response->assertSee('cag-site-nav__lang-btn', false);
        $response->assertSee('cag-auth-modal', false);
        $response->assertSee('cag-auth-modal__accent', false);
        $response->assertSee('id="loginModal"', false);
        $response->assertSee('id="guideApplicationModal"', false);
        $response->assertSee('cag-home-hero__doors', false);
        $response->assertSee('cag-home-hero__door-icon', false);
        $response->assertSee('fa-ship', false);
        $response->assertSee('fa-suitcase-rolling', false);
        $response->assertSee(route('guidings.landing', [], false), false);
        $response->assertSee(route('vacations.index', [], false), false);
        $response->assertSee(route('destination.country', ['country' => 'deutschland'], false), false);
        $response->assertSee('Germany', false);
        $response->assertSee('from €90 / Person', false);
        $response->assertDontSee('Fishing in Germany', false);
        $response->assertSee('cag-home-trust', false);
        $response->assertSee('Shopper Approved', false);
        $response->assertSee('View 2,480+ reviews', false);
        $response->assertSee('450+', false);
        $response->assertSee('12+', false);
        $response->assertSee(__('homepage.trust_offers_label'), false);
        $response->assertSee(__('homepage.trust_countries_label'), false);
        $response->assertSee('cag-home-season', false);
        $response->assertSee('cag-home-season__grid', false);
        $response->assertSee('cag-home-season__card', false);
        $response->assertSee('biting in October?', false);
        $response->assertSee('cag-home-season__badge', false);
        $response->assertSee('Sweden', false);
        $response->assertSee('Pike in Sweden', false);
        $response->assertSee('Atlantic Salmon in Norway', false);
        $response->assertDontSee('cag-home-season__species-card', false);
        $response->assertDontSee('cag-home-guides', false);
        $response->assertSee('cag-home-species__rail', false);
        $response->assertSee('cag-home-species__card', false);
        $response->assertSee('data-species-spotlight', false);
        $response->assertSee('Pike', false);
        $response->assertSee('cag-home-reviews', false);
        $response->assertSee('9.5', false);
        $response->assertSee('/10', false);
        $response->assertSee('Pike fishing day tour', false);
        $response->assertSee('href="/guidings/1/pike-fishing-day-tour"', false);
        $response->assertSee('cag-home-partner', false);
        $response->assertSee('cag-home-partner__cards', false);
        $response->assertSee('cag-home-partner__card', false);
        $response->assertSee('Werde Catch A Guide Partner', false);
        $response->assertSee('Maximiere deine Buchungen', false);
        $response->assertSee('Kein Risiko', false);
        $response->assertSee('Neue Kunden', false);
        $response->assertSee('Volle Kontrolle', false);
        $response->assertSee('Direkt registrieren', false);
        $response->assertSee('Mehr erfahren', false);
        $response->assertSee('cag-home-bottom-nav', false);
        $response->assertSee('cag-footer', false);
        $response->assertSee('info.catchaguide@gmail.com', false);
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

        $html = $response->getContent();
        $campPos = strpos($html, 'data-offer-module="camp"');
        $tripPos = strpos($html, 'data-offer-module="trip"');
        $this->assertNotFalse($campPos);
        $this->assertNotFalse($tripPos);
        $this->assertLessThan($tripPos, $campPos, 'Camps module should appear before Trips on the homepage.');
    }
}
