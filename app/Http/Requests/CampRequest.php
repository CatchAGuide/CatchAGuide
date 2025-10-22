<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CampRequest extends FormRequest
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
            'description_camp' => 'nullable|string',
            'description_area' => 'nullable|string',
            'description_fishing' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'country' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:255',
            'distance_to_store' => 'nullable|string|max:255',
            'distance_to_nearest_town' => 'nullable|string|max:255',
            'distance_to_airport' => 'nullable|string|max:255',
            'distance_to_ferry_port' => 'nullable|string|max:255',
            'policies_regulations' => 'nullable|string',
            'target_fish' => 'nullable|string',
            'best_travel_times' => 'nullable|string',
            'travel_information' => 'nullable|string',
            'extras' => 'nullable|string',
            'camp_facilities' => 'nullable|string',
            'accommodations' => 'nullable|array',
            'accommodations.*' => 'exists:accommodations,id',
            'rental_boats' => 'nullable|array',
            'rental_boats.*' => 'exists:rental_boats,id',
            'guidings' => 'nullable|array',
            'guidings.*' => 'exists:guidings,id',
            'status' => 'nullable|string|in:active,inactive,draft',
            'user_id' => 'nullable|integer',
        ];
    }

    /**
     * Get validation rules for full submissions - ALL VALIDATIONS DISABLED FOR TESTING
     */
    private function getFullRules(): array
    {
        return [
            'title' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'description_camp' => 'nullable|string',
            'description_area' => 'nullable|string',
            'description_fishing' => 'nullable|string',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'country' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:255',
            'distance_to_store' => 'nullable|string|max:255',
            'distance_to_nearest_town' => 'nullable|string|max:255',
            'distance_to_airport' => 'nullable|string|max:255',
            'distance_to_ferry_port' => 'nullable|string|max:255',
            'policies_regulations' => 'nullable|string',
            'target_fish' => 'nullable|string',
            'best_travel_times' => 'nullable|string',
            'travel_information' => 'nullable|string',
            'extras' => 'nullable|string',
            'camp_facilities' => 'nullable|string',
            'accommodations' => 'nullable|array',
            'accommodations.*' => 'exists:accommodations,id',
            'rental_boats' => 'nullable|array',
            'rental_boats.*' => 'exists:rental_boats,id',
            'guidings' => 'nullable|array',
            'guidings.*' => 'exists:guidings,id',
            'status' => 'nullable|string|in:active,inactive,draft',
            'user_id' => 'nullable|integer',
        ];
    }

    /**
     * Get custom validation messages
     */
    public function messages(): array
    {
        return [
            'title.required' => 'The camp title is required.',
            'location.required' => 'The location is required.',
            'description_camp.required' => 'Camp description is required.',
            'description_area.required' => 'Area description is required.',
            'description_fishing.required' => 'Fishing description is required.',
            'camp_facilities.required' => 'At least one camp facility is required.',
            'accommodations.*.exists' => 'One or more selected accommodations are invalid.',
            'rental_boats.*.exists' => 'One or more selected rental boats are invalid.',
            'guidings.*.exists' => 'One or more selected guidings are invalid.',
            'latitude.between' => 'Latitude must be between -90 and 90 degrees.',
            'longitude.between' => 'Longitude must be between -180 and 180 degrees.',
        ];
    }

    /**
     * Get custom attribute names
     */
    public function attributes(): array
    {
        return [
            'description_camp' => 'camp description',
            'description_area' => 'area description',
            'description_fishing' => 'fishing description',
            'camp_facilities' => 'camp facilities',
            'distance_to_store' => 'distance to store',
            'distance_to_nearest_town' => 'distance to nearest town',
            'distance_to_airport' => 'distance to airport',
            'distance_to_ferry_port' => 'distance to ferry port',
            'policies_regulations' => 'policies and regulations',
            'target_fish' => 'target fish',
            'best_travel_times' => 'best travel times',
            'travel_information' => 'travel information',
        ];
    }
}
