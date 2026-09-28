<?php

namespace App\Support;

/**
 * Partial masking for personal data shown on pages reached through an emailed link, so a
 * forwarded or shoulder-surfed page reveals enough for the owner to recognise their details
 * but not enough to reuse them.
 */
final class PiiMask
{
    /** Fixed-width mask: short enough not to wrap, and doesn't reveal the hidden length. */
    private const MASK = '•••••';

    public static function email(?string $email): string
    {
        $email = trim((string) $email);
        if ($email === '') {
            return '';
        }

        [$local, $domain] = array_pad(explode('@', $email, 2), 2, null);

        return mb_substr($local, 0, 1).self::MASK.($domain !== null ? '@'.$domain : '');
    }

    public static function phone(?string $phone): string
    {
        $digits = preg_replace('/\D/', '', (string) $phone);
        if ($digits === '') {
            return '';
        }

        return self::MASK.substr($digits, -3);
    }

    /**
     * "Jonas Keller" → "Jonas K."
     */
    public static function name(?string $first, ?string $last): string
    {
        $first = trim((string) $first);
        $last = trim((string) $last);

        return trim($first.($last !== '' ? ' '.mb_substr($last, 0, 1).'.' : ''));
    }
}
