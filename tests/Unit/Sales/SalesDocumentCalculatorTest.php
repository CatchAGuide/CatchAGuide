<?php

namespace Tests\Unit\Sales;

use App\Enums\Sales\SalesItemType;
use App\Services\Sales\SalesDocumentCalculator;
use Carbon\CarbonImmutable;
use Tests\TestCase;

/**
 * Pricing rules of the offer builder (spec §5): the §5.2 tour example and one case per
 * product type, priced from card snapshots alone.
 */
class SalesDocumentCalculatorTest extends TestCase
{
    private SalesDocumentCalculator $calculator;

    private CarbonImmutable $today;

    protected function setUp(): void
    {
        parent::setUp();
        app()->setLocale('en');
        $this->calculator = new SalesDocumentCalculator;
        $this->today = CarbonImmutable::parse('2026-10-06');
    }

    private function tourProduct(array $overrides = []): array
    {
        return array_merge([
            'type' => 'tour',
            'id' => 1042,
            'title' => 'Zander-Guiding am Rhein',
            'location' => 'Düsseldorf, Deutschland',
            'url' => 'https://catchaguide.de/guidings/offer/zander',
            'duration' => '6 Stunden',
            'max' => 4,
            'partner' => ['id' => 7, 'name' => 'Tobias Brandt', 'email' => 'tobias@example.com', 'phone' => ''],
            'prices' => [1 => 240.0, 2 => 290.0, 3 => 340.0, 4 => 390.0],
            'extras' => [
                ['key' => 'e0', 'name' => 'Fishing permit', 'price' => 15.0, 'unit' => 'per_person'],
                ['key' => 'e1', 'name' => 'Rental tackle', 'price' => 25.0, 'unit' => 'per_person'],
                ['key' => 'e2', 'name' => 'Photo package', 'price' => 49.0, 'unit' => 'per_booking'],
                ['key' => 'e3', 'name' => 'Wader rental', 'price' => 10.0, 'unit' => 'per_item'],
            ],
            'translation_missing' => false,
        ], $overrides);
    }

    private function tourCard(array $overrides = []): array
    {
        return array_merge([
            'key' => 'c1',
            'type' => 'tour',
            'listing_id' => 1042,
            'product' => $this->tourProduct(),
            'date' => '2026-11-14',
            'persons' => 3,
            'override' => '',
            'extras' => [],
            'subs' => [],
        ], $overrides);
    }

    private function campCard(array $subs, int $persons = 3): array
    {
        return [
            'key' => 'c2',
            'type' => 'camp',
            'listing_id' => 2011,
            'product' => [
                'type' => 'camp',
                'id' => 2011,
                'title' => 'Mörrum Lodge',
                'location' => 'Mörrum, Schweden',
                'url' => null,
                'partner' => ['id' => 8, 'name' => 'Lars Terkildsen', 'email' => 'lars@example.com', 'phone' => ''],
                'accommodations' => [
                    5101 => ['id' => 5101, 'name' => 'Lodge Pool 15', 'capacity' => 4, 'tiers' => [
                        ['persons' => 1, 'daily' => 100.0, 'weekly' => 600.0],
                        ['persons' => 3, 'daily' => 116.0, 'weekly' => null],
                    ]],
                ],
                'boats' => [7011 => ['id' => 7011, 'name' => 'Aluminium boat 15 hp', 'capacity' => 3, 'daily' => 65.0, 'weekly' => null]],
                'guidings' => [1201 => ['id' => 1201, 'name' => 'Guiding 8 hours', 'capacity' => 3, 'prices' => [1 => 300.0, 2 => 400.0, 3 => 480.0]]],
                'translation_missing' => false,
            ],
            'persons' => $persons,
            'subs' => $subs,
        ];
    }

    private function line(array $quote, string $key): array
    {
        foreach ($quote as $group) {
            foreach ($group['lines'] as $line) {
                if ($line['key'] === $key) {
                    return $line;
                }
            }
        }
        $this->fail("No line {$key}");
    }

    public function test_spec_example_tour_with_extras_totals_455(): void
    {
        // Tour for 3 persons, licence for all 3 (follows), rental tackle for 2 (edited), 2 waders.
        $quote = $this->calculator->calculate([$this->tourCard(['extras' => [
            'e0' => ['qty' => 3, 'follow' => true],
            'e1' => ['qty' => 2, 'follow' => false],
            'e3' => ['qty' => 2, 'follow' => false],
        ]])], 'de', $this->today);

        $group = $quote->groups[0];
        $this->assertSame([340.0, 45.0, 50.0, 20.0], array_column($group['lines'], 'total'));
        $this->assertSame(455.0, $group['subtotal']);
        $this->assertSame(455.0, $quote->total);
        $this->assertSame([false, true, true, true], array_column($group['lines'], 'extra'));
        $this->assertSame(SalesItemType::TourExtra, $group['lines'][1]['item_type']);
        $this->assertSame('3 Personen × 15,00 €', $group['lines'][1]['detail']);
        $this->assertSame([], $group['errors']);
        $this->assertSame([], $group['warnings']);
    }

