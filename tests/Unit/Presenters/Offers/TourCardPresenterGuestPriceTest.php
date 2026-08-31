<?php

namespace Tests\Unit\Presenters\Offers;

use App\Models\Guiding;
use App\Presenters\Offers\TourCardPresenter;
use Tests\TestCase;

class TourCardPresenterGuestPriceTest extends TestCase
{
    public function test_list_row_defaults_to_two_guest_total_pricing(): void
    {
        $guiding = $this->guiding([
            'price_type' => 'per_person',
            'prices' => json_encode([
                ['person' => 1, 'amount' => 150],
                ['person' => 2, 'amount' => 369],
            ]),
        ]);

        $card = app(TourCardPresenter::class)->presentListRow($guiding);

        $this->assertNull($card['listing_price_prefix']);
        $this->assertSame('369€', $card['listing_price_display']);
        $this->assertNull($card['listing_price_suffix']);
        $this->assertSame(
            __('offers.price_per_person_for_guests', ['price' => '185€', 'count' => 2]),
            $card['listing_price_note']
        );
    }

    public function test_list_row_shows_guest_total_and_subtle_per_person_note(): void
    {
        $guiding = $this->guiding([
            'price_type' => 'per_person',
            'prices' => json_encode([
                ['person' => 1, 'amount' => 150],
                ['person' => 2, 'amount' => 369],
            ]),
        ]);

        $card = app(TourCardPresenter::class)->presentListRow($guiding, 2);

        $this->assertNull($card['listing_price_prefix']);
        $this->assertSame('369€', $card['listing_price_display']);
        $this->assertNull($card['listing_price_suffix']);
        $this->assertSame(
            __('offers.price_per_person_for_guests', ['price' => '185€', 'count' => 2]),
            $card['listing_price_note']
        );
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function guiding(array $attributes): Guiding
    {
        $guiding = new Guiding(array_merge([
            'id' => 99,
            'title' => 'Test tour',
            'slug' => 'test-tour',
            'location' => 'Berlin',
            'max_guests' => 4,
        ], $attributes));
        $guiding->id = 99;

        return $guiding;
    }
}
