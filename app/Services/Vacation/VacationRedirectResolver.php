<?php

namespace App\Services\Vacation;

use App\Models\Camp;
use App\Repositories\Vacation\VacationDestinationRepository;
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

        if ($path === 'vacations/trips' && $request->filled('country')) {
            $country = strtolower((string) $request->get('country'));
            if ($this->isCountrySlug($country)) {
                $query = http_build_query($request->except(['country', 'pillar']));

                return '/vacations/trips/' . $country . ($query ? '?' . $query : '');
            }
        }

        if ($path === 'vacations/camps' && $request->filled('country')) {
            $country = strtolower((string) $request->get('country'));
            if ($this->isCountrySlug($country)) {
                $query = http_build_query($request->except(['country', 'pillar']));

                return '/vacations/camps/' . $country . ($query ? '?' . $query : '');
            }
        }

        if ($path === 'vacations/trips' && $request->has('pillar')) {
            $query = http_build_query($request->except('pillar'));

            return '/vacations/trips' . ($query ? '?' . $query : '');
        }

        if ($path === 'vacations/camps' && $request->has('pillar')) {
            $query = http_build_query($request->except('pillar'));

            return '/vacations/camps' . ($query ? '?' . $query : '');
        }

        if (preg_match('#^vacations-v2/(\d+)$#', $path, $m)) {
            $camp = Camp::find($m[1]);
            if ($camp?->slug) {
                return '/vacations/camps/' . $camp->slug . $suffix;
            }
        }

        // vacations/{slug} -> lowercase country canonical URL, pillar split, or camps if slug is a camp
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

            $pillar = $request->get('pillar');
            if (in_array($pillar, ['trips', 'camps'], true) && $this->isCountrySlug($canonical)) {
                $query = http_build_query($request->except('pillar'));

                return '/vacations/' . $pillar . '/' . $canonical . ($query ? '?' . $query : '');
            }

            if (Camp::query()->where('slug', $segment)->exists() && ! $this->isCountrySlug($canonical)) {
                return '/vacations/camps/' . $segment . $suffix;
            }
        }

        if (preg_match('#^vacations/(trips|camps)/([^/]+)$#', $path, $m)) {
            $pillar = $m[1];
            $segment = $m[2];
            $canonical = strtolower($segment);

            if (! app(VacationDestinationRepository::class)->isKnownCountrySlug($canonical, $pillar)) {
                return null;
            }

            if ($canonical !== $segment || $request->has('pillar')) {
                $query = http_build_query($request->except('pillar'));

                return '/vacations/' . $pillar . '/' . $canonical . ($query ? '?' . $query : '');
            }
        }

        return null;
    }

    private function isCountrySlug(string $slug): bool
    {
        return app(VacationDestinationRepository::class)->isKnownCountrySlug($slug);
    }
}
