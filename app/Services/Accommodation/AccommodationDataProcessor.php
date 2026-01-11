<?php

namespace App\Services\Accommodation;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccommodationDataProcessor
{
    public function __construct(
        private AccommodationDetailsProcessor $detailsProcessor,
        private AccommodationPricingProcessor $pricingProcessor,
        private AccommodationPoliciesProcessor $policiesProcessor,
        private AccommodationExtrasProcessor $extrasProcessor
    ) {}

    /**
     * Process all accommodation data from request
     */
    public function processRequestData(Request $request, $existingAccommodation = null): array
    {
        return [
            'user_id' => $request->user_id ?? Auth::id(),
            'title' => $request->title ?? 'Untitled',
            'location' => $request->location ?? '',
            'city' => $request->city ?? '',
            'country' => $request->country ?? '',
            'region' => $request->region ?? '',
            'lat' => $request->latitude ?? null,
            'lng' => $request->longitude ?? null,
            'accommodation_type' => $request->accommodation_type ?? '',
            'max_occupancy' => $request->max_occupancy ?? null,
            'distance_to_water_m' => $request->distance_to_water ?? null,
            'distance_to_boat_berth_m' => $request->distance_to_boat_mooring ?? null,
            'distance_to_shop_km' => $request->distance_to_shop_km ?? null,
            'distance_to_parking_m' => $request->distance_to_parking_lot ?? null,
            'minimum_stay_nights' => $request->minimum_stay_nights ?? null,
            'currency' => $request->currency ?? 'EUR',
            'amenities' => $this->extrasProcessor->processFacilities($request),
            'kitchen_equipment' => $this->extrasProcessor->processKitchenEquipment($request),
            'bathroom_amenities' => $this->extrasProcessor->processBathroomAmenities($request),
            'accommodation_details' => $this->detailsProcessor->processAccommodationDetails($request),
            'room_configurations' => $this->detailsProcessor->processRoomConfigurations($request),
            'policies' => $this->policiesProcessor->processPolicies($request),
            'rental_conditions' => $this->policiesProcessor->processRentalConditions($request),
            'extras' => $this->extrasProcessor->processExtras($request),
            'inclusives' => $this->extrasProcessor->processInclusives($request),
            'per_person_pricing' => $this->pricingProcessor->processPerPersonPricing($request),
        ];
    }

    /**
     * Prepare edit form data from existing accommodation
     */
    public function prepareEditFormData($accommodation): array
    {
        $formData = [
            'is_update' => 1,
            'id' => $accommodation->id,
            'accommodation_id' => $accommodation->id,
            'user_id' => $accommodation->user_id,
            'title' => $accommodation->title,
            'location' => $accommodation->location,
            'city' => $accommodation->city,
            'country' => $accommodation->country,
            'region' => $accommodation->region,
            'latitude' => $accommodation->lat,
            'longitude' => $accommodation->lng,
            'accommodation_type' => $accommodation->accommodation_type,
            'max_occupancy' => $accommodation->max_occupancy,
            'distance_to_water_m' => $accommodation->distance_to_water_m,
            'distance_to_boat_berth_m' => $accommodation->distance_to_boat_berth_m,
            'distance_to_shop_km' => $accommodation->distance_to_shop_km,
            'distance_to_parking_m' => $accommodation->distance_to_parking_m,
            'minimum_stay_nights' => $accommodation->minimum_stay_nights,
            'currency' => $accommodation->currency,
            'status' => $accommodation->status,
            'thumbnail_path' => $accommodation->thumbnail_path,
            'gallery_images' => $accommodation->gallery_images,
            'existing_images' => json_encode($accommodation->gallery_images ?? []),
            'amenities' => $accommodation->amenities ?? [],
            'kitchen_equipment' => $accommodation->kitchen_equipment ?? [],
            'bathroom_amenities' => $accommodation->bathroom_amenities ?? [],
            'accommodation_details' => $accommodation->accommodation_details ?? [],
            'room_configurations' => $accommodation->room_configurations ?? [],
            'policies' => $accommodation->policies ?? [],
            'rental_conditions' => $accommodation->rental_conditions ?? [],
            'extras' => $accommodation->extras ?? [],
            'inclusives' => $accommodation->inclusives ?? [],
            'per_person_pricing' => $accommodation->per_person_pricing ?? [],
        ];

        return $formData;
    }
}

