<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $casts = [
        'translation' => 'array',
    ];

    protected $fillable = [
        'city',
        'country',
        'translation',
        'region',
    ];

    public function getTranslationAttribute($value)
    {
        return json_decode($value, true);
    }

    public function scopeSearchTranslation($query, $searchString)
    {
        return $query->whereRaw('JSON_SEARCH(translation, "one", ?) IS NOT NULL', [$searchString]);
    }

    /**
     * Resolve navbar / search inputs to English city, country, region using DB only (no Google API).
     */
    public static function resolveEnglishNames(
        ?string $city,
        ?string $country,
        ?string $region,
        ?string $countryShort = null,
        ?string $regionShort = null
    ): ?array {
        $cacheKey = 'location_en_resolve_' . md5(json_encode([
            $city, $country, $region, $countryShort, $regionShort,
        ]));

        return \Cache::remember($cacheKey, 3600, function () use ($city, $country, $region, $countryShort, $regionShort) {
            $englishCountry = $countryShort ? englishCountryFromIso($countryShort) : null;
            $englishRegion = ($countryShort && $regionShort)
                ? englishRegionFromIso($countryShort, $regionShort)
                : null;

            $searchTerms = array_unique(array_filter([
                $city,
                $country,
                $region,
                $englishCountry,
                $englishRegion,
            ]));

            // 1. Pre-saved locations: translation JSON contains any submitted label (e.g. "Spanien" -> Spain row)
            foreach ($searchTerms as $term) {
                $match = static::searchTranslation($term)->first();
                if ($match && ($match->city || $match->country || $match->region)) {
                    $row = [
                        'city' => $match->city,
                        'country' => $match->country,
                        'region' => $match->region,
                    ];
                    if (static::rowHasCanonicalEnglish($row, $englishCountry, $englishRegion)) {
                        return $row;
                    }
                    static::repairNonEnglishRow($match, $englishCountry, $englishRegion, $city, $country, $region);
                    $match->refresh();

                    return array_filter([
                        'city' => $match->city,
                        'country' => $match->country,
                        'region' => $match->region,
                    ]) ?: null;
                }
            }

            // 2. Pre-saved locations: localized keys in translation JSON + direct column match
            $locationQuery = static::query();
            $locationQuery->where(function ($query) use ($city, $country, $region, $englishCountry, $englishRegion) {
                foreach (['city' => $city, 'country' => $country ?: $englishCountry, 'region' => $region ?: $englishRegion] as $field => $value) {
                    if (!$value) {
                        continue;
                    }
                    $escaped = str_replace('"', '\\"', $value);
                    $query->orWhere($field, $value)
                        ->orWhereRaw('JSON_EXTRACT(translation, ?) IS NOT NULL', ['$.' . $field . '."' . $escaped . '"']);
                }
            });

            $match = $locationQuery->first();
            if ($match && ($match->city || $match->country || $match->region)) {
                $row = [
                    'city' => $match->city,
                    'country' => $match->country,
                    'region' => $match->region,
                ];
                if (static::rowHasCanonicalEnglish($row, $englishCountry, $englishRegion)) {
                    return $row;
                }
                static::repairNonEnglishRow($match, $englishCountry, $englishRegion, $city, $country, $region);
                $match->refresh();

                return array_filter([
                    'city' => $match->city,
                    'country' => $match->country,
                    'region' => $match->region,
                ]) ?: null;
            }

            // 3. Destination catalog (c_countries / c_regions / c_cities) — English names used by guidings
            if ($countryShort) {
                $countryModel = Country::where('countrycode', strtoupper($countryShort))->first();
                if ($countryModel) {
                    if ($city) {
                        $cityModel = City::where('country_id', $countryModel->id)
                            ->whereRaw('LOWER(name) = ?', [mb_strtolower($city)])
                            ->with('region')
                            ->first();

                        if ($cityModel) {
                            return [
                                'city' => $cityModel->name,
                                'country' => $countryModel->name,
                                'region' => $cityModel->region->name ?? $englishRegion,
                            ];
                        }
                    }

                    if ($englishRegion) {
                        $regionModel = Region::where('country_id', $countryModel->id)
                            ->whereRaw('LOWER(name) = ?', [mb_strtolower($englishRegion)])
                            ->first();

                        if ($regionModel) {
                            return [
                                'city' => $city,
                                'country' => $countryModel->name,
                                'region' => $regionModel->name,
                            ];
                        }
                    }

                    if ($englishCountry) {
                        return array_filter([
                            'city' => $city,
                            'country' => $countryModel->name,
                            'region' => $englishRegion,
                        ]) ?: null;
                    }
                }
            }

            // 4. Match against cities/countries already used on published guidings
            if ($englishCountry && $city) {
                $guidingLocation = Guiding::query()
                    ->where('status', 1)
                    ->where('country', $englishCountry)
                    ->whereRaw('LOWER(city) = ?', [mb_strtolower($city)])
                    ->whereNotNull('city')
                    ->select('city', 'country', 'region')
                    ->first();

                if ($guidingLocation) {
                    return [
                        'city' => $guidingLocation->city,
                        'country' => $guidingLocation->country,
                        'region' => $guidingLocation->region ?? $englishRegion,
                    ];
                }
            }

            return null;
        });
    }

    /**
     * Store English canonical names + localized aliases from a navbar place selection (no Google API).
     */
    public static function rememberSearchMapping(
        ?string $cityEn,
        ?string $countryEn,
        ?string $regionEn,
        ?string $cityLocal = null,
        ?string $countryLocal = null,
        ?string $regionLocal = null,
        ?string $countryShort = null,
        ?string $regionShort = null
    ): void {
        if ($countryShort) {
            $countryEn = englishCountryFromIso($countryShort) ?? $countryEn;
        }
        if ($countryShort && $regionShort) {
            $regionEn = englishRegionFromIso($countryShort, $regionShort) ?? $regionEn;
        }

        if (!$cityEn && !$countryEn && !$regionEn) {
            return;
        }

        $incomingTranslation = static::buildTranslationAliases(
            $cityEn,
            $countryEn,
            $regionEn,
            $cityLocal,
            $countryLocal,
            $regionLocal
        );

        $existing = static::query()
            ->where('city', $cityEn)
            ->where('country', $countryEn)
            ->where('region', $regionEn)
            ->first();

        if ($existing) {
            $merged = static::mergeTranslationArrays(
                is_array($existing->translation) ? $existing->translation : [],
                $incomingTranslation
            );
            if ($merged !== $existing->translation) {
                $existing->update(['translation' => $merged]);
            }
            return;
        }

        static::create([
            'city' => $cityEn,
            'country' => $countryEn,
            'region' => $regionEn,
            'translation' => $incomingTranslation,
        ]);

        static::forgetResolveCache($cityEn, $countryEn, $regionEn, $countryShort, $regionShort);
        static::forgetResolveCache($cityLocal, $countryLocal, $regionLocal, $countryShort, $regionShort);
    }

    /**
     * Persist English names from a one-time Place Details lookup (cached by place_id).
     */
    public static function rememberFromEnglishPlaceDetails(
        array $english,
        ?string $cityLocal = null,
        ?string $countryLocal = null,
        ?string $regionLocal = null,
        ?string $countryShort = null,
        ?string $regionShort = null
    ): void {
        static::rememberSearchMapping(
            $english['city'] ?? null,
            $english['country'] ?? null,
            $english['region'] ?? null,
            $cityLocal,
            $countryLocal,
            $regionLocal,
            $countryShort,
            $regionShort
        );
    }

    protected static function buildTranslationAliases(
        ?string $cityEn,
        ?string $countryEn,
        ?string $regionEn,
        ?string $cityLocal,
        ?string $countryLocal,
        ?string $regionLocal
    ): array {
        $translation = ['city' => [], 'country' => [], 'region' => []];

        foreach (
            [
                'city' => [$cityLocal, $cityEn],
                'country' => [$countryLocal, $countryEn],
                'region' => [$regionLocal, $regionEn],
            ] as $field => [$local, $english]
        ) {
            if ($local && $english && strcasecmp($local, $english) !== 0) {
                $translation[$field][$local] = $english;
            }
        }

        return $translation;
    }

    protected static function mergeTranslationArrays(array $existing, array $incoming): array
    {
        foreach (['city', 'country', 'region'] as $field) {
            $existing[$field] = array_merge($existing[$field] ?? [], $incoming[$field] ?? []);
        }

        return $existing;
    }

    protected static function rowHasCanonicalEnglish(
        array $row,
        ?string $englishCountry,
        ?string $englishRegion
    ): bool {
        if ($englishCountry && !empty($row['country']) && strcasecmp($row['country'], $englishCountry) !== 0) {
            return false;
        }
        if ($englishRegion && !empty($row['region']) && strcasecmp($row['region'], $englishRegion) !== 0) {
            return false;
        }

        return true;
    }

    protected static function repairNonEnglishRow(
        self $row,
        ?string $englishCountry,
        ?string $englishRegion,
        ?string $cityLocal,
        ?string $countryLocal,
        ?string $regionLocal
    ): void {
        $aliases = static::buildTranslationAliases(
            $row->city,
            $englishCountry ?: $row->country,
            $englishRegion ?: $row->region,
            $cityLocal ?: $row->city,
            $countryLocal ?: $row->country,
            $regionLocal ?: $row->region
        );

        $row->update([
            'city' => $row->city,
            'country' => $englishCountry ?: $row->country,
            'region' => $englishRegion ?: $row->region,
            'translation' => static::mergeTranslationArrays(
                is_array($row->translation) ? $row->translation : [],
                $aliases
            ),
        ]);
    }

    public static function forgetResolveCache(
        ?string $city,
        ?string $country,
        ?string $region,
        ?string $countryShort = null,
        ?string $regionShort = null
    ): void {
        \Cache::forget('location_en_resolve_' . md5(json_encode([
            $city, $country, $region, $countryShort, $regionShort,
        ])));
    }
}