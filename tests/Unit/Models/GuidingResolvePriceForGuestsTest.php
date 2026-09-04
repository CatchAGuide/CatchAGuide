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

    public function test_booking_guest_count_selects_matching_per_person_tier(): void
    {
        $guiding = new Guiding([
            'price_type' => 'per_person',
            'max_guests' => 4,
            'prices' => json_encode([
                ['person' => 1, 'amount' => 150],
                ['person' => 2, 'amount' => 256],
                ['person' => 3, 'amount' => 369],
                ['person' => 4, 'amount' => 480],
            ]),
        ]);

        $this->assertSame(3, $guiding->resolveBookingGuestCount(3));
        $this->assertNull($guiding->resolveBookingGuestCount(null));
        $this->assertNull($guiding->resolveBookingGuestCount(0));
    }

    public function test_booking_guest_count_falls_back_to_next_tier_and_clamps_to_max(): void
    {
        $guiding = new Guiding([
            'price_type' => 'per_person',
            'max_guests' => 4,
            'prices' => json_encode([
                ['person' => 1, 'amount' => 150],
                ['person' => 4, 'amount' => 480],
            ]),
        ]);

        $this->assertSame(4, $guiding->resolveBookingGuestCount(3));

        $fixed = new Guiding();
        $fixed->forceFill([
            'price_type' => 'per_boat',
            'price' => 549,
            'max_guests' => 4,
            'min_guests' => 2,
        ]);

        $this->assertSame(4, $fixed->resolveBookingGuestCount(8));
        $this->assertSame(2, $fixed->resolveBookingGuestCount(1));
    }
}
