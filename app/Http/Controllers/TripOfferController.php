<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use Illuminate\Http\Request;

class TripOfferController extends Controller
{
    public function show(string $slug)
    {
        $trip = Trip::with(['availabilityDates'])
            ->where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        $tripView = $this->mapTrip($trip);
        $gallery = $this->buildGallery($trip);
        $availabilityCards = $this->buildAvailabilityCards($trip);

        return view('pages.trips.show', [
            'trip' => $trip,
            'tripView' => $tripView,
            'gallery' => $gallery,
            'availabilityCards' => $availabilityCards,
        ]);
    }

    private function mapTrip(Trip $trip): array
    {
        $targetSpecies = collect($trip->getTargetSpeciesNames())->pluck('name')->filter()->values()->all();
        $fishingMethods = collect($trip->getFishingMethodNames())->pluck('name')->filter()->values()->all();
        $waterTypes = collect($trip->getWaterTypeNames())->pluck('name')->filter()->values()->all();

        $description = (string) ($trip->description ?? '');
        $descriptionShort = $description;
        $descriptionRest = '';

        if (mb_strlen($description) > 600) {
            $descriptionShort = mb_substr($description, 0, 600);
            $lastDotPos = mb_strrpos($descriptionShort, '.');

            if ($lastDotPos !== false && $lastDotPos > 200) {
                $descriptionShort = mb_substr($descriptionShort, 0, $lastDotPos + 1);
            }

            $descriptionRest = trim(mb_substr($description, mb_strlen($descriptionShort)));
        }

        $rawHighlights = is_array($trip->trip_highlights) ? $trip->trip_highlights : [];
        $highlightItems = [];

        if (is_array($rawHighlights)) {
            foreach ($rawHighlights['items'] ?? [] as $item) {
                $text = is_array($item) ? ($item['text'] ?? '') : (string) $item;
                $text = trim($text);
                if ($text !== '') {
                    $highlightItems[] = $text;
                }
            }

            $structuredKeys = [
                'accommodation_nights' => __('trips.highlight_accommodation'),
                'fishing_days'         => __('trips.highlight_fishing'),
                'travel_days'          => __('trips.highlight_travel'),
            ];

            foreach ($structuredKeys as $key => $template) {
                if (!isset($rawHighlights[$key]) || !is_array($rawHighlights[$key])) {
                    continue;
                }

                $config = $rawHighlights[$key];
                $enabled = (bool) ($config['enabled'] ?? false);
                $value   = $config['value'] ?? null;

                if (! $enabled || $value === null || $value === '' || ! is_scalar($value)) {
                    continue;
                }

                $label = str_replace('x', (string) $value, $template);
                $highlightItems[] = $label;
            }
        }

        if (empty($highlightItems) && ! empty($rawHighlights) && array_is_list($rawHighlights)) {
            foreach ($rawHighlights as $item) {
                $text = is_array($item) ? ($item['text'] ?? '') : (string) $item;
                $text = trim($text);
                if ($text !== '') {
                    $highlightItems[] = $text;
                }
            }
        }

        $additionalInfoList = [];
        if (is_array($trip->additional_info)) {
            foreach ($trip->additional_info as $key => $config) {
                if (!is_array($config)) {
                    continue;
                }

                $enabled = (bool) ($config['enabled'] ?? false);
                $details = $config['details'] ?? null;

                if (! $enabled && ($details === null || $details === '')) {
                    continue;
                }

                $labelKey = 'trips.' . $key;
                $label = __($labelKey);
                if ($label === $labelKey) {
                    $label = ucwords(str_replace('_', ' ', $key));
                }

                $text = $label;
                if ($details) {
                    $text .= ': ' . $details;
                }

                $additionalInfoList[] = $text;
            }
        }

        return [
            'id' => $trip->id,
            'title' => $trip->title,
            'location' => $trip->location,
            'country' => $trip->country,
            'city' => $trip->city,
            'region' => $trip->region,
            'coordinates' => [
                'lat' => $trip->latitude,
                'lng' => $trip->longitude,
            ],
            'duration' => [
                'nights' => $trip->duration_nights,
                'days' => $trip->duration_days,
            ],
            'group_size' => [
                'min' => $trip->group_size_min,
                'max' => $trip->group_size_max,
            ],
            'price' => [
                'per_person' => $trip->price_per_person,
                'single_room_addition' => $trip->price_single_room_addition,
                'currency' => 'EUR',
            ],
            'skill_level' => $trip->skill_level,
            'fishing_style' => $trip->fishing_style,
            'best_season' => [
                'from' => $trip->best_season_from,
                'to' => $trip->best_season_to,
            ],
            'target_species' => $targetSpecies,
            'fishing_methods' => $fishingMethods,
            'water_types' => $waterTypes,
            'trip_schedule' => is_array($trip->trip_schedule) ? $trip->trip_schedule : [],
            'included' => is_array($trip->included) ? $trip->included : [],
            'excluded' => is_array($trip->excluded) ? $trip->excluded : [],
            'additional_info' => $additionalInfoList,
            'cancellation_policy' => $trip->cancellation_policy,
            'provider' => [
                'name' => $trip->provider_name,
                'photo' => $trip->provider_photo,
                'experience' => $trip->provider_experience,
                'certifications' => $trip->provider_certifications,
            ],
            'description' => [
                'full' => $description,
                'intro' => $descriptionShort,
                'rest' => $descriptionRest,
            ],
            'trip_highlights' => $highlightItems,
        ];
    }

    private function buildGallery(Trip $trip): array
    {
        $thumbnail = $this->normalizeImagePath($trip->thumbnail_path);

        $galleryImages = $trip->gallery_images ?? [];
        if (is_string($galleryImages)) {
            $decoded = json_decode($galleryImages, true);
            $galleryImages = is_array($decoded) ? $decoded : [];
        }

        $galleryImages = array_map(function ($path) {
            return $this->normalizeImagePath($path);
        }, $galleryImages);

        $allImages = array_values(array_filter(array_unique(array_merge(
            [$thumbnail],
            $galleryImages
        ))));

        if (empty($allImages)) {
            return [
                'primaryImage' => null,
                'topRightImages' => [],
                'bottomStripImages' => [],
                'remainingGalleryCount' => 0,
                'all' => [],
            ];
        }

        $primaryImage = $allImages[0];
        $topRightImages = array_slice($allImages, 1, 2);
        $bottomStripImages = array_slice($allImages, 3, 5);
        $remainingGalleryCount = max(0, count($allImages) - 8);

        return [
            'primaryImage' => $primaryImage,
            'topRightImages' => $topRightImages,
            'bottomStripImages' => $bottomStripImages,
            'remainingGalleryCount' => $remainingGalleryCount,
            'all' => $allImages,
        ];
    }

    private function buildAvailabilityCards(Trip $trip): array
    {
        $dates = $trip->availabilityDates()
            ->orderBy('departure_date')
            ->get();

        return $dates->map(function ($availability) {
            $date = $availability->departure_date;

            return [
                'month' => $date ? $date->format('M') : null,
                'day' => $date ? $date->format('d') : null,
                'weekday' => $date ? $date->format('D') : null,
                'spots_available' => $availability->spots_available,
                'status' => $availability->status,
                'is_limited' => $availability->spots_available !== null && $availability->spots_available <= 3,
            ];
        })->toArray();
    }

    private function normalizeImagePath(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        if (str_starts_with($path, 'http') || str_starts_with($path, '/')) {
            return $path;
        }

        return '/' . ltrim($path, '/');
    }
}

