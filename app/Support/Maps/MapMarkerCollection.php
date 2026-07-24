<?php

namespace App\Support\Maps;

class MapMarkerCollection
{
    /**
     * Build listing markers without per-item Blade renders (popup HTML is built client-side).
     *
     * @param  iterable  $guidings
     * @param  array<int>  $grayIds
     * @return array<int, array<string, mixed>>
     */
    public static function fromGuidings(iterable $guidings, array $grayIds = []): array
    {
        $markers = [];
        $grayLookup = array_fill_keys(array_map('intval', $grayIds), true);

        foreach ($guidings as $guiding) {
            if (empty($guiding->lat) || empty($guiding->lng)) {
                continue;
            }

            $id = (int) $guiding->id;
            $title = (string) ($guiding->title ?? '');
            $image = $guiding->thumbnail_path ?? null;
            if ($image && function_exists('media_url')) {
                $image = media_url($image);
            }

            $price = method_exists($guiding, 'getLowestPrice')
                ? $guiding->getLowestPrice()
                : ($guiding->price ?? null);
            if ($price !== null && $price !== '' && (float) $price <= 0) {
                $price = null;
            }

            $markers[] = [
                'id' => $id,
                'lat' => (float) $guiding->lat,
                'lng' => (float) $guiding->lng,
                'variant' => isset($grayLookup[$id]) ? 'gray' : 'primary',
                'pillar' => 'guiding',
                'title' => $title,
                'url' => route('guidings.show', [$guiding->id, $guiding->slug]),
                'location' => (string) ($guiding->location ?? ''),
                'image' => (string) ($image ?? ''),
                'price' => $price,
                'priceLabel' => $price !== null
                    ? ('ab ' . $price . '€ p.P.')
                    : null,
                'badge' => function_exists('translate') ? translate('Guiding') : 'Guiding',
                'cta' => __('vacations.view_details'),
            ];
        }

        return $markers;
    }

    /**
     * Structured vacation markers (trips) — popup HTML built client-side.
     *
     * @param  iterable  $trips
     * @return array<int, array<string, mixed>>
     */
    public static function fromTrips(iterable $trips): array
    {
        $markers = [];

        foreach ($trips as $trip) {
            $lat = $trip->latitude ?? $trip->lat ?? null;
            $lng = $trip->longitude ?? $trip->lng ?? null;
            if (empty($lat) || empty($lng)) {
                continue;
            }

            $image = $trip->thumbnail_path ?? null;
            if ($image && function_exists('media_url')) {
                $image = media_url($image);
            }

            $currency = $trip->currency ?? 'EUR';
            $sym = $currency === 'EUR' ? '€' : (($currency === 'USD' ? '$' : $currency . ' '));
            $price = isset($trip->price_per_person) && (float) $trip->price_per_person > 0
                ? (float) $trip->price_per_person
                : null;

            $markers[] = [
                'id' => (int) ($trip->id ?? 0),
                'lat' => (float) $lat,
                'lng' => (float) $lng,
                'variant' => 'trip',
                'pillar' => 'trip',
                'title' => (string) ($trip->title ?? ''),
                'url' => route('vacations.trips.show', $trip->slug),
                'location' => (string) ($trip->location ?? ''),
                'image' => (string) ($image ?? ''),
                'price' => $price,
                'priceLabel' => $price !== null
                    ? __('vacations.price_from_per_person', ['price' => $sym . number_format($price, 0)])
                    : null,
                'badge' => __('vacations.badge_trip'),
                'cta' => __('vacations.view_details'),
            ];
        }

        return $markers;
    }

    /**
     * Structured vacation markers (camps) — popup HTML built client-side.
     * Eager-load accommodations + specialOffers before calling to avoid N+1.
     *
     * @param  iterable  $camps
     * @return array<int, array<string, mixed>>
     */
    public static function fromCamps(iterable $camps): array
    {
        $markers = [];

        foreach ($camps as $camp) {
            $lat = $camp->latitude ?? $camp->lat ?? null;
            $lng = $camp->longitude ?? $camp->lng ?? null;
            if (empty($lat) || empty($lng)) {
                continue;
            }

            $image = $camp->thumbnail_path ?? null;
            if ($image && function_exists('media_url')) {
                $image = media_url($image);
            }

            $price = null;
            if (method_exists($camp, 'getLowestAccommodationOrOfferPrice')) {
                $price = $camp->getLowestAccommodationOrOfferPrice();
            } elseif (method_exists($camp, 'getLowestPrice')) {
                $raw = $camp->getLowestPrice();
                $price = $raw > 0 ? (float) $raw : null;
            }

            $markers[] = [
                'id' => (int) ($camp->id ?? 0),
                'lat' => (float) $lat,
                'lng' => (float) $lng,
                'variant' => 'camp',
                'pillar' => 'camp',
                'title' => (string) ($camp->title ?? ''),
                'url' => route('vacations.camps.show', $camp->slug),
                'location' => (string) ($camp->location ?? $camp->city ?? ''),
                'image' => (string) ($image ?? ''),
                'price' => $price,
                'priceLabel' => $price !== null
                    ? __('vacations.price_from_per_night', ['price' => '€' . number_format($price, 0)])
                    : null,
                'badge' => __('vacations.badge_camp'),
                'cta' => __('vacations.view_details'),
            ];
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

            $image = $vacation->thumbnail_path ?? $vacation->image ?? null;
            if ($image && function_exists('media_url')) {
                $image = media_url($image);
            }

            $price = null;
            if (method_exists($vacation, 'getLowestAccommodationOrOfferPrice')) {
                $price = $vacation->getLowestAccommodationOrOfferPrice();
            } elseif (method_exists($vacation, 'getLowestPrice')) {
                $raw = $vacation->getLowestPrice();
                $price = $raw > 0 ? (float) $raw : null;
            } elseif (isset($vacation->price)) {
                $price = $vacation->price;
            }

            $markers[] = [
                'id' => $id,
                'lat' => (float) $lat,
                'lng' => (float) $lng,
                'variant' => $isGray ? 'gray' : 'camp',
                'pillar' => 'camp',
                'title' => (string) $title,
                'url' => $url,
                'location' => (string) ($vacation->location ?? $vacation->city ?? ''),
                'image' => (string) ($image ?? ''),
                'price' => $price,
                'priceLabel' => $price !== null
                    ? __('vacations.price_from_per_night', ['price' => '€' . number_format((float) $price, 0)])
                    : null,
                'badge' => __('vacations.badge_camp'),
                'cta' => __('vacations.view_details'),
            ];
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
            if (!$mapped || empty($mapped['lat']) || empty($mapped['lng'])) {
                continue;
            }
            $markers[] = $mapped;
        }

        return $markers;
    }

    public static function toJson(array $markers): string
    {
        return json_encode(array_values($markers), JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    }
}
