<?php

namespace App\Services\Accommodation;

use Illuminate\Http\Request;

class AccommodationExtrasProcessor
{
    /**
     * Process facilities/amenities Tagify data
     */
    public function processFacilities(Request $request): ?array
    {
        if (!$request->has('facilities')) {
            return null;
        }

        return $this->parseTagifyData($request->facilities);
    }

    /**
     * Process kitchen equipment Tagify data
     */
    public function processKitchenEquipment(Request $request): ?array
    {
        if (!$request->has('kitchen_equipment')) {
            return null;
        }

        return $this->parseTagifyData($request->kitchen_equipment);
    }

    /**
     * Process bathroom amenities Tagify data
     */
    public function processBathroomAmenities(Request $request): ?array
    {
        if (!$request->has('bathroom_amenities')) {
            return null;
        }

        return $this->parseTagifyData($request->bathroom_amenities);
    }

    /**
     * Process extras Tagify data
     */
    public function processExtras(Request $request): ?array
    {
        if (!$request->has('extras')) {
            return null;
        }

        return $this->parseTagifyData($request->extras);
    }

    /**
     * Process inclusives Tagify data
     */
    public function processInclusives(Request $request): ?array
    {
        if (!$request->has('inclusives')) {
            return null;
        }

        return $this->parseTagifyData($request->inclusives);
    }

    /**
     * Process legacy amenities data (backward compatibility)
     */
    public function processLegacyAmenities(Request $request): array
    {
        $amenities = [];
        
        $amenityFields = [
            'terrace', 'garden', 'swimming_pool', 'private_jetty_boat_dock', 
            'fish_cleaning_station', 'smoker', 'barbecue_area', 'lockable_storage_fishing',
            'wifi', 'fireplace_stove', 'sauna', 'pool_hot_tub', 'games_corner',
            'balcony', 'garden_furniture_sun_loungers', 'parking_spaces',
            'charging_station_electric_cars', 'boat_ramp_nearby', 'tv',
            'sound_system', 'reception', 'keybox', 'heating', 'air_conditioning'
        ];

        foreach ($amenityFields as $field) {
            $amenities[$field] = $request->has($field);
        }

        return $amenities;
    }

    /**
     * Process legacy kitchen equipment data (backward compatibility)
     */
    public function processLegacyKitchenEquipment(Request $request): array
    {
        $kitchenEquipment = [];
        
        $kitchenFields = [
            'oven', 'stove_or_ceramic_hob', 'microwave', 'dishwasher', 'coffee_machine',
            'cookware_and_dishes', 'refrigerator', 'freezer', 'kettle', 'toaster',
            'blender_hand_mixer', 'basic_cooking_supplies', 'kitchen_utensils',
            'baking_equipment', 'dishwashing_items', 'wine_glasses'
        ];

        foreach ($kitchenFields as $field) {
            $kitchenEquipment[$field] = $request->has($field);
        }

        return $kitchenEquipment;
    }

    /**
     * Process legacy bathroom amenities data (backward compatibility)
     */
    public function processLegacyBathroomAmenities(Request $request): array
    {
        $bathroomAmenities = [];
        
        $bathroomFields = [
            'iron_and_ironing_board', 'clothes_drying_rack', 'toilet', 'own_shower'
        ];

        foreach ($bathroomFields as $field) {
            $bathroomAmenities[$field] = $request->has($field);
        }

        return $bathroomAmenities;
    }

    /**
     * Parse Tagify data from JSON string or array
     */
    private function parseTagifyData($data): ?array
    {
        if (empty($data)) {
            return null;
        }

        // If it's already an array, return it
        if (is_array($data)) {
            return $data;
        }

        // If it's a JSON string, decode it
        if (is_string($data)) {
            $decoded = json_decode($data, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }
}

