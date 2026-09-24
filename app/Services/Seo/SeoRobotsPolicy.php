<?php

namespace App\Services\Seo;

use Illuminate\Http\Request;

/**
 * Decides whether a public listing/filter request should be noindexed.
 * Clean product URLs stay indexable; faceted/query variants do not. Plain pagination (?page=N
 * alone) is not a facet: each page is a distinct, self-canonical page (PaginationSeo).
 *
 * Any other non-empty query parameter makes the request a facet. The listings accept far more
 * filters than any list could keep up with (target_fish[], guide_id, fishing_type, duration,
 * country, radius, ...), and a filter missing from a list would silently become indexable.
 * Campaign tracking tags are ignored: they don't change the page, and the canonical drops them.
 */
final class SeoRobotsPolicy
{
    /**
     * Query parameters that never make a listing a facet.
     *
     * @var list<string>
     */
    private const NEUTRAL_PARAMS = [
        'page',
        'gclid',
        'gbraid',
        'wbraid',
        'fbclid',
        'msclkid',
        'mc_cid',
        'mc_eid',
    ];

    public function shouldNoindexGuidings(Request $request): bool
    {
        return $this->hasFacetParams($request);
    }

    public function shouldNoindexVacations(Request $request): bool
    {
        return $this->hasFacetParams($request);
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

    private function hasFacetParams(Request $request): bool
    {
        foreach ($request->query() as $param => $value) {
            $param = (string) $param;
            if (in_array($param, self::NEUTRAL_PARAMS, true) || str_starts_with($param, 'utm_')) {
                continue;
            }

            if ($this->isEmpty($value)) {
                continue;
            }

            return true;
        }

        return false;
    }

    private function isEmpty(mixed $value): bool
    {
        if (is_array($value)) {
            foreach ($value as $item) {
                if (! $this->isEmpty($item)) {
                    return false;
                }
            }

            return true;
        }

        return $value === null || $value === '';
    }
}
