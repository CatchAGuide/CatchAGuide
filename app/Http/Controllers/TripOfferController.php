<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Services\Trip\TripCacheService;
use App\Services\Trip\TripOfferViewMapper;
use Illuminate\View\View;

class TripOfferController extends Controller
{
    public function __construct(
        private TripOfferViewMapper $mapper,
        private TripCacheService $cache
    ) {}

    public function show(string $slug): View
    {
        $trip = Trip::with(['availabilityDates'])
            ->where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        $tripView = $this->cache->rememberTripOfferViewModel($slug, fn () => $this->mapper->map($trip));

        $gallery = $this->buildGallery($trip);
        $availabilityCards = $this->buildAvailabilityCards($trip);

        $tripOfferData = [
            'gallery' => $gallery['all'] ?? [],
            'map' => $this->getMapDataForScript($tripView),
        ];

        return view('pages.trips.show', [
            'tripView' => $tripView,
            'gallery' => $gallery,
            'availabilityCards' => $availabilityCards,
            'tripOfferData' => $tripOfferData,
        ]);
    }

    private function getMapDataForScript(array $tripView): ?array
    {
        $lat = $tripView['coordinates']['lat'] ?? null;
        $lng = $tripView['coordinates']['lng'] ?? null;
        if ($lat === null || $lng === null || ! is_numeric($lat) || ! is_numeric($lng)) {
            return null;
        }
        return [
            'lat' => (float) $lat,
            'lng' => (float) $lng,
            'title' => $tripView['title'] ?? '',
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
            ->where('departure_date', '>=', now()->toDateString())
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

