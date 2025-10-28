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
            'description' => $request->description ?? '',
            'condition_or_style' => $request->condition_or_style ?? '',
            'living_area_sqm' => $request->living_area_sqm ?? null,
            'floor_layout' => $request->floor_layout ?? '',
            'max_occupancy' => $request->max_occupancy ?? null,
            'number_of_bedrooms' => $request->number_of_bedrooms ?? null,
            'kitchen_type' => $request->kitchen_type ?? '',
            'bathroom' => $request->bathroom ?? null,
            'location_description' => $request->location_description ?? '',
            'distance_to_water_m' => $request->distance_to_water ?? null,
            'distance_to_boat_berth_m' => $request->distance_to_boat_mooring ?? null,
            'distance_to_shop_km' => $request->distance_to_shop_km ?? null,
            'distance_to_parking_m' => $request->distance_to_parking_lot ?? null,
            'distance_to_nearest_town_km' => $request->distance_to_nearest_town_km ?? null,
            'distance_to_airport_km' => $request->distance_to_airport_km ?? null,
            'distance_to_ferry_port_km' => $request->distance_to_ferry_port_km ?? null,
            'changeover_day' => $request->changeover_day ?? '',
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
            'bed_types' => $this->detailsProcessor->processBedTypes($request),
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
            'description' => $accommodation->description,
            'condition_or_style' => $accommodation->condition_or_style,
            'living_area_sqm' => $accommodation->living_area_sqm,
            'floor_layout' => $accommodation->floor_layout,
            'max_occupancy' => $accommodation->max_occupancy,
            'number_of_bedrooms' => $accommodation->number_of_bedrooms,
            'kitchen_type' => $accommodation->kitchen_type,
            'bathroom' => $accommodation->bathroom,
            'location_description' => $accommodation->location_description,
            'distance_to_water_m' => $accommodation->distance_to_water_m,
            'distance_to_boat_berth_m' => $accommodation->distance_to_boat_berth_m,
            'distance_to_shop_km' => $accommodation->distance_to_shop_km,
            'distance_to_parking_m' => $accommodation->distance_to_parking_m,
            'distance_to_nearest_town_km' => $accommodation->distance_to_nearest_town_km,
            'distance_to_airport_km' => $accommodation->distance_to_airport_km,
            'distance_to_ferry_port_km' => $accommodation->distance_to_ferry_port_km,
            'changeover_day' => $accommodation->changeover_day,
            'minimum_stay_nights' => $accommodation->minimum_stay_nights,
            'price_type' => $accommodation->price_type,
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
            'bed_types' => $accommodation->bed_types ?? [],
            'per_person_pricing' => $accommodation->per_person_pricing ?? [],
        ];

        return $formData;
    }
}

