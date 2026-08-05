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
        $cacheKey = "homepage_featured_countries_v3_{$locale}_{$limit}";

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($limit) {
            $countries = Country::query()
                ->orderBy('name')
                ->get();

            $withThumb = $countries->filter(fn (Country $c) => filled($c->thumbnail_path));
            $withoutThumb = $countries->reject(fn (Country $c) => filled($c->thumbnail_path));
            $ordered = $withThumb->concat($withoutThumb)->take($limit);

            $minPrices = $this->minPricesByCountryIso($ordered);

            return $ordered->map(function (Country $country) use ($minPrices) {
                $iso = strtoupper((string) ($country->countrycode ?? ''));
                $fromPrice = $iso !== '' ? ($minPrices[$iso] ?? null) : null;

                return [
                    'slug' => $country->slug,
                    'name' => $country->name,
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
        return Cache::remember('homepage_country_total_count', now()->addHour(), function () {
            return Country::query()->count();
        });
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
