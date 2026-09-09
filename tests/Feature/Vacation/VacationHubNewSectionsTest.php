<?php

namespace Tests\Feature\Vacation;

use App\Domain\Vacation\Pillar;
use App\Domain\Vacation\ViewModels\PillarTileViewModel;
use App\Domain\Vacation\ViewModels\VacationHubViewModel;
use App\Services\Vacation\VacationHubPageService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Mockery;
use Tests\TestCase;

class VacationHubNewSectionsTest extends TestCase
{
    private function makeHub(array $overrides = []): VacationHubViewModel
    {
        return new VacationHubViewModel(
            campTile: new PillarTileViewModel(
                pillar: Pillar::Camp,
                title: 'Camps',
                description: 'Camp desc',
                listingCount: 2,
                countryCount: 1,
                minPrice: 100,
                currency: 'EUR',
                url: route('vacations.camps.index'),
            ),
            tripTile: new PillarTileViewModel(
                pillar: Pillar::Trip,
                title: 'Trips',
                description: 'Trip desc',
                listingCount: 3,
                countryCount: 2,
                minPrice: 200,
                currency: 'EUR',
                url: route('vacations.trips.index'),
            ),
            popularListings: collect(),
            newListings: $overrides['newListings'] ?? collect(),
            showNewListingsRail: $overrides['showNewListingsRail'] ?? false,
            countryGrid: collect(),
            faqItems: [['question' => 'Q1?', 'answer' => 'A1']],
            totalTrips: 3,
            totalCamps: 2,
            targetFishTiles: $overrides['targetFishTiles'] ?? collect(),
            testimonials: $overrides['testimonials'] ?? collect(),
        );
    }

