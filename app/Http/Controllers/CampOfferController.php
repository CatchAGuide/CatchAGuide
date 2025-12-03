<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RentalBoat;
use App\Models\Accommodation;
use App\Models\Guiding;
use App\Models\Camp;
use Illuminate\Support\Str;

class CampOfferController extends Controller
{
    private function getImageUrl($path)
    {
        if (empty($path)) {
            return null;
        }
        
        // If path already starts with http or /, return as is
        if (str_starts_with($path, 'http') || str_starts_with($path, '/')) {
            return $path;
        }
        
        // Prepend / to make it a public URL
        return '/' . $path;
    }
    
    /**
     * Convert array of image paths to public URLs
     */
    private function getImageUrls($paths)
    {
        if (empty($paths) || !is_array($paths)) {
            return [];
        }
        
        return array_map(function($path) {
            return $this->getImageUrl($path);
        }, $paths);
    }

    /**
     * Normalize per-person pricing data to a consistent array format.
     */
    private function normalizePerPersonPricing($perPersonPricing): array
    {
        if (empty($perPersonPricing)) {
            return [];
        }

        if (is_string($perPersonPricing)) {
            $decodedPricing = json_decode($perPersonPricing, true);
            $perPersonPricing = is_array($decodedPricing) ? $decodedPricing : [];
        }

        if (!is_array($perPersonPricing)) {
            return [];
        }

        $normalized = [];

        foreach ($perPersonPricing as $tierId => $tierData) {
            if (!is_array($tierData)) {
                continue;
            }

            $normalized[$tierId] = [
                'person_count' => array_key_exists('person_count', $tierData) ? (int) $tierData['person_count'] : null,
                'price_per_night' => array_key_exists('price_per_night', $tierData) ? (float) $tierData['price_per_night'] : null,
                'price_per_week' => array_key_exists('price_per_week', $tierData) ? (float) $tierData['price_per_week'] : null,
            ];
        }

        return $normalized;
    }

    /**
     * Build the price payload for an accommodation, prioritising per-person tiers when available.
     */
    private function formatAccommodationPrice(Accommodation $accommodation): array
    {
        $currency = $accommodation->currency ?? 'EUR';
        $perPersonPricing = $this->normalizePerPersonPricing($accommodation->per_person_pricing);

        if (!empty($perPersonPricing)) {
            $primaryTier = collect($perPersonPricing)
                ->filter(fn ($tier) => is_array($tier) && (!is_null($tier['price_per_night']) || !is_null($tier['price_per_week'])))
                ->sortBy(function ($tier) {
                    if (!is_null($tier['price_per_night'])) {
                        return $tier['price_per_night'];
                    }

                    if (!is_null($tier['price_per_week'])) {
                        return $tier['price_per_week'];
                    }

                    return PHP_INT_MAX;
                })
                ->first();

            if (empty($primaryTier)) {
                $primaryTier = reset($perPersonPricing) ?: [];
            }

            $amount = $primaryTier['price_per_night'] ?? $primaryTier['price_per_week'] ?? null;

            return [
                'type' => 'per_person',
                'currency' => $currency,
                'amount' => $amount,
                'per_week' => $primaryTier['price_per_week'] ?? null,
                'tiers' => $perPersonPricing,
            ];
        }

        return [
            'type' => $accommodation->price_type ?? 'per_night',
            'amount' => $accommodation->price_per_night,
            'currency' => $currency,
            'per_week' => $accommodation->price_per_week
        ];
    }
    

