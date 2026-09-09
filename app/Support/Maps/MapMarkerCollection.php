<?php

namespace App\Support\Maps;

class MapMarkerCollection
{
    public const MODULE_TOUR = 'tour';

    public const MODULE_TRIP = 'trip';

    public const MODULE_CAMP = 'camp';

    /**
     * Build listing markers without per-item Blade renders (popup HTML is built client-side).
     *
     * @param  iterable  $guidings
     * @param  array<int>  $grayIds
     * @param  array<string, mixed>  $query
     * @return array<int, array<string, mixed>>
     */
    public static function fromGuidings(iterable $guidings, array $grayIds = [], array $query = []): array
    {
        $markers = [];
        $grayLookup = array_fill_keys(array_map('intval', $grayIds), true);

        foreach ($guidings as $guiding) {
            if (empty($guiding->lat) || empty($guiding->lng)) {
                continue;
            }

            $id = (int) $guiding->id;
            $title = (string) ($guiding->title ?? '');
            $images = self::resolveImages($guiding);
            $image = $images[0] ?? '';

            $price = method_exists($guiding, 'getLowestPrice')
                ? $guiding->getLowestPrice()
                : ($guiding->price ?? null);
            if ($price !== null && $price !== '' && (float) $price <= 0) {
                $price = null;
            }

            $module = self::MODULE_TOUR;
            $markers[] = array_merge([
                'id' => $id,
                'lat' => (float) $guiding->lat,
                'lng' => (float) $guiding->lng,
                'variant' => isset($grayLookup[$id]) ? 'gray' : 'primary',
                'pillar' => 'guiding',
                'title' => $title,
                'url' => self::guidingShowUrl($guiding, $query),
                'location' => (string) ($guiding->location ?? ''),
                'image' => (string) $image,
                'images' => $images,
                'price' => $price,
                'priceLabel' => $price !== null
                    ? __('destination.map_price_from', [
                        'price' => '€'.number_format((float) $price, 0, ',', '.'),
                    ])
                    : null,
                'badge' => __('offers.badge_tour'),
                'cta' => __('vacations.view_details'),
            ], self::moduleFields($module, $id), self::guidingListMeta($guiding));
        }

        return $markers;
    }

    /**
     * Structured vacation markers (trips) — popup HTML built client-side.
     *
     * @param  iterable  $trips
     * @param  array<string, mixed>  $query
     * @return array<int, array<string, mixed>>
     */
    public static function fromTrips(iterable $trips, array $query = []): array
    {
        $markers = [];

        foreach ($trips as $trip) {
            $lat = $trip->latitude ?? $trip->lat ?? null;
            $lng = $trip->longitude ?? $trip->lng ?? null;
            if (empty($lat) || empty($lng)) {
                continue;
            }

            $images = self::resolveImages($trip);
            $image = $images[0] ?? '';

            $currency = $trip->currency ?? 'EUR';
            $sym = $currency === 'EUR' ? '€' : (($currency === 'USD' ? '$' : $currency.' '));
            $price = isset($trip->price_per_person) && (float) $trip->price_per_person > 0
                ? (float) $trip->price_per_person
                : null;

            $module = self::MODULE_TRIP;
            $id = (int) ($trip->id ?? 0);
            $markers[] = array_merge([
                'id' => $id,
                'lat' => (float) $lat,
                'lng' => (float) $lng,
                'variant' => 'trip',
                'pillar' => 'trip',
                'title' => (string) ($trip->title ?? ''),
                'url' => route('vacations.trips.show', array_merge(
                    ['slug' => $trip->slug],
                    array_filter($query, fn ($v) => $v !== null && $v !== ''),
                )),
                'location' => (string) ($trip->location ?? ''),
                'image' => (string) $image,
                'images' => $images,
                'price' => $price,
                'priceLabel' => $price !== null
                    ? __('vacations.price_from_per_person', ['price' => $sym.number_format($price, 0)])
                    : null,
                'badge' => __('offers.badge_trip'),
                'cta' => __('vacations.view_details'),
            ], self::moduleFields($module, $id), self::tripListMeta($trip));
        }

        return $markers;
    }

