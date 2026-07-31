<?php

namespace App\Services\Seo;

/**
 * Maps public paths between .com (en) and .de (de) where prefixes intentionally differ.
 * Same-path surfaces (guidings, vacations, categories) pass through unchanged.
 */
final class LocalePathMapper
{
    private const MAGAZINE_EN = 'fishing-magazine';

    private const MAGAZINE_DE = 'angelmagazin';

    /**
     * Translate a path from one locale to another (path only, no leading slash required).
     */
    public function mapPath(string $path, string $fromLang, string $toLang): string
    {
        $fromLang = strtolower($fromLang);
        $toLang = strtolower($toLang);
        $path = trim($path, '/');

        if ($fromLang === $toLang) {
            return $path;
        }

        $path = $this->normalizeDestinationHub($path);

        $parts = $path === '' ? [] : explode('/', $path);
        if ($parts === []) {
            return '';
        }

        $prefix = $parts[0];
        if ($prefix === self::MAGAZINE_EN || $prefix === self::MAGAZINE_DE) {
            $parts[0] = $toLang === 'de' ? self::MAGAZINE_DE : self::MAGAZINE_EN;

            return implode('/', $parts);
        }

        return $path;
    }

    public function magazinePrefix(string $lang): string
    {
        return strtolower($lang) === 'de' ? self::MAGAZINE_DE : self::MAGAZINE_EN;
    }

    /**
     * Absolute alternate URL for hreflang / language switch.
     */
    public function alternateUrl(string $baseUrl, string $path, string $fromLang, string $toLang): string
    {
        $mapped = $this->mapPath($path, $fromLang, $toLang);
        $baseUrl = rtrim($baseUrl, '/');

        return $mapped === '' ? $baseUrl : $baseUrl . '/' . $mapped;
    }

    private function normalizeDestinationHub(string $path): string
    {
        if ($path === 'destinationen') {
            return 'destination';
        }

        return $path;
    }
}
