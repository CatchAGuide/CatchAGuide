<?php

namespace App\Services\Sales;

use Carbon\CarbonImmutable;
use Throwable;

/**
 * Money and date formatting for sales documents in the document language
 * ("340,00 €" / "€340.00", "14.11.2026" / "14 Nov 2026").
 */
final class SalesFormat
{
    public static function money(float|int|string|null $amount, string $locale): string
    {
        $value = round((float) $amount, 2);

        return $locale === 'de'
            ? number_format($value, 2, ',', '.').' €'
            : '€'.number_format($value, 2, '.', ',');
    }

    public static function date(?string $date, string $locale): string
    {
        $parsed = self::parse($date);
        if ($parsed === null) {
            return '–';
        }

        return $locale === 'de' ? $parsed->format('d.m.Y') : $parsed->locale('en')->translatedFormat('j M Y');
    }

    public static function parse(?string $date): ?CarbonImmutable
    {
        if ($date === null || trim($date) === '') {
            return null;
        }

        try {
            return CarbonImmutable::createFromFormat('!Y-m-d', substr(trim($date), 0, 10)) ?: null;
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Parses an amount typed by an employee ("340", "340,50", "1.290,00"); null when empty or invalid.
     */
    public static function amount(mixed $value): ?float
    {
        $text = str_replace([' ', '€'], '', trim((string) $value));
        if ($text === '') {
            return null;
        }

        if (str_contains($text, ',')) {
            $text = str_replace(['.', ','], ['', '.'], $text);
        }

        return is_numeric($text) && (float) $text >= 0 ? round((float) $text, 2) : null;
    }
}
