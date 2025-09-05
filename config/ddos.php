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
                'minute' => 60,  // Increased from 20 to 60
                'hour' => 500,   // Increased from 200 to 500
                'day' => 2000    // Increased from 1000 to 2000
            ],
            'validate_input' => true,
            'block_threshold' => 15,     // Increased from 5 to 15
            'block_multiplier' => 30,    // Reduced from 60 to 30
            'max_block_duration' => 1800, // Reduced from 3600 to 1800 (30 min)
            'stubborn_threshold' => 50,  // Increased from 30 to 50
            'stubborn_base_duration' => 1800, // Reduced from 3600 to 1800
            'stubborn_multiplier' => 2,
            'stubborn_max_duration' => 43200, // Reduced from 86400 to 43200 (12 hours)
        ],

        'livewire' => [
            'limits' => [
                'minute' => 100, // Increased from 30 to 100
                'hour' => 800,   // Increased from 300 to 800
                'day' => 3000    // Increased from 1500 to 3000
            ],
            'validate_input' => true,
            'block_threshold' => 25,     // Increased from 10 to 25
            'block_multiplier' => 20,    // Reduced from 30 to 20
            'max_block_duration' => 900, // Reduced from 1800 to 900 (15 min)
            'stubborn_threshold' => 75,  // Increased from 50 to 75
            'stubborn_base_duration' => 1800, // 30 minutes
            'stubborn_multiplier' => 2,
            'stubborn_max_duration' => 21600, // Reduced from 43200 to 21600 (6 hours)
        ],

        'checkout' => [
            'limits' => [
                'minute' => 30,  // Increased from 10 to 30 (CRITICAL for booking users!)
                'hour' => 150,   // Increased from 50 to 150
                'day' => 500     // Increased from 200 to 500
            ],
            'validate_input' => true,
            'block_threshold' => 15,     // Increased from 5 to 15
            'block_multiplier' => 30,    // Reduced from 60 to 30
            'max_block_duration' => 1800, // Reduced from 3600 to 1800 (30 min)
            'stubborn_threshold' => 40,  // Increased from 20 to 40
            'stubborn_base_duration' => 3600, // Reduced from 7200 to 3600 (1 hour)
            'stubborn_multiplier' => 2,  // Reduced from 3 to 2
            'stubborn_max_duration' => 43200, // Reduced from 86400 to 43200 (12 hours)
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
        // Legitimate user agents that should get reduced scoring
        'legitimate_user_agents' => [
            'Mozilla',
            'Safari',
            'Chrome',
            'Firefox',
            'Edge',
            'Opera',
            'iPhone',
            'iPad',
            'Android',
            'Mobile',
        ],
        // Reduce alert frequency to prevent spam
        'alert_cooldown_minutes' => 60, // Send max 1 alert per hour per IP
        'threat_score_blocking_threshold' => 95, // Only block very high threat scores
    ],
];

