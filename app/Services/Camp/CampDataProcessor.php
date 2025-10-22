<?php

namespace App\Services\Camp;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CampDataProcessor
{
    /**
     * Process all camp data from request
     */
    public function processRequestData(Request $request, $existingCamp = null): array
    {
        // Process tagify data for target_fish and extras
        $targetFish = $this->processTagifyData($request->target_fish);
        $extras = $this->processTagifyData($request->extras);

        return [
            'user_id' => $request->user_id ?? Auth::id(),
            'title' => $request->title ?? 'Untitled',
            'location' => $request->location ?? '',
            'description_camp' => $request->description_camp ?? '',
            'description_area' => $request->description_area ?? '',
            'description_fishing' => $request->description_fishing ?? '',
            'latitude' => $request->latitude ?? null,
            'longitude' => $request->longitude ?? null,
            'country' => $request->country ?? '',
            'city' => $request->city ?? '',
            'region' => $request->region ?? '',
            'distance_to_store' => $request->distance_to_store ?? '',
            'distance_to_nearest_town' => $request->distance_to_nearest_town ?? '',
            'distance_to_airport' => $request->distance_to_airport ?? '',
            'distance_to_ferry_port' => $request->distance_to_ferry_port ?? '',
            'policies_regulations' => $request->policies_regulations ?? '',
            'target_fish' => $targetFish,
            'best_travel_times' => $request->best_travel_times ?? '',
            'travel_information' => $request->travel_information ?? '',
            'extras' => $extras,
        ];
    }

    /**
     * Process tagify data from request
     */
    private function processTagifyData($data): string
    {
        if (empty($data)) {
            return '';
        }

        // If it's already a string, return as is
        if (is_string($data)) {
            return $data;
        }

        // If it's an array, convert to comma-separated string
        if (is_array($data)) {
            return implode(',', array_filter($data));
        }

        // If it's JSON, decode and convert to comma-separated string
        if (is_string($data) && json_decode($data)) {
            $decoded = json_decode($data, true);
            if (is_array($decoded)) {
                $values = array_map(function($item) {
                    if (is_array($item) && isset($item['value'])) {
                        return $item['value'];
                    }
                    return $item;
                }, $decoded);
                return implode(',', array_filter($values));
            }
        }

        return '';
    }

    /**
     * Prepare edit form data from existing camp
     */
    public function prepareEditFormData($camp): array
    {
        return [
            'is_update' => 1,
            'id' => $camp->id,
            'camp_id' => $camp->id,
            'user_id' => $camp->user_id,
            'title' => $camp->title,
            'location' => $camp->location,
            'description_camp' => $camp->description_camp,
            'description_area' => $camp->description_area,
            'description_fishing' => $camp->description_fishing,
            'latitude' => $camp->latitude,
            'longitude' => $camp->longitude,
            'country' => $camp->country,
            'city' => $camp->city,
            'region' => $camp->region,
            'distance_to_store' => $camp->distance_to_store,
            'distance_to_nearest_town' => $camp->distance_to_nearest_town,
            'distance_to_airport' => $camp->distance_to_airport,
            'distance_to_ferry_port' => $camp->distance_to_ferry_port,
            'policies_regulations' => $camp->policies_regulations,
            'target_fish' => $camp->target_fish,
            'best_travel_times' => $camp->best_travel_times,
            'travel_information' => $camp->travel_information,
            'extras' => $camp->extras,
            'status' => $camp->status,
            'thumbnail_path' => $camp->thumbnail_path,
            'gallery_images' => $camp->gallery_images,
            'existing_images' => json_encode($this->formatImagePathsForWeb($camp->gallery_images ?? [])),
            'lat' => $camp->latitude,
            'lng' => $camp->longitude,
            'facilities' => $camp->facilities->pluck('name')->toArray(),
            'accommodations' => $camp->accommodations->pluck('id')->toArray(),
            'rental_boats' => $camp->rentalBoats->pluck('id')->toArray(),
            'guidings' => $camp->guidings->pluck('id')->toArray(),
            // Add slug for edit form
            'slug' => $camp->slug,
        ];
    }

    /**
     * Process camp facilities from request
     */
    public function processCampFacilities(Request $request): array
    {
        $facilities = $request->input('camp_facilities', '');
        if (empty($facilities)) {
            return [];
        }

        return array_filter(array_map('trim', explode(',', $facilities)));
    }

    /**
     * Process related entities (accommodations, rental boats, guidings)
     */
    public function processRelatedEntities(Request $request): array
    {
        return [
            'accommodations' => $request->input('accommodations', []),
            'rental_boats' => $request->input('rental_boats', []),
            'guidings' => $request->input('guidings', []),
        ];
    }

    /**
     * Format image paths for web access
     */
    private function formatImagePathsForWeb(array $imagePaths): array
    {
        return array_map(function($path) {
            // If the path already starts with 'storage/' or 'assets/', return as is
            if (str_starts_with($path, 'storage/') || str_starts_with($path, 'assets/')) {
                return $path;
            }
            
            // For camp images, prefix with 'storage/'
            if (str_starts_with($path, 'camps/')) {
                return 'storage/' . $path;
            }
            
            // For other paths, return as is
            return $path;
        }, $imagePaths);
    }
}
