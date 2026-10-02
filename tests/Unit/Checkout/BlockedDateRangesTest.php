<?php

namespace Tests\Unit\Checkout;

use App\Services\Checkout\BlockedDateRanges;
use PHPUnit\Framework\TestCase;

class BlockedDateRangesTest extends TestCase
{
    public function test_merges_overlapping_and_adjacent_ranges_in_date_order(): void
    {
        $merged = BlockedDateRanges::merge([
            ['from' => '2026-10-10', 'due' => '2026-10-10', 'id' => 7],
            ['from' => '2026-10-01', 'due' => '2026-10-03'],
            ['from' => '2026-10-04', 'due' => '2026-10-04'],
            ['from' => '2026-10-02', 'due' => '2026-10-05'],
            ['from' => '2026-10-11', 'due' => '2026-10-12'],
        ]);

        $this->assertSame([
            ['from' => '2026-10-01', 'due' => '2026-10-05'],
            ['from' => '2026-10-10', 'due' => '2026-10-12'],
        ], $merged);
    }

    public function test_drops_malformed_rows_and_normalizes_reversed_or_timed_values(): void
    {
        $merged = BlockedDateRanges::merge([
            ['from' => 'not-a-date', 'due' => '2026-10-01'],
            ['from' => '2026-11-05 10:00:00', 'due' => '2026-11-03'],
            ['due' => '2026-12-01'],
        ]);

        $this->assertSame([['from' => '2026-11-03', 'due' => '2026-11-05']], $merged);
    }

    public function test_handles_month_boundaries_when_merging_adjacent_days(): void
    {
        $merged = BlockedDateRanges::merge([
            ['from' => '2026-12-31', 'due' => '2026-12-31'],
            ['from' => '2027-01-01', 'due' => '2027-01-02'],
        ]);

        $this->assertSame([['from' => '2026-12-31', 'due' => '2027-01-02']], $merged);
    }
}
