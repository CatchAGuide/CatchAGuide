<?php

namespace App\Services\Voucher;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

/**
 * Valid-until date of a gift voucher: two years, running to the end of the year
 * (bought 16.10.2026 → valid until 31.12.2028). Trello #972 shortened it from three years;
 * orders keep the date stored when they were placed, so older vouchers stay at three.
 */
final class VoucherValidity
{
    public const YEARS = 2;

    public static function until(CarbonInterface $purchasedAt): CarbonImmutable
    {
        return CarbonImmutable::create($purchasedAt->year + self::YEARS, 12, 31)->startOfDay();
    }
}
