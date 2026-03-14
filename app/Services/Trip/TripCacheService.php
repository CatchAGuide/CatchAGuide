<?php

namespace App\Services\Trip;

use App\Models\Trip;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class TripCacheService
{
    private const CACHE_TTL = 300;
    private const TRIPS_LIST_CACHE_KEY = 'trips_list';
    private const TRIP_CACHE_KEY = 'trip_';
    private const TRIP_OFFER_VIEW_CACHE_KEY = 'trip_offer_view_';
    private const FORM_DATA_CACHE_KEY = 'trip_form_data';

    public function getTripsList(int $perPage = 15): LengthAwarePaginator
    {
        $cacheKey = self::TRIPS_LIST_CACHE_KEY . '_page_' . request('page', 1);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($perPage) {
            return Trip::with('user')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);
        });
    }

    public function getTrip(int $tripId): ?Trip
    {
        $cacheKey = self::TRIP_CACHE_KEY . $tripId;

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($tripId) {
            return Trip::with(['user', 'availabilityDates'])->find($tripId);
        });
    }

    /**
     * Returns only static, non-locale-sensitive config data for the form.
     *
     * DB-backed whitelists (targets, methods, waters) are locale-sensitive via their
     * getNameAttribute() accessors and must be loaded fresh per request in the controller,
     * exactly as GuidingsController does — they are NOT cached here to avoid stale translations.
     */
    public function getFormData(): array
    {
        return Cache::remember(self::FORM_DATA_CACHE_KEY, self::CACHE_TTL, function () {
            $config = config('trips');

            return [
                'includedPreset'            => $config['included_whitelist'] ?? [],
                'excludedPreset'            => $config['excluded_whitelist'] ?? [],
                'availabilityStatusOptions' => $config['availability_status_options'] ?? ['available', 'limited', 'sold_out'],
            ];
        });
    }

    public function clearTripCache(int $tripId): void
    {
        Cache::forget(self::TRIP_CACHE_KEY . $tripId);
    }

    /** Get or compute the public trip offer view payload (cached). */
    public function rememberTripOfferViewModel(string $slug, callable $callback): array
    {
        return Cache::remember(self::TRIP_OFFER_VIEW_CACHE_KEY . $slug, self::CACHE_TTL, $callback);
    }

    /** Clear the public trip offer page view cache (call when a trip is updated). */
    public function clearTripOfferCacheBySlug(string $slug): void
    {
        Cache::forget(self::TRIP_OFFER_VIEW_CACHE_KEY . $slug);
    }

    public function clearTripsListCache(): void
    {
        for ($i = 1; $i <= 100; $i++) {
            Cache::forget(self::TRIPS_LIST_CACHE_KEY . '_page_' . $i);
        }
    }

    public function clearFormDataCache(): void
    {
        Cache::forget(self::FORM_DATA_CACHE_KEY);
    }

    public function clearAllCaches(): void
    {
        $this->clearFormDataCache();
        $this->clearTripsListCache();

        $tripIds = Trip::pluck('id');
        foreach ($tripIds as $id) {
            $this->clearTripCache($id);
        }
    }
}

