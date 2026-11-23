<?php

namespace App\Support;

use Locale;
use ResourceBundle;

/**
 * ISO 3166-1 alpha-2 countries for address forms (names in the visitor's language, from PHP's
 * intl / ICU data), plus a lookup that turns the free-text countries older profiles stored
 * ("Deutschland", "Italy ", "Frankrike") into codes.
 */
class CountryList
{
    /** ICU region codes that aren't countries (continents, EU, test and unknown regions). */
    private const NOT_COUNTRIES = ['AC', 'CP', 'DG', 'EA', 'EU', 'EZ', 'IC', 'QO', 'TA', 'UN', 'XA', 'XB', 'ZZ'];

    /** Languages whose country names are tried when reading an old free-text value. */
    private const NAME_LOCALES = ['en', 'de', 'nl', 'fr', 'es', 'it', 'pt', 'da', 'sv', 'nb', 'fi', 'pl'];

    /** Short forms the ICU names don't cover. */
    private const ALIASES = ['usa' => 'US', 'uk' => 'GB', 'england' => 'GB', 'scotland' => 'GB', 'schottland' => 'GB', 'holland' => 'NL'];

    /** @var array<string, array<string, string>> */
    private static array $names = [];

    /** @var array<string, string>|null */
    private static ?array $lookup = null;

    /** @return array<string, string> code => name, sorted by name */
    public static function options(?string $locale = null): array
    {
        $names = self::names($locale ?? app()->getLocale());
        asort($names, SORT_LOCALE_STRING);

        return $names;
    }

    /** @return array<int, string> every valid code */
    public static function codes(): array
    {
        return array_keys(self::names('en'));
    }

    public static function name(?string $code, ?string $locale = null): ?string
    {
        $code = strtoupper(trim((string) $code));

        return self::names($locale ?? app()->getLocale())[$code] ?? null;
    }

    /** The ISO code for a stored value (a code or a country name in a common language), or null. */
    public static function normalize(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $upper = strtoupper($value);
        if (strlen($upper) === 2 && isset(self::names('en')[$upper])) {
            return $upper;
        }

        return self::lookup()[self::key($value)] ?? null;
    }

    /** @return array<string, string> code => name in one language */
    private static function names(string $locale): array
    {
        if (! isset(self::$names[$locale])) {
            $names = [];
            $bundle = ResourceBundle::create($locale, 'ICUDATA-region');
            foreach (($bundle['Countries'] ?? []) as $code => $name) {
                if (preg_match('/^[A-Z]{2}$/', (string) $code) && ! in_array($code, self::NOT_COUNTRIES, true)) {
                    $names[$code] = (string) $name;
                }
            }
            // Fallback when the ICU data has no region bundle for this locale.
            if ($names === [] && $locale !== 'en') {
                $names = self::names('en');
                foreach ($names as $code => $name) {
                    $names[$code] = Locale::getDisplayRegion('-'.$code, $locale) ?: $name;
                }
            }
            self::$names[$locale] = $names;
        }

        return self::$names[$locale];
    }

    /** @return array<string, string> normalized name => code */
    private static function lookup(): array
    {
        if (self::$lookup === null) {
            self::$lookup = [];
            foreach (self::NAME_LOCALES as $locale) {
                foreach (self::names($locale) as $code => $name) {
                    self::$lookup[self::key($name)] ??= $code;
                }
            }
            foreach (self::ALIASES as $alias => $code) {
                self::$lookup[$alias] ??= $code;
            }
        }

        return self::$lookup;
    }

    private static function key(string $name): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/u', ' ', $name)));
    }
}
