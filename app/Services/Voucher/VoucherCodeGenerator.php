<?php

namespace App\Services\Voucher;

use App\Models\VoucherOrder;
use RuntimeException;

/**
 * Voucher codes in the form CAG-V-7KQ3PX: six characters from an alphabet without the easily
 * confused 0/O and 1/I/L, unique in voucher_orders.
 */
class VoucherCodeGenerator
{
    public const PREFIX = 'CAG-V-';

    private const ALPHABET = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';

    private const LENGTH = 6;

    private const MAX_ATTEMPTS = 10;

    public function generate(): string
    {
        for ($attempt = 0; $attempt < self::MAX_ATTEMPTS; $attempt++) {
            $code = self::PREFIX.$this->randomSuffix();

            if (! VoucherOrder::where('voucher_code', $code)->exists()) {
                return $code;
            }
        }

        throw new RuntimeException('Could not generate a unique voucher code.');
    }

    private function randomSuffix(): string
    {
        $suffix = '';
        $max = strlen(self::ALPHABET) - 1;

        for ($i = 0; $i < self::LENGTH; $i++) {
            $suffix .= self::ALPHABET[random_int(0, $max)];
        }

        return $suffix;
    }
}
