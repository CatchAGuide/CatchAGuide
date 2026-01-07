<?php

namespace App\Services\Camp;

use App\Models\Camp;
use App\Models\CampFacility;
use App\Models\Accommodation;
use App\Models\RentalBoat;
use App\Models\Guiding;
use App\Models\SpecialOffer;
use App\Models\Target;
use Illuminate\Support\Facades\Cache;

class CampCacheService
{
    private const CACHE_TTL = 300; // 1 hour
    private const CAMPS_LIST_CACHE_KEY = 'camps_list';
    private const CAMP_CACHE_KEY = 'camp_';
    private const FORM_DATA_CACHE_KEY = 'camp_form_data';

    /**
     * Get paginated camps list with caching
     */
    public function getCampsList(int $perPage = 15)
    {
        $cacheKey = self::CAMPS_LIST_CACHE_KEY . '_page_' . request('page', 1);
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($perPage) {
            return Camp::with('user')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);
        });
    }

    /**
     * Get single camp with caching
     */
    public function getCamp(int $campId): ?Camp
    {
        $cacheKey = self::CAMP_CACHE_KEY . $campId;

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($campId) {
            return Camp::with(['user', 'facilities', 'accommodations', 'rentalBoats', 'guidings', 'specialOffers'])
                ->find($campId);
        });
    }

    /**
     * Get form data for create/edit forms with caching
     */
    public function getFormData(): array
    {
        return Cache::remember(self::FORM_DATA_CACHE_KEY, self::CACHE_TTL, function () {
            return [
                'campFacilities' => CampFacility::where('is_active', true)->orderBy('name')->get(),
                'accommodations' => Accommodation::where('status', 'active')->orderBy('title')->get(),
                'rentalBoats' => RentalBoat::where('status', 'active')->orderBy('title')->get(),
                'guidings' => Guiding::where('status', 'active')->orderBy('title')->get(),
                'specialOffers' => SpecialOffer::where('status', 'active')->orderBy('title')->get(),
                'targetFish' => Target::orderBy('name')->get(),
            ];
        });
    }

    /**
     * Clear camp-specific cache
     */
    public function clearCampCache(int $campId): void
    {
        Cache::forget(self::CAMP_CACHE_KEY . $campId);
    }

    /**
     * Clear camps list cache
     */
    public function clearCampsListCache(): void
    {
        $keys = [
            self::CAMPS_LIST_CACHE_KEY . '_15',
            self::CAMPS_LIST_CACHE_KEY . '_20',
            self::CAMPS_LIST_CACHE_KEY . '_25',
            self::CAMPS_LIST_CACHE_KEY . '_50',
        ];

        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }

    /**
     * Clear form data cache
     */
    public function clearFormDataCache(): void
    {
        Cache::forget(self::FORM_DATA_CACHE_KEY);
    }

    /**
     * Clear all camp-related caches
     */
    public function clearAllCaches(): void
    {
        $this->clearCampsListCache();
        $this->clearFormDataCache();
        
        // Clear individual camp caches (this is a bit heavy, but ensures consistency)
        Cache::flush(); // In production, you might want to be more specific
    }

    /**
     * Warm up caches for better performance
     */
    public function warmUpCaches(): void
    {
        // Warm up form data cache
        $this->getFormData();
        
        // Warm up camps list cache
        $this->getCampsList(15);
        
        // You could also warm up recent camps here
        $recentCamps = Camp::latest()->take(10)->get();
        foreach ($recentCamps as $camp) {
            $this->getCamp($camp->id);
        }
    }
}
