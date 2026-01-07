<?php

namespace App\Services\SpecialOffer;

use App\Models\SpecialOffer;
use App\Models\Accommodation;
use App\Models\RentalBoat;
use App\Models\Guiding;
use Illuminate\Support\Facades\Cache;

class SpecialOfferCacheService
{
    private const CACHE_TTL = 300; // 5 minutes
    private const SPECIAL_OFFERS_LIST_CACHE_KEY = 'special_offers_list';
    private const SPECIAL_OFFER_CACHE_KEY = 'special_offer_';
    private const FORM_DATA_CACHE_KEY = 'special_offer_form_data';

    /**
     * Get paginated special offers list with caching
     */
    public function getSpecialOffersList(int $perPage = 15)
    {
        $cacheKey = self::SPECIAL_OFFERS_LIST_CACHE_KEY . '_page_' . request('page', 1);
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($perPage) {
            return SpecialOffer::with('user')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);
        });
    }

    /**
     * Get single special offer with caching
     */
    public function getSpecialOffer(int $specialOfferId): ?SpecialOffer
    {
        $cacheKey = self::SPECIAL_OFFER_CACHE_KEY . $specialOfferId;

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($specialOfferId) {
            return SpecialOffer::with(['user', 'accommodations', 'rentalBoats', 'guidings'])
                ->find($specialOfferId);
        });
    }

    /**
     * Get form data for create/edit forms with caching
     */
    public function getFormData(): array
    {
        return Cache::remember(self::FORM_DATA_CACHE_KEY, self::CACHE_TTL, function () {
            return [
                'accommodations' => Accommodation::where('status', 'active')->orderBy('title')->get(),
                'rentalBoats' => RentalBoat::where('status', 'active')->orderBy('title')->get(),
                'guidings' => Guiding::where('status', 'active')->orderBy('title')->get(),
            ];
        });
    }

    /**
     * Clear special offer-specific cache
     */
    public function clearSpecialOfferCache(int $specialOfferId): void
    {
        Cache::forget(self::SPECIAL_OFFER_CACHE_KEY . $specialOfferId);
    }

    /**
     * Clear special offers list cache
     */
    public function clearSpecialOffersListCache(): void
    {
        $keys = [
            self::SPECIAL_OFFERS_LIST_CACHE_KEY . '_15',
            self::SPECIAL_OFFERS_LIST_CACHE_KEY . '_20',
            self::SPECIAL_OFFERS_LIST_CACHE_KEY . '_25',
            self::SPECIAL_OFFERS_LIST_CACHE_KEY . '_50',
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
     * Clear all special offer-related caches
     */
    public function clearAllCaches(): void
    {
        $this->clearSpecialOffersListCache();
        $this->clearFormDataCache();
        
        // Clear individual special offer caches (this is a bit heavy, but ensures consistency)
        Cache::flush(); // In production, you might want to be more specific
    }

    /**
     * Warm up caches for better performance
     */
    public function warmUpCaches(): void
    {
        // Warm up form data cache
        $this->getFormData();
        
        // Warm up special offers list cache
        $this->getSpecialOffersList(15);
        
        // You could also warm up recent special offers here
        $recentSpecialOffers = SpecialOffer::latest()->take(10)->get();
        foreach ($recentSpecialOffers as $specialOffer) {
            $this->getSpecialOffer($specialOffer->id);
        }
    }
}

