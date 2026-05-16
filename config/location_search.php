<?php

return [
    'scopes' => [
        'city' => [
            'radius_fallback_km' => (int) env('LOCATION_SEARCH_CITY_RADIUS_KM', 20),
            'max_results' => 100,
        ],
        'region' => [
            'radius_fallback_km' => (int) env('LOCATION_SEARCH_REGION_RADIUS_KM', 80),
            'max_results' => 150,
        ],
        'country' => [
            'radius_fallback_km' => (int) env('LOCATION_SEARCH_COUNTRY_RADIUS_KM', 120),
            'max_results' => 200,
        ],
    ],

    'nearest_fallback_limit' => 50,

    /*
    | When the user picks a place with a known country (country_short / country name),
    | only guidings in that country are returned. Prevents cross-border false positives
    | inside rectangular region/country bounding boxes (e.g. NRW viewport vs Limburg, NL).
    */
    'enforce_search_country' => (bool) env('LOCATION_SEARCH_ENFORCE_COUNTRY', true),

    'cache_ttl_seconds' => 3600,

    /*
    | Google Places REST (getLocationDetails) — off for /guidings search; artisan only.
    */
    'google_rest_fallback' => (bool) env('LOCATION_RESOLVER_GOOGLE_FALLBACK', false),

    'locales_for_country_resolve' => ['en', 'de', 'es', 'fr', 'it', 'nl', 'sv'],
];
