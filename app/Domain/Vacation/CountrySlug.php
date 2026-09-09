<?php

namespace App\Domain\Vacation;

use App\Services\Location\CountryResolver;

/**
 * Canonical country URL segment helper.
 *
 * Listings often store Google Places names ("Costa Rica") while destination
 * category pages use hyphenated slugs ("costa-rica"). Matching must treat those
 * as the same country without stripping locale-specific characters (ä, ö, ü).
 *
 * Vacation rows may also store localized labels ("Spanien") while Places search
 * submits English ("Spain") — storageVariants() expands ISO locale aliases.
 */
final class CountrySlug
{
    public static function canonicalize(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $value = trim(self::decode($value));
        if ($value === '') {
            return null;
        }

        $value = mb_strtolower($value, 'UTF-8');
        $value = preg_replace('/[\s_]+/u', '-', $value) ?? $value;
        $value = preg_replace('/-+/', '-', $value) ?? $value;
        $value = trim($value, '-');

        return $value === '' ? null : $value;
    }

    /**
     * Whether a URL segment still needs a 301 to its canonical form.
     * Percent-encoding alone must not trigger a redirect (avoids loops on
     * UTF-8 slugs like österreich / %C3%B6sterreich).
     */
    public static function needsCanonicalRedirect(string $segment): bool
    {
        $canonical = self::canonicalize($segment);

        return $canonical !== null && self::decode($segment) !== $canonical;
    }

    /**
     * Values that may appear in trips.country / camps.country for this slug.
     *
     * @return list<string>
     */
    public static function storageVariants(string $slug, ?string $countryShort = null): array
    {
        $canonical = self::canonicalize($slug) ?? mb_strtolower(trim(self::decode($slug)), 'UTF-8');
        $spaced = str_replace('-', ' ', $canonical);

        $variants = [
            $canonical,
            $spaced,
        ];

        foreach (self::localeAliases($spaced, $countryShort) as $alias) {
            $variants[] = $alias;
            $canonicalAlias = self::canonicalize($alias);
            if ($canonicalAlias !== null) {
                $variants[] = $canonicalAlias;
                $variants[] = str_replace('-', ' ', $canonicalAlias);
            }
        }

        return array_values(array_unique(array_filter(
            $variants,
            static fn ($value) => is_string($value) && $value !== ''
        )));
    }

    /**
     * @return list<string>
     */
    private static function localeAliases(string $countryName, ?string $countryShort = null): array
    {
        try {
            $resolver = app(CountryResolver::class);
        } catch (\Throwable) {
            return [];
        }

        $iso = $resolver->resolveIso($countryShort, $countryName);
        if ($iso === null) {
            return [];
        }

        $names = $resolver->localizedNames($iso);
        $english = $resolver->englishName($iso);
        if ($english) {
            $names[] = $english;
        }
        $names[] = $iso;

        return array_values(array_unique(array_filter(
            $names,
            static fn ($value) => is_string($value) && $value !== ''
        )));
    }

    public static function decode(string $value): string
    {
        $previous = null;
        $current = $value;
        while ($current !== $previous) {
            $previous = $current;
            $current = rawurldecode($current);
        }

        return $current;
    }
}
