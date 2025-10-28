<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RentalBoatRequest extends FormRequest
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
     * DISABLED FOR TESTING PHASE - All fields are now optional
     */
    public function rules(): array
    {
        // DISABLED FOR TESTING - All validation rules are now optional
        return $this->getDraftRules();
        
        // Original logic (commented out for testing):
        // $isDraft = $this->input('is_draft') == '1';
        // if ($isDraft) {
        //     return $this->getDraftRules();
        // }
        // return $this->getFullRules();
    }

    /**
     * Get validation rules for draft submissions
     * ALL FIELDS ARE NOW OPTIONAL FOR TESTING
     */
    private function getDraftRules(): array
    {
        return [
            'title' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'boat_type' => 'nullable|string|max:255',
            'max_persons' => 'nullable|integer|min:1',
            'desc_of_boat' => 'nullable|string',
            'price_type_checkboxes' => 'nullable|array',
            'price_type_checkboxes.*' => 'nullable|in:per_hour,per_day,per_week',
            'price_per_hour' => 'nullable|numeric|min:0',
            'price_per_day' => 'nullable|numeric|min:0',
            'price_per_week' => 'nullable|numeric|min:0',
            'rental_requirement_checkboxes' => 'nullable|array',
            'rental_requirement_checkboxes.*' => 'nullable|integer',
            'boat_info_checkboxes' => 'nullable|array',
            'boat_info_checkboxes.*' => 'nullable|integer',
            'extra_pricing' => 'nullable|array',
            'extra_pricing.*.name' => 'nullable|string|max:255',
            'extra_pricing.*.price' => 'nullable|numeric|min:0',
            'boat_extras' => 'nullable|string',
            'inclusions' => 'nullable|string',
            'status' => 'nullable|string',
            'user_id' => 'nullable|integer',
            'slug' => 'nullable|string',
            'city' => 'nullable|string',
            'country' => 'nullable|string',
            'region' => 'nullable|string',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
            'thumbnail_path' => 'nullable|string',
            'gallery_images' => 'nullable|array',
            'requirements' => 'nullable|array',
            'boat_information' => 'nullable|array',
            'prices' => 'nullable|array',
            'pricing_extra' => 'nullable|array',
        ];
    }

    /**
     * Get validation rules for full submissions
     */
    private function getFullRules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'boat_type' => 'required|string|max:255',
            'max_persons' => 'nullable|integer|min:1',
            'desc_of_boat' => 'required|string',
            'price_type_checkboxes' => 'required|array|min:1',
            'price_type_checkboxes.*' => 'in:per_hour,per_day,per_week',
            'price_per_hour' => 'nullable|numeric|min:0',
            'price_per_day' => 'nullable|numeric|min:0',
            'price_per_week' => 'nullable|numeric|min:0',
            'rental_requirement_checkboxes' => 'nullable|array',
            'rental_requirement_checkboxes.*' => 'integer|exists:rental_boat_requirements,id',
            'boat_info_checkboxes' => 'nullable|array',
            'boat_info_checkboxes.*' => 'integer|exists:guiding_boat_descriptions,id',
            'extra_pricing' => 'nullable|array',
            'extra_pricing.*.name' => 'nullable|string|max:255',
            'extra_pricing.*.price' => 'nullable|numeric|min:0',
            'boat_extras' => 'nullable|string',
            'inclusions' => 'nullable|string',
            'status' => 'required|string|in:active,inactive,draft',
        ];
    }

    /**
     * Get custom validation messages
     */
    public function messages(): array
    {
        return [
            'title.required' => 'The boat title is required.',
            'location.required' => 'The location is required.',
            'boat_type.required' => 'Please select a boat type.',
            'desc_of_boat.required' => 'Please provide a boat description.',
            'price_type_checkboxes.required' => 'Please select at least one price type.',
            'price_type_checkboxes.min' => 'Please select at least one price type.',
            'price_per_hour.numeric' => 'Hourly price must be a valid number.',
            'price_per_day.numeric' => 'Daily price must be a valid number.',
            'price_per_week.numeric' => 'Weekly price must be a valid number.',
            'rental_requirement_checkboxes.*.exists' => 'One or more selected requirements are invalid.',
            'boat_info_checkboxes.*.exists' => 'One or more selected boat information items are invalid.',
            'extra_pricing.*.name.string' => 'Extra pricing item name must be text.',
            'extra_pricing.*.price.numeric' => 'Extra pricing price must be a valid number.',
        ];
    }

    /**
     * Get custom attribute names
     */
    public function attributes(): array
    {
        return [
            'price_type_checkboxes' => 'price types',
            'rental_requirement_checkboxes' => 'requirements',
            'boat_info_checkboxes' => 'boat information',
            'extra_pricing' => 'extra pricing items',
        ];
    }
}
