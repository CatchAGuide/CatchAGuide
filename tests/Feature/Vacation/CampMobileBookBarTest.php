<?php

namespace Tests\Feature\Vacation;

use App\Models\Camp;
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

        $this->assertStringContainsString('class="listing-mobile-book"', $html);
        $this->assertStringContainsString('data-camp-mobile-book', $html);
        $this->assertStringContainsString(__('vacations.contact_us_button'), $html);
        $this->assertStringContainsString('id="camp-booking-date-desktop"', $html);
        $this->assertStringNotContainsString('camp-booking-date-mobile', $html);
        $this->assertSame(1, substr_count($html, 'class="camp-booking-card"'));
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
            'status' => 'active',
            'user_id' => $user->id,
        ], $overrides));
    }
}
