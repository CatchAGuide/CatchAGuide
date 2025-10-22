<?php

namespace App\Services\RentalBoat;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RentalBoatRequirementsProcessor
{
    /**
     * Process rental requirements from request
     */
    public function process(Request $request): array
    {
        $rentalRequirements = [];
        
        if (!$request->has('rental_requirement_checkboxes')) {
            Log::info('RentalBoatRequirementsProcessor::process - No requirement checkboxes found');
            return $rentalRequirements;
        }

        $requirementCheckboxes = $request->input('rental_requirement_checkboxes', []);
        $requirementData = [];

        foreach ($requirementCheckboxes as $checkbox) {
            $inputValue = $request->input("rental_requirement_".$checkbox);
            
            if (!empty($inputValue)) {
                $requirementData[] = [
                    'id' => $checkbox,
                    'value' => $inputValue
                ];
            }
        }

        $rentalRequirements = array_merge($rentalRequirements, $requirementData);

        return $rentalRequirements;
    }
}
