<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AccommodationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $isDraft = $this->input('is_draft') == '1';
        
        if ($isDraft) {
            return $this->getDraftRules();
        }
        
        return $this->getFullRules();
    }

    /**
     * Get validation rules for draft submissions - ALL VALIDATIONS DISABLED
     */
    private function getDraftRules(): array
    {
        return [
            'title' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:255',
            'accommodation_type' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'price_type_checkboxes' => 'nullable|array',
            'price_type_checkboxes.*' => 'nullable|in:per_night,per_week',
            'price_per_night' => 'nullable|numeric|min:0',
            'price_per_week' => 'nullable|numeric|min:0',
            'status' => 'nullable|string|in:active,inactive,draft',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'user_id' => 'nullable|integer',
        ];
    }

    /**
     * Get validation rules for full submissions - ALL VALIDATIONS DISABLED
     */
    private function getFullRules(): array
    {
        return [
            'title' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:255',
            'accommodation_type' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'condition_or_style' => 'nullable|string|max:255',
            'living_area_sqm' => 'nullable|integer|min:0',
            'floor_layout' => 'nullable|string|max:255',
            'max_occupancy' => 'nullable|integer|min:1',
            'number_of_bedrooms' => 'nullable|integer|min:0',
            'kitchen_type' => 'nullable|string|max:255',
            'bathroom' => 'nullable|integer|min:0',
            'location_description' => 'nullable|string',
            'distance_to_water_m' => 'nullable|integer|min:0',
            'distance_to_boat_berth_m' => 'nullable|integer|min:0',
            'distance_to_shop_km' => 'nullable|numeric|min:0',
            'distance_to_parking_m' => 'nullable|integer|min:0',
            'distance_to_nearest_town_km' => 'nullable|numeric|min:0',
            'distance_to_airport_km' => 'nullable|numeric|min:0',
            'distance_to_ferry_port_km' => 'nullable|numeric|min:0',
            'changeover_day' => 'nullable|string|max:255',
            'minimum_stay_nights' => 'nullable|integer|min:1',
            'price_type_checkboxes' => 'nullable|array',
            'price_type_checkboxes.*' => 'nullable|in:per_night,per_week',
            'price_per_night' => 'nullable|numeric|min:0',
            'price_per_week' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|max:3',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'status' => 'nullable|string|in:active,inactive,draft',
        ];
    }

    /**
     * Get custom validation messages
     */
    public function messages(): array
    {
        return [
            'title.required' => 'The accommodation title is required.',
            'location.required' => 'The location is required.',
            'city.required' => 'The city is required.',
            'country.required' => 'The country is required.',
            'accommodation_type.required' => 'Please select an accommodation type.',
            'price_type.required' => 'Please select a pricing type.',
            'max_occupancy.min' => 'Maximum occupancy must be at least 1.',
            'living_area_sqm.min' => 'Living area cannot be negative.',
        ];
    }

    /**
     * Get custom attribute names
     */
    public function attributes(): array
    {
        return [
            'accommodation_type' => 'accommodation type',
            'living_area_sqm' => 'living area',
            'max_occupancy' => 'maximum occupancy',
            'number_of_bedrooms' => 'number of bedrooms',
            'price_per_night' => 'price per night',
            'price_per_week' => 'price per week',
        ];
    }
}

