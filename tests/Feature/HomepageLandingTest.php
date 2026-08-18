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
                ],
                [
                    'slug' => 'schweden',
                    'name' => 'Sweden',
                    'thumbnail' => '/assets/images/300x300.png',
                    'countrycode' => 'SE',
                ],
            ]),
            'countryCount' => 12,
            'mixedOffers' => collect([
                [
                    'type' => 'tour',
                    'id' => 1,
                    'title' => 'Test Day Tour',
                    'url' => '/guidings/offer/test-day-tour',
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
                        'url' => '/guidings/offer/test-day-tour',
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
                    'url' => '/targets/pike',
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
                    'tour_url' => '/guidings/offer/pike-fishing-day-tour',
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
        $response->assertSee('cag-home-hero__who', false);
        $response->assertSee('data-offers-persons-stepper', false);
        $response->assertSee('name="num_guests"', false);
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
        $response->assertSee('fi fi-de', false);
        $response->assertSee('fi fi-se', false);
        $response->assertDontSee('from €90 / Person', false);
        $response->assertDontSee('cag-home-destinations__price', false);
        $response->assertDontSee('Fishing in Germany', false);
        $response->assertSee('cag-home-trust', false);
        $response->assertSee('cag-home-trust__strip', false);
        $response->assertSee('cag-home-trust__primary', false);
        $response->assertSee('cag-home-trust__lead', false);
        $response->assertSee(__('homepage.trust_angler_approved'), false);
        $response->assertDontSee('Shopper Approved', false);
        $response->assertSee('View 2,480+ reviews', false);
        $response->assertSee('450+', false);
        $response->assertSee('12+', false);
        $response->assertSee(__('homepage.trust_offers_label'), false);
        $response->assertSee(__('homepage.trust_countries_label'), false);
        $response->assertSee('cag-home-trust__assurances', false);
        $response->assertSee(__('homepage.trust_reply_title'), false);
        $response->assertSee(__('homepage.trust_cancel_title'), false);
        $response->assertSee(__('homepage.trust_reply_text'), false);
        $response->assertSee(__('homepage.trust_cancel_text'), false);
        $response->assertSee('cag-home-season', false);
        $response->assertSee('cag-home-season__grid', false);
        $response->assertSee('cag-home-season__card', false);
        $response->assertSee('biting in October?', false);
        $response->assertDontSee('cag-home-season__cta', false);
        $response->assertDontSee(__('homepage.season_cta', ['month' => 'October']), false);
        $response->assertSee('cag-home-season__badge', false);
        $response->assertSee('Sweden', false);
        $response->assertSee('Pike in Sweden', false);
        $response->assertSee('Atlantic Salmon in Norway', false);
        $response->assertDontSee('cag-home-season__species-card', false);
        $response->assertDontSee('cag-home-guides', false);
        $response->assertSee('cag-home-species__rail', false);
        $response->assertSee('cag-home-species__card', false);
        $response->assertSee('data-species-spotlight', false);
        $response->assertSee('data-species-viewport', false);
        $response->assertSee('data-dest-rail', false);
        $response->assertSee('enableDragScroll', false);
        $response->assertSee('Pike', false);
        $response->assertSee('cag-home-reviews', false);
        $response->assertSee('9.5', false);
        $response->assertSee('/10', false);
        $response->assertSee('Pike fishing day tour', false);
        $response->assertSee('href="/guidings/offer/pike-fishing-day-tour"', false);
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
        $this->assertMatchesRegularExpression(
            '/cag-home-bottom-nav[\s\S]*'.preg_quote(__('offers.nav_label'), '/').'[\s\S]*'.preg_quote(__('homepage.filter-fishing-near-me'), '/').'[\s\S]*'.preg_quote(__('homepage.header-vacations'), '/').'[\s\S]*'.preg_quote(__('homepage.footer_destinations'), '/').'/',
            $response->getContent()
        );
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
        $response->assertSee('cag-home-offer--tour', false);
        $response->assertSee('cag-home-offer--trip', false);
        $response->assertSee('cag-home-offer--camp', false);
        $response->assertSee('cag-home-offer__badge--tour', false);
        $response->assertSee('cag-home-offer__badge--trip', false);
        $response->assertSee('cag-home-offer__badge--camp', false);
        $response->assertSee('cag-home-offers__module--tour', false);
        $response->assertSee('cag-home-offers__module--camp', false);
        $response->assertSee('cag-home-offers__module--trip', false);
        $response->assertSee('cag-home-offers__module-mark', false);
        $response->assertSee('fa-campground', false);
        $response->assertSee('data-offer-rail="tour"', false);
        $response->assertSee('data-offer-rail="trip"', false);
        $response->assertSee('data-offer-rail="camp"', false);

        $html = $response->getContent();
        $campPos = strpos($html, 'data-offer-module="camp"');
        $tripPos = strpos($html, 'data-offer-module="trip"');
        $this->assertNotFalse($campPos);
        $this->assertNotFalse($tripPos);
        $this->assertLessThan($tripPos, $campPos, 'Camps module should appear before Trips on the homepage.');

        $this->assertMatchesRegularExpression('/data-offer-module="tour"[\s\S]{0,400}fa-ship/', $html);
        $this->assertMatchesRegularExpression('/data-offer-module="camp"[\s\S]{0,400}fa-campground/', $html);
        $this->assertMatchesRegularExpression('/data-offer-module="trip"[\s\S]{0,400}fa-suitcase-rolling/', $html);
    }

    public function test_homepage_reviews_rail_is_interactive(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('data-reviews-rail', false);
        $response->assertSee('data-reviews-prev', false);
        $response->assertSee('data-reviews-next', false);
        $response->assertSee(__('homepage.reviews_slider_prev'), false);
        $response->assertSee(__('homepage.reviews_slider_next'), false);
        $response->assertSee("querySelector('[data-reviews-rail]')", false);
        $response->assertSee('enableDragScroll(reviewsRail', false);
        $response->assertSee('reviewsRail.scrollLeft +=', false);
        $response->assertDontSee("querySelector('[data-reviews-rail] .cag-home-reviews__rail')", false);
        $response->assertDontSee('cag-home-reviews-marquee', false);
        $response->assertDontSee("reviewsRail.addEventListener('mouseenter'", false);
    }
}
