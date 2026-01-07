<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SpecialOfferRequest extends FormRequest
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
     * Get validation rules for draft submissions
     */
    private function getDraftRules(): array
    {
        return [
            'title' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'country' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:255',
            'whats_included' => 'nullable', // Accepts JSON string or array, processed by data processor
            'pricing' => 'nullable', // Accepts JSON string or array, processed by data processor
            'price_type' => 'nullable|string|max:255',
            'currency' => 'nullable|string|max:3',
            'accommodations' => 'nullable', // Tagify input sends string, IDs are in accommodations_ids
            'accommodations.*' => 'exists:accommodations,id', // Only validates if accommodations is an array
            'accommodations_ids' => 'nullable|string', // Comma-separated IDs or JSON array string
            'rental_boats' => 'nullable', // Tagify input sends string, IDs are in rental_boats_ids
            'rental_boats.*' => 'exists:rental_boats,id', // Only validates if rental_boats is an array
            'rental_boats_ids' => 'nullable|string', // Comma-separated IDs or JSON array string
            'guidings' => 'nullable', // Tagify input sends string, IDs are in guidings_ids
            'guidings.*' => 'exists:guidings,id', // Only validates if guidings is an array
            'guidings_ids' => 'nullable|string', // Comma-separated IDs or JSON array string
            'status' => 'nullable|string|in:active,inactive,draft',
            'user_id' => 'nullable|integer',
        ];
    }

    /**
     * Get validation rules for full submissions
     */
    private function getFullRules(): array
    {
        return [
            'title' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'country' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'region' => 'nullable|string|max:255',
            'whats_included' => 'nullable', // Accepts JSON string or array, processed by data processor
            'pricing' => 'nullable', // Accepts JSON string or array, processed by data processor
            'price_type' => 'nullable|string|max:255',
            'currency' => 'nullable|string|max:3',
            'accommodations' => 'nullable', // Tagify input sends string, IDs are in accommodations_ids
            'accommodations.*' => 'exists:accommodations,id', // Only validates if accommodations is an array
            'accommodations_ids' => 'nullable|string', // Comma-separated IDs or JSON array string
            'rental_boats' => 'nullable', // Tagify input sends string, IDs are in rental_boats_ids
            'rental_boats.*' => 'exists:rental_boats,id', // Only validates if rental_boats is an array
            'rental_boats_ids' => 'nullable|string', // Comma-separated IDs or JSON array string
            'guidings' => 'nullable', // Tagify input sends string, IDs are in guidings_ids
            'guidings.*' => 'exists:guidings,id', // Only validates if guidings is an array
            'guidings_ids' => 'nullable|string', // Comma-separated IDs or JSON array string
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
            'title.required' => 'The special offer title is required.',
            'location.required' => 'The location is required.',
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
            'whats_included' => 'what\'s included',
            'price_type' => 'price type',
            'rental_boats' => 'rental boats',
        ];
    }

}
