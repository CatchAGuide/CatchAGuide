<?php

namespace App\Services\RentalBoat;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RentalBoatPricingProcessor
{
    /**
     * Process pricing data from request
     */
    public function process(Request $request): array
    {
        $pricing = [];

        // Process individual price types with checkboxes
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

        // Handle extra pricing if provided
        if ($request->has('extra_pricing') && is_array($request->extra_pricing)) {
            
            // Filter out empty entries
            $extraPricing = array_filter($request->extra_pricing, function($item) {
                return !empty($item['name']) && !empty($item['price']);
            });
            
            if (!empty($extraPricing)) {
                $pricing['pricing_extra'] = array_values($extraPricing);
            } else {
                Log::info('RentalBoatPricingProcessor::process - No valid extra pricing found');
            }
        } else {
            Log::info('RentalBoatPricingProcessor::process - No extra pricing in request', [
                'has_extra_pricing' => $request->has('extra_pricing'),
                'is_array' => is_array($request->extra_pricing ?? null)
            ]);
        }

        Log::info('RentalBoatPricingProcessor::process - Final pricing', [
            'pricing' => $pricing
        ]);

        return $pricing;
    }

    /**
     * Determine the primary price type based on selected checkboxes
     */
    public function determinePrimaryPriceType(Request $request): string
    {
        if (!$request->has('price_type_checkboxes')) {
            return 'per_hour';
        }

        $priceTypeCheckboxes = $request->input('price_type_checkboxes', []);
        
        // Priority order: per_hour, per_day, per_week
        $priority = ['per_hour', 'per_day', 'per_week'];
        
        foreach ($priority as $priceType) {
            if (in_array($priceType, $priceTypeCheckboxes)) {
                $priceField = 'price_' . $priceType;
                if ($request->has($priceField) && $request->input($priceField) > 0) {
                    return $priceType;
                }
            }
        }

        return 'per_hour';
    }
}
