<?php

namespace App\Services\Catalog;

use App\Models\Camp;
use App\Models\Guiding;
use App\Models\Vacation;
use Illuminate\Support\Facades\Cache;

class TripCatalogService
{
    /**
     * Build a unified list of trips (guidings + vacations) for AI/agent consumption.
     *
     * The schema is intentionally simple and stable:
     * - id: int
     * - type: string  (\"guiding\" | \"vacation\")
     * - title: string
     * - slug: string
     * - url: string (absolute URL)
     * - language: string (e.g. \"en\", \"de\")
     * - country: string|null
     * - region: string|null
     * - city: string|null
     * - categories: string[]  (target fish, methods, water types, etc.)
     * - min_price: float|null
     * - currency: string|null
     * - duration: string|null
     * - availability_summary: string|null
     * - short_description: string|null
     * - images: string[] (absolute or site-relative URLs)
     *
     * @return array<int, array<string, mixed>>
     */
    public function getAllTrips(): array
    {
        $cacheKey = 'catalog_trips_' . app()->getLocale();

        return Cache::remember($cacheKey, 600, function () {
            $guidings = $this->getGuidingTrips();
            $vacations = $this->getVacationTrips();

            return array_values(array_merge($guidings, $vacations));
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getGuidingTrips(): array
    {
        $cacheKey = 'catalog_guidings_' . app()->getLocale();

        return Cache::remember($cacheKey, 600, function () {
            /** @var \Illuminate\Support\Collection<int, \App\Models\Guiding> $guidings */
            $guidings = Guiding::query()
                ->where('status', 1)
                ->whereNotNull('title')
                ->whereNotNull('slug')
                ->where('slug', '!=', '')
                ->limit(1000)
                ->get();

            return $guidings->map(function (Guiding $guiding): array {
                $categories = $this->buildGuidingCategories($guiding);
                $pricing = $this->buildGuidingPricing($guiding);
                $included = $this->buildGuidingIncluded($guiding);
                $boatType = $this->buildGuidingBoatType($guiding);
                $fishingType = $this->buildGuidingFishingType($guiding);
                $targetFish = $this->buildGuidingTargetFish($guiding);

                return [
                    'type' => 'guiding',
                    'title' => (string) $guiding->title,
                    'slug' => (string) $guiding->slug,
                    'url' => $this->buildGuidingUrl($guiding),
                    'language' => (string) ($guiding->language ?? app()->getLocale()),
                    'country' => $guiding->country ?? null,
                    'region' => $guiding->region ?? null,
                    'city' => $guiding->city ?? null,
                    'categories' => $categories,
                    'min_price' => $pricing['min_price'] ?? null,
                    'currency' => $pricing['currency'] ?? 'EUR',
                    'duration' => $guiding->duration ?? null,
                    'availability_summary' => $this->buildGuidingAvailabilitySummary($guiding),
                    'short_description' => $guiding->desc_tour_unique
                        ?: $guiding->desc_course_of_action
                        ?: $guiding->description,
                    'images' => $this->buildGuidingImages($guiding),
                    'included' => $included,
                    'boat_type' => $boatType,
                    'fishing_type' => $fishingType,
                    'target_fish' => $targetFish,
                    'pricing' => $pricing,
                ];
            })->all();
        });
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getVacationTrips(): array
    {
        $cacheKey = 'catalog_vacations_' . app()->getLocale();

        return Cache::remember($cacheKey, 600, function () {
            /** @var \Illuminate\Support\Collection<int, \App\Models\Vacation> $vacations */
            $vacations = Vacation::query()
                ->where('status', 1)
                ->whereNotNull('title')
                ->whereNotNull('slug')
                ->where('slug', '!=', '')
                ->limit(1000)
                ->get();

            return $vacations->map(function (Vacation $vacation): array {
                $categories = $this->buildVacationCategories($vacation);
                $included = $this->buildVacationIncluded($vacation);
                $vacationExtras = $this->buildVacationExtras($vacation);
                $pricing = $this->buildVacationPricing($vacation, $vacationExtras);

                return [
                    'type' => 'vacation',
                    'title' => (string) $vacation->title,
                    'slug' => (string) $vacation->slug,
                    'url' => $this->buildVacationUrl($vacation),
                    'language' => (string) ($vacation->language ?? app()->getLocale()),
                    'country' => $vacation->country ?? null,
                    'region' => $vacation->region ?? null,
                    'city' => $vacation->city ?? null,
                    'categories' => $categories,
                    'min_price' => $pricing['min_price'] ?? null,
                    'currency' => $pricing['currency'] ?? 'EUR',
                    'duration' => null,
                    'availability_summary' => $vacation->best_travel_times
                        ? (is_array($vacation->best_travel_times)
                            ? implode(', ', $vacation->best_travel_times)
                            : (string) $vacation->best_travel_times)
                        : null,
                    'short_description' => $vacation->basic_fishing_description
                        ?: $vacation->surroundings_description
                        ?: $vacation->accommodation_description,
                    'images' => $this->buildVacationImages($vacation),
                    'included' => $included,
                    'pricing' => $pricing,
                ];
            })->all();
        });
    }

    protected function buildGuidingUrl(Guiding $guiding): string
    {
        try {
            return route('guidings.show', ['id' => $guiding->id, 'slug' => $guiding->slug]);
        } catch (\Throwable $e) {
            return url('/guidings/' . $guiding->id . '/' . $guiding->slug);
        }
    }

    protected function buildVacationUrl(Vacation $vacation): string
    {
        try {
            return route('vacations.show', ['slug' => $vacation->slug]);
        } catch (\Throwable $e) {
            return url('/vacations/' . $vacation->slug);
        }
    }

    /**
     * @return string[]
     */
    protected function buildGuidingCategories(Guiding $guiding): array
    {
        $categories = [];

        // JSON fields with target fish, water types, methods, etc.
        foreach (['target_fish', 'fishing_methods', 'water_types'] as $field) {
            $value = $guiding->{$field} ?? null;

            if (is_string($value)) {
                $decoded = json_decode($value, true);
                if (is_array($decoded)) {
                    $value = $decoded;
                }
            }

            if (is_array($value)) {
                foreach ($value as $item) {
                    if (!empty($item) && is_string($item)) {
                        $categories[] = $item;
                    }
                }
            }
        }

        return array_values(array_unique($categories));
    }

    /**
     * Build a detailed pricing payload for guidings, including per-person prices and extras.
     *
     * Shape:
     * - currency: string
     * - tiers: array<array{person:int, amount:float}>
     * - extras: array<array{name:string, price:float}>
     * - min_price: float|null (derived from tiers when available, or legacy price fields)
     */
    protected function buildGuidingPricing(Guiding $guiding): array
    {
        $currency = 'EUR';

        // Base per-person tiers from the modern prices JSON field
        $tiers = [];
        $prices = $guiding->prices;
        if ($prices) {
            if (is_string($prices)) {
                $decoded = json_decode($prices, true);
                $prices = is_array($decoded) ? $decoded : [];
            }

            if (is_array($prices)) {
                foreach ($prices as $price) {
                    if (!isset($price['person'], $price['amount'])) {
                        continue;
                    }

                    $tiers[] = [
                        'person' => (int) $price['person'],
                        'amount' => (float) $price['amount'],
                    ];
                }
            }
        }

        // Extras from pricing_extra accessor (already normalized)
        $extras = [];
        if ($guiding->pricing_extra) {
            $extrasCollection = $guiding->getPricingExtraAttribute();
            foreach ($extrasCollection as $extra) {
                if (!isset($extra['name'], $extra['price'])) {
                    continue;
                }

                $extras[] = [
                    'name' => (string) $extra['name'],
                    'price' => (float) $extra['price'],
                ];
            }
        }

        // Derive min_price, preferring per-person tiers when available
        $minPrice = null;

        if (!empty($tiers)) {
            $candidatePrices = [];

            foreach ($tiers as $tier) {
                $person = max(1, (int) $tier['person']);
                $amount = (float) $tier['amount'];

                // Normalize to a per-person view when person > 1
                $candidatePrices[] = $person > 1 ? round($amount / $person, 2) : $amount;
            }

            if (!empty($candidatePrices)) {
                $minPrice = min($candidatePrices);
            }
        } else {
            // Fallback to legacy price columns if prices JSON is empty
            $legacyPrices = [];
            foreach ([
                'price',
                'price_two_persons',
                'price_three_persons',
                'price_four_persons',
                'price_five_persons',
            ] as $field) {
                $value = $guiding->{$field};
                if ($value !== null && $value !== '') {
                    $legacyPrices[] = (float) $value;
                }
            }

            if (!empty($legacyPrices)) {
                $minPrice = min($legacyPrices);
            }
        }

        return [
            'currency' => $currency,
            'tiers' => $tiers,
            'extras' => $extras,
            'min_price' => $minPrice,
        ];
    }

    /**
     * Included items for guidings, as shown under “What’s included”.
     *
     * @return string[]
     */
    protected function buildGuidingIncluded(Guiding $guiding): array
    {
        $names = $guiding->getInclusionNames();

        if (empty($names)) {
            return [];
        }

        return array_values(array_filter(array_map(function ($item) {
            if (is_array($item) && isset($item['name'])) {
                return (string) $item['name'];
            }
            if (is_string($item)) {
                return $item;
            }
            return null;
        }, $names)));
    }

    protected function buildGuidingBoatType(Guiding $guiding): ?string
    {
        if ($guiding->is_boat) {
            if ($guiding->boatType && $guiding->boatType->name !== null) {
                return (string) $guiding->boatType->name;
            }

            return 'boat';
        }

        return 'shore';
    }

    /**
     * Human-readable fishing type name (e.g. “Spinning”, “Fly fishing”).
     */
    protected function buildGuidingFishingType(Guiding $guiding): ?string
    {
        if ($guiding->fishingTypes) {
            // Prefer localized name if available
            if (!empty($guiding->fishingTypes->name_en)) {
                return (string) $guiding->fishingTypes->name_en;
            }

            if (!empty($guiding->fishingTypes->name)) {
                return (string) $guiding->fishingTypes->name;
            }
        }

        if (!empty($guiding->fishing_type)) {
            return (string) $guiding->fishing_type;
        }

        return null;
    }

    /**
     * Target fish labels for the guiding.
     *
     * @return string[]
     */
    protected function buildGuidingTargetFish(Guiding $guiding): array
    {
        $targetFish = $guiding->target_fish;

        if (is_string($targetFish)) {
            $decoded = json_decode($targetFish, true);
            if (is_array($decoded)) {
                $targetFish = $decoded;
            }
        }

        if (!is_array($targetFish)) {
            return [];
        }

        return array_values(array_filter(array_map(function ($fish) {
            return is_string($fish) && $fish !== '' ? $fish : null;
        }, $targetFish)));
    }

    protected function buildGuidingAvailabilitySummary(Guiding $guiding): ?string
    {
        $parts = [];

        if (!empty($guiding->months)) {
            $months = is_array($guiding->months)
                ? $guiding->months
                : (json_decode($guiding->months, true) ?: []);
            if (!empty($months)) {
                $parts[] = 'Months: ' . implode(', ', $months);
            }
        }

        if (!empty($guiding->weekday_availability)) {
            $weekdays = is_array($guiding->weekday_availability)
                ? $guiding->weekday_availability
                : (json_decode($guiding->weekday_availability, true) ?: []);
            if (!empty($weekdays)) {
                $parts[] = 'Weekdays: ' . implode(', ', $weekdays);
            }
        }

        if (!empty($guiding->seasonal_trip)) {
            $parts[] = 'Seasonal trip';
        }

        if (empty($parts)) {
            return null;
        }

        return implode(' | ', $parts);
    }

    /**
     * @return string[]
     */
    protected function buildGuidingImages(Guiding $guiding): array
    {
        $images = [];

        if (!empty($guiding->thumbnail_path)) {
            $images[] = $this->normalizeImagePath($guiding->thumbnail_path);
        }

        if (!empty($guiding->gallery_images)) {
            $gallery = is_array($guiding->gallery_images)
                ? $guiding->gallery_images
                : (json_decode($guiding->gallery_images, true) ?: []);

            foreach ($gallery as $path) {
                if (!empty($path) && is_string($path)) {
                    $images[] = $this->normalizeImagePath($path);
                }
            }
        }

        return array_values(array_unique($images));
    }

    /**
     * @return string[]
     */
    protected function buildVacationImages(Vacation $vacation): array
    {
        $images = [];

        if (!empty($vacation->gallery)) {
            $gallery = is_array($vacation->gallery)
                ? $vacation->gallery
                : (json_decode($vacation->gallery, true) ?: []);

            foreach ($gallery as $path) {
                if (!empty($path) && is_string($path)) {
                    $images[] = $this->normalizeImagePath($path);
                }
            }
        }

        return array_values(array_unique($images));
    }

    protected function getVacationMinPrice(Vacation $vacation): ?float
    {
        $prices = [];

        foreach ([
            'package_price_per_person',
            'accommodation_price',
            'boat_rental_price',
            'guiding_price',
        ] as $field) {
            $value = $vacation->{$field};
            if ($value !== null && $value !== '') {
                $prices[] = (float) $value;
            }
        }

        if (empty($prices)) {
            return null;
        }

        return min($prices);
    }

    /**
     * Included services for vacations (inclusive services + a brief mention of extras).
     *
     * @return string[]
     */
    protected function buildVacationIncluded(Vacation $vacation): array
    {
        $included = [];

        if (!empty($vacation->included_services)) {
            $services = $vacation->included_services;
            if (is_string($services)) {
                $decoded = json_decode($services, true);
                $services = is_array($decoded) ? $decoded : [$services];
            }

            if (is_array($services)) {
                foreach ($services as $service) {
                    if (is_string($service) && $service !== '') {
                        $included[] = $service;
                    }
                }
            }
        }

        return array_values(array_unique($included));
    }

    /**
     * Vacation extras list (description + price + type), aligned with what is shown in the table.
     *
     * @return array<int, array<string, mixed>>
     */
    protected function buildVacationExtras(Vacation $vacation): array
    {
        $extras = [];

        if ($vacation->extras && $vacation->extras->count() > 0) {
            foreach ($vacation->extras as $extra) {
                $extras[] = [
                    'description' => (string) $extra->description,
                    'price' => (float) $extra->price,
                    'type' => (string) $extra->type,
                ];
            }
        }

        return $extras;
    }

    /**
     * Build a pricing payload for vacations, including a min_price and optional extras.
     */
    protected function buildVacationPricing(Vacation $vacation, array $extras = []): array
    {
        $currency = 'EUR';
        $minPrice = $this->getVacationMinPrice($vacation);

        return [
            'currency' => $currency,
            'min_price' => $minPrice,
            'extras' => $extras,
        ];
    }

    /**
     * @return string[]
     */
    protected function buildVacationCategories(Vacation $vacation): array
    {
        $categories = [];

        if (!empty($vacation->target_fish)) {
            $value = $vacation->target_fish;

            if (is_string($value)) {
                $decoded = json_decode($value, true);
                if (is_array($decoded)) {
                    $value = $decoded;
                }
            }

            if (is_array($value)) {
                foreach ($value as $item) {
                    if (!empty($item) && is_string($item)) {
                        $categories[] = $item;
                    }
                }
            }
        }

        return array_values(array_unique($categories));
    }

    /**
     * Fishing camps (active listings) in the same catalog shape as guidings/vacations.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getCampTrips(): array
    {
        $cacheKey = 'catalog_camps_' . app()->getLocale();

        return Cache::remember($cacheKey, 600, function () {
            /** @var \Illuminate\Support\Collection<int, \App\Models\Camp> $camps */
            $camps = Camp::query()
                ->where('status', 'active')
                ->whereNotNull('title')
                ->whereNotNull('slug')
                ->where('slug', '!=', '')
                ->limit(500)
                ->get();

            return $camps->map(function (Camp $camp): array {
                $minPrice = null;
                try {
                    $low = $camp->getLowestPrice();
                    $minPrice = $low > 0 ? (float) $low : null;
                } catch (\Throwable) {
                    $minPrice = null;
                }

                $bestTimes = $camp->best_travel_times;
                $availability = null;
                if (is_array($bestTimes) && $bestTimes !== []) {
                    $availability = implode(', ', array_filter($bestTimes, 'is_string'));
                } elseif (is_string($bestTimes) && $bestTimes !== '') {
                    $availability = $bestTimes;
                }

                $targetFish = $camp->target_fish;
                $categories = [];
                if (is_array($targetFish)) {
                    foreach ($targetFish as $item) {
                        if (!empty($item) && is_string($item)) {
                            $categories[] = $item;
                        }
                    }
                }

                return [
                    'type' => 'camp',
                    'title' => (string) $camp->title,
                    'slug' => (string) $camp->slug,
                    'url' => $this->buildCampUrl($camp),
                    'language' => (string) app()->getLocale(),
                    'country' => $camp->country ?? null,
                    'region' => $camp->region ?? null,
                    'city' => $camp->city ?? null,
                    'categories' => array_values(array_unique($categories)),
                    'min_price' => $minPrice,
                    'currency' => 'EUR',
                    'duration' => null,
                    'availability_summary' => $availability,
                    'short_description' => $camp->description_camp
                        ?: $camp->description_fishing
                        ?: $camp->description_area,
                    'images' => $this->buildCampImages($camp),
                ];
            })->all();
        });
    }

    protected function buildCampUrl(Camp $camp): string
    {
        try {
            return route('vacations.v2', ['campId' => $camp->id]);
        } catch (\Throwable) {
            return url('/vacations-v2/' . $camp->id);
        }
    }

    /**
     * @return string[]
     */
    protected function buildCampImages(Camp $camp): array
    {
        $out = [];
        if (!empty($camp->thumbnail_path)) {
            $out[] = $this->normalizeImagePath((string) $camp->thumbnail_path);
        }
        if (is_array($camp->gallery_images)) {
            foreach ($camp->gallery_images as $path) {
                if (is_string($path) && $path !== '') {
                    $out[] = $this->normalizeImagePath($path);
                }
            }
        }

        return array_values(array_unique($out));
    }

    protected function normalizeImagePath(string $path): string
    {
        // If path already looks like an absolute URL, return as is
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        // Ensure leading slash for site-relative assets
        if (!str_starts_with($path, '/')) {
            $path = '/' . ltrim($path, '/');
        }

        return $path;
    }
}

