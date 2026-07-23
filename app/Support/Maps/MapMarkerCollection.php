<?php

namespace App\Support\Maps;

use Illuminate\Support\Facades\View;

class MapMarkerCollection
{
    /**
     * @param  iterable  $guidings
     * @param  array<int>  $grayIds
     * @return array<int, array<string, mixed>>
     */
    public static function fromGuidings(iterable $guidings, array $grayIds = []): array
    {
        $markers = [];

        foreach ($guidings as $guiding) {
            if (empty($guiding->lat) || empty($guiding->lng)) {
                continue;
            }

            $id = (int) $guiding->id;
            $isGray = in_array($id, $grayIds, true) || in_array($id, array_map('intval', $grayIds), true);

            $markers[] = [
                'id' => $id,
                'lat' => (float) $guiding->lat,
                'lng' => (float) $guiding->lng,
                'variant' => $isGray ? 'gray' : 'primary',
                'title' => method_exists($guiding, 'getTranslation')
                    ? (string) translate($guiding->title)
                    : (string) ($guiding->title ?? ''),
                'popupHtml' => View::make('components.maps.popup-card', [
                    'title' => translate($guiding->title ?? ''),
                    'url' => route('guidings.show', [$guiding->id, $guiding->slug]),
                    'location' => $guiding->location ?? '',
                    'image' => function_exists('media_url') ? media_url($guiding->thumbnail_path ?? null) : ($guiding->thumbnail_path ?? ''),
                    'price' => method_exists($guiding, 'getLowestPrice') ? $guiding->getLowestPrice() : null,
                    'showPrice' => true,
                ])->render(),
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

        foreach ($vacations as $vacation) {
            $lat = $vacation->latitude ?? $vacation->lat ?? null;
            $lng = $vacation->longitude ?? $vacation->lng ?? null;
            if (empty($lat) || empty($lng)) {
                continue;
            }

            $id = (int) $vacation->id;
            $isGray = in_array($id, $grayIds, true) || in_array($id, array_map('intval', $grayIds), true);

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
            if (method_exists($vacation, 'getLowestPrice')) {
                $price = $vacation->getLowestPrice();
            } elseif (isset($vacation->price)) {
                $price = $vacation->price;
            }

            $markers[] = [
                'id' => $id,
                'lat' => (float) $lat,
                'lng' => (float) $lng,
                'variant' => $isGray ? 'gray' : 'primary',
                'title' => (string) $title,
                'popupHtml' => View::make('components.maps.popup-card', [
                    'title' => $title,
                    'url' => $url,
                    'location' => $vacation->location ?? $vacation->city ?? '',
                    'image' => $image ?? '',
                    'price' => $price,
                    'showPrice' => $price !== null,
                ])->render(),
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
