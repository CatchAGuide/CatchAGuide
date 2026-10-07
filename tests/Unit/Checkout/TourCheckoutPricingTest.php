<?php

namespace Tests\Unit\Checkout;

use App\Models\Guiding;
use App\Services\Checkout\TourCheckoutPricing;
use Tests\TestCase;

class TourCheckoutPricingTest extends TestCase
{
    private function guiding(array $attributes): Guiding
    {
        return (new Guiding())->forceFill(array_merge([
            'max_guests' => 4,
            'price' => 0,
            'price_per_person' => 0,
            'prices' => null,
            'pricing_extra' => null,
        ], $attributes));
    }

    private function extras(): string
    {
        return json_encode([
            ['name' => 'Fishing licence', 'price' => 25],
            ['name' => 'Lunch', 'price' => '12.5'],
        ]);
    }

    public function test_per_person_tier_is_the_tour_total_for_that_guest_count(): void
    {
        $pricing = TourCheckoutPricing::for($this->guiding([
            'price_type' => 'per_person',
            'prices' => json_encode([['person' => 1, 'amount' => 200], ['person' => 2, 'amount' => 350]]),
        ]));

        $this->assertSame(200.0, $pricing->basePrice(1));
        $this->assertSame(350.0, $pricing->basePrice(2));
    }

    public function test_per_person_without_matching_tier_scales_the_last_tier(): void
    {
        $pricing = TourCheckoutPricing::for($this->guiding([
            'price_type' => 'per_person',
            'prices' => json_encode([['person' => 1, 'amount' => 200], ['person' => 2, 'amount' => 350]]),
        ]));

        $this->assertSame(1050.0, $pricing->basePrice(3));
    }

    public function test_per_person_without_tiers_uses_price_per_person(): void
    {
        $pricing = TourCheckoutPricing::for($this->guiding(['price_type' => 'per_person', 'price_per_person' => 90]));

        $this->assertSame(270.0, $pricing->basePrice(3));
    }

    public function test_fixed_price_ignores_guest_count(): void
    {
        $pricing = TourCheckoutPricing::for($this->guiding(['price_type' => 'per_tour', 'price' => 480]));

        $this->assertSame(480.0, $pricing->basePrice(1));
        $this->assertSame(480.0, $pricing->basePrice(4));
    }

    public function test_price_table_covers_every_bookable_guest_count(): void
    {
        $pricing = TourCheckoutPricing::for($this->guiding(['price_type' => 'per_tour', 'price' => 300, 'max_guests' => 3]));

        $this->assertSame([1 => 300.0, 2 => 300.0, 3 => 300.0], $pricing->priceTable());
    }

    public function test_missing_max_guests_falls_back_to_ten(): void
    {
        $pricing = TourCheckoutPricing::for($this->guiding(['price_type' => 'per_tour', 'price' => 300, 'max_guests' => null]));

        $this->assertSame(10, $pricing->maxGuests());
    }

    public function test_quote_prices_selected_extras_per_person_and_ignores_unknown_positions(): void
    {
        $pricing = TourCheckoutPricing::for($this->guiding([
            'price_type' => 'per_tour',
            'price' => 400,
            'pricing_extra' => $this->extras(),
        ]));

        $quote = $pricing->quote(3, [1, 7]);

        $this->assertSame(400.0, $quote->basePrice);
        $this->assertCount(1, $quote->extras);
        $this->assertSame('Lunch', $quote->extras[0]['name']);
        $this->assertSame(37.5, $quote->extrasTotal());
        $this->assertSame(437.5, $quote->total());
    }

    public function test_quote_charges_booking_and_item_extras_once(): void
    {
        $pricing = TourCheckoutPricing::for($this->guiding([
            'price_type' => 'per_tour',
            'price' => 400,
            'pricing_extra' => json_encode([
                ['name' => 'Licence', 'price' => 15, 'unit' => 'per_person'],
                ['name' => 'Photo package', 'price' => 49, 'unit' => 'per_booking'],
                ['name' => 'Wader', 'price' => 10, 'unit' => 'per_item'],
                ['name' => 'Legacy', 'price' => 5, 'unit' => 'bogus'],
            ]),
        ]));

        $this->assertSame(['per_person', 'per_booking', 'per_item', 'per_person'], array_column($pricing->extras(), 'unit'));

        $quote = $pricing->quote(3, [0, 1, 2, 3]);

        $this->assertSame([3, 1, 1, 3], array_column($quote->extras, 'quantity'));
        $this->assertSame(45.0 + 49.0 + 10.0 + 15.0, $quote->extrasTotal());
    }

    public function test_quote_clamps_guests_to_the_bookable_range(): void
    {
        $pricing = TourCheckoutPricing::for($this->guiding(['price_type' => 'per_tour', 'price' => 100, 'max_guests' => 2]));

        $this->assertSame(2, $pricing->quote(9, [])->persons);
        $this->assertSame(1, $pricing->quote(0, [])->persons);
    }

    public function test_serialized_extras_keep_the_booking_storage_shape(): void
    {
        $pricing = TourCheckoutPricing::for($this->guiding([
            'price_type' => 'per_tour',
            'price' => 100,
            'pricing_extra' => $this->extras(),
        ]));

        $this->assertNull($pricing->quote(2, [])->serializedExtras());

        $stored = unserialize($pricing->quote(2, [0])->serializedExtras());

        $this->assertSame([[
            'extra_id' => 0,
            'extra_name' => 'Fishing licence',
            'extra_price' => 25.0,
            'extra_quantity' => 2,
            'extra_total_price' => 50.0,
        ]], $stored);
    }
}
