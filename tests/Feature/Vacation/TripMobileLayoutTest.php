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
        $this->assertStringContainsString('trip-offer-page__booking-cta', $html);
        $this->assertStringContainsString(__('vacations.request_trip_bar'), $html);
        $this->assertStringContainsString(__('vacations.request_trip'), $html);
        $this->assertStringContainsString(__('vacations.per_person'), $html);
        $this->assertStringContainsString(e(__('vacations.product_spec_duration_included', [
            'days' => 7,
            'nights' => 6,
        ])), $html);
        $this->assertStringContainsString('camp-product-specs', $html);
        $this->assertStringContainsString(__('trips.duration'), $html);
        $this->assertStringContainsString(__('vacations.product_spec_group'), $html);
        $this->assertStringContainsString(__('vacations.product_spec_max', ['count' => 6]), $html);
        $this->assertStringContainsString(__('trips.catering_full_board'), $html);
        $this->assertStringContainsString(__('vacations.catalog_header_mobile_trigger_trip'), $html);
        $this->assertStringContainsString(__('vacations.catalog_header_mobile_sheet_title_trip'), $html);
        $this->assertStringContainsString('data-mobile-search-sheet', $html);
        $this->assertStringContainsString('camp-gallery__counter', $html);
        $this->assertStringNotContainsString('trip-offer-page__mobile-sticky-simple', $html);
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
            '<x-vacation.mobile-book-bar variant="stacked" price-display="€1.290" price-suffix="pro Person" price-note="7 Tage & 6 Nächte inklusive" cta-label="Reise anfragen" />'
        );

        $this->assertStringContainsString('listing-mobile-book--stacked', $html);
        $this->assertStringContainsString('listing-mobile-book__note', $html);
        $this->assertStringContainsString('7 Tage &amp; 6 Nächte inklusive', $html);
        $this->assertStringContainsString('Reise anfragen', $html);
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
