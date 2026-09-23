<?php

namespace App\Services\Seo;

use Illuminate\Http\Request;

/**
 * Decides whether a public listing/filter request should be noindexed.
 * Clean product URLs stay indexable; faceted/query variants do not. Plain pagination (?page=N
 * alone) is not a facet: each page is a distinct, self-canonical page (PaginationSeo).
 */
final class SeoRobotsPolicy
{
    /**
     * @var list<string>
     */
    private const GUIDING_NOINDEX_PARAMS = [
        'species',
        'methods',
        'water',
        'duration_types',
        'num_guests',
        'place',
        'city',
        'region',
        'placeLat',
        'placeLng',
        'sortby',
    ];

    /**
     * @var list<string>
     */
    private const VACATION_NOINDEX_PARAMS = [
        'species',
        'sortby',
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
