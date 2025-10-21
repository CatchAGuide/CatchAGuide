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
        if ($request->price_type !== 'per_person') {
            return [];
        }

        $pricing = [];
        
        // Process dynamic pricing tiers
        foreach ($request->all() as $key => $value) {
            if (strpos($key, 'guest_count_') === 0) {
                $tierId = str_replace('guest_count_', '', $key);
                $guestCount = (int)$value;
                
                if ($guestCount > 0) {
                    $pricing[$tierId] = [
                        'guest_count' => $guestCount,
                        'price_per_night' => (float)($request->input("price_per_person_night_{$tierId}") ?? 0),
                        'price_per_week' => (float)($request->input("price_per_person_week_{$tierId}") ?? 0)
                    ];
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

