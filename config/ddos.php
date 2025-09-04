<?php

return [
    /*
    |--------------------------------------------------------------------------
    | DDoS Protection Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains all the configuration settings for the DDoS protection
    | system. You can customize rate limits, blocking thresholds, and other
    | security parameters for different contexts.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Default Configuration
    |--------------------------------------------------------------------------
    |
    | These are the default settings that will be used if no specific context
    | configuration is found.
    |
    */
    'defaults' => [
        'limits' => [
            'minute' => 60,
            'hour' => 1000,
            'day' => 5000
        ],
        'validate_input' => false,
        'block_threshold' => 20,
        'block_multiplier' => 30,
        'max_block_duration' => 1800, // 30 minutes
        'stubborn_threshold' => 100,
        'stubborn_base_duration' => 1800, // 30 minutes
        'stubborn_multiplier' => 2,
        'stubborn_max_duration' => 43200, // 12 hours
    ],

    /*
    |--------------------------------------------------------------------------
    | Context-Specific Configurations
    |--------------------------------------------------------------------------
    |
    | Different parts of your application may need different protection levels.
    | Configure each context with appropriate rate limits and blocking rules.
    |
    */
    'contexts' => [
        'search' => [
            'limits' => [
                'minute' => 20,
                'hour' => 200,
                'day' => 1000
            ],
            'validate_input' => true,
            'block_threshold' => 5,
            'block_multiplier' => 60,
            'max_block_duration' => 3600, // 1 hour
            'stubborn_threshold' => 30,
            'stubborn_base_duration' => 3600, // 1 hour
            'stubborn_multiplier' => 2,
            'stubborn_max_duration' => 86400, // 24 hours
        ],

        'livewire' => [
            'limits' => [
                'minute' => 30,
                'hour' => 300,
                'day' => 1500
            ],
            'validate_input' => true,
            'block_threshold' => 10,
            'block_multiplier' => 30,
            'max_block_duration' => 1800, // 30 minutes
            'stubborn_threshold' => 50,
            'stubborn_base_duration' => 1800, // 30 minutes
            'stubborn_multiplier' => 2,
            'stubborn_max_duration' => 43200, // 12 hours
        ],

        'checkout' => [
            'limits' => [
                'minute' => 10,
                'hour' => 50,
                'day' => 200
            ],
            'validate_input' => true,
            'block_threshold' => 5,
            'block_multiplier' => 60,
            'max_block_duration' => 3600, // 1 hour
            'stubborn_threshold' => 20,
            'stubborn_base_duration' => 7200, // 2 hours
            'stubborn_multiplier' => 3,
            'stubborn_max_duration' => 86400, // 24 hours
        ],

        'gemini' => [
            'limits' => [
                'minute' => 10,
                'hour' => 100,
                'day' => 500
            ],
            'validate_input' => true,
            'block_threshold' => 10,
            'block_multiplier' => 30,
            'max_block_duration' => 1800, // 30 minutes
            'stubborn_threshold' => 25,
            'stubborn_base_duration' => 3600, // 1 hour
            'stubborn_multiplier' => 2,
            'stubborn_max_duration' => 86400, // 24 hours
        ],

        'general' => [
            'limits' => [
                'minute' => 60,
                'hour' => 1000,
                'day' => 5000
            ],
            'validate_input' => false,
            'block_threshold' => 20,
            'block_multiplier' => 30,
            'max_block_duration' => 1800, // 30 minutes
            'stubborn_threshold' => 100,
            'stubborn_base_duration' => 1800, // 30 minutes
            'stubborn_multiplier' => 2,
            'stubborn_max_duration' => 43200, // 12 hours
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Input Validation Patterns
    |--------------------------------------------------------------------------
    |
    | Define suspicious input patterns that should trigger blocking.
    | These are regex patterns that will be checked against user input.
    |
    */
    'input_patterns' => [
        // SQL Injection patterns
        '/(\b(SELECT|INSERT|UPDATE|DELETE|DROP|CREATE|ALTER|EXEC|UNION|SCRIPT)\b)/i',
        '/(\b(OR|AND)\s+\d+\s*=\s*\d+)/i',
        '/(\b(OR|AND)\s+[\'"]?\w+[\'"]?\s*=\s*[\'"]?\w+[\'"]?)/i',
        
        // XSS patterns
        '/<script[^>]*>.*?<\/script>/i',
        '/javascript:/i',
        '/on\w+\s*=/i',
        '/<iframe[^>]*>.*?<\/iframe>/i',
        
        // Path traversal
        '/\.\.\//',
        '/\.\.\\\\/',
        
        // Command injection
        '/[;&|`$()]/',
        '/\b(cat|ls|pwd|whoami|id|uname|ps|netstat|ifconfig)\b/i',
        
        // LDAP injection
        '/[()=*!&|]/',
        
        // NoSQL injection
        '/\$where/i',
        '/\$ne/i',
        '/\$gt/i',
        '/\$lt/i',
        '/\$regex/i',
    ],

    /*
    |--------------------------------------------------------------------------
    | Context-Specific Input Patterns
    |--------------------------------------------------------------------------
    |
    | Additional input patterns specific to certain contexts.
    |
    */
    'context_input_patterns' => [
        'livewire' => [
            '/wire:model/i',
            '/@this\./i',
            '/wire:click/i',
            '/wire:submit/i',
        ],
        
        'search' => [
            '/[<>"\']/', // Basic XSS prevention
            '/\b(admin|root|administrator)\b/i', // Admin-related searches
        ],
        
        'checkout' => [
            '/[<>"\']/', // Basic XSS prevention
            '/\b(admin|root|administrator)\b/i', // Admin-related data
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Threat Intelligence Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for threat intelligence collection and analysis.
    |
    */
    'threat_intelligence' => [
        'enabled' => true,
        'collect_user_agent' => true,
        'collect_headers' => true,
        'collect_request_data' => true,
        'threat_score_threshold' => 80,
        'high_threat_threshold' => 90,
    ],

    /*
    |--------------------------------------------------------------------------
    | Honeypot Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for honeypot traps to catch automated attacks.
    |
    */
    'honeypots' => [
        'enabled' => true,
        'hidden_fields' => [
            'website',
            'url',
            'homepage',
            'phone',
            'fax',
        ],
        'time_trap_threshold' => 2, // seconds
        'js_challenge_enabled' => true,
        'css_trap_enabled' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for DDoS attack notifications.
    |
    */
    'notifications' => [
        'enabled' => true,
        'admin_email' => env('MAIL_ADMIN_EMAIL', env('MAIL_FROM_ADDRESS')),
        'cooldown_minutes' => 15,
        'send_on_rate_limit' => true,
        'send_on_block' => true,
        'send_on_stubborn_attacker' => true,
        'send_on_high_threat' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for DDoS attack logging.
    |
    */
    'logging' => [
        'enabled' => true,
        'log_channel' => 'ddos_attacks',
        'log_alerts' => 'ddos_alerts',
        'log_gemini_usage' => 'gemini_usage',
        'log_level' => 'info',
        'log_retention_days' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for caching DDoS protection data.
    |
    */
    'cache' => [
        'driver' => env('CACHE_DRIVER', 'file'),
        'prefix' => 'ddos_protection_',
        'violation_ttl' => 86400, // 24 hours
        'block_ttl' => 86400, // 24 hours
        'threat_data_ttl' => 3600, // 1 hour
    ],

    /*
    |--------------------------------------------------------------------------
    | Response Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for blocked request responses.
    |
    */
    'responses' => [
        'rate_limit_exceeded' => [
            'status' => 429,
            'message' => 'Rate limit exceeded. Please try again later.',
        ],
        'already_blocked' => [
            'status' => 429,
            'message' => 'Too many requests. Please try again later.',
        ],
        'suspicious_input' => [
            'status' => 400,
            'message' => 'Invalid request parameters provided.',
        ],
        'high_threat' => [
            'status' => 403,
            'message' => 'Request blocked due to security concerns.',
        ],
        'honeypot_triggered' => [
            'status' => 400,
            'message' => 'Invalid request detected.',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Advanced Settings
    |--------------------------------------------------------------------------
    |
    | Advanced configuration options for fine-tuning the protection system.
    |
    */
    'advanced' => [
        'enable_circuit_breaker' => false,
        'circuit_breaker_threshold' => 10,
        'circuit_breaker_timeout' => 300, // 5 minutes
        'enable_geolocation_blocking' => false,
        'blocked_countries' => [],
        'enable_ip_whitelist' => false,
        'whitelisted_ips' => [],
        'enable_ip_blacklist' => false,
        'blacklisted_ips' => [],
        'enable_user_agent_blocking' => false,
        'blocked_user_agents' => [
            'curl',
            'wget',
            'python-requests',
            'bot',
            'spider',
            'crawler',
        ],
    ],
];