    /**
     * Structured vacation markers (camps) — popup HTML built client-side.
     * Eager-load accommodations + specialOffers before calling to avoid N+1.
     *
     * @param  iterable  $camps
     * @param  array<string, mixed>  $query
     * @return array<int, array<string, mixed>>
     */
    public static function fromCamps(iterable $camps, array $query = []): array
    {
        $markers = [];

        foreach ($camps as $camp) {
            $lat = $camp->latitude ?? $camp->lat ?? null;
            $lng = $camp->longitude ?? $camp->lng ?? null;
            if (empty($lat) || empty($lng)) {
                continue;
            }

            $images = self::resolveImages($camp);
            $image = $images[0] ?? '';

            $price = null;
            if (method_exists($camp, 'getLowestAccommodationOrOfferPrice')) {
                $price = $camp->getLowestAccommodationOrOfferPrice();
            } elseif (method_exists($camp, 'getLowestPrice')) {
                $raw = $camp->getLowestPrice();
                $price = $raw > 0 ? (float) $raw : null;
            }

            $module = self::MODULE_CAMP;
            $id = (int) ($camp->id ?? 0);
            $markers[] = array_merge([
                'id' => $id,
                'lat' => (float) $lat,
                'lng' => (float) $lng,
                'variant' => 'camp',
                'pillar' => 'camp',
                'title' => (string) ($camp->title ?? ''),
                'url' => route('vacations.camps.show', array_merge(
                    ['slug' => $camp->slug],
                    array_filter($query, fn ($v) => $v !== null && $v !== ''),
                )),
                'location' => (string) ($camp->location ?? $camp->city ?? ''),
                'image' => (string) $image,
                'images' => $images,
                'price' => $price,
                'priceLabel' => $price !== null
                    ? __('vacations.price_from_per_night', ['price' => '€'.number_format($price, 0)])
                    : null,
                'badge' => __('offers.badge_camp'),
                'cta' => __('vacations.view_details'),
            ], self::moduleFields($module, $id), self::campListMeta($camp));
        }

        return $markers;
    }

    /**
     * @param  iterable  $vacations
     * @param  array<int>  $grayIds
     * @return array<int, array<string, mixed>>
     */
    public static function fromVacations(iterable $vacations, array $grayIds = []): array
    {
        $markers = [];
        $grayLookup = array_fill_keys(array_map('intval', $grayIds), true);

        foreach ($vacations as $vacation) {
            $lat = $vacation->latitude ?? $vacation->lat ?? null;
            $lng = $vacation->longitude ?? $vacation->lng ?? null;
            if (empty($lat) || empty($lng)) {
                continue;
            }

            $id = (int) $vacation->id;
            $isGray = isset($grayLookup[$id]);

            $title = $vacation->title ?? $vacation->name ?? '';
            $slug = $vacation->slug ?? '';
            $url = $vacation->url ?? '#';
            if ($slug !== '') {
                try {
                    if (\Illuminate\Support\Facades\Route::has('vacations.camps.show')) {
                        $url = route('vacations.camps.show', ['slug' => $slug]);
                    } elseif (isset($vacation->id) && \Illuminate\Support\Facades\Route::has('vacations.show')) {
                        $url = route('vacations.show', [$vacation->id, $slug]);
                    }
                } catch (\Throwable $e) {
                    // keep $url fallback
                }
            }

            $images = self::resolveImages($vacation);
            $image = $images[0] ?? '';

            $price = null;
            if (method_exists($vacation, 'getLowestAccommodationOrOfferPrice')) {
                $price = $vacation->getLowestAccommodationOrOfferPrice();
            } elseif (method_exists($vacation, 'getLowestPrice')) {
                $raw = $vacation->getLowestPrice();
                $price = $raw > 0 ? (float) $raw : null;
            } elseif (isset($vacation->price)) {
                $price = $vacation->price;
            }

            $module = self::MODULE_CAMP;
            $markers[] = array_merge([
                'id' => $id,
                'lat' => (float) $lat,
                'lng' => (float) $lng,
                'variant' => $isGray ? 'gray' : 'camp',
                'pillar' => 'camp',
                'title' => (string) $title,
                'url' => $url,
                'location' => (string) ($vacation->location ?? $vacation->city ?? ''),
                'image' => (string) $image,
                'images' => $images,
                'price' => $price,
                'priceLabel' => $price !== null
                    ? __('vacations.price_from_per_night', ['price' => '€'.number_format((float) $price, 0)])
                    : null,
                'badge' => __('offers.badge_camp'),
                'cta' => __('vacations.view_details'),
            ], self::moduleFields($module, $id));
        }

        return $markers;
    }

