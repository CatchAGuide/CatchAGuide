<?php

namespace Tests\Unit\Presenters\Offers;

use App\Models\Guiding;
use App\Presenters\Offers\TourCardPresenter;
use Tests\TestCase;

class TourCardPresenterGuestPriceTest extends TestCase
{
    public function test_list_row_defaults_to_one_guest_total_pricing(): void
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
        $this->assertSame('150€', $card['listing_price_display']);
        $this->assertNull($card['listing_price_suffix']);
        $this->assertSame(
            __('offers.price_per_person_for_guests', ['price' => '150€', 'count' => 1]),
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

    public function test_list_row_url_keeps_search_place_and_guests(): void
    {
        $guiding = $this->guiding([
            'price_type' => 'per_person',
            'prices' => json_encode([
                ['person' => 1, 'amount' => 150],
                ['person' => 3, 'amount' => 369],
            ]),
        ]);

        $card = app(TourCardPresenter::class)->presentListRow($guiding, 3, [
            'place' => 'Düsseldorf, Deutschland',
            'placeLat' => 51.2277,
            'placeLng' => 6.7735,
            'city' => 'Düsseldorf',
            'country' => 'germany',
            'num_guests' => 3,
        ]);

        $query = [];
        parse_str((string) parse_url($card['url'], PHP_URL_QUERY), $query);

        $this->assertSame('test-tour', basename((string) parse_url($card['url'], PHP_URL_PATH)));
        $this->assertSame('3', $query['num_guests']);
        $this->assertSame('Düsseldorf, Deutschland', $query['place']);
        $this->assertSame('51.2277', $query['placeLat']);
        $this->assertSame('6.7735', $query['placeLng']);
        $this->assertSame('Düsseldorf', $query['city']);
        $this->assertSame('germany', $query['country']);
    }

    public function test_present_without_query_keeps_a_bare_product_url(): void
    {
        $guiding = $this->guiding([]);

        $card = app(TourCardPresenter::class)->present($guiding);

        $this->assertSame(
            route('guidings.show', ['slug' => 'test-tour']),
            $card['url']
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