    public function test_following_extra_takes_the_tour_persons_and_edited_one_keeps_its_count(): void
    {
        $quote = $this->calculator->calculate([$this->tourCard(['persons' => 4, 'extras' => [
            'e0' => ['qty' => 3, 'follow' => true],
            'e1' => ['qty' => 2, 'follow' => false],
        ]])], 'en', $this->today);

        $this->assertSame(60.0, $this->line($quote->groups, 'c1-xe0')['total']);
        $this->assertSame(50.0, $this->line($quote->groups, 'c1-xe1')['total']);
    }

    public function test_booking_extra_is_charged_once(): void
    {
        $quote = $this->calculator->calculate([$this->tourCard(['extras' => ['e2' => ['qty' => 5, 'follow' => false]]])], 'en', $this->today);

        $this->assertSame(49.0, $this->line($quote->groups, 'c1-xe2')['total']);
    }

    public function test_tour_above_maximum_uses_the_maximum_price_and_warns(): void
    {
        $quote = $this->calculator->calculate([$this->tourCard(['persons' => 6])], 'en', $this->today);

        $this->assertSame(390.0, $quote->total);
        $this->assertNotEmpty($quote->groups[0]['warnings']);
    }

    public function test_extra_with_more_persons_than_the_tour_warns(): void
    {
        $quote = $this->calculator->calculate([$this->tourCard(['extras' => ['e1' => ['qty' => 5, 'follow' => false]]])], 'en', $this->today);

        $this->assertCount(1, $quote->groups[0]['warnings']);
    }

    public function test_manual_price_replaces_only_the_base_price(): void
    {
        $quote = $this->calculator->calculate([$this->tourCard([
            'override' => '300,50',
            'extras' => ['e0' => ['qty' => 3, 'follow' => true]],
        ])], 'en', $this->today);

        $base = $this->line($quote->groups, 'c1');
        $this->assertTrue($base['adjusted']);
        $this->assertSame(340.0, $base['calculated']);
        $this->assertSame(300.5, $base['total']);
        $this->assertSame(345.5, $quote->total);
    }

    public function test_tour_without_date_or_price_blocks_sending(): void
    {
        $quote = $this->calculator->calculate([$this->tourCard([
            'date' => '',
            'product' => $this->tourProduct(['prices' => [1 => 0.0, 2 => 0.0, 3 => 0.0, 4 => 0.0]]),
        ])], 'en', $this->today);

        $this->assertCount(2, $quote->errors());
    }

    public function test_accommodation_uses_the_party_size_tier_times_nights_and_units(): void
    {
        $quote = $this->calculator->calculate([$this->campCard([
            ['key' => 's1', 'kind' => 'accommodation', 'option_id' => 5101, 'from' => '2026-11-10', 'to' => '2026-11-14', 'qty' => 2, 'override' => ''],
        ])], 'en', $this->today);

        $line = $this->line($quote->groups, 's1');
        // 3 persons → the 3-person tier (116/night), 4 nights, 2 units.
        $this->assertSame(928.0, $line['total']);
        $this->assertSame('2026-11-10', $quote->travelFrom);
        $this->assertSame('2026-11-14', $quote->travelTo);
    }

    public function test_accommodation_week_rate_applies_from_seven_nights(): void
    {
        $quote = $this->calculator->calculate([$this->campCard([
            ['key' => 's1', 'kind' => 'accommodation', 'option_id' => 5101, 'from' => '2026-11-01', 'to' => '2026-11-09', 'qty' => 1, 'override' => ''],
        ], 1)], 'en', $this->today);

        // 8 nights at the 1-person tier: one week (600) + one night (100).
        $line = $this->line($quote->groups, 's1');
        $this->assertSame(700.0, $line['total']);
        $this->assertSame('1 × 600.00/week + 1 n × 100.00 × 1', $line['formula']);
    }

    public function test_camp_without_options_warns(): void
    {
        $quote = $this->calculator->calculate([$this->campCard([])], 'en', $this->today);

        $this->assertSame(0, $quote->lineCount());
        $this->assertCount(1, $quote->warnings());
    }

