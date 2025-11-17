<?php

namespace App\Services\RentalBoat;

use App\Models\GuidingBoatType;
use Illuminate\Http\Request;

class RentalBoatDataProcessor
{
    public function __construct(
        private RentalBoatRequirementsProcessor $requirementsProcessor,
        private RentalBoatInformationProcessor $informationProcessor,
        private RentalBoatPricingProcessor $pricingProcessor,
        private RentalBoatExtrasProcessor $extrasProcessor
    ) {}

    /**
     * Process all rental boat data from request
     */
    public function processRequestData(Request $request, $existingRentalBoat = null): array
    {
        $pricing = $this->pricingProcessor->process($request);
        $pricingExtra = $pricing['pricing_extra'] ?? null;
    
        unset($pricing['pricing_extra']);
        $prices = $pricing;
        return [
            'user_id' => $request->user_id ?? auth()->id(),
            'title' => $request->title ?? 'Untitled',
            'location' => $request->location ?? '',
            'city' => $request->city ?? '',
            'country' => $request->country ?? '',
            'region' => $request->region ?? '',
            'lat' => $request->latitude ?? null,
            'lng' => $request->longitude ?? null,
            'boat_type' => $this->processBoatType($request->boat_type),
            'max_persons' => $request->max_persons ?? null,
            'desc_of_boat' => $request->desc_of_boat ?? '',
            'requirements' => $this->requirementsProcessor->process($request),
            'boat_information' => $this->informationProcessor->process($request),
            'boat_extras' => $this->extrasProcessor->processBoatExtras($request),
            'inclusions' => $this->extrasProcessor->processInclusions($request),
            'prices' => $prices,
            'pricing_extra' => $pricingExtra,
            'price_type' => $this->pricingProcessor->determinePrimaryPriceType($request),
        ];
    }

    /**
     * Process boat type from request
     */
    private function processBoatType($boatTypeValue): mixed
    {
        if (empty($boatTypeValue)) {
            return null;
        }
        
        if (is_numeric($boatTypeValue)) {
            return $boatTypeValue;
        }
        
        $boatType = GuidingBoatType::where('name', $boatTypeValue)
            ->orWhere('name_en', $boatTypeValue)
            ->first();
            
        return $boatType ? $boatType->id : $boatTypeValue;
    }
}
