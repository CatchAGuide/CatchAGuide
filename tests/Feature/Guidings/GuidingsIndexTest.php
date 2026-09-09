<?php

namespace Tests\Feature\Guidings;

use App\Domain\Offers\OfferListingFilter;
use App\Domain\Offers\ViewModels\OfferCatalogViewModel;
use App\Services\Offers\OfferCatalogPageService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\URL;
use Mockery;
use Tests\TestCase;

class GuidingsIndexTest extends TestCase
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

    public function test_guidings_index_renders_offers_style_tour_filters_and_cards(): void
    {
        $this->bindToursCatalog(fn () => $this->viewModel());

        $response = $this->get(route('guidings.index'));

        $response->assertOk();

        // Same offers-style hero header as before.
        $response->assertSee('cag-site-nav-shell', false);
        $response->assertSee('offers-page-header__hero', false);

        // Offers catalog shell, locked to tour (no type pillar toggles).
        $response->assertSee('id="offers-catalog"', false);
        $response->assertSee('offers-filters__region', false);
        $response->assertSee('offers-filters__species', false);
        $response->assertSee('data-offers-facet-section="tour"', false);

        // Listing card rendered via the shared offers card component.
        $response->assertSee('Dawn Pike Tour', false);
        $response->assertSee('offers-card', false);

        // Legacy checkbox-filter markup is gone.
        $response->assertDontSee('id="filterContainer"', false);
        $response->assertDontSee('price-range-slider', false);
    }

    public function test_guidings_index_filter_form_submits_back_to_guidings_alloffers(): void
    {
        $this->bindToursCatalog(fn () => $this->viewModel());

        $response = $this->get(route('guidings.index'));

        $response->assertOk();
        $response->assertSee('action="'.route('guidings.index').'"', false);
        $response->assertDontSee('action="'.route('offers.index').'"', false);
    }

    private function bindToursCatalog(callable $factory): void
    {
        $mock = Mockery::mock(OfferCatalogPageService::class);
        $mock->shouldReceive('buildForTours')->andReturnUsing($factory);
        $this->app->instance(OfferCatalogPageService::class, $mock);
    }

    private function viewModel(): OfferCatalogViewModel
    {
        $filter = OfferListingFilter::fromRequest(['type' => 'tour']);

        $card = [
            'type' => 'tour',
            'id' => 1,
            'title' => 'Dawn Pike Tour',
            'url' => '/guidings/offer/dawn-pike-tour',
            'image' => '/images/placeholder_guide.jpg',
            'gallery_images' => ['/images/placeholder_guide.jpg'],
            'badge' => 'Tour',
            'badge_class' => 'tour',
            'location' => 'Berlin',
            'listing_price_display' => '€100',
            'listing_price_prefix' => 'from',
            'listing_price_suffix' => '/ person',
            'listing_cta' => 'View',
            'cta' => 'View',
            'target_fish_tags' => ['Pike'],
            'target_fish_tags_extra' => 0,
            'listing_included' => ['Rod & reel'],
            'duration_label' => '8 Hours',
            'guests_label' => 'Max 4 Personen',
            'water_label' => 'Lake',
            'boat_label' => 'Boat',
            'rating' => 9.5,
            'review_count' => 2,
            'price' => 100,
        ];

        $cards = collect([$card]);
        $paginator = new LengthAwarePaginator(
            $cards->map(fn ($c) => ['type' => 'tour', 'model' => null])->all(),
            1,
            9,
            1,
            ['path' => route('guidings.index')],
        );

        return new OfferCatalogViewModel(
            filter: $filter,
            listings: $paginator,
            cards: $cards,
            toursTotal: 1,
            tripsTotal: 0,
            campsTotal: 0,
            listingsTotal: 1,
            speciesOptions: collect([['id' => 1, 'name' => 'Pike']]),
            countries: collect([['slug' => 'germany', 'name' => 'Germany']]),
            methodOptions: collect([['id' => 1, 'name' => 'Spinning']]),
            waterOptions: collect([['id' => 1, 'name' => 'Lake']]),
            tourDurationOptions: collect([['value' => 'full_day', 'label' => 'Full day']]),
            tripDurationOptions: collect(),
            accommodationTypeOptions: collect(),
            faq: collect(),
            mapMarkers: [],
            suggestedCards: collect(),
            catalogUrl: route('guidings.index'),
            lockTourScope: true,
        );
    }
}
