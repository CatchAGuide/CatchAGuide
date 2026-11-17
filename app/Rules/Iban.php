<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/** ISO 13616 IBAN: country code, check digits, and the mod-97 checksum. */
class Iban implements ValidationRule
{
    public static function normalize(?string $value): string
    {
        return strtoupper(preg_replace('/\s+/', '', (string) $value));
    }

    /** Groups of four, the way IBANs are printed. */
    public static function format(?string $value): string
    {
        return trim(chunk_split(self::normalize($value), 4, ' '));
    }

    public static function isValid(?string $value): bool
    {
        $iban = self::normalize($value);
        if (! preg_match('/^[A-Z]{2}\d{2}[A-Z0-9]{11,30}$/', $iban)) {
            return false;
        }

        $rearranged = substr($iban, 4).substr($iban, 0, 4);
        $numeric = '';
        foreach (str_split($rearranged) as $char) {
            $numeric .= ctype_alpha($char) ? (string) (ord($char) - 55) : $char;
        }

        // Piecewise mod 97 so the number never overflows an int.
        $remainder = 0;
        foreach (str_split($numeric, 7) as $chunk) {
            $remainder = (int) ($remainder.$chunk) % 97;
        }

        return $remainder === 1;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! self::isValid(is_string($value) ? $value : null)) {
            $fail(__('guide_area.validation.iban'));
        }
    }
}
