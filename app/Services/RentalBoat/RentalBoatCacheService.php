<?php

namespace App\Services\RentalBoat;

use App\Models\RentalBoat;
use App\Models\GuidingBoatType;
use App\Models\RentalBoatRequirement;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class RentalBoatCacheService
{
    private const CACHE_TTL = 3600; // 1 hour
    private const FORM_DATA_CACHE_KEY = 'rental_boat_form_data';
    private const RENTAL_BOATS_CACHE_KEY = 'rental_boats_list';
    private const RENTAL_BOAT_CACHE_KEY = 'rental_boat_';

    /**
     * Get cached form data
     */
    public function getFormData(): array
    {
        return Cache::remember(self::FORM_DATA_CACHE_KEY, self::CACHE_TTL, function () {
            $locale = Config::get('app.locale');
            $nameField = $locale == 'en' ? 'name_en' : 'name';

            return [
                'rentalBoatTypes' => \App\Models\GuidingBoatType::all()->map(function($item) use ($nameField) {
                    return [
                        'value' => $item->$nameField,
                        'id' => $item->id
                    ];
                }),
                'boatExtras' => \App\Models\BoatExtras::all()->map(function($item) use ($nameField) {
                    return [
                        'value' => $item->$nameField,
                        'id' => $item->id
                    ];
                }),
                'inclusions' => \App\Models\Inclussion::all()->map(function($item) use ($nameField) {
                    return [
                        'value' => $item->$nameField,
                        'id' => $item->id
                    ];
                }),
                'guiding_boat_descriptions' => \App\Models\GuidingBoatDescription::all()->map(function($item) use ($nameField) {
                    return [
                        'value' => $item->$nameField,
                        'id' => $item->id
                    ];
                }),
                'rentalBoatRequirements' => RentalBoatRequirement::active()->ordered()->get()
            ];
        });
    }

    /**
     * Get cached rental boats list
     */
    public function getRentalBoatsList(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember(self::RENTAL_BOATS_CACHE_KEY, self::CACHE_TTL, function () {
            return RentalBoat::with('user')
                ->where('status', 'active')
                ->orderBy('title')
                ->get();
        });
    }

    /**
     * Get cached rental boat
     */
    public function getRentalBoat(int $id): ?RentalBoat
    {
        return Cache::remember(
            self::RENTAL_BOAT_CACHE_KEY . $id, 
            self::CACHE_TTL, 
            function () use ($id) {
                return RentalBoat::with('user')->find($id);
            }
        );
    }

    /**
     * Clear all rental boat related caches
     */
    public function clearAllCaches(): void
    {
        Cache::forget(self::FORM_DATA_CACHE_KEY);
        Cache::forget(self::RENTAL_BOATS_CACHE_KEY);
        
        // Clear individual rental boat caches
        $rentalBoats = RentalBoat::pluck('id');
        foreach ($rentalBoats as $id) {
            Cache::forget(self::RENTAL_BOAT_CACHE_KEY . $id);
        }
    }

    /**
     * Clear specific rental boat cache
     */
    public function clearRentalBoatCache(int $id): void
    {
        Cache::forget(self::RENTAL_BOAT_CACHE_KEY . $id);
        Cache::forget(self::RENTAL_BOATS_CACHE_KEY);
    }

    /**
     * Clear form data cache
     */
    public function clearFormDataCache(): void
    {
        Cache::forget(self::FORM_DATA_CACHE_KEY);
    }
}
