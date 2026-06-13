<?php

return [
    'new_trips_rail_max_catalog' => 30,

    'popular_listing_limit' => 6,

    'new_trips_rail_limit' => 6,

    'country_page_per_page' => 6,

    'pillar_index_per_page' => 9,

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

    'hub_faq' => [
        ['question_key' => 'vacations.faq_camp_vs_trip_q', 'answer_key' => 'vacations.faq_camp_vs_trip_a'],
        ['question_key' => 'vacations.faq_booking_q', 'answer_key' => 'vacations.faq_booking_a'],
        ['question_key' => 'vacations.faq_included_q', 'answer_key' => 'vacations.faq_included_a'],
        ['question_key' => 'vacations.faq_countries_q', 'answer_key' => 'vacations.faq_countries_a'],
    ],

    'trip_legal_seller' => env('VACATION_TRIP_LEGAL_SELLER', ''),
];