    public function show($slug)
    {
        // Fetch camp from database with relationships
        $camp = Camp::with([
            'accommodations.accommodationType',
            'rentalBoats',
            'guidings.fishingFrom',
            'facilities'
        ])->where('slug', $slug)
        ->firstOrFail();
        
        // Map camp data to view format
        $campData = $this->mapCampData($camp);
        
        // Get first boat and guiding from relationships (for single card display)
        if ($camp->rentalBoats->first()) {
            $boat = $this->mapBoatData($camp->rentalBoats->first());
        } else {
            $boat = null;
        }
            
        if ($camp->guidings->first()) {
            $guiding = $this->mapGuidingData($camp->guidings->first());
        } else {
            $guiding = null;
        }

        // Process gallery images from camp and convert to public URLs
        $campGallery = $this->getImageUrls($camp->gallery_images ?? []);
        $campHero = $this->getImageUrl($camp->thumbnail_path) ?? ($campGallery[0] ?? null);
        
        // Merge and remove duplicates
        $allImages = array_merge(
            [$this->getImageUrl($camp->thumbnail_path) ?? null],
            $campGallery
        );
        $galleryImages = array_values(array_filter(array_unique($allImages)));
        
        if (empty($galleryImages)) {
            $galleryImages = [$campHero];
        }
        
        $primaryImage = $galleryImages[0] ?? null;
        $topRightImages = array_slice($galleryImages, 1, 2);
        $bottomStripImages = array_slice($galleryImages, 3, 5);
        $remainingGalleryCount = max(0, count($galleryImages) - 8);
        
        // For configurator dropdown options - get all related items
        $accommodations = $camp->accommodations->map(function($acc) {
            return $this->mapAccommodationData($acc);
        })->toArray();
        
        // Map all boats with full data for card display
        $boats = $camp->rentalBoats->map(function($boat) {
            return $this->mapBoatData($boat);
        })->toArray();
        
        // For dropdown - simplified version
        $boatsDropdown = $camp->rentalBoats->map(function($boat) {
            return $this->mapBoatForDropdown($boat);
        })->toArray();
    
        // Map all guidings with full data for display
        $guidings = $camp->guidings->map(function($guiding) {
            return $this->mapGuidingData($guiding);
        })->toArray();
        
        // For dropdown - simplified version
        $guidingsDropdown = $camp->guidings->map(function($guiding) {
            return $this->mapGuidingForDropdown($guiding);
        })->toArray();
        
        $showCategories = true;

        return view('pages.vacations.v2', compact(
            'campData',
            'guiding',
            'boat',
            'accommodations',
            'boats',
            'guidings',
            'guidingsDropdown',
            'showCategories',
            'primaryImage',
            'topRightImages',
            'bottomStripImages',
            'remainingGalleryCount',
            'galleryImages'
        ))->with('camp', $campData);
    }
    
    /**
     * Parse best travel times text into structured format with titles and descriptions
     * Titles are rendered as headings, descriptions as bullet points
     */
    private function parseBestTravelTimesText($text)
    {
        if (empty($text)) {
            return [];
        }
        
        // Normalize line endings (handle \r\n, \r, and \n)
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        
        // Split by double newlines to get sections (each section is title + description)
        $sections = preg_split('/\n\s*\n/', trim($text), -1, PREG_SPLIT_NO_EMPTY);
        
        $parsed = [];
        
        foreach ($sections as $section) {
            $lines = array_map('trim', explode("\n", $section));
            $lines = array_filter($lines, function($line) {
                return !empty($line);
            });
            
            if (empty($lines)) {
                continue;
            }
            
            // First line is the title, rest are description lines
            $title = array_shift($lines);
            
            // Join description lines with space (in case description spans multiple lines)
            $description = implode(' ', $lines);
            
            if (!empty($title)) {
                $parsed[] = [
                    'title' => $title,
                    'description' => $description
                ];
            }
        }
        
        return $parsed;
    }
    
