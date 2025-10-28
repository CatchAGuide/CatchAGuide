<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RentalBoat;
use App\Models\Accommodation;
use App\Models\Guiding;
use App\Models\Camp;

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
    

    public function show($campId)
    {
        // Fetch camp from database with relationships
        $camp = Camp::with(['accommodations', 'rentalBoats', 'guidings', 'facilities'])
            ->findOrFail($campId);
        
        // Log raw camp data
        \Log::info('=== CAMP DATA DEBUG ===', [
            'camp_id' => $camp->id,
            'camp_title' => $camp->title,
            'accommodations_count' => $camp->accommodations->count(),
            'boats_count' => $camp->rentalBoats->count(),
            'guidings_count' => $camp->guidings->count(),
            'facilities_count' => $camp->facilities->count(),
        ]);
        
        \Log::info('Raw Camp Model Data:', $camp->toArray());
        
        // Map camp data to view format
        $campData = $this->mapCampData($camp);
        
        \Log::info('Mapped Camp Data:', $campData);
        
        // Get first accommodation, boat, and guiding from relationships
        if ($camp->accommodations->first()) {
            \Log::info('Raw Accommodation Data:', $camp->accommodations->first()->toArray());
            $accommodation = $this->mapAccommodationData($camp->accommodations->first());
            \Log::info('Mapped Accommodation Data:', $accommodation);
        } else {
            \Log::warning('No accommodations found for camp');
            $accommodation = null;
        }
            
        if ($camp->rentalBoats->first()) {
            \Log::info('Raw Boat Data:', $camp->rentalBoats->first()->toArray());
            $boat = $this->mapBoatData($camp->rentalBoats->first());
            \Log::info('Mapped Boat Data:', $boat);
        } else {
            \Log::warning('No boats found for camp');
            $boat = null;
        }
            
        if ($camp->guidings->first()) {
            \Log::info('Raw Guiding Data:', $camp->guidings->first()->toArray());
            $guiding = $this->mapGuidingData($camp->guidings->first());
            \Log::info('Mapped Guiding Data:', $guiding);
        } else {
            \Log::warning('No guidings found for camp');
            $guiding = null;
        }

        // Process gallery images from camp and convert to public URLs
        $campGallery = $this->getImageUrls($camp->gallery_images ?? []);
        $campHero = $this->getImageUrl($camp->thumbnail_path) ?? ($campGallery[0] ?? null);
        $galleryImages = array_values(array_filter(array_merge(
            [$this->getImageUrl($camp->thumbnail_path) ?? null],
            $campGallery
        )));
        
        if (empty($galleryImages)) {
            $galleryImages = [$campHero];
        }
        
        $primaryImage = $galleryImages[0] ?? null;
        $topRightImages = array_slice($galleryImages, 1, 2);
        while (count($topRightImages) < 2 && $primaryImage) {
            $topRightImages[] = $primaryImage;
        }
        
        $bottomStripImages = array_slice($galleryImages, 3, 5);
        $fallbackIndex = 0;
        while (count($bottomStripImages) < 5 && !empty($galleryImages)) {
            $bottomStripImages[] = $galleryImages[$fallbackIndex % count($galleryImages)];
            $fallbackIndex++;
            if ($fallbackIndex > 20) break;
        }
        $bottomStripImages = array_slice($bottomStripImages, 0, 5);
        $remainingGalleryCount = max(0, count($galleryImages) - 8);
        
        // For configurator dropdown options - get all related items
        $accommodations = $camp->accommodations->map(function($acc) {
            return $this->mapAccommodationData($acc);
        })->toArray();
        
        $boats = $camp->rentalBoats->map(function($boat) {
            return $this->mapBoatForDropdown($boat);
        })->toArray();
        
        $guidings = $camp->guidings->map(function($guiding) {
            return $this->mapGuidingForDropdown($guiding);
        })->toArray();
        
        \Log::info('Dropdown Options:', [
            'accommodations_count' => count($accommodations),
            'boats_count' => count($boats),
            'guidings_count' => count($guidings),
        ]);
        
        $showCategories = true;

        return view('pages.vacations.v2', compact(
            'campData',
            'accommodation',
            'guiding',
            'boat',
            'accommodations',
            'boats',
            'guidings',
            'showCategories',
            'primaryImage',
            'topRightImages',
            'bottomStripImages',
            'remainingGalleryCount'
        ))->with('camp', $campData);
    }
    
    /**
     * Map Camp model to view format
     */
    private function mapCampData(Camp $camp)
    {
        // Decode JSON fields if they're strings
        $facilities = $camp->facilities->pluck('name')->toArray();
        
        // target_fish and best_travel_times are cast to array in model
        // but we need to handle old data that might be strings
        $targetFish = $camp->target_fish;
        if (is_string($targetFish) && !empty($targetFish)) {
            $targetFish = json_decode($targetFish, true);
        }
        
        $bestTravelTimes = $camp->best_travel_times;
        if (is_string($bestTravelTimes) && !empty($bestTravelTimes)) {
            $bestTravelTimes = json_decode($bestTravelTimes, true);
        }
        
        \Log::info('Camp Mapping Debug:', [
            'target_fish_raw' => $camp->target_fish,
            'target_fish_type' => gettype($camp->target_fish),
            'target_fish_decoded' => $targetFish,
            'best_travel_times_raw' => $camp->best_travel_times,
            'best_travel_times_type' => gettype($camp->best_travel_times),
            'best_travel_times_decoded' => $bestTravelTimes,
            'facilities_count' => count($facilities),
        ]);
        
        return [
            'title' => $camp->title,
            'city' => $camp->city,
            'region' => $camp->region,
            'country' => $camp->country,
            'lat' => $camp->latitude,
            'lng' => $camp->longitude,
            'description' => [
                'camp_area_fishing' => $camp->description_fishing ?? '',
                'camp_area' => $camp->description_area ?? '',
                'camp_description' => $camp->description_camp ?? '',
            ],
            'distances' => [
                'to_shop_km' => $camp->distance_to_store,
                'to_town_km' => $camp->distance_to_nearest_town,
                'to_airport_km' => $camp->distance_to_airport,
                'to_ferry_km' => $camp->distance_to_ferry_port,
            ],
            'amenities' => $facilities,
            'policies_regulations' => $camp->policies_regulations ? explode("\n", $camp->policies_regulations) : [],
            'best_travel_times' => is_array($bestTravelTimes) ? $bestTravelTimes : [],
            'travel_info' => $camp->travel_information ? explode("\n", $camp->travel_information) : [],
            'extras' => $camp->extras ? array_map('trim', explode(',', $camp->extras)) : [],
            'target_fish' => is_array($targetFish) ? $targetFish : [],
            'conditions' => [
                'minimum_stay_nights' => null,
                'booking_window' => null,
            ],
            'thumbnail_path' => $this->getImageUrl($camp->thumbnail_path),
            'manual_gallery_images' => $this->getImageUrls($camp->gallery_images ?? []),
        ];
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
        
        return [
            'id' => $boat->id,
            'title' => $boat->title,
            'type' => $boat->boatType->name ?? 'Boat',
            'location' => $boat->location,
            'description' => $boat->desc_of_boat,
            'thumbnail_path' => $this->getImageUrl($boat->thumbnail_path),
            'gallery_images' => $this->getImageUrls($boat->gallery_images ?? []),
            'seats' => $boatInfo['seats'] ?? null,
            'length_m' => $boatInfo['length_m'] ?? null,
            'width_m' => $boatInfo['width_m'] ?? null,
            'year_built' => $boatInfo['year_built'] ?? null,
            'manufacturer' => $boatInfo['manufacturer'] ?? null,
            'engine' => $boatInfo['engine'] ?? null,
            'power' => $boatInfo['power'] ?? null,
            'max_speed_kmh' => $boatInfo['max_speed_kmh'] ?? null,
            'equipment' => $boat->boat_extras ?? [],
            'requirements' => $boat->requirements ?? [],
            'inclusives' => $boat->inclusions ?? [],
            'extras' => $boat->pricing_extra ?? [],
            'price' => [
                'amount' => $firstPrice['amount'] ?? 0,
                'currency' => $firstPrice['currency'] ?? 'EUR',
                'type' => $boat->price_type ?? 'per_day'
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
        $accommodationDetails = $accommodation->accommodation_details ?? [];
        
        return [
            'id' => $accommodation->id,
            'title' => $accommodation->title,
            'accommodation_type' => $accommodation->accommodation_type,
            'thumbnail_path' => $this->getImageUrl($accommodation->thumbnail_path),
            'gallery_images' => $this->getImageUrls($accommodation->gallery_images ?? []),
            'city' => $accommodation->city,
            'region' => $accommodation->region,
            'country' => $accommodation->country,
            'description' => $accommodation->description,
            'living_area_sqm' => $accommodation->living_area_sqm,
            'max_occupancy' => $accommodation->max_occupancy,
            'number_of_bedrooms' => $accommodation->number_of_bedrooms,
            'bathrooms' => $accommodation->bathroom,
            'floors' => $accommodation->floor_layout,
            'year_or_renovated' => $accommodation->condition_or_style,
            'living_room' => $accommodationDetails['living_room'] ?? false,
            'dining_room' => $accommodationDetails['dining_room'] ?? false,
            'bed_config' => $accommodation->bed_types ?? [],
            'location_description' => $accommodation->location_description,
            'distances' => [
                'to_water_m' => $accommodation->distance_to_water_m,
                'to_berth_m' => $accommodation->distance_to_boat_berth_m,
                'to_parking_m' => $accommodation->distance_to_parking_m
            ],
            'amenities' => $accommodation->amenities ?? [],
            'kitchen' => $accommodation->kitchen_equipment ?? [],
            'bathroom_laundry' => $accommodation->bathroom_amenities ?? [],
            'policies' => $accommodation->policies ?? [],
            'extras_inclusives' => [
                'inclusives' => $accommodation->inclusives ?? [],
                'extras' => $accommodation->extras ?? [],
            ],
            'price' => [
                'type' => $accommodation->price_type ?? 'per_night',
                'amount' => $accommodation->price_per_night,
                'currency' => $accommodation->currency,
                'per_week' => $accommodation->price_per_week
            ],
            'changeover_day' => $accommodation->changeover_day,
            'minimum_stay_nights' => $accommodation->minimum_stay_nights,
        ];
    }
    
    /**
     * Map Guiding model to view format
     */
    private function mapGuidingData(Guiding $guiding)
    {
        // Decode JSON fields
        $targetFish = is_string($guiding->target_fish) ? json_decode($guiding->target_fish, true) : $guiding->target_fish;
        $fishingMethods = is_string($guiding->fishing_methods) ? json_decode($guiding->fishing_methods, true) : $guiding->fishing_methods;
        $inclusions = is_string($guiding->inclusions) ? json_decode($guiding->inclusions, true) : $guiding->inclusions;
        
        return [
            'id' => $guiding->id,
            'title' => $guiding->title,
            'location' => $guiding->location,
            'description' => $guiding->description ?? $guiding->desc_course_of_action,
            'thumbnail_path' => $this->getImageUrl($guiding->thumbnail_path),
            'gallery_images' => $this->getImageUrls($guiding->gallery_images ?? []),
            'duration_hours' => $guiding->duration_type ?? 4,
            'max_persons' => $guiding->max_guests,
            'type' => $guiding->tour_type,
            'guiding_info' => [
                'art' => $guiding->fishingFrom->name ?? 'Tour',
                'dauer' => $guiding->duration_type . ' h',
                'max_personen' => $guiding->max_guests,
                'gewaesser' => $guiding->water_name ?? 'Water'
            ],
            'target_fish' => $targetFish ?? [],
            'methods' => $fishingMethods ?? [],
            'meeting_point' => $guiding->meeting_point,
            'start_times' => $guiding->desc_starting_time ? explode(',', $guiding->desc_starting_time) : [],
            'inclusives' => $inclusions ?? [],
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

    private function getSampleCamp()
    {
        return [
            'title' => 'Ebro Fishing Camp – Riba Roja',
            'city' => 'Riba Roja d\'Ebre',
            'region' => 'Catalonia',
            'country' => 'Spain',
            'lat' => 41.2506,
            'lng' => 0.4921,
            'description' => [
                'camp_area_fishing' => 'Directly at the reservoir. Shallow water bays + old river courses – Top for catfish, pike perch & black bass. Short distances to pier & slipway.',
            ],
            'distances' => [
                'to_shop_km' => 5,
                'to_town_km' => 7,
                'to_airport_km' => 95,
                'to_ferry_km' => 180,
            ],
            'amenities' => [
                'swimming_pool' => false,
                'private_jetty' => true,
                'fish_cleaning_station' => true,
                'smoker_device' => true,
                'bbq_area' => true,
                'lockable_fishing_storage' => true,
                'fireplace' => false,
                'sauna' => false,
                'hot_tub' => false,
                'games_corner' => true,
                'parking_spaces' => true,
                'ev_charger' => true,
                'boat_ramp_nearby' => true,
                'reception' => true,
                'fishfilet_freezer' => true,
            ],
            'conditions' => [
                'minimum_stay_nights' => 3,
                'booking_window' => 'Bookable until 2 days before arrival, high season min. 7 nights (Sat-Sat)',
            ],
            'policies_regulations' => [
                'Fishing licenses required (available at camp)',
                'Catch & Release for black bass, observe local rules',
                'Life jacket mandatory on the boat',
            ],
            'best_travel_times' => [
                ['month' => 'Mar', 'note' => 'Pike perch active'],
                ['month' => 'Apr', 'note' => 'Catfish season start'],
                ['month' => 'Oct', 'note' => 'Top for pike perch/jerkbait'],
            ],
            'target_fish' => ['Catfish', 'Pike perch', 'Black bass', 'Carp'],
            'travel_info' => [
                'Arrival via Barcelona (BCN) or Reus (REU)',
                'Rental car recommended; road conditions good',
            ],
            'extras' => ['Bed linen', 'Towels', 'Final cleaning', 'Equipment rental'],
            'manual_gallery_images' => [
                'https://images.unsplash.com/photo-1512914890250-33530e2f0d72?q=80&w=1600&auto=format&fit=crop',
                'https://images.unsplash.com/photo-1469474968028-56623f02e42e?q=80&w=1600&auto=format&fit=crop',
                'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?q=80&w=1600&auto=format&fit=crop',
                'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?q=80&w=1600&auto=format&fit=crop',
                'https://images.unsplash.com/photo-1470071459604-3b5ec3a7fe05?q=80&w=1600&auto=format&fit=crop',
                'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?q=80&w=1600&auto=format&fit=crop',
                'https://images.unsplash.com/photo-1448375240586-882707db888b?q=80&w=1600&auto=format&fit=crop',
                'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?q=80&w=1600&auto=format&fit=crop'
            ],
            'thumbnail_path' => 'https://images.unsplash.com/photo-1512914890250-33530e2f0d72?q=80&w=1600&auto=format&fit=crop',
        ];
    }

    private function getSampleAccommodation()
    {
        return [
            'id' => 1,
            'title' => 'Apartment 3 – Lake View',
            'accommodation_type' => 'Apartment / Holiday Home',
            'thumbnail_path' => 'https://images.unsplash.com/photo-1519710164239-da123dc03ef4?q=80&w=1600&auto=format&fit=crop',
            'gallery_images' => [
                'https://images.unsplash.com/photo-1523217582562-09d0def993a6?q=80&w=1600&auto=format&fit=crop',
                'https://images.unsplash.com/photo-1505691723518-36a1f0f6f1b6?q=80&w=1600&auto=format&fit=crop',
                'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?q=80&w=1600&auto=format&fit=crop',
            ],
            'city' => 'Riba Roja d\'Ebre',
            'region' => 'Catalonia',
            'country' => 'Spain',
            'description' => 'Bright apartment with direct water access – perfect for 2–4 anglers. Short distances to boat jetty & filleting station.',
            'living_area_sqm' => 80,
            'max_occupancy' => 4,
            'number_of_bedrooms' => 2,
            'bathrooms' => 1,
            'floors' => 'EG',
            'year_or_renovated' => 'Renovated 2023',
            'living_room' => true,
            'dining_room' => true,
            'bed_config' => [
                'single' => 2,
                'double' => 1,
                'sofabed' => 0,
                'bunk' => 0,
                'child' => 0,
                'folding' => 0
            ],
            'location_description' => 'Near the shore, sheltered bay; ideal for mooring and casting off.',
            'distances' => [
                'to_water_m' => 40,
                'to_berth_m' => 60,
                'to_parking_m' => 20
            ],
            'amenities' => [
                'terrace' => true,
                'garden' => false,
                'pool' => false,
                'bbq_area' => true,
                'lockable_fishing_storage' => true,
                'parking_spaces' => true,
                'ev_charger' => false,
                'tv' => true,
                'keybox' => true,
                'heating' => true,
                'aircon' => false,
                'fireplace' => false,
                'sauna' => false,
                'hot_tub' => false,
                'games_corner' => false,
                'fish_cleaning_station' => true,
                'fishfilet_freezer' => true,
                'wifi' => true,
            ],
            'kitchen' => [
                'refrigerator_freezer' => true,
                'freezer_compartment' => true,
                'oven' => true,
                'stove' => true,
                'microwave' => true,
                'dishwasher' => true,
                'coffee_machine' => 'Filter',
                'kettle' => true,
                'toaster' => true,
                'blender' => false,
                'cutlery' => true,
                'baking_equipment' => true,
                'dishwashing_items' => true,
                'wine_glasses' => true,
                'pans_pots' => true,
                'sink' => true,
                'basics' => true,
            ],
            'bathroom_laundry' => [
                'toilet' => 1,
                'shower' => 1,
                'washbasin' => 1,
                'washing_machine' => true,
                'dryer' => false,
                'separate_wc_bath' => false,
                'iron_board' => true,
                'drying_rack' => true,
            ],
            'policies' => [
                'pets_allowed' => false,
                'smoking_allowed' => false,
                'children_allowed' => true,
                'accessible' => false,
                'self_checkin' => true,
                'quiet_hours' => '22:00–7:00',
                'waste_rules' => 'Waste separation, containers at parking lot',
                'only_registered_guests' => true,
                'deposit_required' => true,
                'energy_included' => true,
                'water_included' => true,
            ],
            'extras_inclusives' => [
                'inclusives' => ['WiFi', 'Electricity/Heating'],
                'extras' => ['Bed linen', 'Towels', 'Final cleaning'],
            ],
            'price' => [
                'type' => 'per night',
                'amount' => 110,
                'currency' => 'EUR',
                'per_week' => 690
            ],
            'changeover_day' => 'Saturday',
            'minimum_stay_nights' => 3,
        ];
    }

    private function getSampleGuiding()
    {
        return [
            'id' => 1,
            'title' => 'Shore-Guiding Nacht - Wels',
            'location' => 'Bucht Nord - Riba Roja',
            'description' => 'Uferbasiertes Nachtangeln auf Wels; Spots an Altarmen & Warmwassereinläufen.',
            'thumbnail_path' => 'https://images.unsplash.com/photo-1474843148229-3163319fcc00?q=80&w=1600&auto=format&fit=crop',
            'gallery_images' => [
                'https://images.unsplash.com/photo-1474843148229-3163319fcc00?q=80&w=1600&auto=format&fit=crop',
                'https://images.unsplash.com/photo-1469536526925-9b5547cd51f6?q=80&w=1600&auto=format&fit=crop',
                'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?q=80&w=1600&auto=format&fit=crop',
            ],
            'duration_hours' => 6,
            'max_persons' => 3,
            'type' => 'Ufer',
            'guiding_info' => [
                'art' => 'Ufer',
                'dauer' => '6 h',
                'max_personen' => 3,
                'gewaesser' => 'Stausee / Uferzonen'
            ],
            'target_fish' => ['Wels'],
            'methods' => ['U-Pose', 'Boje', 'Grundmontage'],
            'meeting_point' => 'Bucht Nord - Riba Roja',
            'start_times' => ['abends', 'nachts'],
            'inclusives' => ['Spottransfer', 'Signalhorn'],
            'price' => [
                'amount' => 260.00,
                'currency' => 'EUR',
                'type' => 'Preis pro Tour'
            ]
        ];
    }

    private function getSampleBoat()
    {
        return [
            'id' => 1,
            'title' => 'Pedal-Kajak 3,6 m',
            'type' => 'Kajak',
            'location' => 'Flachwasser-Bucht am Camp',
            'description' => 'Leises Kayak mit Pedalantrieb für stealthy Ufernähe und Krautfelder. Ideal zum Casting auf Schwarzbarsch.',
            'thumbnail_path' => 'https://images.unsplash.com/photo-1520440229-84f3865cf003?q=80&w=1600&auto=format&fit=crop',
            'gallery_images' => [
                'https://images.unsplash.com/photo-1520440229-84f3865cf003?q=80&w=1600&auto=format&fit=crop',
                'https://images.unsplash.com/photo-1505839673365-e3971f8d9184?q=80&w=1600&auto=format&fit=crop',
            ],
            'seats' => 1,
            'length_m' => 3.6,
            'width_m' => 0.9,
            'year_built' => 2022,
            'manufacturer' => 'Hobie',
            'engine' => '-',
            'power' => '-',
            'max_speed_kmh' => 8,
            'equipment' => ['Rutenhalter', 'Anker', 'Signalhorn', 'Erste Hilfe', 'Schwimmwesten', 'Ruder'],
            'requirements' => ['Führerschein nicht nötig', 'Mindestalter 14', 'Ausweis mitbringen', 'Kaution nein', 'Sicherheitsunterweisung', 'Schwimmwestenpflicht'],
            'inclusives' => ['Sicherheitsunterweisung', 'Anker', 'Signalhorn', 'Erste Hilfe Set', 'Schwimmweste'],
            'extras' => ['Dry Bag'],
            'price' => [
                'amount' => 25.00,
                'currency' => 'EUR',
                'type' => 'pro Tag'
            ]
        ];
    }
}
