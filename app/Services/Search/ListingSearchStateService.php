<?php

namespace App\Services\Search;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Carries the location a visitor searched for across pages that don't
 * themselves reflect it in the query string (e.g. listing detail pages).
 */
class ListingSearchStateService
{
    private const SESSION_KEY = 'listing_search.location';

    /**
     * Store the location search carried by a catalog search submission.
     */
    public function remember(Request $request): void
    {
        $hasPlaceCoords = ($request->filled('placeLat') || $request->filled('placelat'))
            && ($request->filled('placeLng') || $request->filled('placelng'));

        $data = [
            'place' => $hasPlaceCoords ? trim((string) $request->query('place', '')) : '',
            'placeLat' => $hasPlaceCoords ? (string) $request->query('placeLat', $request->query('placelat', '')) : '',
            'placeLng' => $hasPlaceCoords ? (string) $request->query('placeLng', $request->query('placelng', '')) : '',
            'city' => $hasPlaceCoords ? (string) $request->query('city', '') : '',
            'country' => trim((string) ($request->query('country') ?: $request->route('country') ?: '')),
            'region' => $hasPlaceCoords ? (string) $request->query('region', '') : '',
        ];

        if ($request->routeIs('vacations.all-offers')) {
            $data['country'] = 'all-offers';
        }

        if ((new Collection($data))->every(fn (string $value) => $value === '')) {
            return;
        }

        session([self::SESSION_KEY => $data]);
    }

    /**
     * The last persisted location search, if any.
     *
     * @return array{place: string, placeLat: string, placeLng: string, city: string, country: string, region: string}
     */
    public function current(): array
    {
        return array_merge(self::blank(), session(self::SESSION_KEY, []));
    }

    /**
     * Values to render into a search header: whatever this request explicitly
     * carries, falling back to the last persisted search otherwise.
     *
     * @return array{place: string, placeLat: string, placeLng: string, city: string, country: string, region: string}
     */
    public function resolveFromRequest(Request $request): array
    {
        $hasPlaceCoords = ($request->filled('placeLat') || $request->filled('placelat'))
            && ($request->filled('placeLng') || $request->filled('placelng'));

        if ($hasPlaceCoords) {
            return [
                'place' => (string) $request->query('place', ''),
                'placeLat' => (string) $request->query('placeLat', $request->query('placelat', '')),
                'placeLng' => (string) $request->query('placeLng', $request->query('placelng', '')),
                'city' => (string) $request->query('city', ''),
                'country' => (string) $request->query('country', ''),
                'region' => (string) $request->query('region', ''),
            ];
        }

        return $this->current();
    }

    /**
     * @return array{place: string, placeLat: string, placeLng: string, city: string, country: string, region: string}
     */
    private static function blank(): array
    {
        return [
            'place' => '',
            'placeLat' => '',
            'placeLng' => '',
            'city' => '',
            'country' => '',
            'region' => '',
        ];
    }
}
