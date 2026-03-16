<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Services\Trip\TripCacheService;
use App\Services\Trip\TripOfferViewMapper;
use Illuminate\View\View;

class TripOfferController extends Controller
{
    /**
     * Spots below this number are considered "almost full".
     * Example: 2 => 1 spot left is almost full; 0 is fully booked.
     */
    private int $almostFullThreshold = 2;

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
        $durationDays = $trip->duration_days ?? null;

        $dates = $trip->availabilityDates()
            ->where('departure_date', '>=', now()->toDateString())
            ->orderBy('departure_date')
            ->get();

        return $dates->map(function ($availability) use ($durationDays) {
            $date = $availability->departure_date;
            $spots = $availability->spots_available;
            $availabilityStatus = $this->deriveAvailabilityStatus($spots);

            $returnDate = null;
            if ($date && $durationDays && $durationDays > 0) {
                $returnDate = $date->copy()->addDays((int) $durationDays);
            }

            return [
                'month' => $date ? $date->format('M') : null,
                'day' => $date ? $date->format('d') : null,
                'weekday' => $date ? $date->format('D') : null,
                'date_formatted' => $date ? $date->format('d. F Y') : null,
                'departure_date' => $date?->toDateString(),
                'return_date_formatted' => $returnDate ? $returnDate->format('d. F Y') : null,
                'spots_available' => $spots,
                'availability_status' => $availabilityStatus,
                'is_limited' => $spots !== null && $spots > 0 && $spots < $this->almostFullThreshold,
            ];
        })->toArray();
    }

    /**
     * Derive availability status from spots_available (from admin panel).
     * fully_booked: 0 spots; almost_full: 1 spot; available: 2+ or null.
     */
    private function deriveAvailabilityStatus(?int $spots): string
    {
        if ($spots === 0) {
            return 'fully_booked';
        }
        if ($spots !== null && $spots < $this->almostFullThreshold) {
            return 'almost_full';
        }
        return 'available';
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

