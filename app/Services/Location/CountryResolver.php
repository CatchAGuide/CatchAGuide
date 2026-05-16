<?php

namespace App\Services\Location;

use App\Services\Finance\CountryNormalizer;

class CountryResolver
{
    /** @var array<string, string> Canonical finance keys → ISO 3166-1 alpha-2 */
    private const CANONICAL_TO_ISO = [
        'germany' => 'DE',
        'netherlands' => 'NL',
        'sweden' => 'SE',
        'spain' => 'ES',
        'italy' => 'IT',
        'france' => 'FR',
        'portugal' => 'PT',
        'croatia' => 'HR',
        'norway' => 'NO',
        'denmark' => 'DK',
        'austria' => 'AT',
        'belgium' => 'BE',
    ];

    /**
     * Resolve ISO 3166-1 alpha-2 from short code or localized / English country name.
     */
    public function resolveIso(?string $countryShort, ?string $countryName = null): ?string
    {
        if ($countryShort !== null && $countryShort !== '') {
            $code = strtoupper(trim($countryShort));
            if (strlen($code) === 2 && $this->isValidIso($code)) {
                return $code;
            }
        }

        if ($countryName === null || trim($countryName) === '') {
            return null;
        }

        $needle = mb_strtolower(trim($countryName));

        $iso = $this->resolveViaFinanceAliases($needle);
        if ($iso !== null) {
            return $iso;
        }

        $iso = $this->resolveViaSymfonyIntl($needle);
        if ($iso !== null) {
            return $iso;
        }

        return $this->resolveViaPhpIntl($needle);
    }

    public function englishName(?string $iso): ?string
    {
        if ($iso === null || $iso === '') {
            return null;
        }

        $code = strtoupper(trim($iso));
        if (! $this->isValidIso($code)) {
            return null;
        }

        if (class_exists(\Symfony\Component\Intl\Countries::class)) {
            try {
                return \Symfony\Component\Intl\Countries::getName($code, 'en');
            } catch (\Throwable) {
                // fall through
            }
        }

        if (extension_loaded('intl')) {
            $name = \Locale::getDisplayRegion('_'.$code, 'en');
            if ($name !== false && $name !== '') {
                return $name;
            }
        }

        $canonical = array_search($code, self::CANONICAL_TO_ISO, true);
        if ($canonical !== false) {
            return (new CountryNormalizer)->englishLabel($canonical);
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    public function localizedNames(?string $iso): array
    {
        if ($iso === null || ! $this->isValidIso(strtoupper($iso))) {
            return [];
        }

        $code = strtoupper($iso);
        $names = [];
        $locales = config('location_search.locales_for_country_resolve', ['en', 'de', 'es', 'fr']);

        if (class_exists(\Symfony\Component\Intl\Countries::class)) {
            foreach ($locales as $locale) {
                try {
                    $names[] = \Symfony\Component\Intl\Countries::getName($code, $locale);
                } catch (\Throwable) {
                    continue;
                }
            }
        } elseif (extension_loaded('intl')) {
            foreach ($locales as $locale) {
                $display = \Locale::getDisplayRegion('_'.$code, $locale);
                if ($display !== false && $display !== '') {
                    $names[] = $display;
                }
            }
        }

        $english = $this->englishName($code);
        if ($english) {
            $names[] = $english;
        }

        return array_values(array_unique($names));
    }

    private function resolveViaFinanceAliases(string $needle): ?string
    {
        $normalizer = new CountryNormalizer;

        foreach (self::CANONICAL_TO_ISO as $canonical => $iso) {
            $english = $normalizer->englishLabel($canonical);
            if (mb_strtolower($english) === $needle) {
                return $iso;
            }
        }

        $key = $normalizer->canonicalKey($needle);
        if (isset(self::CANONICAL_TO_ISO[$key])) {
            return self::CANONICAL_TO_ISO[$key];
        }

        // Match localized names against each canonical bucket via normalizer
        foreach (self::CANONICAL_TO_ISO as $canonical => $iso) {
            if ($normalizer->canonicalKey($needle) === $canonical) {
                return $iso;
            }
        }

        return null;
    }

    private function resolveViaSymfonyIntl(string $needle): ?string
    {
        if (! class_exists(\Symfony\Component\Intl\Countries::class)) {
            return null;
        }

        $locales = config('location_search.locales_for_country_resolve', ['en', 'de', 'es', 'fr']);

        foreach (\Symfony\Component\Intl\Countries::getCountryCodes() as $code) {
            foreach ($locales as $locale) {
                try {
                    if (mb_strtolower(\Symfony\Component\Intl\Countries::getName($code, $locale)) === $needle) {
                        return $code;
                    }
                } catch (\Throwable) {
                    continue;
                }
            }
        }

        return null;
    }

    private function resolveViaPhpIntl(string $needle): ?string
    {
        if (! extension_loaded('intl')) {
            return null;
        }

        $locales = config('location_search.locales_for_country_resolve', ['en', 'de', 'es', 'fr']);

        foreach ($this->intlCountryCodes() as $code) {
            foreach ($locales as $locale) {
                $display = \Locale::getDisplayRegion('_'.$code, $locale);
                if ($display !== false && mb_strtolower($display) === $needle) {
                    return $code;
                }
            }
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    private function intlCountryCodes(): array
    {
        static $codes = null;
        if ($codes !== null) {
            return $codes;
        }

        $codes = [];
        try {
            $bundle = \ResourceBundle::create('en', 'ICUDATA-region');
            if ($bundle) {
                $countries = $bundle->get('Countries');
                if ($countries instanceof \ResourceBundle) {
                    $codes = array_keys(iterator_to_array($countries));
                }
            }
        } catch (\Throwable) {
            $codes = [];
        }

        if ($codes === []) {
            $codes = array_values(self::CANONICAL_TO_ISO);
        }

        return $codes;
    }

    private function isValidIso(string $code): bool
    {
        if (! preg_match('/^[A-Z]{2}$/', $code)) {
            return false;
        }

        if (class_exists(\Symfony\Component\Intl\Countries::class)) {
            try {
                return \Symfony\Component\Intl\Countries::exists($code);
            } catch (\Throwable) {
                // fall through
            }
        }

        if (extension_loaded('intl')) {
            $name = \Locale::getDisplayRegion('_'.$code, 'en');

            return $name !== false && $name !== '';
        }

        return isset(array_flip(self::CANONICAL_TO_ISO)[$code]);
    }
}
