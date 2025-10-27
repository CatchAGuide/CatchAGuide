<?php

namespace App\Services\Accommodation;

use Illuminate\Http\Request;

class AccommodationPricingProcessor
{
    /**
     * Process per-person pricing data
     */
    public function processPerPersonPricing(Request $request): array
    {
        $pricing = [];
        
        // Process new format: per_person_count[tier_id] and per_person_price_night[tier_id]
        $personCounts = $request->input('per_person_count', []);
        $priceNights = $request->input('per_person_price_night', []);
        $priceWeeks = $request->input('per_person_price_week', []);
        
        if (!empty($personCounts) && is_array($personCounts)) {
            foreach ($personCounts as $tierId => $personCount) {
                $personCount = (int)$personCount;
                $pricePerNight = isset($priceNights[$tierId]) ? (float)$priceNights[$tierId] : 0;
                $pricePerWeek = isset($priceWeeks[$tierId]) ? (float)$priceWeeks[$tierId] : 0;
                
                // Only add if person count is valid and at least one price is set
                if ($personCount > 0 && ($pricePerNight > 0 || $pricePerWeek > 0)) {
                    $pricing[$tierId] = [
                        'person_count' => $personCount,
                        'price_per_night' => $pricePerNight,
                        'price_per_week' => $pricePerWeek
                    ];
                }
            }
        }
        
        // Fallback: Process old format for backward compatibility
        // guest_count_ and price_per_person_night_{tierId}
        if (empty($pricing)) {
            foreach ($request->all() as $key => $value) {
                if (strpos($key, 'guest_count_') === 0) {
                    $tierId = str_replace('guest_count_', '', $key);
                    $guestCount = (int)$value;
                    
                    if ($guestCount > 0) {
                        $pricing[$tierId] = [
                            'person_count' => $guestCount,
                            'guest_count' => $guestCount, // Keep for backward compatibility
                            'price_per_night' => (float)($request->input("price_per_person_night_{$tierId}") ?? 0),
                            'price_per_week' => (float)($request->input("price_per_person_week_{$tierId}") ?? 0)
                        ];
                    }
                }
            }
        }

        return $pricing;
    }

    /**
     * Calculate base pricing from request
     */
    public function calculateBasePricing(Request $request): array
    {
        $pricing = [
            'currency' => $request->currency ?? 'EUR',
        ];

        // Process checkbox-based pricing like rental boats
        if ($request->has('price_type_checkboxes')) {
            $priceTypeCheckboxes = $request->input('price_type_checkboxes', []);
            
            foreach ($priceTypeCheckboxes as $priceType) {
                $priceField = 'price_' . $priceType;
                $priceValue = $request->input($priceField);
                
                if ($request->has($priceField) && $priceValue > 0) {
                    $pricing[$priceType] = (float) $priceValue;
                }
            }
        }

        // Fallback to old format for backward compatibility
        if (empty($pricing) && $request->has('price_type')) {
            $pricing['price_type'] = $request->price_type ?? 'per_accommodation';
            $pricing['price_per_night'] = $request->price_per_night ?? null;
            $pricing['price_per_week'] = $request->price_per_week ?? null;
        }

        return $pricing;
    }

    /**
     * Determine the display price for listing
     */
    public function determineDisplayPrice(Request $request): ?float
    {
        // Priority: per_night > per_week
        if ($request->price_per_night && $request->price_per_night > 0) {
            return (float) $request->price_per_night;
        }
        
        if ($request->price_per_week && $request->price_per_week > 0) {
            return (float) $request->price_per_week;
        }
        
        return null;
    }
}

