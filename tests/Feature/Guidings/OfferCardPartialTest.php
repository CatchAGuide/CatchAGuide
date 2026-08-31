<?php

namespace Tests\Feature\Guidings;

use Illuminate\Support\Facades\View;
use Tests\TestCase;

class OfferCardPartialTest extends TestCase
{
    /**
     * @return array<string, mixed>
     */
    private function card(array $overrides = []): array
    {
        return array_merge([
            'type' => 'tour',
            'id' => 1,
            'title' => 'Big game fishing off the coast of Marina, Croatia',
            'url' => '/guidings/offer/test-tour',
            'image' => '/images/placeholder_guide.jpg',
            'badge' => 'Fishing tour',
            'location' => 'Marina, Croatia',
            'meta' => '8 hours · Max 5 guests',
            'rating' => null,
            'review_count' => 0,
            'is_new' => true,
            'price_amount' => '€156',
            'price_unit' => 'person',
        ], $overrides);
    }

    private function render(array $card): string
    {
        return View::make('pages.home.partials.offer-card', [
            'card' => $card,
            'type' => $card['type'],
        ])->render();
    }

    public function test_new_tours_show_neu_as_an_image_badge_not_body_copy(): void
    {
        app()->setLocale('de');

        $html = $this->render($this->card());

        $this->assertStringContainsString('cag-home-offer__new-badge', $html);
        $this->assertStringContainsString('cag-home-offer__media', $html);
        $this->assertStringContainsString(__('homepage.landing_card_new'), $html);
        $this->assertStringNotContainsString('cag-home-offer__new"', $html);
        $this->assertStringNotContainsString('cag-home-offer__new ', $html);
        $this->assertMatchesRegularExpression(
            '/cag-home-offer__media[\s\S]+cag-home-offer__new-badge[\s\S]+cag-home-offer__body/',
            $html
        );
    }

    public function test_rated_tours_do_not_show_the_new_image_badge(): void
    {
        $html = $this->render($this->card([
            'is_new' => true,
            'rating' => '4.8',
            'review_count' => 12,
        ]));

        $this->assertStringNotContainsString('cag-home-offer__new-badge', $html);
        $this->assertStringContainsString('cag-home-offer__rating-badge', $html);
    }

    public function test_price_keeps_amount_and_unit_on_one_line(): void
    {
        $html = $this->render($this->card(['is_new' => false]));

        $this->assertStringContainsString('cag-home-offer__price-from', $html);
        $this->assertStringContainsString('cag-home-offer__price-line', $html);
        $this->assertMatchesRegularExpression(
            '/cag-home-offer__price-line[\s\S]+<strong>€156<\/strong>[\s\S]+<small>\/ person<\/small>/',
            $html
        );
    }

    public function test_mobile_cta_uses_the_short_label(): void
    {
        app()->setLocale('de');

        $html = $this->render($this->card(['is_new' => false]));

        $this->assertStringContainsString(__('homepage.offer_details'), $html);
        $this->assertStringContainsString(__('homepage.offer_details_short'), $html);
        $this->assertMatchesRegularExpression(
            '/d-none d-md-inline[\s\S]*Angebot ansehen[\s\S]*d-md-none[\s\S]*Ansehen/',
            $html
        );
    }
}
