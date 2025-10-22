<?php

namespace App\Services\RentalBoat;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RentalBoatInformationProcessor
{
    /**
     * Process boat information from request
     */
    public function process(Request $request): array
    {
        $boatInformation = [];
        
        if (!$request->has('boat_info_checkboxes')) {
            Log::info('RentalBoatInformationProcessor::process - No boat info checkboxes found');
            return $boatInformation;
        }

        $boatInfoCheckboxes = $request->input('boat_info_checkboxes', []);
        $boatInfoData = [];

        foreach ($boatInfoCheckboxes as $checkbox) {
            $inputValue = $request->input("boat_info_".$checkbox);
            
            Log::info('RentalBoatInformationProcessor::process - Processing boat info item', [
                'checkbox_id' => $checkbox,
                'input_value' => $inputValue,
                'field_name' => "boat_info_".$checkbox
            ]);
            
            if (!empty($inputValue)) {
                // Structure as ID-value pair like boat_extras
                $boatInfoData[] = [
                    'id' => $checkbox,
                    'value' => $inputValue
                ];
            }
        }

        $boatInformation = array_merge($boatInformation, $boatInfoData);

        return $boatInformation;
    }
}
