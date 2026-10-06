<?php

namespace Tests\Unit\Checkout;

use App\Models\Accommodation;
use App\Models\Camp;
use App\Models\Guiding;
use App\Models\RentalBoat;
use App\Models\SpecialOffer;
use App\Services\Checkout\Camp\CampCheckoutPricing;
use App\Services\Checkout\Camp\CampCheckoutSelection;
use App\Services\Checkout\Camp\StayRate;
use Tests\TestCase;

class CampCheckoutPricingTest extends TestCase
{
    public function test_stay_rate_combines_weeks_and_nights_and_never_exceeds_the_nightly_total(): void
    {
        $rate = StayRate::from(100, 600);

        $this->assertSame(300.0, $rate->total(3));
        $this->assertSame(600.0, $rate->total(7));
        $this->assertSame(800.0, $rate->total(9));

        // A weekly rate that is dearer than 7 nights is ignored.
        $this->assertSame(700.0, StayRate::from(100, 900)->total(7));
    }

    public function test_stay_rate_with_only_a_weekly_price_charges_started_weeks(): void
    {
        $this->assertSame(1800.0, StayRate::from(null, 900)->total(8));
        $this->assertSame(0.0, StayRate::from(null, null)->total(5));
        $this->assertFalse(StayRate::from(0, '')->isPriced());
    }

    public function test_accommodation_uses_the_tier_for_the_party_size_or_the_largest_below(): void
    {
        $pricing = CampCheckoutPricing::for($this->camp());

        $this->assertSame(142.0, $pricing->accommodationRate(1, 1)->daily);
        $this->assertSame(171.0, $pricing->accommodationRate(1, 2)->daily);
        // No tier for 5 guests: the 3-guest tier applies.
        $this->assertSame(199.0, $pricing->accommodationRate(1, 5)->daily);
    }

    public function test_quote_prices_every_selected_option_on_the_server(): void
    {
        $pricing = CampCheckoutPricing::for($this->camp());

        $quote = $pricing->quote(new CampCheckoutSelection(
            nights: 3,
            persons: 2,
            accommodationId: 1,
            rentalBoatId: 10,
            guidingId: 20,
            specialOfferId: 30,
        ));

        $this->assertSame(['accommodation', 'boat', 'tour', 'special'], array_column($quote->lines, 'type'));
        $this->assertSame([513.0, 660.0, 260.0, 90.0], array_column($quote->lines, 'amount'));
        $this->assertSame(1523.0, $quote->total());
        $this->assertSame(10, $quote->lineId('boat'));
    }

    public function test_per_person_night_accommodation_charges_every_guest(): void
    {
        $camp = $this->camp();
        $camp->accommodations->first()->price_unit = 'per_person_night';

        $quote = CampCheckoutPricing::for($camp)->quote(new CampCheckoutSelection(
            nights: 3,
            persons: 2,
            accommodationId: 1,
            rentalBoatId: null,
            guidingId: null,
            specialOfferId: null,
        ));

        // 2-guest tier 171 × 3 nights × 2 guests.
        $this->assertSame(1026.0, $quote->lines[0]['amount']);
        $this->assertSame(342.0, $quote->lines[0]['unit_price']);
        $this->assertSame('per_person_night', CampCheckoutPricing::for($camp)->clientConfig()['accommodations'][0]['unit']);
    }

    public function test_quote_ignores_ids_that_do_not_belong_to_the_camp(): void
    {
        $quote = CampCheckoutPricing::for($this->camp())->quote(new CampCheckoutSelection(
            nights: 2,
            persons: 1,
            accommodationId: 999,
            rentalBoatId: 998,
            guidingId: 997,
            specialOfferId: 996,
        ));

        $this->assertSame([], $quote->lines);
        $this->assertSame(0.0, $quote->total());
    }

    public function test_quote_clamps_nights_and_persons(): void
    {
        $quote = CampCheckoutPricing::for($this->camp())->quote(new CampCheckoutSelection(nights: 400, persons: 0, accommodationId: 1));

        $this->assertSame(CampCheckoutPricing::MAX_NIGHTS, $quote->nights);
        $this->assertSame(1, $quote->persons);
    }

    public function test_tour_price_caps_the_party_at_the_tour_maximum(): void
    {
        $pricing = CampCheckoutPricing::for($this->camp());

        $this->assertSame(130.0, $pricing->tourPrice(20, 1));
        $this->assertSame(390.0, $pricing->tourPrice(20, 8));
    }

    public function test_min_nights_come_from_the_accommodation(): void
    {
        $pricing = CampCheckoutPricing::for($this->camp());

        $this->assertSame(2, $pricing->minNights(1));
        $this->assertSame(1, $pricing->minNights(null));
    }

    private function camp(): Camp
    {
        $accommodation = (new Accommodation)->forceFill([
            'id' => 1,
            'title' => 'Apartment',
            'max_occupancy' => 3,
            'minimum_stay_nights' => 2,
            'per_person_pricing' => [
                'tier_b' => ['person_count' => 2, 'price_per_night' => 171, 'price_per_week' => 1195],
                'tier_a' => ['person_count' => 1, 'price_per_night' => 142, 'price_per_week' => 995],
                'tier_c' => ['person_count' => 3, 'price_per_night' => 199, 'price_per_week' => 1395],
            ],
        ]);

        $boat = (new RentalBoat)->forceFill([
            'id' => 10,
            'title' => 'Alu boat',
            'max_persons' => 3,
            'price_type' => 'per_day',
            'prices' => ['per_day' => 220, 'per_week' => 900],
        ]);

        $guiding = (new Guiding)->forceFill([
            'id' => 20,
            'title' => 'Catfish tour',
            'price_type' => 'per_person',
            'max_guests' => 3,
            'prices' => json_encode([
                ['person' => 1, 'amount' => 130],
                ['person' => 2, 'amount' => 260],
                ['person' => 3, 'amount' => 390],
            ]),
            'pricing_extra' => null,
        ]);

        $special = (new SpecialOffer)->forceFill([
            'id' => 30,
            'title' => 'Airport transfer',
            'pricing' => [['type' => 'fixed', 'amount' => 90, 'currency' => 'EUR']],
        ]);

        $camp = new Camp;
        $camp->setRelation('accommodations', collect([$accommodation]));
        $camp->setRelation('rentalBoats', collect([$boat]));
        $camp->setRelation('guidings', collect([$guiding]));
        $camp->setRelation('specialOffers', collect([$special]));

        return $camp;
    }
}