    /**
     * Generic markers from arrays/objects with lat/lng.
     *
     * @param  iterable  $items
     * @return array<int, array<string, mixed>>
     */
    public static function fromItems(iterable $items, callable $mapper): array
    {
        $markers = [];
        foreach ($items as $item) {
            $mapped = $mapper($item);
            if (! $mapped || empty($mapped['lat']) || empty($mapped['lng'])) {
                continue;
            }
            if (empty($mapped['module']) && ! empty($mapped['pillar'])) {
                $mapped = array_merge(
                    $mapped,
                    self::moduleFields(self::normalizeModule((string) $mapped['pillar']), $mapped['id'] ?? null)
                );
            }
            if (empty($mapped['key']) && isset($mapped['id'])) {
                $mapped['key'] = self::itemKey(
                    (string) ($mapped['module'] ?? $mapped['pillar'] ?? self::MODULE_TOUR),
                    $mapped['id']
                );
            }
            $markers[] = $mapped;
        }

        return $markers;
    }

    public static function toJson(array $markers): string
    {
        return json_encode(array_values($markers), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    }

    /**
     * @param  array<string, mixed>  $query
     */
    private static function guidingShowUrl(object $guiding, array $query = []): string
    {
        $query = array_filter($query, fn ($v) => $v !== null && $v !== '');

        if (method_exists($guiding, 'publicShowUrl')) {
            return $guiding->publicShowUrl($query);
        }

        $slug = $guiding->slug ?? null;
        if (! is_string($slug) || $slug === '') {
            return '#';
        }

        return route('guidings.show', array_merge(['slug' => $slug], $query));
    }

    /**
     * Stable map identity across mixed offer types (tour / trip / camp share numeric IDs).
     */
    public static function itemKey(string $module, int|string $id): string
    {
        return self::normalizeModule($module).':'.$id;
    }

    /**
     * Canonical offer module for mixed listings (tour | trip | camp).
     *
     * @return array{module: string, moduleLabel: string, key?: string}
     */
    public static function moduleFields(string $module, int|string|null $id = null): array
    {
        $module = self::normalizeModule($module);

        $fields = [
            'module' => $module,
            'moduleLabel' => __('offers.badge_'.$module),
        ];

        if ($id !== null && $id !== '') {
            $fields['key'] = self::itemKey($module, $id);
        }

        return $fields;
    }

    public static function normalizeModule(string $value): string
    {
        $value = strtolower(trim($value));

        return match ($value) {
            'guiding', 'tour', 'primary' => self::MODULE_TOUR,
            'trip', 'trips' => self::MODULE_TRIP,
            'camp', 'camps', 'vacation' => self::MODULE_CAMP,
            default => self::MODULE_TOUR,
        };
    }

    /**
     * Absolute image URLs for map preview carousel (thumbnail first, then gallery).
     *
     * @return list<string>
     */
    public static function resolveImages(object $model, int $limit = 5): array
    {
        $limit = max(1, min(8, $limit));
        $urls = [];
        $seen = [];

        $push = static function (?string $path) use (&$urls, &$seen, $limit): void {
            if (count($urls) >= $limit || $path === null || $path === '') {
                return;
            }
            $url = $path;
            if (function_exists('media_url') && ! str_starts_with($path, 'http://') && ! str_starts_with($path, 'https://')) {
                $url = (string) media_url($path);
            }
            if ($url === '' || isset($seen[$url])) {
                return;
            }
            $seen[$url] = true;
            $urls[] = $url;
        };

        $gallery = $model->gallery_images ?? $model->gallery ?? [];
        if (is_string($gallery)) {
            $gallery = json_decode($gallery, true) ?: [];
        }
        if (! is_array($gallery)) {
            $gallery = [];
        }

        $push(isset($model->thumbnail_path) ? (string) $model->thumbnail_path : null);
        if (empty($urls) && isset($model->image) && is_string($model->image)) {
            $push($model->image);
        }
        foreach ($gallery as $path) {
            if (is_string($path)) {
                $push($path);
            }
        }

        return $urls;
    }

    /**
     * Extra list-rail fields for guidings/tours (kept off the map popup).
     *
     * @return array<string, mixed>
     */
    public static function guidingListMeta(object $guiding): array
    {
        $durationLabel = null;
        if (! empty($guiding->duration)) {
            $unit = ($guiding->duration_type ?? '') === 'multi_day'
                ? __('guidings.days')
                : __('guidings.hours');
            $durationLabel = trim((string) $guiding->duration.' '.$unit);
        }

        $guestsLabel = null;
        $maxGuests = (int) ($guiding->max_guests ?? 0);
        if ($maxGuests > 0) {
            $guestsLabel = $maxGuests === 1
                ? __('destination.map_max_guests_one')
                : __('destination.map_max_guests', ['count' => $maxGuests]);
        }

        $rating = $guiding->cached_average_rating ?? null;
        $reviewCount = $guiding->cached_review_count ?? null;
        if ($rating !== null && (float) $rating <= 0) {
            $rating = null;
        }

        $boatLabel = $guiding->cached_boat_type_name ?? null;
        if ($boatLabel === null && isset($guiding->is_boat)) {
            $boatLabel = ((int) $guiding->is_boat === 1)
                ? __('guidings.boat')
                : __('guidings.shore');
        }

        return array_filter([
            'durationLabel' => $durationLabel,
            'guestsLabel' => $guestsLabel,
            'maxGuests' => $maxGuests > 0 ? $maxGuests : null,
            'rating' => $rating !== null ? round((float) $rating, 1) : null,
            'reviewCount' => $reviewCount !== null ? (int) $reviewCount : null,
            'boatLabel' => $boatLabel ? (string) $boatLabel : null,
        ], static fn ($v) => $v !== null && $v !== '');
    }

    /**
     * @return array<string, mixed>
     */
    public static function tripListMeta(object $trip): array
    {
        $durationLabel = null;
        if (! empty($trip->duration_days)) {
            $durationLabel = trim((string) $trip->duration_days.' '.__('guidings.days'));
        } elseif (! empty($trip->days)) {
            $durationLabel = trim((string) $trip->days.' '.__('guidings.days'));
        }

        return array_filter([
            'durationLabel' => $durationLabel,
            'rating' => isset($trip->cached_average_rating) ? round((float) $trip->cached_average_rating, 1) : null,
            'reviewCount' => isset($trip->cached_review_count) ? (int) $trip->cached_review_count : null,
        ], static fn ($v) => $v !== null && $v !== '');
    }

    /**
     * @return array<string, mixed>
     */
    public static function campListMeta(object $camp): array
    {
        return array_filter([
            'rating' => isset($camp->cached_average_rating) ? round((float) $camp->cached_average_rating, 1) : null,
            'reviewCount' => isset($camp->cached_review_count) ? (int) $camp->cached_review_count : null,
        ], static fn ($v) => $v !== null && $v !== '');
    }
}