    /**
     * Map Camp model to view format
     */
    private function mapCampData(Camp $camp)
    {
        // Process target fish - could be comma-separated string or JSON array
        $targetFish = [];
        if (!empty($camp->target_fish)) {
            if (is_string($camp->target_fish)) {
                // Try JSON decode first
                $decoded = json_decode($camp->target_fish, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $targetFish = $decoded;
                } else {
                    // Fall back to comma-separated
                    $targetFish = array_map('trim', explode(',', $camp->target_fish));
                }
            } elseif (is_array($camp->target_fish)) {
                $targetFish = $camp->target_fish;
            }
        }
        
        // Process best travel times - currently stored as text, not structured data
        $bestTravelTimes = [];
        $bestTravelTimesParsed = [];
        if (!empty($camp->best_travel_times)) {
            if (is_string($camp->best_travel_times)) {
                // Try JSON decode first
                $decoded = json_decode($camp->best_travel_times, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $bestTravelTimes = $decoded;
                } else {
                    // Parse text format: title on one line, description on next, separated by empty lines
                    $bestTravelTimesParsed = $this->parseBestTravelTimesText($camp->best_travel_times);
                }
            } elseif (is_array($camp->best_travel_times)) {
                $bestTravelTimes = $camp->best_travel_times;
            }
        }
        
        // Get amenities directly from the camp_facility_camp pivot table relationship
        // This is fully dynamic - whatever facilities are assigned to the camp will show
        $amenities = [];
        
        foreach ($camp->facilities as $facility) {
            $amenities[] = [
                'id' => $facility->id,
                'name' => $facility->name,
                'name_en' => $facility->name_en ?? $facility->name,
                'name_de' => $facility->name_de ?? $facility->name,
            ];
        }
        
        // Parse distances to extract numeric values
        $distanceToShop = $this->extractDistanceValue($camp->distance_to_store);
        $distanceToTown = $this->extractDistanceValue($camp->distance_to_nearest_town);
        $distanceToAirport = $this->extractDistanceValue($camp->distance_to_airport);
        $distanceToFerry = $this->extractDistanceValue($camp->distance_to_ferry_port);
        
        return [
            'id' => $camp->id,
            'title' => $camp->title,
            'city' => $camp->city,
            'region' => $camp->region,
            'country' => $camp->country,
            'lat' => $camp->latitude,
            'lng' => $camp->longitude,
            'description' => [
                'camp_description' => $camp->description_camp ?? '',
                'camp_area' => $camp->description_area ?? '',
                'camp_area_fishing' => $camp->description_fishing ?? '',
            ],
            'distances' => [
                'to_shop_km' => $distanceToShop,
                'to_shop_label' => $camp->distance_to_store,
                'to_town_km' => $distanceToTown,
                'to_town_label' => $camp->distance_to_nearest_town,
                'to_airport_km' => $distanceToAirport,
                'to_airport_label' => $camp->distance_to_airport,
                'to_ferry_km' => $distanceToFerry,
                'to_ferry_label' => $camp->distance_to_ferry_port,
            ],
            'amenities' => $amenities, // Dynamic from pivot table camp_facility_camp
            'policies_regulations' => $camp->policies_regulations ? array_filter(array_map('trim', explode("\n", $camp->policies_regulations))) : [],
            'best_travel_times' => $bestTravelTimes,
            'best_travel_times_parsed' => $bestTravelTimesParsed,
            'travel_info' => $camp->travel_information ? array_filter(array_map('trim', explode("\n", $camp->travel_information))) : [],
            'extras' => $camp->extras ? array_filter(array_map('trim', explode(',', $camp->extras))) : [],
            'target_fish' => $targetFish,
            'conditions' => [
                'minimum_stay_nights' => $camp->minimum_stay_nights ?? null,
                'booking_window' => $camp->booking_window ?? null,
            ],
            'thumbnail_path' => $this->getImageUrl($camp->thumbnail_path),
            'manual_gallery_images' => $this->getImageUrls($camp->gallery_images ?? []),
        ];
    }
    
    /**
     * Extract numeric distance value from string like "Fayón – 4 km"
     */
    private function extractDistanceValue($distanceString)
    {
        if (empty($distanceString)) {
            return null;
        }
        
        // Try to extract number followed by km
        if (preg_match('/(\d+(?:\.\d+)?)\s*km/i', $distanceString, $matches)) {
            return floatval($matches[1]);
        }
        
        // Try to extract just any number
        if (preg_match('/(\d+(?:\.\d+)?)/', $distanceString, $matches)) {
            return floatval($matches[1]);
        }
        
        return null;
    }
    
