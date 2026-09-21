<?php

namespace Tests\Feature\Guidings;

use App\Models\Guiding;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class GuidingBookingWidgetTest extends TestCase
{
    public function test_desktop_widget_matches_classic_from_price_card(): void
    {
        $html = $this->renderWidget('pages.guidings.content.bookguiding', 3, false);

        $this->assertStringContainsString('id="booking-tour"', $html);
        $this->assertStringContainsString('id="personSelect"', $html);
        $this->assertStringContainsString('form-select', $html);
        $this->assertStringContainsString(__('booking.from'), $html);
        $this->assertStringContainsString(__('booking.per_person_for_a_tour_of'), $html);
        $this->assertStringContainsString(__('booking.contact_us'), $html);
        $this->assertStringContainsString(e(__('booking.reserve_now')), $html);
        $this->assertStringNotContainsString(e(__('booking.choose_date_and_reserve')), $html);
        $this->assertSame('Reserve now', trans('booking.reserve_now', [], 'en'));
        $this->assertSame('Reservieren', trans('booking.reserve_now', [], 'de'));
        $this->assertSame('Choose date & reserve', trans('booking.choose_date_and_reserve', [], 'en'));
        $this->assertSame('Datum wählen & reservieren', trans('booking.choose_date_and_reserve', [], 'de'));
        $this->assertStringContainsString('name="person"', $html);
        $this->assertMatchesRegularExpression(
            '/value="3"[^>]*\bselected\b|\bselected\b[^>]*value="3"/',
            $html
        );
        $this->assertStringContainsString('369€', $html);
        $this->assertStringNotContainsString('data-guidings-book-stepper', $html);
        $this->assertStringNotContainsString('guidings-book-card__cta-arrow', $html);
    }

    public function test_mobile_widget_defaults_to_first_guest_tier(): void
    {
        $html = $this->renderWidget('pages.guidings.content.bookguidingmobile', null, true);

        $this->assertStringContainsString('data-guidings-book-instance="mobile"', $html);
        $this->assertStringContainsString('id="reserveButtonMobile"', $html);
        $this->assertMatchesRegularExpression(
            '/name="person"[^>]*value="1"|value="1"[^>]*name="person"/',
            $html
        );
        $this->assertStringContainsString('1 '.__('booking.person'), $html);
        $this->assertStringContainsString('150€', $html);
        $this->assertMatchesRegularExpression(
            '/data-guidings-book-breakdown[^>]*\bhidden\b|\bhidden\b[^>]*data-guidings-book-breakdown/',
            $html
        );
        $this->assertStringContainsString('data-guidings-booking-widget-script', $html);
        $this->assertStringContainsString(e(__('booking.choose_date_and_reserve')), $html);
        $this->assertStringNotContainsString('>'.e(__('booking.reserve_now')).'<', $html);
    }

    private function renderWidget(string $view, ?int $preselectedGuests, bool $isMobile): string
    {
        $guiding = new Guiding([
            'id' => 99,
            'title' => 'Rhine Perch',
            'slug' => 'rhine-perch',
            'location' => 'Düsseldorf',
            'max_guests' => 4,
            'price_type' => 'per_person',
            'price' => 150,
            'prices' => json_encode([
                ['person' => 1, 'amount' => 150],
                ['person' => 2, 'amount' => 256],
                ['person' => 3, 'amount' => 369],
                ['person' => 4, 'amount' => 480],
            ]),
        ]);
        $guiding->id = 99;

        $agent = new class($isMobile)
        {
            public function __construct(private bool $mobile)
            {
            }

            public function ismobile(): bool
            {
                return $this->mobile;
            }
        };

        return View::make($view, [
            'guiding' => $guiding,
            'agent' => $agent,
            'preselectedGuests' => $preselectedGuests,
        ])->render();
    }
}
