<?php

return [
    'new_trips_rail_max_catalog' => 30,

    'new_camps_rail_max_catalog' => 30,

    'popular_listing_limit' => 6,

    'new_trips_rail_limit' => 6,

    'new_camps_rail_limit' => 6,

    'country_page_per_page' => 6,

    'pillar_index_per_page' => 9,

    'duration_filter_options' => [
        ['value' => '3+', 'label_key' => 'vacations.duration_filter_min_3'],
        ['value' => '7+', 'label_key' => 'vacations.duration_filter_min_7'],
        ['value' => '14+', 'label_key' => 'vacations.duration_filter_min_14'],
    ],

    'reserved_country_segments' => ['trips', 'camps'],

    'inspiration_tiles' => [
        [
            'title_key' => 'vacations.inspiration_family',
            'url' => '/vacations/camps',
            'query' => [],
        ],
        [
            'title_key' => 'vacations.inspiration_carp',
            'url' => '/vacations/trips',
            'query' => ['species' => 'carp'],
        ],
        [
            'title_key' => 'vacations.inspiration_luxury',
            'url' => '/vacations/camps',
            'query' => ['country' => 'sweden'],
        ],
    ],

    'hub_value_props' => [
        [
            'icon' => 'fa-calendar-check',
            'title_key' => 'vacations.hub_usp_instant_title',
            'text_key' => 'vacations.hub_usp_instant_text',
        ],
        [
            'icon' => 'fa-suitcase-rolling',
            'title_key' => 'vacations.hub_usp_trips_title',
            'text_key' => 'vacations.hub_usp_trips_text',
        ],
        [
            'icon' => 'fa-shield-alt',
            'title_key' => 'vacations.hub_usp_verified_title',
            'text_key' => 'vacations.hub_usp_verified_text',
        ],
        [
            'icon' => 'fa-globe-europe',
            'title_key' => 'vacations.hub_usp_europe_title',
            'text_key' => 'vacations.hub_usp_europe_text',
        ],
    ],

    'hub_faq' => [
        ['question_key' => 'vacations.faq_camp_vs_trip_q', 'answer_key' => 'vacations.faq_camp_vs_trip_a'],
        ['question_key' => 'vacations.faq_booking_q', 'answer_key' => 'vacations.faq_booking_a'],
        ['question_key' => 'vacations.faq_included_q', 'answer_key' => 'vacations.faq_included_a'],
        ['question_key' => 'vacations.faq_countries_q', 'answer_key' => 'vacations.faq_countries_a'],
    ],

    'trips_faq' => [
        ['question_key' => 'vacations.faq_booking_q', 'answer_key' => 'vacations.faq_booking_a'],
        ['question_key' => 'vacations.faq_included_q', 'answer_key' => 'vacations.faq_included_a'],
        ['question_key' => 'vacations.faq_camp_vs_trip_q', 'answer_key' => 'vacations.faq_camp_vs_trip_a'],
        ['question_key' => 'vacations.faq_countries_q', 'answer_key' => 'vacations.faq_countries_a'],
    ],

    'camps_faq' => [
        ['question_key' => 'vacations.faq_camp_vs_trip_q', 'answer_key' => 'vacations.faq_camp_vs_trip_a'],
        ['question_key' => 'vacations.faq_booking_q', 'answer_key' => 'vacations.faq_booking_a'],
        ['question_key' => 'vacations.faq_countries_q', 'answer_key' => 'vacations.faq_countries_a'],
    ],

    'trip_legal_seller' => env('VACATION_TRIP_LEGAL_SELLER', ''),
];
