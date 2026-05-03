<?php

return [

    'enabled' => env('BOOKING_ASSISTANT_ENABLED', false),

    'driver' => env('BOOKING_ASSISTANT_DRIVER', 'groq'),

    'max_output_tokens' => (int) env('BOOKING_ASSISTANT_MAX_OUTPUT_TOKENS', 512),

    'max_messages' => (int) env('BOOKING_ASSISTANT_MAX_MESSAGES', 16),

    'max_tool_iterations' => (int) env('BOOKING_ASSISTANT_MAX_TOOL_ITERATIONS', 3),

    'max_tool_rows' => (int) env('BOOKING_ASSISTANT_MAX_TOOL_ROWS', 8),

    'temperature' => (float) env('BOOKING_ASSISTANT_TEMPERATURE', 0.4),

    'http_timeout_seconds' => (int) env('BOOKING_ASSISTANT_HTTP_TIMEOUT', 45),

    'rate_limit' => [
        'per_minute' => (int) env('BOOKING_ASSISTANT_RATE_PER_MINUTE', 15),
    ],

    'providers' => [
        'groq' => [
            'api_key' => env('BOOKING_ASSISTANT_GROQ_API_KEY'),
            'base_url' => env('BOOKING_ASSISTANT_GROQ_BASE_URL', 'https://api.groq.com/openai/v1'),
            'model' => env('BOOKING_ASSISTANT_GROQ_MODEL', 'llama-3.1-8b-instant'),
        ],
    ],

];
