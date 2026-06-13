<?php

namespace App\Services\Vacation;

use App\Models\Camp;
use Illuminate\Http\Request;

class VacationRedirectResolver
{
    /**
     * @return string|null Canonical path (with leading slash) or null if no redirect.
     */
    public function resolve(Request $request): ?string
    {
        $path = trim($request->path(), '/');
        $query = $request->getQueryString();
        $suffix = $query ? '?' . $query : '';

        // Legacy trip catalog
        if ($path === 'trips-destinations') {
            return '/vacations/trips' . $suffix;
        }

        if (preg_match('#^trips-destinations/c/([^/]+)$#', $path, $m)) {
            return '/vacations/' . $m[1] . $suffix;
        }

        if (preg_match('#^trips/([^/]+)$#', $path, $m) && $m[1] !== '') {
            return '/vacations/trips/' . $m[1] . $suffix;
        }

        if (preg_match('#^vacations/c/([^/]+)$#', $path, $m)) {
            return '/vacations/' . $m[1] . $suffix;
        }

        if (preg_match('#^vacations-v2/(\d+)$#', $path, $m)) {
            $camp = Camp::find($m[1]);
            if ($camp?->slug) {
                return '/vacations/camps/' . $camp->slug . $suffix;
            }
        }

        // vacations/{slug} -> lowercase country canonical URL, or camps if slug is a camp
        if (preg_match('#^vacations/([^/]+)$#', $path, $m)) {
            $segment = $m[1];
            $reserved = config('vacations.reserved_country_segments', ['trips', 'camps']);
            if (in_array(strtolower($segment), $reserved, true)) {
                return null;
            }

            $canonical = strtolower($segment);
            if ($canonical !== $segment && ! in_array($canonical, $reserved, true)) {
                return '/vacations/' . $canonical . $suffix;
            }

            if (Camp::query()->where('slug', $segment)->exists() && ! $this->isCountrySlug($canonical)) {
                return '/vacations/camps/' . $segment . $suffix;
            }
        }

        return null;
    }

    private function isCountrySlug(string $slug): bool
    {
        return \App\Models\Destination::query()
            ->whereRaw('LOWER(slug) = ?', [strtolower($slug)])
            ->whereIn('type', ['vacations', 'trips'])
            ->exists();
    }
}
