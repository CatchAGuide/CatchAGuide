<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use App\Models\Booking;
use App\Models\Camp;
use App\Models\Guiding;
use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class StrategyController extends Controller
{
    public function index()
    {
        $guidingsActive = (int) Guiding::query()->where('status', 1)->count();
        $tripsActive = (int) Trip::query()->where('status', 'active')->count();
        $campsActive = (int) Camp::query()->where('status', 'active')->count();
        $accommodationsActive = (int) Accommodation::query()->where('status', 'active')->count();

        $missing = [
            'guidings' => [
                'thumbnail' => (int) Guiding::query()->where('status', 1)->where(function ($q) {
                    $q->whereNull('thumbnail_path')->orWhere('thumbnail_path', '');
                })->count(),
                'description' => (int) Guiding::query()->where('status', 1)->where(function ($q) {
                    $q->whereNull('description')->orWhere('description', '');
                })->count(),
                'country' => (int) Guiding::query()->where('status', 1)->where(function ($q) {
                    $q->whereNull('country')->orWhere('country', '');
                })->count(),
            ],
            'trips' => [
                'thumbnail' => (int) Trip::query()->where('status', 'active')->where(function ($q) {
                    $q->whereNull('thumbnail_path')->orWhere('thumbnail_path', '');
                })->count(),
                'description' => (int) Trip::query()->where('status', 'active')->where(function ($q) {
                    $q->whereNull('description')->orWhere('description', '');
                })->count(),
                'country' => (int) Trip::query()->where('status', 'active')->where(function ($q) {
                    $q->whereNull('country')->orWhere('country', '');
                })->count(),
                // JSON-empty checks are DB-dependent; keep this lightweight
                'gallery_empty' => (int) Trip::query()->where('status', 'active')->where(function ($q) {
                    $q->whereNull('gallery_images')->orWhere('gallery_images', '')->orWhere('gallery_images', '[]');
                })->count(),
            ],
            'camps' => [
                'thumbnail' => (int) Camp::query()->where('status', 'active')->where(function ($q) {
                    $q->whereNull('thumbnail_path')->orWhere('thumbnail_path', '');
                })->count(),
                'description' => (int) Camp::query()->where('status', 'active')->where(function ($q) {
                    $q->whereNull('description_camp')->orWhere('description_camp', '');
                })->count(),
                'country' => (int) Camp::query()->where('status', 'active')->where(function ($q) {
                    $q->whereNull('country')->orWhere('country', '');
                })->count(),
                'gallery_empty' => (int) Camp::query()->where('status', 'active')->where(function ($q) {
                    $q->whereNull('gallery_images')->orWhere('gallery_images', '')->orWhere('gallery_images', '[]');
                })->count(),
            ],
            'accommodations' => [
                'thumbnail' => (int) Accommodation::query()->where('status', 'active')->where(function ($q) {
                    $q->whereNull('thumbnail_path')->orWhere('thumbnail_path', '');
                })->count(),
                'details' => (int) Accommodation::query()->where('status', 'active')->where(function ($q) {
                    $q->whereNull('accommodation_details')->orWhere('accommodation_details', '')->orWhere('accommodation_details', '[]');
                })->count(),
                'pricing' => (int) Accommodation::query()->where('status', 'active')->where(function ($q) {
                    $q->whereNull('per_person_pricing')->orWhere('per_person_pricing', '')->orWhere('per_person_pricing', '[]');
                })->count(),
                'country' => (int) Accommodation::query()->where('status', 'active')->where(function ($q) {
                    $q->whereNull('country')->orWhere('country', '');
                })->count(),
                'gallery_empty' => (int) Accommodation::query()->where('status', 'active')->where(function ($q) {
                    $q->whereNull('gallery_images')->orWhere('gallery_images', '')->orWhere('gallery_images', '[]');
                })->count(),
            ],
        ];

        $contentTasks =
            $missing['guidings']['thumbnail'] + $missing['guidings']['description'] + $missing['guidings']['country']
            + $missing['trips']['thumbnail'] + $missing['trips']['description'] + $missing['trips']['country'] + $missing['trips']['gallery_empty']
            + $missing['camps']['thumbnail'] + $missing['camps']['description'] + $missing['camps']['country'] + $missing['camps']['gallery_empty']
            + $missing['accommodations']['thumbnail'] + $missing['accommodations']['details'] + $missing['accommodations']['pricing'] + $missing['accommodations']['country'] + $missing['accommodations']['gallery_empty'];

        return view('admin.pages.strategy.index', [
            'guidingsActive' => $guidingsActive,
            'tripsActive' => $tripsActive,
            'campsActive' => $campsActive,
            'accommodationsActive' => $accommodationsActive,
            'contentTasks' => $contentTasks,
            'missing' => $missing,
        ]);
    }

    public function supplyGaps(Request $request)
    {
        $countryInput = (string) $request->input('countries', '');
        $countries = $this->normalizeCountryFilter($countryInput);

        $guidings = Guiding::query()
            ->select(['id', 'country', 'status'])
            ->when(!empty($countries), fn ($q) => $q->whereIn('country', $countries))
            ->get();

        $trips = Trip::query()
            ->select(['id', 'country', 'status'])
            ->when(!empty($countries), fn ($q) => $q->whereIn('country', $countries))
            ->get();

        $camps = Camp::query()
            ->select(['id', 'country', 'status'])
            ->when(!empty($countries), fn ($q) => $q->whereIn('country', $countries))
            ->get();

        $accommodations = Accommodation::query()
            ->select(['id', 'country', 'status'])
            ->when(!empty($countries), fn ($q) => $q->whereIn('country', $countries))
            ->get();

        $rows = $this->buildSupplyGapRows($guidings, $trips, $camps, $accommodations);

        return view('admin.pages.strategy.supply-gaps', [
            'rows' => $rows,
            'countries' => $countries,
            'countryInput' => $countryInput,
        ]);
    }

    public function contentCoverage(Request $request)
    {
        $countryInput = (string) $request->input('countries', '');
        $countries = $this->normalizeCountryFilter($countryInput);

        $minGallery = max(0, (int) $request->input('min_gallery', 3));
        $onlyMissing = (bool) $request->boolean('only_missing', true);

        $guidings = Guiding::query()
            ->select(['id', 'title', 'country', 'status', 'thumbnail_path', 'gallery_images', 'description', 'price', 'created_at'])
            ->when(!empty($countries), fn ($q) => $q->whereIn('country', $countries))
            ->orderByDesc('id')
            ->limit(2000)
            ->get();

        $trips = Trip::query()
            ->select(['id', 'title', 'country', 'status', 'thumbnail_path', 'gallery_images', 'description', 'price_per_person', 'created_at'])
            ->when(!empty($countries), fn ($q) => $q->whereIn('country', $countries))
            ->orderByDesc('id')
            ->limit(2000)
            ->get();

        $camps = Camp::query()
            ->select(['id', 'title', 'country', 'status', 'thumbnail_path', 'gallery_images', 'description_camp', 'created_at'])
            ->when(!empty($countries), fn ($q) => $q->whereIn('country', $countries))
            ->orderByDesc('id')
            ->limit(2000)
            ->get();

        $accommodations = Accommodation::query()
            ->select(['id', 'title', 'country', 'status', 'thumbnail_path', 'gallery_images', 'accommodation_details', 'per_person_pricing', 'created_at'])
            ->when(!empty($countries), fn ($q) => $q->whereIn('country', $countries))
            ->orderByDesc('id')
            ->limit(2000)
            ->get();

        $items = $this->buildContentCoverageItems($guidings, $trips, $camps, $accommodations, $minGallery);

        if ($onlyMissing) {
            $items = $items->filter(fn ($i) => $i['missing_count'] > 0)->values();
        }

        return view('admin.pages.strategy.content-coverage', [
            'items' => $items,
            'countries' => $countries,
            'countryInput' => $countryInput,
            'minGallery' => $minGallery,
            'onlyMissing' => $onlyMissing,
        ]);
    }

    private function normalizeCountryFilter(string $input): array
    {
        $raw = collect(preg_split('/[,\n\r\t]+/', $input) ?: [])
            ->map(fn ($v) => trim((string) $v))
            ->filter()
            ->values();

        // Countries are stored as free text in many places; keep original casing but normalize duplicates.
        $unique = [];
        foreach ($raw as $v) {
            $k = mb_strtolower($v);
            if (!array_key_exists($k, $unique)) {
                $unique[$k] = $v;
            }
        }

        return array_values($unique);
    }

    private function buildSupplyGapRows(Collection $guidings, Collection $trips, Collection $camps, Collection $accommodations): array
    {
        $countries = collect()
            ->concat($guidings->pluck('country'))
            ->concat($trips->pluck('country'))
            ->concat($camps->pluck('country'))
            ->concat($accommodations->pluck('country'))
            ->map(fn ($v) => trim((string) $v))
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $byCountry = function (Collection $items, callable $isActive) use ($countries) {
            $active = $items->filter($isActive);

            $map = $active
                ->groupBy(fn ($i) => trim((string) ($i->country ?? '')))
                ->map(fn (Collection $g) => $g->count());

            return $countries->map(fn ($c) => (int) ($map->get($c) ?? 0))->toArray();
        };

        // Active statuses differ by model
        $guidingActive = fn ($g) => (int) ($g->status ?? 0) === 1;
        $tripActive = fn ($t) => (string) ($t->status ?? '') === 'active';
        $campActive = fn ($c) => (string) ($c->status ?? '') === 'active';
        $accommodationActive = fn ($a) => (string) ($a->status ?? '') === 'active';

        $guidingCounts = $byCountry($guidings, $guidingActive);
        $tripCounts = $byCountry($trips, $tripActive);
        $campCounts = $byCountry($camps, $campActive);
        $accommodationCounts = $byCountry($accommodations, $accommodationActive);

        $rows = [];
        foreach ($countries as $idx => $country) {
            $rows[] = [
                'country' => $country,
                'guidings_active' => $guidingCounts[$idx] ?? 0,
                'trips_active' => $tripCounts[$idx] ?? 0,
                'camps_active' => $campCounts[$idx] ?? 0,
                'accommodations_active' => $accommodationCounts[$idx] ?? 0,
                'total_active' => ($guidingCounts[$idx] ?? 0) + ($tripCounts[$idx] ?? 0) + ($campCounts[$idx] ?? 0) + ($accommodationCounts[$idx] ?? 0),
            ];
        }

        usort($rows, fn ($a, $b) => ($b['total_active'] <=> $a['total_active']) ?: strcmp($a['country'], $b['country']));

        return $rows;
    }

    private function buildContentCoverageItems(
        Collection $guidings,
        Collection $trips,
        Collection $camps,
        Collection $accommodations,
        int $minGallery
    ): Collection {
        $items = collect();

        foreach ($guidings as $g) {
            $galleryCount = is_array($g->gallery_images) ? count($g->gallery_images) : (is_string($g->gallery_images) ? count(json_decode($g->gallery_images, true) ?: []) : 0);
            $missing = $this->missingFlags([
                'thumbnail' => empty($g->thumbnail_path),
                'gallery' => $galleryCount < $minGallery,
                'description' => empty(trim((string) $g->description)),
                'country' => empty(trim((string) $g->country)),
            ]);

            $items->push([
                'type' => 'guiding',
                'id' => $g->id,
                'title' => (string) $g->title,
                'country' => (string) $g->country,
                'status' => ((int) ($g->status ?? 0)) === 1 ? 'active' : (((int) ($g->status ?? 0)) === 2 ? 'draft' : 'inactive'),
                'price_low' => $g->price !== null ? (float) $g->price : null,
                'gallery_count' => $galleryCount,
                'missing' => $missing,
                'missing_count' => count($missing),
                'edit_url' => route('admin.guidings.edit', $g),
            ]);
        }

        foreach ($trips as $t) {
            $galleryCount = is_array($t->gallery_images) ? count($t->gallery_images) : (is_string($t->gallery_images) ? count(json_decode($t->gallery_images, true) ?: []) : 0);
            $missing = $this->missingFlags([
                'thumbnail' => empty($t->thumbnail_path),
                'gallery' => $galleryCount < $minGallery,
                'description' => empty(trim((string) $t->description)),
                'country' => empty(trim((string) $t->country)),
            ]);

            $items->push([
                'type' => 'trip',
                'id' => $t->id,
                'title' => (string) $t->title,
                'country' => (string) $t->country,
                'status' => (string) ($t->status ?? ''),
                'price_low' => $t->price_per_person !== null ? (float) $t->price_per_person : null,
                'gallery_count' => $galleryCount,
                'missing' => $missing,
                'missing_count' => count($missing),
                'edit_url' => route('admin.trips.edit', $t),
            ]);
        }

        foreach ($camps as $c) {
            $galleryCount = is_array($c->gallery_images) ? count($c->gallery_images) : (is_string($c->gallery_images) ? count(json_decode($c->gallery_images, true) ?: []) : 0);
            $missing = $this->missingFlags([
                'thumbnail' => empty($c->thumbnail_path),
                'gallery' => $galleryCount < $minGallery,
                'description' => empty(trim((string) $c->description_camp)),
                'country' => empty(trim((string) $c->country)),
            ]);

            $items->push([
                'type' => 'camp',
                'id' => $c->id,
                'title' => (string) $c->title,
                'country' => (string) $c->country,
                'status' => (string) ($c->status ?? ''),
                'price_low' => null,
                'gallery_count' => $galleryCount,
                'missing' => $missing,
                'missing_count' => count($missing),
                'edit_url' => route('admin.camps.edit', $c),
            ]);
        }

        foreach ($accommodations as $a) {
            $galleryCount = is_array($a->gallery_images) ? count($a->gallery_images) : (is_string($a->gallery_images) ? count(json_decode($a->gallery_images, true) ?: []) : 0);
            $missing = $this->missingFlags([
                'thumbnail' => empty($a->thumbnail_path),
                'gallery' => $galleryCount < $minGallery,
                'details' => empty($a->accommodation_details),
                'pricing' => empty($a->per_person_pricing),
                'country' => empty(trim((string) $a->country)),
            ]);

            $items->push([
                'type' => 'accommodation',
                'id' => $a->id,
                'title' => (string) $a->title,
                'country' => (string) $a->country,
                'status' => (string) ($a->status ?? ''),
                'price_low' => $this->extractAccommodationLowPrice($a->per_person_pricing),
                'gallery_count' => $galleryCount,
                'missing' => $missing,
                'missing_count' => count($missing),
                'edit_url' => route('admin.accommodations.edit', $a),
            ]);
        }

        return $items
            ->sortByDesc('missing_count')
            ->sortBy(fn ($i) => $i['type'])
            ->values();
    }

    private function missingFlags(array $flags): array
    {
        $out = [];
        foreach ($flags as $key => $isMissing) {
            if ($isMissing) {
                $out[] = (string) $key;
            }
        }
        return $out;
    }

    private function extractAccommodationLowPrice($perPersonPricing): ?float
    {
        if (empty($perPersonPricing)) {
            return null;
        }
        $tiers = $perPersonPricing;
        if (is_string($tiers)) {
            $tiers = json_decode($tiers, true);
        }
        if (!is_array($tiers) || empty($tiers)) {
            return null;
        }

        $prices = [];
        foreach ($tiers as $tier) {
            if (!is_array($tier)) {
                continue;
            }
            if (isset($tier['price_per_night']) && is_numeric($tier['price_per_night']) && (float) $tier['price_per_night'] > 0) {
                $prices[] = (float) $tier['price_per_night'];
            }
            if (isset($tier['price_per_week']) && is_numeric($tier['price_per_week']) && (float) $tier['price_per_week'] > 0) {
                // normalize to nightly equivalent
                $prices[] = (float) $tier['price_per_week'] / 7;
            }
        }

        return empty($prices) ? null : min($prices);
    }
}

