<?php

namespace App\Services\Finance;

/**
 * Normalizes multilingual country strings from guidings/bookings to a canonical key and English label.
 * Shared contract for finance dashboards; aligns with legacy matching in FinanceAnalyticsService::countryKey().
 */
class CountryNormalizer
{
    /**
     * Stable key used for grouping and API filters (e.g. germany, spain).
     */
    public function canonicalKey(?string $country): string
    {
        $c = trim((string) $country);
        if ($c === '') {
            return '__unknown__';
        }

        $lower = mb_strtolower($c);

        return match (true) {
            str_contains($lower, 'germany') || str_contains($lower, 'deutschland') || $lower === 'de' => 'germany',
            str_contains($lower, 'netherlands') || str_contains($lower, 'niederlande') || str_contains($lower, 'nederland')
                || str_contains($lower, 'holland') || $lower === 'nl' => 'netherlands',
            str_contains($lower, 'sweden') || str_contains($lower, 'schweden') || $lower === 'se' => 'sweden',
            str_contains($lower, 'spain') || str_contains($lower, 'spanien') || str_contains($lower, 'españa')
                || str_contains($lower, 'espana') || $lower === 'es' => 'spain',
            str_contains($lower, 'italy') || str_contains($lower, 'italien') || str_contains($lower, 'italia') || $lower === 'it' => 'italy',
            str_contains($lower, 'france') || str_contains($lower, 'frankreich') || str_contains($lower, 'frankrijk')
                || $lower === 'fr' => 'france',
            str_contains($lower, 'portugal') || $lower === 'pt' => 'portugal',
            str_contains($lower, 'croatia') || str_contains($lower, 'kroatien') || $lower === 'hr' => 'croatia',
            str_contains($lower, 'norway') || str_contains($lower, 'norwegen') || $lower === 'no' => 'norway',
            str_contains($lower, 'denmark') || str_contains($lower, 'dänemark') || $lower === 'dk' => 'denmark',
            str_contains($lower, 'austria') || str_contains($lower, 'österreich') || $lower === 'at' => 'austria',
            str_contains($lower, 'belgium') || str_contains($lower, 'belgien') || str_contains($lower, 'belgië')
                || str_contains($lower, 'belgie') || $lower === 'be' => 'belgium',
            default => 'c_'.md5($lower),
        };
    }

    /**
     * English display name for filters and charts (doc: normalized country names).
     */
    public function englishLabel(?string $country): string
    {
        $key = $this->canonicalKey($country);

        return match ($key) {
            '__unknown__' => __('admin.finance_analytics.unknown_country'),
            'germany' => 'Germany',
            'netherlands' => 'Netherlands',
            'sweden' => 'Sweden',
            'spain' => 'Spain',
            'italy' => 'Italy',
            'france' => 'France',
            'portugal' => 'Portugal',
            'croatia' => 'Croatia',
            'norway' => 'Norway',
            'denmark' => 'Denmark',
            'austria' => 'Austria',
            'belgium' => 'Belgium',
            default => str_starts_with($key, 'c_')
                ? trim((string) $country) ?: __('admin.finance_analytics.unknown_country')
                : ucfirst($key),
        };
    }

    /**
     * Match filter parameter (normalized English name from API) against a raw DB country string.
     */
    public function matchesFilter(?string $country, string $normalizedEnglish): bool
    {
        return $this->englishLabel($country) === $normalizedEnglish;
    }
}
