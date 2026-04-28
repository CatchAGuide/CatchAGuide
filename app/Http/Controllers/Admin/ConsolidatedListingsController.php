<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use App\Models\Camp;
use App\Models\CampVacationBooking;
use App\Models\Guiding;
use App\Models\Trip;
use App\Models\TripBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ConsolidatedListingsController extends Controller
{
    private const TYPE_GUIDING = 'guiding';
    private const TYPE_ACCOMMODATION = 'accommodation';
    private const TYPE_CAMP = 'camp';
    private const TYPE_TRIP = 'trip';

    public function index(Request $request)
    {
        $filters = $this->normalizeFilters($request);

        $rows = $this->buildRows($filters);

        // Default sort: newest first within each type by id desc, then type.
        $rows = $rows
            ->sortByDesc(fn (array $r) => (int) ($r['id'] ?? 0))
            ->sortBy(fn (array $r) => $r['type'] ?? '')
            ->values();

        return view('admin.pages.listings.consolidated', [
            'rows' => $rows,
            'filters' => $filters,
            'typeOptions' => $this->typeOptions(),
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $this->normalizeFilters($request);
        $rows = $this->buildRows($filters);

        $filename = 'consolidated_listings_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return response()->stream(function () use ($rows) {
            $out = fopen('php://output', 'w');
            if ($out === false) {
                return;
            }

            // UTF-8 BOM for Excel compatibility
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, [
                'Type',
                'ID',
                'Title',
                'Description',
                'Price (low)',
                'Price (high)',
                'Currency',
                'Owner/Provider',
                'Images',
                'Status',
                'Bookings',
                'Location',
                'Created at',
                'Edit URL',
                'Public URL',
            ]);

            foreach ($rows as $r) {
                fputcsv($out, [
                    $r['type_label'] ?? $r['type'] ?? '',
                    $r['id'] ?? '',
                    $r['title'] ?? '',
                    $this->oneLine($r['description'] ?? ''),
                    $r['price_low'] ?? '',
                    $r['price_high'] ?? '',
                    $r['currency'] ?? '',
                    $r['owner_name'] ?? '',
                    $r['images_count'] ?? 0,
                    $r['status_label'] ?? $r['status'] ?? '',
                    $r['bookings_count'] ?? 0,
                    $r['location'] ?? '',
                    $r['created_at'] ?? '',
                    $r['admin_edit_url'] ?? '',
                    $r['public_url'] ?? '',
                ]);
            }

            fclose($out);
        }, 200, $headers);
    }

    private function buildRows(array $filters): Collection
    {
        $types = $filters['types'];

        $rows = collect();

        if (in_array(self::TYPE_GUIDING, $types, true)) {
            $rows = $rows->concat($this->guidingRows($filters));
        }
        if (in_array(self::TYPE_ACCOMMODATION, $types, true)) {
            $rows = $rows->concat($this->accommodationRows($filters));
        }
        if (in_array(self::TYPE_CAMP, $types, true)) {
            $rows = $rows->concat($this->campRows($filters));
        }
        if (in_array(self::TYPE_TRIP, $types, true)) {
            $rows = $rows->concat($this->tripRows($filters));
        }

        // Cross-type filters that are easiest post-normalization
        $rows = $rows->filter(function (array $r) use ($filters) {
            if ($filters['status'] !== null && $filters['status'] !== '') {
                if (($r['status'] ?? '') !== $filters['status']) {
                    return false;
                }
            }

            if ($filters['has_bookings'] === true && (($r['bookings_count'] ?? 0) <= 0)) {
                return false;
            }
            if ($filters['has_bookings'] === false && (($r['bookings_count'] ?? 0) > 0)) {
                return false;
            }

            if ($filters['price_min'] !== null) {
                $low = $r['price_low'] !== null ? (float) $r['price_low'] : null;
                if ($low === null || $low < $filters['price_min']) {
                    return false;
                }
            }
            if ($filters['price_max'] !== null) {
                $low = $r['price_low'] !== null ? (float) $r['price_low'] : null;
                if ($low !== null && $low > $filters['price_max']) {
                    return false;
                }
            }

            if ($filters['q'] !== '') {
                $hay = strtolower(implode(' ', array_filter([
                    $r['title'] ?? null,
                    $r['description'] ?? null,
                    $r['location'] ?? null,
                    $r['owner_name'] ?? null,
                ])));
                if (strpos($hay, $filters['q']) === false) {
                    return false;
                }
            }

            if ($filters['owner_q'] !== '') {
                $owner = strtolower((string) ($r['owner_name'] ?? ''));
                if ($owner === '' || strpos($owner, $filters['owner_q']) === false) {
                    return false;
                }
            }

            return true;
        })->values();

        return $rows;
    }

    private function guidingRows(array $filters): Collection
    {
        $query = Guiding::query()
            ->with(['user.information'])
            ->withCount(['bookings', 'ratings']);

        $guidings = $query->get();

        return $guidings->map(function (Guiding $g) {
            [$low, $high] = $this->guidingPriceRange($g);
            $imagesCount = $this->countImages($g->thumbnail_path ?? null, $g->gallery_images ?? null);

            $status = match ((int) ($g->status ?? 0)) {
                1 => 'active',
                2 => 'draft',
                default => 'inactive',
            };

            return [
                'type' => self::TYPE_GUIDING,
                'type_label' => 'Guiding',
                'id' => $g->id,
                'title' => (string) ($g->title ?? ''),
                'meta' => array_values(array_filter([
                    $g->max_guests ? [
                        'icon' => 'fa-users',
                        'tooltip' => 'Max guests',
                        'text' => (string) $g->max_guests,
                        'tone' => 'indigo',
                    ] : null,
                    $g->duration ? [
                        'icon' => 'fa-clock',
                        'tooltip' => 'Duration',
                        'text' => trim((string) $g->duration . ' ' . (string) ($g->duration_type ?? '')),
                        'tone' => 'slate',
                    ] : null,
                    !empty($g->tour_type) ? [
                        'icon' => 'fa-tags',
                        'tooltip' => 'Tour type',
                        'text' => (string) $g->tour_type,
                        'tone' => 'amber',
                    ] : null,
                    (($g->ratings_count ?? 0) > 0) ? [
                        'icon' => 'fa-star',
                        'tooltip' => 'Ratings',
                        'text' => (string) $g->ratings_count,
                        'tone' => 'yellow',
                    ] : null,
                    [
                        'icon' => 'fa-calendar-check',
                        'tooltip' => 'Bookings',
                        'text' => (string) ((int) ($g->bookings_count ?? 0)),
                        'tone' => ((int) ($g->bookings_count ?? 0)) > 0 ? 'emerald' : 'slate',
                    ],
                    ($imagesCount > 0) ? [
                        'icon' => 'fa-images',
                        'tooltip' => 'Images',
                        'text' => (string) $imagesCount,
                        'tone' => 'cyan',
                    ] : null,
                ])),
                'description' => $this->stripHtml((string) ($g->description ?? '')),
                'price_low' => $low,
                'price_high' => $high,
                'currency' => 'EUR',
                'owner_name' => (string) ($g->user->full_name ?? $g->user->name ?? ''),
                'owner_photo_url' => $this->guideProfilePhotoUrl($g->user->profil_image ?? null),
                'owner_email' => (string) ($g->user->email ?? ''),
                'owner_phone' => method_exists($g->user, 'getFullPhoneNumber') ? $g->user->getFullPhoneNumber() : ((string) ($g->user->phone ?? '')),
                'owner_city' => (string) ($g->user->information->city ?? ''),
                'images_count' => $imagesCount,
                'status' => $status,
                'status_label' => ucfirst($status),
                'bookings_count' => (int) ($g->bookings_count ?? 0),
                'location' => (string) ($g->location ?? ''),
                'created_at' => optional($g->created_at)->format('Y-m-d H:i:s'),
                'created_at_ts' => optional($g->created_at)->timestamp ?? 0,
                'admin_edit_url' => route('admin.guidings.edit', $g),
                'public_url' => route('admin.guidings.show', $g), // admin show is consistent
            ];
        })->values();
    }

    private function accommodationRows(array $filters): Collection
    {
        $items = Accommodation::query()
            ->with(['user.information', 'accommodationType'])
            ->get();

        return $items->map(function (Accommodation $a) {
            [$low, $high] = $this->accommodationPriceRange($a);
            $imagesCount = $this->countImages($a->thumbnail_path ?? null, $a->gallery_images ?? null);

            $location = $a->location;
            if (!$location) {
                $parts = array_filter([$a->city ?? null, $a->region ?? null, $a->country ?? null]);
                $location = $parts ? implode(', ', $parts) : '';
            }

            $description = '';
            $details = $a->accommodation_details ?? [];
            if (is_array($details) && !empty($details)) {
                $first = $details[0] ?? null;
                if (is_array($first)) {
                    $label = $first['name'] ?? $first['name_en'] ?? null;
                    $val = $first['value'] ?? null;
                    if ($label || $val) {
                        $description = trim(($label ? $label . ': ' : '') . ($val ?? ''));
                    }
                }
            }

            return [
                'type' => self::TYPE_ACCOMMODATION,
                'type_label' => 'Accommodation',
                'id' => $a->id,
                'title' => (string) ($a->title ?? ''),
                'meta' => array_values(array_filter([
                    ($a->accommodationType?->name) ? [
                        'icon' => 'fa-home',
                        'tooltip' => 'Accommodation type',
                        'text' => (string) $a->accommodationType->name,
                        'tone' => 'emerald',
                    ] : null,
                    ($a->max_occupancy) ? [
                        'icon' => 'fa-user-friends',
                        'tooltip' => 'Max occupancy',
                        'text' => (string) $a->max_occupancy,
                        'tone' => 'indigo',
                    ] : null,
                    ($a->minimum_stay_nights) ? [
                        'icon' => 'fa-moon',
                        'tooltip' => 'Minimum stay (nights)',
                        'text' => (string) $a->minimum_stay_nights,
                        'tone' => 'violet',
                    ] : null,
                    [
                        'icon' => 'fa-calendar-check',
                        'tooltip' => 'Bookings',
                        'text' => '0',
                        'tone' => 'slate',
                    ],
                    ($imagesCount > 0) ? [
                        'icon' => 'fa-images',
                        'tooltip' => 'Images',
                        'text' => (string) $imagesCount,
                        'tone' => 'cyan',
                    ] : null,
                ])),
                'description' => $this->stripHtml($description),
                'price_low' => $low,
                'price_high' => $high,
                'currency' => (string) ($a->currency ?? 'EUR'),
                'owner_name' => (string) ($a->user->full_name ?? $a->user->name ?? ''),
                'owner_photo_url' => $this->guideProfilePhotoUrl($a->user->profil_image ?? null),
                'owner_email' => (string) ($a->user->email ?? ''),
                'owner_phone' => method_exists($a->user, 'getFullPhoneNumber') ? $a->user->getFullPhoneNumber() : ((string) ($a->user->phone ?? '')),
                'owner_city' => (string) ($a->user->information->city ?? ''),
                'images_count' => $imagesCount,
                'status' => (string) ($a->status ?? ''),
                'status_label' => ucfirst((string) ($a->status ?? '')),
                'bookings_count' => 0,
                'location' => (string) $location,
                'created_at' => optional($a->created_at)->format('Y-m-d H:i:s'),
                'created_at_ts' => optional($a->created_at)->timestamp ?? 0,
                'admin_edit_url' => route('admin.accommodations.edit', $a),
                'public_url' => route('admin.accommodations.show', $a),
            ];
        })->values();
    }

    private function campRows(array $filters): Collection
    {
        $camps = Camp::query()
            ->with(['user.information'])
            ->withCount(['accommodations', 'rentalBoats', 'guidings', 'specialOffers'])
            ->get();

        $counts = CampVacationBooking::query()
            ->selectRaw('source_id, COUNT(*) as c')
            ->where('source_type', CampVacationBooking::SOURCE_CAMP)
            ->groupBy('source_id')
            ->pluck('c', 'source_id');

        return $camps->map(function (Camp $c) use ($counts) {
            $low = $c->getLowestAccommodationOrOfferPrice();
            $high = null;
            $imagesCount = $this->countImages($c->thumbnail_path ?? null, $c->gallery_images ?? null);

            $location = $c->location;
            if (!$location) {
                $parts = array_filter([$c->city ?? null, $c->region ?? null, $c->country ?? null]);
                $location = $parts ? implode(', ', $parts) : '';
            }

            $desc = (string) ($c->description_camp ?? $c->description_area ?? $c->description_fishing ?? '');

            return [
                'type' => self::TYPE_CAMP,
                'type_label' => 'Camp',
                'id' => $c->id,
                'title' => (string) ($c->title ?? ''),
                'meta' => array_values(array_filter([
                    (($c->accommodations_count ?? 0) > 0) ? [
                        'icon' => 'fa-bed',
                        'tooltip' => 'Linked accommodations',
                        'text' => (string) $c->accommodations_count,
                        'tone' => 'emerald',
                    ] : null,
                    (($c->rental_boats_count ?? 0) > 0) ? [
                        'icon' => 'fa-ship',
                        'tooltip' => 'Linked rental boats',
                        'text' => (string) $c->rental_boats_count,
                        'tone' => 'cyan',
                    ] : null,
                    (($c->guidings_count ?? 0) > 0) ? [
                        'icon' => 'fa-fish',
                        'tooltip' => 'Linked guidings',
                        'text' => (string) $c->guidings_count,
                        'tone' => 'indigo',
                    ] : null,
                    (($c->special_offers_count ?? 0) > 0) ? [
                        'icon' => 'fa-bolt',
                        'tooltip' => 'Special offers',
                        'text' => (string) $c->special_offers_count,
                        'tone' => 'amber',
                    ] : null,
                    [
                        'icon' => 'fa-calendar-check',
                        'tooltip' => 'Bookings',
                        'text' => (string) ((int) ($counts[$c->id] ?? 0)),
                        'tone' => ((int) ($counts[$c->id] ?? 0)) > 0 ? 'emerald' : 'slate',
                    ],
                    ($imagesCount > 0) ? [
                        'icon' => 'fa-images',
                        'tooltip' => 'Images',
                        'text' => (string) $imagesCount,
                        'tone' => 'slate',
                    ] : null,
                ])),
                'description' => $this->stripHtml($desc),
                'price_low' => $low !== null ? round((float) $low, 2) : null,
                'price_high' => $high,
                'currency' => 'EUR',
                'owner_name' => (string) ($c->user->full_name ?? $c->user->name ?? ''),
                'owner_photo_url' => $this->guideProfilePhotoUrl($c->user->profil_image ?? null),
                'owner_email' => (string) ($c->user->email ?? ''),
                'owner_phone' => method_exists($c->user, 'getFullPhoneNumber') ? $c->user->getFullPhoneNumber() : ((string) ($c->user->phone ?? '')),
                'owner_city' => (string) ($c->user->information->city ?? ''),
                'images_count' => $imagesCount,
                'status' => (string) ($c->status ?? ''),
                'status_label' => ucfirst((string) ($c->status ?? '')),
                'bookings_count' => (int) ($counts[$c->id] ?? 0),
                'location' => (string) $location,
                'created_at' => optional($c->created_at)->format('Y-m-d H:i:s'),
                'created_at_ts' => optional($c->created_at)->timestamp ?? 0,
                'admin_edit_url' => route('admin.camps.edit', $c->id),
                'public_url' => route('admin.camps.show', $c->id),
            ];
        })->values();
    }

    private function tripRows(array $filters): Collection
    {
        $trips = Trip::query()
            ->with(['user.information'])
            ->get();

        $counts = TripBooking::query()
            ->selectRaw('source_id, COUNT(*) as c')
            ->where('source_type', TripBooking::SOURCE_TRIP)
            ->groupBy('source_id')
            ->pluck('c', 'source_id');

        return $trips->map(function (Trip $t) use ($counts) {
            $low = $t->price_per_person !== null ? round((float) $t->price_per_person, 2) : null;
            $high = $low;
            $imagesCount = $this->countImages($t->thumbnail_path ?? null, $t->gallery_images ?? null);

            $location = $t->location;
            if (!$location) {
                $parts = array_filter([$t->city ?? null, $t->region ?? null, $t->country ?? null]);
                $location = $parts ? implode(', ', $parts) : '';
            }

            return [
                'type' => self::TYPE_TRIP,
                'type_label' => 'Trip',
                'id' => $t->id,
                'title' => (string) ($t->title ?? ''),
                'meta' => array_values(array_filter([
                    (($t->duration_nights || $t->duration_days)) ? [
                        'icon' => 'fa-calendar-alt',
                        'tooltip' => 'Duration',
                        'text' => trim(($t->duration_nights ? ($t->duration_nights . 'n') : '') . ' ' . ($t->duration_days ? ($t->duration_days . 'd') : '')),
                        'tone' => 'violet',
                    ] : null,
                    ($t->group_size_max) ? [
                        'icon' => 'fa-users',
                        'tooltip' => 'Group size (max)',
                        'text' => (string) $t->group_size_max,
                        'tone' => 'indigo',
                    ] : null,
                    ($t->best_season_from && $t->best_season_to) ? [
                        'icon' => 'fa-sun',
                        'tooltip' => 'Best season',
                        'text' => (string) $t->best_season_from . '–' . (string) $t->best_season_to,
                        'tone' => 'amber',
                    ] : null,
                    [
                        'icon' => 'fa-calendar-check',
                        'tooltip' => 'Booking requests',
                        'text' => (string) ((int) ($counts[$t->id] ?? 0)),
                        'tone' => ((int) ($counts[$t->id] ?? 0)) > 0 ? 'emerald' : 'slate',
                    ],
                    ($imagesCount > 0) ? [
                        'icon' => 'fa-images',
                        'tooltip' => 'Images',
                        'text' => (string) $imagesCount,
                        'tone' => 'cyan',
                    ] : null,
                ])),
                'description' => $this->stripHtml((string) ($t->description ?? '')),
                'price_low' => $low,
                'price_high' => $high,
                'currency' => (string) ($t->currency ?? 'EUR'),
                'owner_name' => (string) ($t->provider_name ?? $t->user->full_name ?? $t->user->name ?? ''),
                'owner_photo_url' => $this->tripProviderPhotoUrl($t->provider_photo ?? null) ?: $this->guideProfilePhotoUrl($t->user->profil_image ?? null),
                'owner_email' => (string) ($t->user->email ?? ''),
                'owner_phone' => method_exists($t->user, 'getFullPhoneNumber') ? $t->user->getFullPhoneNumber() : ((string) ($t->user->phone ?? '')),
                'owner_city' => (string) ($t->user->information->city ?? ''),
                'images_count' => $imagesCount,
                'status' => (string) ($t->status ?? ''),
                'status_label' => ucfirst((string) ($t->status ?? '')),
                'bookings_count' => (int) ($counts[$t->id] ?? 0),
                'location' => (string) $location,
                'created_at' => optional($t->created_at)->format('Y-m-d H:i:s'),
                'created_at_ts' => optional($t->created_at)->timestamp ?? 0,
                'admin_edit_url' => route('admin.trips.edit', $t->id),
                'public_url' => $t->slug ? route('trips.show', $t->slug) : null,
            ];
        })->values();
    }

    private function guideProfilePhotoUrl(?string $profileImage): ?string
    {
        $img = trim((string) ($profileImage ?? ''));
        if ($img === '') {
            return null;
        }
        if (str_starts_with($img, 'http') || str_starts_with($img, '//')) {
            return $img;
        }
        // Some tables store only filename; some might store a relative path.
        if (str_contains($img, '/')) {
            return asset(ltrim($img, '/'));
        }
        // Existing admin guidings list uses this path convention.
        return asset('uploads/profile_images/' . ltrim($img, '/'));
    }

    private function tripProviderPhotoUrl(?string $path): ?string
    {
        $p = trim((string) ($path ?? ''));
        if ($p === '') {
            return null;
        }
        if (str_starts_with($p, 'http') || str_starts_with($p, '//')) {
            return $p;
        }
        return asset('storage/' . ltrim($p, '/'));
    }

    private function guidingPriceRange(Guiding $g): array
    {
        $tiers = [];

        $rawPrices = $g->prices ?? null;
        $prices = is_string($rawPrices) ? json_decode($rawPrices, true) : $rawPrices;
        if (is_array($prices)) {
            foreach ($prices as $p) {
                if (!is_array($p)) {
                    continue;
                }
                $amount = isset($p['amount']) ? (float) $p['amount'] : null;
                $persons = isset($p['person']) ? (int) $p['person'] : null;
                if ($amount !== null && $amount > 0) {
                    $tiers[] = $persons && $persons > 1 ? ($amount / $persons) : $amount;
                }
            }
        }

        foreach (['price', 'price_two_persons', 'price_three_persons', 'price_four_persons', 'price_five_persons'] as $col) {
            $v = $g->{$col} ?? null;
            if ($v !== null && is_numeric($v) && (float) $v > 0) {
                $tiers[] = (float) $v;
            }
        }

        $tiers = array_values(array_filter($tiers, fn ($x) => is_numeric($x) && (float) $x > 0));
        if (empty($tiers)) {
            return [null, null];
        }

        return [round((float) min($tiers), 2), round((float) max($tiers), 2)];
    }

    private function accommodationPriceRange(Accommodation $a): array
    {
        $tiers = [];
        $pricing = $a->per_person_pricing ?? [];
        if (is_string($pricing)) {
            $pricing = json_decode($pricing, true);
        }
        if (is_array($pricing)) {
            foreach ($pricing as $tier) {
                if (!is_array($tier)) {
                    continue;
                }
                if (!empty($tier['price_per_night']) && is_numeric($tier['price_per_night'])) {
                    $tiers[] = (float) $tier['price_per_night'];
                }
                if (!empty($tier['price_per_week']) && is_numeric($tier['price_per_week'])) {
                    $tiers[] = (float) $tier['price_per_week'] / 7;
                }
            }
        }
        $tiers = array_values(array_filter($tiers, fn ($x) => is_numeric($x) && (float) $x > 0));
        if (empty($tiers)) {
            return [null, null];
        }
        return [round((float) min($tiers), 2), round((float) max($tiers), 2)];
    }

    private function countImages(?string $thumbnailPath, $galleryImages): int
    {
        $imgs = [];
        $thumb = trim((string) ($thumbnailPath ?? ''));
        if ($thumb !== '') {
            $imgs[] = $thumb;
        }

        $gallery = $galleryImages;
        if (is_string($gallery)) {
            $gallery = json_decode($gallery, true);
        }
        if (is_array($gallery)) {
            foreach ($gallery as $img) {
                if (!empty($img)) {
                    $imgs[] = (string) $img;
                }
            }
        }

        $imgs = array_values(array_unique(array_filter(array_map('trim', $imgs))));
        return count($imgs);
    }

    private function normalizeFilters(Request $request): array
    {
        $types = $request->input('types');
        if (is_string($types)) {
            $types = array_filter(array_map('trim', explode(',', $types)));
        }
        if (!is_array($types) || empty($types)) {
            $types = array_keys($this->typeOptions());
        }
        $types = array_values(array_intersect($types, array_keys($this->typeOptions())));

        $status = $request->input('status');
        $status = is_string($status) ? trim($status) : $status;
        if ($status === '') {
            $status = null;
        }
        if ($status !== null && !array_key_exists($status, $this->statusOptions())) {
            $status = null;
        }

        $hasBookings = $request->input('has_bookings');
        $hasBookings = is_string($hasBookings) ? trim($hasBookings) : $hasBookings;
        $hasBookings = match ($hasBookings) {
            '1', 1, true, 'true' => true,
            '0', 0, false, 'false' => false,
            default => null,
        };

        $priceMin = $request->input('price_min');
        $priceMax = $request->input('price_max');
        $priceMin = is_numeric($priceMin) ? (float) $priceMin : null;
        $priceMax = is_numeric($priceMax) ? (float) $priceMax : null;

        $q = strtolower(trim((string) $request->input('q', '')));
        $ownerQ = strtolower(trim((string) $request->input('owner_q', '')));

        return [
            'types' => $types,
            'status' => $status,
            'has_bookings' => $hasBookings,
            'price_min' => $priceMin,
            'price_max' => $priceMax,
            'q' => $q,
            'owner_q' => $ownerQ,
        ];
    }

    private function typeOptions(): array
    {
        return [
            self::TYPE_GUIDING => 'Guidings',
            self::TYPE_ACCOMMODATION => 'Accommodations',
            self::TYPE_CAMP => 'Camps',
            self::TYPE_TRIP => 'Trips',
        ];
    }

    private function statusOptions(): array
    {
        return [
            '' => 'Any',
            'active' => 'Active',
            'draft' => 'Draft',
            'inactive' => 'Inactive',
        ];
    }

    private function stripHtml(string $text): string
    {
        $text = html_entity_decode($text, ENT_QUOTES);
        $text = strip_tags($text);
        return trim(preg_replace('/\s+/', ' ', $text) ?? '');
    }

    private function oneLine(string $text): string
    {
        $t = trim(preg_replace('/\s+/', ' ', $text) ?? '');
        return $t;
    }
}

