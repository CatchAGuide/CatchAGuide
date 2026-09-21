<?php

namespace Tests\Feature\Vacation;

use App\Models\Accommodation;
use App\Models\Camp;
use App\Models\Guiding;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class CampMobileBookBarTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://cag.local']);
        URL::forceRootUrl('http://cag.local');
    }

    public function test_live_camp_page_renders_a_mobile_book_bar_and_one_desktop_booking_card(): void
    {
        $camp = $this->makeCamp();

        $response = $this->get(route('vacations.camps.show', $camp->slug));

        $response->assertOk();
        $html = $response->getContent();

        $this->assertStringContainsString('listing-mobile-book', $html);
        $this->assertStringContainsString('data-camp-mobile-book', $html);
        $this->assertStringContainsString(__('vacations.check_availability'), $html);
        $this->assertStringContainsString(__('vacations.no_booking_fees'), $html);
        $this->assertStringContainsString('listing-mobile-book__note', $html);
        $this->assertStringContainsString('listing-mobile-book--stacked', $html);
        $this->assertStringContainsString('id="camp-booking-date-desktop"', $html);
        $this->assertStringNotContainsString('camp-booking-date-mobile', $html);
        $this->assertSame(1, substr_count($html, 'class="camp-booking-card"'));
        $this->assertStringContainsString('Test Waters', $html);
        $this->assertStringContainsString(__('vacations.catalog_header_mobile_trigger_camp'), $html);
        $this->assertStringContainsString('vacations-page-header__product-title', $html);
        $this->assertStringContainsString('vacations-page-header__place', $html);
        $this->assertStringContainsString(__('vacations.show_on_map'), $html);
    }

    public function test_camp_product_header_falls_back_to_location_when_city_is_empty(): void
    {
        $camp = $this->makeCamp([
            'city' => '',
            'region' => '',
            'country' => '',
            'location' => 'Baalensee Str. 8 Fürstenberg',
        ]);

        $response = $this->get(route('vacations.camps.show', $camp->slug));

        $response->assertOk();
        $html = $response->getContent();

        $this->assertStringContainsString('vacations-page-header__place', $html);
        $this->assertMatchesRegularExpression(
            '/vacations-page-header__place[\s\S]{0,200}Baalensee Str\. 8 Fürstenberg/',
            $html
        );
        $this->assertStringContainsString(__('vacations.show_on_map'), $html);
    }

    public function test_draft_camp_page_does_not_render_the_mobile_book_bar(): void
    {
        $camp = $this->makeCamp(['status' => 'draft']);

        $response = $this->get(route('vacations.camps.show', $camp->slug));

        $response->assertOk();
        $response->assertSee(__('vacations.draft_review_banner_title'), false);
        $response->assertDontSee('class="listing-mobile-book"', false);
        $response->assertDontSee('camp-booking-date-desktop', false);
    }

    public function test_mobile_book_bar_component_renders_price_and_cta(): void
    {
        $html = Blade::render(
            '<x-vacation.mobile-book-bar price-display="€199" price-suffix="/ night" cta-label="Request Now" />'
        );

        $this->assertStringContainsString('listing-mobile-book', $html);
        $this->assertStringContainsString('€199', $html);
        $this->assertStringContainsString('/ night', $html);
        $this->assertStringContainsString('Request Now', $html);
        $this->assertStringContainsString('listing-mobile-book__cta', $html);
        $this->assertStringNotContainsString('listing-mobile-book--stacked', $html);
    }

    public function test_stacked_mobile_book_bar_uses_the_dark_card_layout(): void
    {
        $html = Blade::render(
            '<x-vacation.mobile-book-bar variant="stacked" price-display="€100" price-suffix="pro Nacht" cta-label="Urlaub anfragen" />'
        );

        $this->assertStringContainsString('listing-mobile-book--stacked', $html);
        $this->assertStringContainsString('€100', $html);
        $this->assertStringContainsString('Urlaub anfragen', $html);
    }

    public function test_camp_book_bar_and_card_use_from_price_and_availability_copy(): void
    {
        $this->assertSame('From €100', __('vacations.from_price', ['price' => '€100'], 'en'));
        $this->assertSame('Ab €100', __('vacations.from_price', ['price' => '€100'], 'de'));
        $this->assertSame('Check availability', __('vacations.check_availability', [], 'en'));
        $this->assertSame('Verfügbarkeit prüfen', __('vacations.check_availability', [], 'de'));

        $source = (string) file_get_contents(resource_path('views/pages/vacations/v2.blade.php'));
        $card = (string) file_get_contents(resource_path('views/pages/vacations/partials/camp-booking-card.blade.php'));

        $this->assertStringContainsString("__('vacations.from_price'", $source);
        $this->assertStringContainsString("__('vacations.no_booking_fees')", $source);
        $this->assertStringContainsString("__('vacations.check_availability')", $source);
        $this->assertStringContainsString("__('vacations.check_availability')", $card);
        $this->assertStringContainsString('camp-booking-card__price-row', $card);
        $this->assertStringContainsString("__('vacations.no_booking_fees')", $card);
    }

    public function test_camp_page_shows_section_anchors_under_the_gallery_instead_of_spec_badges(): void
    {
        $camp = $this->makeCamp();
        $this->attachAccommodation($camp);
        $this->attachGuiding($camp);

        $response = $this->get(route('vacations.camps.show', $camp->slug));

        $response->assertOk();
        $html = $response->getContent();

        $this->assertStringContainsString('camp-nav-enhanced', $html);
        $this->assertStringContainsString('href="#accommodations"', $html);
        $this->assertStringContainsString(__('vacations.accommodations'), $html);
        $this->assertStringContainsString('href="#guidings"', $html);
        $this->assertStringContainsString(e(__('vacations.guidings_tours')), $html);
        $this->assertStringNotContainsString('camp-product-specs', $html);
        $this->assertStringNotContainsString(__('vacations.product_spec_waters'), $html);

        $galleryPos = strpos($html, 'class="camp-gallery"');
        $navPos = strpos($html, 'class="camp-nav-enhanced"');

        $this->assertNotFalse($galleryPos);
        $this->assertNotFalse($navPos);
        $this->assertGreaterThan($galleryPos, $navPos);
    }

    private function makeCamp(array $overrides = []): Camp
    {
        $user = User::query()->first();
        if (! $user) {
            $this->markTestSkipped('No user available to own a test camp.');
        }

        return Camp::query()->create(array_merge([
            'title' => 'Test Camp Mobile Book',
            'slug' => 'test-camp-mobile-book-'.uniqid(),
            'description_camp' => 'Camp description',
            'description_area' => 'Area description',
            'description_fishing' => 'Fishing description',
            'location' => 'Test Location',
            'city' => 'Test Waters',
            'region' => 'Värmland',
            'country' => 'Sweden',
            'status' => 'active',
            'user_id' => $user->id,
        ], $overrides));
    }

    private function attachAccommodation(Camp $camp): void
    {
        $accommodation = Accommodation::query()->create([
            'status' => 'active',
            'user_id' => $camp->user_id,
            'title' => 'Test Cabin '.uniqid(),
            'slug' => 'test-cabin-'.uniqid(),
            'location' => $camp->location,
            'city' => $camp->city ?: 'Test City',
            'country' => $camp->country,
            'region' => $camp->region ?: 'Test Region',
            'accommodation_type' => 'cabin',
            'max_occupancy' => 4,
        ]);

        $camp->accommodations()->attach($accommodation->id);
    }

    private function attachGuiding(Camp $camp): void
    {
        $template = Guiding::query()->first();
        if (! $template) {
            $this->markTestSkipped('No existing guiding available to attach to a test camp.');
        }

        $guiding = $template->replicate();
        $guiding->title = 'Test Camp Guiding';
        $guiding->slug = null;
        $guiding->save();

        $camp->guidings()->sync([$guiding->id]);
    }
}
