<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'gemini' => [
        'key' => env('GOOGLE_GEMINI_API_KEY'),
        'model' => env('GOOGLE_GEMINI_MODEL', 'gemini-pro'),
        'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta/models'),
    ],

    'google_maps' => [
        'api_key' => env('GOOGLE_MAPS_API_KEY'),
        'map_id' => env('GOOGLE_MAPS_MAP_ID', 'DEMO_MAP_ID'),
    ],

    /*
    | Leaflet / free tile maps (product + listing UI). Places Autocomplete still uses google_maps.
    */
    'maps' => [
        'engine' => env('MAPS_ENGINE', 'leaflet'),
        'tile_url' => env('MAPS_TILE_URL', 'https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png'),
        'attribution' => env('MAPS_ATTRIBUTION', '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> &copy; <a href="https://carto.com/attributions">CARTO</a>'),
        'default_center' => [
            'lat' => (float) env('MAPS_DEFAULT_LAT', 51.165691),
            'lng' => (float) env('MAPS_DEFAULT_LNG', 10.451526),
        ],
        'default_zoom' => (int) env('MAPS_DEFAULT_ZOOM', 5),
    ],

    'translation' => [
        'driver' => env('TRANSLATION_SERVICE', 'gemini'), // Options: 'gemini' or 'google'
    ],

    'stripe' => [
        'key' => env('STRIPE_KEY', ''),
    ],

];
