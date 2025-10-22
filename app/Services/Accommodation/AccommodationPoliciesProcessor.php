<?php

namespace App\Services\Accommodation;

use Illuminate\Http\Request;

class AccommodationPoliciesProcessor
{
    /**
     * Process policies Tagify data
     */
    public function processPolicies(Request $request): ?array
    {
        if (!$request->has('policies')) {
            return null;
        }

        return $this->parseTagifyData($request->policies);
    }

    /**
     * Process rental conditions checkbox data with input fields
     */
    public function processRentalConditions(Request $request): ?array
    {
        $rentalConditions = [];
        
        // Get selected rental condition checkboxes
        $selectedConditions = $request->input('rental_condition_checkboxes', []);
        
        foreach ($selectedConditions as $conditionId) {
            $conditionData = [
                'id' => $conditionId,
                'value' => $request->input("rental_condition_{$conditionId}", '')
            ];
            $rentalConditions[] = $conditionData;
        }
        
        return empty($rentalConditions) ? null : $rentalConditions;
    }

    /**
     * Process legacy policies data (backward compatibility)
     */
    public function processLegacyPolicies(Request $request): array
    {
        $policies = [];
        
        // Handle policy checkboxes with textarea inputs
        if ($request->has('policy_checkboxes')) {
            $policyCheckboxes = $request->input('policy_checkboxes', []);
            
            foreach ($policyCheckboxes as $checkbox) {
                $policies[$checkbox] = [
                    'enabled' => true,
                    'details' => $request->input("policy_{$checkbox}", '')
                ];
            }
        }
        
        // Also handle any remaining individual policy fields
        $policyFields = [
            'bed_linen_included',
            'pets_allowed',
            'smoking_allowed',
            'towels_included',
            'quiet_hours_no_parties',
            'checkin_checkout_times',
            'children_allowed_child_friendly',
            'accessible_barrier_free',
            'energy_usage_included',
            'water_usage_included',
            'parking_availability'
        ];
        
        foreach ($policyFields as $field) {
            if (!isset($policies[$field])) {
                $policies[$field] = [
                    'enabled' => $request->has($field) ? (bool)$request->input($field) : false,
                    'details' => ''
                ];
            }
        }
        
        return $policies;
    }

    /**
     * Process legacy rental conditions (backward compatibility)
     */
    public function processLegacyRentalConditions(Request $request): array
    {
        return [
            'checkin_checkout_time' => $request->checkin_checkout_time ?? '',
            'self_checkin' => $request->has('self_checkin'),
            'quiet_times' => $request->quiet_times ?? '',
            'waste_disposal_recycling_rules' => $request->waste_disposal_recycling_rules ?? ''
        ];
    }

    /**
     * Parse Tagify data from JSON string or array
     */
    private function parseTagifyData($data): ?array
    {
        if (empty($data)) {
            return null;
        }

        // If it's already an array, return it
        if (is_array($data)) {
            return $data;
        }

        // If it's a JSON string, decode it
        if (is_string($data)) {
            $decoded = json_decode($data, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }

        return null;
    }
}

