<?php

namespace App\Services\Accommodation;

use App\Models\Accommodation;
use App\Models\AccommodationType;
use App\Models\AccommodationDetail;
use App\Models\RoomConfiguration;
use App\Models\Facility;
use App\Models\KitchenEquipment;
use App\Models\BathroomAmenity;
use App\Models\AccommodationPolicy;
use App\Models\AccommodationRentalCondition;
use App\Models\AccommodationExtra;
use App\Models\AccommodationInclusive;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class AccommodationCacheService
{
    private const CACHE_TTL = 3600; // 1 hour
    private const FORM_DATA_CACHE_KEY = 'accommodation_form_data';
    private const ACCOMMODATIONS_CACHE_KEY = 'accommodations_list';
    private const ACCOMMODATION_CACHE_KEY = 'accommodation_';

    /**
     * Get cached form data for create/edit forms
     */
    public function getFormData(): array
    {
        return Cache::remember(self::FORM_DATA_CACHE_KEY, self::CACHE_TTL, function () {
            return [
                'accommodationTypes' => AccommodationType::active()->ordered()->get(),
                'accommodationDetails' => AccommodationDetail::active()->ordered()->get(),
                'roomConfigurations' => RoomConfiguration::active()->ordered()->get(),
                'facilities' => $this->transformForTagify(Facility::active()->ordered()->get()),
                'kitchenEquipment' => $this->transformForTagify(KitchenEquipment::active()->ordered()->get()),
                'bathroomAmenities' => $this->transformForTagify(BathroomAmenity::active()->ordered()->get()),
                'accommodationPolicies' => AccommodationPolicy::active()->ordered()->get(),
                'accommodationRentalConditions' => AccommodationRentalCondition::active()->ordered()->get(),
                'accommodationExtras' => AccommodationExtra::active()->ordered()->get(),
                'accommodationInclusives' => AccommodationInclusive::active()->ordered()->get(),
            ];
        });
    }

    /**
     * Transform collection for Tagify compatibility and remove duplicates
     */
    private function transformForTagify($collection): \Illuminate\Support\Collection
    {
        return $collection->map(function($item) {
            return [
                'value' => $item->name,
                'id' => $item->id
            ];
        })->unique('value'); // Remove duplicates based on value
    }

    /**
     * Get cached accommodations list with pagination
     */
    public function getAccommodationsList(int $perPage = 15)
    {
        $cacheKey = self::ACCOMMODATIONS_CACHE_KEY . '_page_' . request('page', 1);
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($perPage) {
            return Accommodation::with('user')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);
        });
    }

    /**
     * Get cached accommodation
     */
    public function getAccommodation(int $id): ?Accommodation
    {
        return Cache::remember(
            self::ACCOMMODATION_CACHE_KEY . $id, 
            self::CACHE_TTL, 
            function () use ($id) {
                return Accommodation::with('user')->find($id);
            }
        );
    }

    /**
     * Clear all accommodation related caches
     */
    public function clearAllCaches(): void
    {
        Cache::forget(self::FORM_DATA_CACHE_KEY);
        
        // Clear paginated lists
        for ($i = 1; $i <= 100; $i++) {
            Cache::forget(self::ACCOMMODATIONS_CACHE_KEY . '_page_' . $i);
        }
        
        // Clear individual accommodation caches
        $accommodations = Accommodation::pluck('id');
        foreach ($accommodations as $id) {
            Cache::forget(self::ACCOMMODATION_CACHE_KEY . $id);
        }
    }

    /**
     * Clear specific accommodation cache
     */
    public function clearAccommodationCache(int $id): void
    {
        Cache::forget(self::ACCOMMODATION_CACHE_KEY . $id);
        
        // Clear paginated lists
        for ($i = 1; $i <= 100; $i++) {
            Cache::forget(self::ACCOMMODATIONS_CACHE_KEY . '_page_' . $i);
        }
    }

    /**
     * Clear form data cache
     */
    public function clearFormDataCache(): void
    {
        Cache::forget(self::FORM_DATA_CACHE_KEY);
    }
}

