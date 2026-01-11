<?php

namespace App\Services\RentalBoat;

use Illuminate\Http\Request;

class RentalBoatInformationProcessor
{
    /**
     * Process boat information from request
     */
    public function process(Request $request): array
    {
        $boatInformation = [];
        
        if (!$request->has('boat_info_checkboxes')) {
            return $boatInformation;
        }

        $boatInfoCheckboxes = $request->input('boat_info_checkboxes', []);
        $boatInfoData = [];

        foreach ($boatInfoCheckboxes as $checkbox) {
            $inputValue = $request->input("boat_info_".$checkbox);
            
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