    /**
     * Map RentalBoat model to view format
     */
    private function mapBoatData(RentalBoat $boat)
    {
        $boatInfo = $boat->boat_information ?? [];
        $prices = $boat->prices ?? [];
        
        // Handle both indexed and associative price arrays
        $firstPrice = null;
        if (is_array($prices) && !empty($prices)) {
            if (isset($prices[0])) {
                // Indexed array format
                $firstPrice = $prices[0];
            } else {
                // Associative array format - get price based on boat's price type
                $priceType = $boat->price_type ?? 'per_day';
                if (isset($prices[$priceType])) {
                    $firstPrice = ['amount' => $prices[$priceType], 'currency' => 'EUR'];
                } else {
                    // Fallback to first available price
                    $firstPrice = ['amount' => reset($prices), 'currency' => 'EUR'];
                }
            }
        }

        $priceType = $boat->price_type ?? 'per_day';
        $priceAmount = (float) ($firstPrice['amount'] ?? 0);
        $priceTypeMap = [
            'per_day' => __('per day'),
            'per_hour' => __('per hour'),
            'per_week' => __('per week'),
            'per_night' => __('per night'),
        ];

        // Process gallery images
        $galleryImages = $this->getImageUrls($boat->gallery_images ?? []);
        $galleryCount = count($galleryImages);

        // Process inclusives
        $inclusiveItems = collect(is_array($boat->boat_extras) ? $boat->boat_extras : [])
            ->map(function ($item) {
                return is_array($item) ? ($item['name'] ?? ($item['value'] ?? json_encode($item))) : $item;
            })
            ->filter(fn ($value) => filled($value))
            ->values()
            ->toArray();

        // Process extras
        $extraItems = collect(is_array($boat->pricing_extra) ? $boat->pricing_extra : [])
            ->map(function ($item) {
                return $item['name']. ": ". $item['price'];
            })
            ->filter(fn ($value) => filled($value))
            ->values()
            ->toArray();

        // Process requirements
        $requirementsRaw = $boat->requirements ?? [];
        $requirementItems = collect(is_array($requirementsRaw) ? $requirementsRaw : [])
            ->map(function ($item) {
                return is_array($item) ? ($item['name'] ?? ($item['value'] ?? json_encode($item))) : $item;
            })
            ->filter(fn ($value) => filled($value))
            ->values()
            ->toArray();

        // Find license requirement
        $licenseRequirement = null;
        if (!empty($requirementsRaw) && is_array($requirementsRaw)) {
            foreach ($requirementsRaw as $requirement) {
                $value = is_array($requirement) ? ($requirement['name'] ?? ($requirement['value'] ?? null)) : $requirement;
                if ($value && (stripos($value, 'license') !== false || stripos($value, 'führerschein') !== false)) {
                    $licenseRequirement = $value;
                    break;
                }
            }
        }

        // Build specs array
        $specs = [];
        foreach ($boatInfo as $boatIn) {
            if ($boatIn['id'] == 1 && $boatIn['value'] != "") {
                $specs[] = [
                    'label' => __('Capacity'),
                    'value' => $boatIn['value'],
                ];
            }
            if ($boatIn['id'] == 6 && $boatIn['value'] != "") {
                $specs[] = [
                    'label' => __('Motor'),
                    'value' => $boatIn['value'],
                ];
            }
        }
        
        // License requirement
        if ($licenseRequirement) {
            $specs[] = [
                'label' => __('License'),
                'value' => $licenseRequirement,
            ];
        }
        
        return [
            'id' => $boat->id,
            'title' => $boat->title,
            'type' => $boat->boatType->name ?? 'Boat',
            'location' => $boat->location,
            'description' => $boat->desc_of_boat,
            'thumbnail_path' => $this->getImageUrl($boat->thumbnail_path),
            'gallery_images' => $this->getImageUrls($boat->gallery_images ?? []),
            'gallery_count' => $galleryCount,
            'seats' => $boatInfo['seats'] ?? null,
            'length_m' => $boatInfo['length_m'] ?? null,
            'width_m' => $boatInfo['width_m'] ?? null,
            'year_built' => $boatInfo['year_built'] ?? null,
            'manufacturer' => $boatInfo['manufacturer'] ?? null,
            'engine' => $boatInfo['engine'] ?? null,
            'power' => $boatInfo['power'] ?? null,
            'max_speed_kmh' => $boatInfo['max_speed_kmh'] ?? null,
            'boat_info' => $boatInfo,
            'equipment' => $boat->boat_extras ?? [],
            'requirements' => $requirementItems,
            'inclusives' => $inclusiveItems,
            'extras' => $extraItems,
            'specs' => $specs,
            'price' => [
                'amount' => $priceAmount,
                'currency' => $firstPrice['currency'] ?? 'EUR',
                'type' => $priceType,
                'display_type' => $priceTypeMap[$priceType] ?? $priceType,
            ]
        ];
    }
    
