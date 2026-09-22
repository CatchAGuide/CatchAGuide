<?php

namespace App\Services\Guiding;

use App\Models\Guiding;
use App\Services\Translation\GuidingTranslationService;
use Illuminate\Support\Collection;

class SimilarGuidingsService
{
    public const LIMIT = 4;

    public const CANDIDATE_LIMIT = 40;

    public function __construct(
        private GuidingTranslationService $translationService,
    ) {}

    /**
     * Nearby tours for a product page (catalog list rows on desktop, homepage carousel on mobile).
     *
     * @return array{guidings: Collection<int, Guiding>, see_all_url: string}
     */
    public function forProductPage(Guiding $guiding, int $limit = self::LIMIT): array
    {
        $models = $this->queryFor($guiding, $limit);
        $this->applyTranslations($models);

        return [
            'guidings' => $models,
            'see_all_url' => $this->catalogUrl($guiding),
        ];
    }

    public function queryFor(Guiding $guiding, int $limit = self::LIMIT): Collection
    {
        $candidates = $this->geoCandidateQuery($guiding)->get();

        if ($candidates->isEmpty()) {
            $candidates = $this->placeCandidateQuery($guiding)->get();
        }

        if ($candidates->isEmpty()) {
            return collect();
        }

        return $this->rank($guiding, $candidates)->take($limit)->values();
    }

    public function catalogUrl(Guiding $guiding): string
    {
        $params = array_filter([
            'place' => listing_place_label([
                $guiding->city ?: $guiding->location,
                $guiding->country,
            ]) ?: null,
            'city' => $guiding->city ?: null,
            'region' => $guiding->region ?: null,
            'country' => $guiding->country ?: null,
            'placeLat' => $this->floatOrNull($guiding->lat),
            'placeLng' => $this->floatOrNull($guiding->lng),
        ], fn ($value) => $value !== null && $value !== '');

        return route('guidings.index', $params);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<Guiding>
     */
    private function baseCandidateQuery(Guiding $guiding)
    {
        $excludeIds = Guiding::query()
            ->where('user_id', $guiding->user_id)
            ->pluck('id')
            ->all();

        return Guiding::query()
            ->publiclyVisible()
            ->whereNotIn('guidings.id', $excludeIds)
            ->with(['user.reviews', 'boatType']);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<Guiding>
     */
    private function geoCandidateQuery(Guiding $guiding)
    {
        $query = $this->baseCandidateQuery($guiding);
        $lat = $this->floatOrNull($guiding->lat);
        $lng = $this->floatOrNull($guiding->lng);

        if ($lat === null || $lng === null) {
            return $query->whereRaw('1 = 0');
        }

        $radiusKm = (int) config('location_search.nearby_radius_km.tour', 200);

        return $query
            ->select(['guidings.*'])
            ->selectRaw('ST_Distance_Sphere(point(lng, lat), point(?, ?)) as similar_distance', [
                $lng,
                $lat,
            ])
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->where('lat', '!=', 0)
            ->where('lng', '!=', 0)
            ->whereRaw('ST_Distance_Sphere(point(lng, lat), point(?, ?)) <= ?', [
                $lng,
                $lat,
                $radiusKm * 1000,
            ])
            ->orderByRaw('CASE WHEN similar_distance IS NULL THEN 1 ELSE 0 END')
            ->orderBy('similar_distance')
            ->limit(self::CANDIDATE_LIMIT);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<Guiding>
     */
    private function placeCandidateQuery(Guiding $guiding)
    {
        $query = $this->baseCandidateQuery($guiding);
        $city = $this->normalized($guiding->city);
        $region = $this->normalized($guiding->region);
        $country = $this->normalized($guiding->country);
        $location = $this->normalized($guiding->location);

        if ($city === null && $region === null && $country === null && $location === null) {
            return $query->whereRaw('1 = 0');
        }

        return $query
            ->where(function ($place) use ($city, $region, $country, $location) {
                if ($city !== null) {
                    $place->orWhere('city', $city);
                }
                if ($region !== null) {
                    $place->orWhere('region', $region);
                }
                if ($country !== null) {
                    $place->orWhere('country', $country);
                }
                if ($location !== null && $city === null) {
                    $place->orWhere('location', $location);
                }
            })
            ->orderByRaw(
                'CASE WHEN city = ? THEN 0 WHEN region = ? THEN 1 WHEN country = ? THEN 2 ELSE 3 END',
                [$city ?? '', $region ?? '', $country ?? '']
            )
            ->limit(self::CANDIDATE_LIMIT);
    }

    private function rank(Guiding $source, Collection $candidates): Collection
    {
        $sourceFish = $this->fishIds($source);

        return $candidates
            ->sortByDesc(fn (Guiding $candidate) => $this->score($source, $candidate, $sourceFish))
            ->values();
    }

    /**
     * @param  array<int, string>  $sourceFish
     */
    private function score(Guiding $source, Guiding $candidate, array $sourceFish): float
    {
        $score = 0.0;

        if ($this->samePlace($source->city, $candidate->city)) {
            $score += 50;
        }
        if ($this->samePlace($source->region, $candidate->region)) {
            $score += 20;
        }
        if ($this->samePlace($source->country, $candidate->country)) {
            $score += 8;
        }

        $sharedFish = count(array_intersect($sourceFish, $this->fishIds($candidate)));
        $score += $sharedFish * 15;

        if (! empty($source->fishing_from_id) && (int) $source->fishing_from_id === (int) $candidate->fishing_from_id) {
            $score += 6;
        }
        if (! empty($source->fishing_type_id) && (int) $source->fishing_type_id === (int) $candidate->fishing_type_id) {
            $score += 6;
        }

        $distanceMeters = isset($candidate->similar_distance) ? $this->floatOrNull($candidate->similar_distance) : null;
        if ($distanceMeters !== null) {
            $score -= $distanceMeters / 2000;
        }

        return $score;
    }

    /**
     * @return array<int, string>
     */
    private function fishIds(Guiding $guiding): array
    {
        $ids = decode_if_json($guiding->target_fish) ?? [];

        return collect($ids)
            ->map(fn ($id) => is_array($id) ? (string) ($id['id'] ?? '') : (string) $id)
            ->filter()
            ->values()
            ->all();
    }

    private function applyTranslations(Collection $guidings): void
    {
        $locale = app()->getLocale();
        $ids = [];
        $byId = [];

        foreach ($guidings as $guiding) {
            if ($guiding->language === $locale) {
                continue;
            }
            $ids[] = $guiding->id;
            $byId[$guiding->id] = $guiding;
        }

        if ($ids === []) {
            return;
        }

        $map = $this->translationService->getTranslatedGuidingsBatch(array_values(array_unique($ids)), $locale);
        foreach ($map as $id => $translated) {
            if (isset($byId[$id])) {
                $byId[$id]->translated = $translated;
            }
        }
    }

    private function samePlace(?string $left, ?string $right): bool
    {
        $left = $this->normalized($left);
        $right = $this->normalized($right);

        return $left !== null && $left === $right;
    }

    private function normalized(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    private function floatOrNull(mixed $value): ?float
    {
        if ($value === null || $value === '' || ! is_numeric($value)) {
            return null;
        }

        $number = (float) $value;

        return $number == 0.0 ? null : $number;
    }
}
