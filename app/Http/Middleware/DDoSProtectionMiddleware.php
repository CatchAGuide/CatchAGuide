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
        // Get base configuration from config file
        $defaults = config('ddos.defaults', []);
        $contextConfigs = config('ddos.contexts', []);
        
        // Get context-specific config or fall back to defaults
        $config = $contextConfigs[$context] ?? $defaults;
        
        // Add context identifier
        $config['context'] = $context;
        
        // Add global input patterns
        $config['input_patterns'] = config('ddos.input_patterns', []);
        
        // Add context-specific input patterns
        $contextPatterns = config("ddos.context_input_patterns.{$context}", []);
        if (!empty($contextPatterns)) {
            $config['input_patterns'] = array_merge($config['input_patterns'], $contextPatterns);
        }
        
        return $config;
    }

    /**
     * Create appropriate response for blocked request
     */
    private function createBlockedResponse(array $result): Response
    {
        $reason = $result['reason'] ?? 'default';
        $responseConfigs = config('ddos.responses', []);
        
        // Get response configuration for this reason
        $responseConfig = $responseConfigs[$reason] ?? $responseConfigs['default'] ?? [
            'status' => 429,
            'message' => 'Request blocked.'
        ];

        $response = [
            'error' => $responseConfig['message']
        ];

        if (isset($result['retry_after']) && $result['retry_after'] > 0) {
            $response['retry_after'] = $result['retry_after'];
        }

        return response()->json($response, $responseConfig['status']);
    }
}
