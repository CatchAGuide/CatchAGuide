<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

use Akuechler\Geoly;
use App\Traits\ModelImageTrait;

class Vacation extends Model
{
    use HasFactory, Geoly, ModelImageTrait;

    protected $fillable = ['title', 'slug', 'location', 'city', 'country', 'latitude', 'longitude', 'region', 'gallery', 'best_travel_times', 'surroundings_description', 'target_fish', 'airport_distance', 'water_distance', 'shopping_distance', 'travel_included', 'travel_options', 'pets_allowed', 'smoking_allowed', 'disability_friendly', 'accommodation_description', 'living_area', 'bedroom_count', 'bed_count', 'max_persons', 'min_rental_days', 'amenities', 'boat_description', 'equipment', 'basic_fishing_description', 'catering_info', 'package_price_per_person', 'accommodation_price', 'boat_rental_price', 'guiding_price', 'additional_services', 'included_services', 'status'];

    public static function locationFilter(string $location, ?int $radius = null)
    {
        $locationParts = self::parseLocation($location);
        $returnData = [
            'message' => '',
            'ids' => []
        ];
        
        // First try direct database match based on parsed location
        $vacations = self::select('id')
            ->where(function($query) use ($locationParts) {
                if ($locationParts['city']) {
                    $query->where('city', $locationParts['city']);
                } else if ($locationParts['country']) {
                    $query->where('country', $locationParts['country']); 
                }
            })
            ->where('status', 1)
            ->pluck('id');

        if ($vacations->isNotEmpty()) {
            $returnData['ids'] = $vacations;
            $returnData['message'] = str_replace('#location#', $location, __('search-request.searchLevel1'));;
            return $returnData;
        }

        // If no direct matches, use geocoding
        $coordinates = self::getCoordinatesFromLocation($locationParts['original']);
        
        if (!$coordinates) {
            return collect();
        }

        // Try radius search
        $searchRadius = $radius ?? 200;
        
        $vacations = self::select('id')
            ->whereRaw("ST_Distance_Sphere(
                point(lng, lat),
                point(?, ?)
            ) <= ?", [
                $coordinates['lng'],
                $coordinates['lat'],
                $searchRadius * 1000
            ])
            ->where('status', 1)
            ->pluck('id');

        if ($vacations->isNotEmpty()) {
            $returnData['ids'] = $vacations;
            $returnData['message'] = str_replace('#location#', $location, __('search-request.searchLevel2'));
            return $returnData;
        }

        // If still no results, find nearest guiding
        $returnData['ids'] = self::select('id')
            ->where('status', 1)
            ->selectRaw("ST_Distance_Sphere(
                point(lng, lat), 
                point(?, ?)
            ) as distance", [
                $coordinates['lng'],
                $coordinates['lat']
            ])
            ->orderBy('distance')
            ->pluck('id');
        $returnData['message'] = str_replace('#location#', $location, __('search-request.searchLevel3'));
        return $returnData;
    }
    
    private static function getCoordinatesFromLocation(string $location): ?array
    {
        $geocodeResult = getCoordinatesFromLocation($location, true);
        if (!$geocodeResult) {
            return null;
        }

        return [
            'lat' => $geocodeResult['lat'],
            'lng' => $geocodeResult['lng'],
            'type' => $geocodeResult['types']
        ];
    }
}