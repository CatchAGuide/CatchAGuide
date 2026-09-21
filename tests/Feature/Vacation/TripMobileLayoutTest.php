<?php

namespace Tests\Feature\Vacation;

use App\Models\Trip;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class TripMobileLayoutTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://cag.local']);
        URL::forceRootUrl('http://cag.local');
    }

    public function test_live_trip_page_renders_stacked_book_bar_specs_and_search_sheet(): void
    {
        $trip = $this->makeTrip();

        $response = $this->get(route('vacations.trips.show', $trip->slug));

        $response->assertOk();
        $html = $response->getContent();

        $this->assertStringContainsString('listing-mobile-book--stacked', $html);
        $this->assertMatchesRegularExpression('/<footer[^>]*class="[^"]*site-footer/', $html);
        $this->assertStringContainsString('trip-offer-page__booking-cta', $html);
        $this->assertSame('Request a quote', __('vacations.request_trip', [], 'en'));
        $this->assertSame('Request a quote', __('vacations.request_trip_bar', [], 'en'));
        $this->assertSame('Unverbindlich anfragen', __('vacations.request_trip', [], 'de'));
        $this->assertSame('Unverbindlich anfragen', __('vacations.request_trip_bar', [], 'de'));
        $this->assertStringContainsString(__('vacations.request_trip_bar'), $html);
        $this->assertStringContainsString(__('vacations.request_trip'), $html);
        $this->assertStringContainsString(__('vacations.per_person'), $html);
        $this->assertStringContainsString(e(__('vacations.product_spec_duration_included', [
            'days' => 7,
            'nights' => 6,
        ])), $html);
        $this->assertStringNotContainsString('camp-product-specs', $html);
        $this->assertStringContainsString('trip-offer-page__feature-cards', $html);
        $this->assertStringContainsString('trip-offer-page__about-card', $html);
        $this->assertStringContainsString(__('trips.about_this_trip'), $html);
        $this->assertStringContainsString(__('trips.duration'), $html);
        $this->assertStringContainsString(__('trips.group_size'), $html);
        $this->assertStringContainsString(__('trips.people'), $html);
        $this->assertStringContainsString(__('vacations.catalog_header_mobile_trigger_trip'), $html);
        $this->assertStringContainsString(__('vacations.catalog_header_mobile_sheet_title_trip'), $html);
        $this->assertStringContainsString('data-mobile-search-sheet', $html);
        $this->assertStringContainsString('camp-gallery__counter', $html);
        $this->assertStringNotContainsString('trip-offer-page__mobile-sticky-simple', $html);
        $this->assertStringContainsString('vacations-page-header__product-title', $html);
        $this->assertStringContainsString('vacations-page-header__place', $html);
        $this->assertStringContainsString('Bäverfjärden', $html);
        $this->assertStringContainsString(__('vacations.show_on_map'), $html);
    }

    public function test_trip_product_header_falls_back_to_location_when_city_region_country_empty(): void
    {
        $trip = $this->makeTrip([
            'city' => null,
            'region' => null,
            'country' => null,
            'location' => 'Po-Delta bei Adria, Italien',
        ]);

        $response = $this->get(route('vacations.trips.show', $trip->slug));

        $response->assertOk();
        $html = $response->getContent();

        $this->assertStringContainsString('vacations-page-header__place', $html);
        $this->assertMatchesRegularExpression(
            '/vacations-page-header__place[\s\S]{0,240}Po-Delta bei Adria, Italien/',
            $html
        );
        $this->assertStringContainsString(__('vacations.show_on_map'), $html);
    }

    public function test_draft_trip_page_does_not_render_the_mobile_book_bar(): void
    {
        $trip = $this->makeTrip(['status' => 'draft']);

        $response = $this->get(route('vacations.trips.show', $trip->slug));

        $response->assertOk();
        $response->assertDontSee('listing-mobile-book--stacked', false);
    }

    public function test_stacked_book_bar_renders_duration_note(): void
    {
        $html = Blade::render(
            '<x-vacation.mobile-book-bar variant="stacked" price-display="€1.290" price-suffix="pro Person" price-note="7 Tage & 6 Nächte inklusive" cta-label="Unverbindlich anfragen" />'
        );

        $this->assertStringContainsString('listing-mobile-book--stacked', $html);
        $this->assertStringContainsString('listing-mobile-book__note', $html);
        $this->assertStringContainsString('7 Tage &amp; 6 Nächte inklusive', $html);
        $this->assertStringContainsString('Unverbindlich anfragen', $html);
    }

    private function makeTrip(array $overrides = []): Trip
    {
        $user = User::query()->first() ?? User::factory()->create();

        return Trip::query()->create(array_merge([
            'title' => 'Test Trip Mobile Layout',
            'slug' => 'test-trip-mobile-layout-'.uniqid(),
            'description' => 'Guided pike fishing with full board.',
            'location' => 'Test Location',
            'city' => 'Bäverfjärden',
            'region' => 'Värmland',
            'country' => 'Sweden',
            'status' => 'active',
            'user_id' => $user->id,
            'duration_days' => 7,
            'duration_nights' => 6,
            'group_size_min' => 2,
            'group_size_max' => 6,
            'price_per_person' => 1290,
            'currency' => 'EUR',
            'target_species' => ['Hecht'],
            'catering' => [__('trips.catering_full_board')],
            'gallery_images' => [
                '/images/placeholder.jpg',
                '/images/placeholder_guide.jpg',
            ],
            'thumbnail_path' => '/images/placeholder.jpg',
        ], $overrides));
    }
}
