<?php

namespace Tests\Unit\Models;

use App\Models\Guiding;
use Tests\TestCase;

class GuidingResolvePriceForGuestsTest extends TestCase
{
    public function test_per_person_tier_returns_total_and_per_person_rate(): void
    {
        $guiding = new Guiding([
            'price_type' => 'per_person',
            'prices' => json_encode([
                ['person' => 1, 'amount' => 150],
                ['person' => 2, 'amount' => 256],
                ['person' => 4, 'amount' => 480],
            ]),
        ]);

        $resolved = $guiding->resolvePriceForGuests(2);

        $this->assertSame([
            'total' => 256,
            'per_person' => 128,
            'guests' => 2,
            'is_fixed' => false,
        ], $resolved);
    }

    public function test_per_person_falls_back_to_next_available_tier(): void
    {
        $guiding = new Guiding([
            'price_type' => 'per_person',
            'prices' => json_encode([
                ['person' => 1, 'amount' => 150],
                ['person' => 4, 'amount' => 480],
            ]),
        ]);

        $resolved = $guiding->resolvePriceForGuests(3);

        $this->assertSame(4, $resolved['guests']);
        $this->assertSame(480, $resolved['total']);
        $this->assertSame(120, $resolved['per_person']);
    }

    public function test_fixed_price_divides_across_selected_guests(): void
    {
        $guiding = new Guiding([
            'price_type' => 'per_boat',
            'price' => 549,
            'max_guests' => 6,
        ]);

        $resolved = $guiding->resolvePriceForGuests(4);

        $this->assertSame([
            'total' => 549,
            'per_person' => 137,
            'guests' => 4,
            'is_fixed' => true,
        ], $resolved);
    }
}
