<?php

namespace App\Services\Homepage;

use App\Models\Country;
use App\Models\Guiding;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class HomepageCountrySelector
{
    public const FEATURED_LIMIT = 12;

    /**
     * Featured countries for the homepage destinations rail.
     *
     * @return Collection<int, array{slug: string, name: string, thumbnail: string, countrycode: ?string, from_price: ?int, from_price_label: ?string}>
     */
    public function featured(?int $limit = null): Collection
    {
        $limit = $limit ?? self::FEATURED_LIMIT;
        $locale = app()->getLocale();
        $cacheKey = "homepage_featured_countries_v4_{$locale}_{$limit}";

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($limit, $locale) {
            $countries = Country::query()
                ->with('translations')
                ->orderBy('name')
                ->get();

            $unique = $this->uniqueByCountryCode($countries, $locale);

            $withThumb = $unique->filter(fn (Country $c) => filled($c->thumbnail_path));
            $withoutThumb = $unique->reject(fn (Country $c) => filled($c->thumbnail_path));
            $ordered = $withThumb->concat($withoutThumb)->take($limit);

            $minPrices = $this->minPricesByCountryIso($ordered);

            return $ordered->map(function (Country $country) use ($minPrices, $locale) {
                $iso = strtoupper((string) ($country->countrycode ?? ''));
                $fromPrice = $iso !== '' ? ($minPrices[$iso] ?? null) : null;

                return [
                    'slug' => $country->slug,
                    'name' => $this->displayName($country, $locale),
                    'thumbnail' => $country->getThumbnailPath(),
                    'countrycode' => $country->countrycode,
                    'from_price' => $fromPrice,
                    'from_price_label' => $fromPrice
                        ? __('homepage.destination_from_price', [
                            'price' => '€'.number_format($fromPrice, 0),
                        ])
                        : null,
                ];
            })->values();
        });
    }

    public function totalCount(): int
    {
        return Cache::remember('homepage_country_total_count_v2', now()->addHour(), function () {
            $countries = Country::query()->get(['id', 'countrycode']);

            $withIso = $countries
                ->filter(fn (Country $c) => filled($c->countrycode))
                ->unique(fn (Country $c) => strtoupper((string) $c->countrycode));

            $withoutIso = $countries->reject(fn (Country $c) => filled($c->countrycode));

            return $withIso->count() + $withoutIso->count();
        });
    }

    /**
     * Keep one row per ISO so EN/DE migration duplicates (Finland / Finnland) cannot both appear.
     *
     * @param  Collection<int, Country>  $countries
     * @return Collection<int, Country>
     */
    private function uniqueByCountryCode(Collection $countries, string $locale): Collection
    {
        $preferred = $countries->sort(function (Country $a, Country $b) use ($locale) {
            $thumbCmp = (filled($a->thumbnail_path) ? 0 : 1) <=> (filled($b->thumbnail_path) ? 0 : 1);
            if ($thumbCmp !== 0) {
                return $thumbCmp;
            }

            $localeCmp = ($this->hasLocaleTranslation($a, $locale) ? 0 : 1)
                <=> ($this->hasLocaleTranslation($b, $locale) ? 0 : 1);
            if ($localeCmp !== 0) {
                return $localeCmp;
            }

            return $a->id <=> $b->id;
        });

        $seen = [];
        $unique = collect();

        foreach ($preferred as $country) {
            $iso = strtoupper((string) ($country->countrycode ?? ''));

            if ($iso === '') {
                $unique->push($country);
                continue;
            }

            if (isset($seen[$iso])) {
                continue;
            }

            $seen[$iso] = true;
            $unique->push($country);
        }

        return $unique->sortBy(fn (Country $c) => mb_strtolower($this->displayName($c, $locale)))->values();
    }

    public function labelFor(Country $country, ?string $locale = null): string
    {
        return $this->displayName($country, $locale ?? app()->getLocale());
    }

    private function displayName(Country $country, string $locale): string
    {
        // Prefer short locale labels (e.g. Finnland), not SEO translation titles.
        $iso = strtoupper((string) ($country->countrycode ?? ''));
        if ($iso !== '' && class_exists(\Symfony\Component\Intl\Countries::class)) {
            try {
                return \Symfony\Component\Intl\Countries::getName($iso, $locale);
            } catch (\Throwable) {
                // fall through
            }
        }

        $globalKey = 'global.'.$country->name;
        $translated = __($globalKey);
        if ($translated !== $globalKey) {
            return $translated;
        }

        return $country->name;
    }

    private function hasLocaleTranslation(Country $country, string $locale): bool
    {
        if (! $country->relationLoaded('translations')) {
            return $country->translation($locale)->exists();
        }

        return $country->translations->contains(
            fn ($translation) => $translation->language === $locale && filled($translation->title)
        );
    }

    /**
     * @param  Collection<int, Country>  $countries
     * @return array<string, int>
     */
    private function minPricesByCountryIso(Collection $countries): array
    {
        $isos = $countries
            ->pluck('countrycode')
            ->filter()
            ->map(fn ($code) => strtoupper((string) $code))
            ->unique()
            ->values()
            ->all();

        if ($isos === []) {
            return [];
        }

        $isoVariants = collect($isos)
            ->flatMap(fn (string $iso) => [$iso, strtolower($iso)])
            ->unique()
            ->values()
            ->all();

        $guidings = Guiding::query()
            ->publiclyVisible()
            ->whereIn('country_iso', $isoVariants)
            ->get(['id', 'country_iso', 'price', 'prices', 'price_type', 'max_guests']);

        $mins = [];

        foreach ($guidings as $guiding) {
            $iso = strtoupper((string) $guiding->country_iso);
            $price = $guiding->getLowestPrice();

            if ($price <= 0) {
                continue;
            }

            if (! isset($mins[$iso]) || $price < $mins[$iso]) {
                $mins[$iso] = $price;
            }
        }

        return $mins;
    }
}
