<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\DDoSProtectionService;
use Symfony\Component\HttpFoundation\Response;

class DDoSProtectionMiddleware
{
    private DDoSProtectionService $protectionService;
    
    public function __construct(DDoSProtectionService $protectionService)
    {
        $this->protectionService = $protectionService;
    }

    public function handle(Request $request, Closure $next, string $context = 'general'): Response
    {
        // Get configuration for this context
        $config = $this->getContextConfig($context, $request);
        
        // Check if request should be blocked
        $result = $this->protectionService->shouldBlockRequest($request, $config);
        
        if ($result['blocked']) {
            return $this->createBlockedResponse($result);
        }

        return $next($request);
    }

    /**
     * Get configuration for specific context
     */
    private function getContextConfig(string $context, Request $request): array
    {
        $configs = [
            'search' => [
                'context' => 'search',
                'limits' => [
                    'minute' => 20,
                    'hour' => 200,
                    'day' => 1000
                ],
                'validate_input' => true,
                'block_threshold' => 5,
                'block_multiplier' => 60,
                'max_block_duration' => 3600
            ],
            
            'livewire' => [
                'context' => 'livewire',
                'limits' => [
                    'minute' => 30,
                    'hour' => 300,
                    'day' => 1500
                ],
                'validate_input' => true,
                'block_threshold' => 10,
                'block_multiplier' => 30,
                'max_block_duration' => 1800
            ],
            
            'checkout' => [
                'context' => 'checkout',
                'limits' => [
                    'minute' => 10,
                    'hour' => 50,
                    'day' => 200
                ],
                'validate_input' => true,
                'block_threshold' => 5,
                'block_multiplier' => 60,
                'max_block_duration' => 3600
            ],
            
            'gemini' => [
                'context' => 'gemini',
                'limits' => [
                    'minute' => 10,
                    'hour' => 100,
                    'day' => 500
                ],
                'validate_input' => true,
                'block_threshold' => 10,
                'block_multiplier' => 30,
                'max_block_duration' => 1800
            ],
            
            'general' => [
                'context' => 'general',
                'limits' => [
                    'minute' => 60,
                    'hour' => 1000,
                    'day' => 5000
                ],
                'validate_input' => false,
                'block_threshold' => 20,
                'block_multiplier' => 30,
                'max_block_duration' => 1800
            ]
        ];

        $config = $configs[$context] ?? $configs['general'];
        
        // Add context-specific input patterns if needed
        if ($context === 'livewire') {
            $config['input_patterns'] = [
                // Additional patterns for Livewire-specific attacks
                '/wire:model/i',
                '/@this\./i'
            ];
        }
        
        return $config;
    }

    /**
     * Create appropriate response for blocked request
     */
    private function createBlockedResponse(array $result): Response
    {
        $statusCode = match($result['reason']) {
            'already_blocked' => 429,
            'rate_limit_exceeded' => 429,
            'suspicious_input' => 400,
            default => 429
        };

        $message = match($result['reason']) {
            'already_blocked' => 'Too many requests. Please try again later.',
            'rate_limit_exceeded' => 'Rate limit exceeded. Please try again later.',
            'suspicious_input' => 'Invalid request parameters provided.',
            default => 'Request blocked.'
        };

        $response = [
            'error' => $message
        ];

        if (isset($result['retry_after']) && $result['retry_after'] > 0) {
            $response['retry_after'] = $result['retry_after'];
        }

        return response()->json($response, $statusCode);
    }
}