    public function test_checkout_before_checkin_blocks_and_capacity_warns(): void
    {
        $quote = $this->calculator->calculate([$this->campCard([
            ['key' => 's1', 'kind' => 'accommodation', 'option_id' => 5101, 'from' => '2026-11-14', 'to' => '2026-11-10', 'qty' => 1, 'override' => ''],
        ], 6)], 'en', $this->today);

        $this->assertNotEmpty($quote->errors());
        $this->assertNotEmpty($quote->warnings());
    }

    public function test_rental_boat_is_price_per_day_times_days_times_boats_and_warns_beyond_the_stay(): void
    {
        $quote = $this->calculator->calculate([$this->campCard([
            ['key' => 's1', 'kind' => 'accommodation', 'option_id' => 5101, 'from' => '2026-11-10', 'to' => '2026-11-12', 'qty' => 1, 'override' => ''],
            ['key' => 's2', 'kind' => 'boat', 'option_id' => 7011, 'days' => 4, 'qty' => 2, 'override' => ''],
        ])], 'en', $this->today);

        $this->assertSame(520.0, $this->line($quote->groups, 's2')['total']);
        $this->assertNull($this->line($quote->groups, 's2')['from']);
        // The stay covers 3 days, the boat 4.
        $this->assertCount(1, $quote->warnings());
    }

    public function test_camp_guiding_is_the_group_price_times_number_of_guidings(): void
    {
        $quote = $this->calculator->calculate([$this->campCard([
            ['key' => 's1', 'kind' => 'accommodation', 'option_id' => 5101, 'from' => '2026-11-10', 'to' => '2026-11-12', 'qty' => 1, 'override' => ''],
            ['key' => 's3', 'kind' => 'guiding', 'option_id' => 1201, 'date' => '2026-11-20', 'qty' => 2, 'override' => ''],
        ])], 'en', $this->today);

        $this->assertSame(960.0, $this->line($quote->groups, 's3')['total']);
        // The guiding date lies outside the stay.
        $this->assertCount(1, $quote->warnings());
    }

    public function test_trip_is_price_per_person_with_derived_end_date_and_group_size_warnings(): void
    {
        $card = [
            'key' => 'c3',
            'type' => 'trip',
            'listing_id' => 3005,
            'product' => [
                'type' => 'trip', 'id' => 3005, 'title' => 'Cod & halibut week Hitra', 'location' => 'Hitra, Norwegen', 'url' => null,
                'nights' => 7, 'price' => 1290.0, 'min' => 2, 'max' => 6, 'inclusions' => ['7 nights house', 'Boat'],
                'partner' => null, 'translation_missing' => false,
            ],
            'date' => '2026-11-01',
            'persons' => 1,
            'override' => '',
        ];

        $quote = $this->calculator->calculate([$card], 'en', $this->today);

        $line = $this->line($quote->groups, 'c3');
        $this->assertSame(1290.0, $line['total']);
        $this->assertSame('2026-11-08', $line['to']);
        $this->assertSame(['7 nights house', 'Boat'], $quote->groups[0]['inclusions']);
        $this->assertCount(1, $quote->warnings());
    }

    public function test_custom_line_is_unit_price_times_quantity_and_needs_a_title(): void
    {
        $card = ['key' => 'c4', 'type' => 'custom', 'title' => '', 'description' => 'Airport', 'date' => '', 'quantity' => 3, 'unit_label' => 'per person', 'unit_price' => '45,00'];

        $quote = $this->calculator->calculate([$card], 'en', $this->today);

        $this->assertSame(135.0, $quote->total);
        $this->assertCount(1, $quote->errors());
        $this->assertNull($quote->travelFrom);
    }

    public function test_past_dates_and_missing_translations_warn(): void
    {
        $quote = $this->calculator->calculate([$this->tourCard([
            'date' => '2026-01-01',
            'product' => $this->tourProduct(['translation_missing' => true]),
        ])], 'en', $this->today);

        $this->assertCount(2, $quote->warnings());
    }

    public function test_partners_and_travel_period_across_cards(): void
    {
        $quote = $this->calculator->calculate([
            $this->tourCard(),
            $this->campCard([['key' => 's1', 'kind' => 'accommodation', 'option_id' => 5101, 'from' => '2026-11-10', 'to' => '2026-11-12', 'qty' => 1, 'override' => '']]),
            $this->tourCard(['key' => 'c9']),
        ], 'en', $this->today);

        $this->assertSame([7, 8], array_column($quote->partners(), 'id'));
        $this->assertSame('2026-11-10', $quote->travelFrom);
        $this->assertSame('2026-11-14', $quote->travelTo);
        $this->assertSame(3, $quote->lineCount());
    }
}
