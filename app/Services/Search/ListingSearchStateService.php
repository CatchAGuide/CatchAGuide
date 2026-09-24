<?php

namespace App\Services\Search;

use App\Domain\Offers\OfferListingFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Carries the location/guest-count a visitor searched for across pages that
 * don't themselves reflect it in the query string (e.g. listing detail
 * pages) — the session-backed alternative to putting search state on a
 * crawlable link to a single-entity detail page (see CLAUDE.md's
 * "SEO / catalog page conventions").
 */
class ListingSearchStateService
{
    private const SESSION_KEY = 'listing_search.location';

    /**
     * Store the location/guest-count search carried by a catalog search submission.
     */
    public function remember(Request $request): void
    {
        $hasPlaceCoords = ($request->filled('placeLat') || $request->filled('placelat'))
            && ($request->filled('placeLng') || $request->filled('placelng'));

        $numGuests = self::nullableGuests($request->query('num_guests', $request->query('num_persons')));

        $data = [
            'place' => $hasPlaceCoords ? trim((string) $request->query('place', '')) : '',
            'placeLat' => $hasPlaceCoords ? (string) $request->query('placeLat', $request->query('placelat', '')) : '',
            'placeLng' => $hasPlaceCoords ? (string) $request->query('placeLng', $request->query('placelng', '')) : '',
            'city' => $hasPlaceCoords ? (string) $request->query('city', '') : '',
            'country' => trim((string) ($request->query('country') ?: $request->route('country') ?: '')),
            'region' => $hasPlaceCoords ? (string) $request->query('region', '') : '',
            'numGuests' => $numGuests !== null ? (string) $numGuests : '',
        ];

        if ($request->routeIs('vacations.all-offers')) {
            $data['country'] = 'all-offers';
        }

        if ((new Collection($data))->every(fn (string $value) => $value === '')) {
            return;
        }

        session([self::SESSION_KEY => $data]);
    }

    private static function nullableGuests(mixed $value): ?int
    {
        if ($value === null || $value === '' || ! is_numeric($value)) {
            return null;
        }

        $guests = (int) $value;

        return $guests >= 1 ? min($guests, OfferListingFilter::MAX_GUESTS) : null;
    }

    /**
     * The last persisted location/guest-count search, if any.
     *
     * @return array{place: string, placeLat: string, placeLng: string, city: string, country: string, region: string, numGuests: string}
     */
    public function current(): array
    {
        return array_merge(self::blank(), session(self::SESSION_KEY, []));
    }

    /**
     * Values to render into a search header: whatever this request explicitly
     * carries, falling back to the last persisted search otherwise. Location
     * and guest count fall back independently, since a detail-page link may
     * carry neither, either, or both stripped in favor of session state.
     *
     * @return array{place: string, placeLat: string, placeLng: string, city: string, country: string, region: string, numGuests: string}
     */
    public function resolveFromRequest(Request $request): array
    {
        $persisted = $this->current();

        $hasPlaceCoords = ($request->filled('placeLat') || $request->filled('placelat'))
            && ($request->filled('placeLng') || $request->filled('placelng'));

        $location = $hasPlaceCoords ? [
            'place' => (string) $request->query('place', ''),
            'placeLat' => (string) $request->query('placeLat', $request->query('placelat', '')),
            'placeLng' => (string) $request->query('placeLng', $request->query('placelng', '')),
            'city' => (string) $request->query('city', ''),
            'country' => (string) $request->query('country', ''),
            'region' => (string) $request->query('region', ''),
        ] : [
            'place' => $persisted['place'],
            'placeLat' => $persisted['placeLat'],
            'placeLng' => $persisted['placeLng'],
            'city' => $persisted['city'],
            'country' => $persisted['country'],
            'region' => $persisted['region'],
        ];

        $requestGuests = self::nullableGuests($request->query('num_guests', $request->query('num_persons')));

        return $location + [
            'numGuests' => $requestGuests !== null ? (string) $requestGuests : $persisted['numGuests'],
        ];
    }

    /**
     * @return array{place: string, placeLat: string, placeLng: string, city: string, country: string, region: string, numGuests: string}
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
            'numGuests' => '',
        ];
    }
}
