<?php

namespace App\Services\SpecialOffer;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SpecialOfferDataProcessor
{
    /**
     * Process all special offer data from request
     */
    public function processRequestData(Request $request, $existingSpecialOffer = null): array
    {
        // Process tagify data for whats_included
        $whatsIncluded = $this->processTagifyData($request->whats_included);
        
        // Process pricing data
        $pricing = $this->processPricingData($request);

        return [
            'user_id' => $request->user_id ?? Auth::id(),
            'title' => $request->title ?? 'Untitled',
            'location' => $request->location ?? '',
            'latitude' => $request->latitude ?? null,
            'longitude' => $request->longitude ?? null,
            'country' => $request->country ?? '',
            'city' => $request->city ?? '',
            'region' => $request->region ?? '',
            'whats_included' => $whatsIncluded,
            'pricing' => $pricing,
            'price_type' => 'fixed',
            'currency' => 'EUR',
        ];
    }

    /**
     * Process tagify data from request
     */
    private function processTagifyData($data): array
    {
        if (empty($data)) {
            return [];
        }

        // If it's already an array, return as is
        if (is_array($data)) {
            return $data;
        }

        // If it's a string, try to decode JSON
        if (is_string($data)) {
            $decoded = json_decode($data, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                // If it's an array of objects with 'value' property, extract values
                if (!empty($decoded) && is_array($decoded[0]) && isset($decoded[0]['value'])) {
                    return array_map(function($item) {
                        return is_array($item) && isset($item['value']) ? $item['value'] : $item;
                    }, $decoded);
                }
                return $decoded;
            }
            // If not JSON, treat as comma-separated
            return array_filter(array_map('trim', explode(',', $data)));
        }

        return [];
    }

    /**
     * Process pricing data from request
     */
    private function processPricingData(Request $request): ?array
    {
        $pricing = $request->input('pricing');
        
        if (empty($pricing)) {
            return null;
        }

        // If it's a string, try to decode JSON
        if (is_string($pricing)) {
            $decoded = json_decode($pricing, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $pricing = $decoded;
            } else {
                return null;
            }
        }

        // Handle extra pricing if provided (similar to RentalBoatPricingProcessor)
        if ($request->has('extra_pricing') && is_array($request->extra_pricing)) {
            // Filter out empty entries
            $extraPricing = array_filter($request->extra_pricing, function($item) {
                return !empty($item['name']) && !empty($item['price']);
            });
            
            if (!empty($extraPricing)) {
                // If pricing is an array, convert first element to object and add pricing_extra
                if (is_array($pricing) && !empty($pricing)) {
                    $pricingObj = $pricing[0] ?? [];
                    $pricingObj['pricing_extra'] = array_values($extraPricing);
                    return [$pricingObj];
                } elseif (is_array($pricing)) {
                    // If pricing is empty array, create new structure
                    return [['pricing_extra' => array_values($extraPricing)]];
                } else {
                    // If pricing is already an object, add pricing_extra
                    $pricing['pricing_extra'] = array_values($extraPricing);
                    return [$pricing];
                }
            }
        }

        // If it's already an array, return as is
        if (is_array($pricing)) {
            return $pricing;
        }

        // If it's an object, wrap it in an array
        if (is_array($pricing) || is_object($pricing)) {
            return [$pricing];
        }

        return null;
    }

    /**
     * Prepare edit form data from existing special offer
     */
    public function prepareEditFormData($specialOffer): array
    {
        return [
            'is_update' => 1,
            'id' => $specialOffer->id,
            'special_offer_id' => $specialOffer->id,
            'user_id' => $specialOffer->user_id,
            'title' => $specialOffer->title,
            'location' => $specialOffer->location,
            'latitude' => $specialOffer->latitude,
            'longitude' => $specialOffer->longitude,
            'country' => $specialOffer->country,
            'city' => $specialOffer->city,
            'region' => $specialOffer->region,
            'whats_included' => $specialOffer->whats_included ?? [],
            'pricing' => $specialOffer->pricing ?? null,
            'price_type' => 'fixed',
            'currency' => 'EUR',
            'status' => $specialOffer->status,
            'thumbnail_path' => $specialOffer->thumbnail_path,
            'gallery_images' => $specialOffer->gallery_images,
            'existing_images' => json_encode($this->formatImagePathsForWeb($specialOffer->gallery_images ?? [])),
            'lat' => $specialOffer->latitude,
            'lng' => $specialOffer->longitude,
            'accommodations' => $specialOffer->accommodations->pluck('id')->toArray(),
            'rental_boats' => $specialOffer->rentalBoats->pluck('id')->toArray(),
            'guidings' => $specialOffer->guidings->pluck('id')->toArray(),
            'slug' => $specialOffer->slug,
        ];
    }

    /**
     * Format image paths for web access
     */
    private function formatImagePathsForWeb(array $imagePaths): array
    {
        return array_map(function($path) {
            // If the path already starts with 'storage/' or 'assets/', return as is
            if (str_starts_with($path, 'storage/') || str_starts_with($path, 'assets/')) {
                return $path;
            }
            
            // For special offer images, prefix with 'storage/'
            if (str_starts_with($path, 'special-offers/')) {
                return 'storage/' . $path;
            }
            
            // For other paths, return as is
            return $path;
        }, $imagePaths);
    }
}

