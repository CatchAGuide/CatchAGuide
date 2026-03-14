<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Trips (All-Inclusive) Field Definitions
    |--------------------------------------------------------------------------
    |
    | This configuration is the single source of truth for the Trips admin
    | form. It is derived from the AllInclusive_FormFields.csv file and
    | intentionally mirrors the Camp/Accommodation patterns.
    |
    | Each field entry defines:
    | - key:        internal form/model key
    | - label:      human readable label (fallback, UI should prefer lang file)
    | - type:       text, textarea, number, tagify, radio, image, repeater,
    |               json, richtext, toggle_text, date, availability_table
    | - step:       wizard step (1–8)
    | - section:    logical section within the step
    | - required:   boolean for full publish validation
    | - draft_only: if true, only validated for drafts (rare)
    | - options:    for radios or constrained inputs
    |
    */

    'fields' => [
        // Page 1 — Images & Basics
        'gallery_images' => [
            'label'    => 'Images (Hero + Gallery)',
            'type'     => 'image_multi',
            'step'     => 1,
            'section'  => 'images',
            'required' => true,
        ],
        'title' => [
            'label'    => 'Title',
            'type'     => 'text',
            'step'     => 1,
            'section'  => 'basics',
            'required' => true,
        ],
        'location' => [
            'label'    => 'Location',
            'type'     => 'location',
            'step'     => 1,
            'section'  => 'basics',
            'required' => true,
        ],

        // Page 2 — Fishing Details
        'target_species' => [
            'label'    => 'Target Species',
            'type'     => 'tagify',
            'step'     => 2,
            'section'  => 'fishing_details',
            'required' => true,
        ],
        'fishing_methods' => [
            'label'    => 'Fishing Methods',
            'type'     => 'tagify',
            'step'     => 2,
            'section'  => 'fishing_details',
            'required' => true,
        ],
        'fishing_style' => [
            'label'    => 'Fishing Style',
            'type'     => 'radio',
            'step'     => 2,
            'section'  => 'fishing_details',
            'required' => true,
            'options'  => ['active', 'passive', 'both'],
        ],
        'water_types' => [
            'label'    => 'Water Types',
            'type'     => 'tagify',
            'step'     => 2,
            'section'  => 'fishing_details',
            'required' => true,
        ],
        'skill_level' => [
            'label'    => 'Skill Level',
            'type'     => 'radio',
            'step'     => 2,
            'section'  => 'fishing_details',
            'required' => true,
            'options'  => ['beginner', 'intermediate', 'advanced', 'all_levels'],
        ],

        // Page 2 — General Details
        'duration_nights' => [
            'label'    => 'Total Trip Duration (Nights)',
            'type'     => 'number',
            'step'     => 2,
            'section'  => 'general_details',
            'required' => true,
        ],
        'duration_days' => [
            'label'    => 'Total Trip Duration (Days)',
            'type'     => 'number',
            'step'     => 2,
            'section'  => 'general_details',
            'required' => true,
        ],
        'group_size_min' => [
            'label'    => 'Group Size (Min)',
            'type'     => 'number',
            'step'     => 2,
            'section'  => 'general_details',
            'required' => false,
        ],
        'group_size_max' => [
            'label'    => 'Group Size (Max)',
            'type'     => 'number',
            'step'     => 2,
            'section'  => 'general_details',
            'required' => true,
        ],
        'trip_schedule' => [
            'label'    => 'Trip Schedule',
            'type'     => 'repeater',
            'step'     => 2,
            'section'  => 'general_details',
            'required' => true,
        ],
        'meeting_point' => [
            'label'    => 'Meeting Point / Check-in Info',
            'type'     => 'textarea',
            'step'     => 2,
            'section'  => 'general_details',
            'required' => true,
        ],
        'best_season_from' => [
            'label'    => 'Best Season From',
            'type'     => 'month',
            'step'     => 2,
            'section'  => 'general_details',
            'required' => false,
        ],
        'best_season_to' => [
            'label'    => 'Best Season To',
            'type'     => 'month',
            'step'     => 2,
            'section'  => 'general_details',
            'required' => false,
        ],
        'catering' => [
            'label'    => 'Catering',
            'type'     => 'tagify',
            'step'     => 2,
            'section'  => 'general_details',
            'required' => true,
        ],
        'best_arrival_options' => [
            'label'    => 'Best Arrival Options',
            'type'     => 'text',
            'step'     => 2,
            'section'  => 'general_details',
            'required' => false,
        ],
        'arrival_day' => [
            'label'    => 'Arrival Day',
            'type'     => 'text',
            'step'     => 2,
            'section'  => 'general_details',
            'required' => false,
        ],

        // Page 3 — Boat Information
        'boat_type' => [
            'label'    => 'Boat Type',
            'type'     => 'text',
            'step'     => 3,
            'section'  => 'boat',
            'required' => false,
        ],
        'boat_features' => [
            'label'    => 'Included Boat Features',
            'type'     => 'tagify',
            'step'     => 3,
            'section'  => 'boat',
            'required' => false,
        ],
        'boat_information' => [
            'label'    => 'Boat Information',
            'type'     => 'textarea',
            'step'     => 3,
            'section'  => 'boat',
            'required' => false,
        ],

        // Page 3 — Accommodation & Logistics
        'accommodation_description' => [
            'label'    => 'Accommodation Description',
            'type'     => 'textarea',
            'step'     => 3,
            'section'  => 'accommodation',
            'required' => true,
        ],
        'accommodation_type' => [
            'label'    => 'Accommodation Type',
            'type'     => 'radio',
            'step'     => 3,
            'section'  => 'accommodation',
            'required' => true,
        ],
        'room_types' => [
            'label'    => 'Room Types Available',
            'type'     => 'tagify',
            'step'     => 3,
            'section'  => 'accommodation',
            'required' => true,
        ],
        'distance_to_water' => [
            'label'    => 'Distance to Water',
            'type'     => 'text',
            'step'     => 3,
            'section'  => 'accommodation',
            'required' => false,
        ],
        'nearest_airport' => [
            'label'    => 'Nearest Airport',
            'type'     => 'text',
            'step'     => 3,
            'section'  => 'accommodation',
            'required' => false,
        ],

        // Page 3 — Provider
        'provider_name' => [
            'label'    => 'Provider Name',
            'type'     => 'text',
            'step'     => 3,
            'section'  => 'provider',
            'required' => true,
        ],
        'provider_photo' => [
            'label'    => 'Provider Photo',
            'type'     => 'image_single',
            'step'     => 3,
            'section'  => 'provider',
            'required' => false,
        ],
        'provider_experience' => [
            'label'    => 'Provider Experience',
            'type'     => 'textarea',
            'step'     => 3,
            'section'  => 'provider',
            'required' => false,
        ],
        'provider_certifications' => [
            'label'    => 'Provider Certifications & Licences',
            'type'     => 'textarea',
            'step'     => 3,
            'section'  => 'provider',
            'required' => false,
        ],
        'boat_staff' => [
            'label'    => 'Boat Staff',
            'type'     => 'text',
            'step'     => 3,
            'section'  => 'provider',
            'required' => false,
        ],
        'guide_languages' => [
            'label'    => 'Guide Languages',
            'type'     => 'tagify',
            'step'     => 3,
            'section'  => 'provider',
            'required' => false,
        ],

        // Page 4 — Description & Itinerary
        'description' => [
            'label'    => 'Full Description',
            'type'     => 'richtext',
            'step'     => 4,
            'section'  => 'description',
            'required' => true,
        ],
        'trip_highlights' => [
            'label'    => 'Trip Highlights',
            'type'     => 'json',
            'step'     => 4,
            'section'  => 'description',
            'required' => true,
        ],

        // Page 5 — Included & Excluded
        'included' => [
            'label'    => 'Included',
            'type'     => 'tagify',
            'step'     => 5,
            'section'  => 'included_excluded',
            'required' => false,
        ],
        'excluded' => [
            'label'    => 'Excluded',
            'type'     => 'tagify',
            'step'     => 5,
            'section'  => 'included_excluded',
            'required' => false,
        ],

        // Page 6 — Additional Information (toggle + text)
        'additional_info' => [
            'label'    => 'Additional Information Toggles',
            'type'     => 'json',
            'step'     => 6,
            'section'  => 'additional',
            'required' => false,
        ],

        // Page 7 — Pricing
        'cancellation_policy' => [
            'label'    => 'Cancellation Policy',
            'type'     => 'textarea',
            'step'     => 7,
            'section'  => 'pricing',
            'required' => false,
        ],
        'price_per_person' => [
            'label'    => 'Price per Person (Double Occupancy)',
            'type'     => 'number',
            'step'     => 7,
            'section'  => 'pricing',
            'required' => true,
        ],
        'price_single_room_addition' => [
            'label'    => 'Single Room Addition Price',
            'type'     => 'number',
            'step'     => 7,
            'section'  => 'pricing',
            'required' => false,
        ],
        'downpayment_policy' => [
            'label'    => 'Downpayment Policy',
            'type'     => 'textarea',
            'step'     => 7,
            'section'  => 'pricing',
            'required' => false,
        ],
        'currency' => [
            'label'    => 'Currency',
            'type'     => 'text',
            'step'     => 7,
            'section'  => 'pricing',
            'required' => false,
        ],

        // Page 8 — Availability & Dates
        'availability' => [
            'label'    => 'Availability & Dates',
            'type'     => 'availability_table',
            'step'     => 8,
            'section'  => 'availability',
            'required' => false,
        ],
    ],

    'included_whitelist' => [
        'Transfer Included',
        'Guided Fishing',
        'Boat & Fuel',
        'Fishing License / Permits',
        'Fishing Equipment & Tackle',
        'Food & Drinks',
        'Non-alcoholic Drinks',
        'Airport / Local Transfer',
        'Fish Processing / Packaging',
        'Welcome / Farewell Dinner',
        'Custom Included Item',
    ],

    'excluded_whitelist' => [
        'Flights / Travel to Destination',
        'Gratuities',
        'Travel Insurance',
        'Alcoholic Beverages',
        'Personal Expenses',
        'Custom Excluded Item',
        'Rental Gear Available',
        'Fish Cleaning & Packaging',
    ],

    // Availability status options
    'availability_status_options' => [
        'available',
        'limited',
        'sold_out',
    ],
];

