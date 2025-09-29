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
            'minute' => 120,  // Balanced for normal users
            'hour' => 1000,   // Balanced for normal users
            'day' => 5000     // Balanced for normal users
        ],
        'validate_input' => false,  // Disable aggressive input validation
        'block_threshold' => 30,    // Reasonable threshold for attacks
        'block_multiplier' => 15,   // Moderate block duration
        'max_block_duration' => 900, // 15 minutes max for normal violations
        'stubborn_threshold' => 100, // Higher threshold for persistent attacks
        'stubborn_base_duration' => 1800, // 30 minutes for stubborn attacks
        'stubborn_multiplier' => 2,
        'stubborn_max_duration' => 14400, // Max 4 hours for severe attacks
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
                'minute' => 80,   // Reasonable for search browsing
                'hour' => 800,    // Reasonable for search browsing
                'day' => 4000     // Reasonable for search browsing
            ],
            'validate_input' => false,  // Disable aggressive input validation
            'block_threshold' => 25,    // Moderate threshold for search attacks
            'block_multiplier' => 20,   // Moderate block duration
            'max_block_duration' => 600, // 10 minutes max for search violations
            'stubborn_threshold' => 80,  // Higher threshold for persistent search attacks
            'stubborn_base_duration' => 1200, // 20 minutes for stubborn search attacks
            'stubborn_multiplier' => 2,
            'stubborn_max_duration' => 7200, // Max 2 hours for severe search attacks
        ],

        'livewire' => [
            'limits' => [
                'minute' => 150, // Reasonable for livewire interactions
                'hour' => 1000,  // Reasonable for livewire interactions
                'day' => 5000    // Reasonable for livewire interactions
            ],
            'validate_input' => false,  // Disable aggressive input validation
            'block_threshold' => 40,    // Moderate threshold for livewire attacks
            'block_multiplier' => 15,   // Moderate block duration
            'max_block_duration' => 600, // 10 minutes max for livewire violations
            'stubborn_threshold' => 120, // Higher threshold for persistent livewire attacks
            'stubborn_base_duration' => 1800, // 30 minutes for stubborn livewire attacks
            'stubborn_multiplier' => 2,
            'stubborn_max_duration' => 10800, // Max 3 hours for severe livewire attacks
        ],

        'checkout' => [
            'limits' => [
                'minute' => 60,   // Moderate for booking users (critical path)
                'hour' => 300,    // Moderate for booking users
                'day' => 1000     // Moderate for booking users
            ],
            'validate_input' => false,  // Disable aggressive input validation
            'block_threshold' => 20,    // Lower threshold for checkout (more sensitive)
            'block_multiplier' => 30,   // Longer blocks for checkout attacks
            'max_block_duration' => 1800, // 30 minutes max for checkout violations
            'stubborn_threshold' => 60,  // Moderate threshold for persistent checkout attacks
            'stubborn_base_duration' => 3600, // 1 hour for stubborn checkout attacks
            'stubborn_multiplier' => 2,
            'stubborn_max_duration' => 21600, // Max 6 hours for severe checkout attacks
        ],

        'gemini' => [
            'limits' => [
                'minute' => 50,  // Higher limits for AI usage
                'hour' => 500,   // Higher limits for AI usage
                'day' => 2000    // Higher limits for AI usage
            ],
            'validate_input' => false,  // Disable input validation
            'block_threshold' => 50,    // Higher threshold
            'block_multiplier' => 10,   // Shorter blocks
            'max_block_duration' => 600, // Only 10 minutes max
            'stubborn_threshold' => 200, // Higher stubborn threshold
            'stubborn_base_duration' => 1200, // Only 20 minutes
            'stubborn_multiplier' => 2,
            'stubborn_max_duration' => 7200, // Max 2 hours
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
            '/<script[^>]*>.*?<\/script>/i', // Script tags
            '/javascript:/i', // JavaScript URLs
            '/on\w+\s*=/i', // Event handlers
            '/<iframe[^>]*>.*?<\/iframe>/i', // Iframe tags
            '/\b(admin|root|administrator)\b/i', // Admin-related searches
        ],
        
        'checkout' => [
            '/<script[^>]*>.*?<\/script>/i', // Script tags
            '/javascript:/i', // JavaScript URLs
            '/on\w+\s*=/i', // Event handlers
            '/<iframe[^>]*>.*?<\/iframe>/i', // Iframe tags
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
        'threat_score_threshold' => 70,  // Lower threshold for better detection
        'high_threat_threshold' => 85,   // Lower threshold for high threat detection
        'bot_detection' => true,         // Enable bot detection
        'suspicious_patterns' => [
            'rapid_requests' => true,    // Detect rapid request patterns
            'missing_headers' => true,   // Detect missing common headers
            'suspicious_user_agents' => true, // Detect suspicious user agents
            'geolocation_anomalies' => true,  // Detect unusual geographic patterns
        ],
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

    /*
    |--------------------------------------------------------------------------
    | Advanced Security Features
    |--------------------------------------------------------------------------
    |
    | Additional security measures for better attack detection and prevention.
    |
    */
    'advanced_security' => [
        'enabled' => false,                      // Enable advanced security features
        'enable_ip_whitelist' => false,
        'whitelisted_ips' => [
            // Add trusted IPs here if needed
        ],
        'enable_geolocation_blocking' => false,
        'blocked_countries' => [
            // Add country codes to block if needed
        ],
        'enable_behavioral_analysis' => true,
        'suspicious_behavior_patterns' => [
            'rapid_page_navigation' => true,    // Detect rapid page changes
            'missing_referrer' => true,         // Detect missing referrer headers
            'suspicious_request_sequences' => true, // Detect unusual request patterns
            'high_error_rate' => true,          // Detect high 404/error rates
        ],
        'adaptive_blocking' => true,            // Enable adaptive blocking based on threat level
        'emergency_mode' => false,              // Emergency mode for severe attacks
    ],
];
