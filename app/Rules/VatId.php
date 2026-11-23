<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * VAT ID format per country (spec 13.6; the VIES online check is P2). An ID that starts with a
 * known country prefix must match that country's format. Guides in an EU country must use the
 * prefix; elsewhere a plain tax number of 4–20 letters/digits is accepted.
 */
class VatId implements ValidationRule
{
    /** Prefix => number format after the prefix. Greece uses EL, Northern Ireland XI. */
    private const FORMATS = [
        'AT' => 'U\d{8}',
        'BE' => '[01]\d{9}',
        'BG' => '\d{9,10}',
        'CY' => '\d{8}[A-Z]',
        'CZ' => '\d{8,10}',
        'DE' => '\d{9}',
        'DK' => '\d{8}',
        'EE' => '\d{9}',
        'EL' => '\d{9}',
        'ES' => '[A-Z0-9]\d{7}[A-Z0-9]',
        'FI' => '\d{8}',
        'FR' => '[A-HJ-NP-Z0-9]{2}\d{9}',
        'HR' => '\d{11}',
        'HU' => '\d{8}',
        'IE' => '\d{7}[A-W][A-I]?|\d[A-Z+*]\d{5}[A-W]',
        'IT' => '\d{11}',
        'LT' => '\d{9}|\d{12}',
        'LU' => '\d{8}',
        'LV' => '\d{11}',
        'MT' => '\d{8}',
        'NL' => '\d{9}B\d{2}',
        'PL' => '\d{10}',
        'PT' => '\d{9}',
        'RO' => '\d{2,10}',
        'SE' => '\d{12}',
        'SI' => '\d{8}',
        'SK' => '\d{10}',
        'XI' => '\d{9}|\d{12}|GD\d{3}|HA\d{3}',
        'GB' => '\d{9}|\d{12}|GD\d{3}|HA\d{3}',
        'CHE' => '\d{9}(MWST|TVA|IVA)?',
        'NO' => '\d{9}(MVA)?',
    ];

    /** EU member states: a VAT ID there always carries the prefix. */
    private const EU = ['AT', 'BE', 'BG', 'CY', 'CZ', 'DE', 'DK', 'EE', 'GR', 'ES', 'FI', 'FR', 'HR', 'HU', 'IE', 'IT', 'LT', 'LU', 'LV', 'MT', 'NL', 'PL', 'PT', 'RO', 'SE', 'SI', 'SK'];

    public function __construct(private readonly ?string $country = null) {}

    /** Upper case without spaces, dots and dashes ("de 123.456.789" → "DE123456789"). */
    public static function normalize(?string $value): string
    {
        return strtoupper(preg_replace('/[\s.\-]+/', '', (string) $value));
    }

    public static function isValid(?string $value, ?string $country = null): bool
    {
        $vat = self::normalize($value);
        if ($vat === '') {
            return false;
        }

        $prefixed = false;
        foreach (self::FORMATS as $prefix => $format) {
            if (str_starts_with($vat, $prefix)) {
                if (preg_match('/^'.$prefix.'(?:'.$format.')$/', $vat)) {
                    return true;
                }
                $prefixed = true;
            }
        }

        // It names a country but doesn't fit that country's format.
        if ($prefixed) {
            return false;
        }

        // EU VAT IDs always carry the prefix; elsewhere accept a plain tax number.
        if (in_array(strtoupper((string) $country), self::EU, true)) {
            return false;
        }

        return (bool) preg_match('/^[A-Z0-9]{4,20}$/', $vat);
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! self::isValid(is_string($value) ? $value : null, $this->country)) {
            $fail(__('guide_area.validation.vat_id'));
        }
    }
}
