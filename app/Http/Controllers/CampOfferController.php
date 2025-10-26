<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CampOfferController extends Controller
{
    public function show()
    {
        $camp = $this->getSampleCamp();
        $accommodation = $this->getSampleAccommodation();
        $guiding = $this->getSampleGuiding();
        $boat = $this->getSampleBoat();

        // Process gallery images
        $campHero = $camp['thumbnail_path'] ?? ($camp['manual_gallery_images'][0] ?? 'https://images.unsplash.com/photo-1512914890250-33530e2f0d72?q=80&w=1600&auto=format&fit=crop');
        $galleryImages = array_values(array_filter(array_merge(
            [$camp['thumbnail_path'] ?? null],
            $camp['manual_gallery_images'] ?? []
        )));
        
        if (empty($galleryImages)) {
            $galleryImages = [$campHero];
        }
        
        $primaryImage = $galleryImages[0];
        $topRightImages = array_slice($galleryImages, 1, 2);
        while (count($topRightImages) < 2) {
            $topRightImages[] = $primaryImage;
        }
        
        $bottomStripImages = array_slice($galleryImages, 3, 5);
        $fallbackIndex = 0;
        while (count($bottomStripImages) < 5) {
            $bottomStripImages[] = $galleryImages[$fallbackIndex % count($galleryImages)];
            $fallbackIndex++;
            if ($fallbackIndex > 20) break;
        }
        $bottomStripImages = array_slice($bottomStripImages, 0, 5);
        $remainingGalleryCount = max(0, count($galleryImages) - 8);
        
        // For configurator dropdown options
        $accommodations = [$accommodation];
        $boats = [$this->getBoatForDropdown()];
        $guidings = [$this->getGuidingForDropdown()];
        $showCategories = true;

        return view('pages.vacations.v2', compact(
            'camp',
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
        ));
    }
    
    private function getBoatForDropdown()
    {
        return [
            'id' => 1,
            'title' => 'Aluminum 4.5m | 15 HP',
            'seats' => 3,
            'sonar_gps' => true,
            'price_per_day' => 65,
            'currency' => 'EUR',
            'img' => 'https://images.unsplash.com/photo-1520440229-84f3865cf003?q=80&w=1600&auto=format&fit=crop',
        ];
    }
    
    private function getGuidingForDropdown()
    {
        return [
            'id' => 1,
            'title' => 'Half-day Guiding (4h)',
            'group_size' => 2,
            'price' => 180,
            'currency' => 'EUR',
            'img' => 'https://images.unsplash.com/photo-1474843148229-3163319fcc00?q=80&w=1600&auto=format&fit=crop',
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