    /**
     * Map boat for dropdown
     */
    private function mapBoatForDropdown(RentalBoat $boat)
    {
        $boatInfo = $boat->boat_information ?? [];
        $prices = $boat->prices ?? [];
        
        // Handle both indexed and associative price arrays
        $firstPrice = null;
        if (is_array($prices) && !empty($prices)) {
            if (isset($prices[0])) {
                // Indexed array format
                $firstPrice = $prices[0];
            } else {
                // Associative array format - get price based on boat's price type
                $priceType = $boat->price_type ?? 'per_day';
                if (isset($prices[$priceType])) {
                    $firstPrice = ['amount' => $prices[$priceType], 'currency' => 'EUR'];
                } else {
                    // Fallback to first available price
                    $firstPrice = ['amount' => reset($prices), 'currency' => 'EUR'];
                }
            }
        }
        
        return [
            'id' => $boat->id,
            'title' => $boat->title,
            'seats' => $boatInfo['seats'] ?? null,
            'sonar_gps' => in_array('sonar', $boat->boat_extras ?? []) || in_array('gps', $boat->boat_extras ?? []),
            'price_per_day' => $firstPrice['amount'] ?? 0,
            'currency' => $firstPrice['currency'] ?? 'EUR',
            'img' => $this->getImageUrl($boat->thumbnail_path),
        ];
    }
    
