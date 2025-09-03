<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Gemini API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Google Gemini API integration with security settings
    |
    */

    'api_key' => env('GOOGLE_GEMINI_API_KEY'),
    'model' => env('GOOGLE_GEMINI_MODEL', 'gemini-pro'),
    'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta/models'),

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Configuration
    |--------------------------------------------------------------------------
    |
    | Configure rate limits for Gemini API calls to prevent abuse
    |
    */

    'rate_limits' => [
        'per_minute' => env('GEMINI_RATE_LIMIT_PER_MINUTE', 10),
        'per_hour' => env('GEMINI_RATE_LIMIT_PER_HOUR', 100),
        'per_day' => env('GEMINI_RATE_LIMIT_PER_DAY', 500),
    ],

    /*
    |--------------------------------------------------------------------------
    | Input Validation Configuration
    |--------------------------------------------------------------------------
    |
    | Configure input validation rules for translation requests
    |
    */

    'validation' => [
        'max_input_length' => env('GEMINI_MAX_INPUT_LENGTH', 500),
        'min_input_length' => env('GEMINI_MIN_INPUT_LENGTH', 2),
    ],

    /*
    |--------------------------------------------------------------------------
    | Circuit Breaker Configuration
    |--------------------------------------------------------------------------
    |
    | Configure circuit breaker settings for handling API failures
    |
    */

    'circuit_breaker' => [
        'max_failures' => env('GEMINI_MAX_FAILURES', 5),
        'open_duration' => env('GEMINI_CIRCUIT_OPEN_DURATION', 300), // seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Caching Configuration
    |--------------------------------------------------------------------------
    |
    | Configure caching settings for translations
    |
    */

    'cache' => [
        'ttl' => env('GEMINI_CACHE_TTL', 86400), // 24 hours in seconds
        'prefix' => env('GEMINI_CACHE_PREFIX', 'gemini_translation_'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Request Queue Configuration
    |--------------------------------------------------------------------------
    |
    | Configure request queuing for handling burst requests
    |
    */

    'queue' => [
        'enabled' => env('GEMINI_QUEUE_ENABLED', true),
        'max_size' => env('GEMINI_QUEUE_MAX_SIZE', 100),
        'batch_size' => env('GEMINI_QUEUE_BATCH_SIZE', 5),
        'process_interval' => env('GEMINI_QUEUE_PROCESS_INTERVAL', 10), // seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitoring Configuration
    |--------------------------------------------------------------------------
    |
    | Configure monitoring and alerting settings
    |
    */

    'monitoring' => [
        'daily_usage_threshold' => env('GEMINI_DAILY_USAGE_THRESHOLD', 1000),
        'log_channel' => env('GEMINI_LOG_CHANNEL', 'gemini_usage'),
        'enable_cost_tracking' => env('GEMINI_ENABLE_COST_TRACKING', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | Configure security settings for preventing abuse
    |
    */

    'security' => [
        'enable_ip_blocking' => env('GEMINI_ENABLE_IP_BLOCKING', true),
        'max_violations_before_block' => env('GEMINI_MAX_VIOLATIONS_BEFORE_BLOCK', 3),
        'block_duration' => env('GEMINI_BLOCK_DURATION', 3600), // seconds
        'enable_suspicious_pattern_detection' => env('GEMINI_ENABLE_PATTERN_DETECTION', true),
    ],
];

