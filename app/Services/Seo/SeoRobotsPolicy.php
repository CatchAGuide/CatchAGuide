<?php

namespace App\Services\Seo;

use Illuminate\Http\Request;

/**
 * Decides whether a public listing/filter request should be noindexed.
 * Clean product URLs stay indexable; faceted/query variants do not.
 */
final class SeoRobotsPolicy
{
    /**
     * @var list<string>
     */
    private const GUIDING_NOINDEX_PARAMS = [
        'target_fish',
        'methods',
        'water',
        'duration_types',
        'num_persons',
        'price_min',
        'price_max',
        'place',
        'city',
        'region',
        'radius',
        'placeLat',
        'placeLng',
        'sortby',
        'page',
    ];

    /**
     * @var list<string>
     */
    private const VACATION_NOINDEX_PARAMS = [
        'species',
        'sortby',
        'page',
        'pillar',
    ];

    public function shouldNoindexGuidings(Request $request): bool
    {
        return $this->requestHasAny($request, self::GUIDING_NOINDEX_PARAMS);
    }

    public function shouldNoindexVacations(Request $request): bool
    {
        return $this->requestHasAny($request, self::VACATION_NOINDEX_PARAMS);
    }

    public function robotsContentForGuidings(Request $request): string
    {
        return $this->shouldNoindexGuidings($request)
            ? 'NOINDEX, NOFOLLOW'
            : 'INDEX, FOLLOW';
    }

    public function robotsContentForVacations(Request $request): string
    {
        return $this->shouldNoindexVacations($request)
            ? 'NOINDEX, NOFOLLOW'
            : 'INDEX, FOLLOW';
    }

    /**
     * @param  list<string>  $params
     */
    private function requestHasAny(Request $request, array $params): bool
    {
        foreach ($params as $param) {
            if (! $request->has($param)) {
                continue;
            }

            $value = $request->query($param);
            if ($value === null || $value === '') {
                continue;
            }

            return true;
        }

        return false;
    }
}
