<?php

namespace App\Services\Accommodation;

use Illuminate\Http\Request;

class AccommodationDetailsProcessor
{
    /**
     * Process accommodation details checkbox data with input fields
     */
    public function processAccommodationDetails(Request $request): ?array
    {
        $accommodationDetails = [];
        
        // Get selected accommodation detail checkboxes
        $selectedDetails = $request->input('accommodation_detail_checkboxes', []);
        
        foreach ($selectedDetails as $detailId) {
            $detailData = [
                'id' => $detailId,
                'value' => $request->input("accommodation_detail_{$detailId}", '')
            ];
            $accommodationDetails[] = $detailData;
        }
        
        return empty($accommodationDetails) ? null : $accommodationDetails;
    }

    /**
     * Process room configurations checkbox data with input fields
     */
    public function processRoomConfigurations(Request $request): ?array
    {
        $roomConfigurations = [];
        
        // Get selected room configuration checkboxes
        $selectedConfigs = $request->input('room_config_checkboxes', []);
        
        foreach ($selectedConfigs as $configId) {
            $configData = [
                'id' => $configId,
                'value' => $request->input("room_config_{$configId}", '')
            ];
            $roomConfigurations[] = $configData;
        }
        
        return empty($roomConfigurations) ? null : $roomConfigurations;
    }

    /**
     * Process bed types data
     */
    public function processBedTypes(Request $request): array
    {
        $bedTypes = [];
        
        // Handle new bed type checkboxes with quantity inputs
        if ($request->has('bed_type_checkboxes')) {
            $bedTypeCheckboxes = $request->input('bed_type_checkboxes', []);
            
            foreach ($bedTypeCheckboxes as $bedType) {
                $bedTypes[$bedType] = [
                    'enabled' => true,
                    'quantity' => (int)($request->input("bed_type_{$bedType}", 0))
                ];
            }
        }
        
        // Handle old bed_types structure for backward compatibility
        if ($request->has('bed_types')) {
            foreach ($request->bed_types as $type => $data) {
                if (!isset($bedTypes[$type])) { // Don't override new structure
                    if (isset($data['enabled']) && $data['enabled']) {
                        $bedTypes[$type] = [
                            'enabled' => true,
                            'quantity' => (int)($data['quantity'] ?? 0)
                        ];
                    }
                }
            }
        }

        return $bedTypes;
    }
}

