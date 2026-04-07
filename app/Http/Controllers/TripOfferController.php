<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Services\Trip\TripCacheService;
use App\Services\Trip\TripOfferViewMapper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
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

    public function show(Request $request, string $slug): View
    {
        $trip = Trip::with(['availabilityDates'])
            ->where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        $tripView = $this->cache->rememberTripOfferViewModel(
            $slug,
            app()->getLocale(),
            fn () => $this->mapper->map($trip)
        );

        $gallery = $this->buildGallery($trip);
        $presentation = $this->cache->rememberTripOfferPresentationData(
            $slug,
            app()->getLocale(),
            fn () => $this->buildPresentationData($tripView, $gallery)
        );
        $availabilityCards = $this->buildAvailabilityCards($trip);
        $selectedDate = $this->resolveSelectedDate($request, $availabilityCards);

        $tripOfferData = [
            'gallery' => $gallery['all'] ?? [],
            'map' => $this->getMapDataForScript($tripView),
        ];

        return view('pages.trips.show', [
            'tripView' => $tripView,
            'gallery' => $gallery,
            ...$presentation,
            'availabilityCards' => $availabilityCards,
            'tripOfferData' => $tripOfferData,
            'selectedDate' => $selectedDate,
            'contactModalTitle' => !empty($tripView['title']) ? '' . $tripView['title'] : '',
        ]);
    }

    private function resolveSelectedDate(Request $request, array $availabilityCards): ?string
    {
        $candidate = $request->query('departure_date');
        if (!is_string($candidate) || $candidate === '') {
            $candidate = Session::get('selected_date');
        }

        if (!is_string($candidate) || $candidate === '') {
            return null;
        }

        // Only allow selecting future, non-fully-booked dates shown on the page.
        $allowed = [];
        foreach ($availabilityCards as $card) {
            $date = $card['departure_date'] ?? null;
            $status = $card['availability_status'] ?? null;
            if (is_string($date) && $date !== '' && $status !== 'fully_booked') {
                $allowed[$date] = true;
            }
        }

        if (!isset($allowed[$candidate])) {
            return null;
        }

        Session::put('selected_date', $candidate);
        return $candidate;
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

            $locale = app()->getLocale();
            $formatShort = function ($d) use ($locale) {
                if (!$d) return null;
                try {
                    return $d->copy()->locale($locale)->isoFormat('DD. MMM YYYY');
                } catch (\Throwable $e) {
                    // Fallback (English month abbreviations)
                    return $d->format('d. M Y');
                }
            };

            return [
                'month' => $date ? $date->copy()->locale($locale)->isoFormat('MMM') : null,
                'day' => $date ? $date->format('d') : null,
                'weekday' => $date ? $date->copy()->locale($locale)->isoFormat('ddd') : null,
                'date_formatted' => $formatShort($date),
                'departure_date' => $date?->toDateString(),
                'return_date_formatted' => $formatShort($returnDate),
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
            return 'limited';
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

    private function buildPresentationData(array $tripView, array $gallery): array
    {
        $includedItems = array_values(array_filter((array) ($tripView['included'] ?? []), fn ($item) => $this->isFilled(is_array($item) ? ($item['label'] ?? null) : $item)));
        $excludedItems = array_values(array_filter((array) ($tripView['excluded'] ?? []), fn ($item) => $this->isFilled(is_array($item) ? ($item['label'] ?? null) : $item)));

        $tripScheduleItems = array_values(array_filter((array) ($tripView['trip_schedule'] ?? []), function ($item): bool {
            if (!is_array($item)) {
                return false;
            }

            return $this->isFilled($item['time'] ?? null)
                || $this->isFilled($item['day_label'] ?? null)
                || $this->isFilled($item['description'] ?? null);
        }));

        $acc = (array) ($tripView['accommodation'] ?? []);
        $accRoomTypes = array_values(array_filter((array) ($acc['room_types'] ?? []), fn ($rt) => $this->isFilled($rt)));
        $accCatering = array_values(array_filter((array) ($acc['catering'] ?? []), fn ($meal) => $this->isFilled($meal)));
        $hasAccommodationContent =
            $this->isFilled($acc['name'] ?? null) ||
            $this->isFilled($acc['description'] ?? null) ||
            !empty($accRoomTypes) ||
            !empty($accCatering) ||
            $this->isFilled($acc['distance_to_water'] ?? null) ||
            $this->isFilled($acc['nearest_airport'] ?? null) ||
            $this->isFilled($acc['arrival_day'] ?? null) ||
            $this->isFilled($acc['best_arrival_options'] ?? null) ||
            $this->isFilled($acc['meeting_point'] ?? null);

        $prov = (array) ($tripView['provider'] ?? []);
        $provCertifications = array_values(array_filter((array) ($prov['certifications_list'] ?? []), fn ($cert) => $this->isFilled($cert)));
        $provGuideLanguages = array_values(array_filter((array) ($prov['guide_languages'] ?? []), fn ($lang) => $this->isFilled($lang)));
        $hasGuideContent =
            $this->isFilled($prov['photo'] ?? null) ||
            $this->isFilled($prov['name'] ?? null) ||
            $this->isFilled($prov['experience'] ?? null) ||
            !empty($provCertifications) ||
            !empty($provGuideLanguages);

        $boat = (array) ($tripView['boat'] ?? []);
        $boatFeatures = array_values(array_filter((array) ($boat['features'] ?? []), fn ($feat) => $this->isFilled($feat)));
        $hasBoatContent =
            $this->isFilled($boat['boat_type'] ?? null) ||
            $this->isFilled($boat['boat_staff'] ?? null) ||
            !empty($boatFeatures) ||
            $this->isFilled($boat['boat_information'] ?? null);

        $additionalInfoItems = array_values(array_filter((array) ($tripView['additional_info_structured'] ?? []), function ($item): bool {
            if (!is_array($item)) {
                return false;
            }

            return $this->isFilled($item['label'] ?? null) && $this->isFilled($item['value'] ?? null);
        }));

        $nonFishingActivities = array_values(array_filter((array) ($tripView['non_fishing_activities_list'] ?? []), fn ($activity) => $this->isFilled($activity)));

        return [
            'primaryImage' => $gallery['primaryImage'] ?? null,
            'topRightImages' => $gallery['topRightImages'] ?? [],
            'bottomStripImages' => $gallery['bottomStripImages'] ?? [],
            'galleryImages' => $gallery['all'] ?? [],
            'remainingGalleryCount' => $gallery['remainingGalleryCount'] ?? 0,
            'includedItems' => $includedItems,
            'excludedItems' => $excludedItems,
            'tripScheduleItems' => $tripScheduleItems,
            'acc' => $acc,
            'accRoomTypes' => $accRoomTypes,
            'accCatering' => $accCatering,
            'hasAccommodationContent' => $hasAccommodationContent,
            'prov' => $prov,
            'provCertifications' => $provCertifications,
            'provGuideLanguages' => $provGuideLanguages,
            'hasGuideContent' => $hasGuideContent,
            'boat' => $boat,
            'boatFeatures' => $boatFeatures,
            'hasBoatContent' => $hasBoatContent,
            'additionalInfoItems' => $additionalInfoItems,
            'nonFishingActivities' => $nonFishingActivities,
        ];
    }

    private function isFilled(mixed $value): bool
    {
        if ($value === null) {
            return false;
        }

        if (is_string($value)) {
            $normalized = str_replace('&nbsp;', ' ', $value);
            return trim(strip_tags($normalized)) !== '';
        }

        if (is_array($value)) {
            foreach ($value as $item) {
                if ($this->isFilled($item)) {
                    return true;
                }
            }

            return false;
        }

        return true;
    }
}

