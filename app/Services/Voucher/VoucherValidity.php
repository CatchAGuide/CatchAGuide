<?php

namespace App\Services\Voucher;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

/**
 * Valid-until date of a gift voucher: the German statutory limitation period of three years,
 * starting at the end of the year of purchase (bought 16.10.2026 → valid until 31.12.2029).
 */
final class VoucherValidity
{
    public const YEARS = 3;

    public static function until(CarbonInterface $purchasedAt): CarbonImmutable
    {
        return CarbonImmutable::create($purchasedAt->year + self::YEARS, 12, 31)->startOfDay();
    }
}