    /**
     * Map Accommodation model to view format
     */
    private function mapAccommodationData(Accommodation $accommodation)
    {
        $galleryImages = $this->getImageUrls($accommodation->gallery_images ?? []);

        $minOccupancy = $accommodation->min_occupancy;
        $maxOccupancy = $accommodation->max_occupancy;
        $occupancyLabel = null;

        if ($minOccupancy && $maxOccupancy) {
            $occupancyLabel = (int) $minOccupancy === (int) $maxOccupancy
                ? (string) $maxOccupancy
                : $minOccupancy . '–' . $maxOccupancy;
        } elseif ($maxOccupancy) {
            $occupancyLabel = (string) $maxOccupancy;
        } elseif ($minOccupancy) {
            $occupancyLabel = (string) $minOccupancy;
        }

        $bedConfig = $accommodation->bed_types ?? [];

        $bedSummaryParts = [];
        foreach ($accommodation->room_configurations as $roomConfig) {
            array_push( $bedSummaryParts, "(" . $roomConfig['value'] . ") " . $roomConfig['name'] );
        }

        $galleryTotal = max(count($galleryImages), 1);
        
        $number_of_bedrooms = "";
        $living_area_sqm = "";
        foreach ($accommodation->accommodation_details as $accommodation_detail) {
            if ($accommodation_detail['id'] == 4 && $accommodation_detail['value'] != "") {
                $number_of_bedrooms = $accommodation_detail['value'];
            }
            if ($accommodation_detail['id'] == 1 && $accommodation_detail['value'] != "") {
                $living_area_sqm = $accommodation_detail['value'];
            }
        }
        
        return [
            'id' => $accommodation->id,
            'title' => $accommodation->title,
            'accommodation_type' => $accommodation->accommodationType->name ?? 'Accommodation',
            'thumbnail_path' => $this->getImageUrl($accommodation->thumbnail_path),
            'gallery_images' => $galleryImages,
            'gallery_total' => $galleryTotal,
            'min_occupancy' => $minOccupancy,
            'max_occupancy' => $maxOccupancy,
            'occupancy_label' => $occupancyLabel,
            'city' => $accommodation->city,
            'region' => $accommodation->region,
            'country' => $accommodation->country,
            'description' => $accommodation->description,
            'living_area_sqm' => $living_area_sqm,
            'number_of_bedrooms' => $number_of_bedrooms,
            'bathroom_count' => $accommodation->number_of_bathrooms,
            'bed_summary' => implode(', ',$bedSummaryParts),
            'bed_config' => $bedConfig,
            'location_description' => $accommodation->location_description,
            'distances' => [
                'to_water_m' => $accommodation->distance_to_water_m,
                'to_berth_m' => $accommodation->distance_to_boat_berth_m,
                'to_parking_m' => $accommodation->distance_to_parking_m
            ],
            'amenities' => $accommodation->amenities ?? [],
            'kitchen' => $accommodation->kitchen_equipment ?? [],
            'bathroom_laundry' => $accommodation->bathroom_amenities ?? [],
            'policies' => $accommodation->policies,
            'extras_inclusives' => [
                'inclusives' => $accommodation->inclusives,
                'extras' => $accommodation->extras,
            ],
            'price' => $this->formatAccommodationPrice($accommodation),
            'changeover_day' => $accommodation->changeover_day,
            'minimum_stay_nights' => $accommodation->minimum_stay_nights,
            'accommodation_details' => $accommodation->accommodation_details,
        ];
    }
    
    /**
     * Map Guiding model to view format
     */
    private function mapGuidingData(Guiding $guiding)
    {
        // Decode gallery_images if it's a JSON string
        $galleryImages = is_string($guiding->gallery_images) 
            ? json_decode($guiding->gallery_images, true) 
            : $guiding->gallery_images;
        
        // Use model methods to get actual names instead of IDs
        $targetFish = $guiding->getTargetFishNames();
        $fishingMethods = $guiding->getFishingMethodNames();
        $inclusions = $guiding->getInclusionNames();
        
        return [
            'id' => $guiding->id,
            'title' => $guiding->title,
            'location' => $guiding->location,
            'description' => $guiding->description ?? $guiding->desc_course_of_action,
            'thumbnail_path' => $this->getImageUrl($guiding->thumbnail_path),
            'gallery_images' => $this->getImageUrls($galleryImages ?? []),
            'duration_hours' => $guiding->duration_type ?? 4,
            'max_persons' => $guiding->max_guests,
            'type' => $guiding->tour_type,
            'guiding_info' => [
                'art' => $guiding->fishingFrom->name ?? 'Tour',
                'dauer' => $guiding->duration_type . ' h',
                'max_personen' => $guiding->max_guests,
                'gewaesser' => $guiding->water_name ?? 'Water'
            ],
            'target_fish' => $targetFish,
            'methods' => $fishingMethods,
            'meeting_point' => $guiding->meeting_point,
            'start_times' => $guiding->desc_starting_time ? explode(',', $guiding->desc_starting_time) : [],
            'inclusives' => $inclusions,
            'price' => [
                'amount' => $guiding->price,
                'currency' => 'EUR',
                'type' => $guiding->price_type ?? 'per_boat'
            ]
        ];
    }
    
    /**
     * Map guiding for dropdown
     */
    private function mapGuidingForDropdown(Guiding $guiding)
    {
        return [
            'id' => $guiding->id,
            'title' => $guiding->title,
            'group_size' => $guiding->max_guests,
            'price' => $guiding->price,
            'currency' => 'EUR',
            'img' => $this->getImageUrl($guiding->thumbnail_path),
        ];
    }
}