    private function mockHubService(VacationHubViewModel $hub): void
    {
        $service = Mockery::mock(VacationHubPageService::class);
        $service->shouldReceive('build')->andReturn($hub);
        $this->app->instance(VacationHubPageService::class, $service);
    }

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://localhost']);
        URL::forceRootUrl('http://localhost');

        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);

        Cache::flush();
    }

    public function test_hub_renders_consultation_season_picker_seo_and_provider_cta(): void
    {
        $this->mockHubService($this->makeHub());

        $response = $this->get(route('vacations.index'));

        $response->assertOk();
        $response->assertSee('vacation-hub__consultation', false);
        $response->assertSee('vacation-hub__consultation-main', false);
        $response->assertSee('vacation-hub__consultation-aside', false);
        $response->assertSee('vacation-hub__consultation-checklist', false);
        $response->assertSee('vacation-hub__consultation-cta', false);
        $response->assertSee(__('vacations.hub_consultation_title'), false);
        $response->assertSee('vacation-hub__seo gl-seo', false);
        $response->assertSee(__('vacations.hub_seo_title'), false);
        $response->assertSee('vacation-hub__provider-cta cag-home-partner', false);
        $response->assertSee(__('vacations.provider_cta_title'), false);
        $response->assertSee('cag-home-partner__cards', false);
        $response->assertSee(__('vacations.provider_cta_card_risk_title'), false);
        $response->assertSee('vacation-hub__cross-sell', false);
        $response->assertSee(__('vacations.hub_cross_sell_title'), false);
        $response->assertSee('Q1?', false);
        $response->assertSee('vacation-faq', false);
        $response->assertSee('vacation-faq__inner', false);
        $response->assertSee('vacation-faq__list', false);
        $response->assertSee('data-vacation-faq', false);
        $response->assertSee('vacation-faq__q', false);
        $response->assertDontSee('id="vacationHubFaq"', false);
    }

    public function test_vacation_faq_uses_guidings_blue_band_styles(): void
    {
        $source = (string) file_get_contents(resource_path('sass/page/_vacations-two-pillar.scss'));

        $this->assertMatchesRegularExpression(
            '/\.vacation-faq \{\s*background:\s*#EEF2F8;/',
            $source
        );
        $this->assertMatchesRegularExpression(
            '/\.vacation-faq__inner \{\s*@include cag-page-container;/',
            $source
        );
        $this->assertMatchesRegularExpression(
            '/\.vacation-faq__list \{[\s\S]*?background:\s*#fff;/',
            $source
        );
    }

    public function test_hub_provider_cta_matches_guiding_partner_layout(): void
    {
        $this->mockHubService($this->makeHub());

        $response = $this->get(route('vacations.index'));

        $response->assertOk();
        $response->assertSee('cag-home cag-home--embed', false);
        $response->assertSee('vacation-hub__provider-cta cag-home-partner', false);
        $response->assertSee('cag-home-container cag-home-partner__inner', false);
        $response->assertSee(__('vacations.provider_cta_eyebrow'), false);
        $response->assertSee(__('vacations.provider_cta_title'), false);
        $response->assertSee(__('vacations.provider_cta_secondary'), false);
        $response->assertSee('cag-home-partner__cards', false);
        $response->assertSee(__('vacations.provider_cta_card_risk_title'), false);
        $response->assertSee(__('vacations.provider_cta_card_demand_title'), false);
        $response->assertSee(__('vacations.provider_cta_card_control_title'), false);
        $response->assertSee('vacation-hub__seo gl-seo', false);
        $response->assertSee(__('vacations.hub_seo_title'), false);
        $response->assertSee('data-gl-seo-toggle', false);
        $response->assertSee(__('vacations.hub_seo_more'), false);
    }

    public function test_consultation_desktop_grid_places_cta_beside_checklist(): void
    {
        $source = (string) file_get_contents(resource_path('sass/page/_vacations-hub-extras.scss'));

        $this->assertMatchesRegularExpression(
            '/\.vacation-hub__consultation \{[\s\S]*?@media \(min-width:\s*768px\) \{[\s\S]*?grid-template-rows:\s*auto auto;/',
            $source
        );
        $this->assertMatchesRegularExpression(
            '/\.vacation-hub__consultation-main,[\s\S]*?\.vacation-hub__consultation-aside \{[\s\S]*?display:\s*contents;/',
            $source
        );
        $this->assertMatchesRegularExpression(
            '/\.vacation-hub__consultation-checklist \{[\s\S]*?grid-column:\s*1;[\s\S]*?grid-row:\s*2;[\s\S]*?align-self:\s*center;/',
            $source
        );
        $this->assertMatchesRegularExpression(
            '/\.vacation-hub__consultation-cta \{[\s\S]*?grid-column:\s*2;[\s\S]*?grid-row:\s*2;[\s\S]*?align-self:\s*center;[\s\S]*?width:\s*100%;/',
            $source
        );
        $this->assertMatchesRegularExpression(
            '/&-aside \{[\s\S]*?display:\s*flex;[\s\S]*?flex-direction:\s*column;[\s\S]*?gap:\s*1\.5rem;/',
            $source
        );
    }

    public function test_hub_renders_target_fish_rail_when_present(): void
    {
        $tile = [
            'name' => 'Pike',
            'slug' => 'pike-test',
            'thumbnail' => 'fish/pike.jpg',
            'count' => 5,
            'url' => route('vacations.targets', ['slug' => 'pike-test']),
        ];

        $this->mockHubService($this->makeHub(['targetFishTiles' => collect([$tile])]));

        $response = $this->get(route('vacations.index'));

        $response->assertOk();
        $response->assertSee('vacation-fish-rail', false);
        $response->assertSee('vacation-fish-rail__img', false);
        $response->assertDontSee('vacation-fish-rail__placeholder', false);
        $response->assertSee('Pike', false);
        $response->assertSee('vacation-fish-rail__tile--compact', false);
        $response->assertDontSee(__('vacations.hub_target_fish_count', ['count' => 5]), false);
        $response->assertSee(route('vacations.targets', ['slug' => 'pike-test'], false), false);
        $response->assertSee(route('vacations.targets.index', [], false), false);
        $response->assertSee(__('vacations.hub_target_fish_view_all'), false);
    }

    public function test_hub_hides_target_fish_rail_when_empty(): void
    {
        $this->mockHubService($this->makeHub());

        $response = $this->get(route('vacations.index'));

        $response->assertOk();
        $response->assertDontSee('vacation-fish-rail__tile', false);
    }

    public function test_hub_renders_reviews_when_present(): void
    {
        $review = [
            'quote' => 'Amazing trip!',
            'score' => 9.5,
            'author' => 'Alex',
            'date' => 'Jan 2026',
            'listing_title' => 'Test Camp',
            'listing_url' => 'https://example.test/camp',
        ];

        $this->mockHubService($this->makeHub(['testimonials' => collect([$review])]));

        $response = $this->get(route('vacations.index'));

        $response->assertOk();
        $response->assertSee(__('vacations.hub_reviews_title'), false);
        $response->assertSee('Amazing trip!', false);
        $response->assertSee('Alex', false);
    }

    public function test_hub_hides_reviews_when_empty(): void
    {
        $this->mockHubService($this->makeHub());

        $response = $this->get(route('vacations.index'));

        $response->assertOk();
        $response->assertDontSee(__('vacations.hub_reviews_title'), false);
    }

    public function test_hub_pillar_tiles_use_header_keywords_and_cta_layout(): void
    {
        $this->mockHubService($this->makeHub());

        $response = $this->get(route('vacations.index'));

        $response->assertOk();
        $response->assertSee('vacation-hub__pillar-fork', false);
        $response->assertSee(__('vacations.hub_fork_eyebrow'), false);
        $response->assertSee(__('vacations.hub_fork_title'), false);
        $response->assertSee('vacation-pillar-tile__header', false);
        $response->assertSee('vacation-pillar-tile--camp', false);
        $response->assertSee('vacation-pillar-tile--trip', false);
        $response->assertSee('fa-campground', false);
        $response->assertSee('fa-compass', false);
        $response->assertSee(__('vacations.pillar_camps_cta'), false);
        $response->assertSee(__('vacations.pillar_trips_cta'), false);
        $response->assertSee(__('vacations.pillar_camps_keywords.0'), false);
        $response->assertSee(__('vacations.pillar_trips_keywords.0'), false);
        $response->assertDontSee('vacation-pillar-tile__stats', false);
        $response->assertDontSee(__('vacations.pillar_tile_explore'), false);
        $response->assertDontSee(__('vacations.price_from_per_night', ['price' => '€100']), false);
    }

    public function test_hub_renders_recently_added_holidays_rail_with_mixed_trip_and_camp_cards(): void
    {
        $tripCard = [
            'type' => 'trip',
            'id' => 1,
            'title' => 'Test Trip',
            'url' => '/vacations/trips/test-trip',
            'image' => '/images/placeholder_guide.jpg',
            'gallery_images' => ['/images/placeholder_guide.jpg'],
            'badge' => 'Trip',
            'location' => 'Sweden',
            'cta' => 'Request trip',
            'price_amount' => '€450',
            'price_unit' => 'person',
        ];

        $campCard = [
            'type' => 'camp',
            'id' => 2,
            'title' => 'Test Camp',
            'url' => '/vacations/camps/test-camp',
            'image' => '/images/placeholder_guide.jpg',
            'gallery_images' => ['/images/placeholder_guide.jpg'],
            'badge' => 'Camp',
            'location' => 'Norway',
            'cta' => 'Book now',
            'price_amount' => '€300',
            'price_unit' => 'night',
        ];

        $this->mockHubService($this->makeHub([
            'newListings' => collect([$tripCard, $campCard]),
            'showNewListingsRail' => true,
        ]));

        $response = $this->get(route('vacations.index'));

        $response->assertOk();
        $response->assertSee('data-analytics-vacation-rail="new-listings"', false);
        $response->assertSee(__('vacations.hub_new_listings_title'), false);
        $response->assertSee('Test Trip', false);
        $response->assertSee('Test Camp', false);
        $response->assertSee('vacation-slider-card--trip', false);
        $response->assertSee('vacation-slider-card--camp', false);
    }

    public function test_hub_hides_new_listings_rail_when_empty(): void
    {
        $this->mockHubService($this->makeHub());

        $response = $this->get(route('vacations.index'));

        $response->assertOk();
        $response->assertDontSee('data-analytics-vacation-rail="new-listings"', false);
        $response->assertDontSee(__('vacations.hub_new_listings_title'), false);
    }

    public function test_hub_interlude_resets_head_flex_basis_on_mobile(): void
    {
        $source = (string) file_get_contents(resource_path('sass/page/_vacations-two-pillar.scss'));

        $this->assertMatchesRegularExpression(
            '/\.vacation-hub__interlude-head \{\s*flex:\s*1 1 16rem;/',
            $source
        );
        $this->assertMatchesRegularExpression(
            '/@media \(max-width:\s*991px\) \{[\s\S]*?\.vacation-hub__interlude-head \{\s*flex:\s*0 0 auto;/',
            $source
        );
    }
}
