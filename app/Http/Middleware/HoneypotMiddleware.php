<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\HoneypotService;
use Symfony\Component\HttpFoundation\Response;

class HoneypotMiddleware
{
    private HoneypotService $honeypotService;
    
    public function __construct(HoneypotService $honeypotService)
    {
        $this->honeypotService = $honeypotService;
    }

    public function handle(Request $request, Closure $next): Response
    {
        // Check for honeypot triggers on POST requests
        if ($request->isMethod('POST')) {
            $triggers = $this->honeypotService->checkHoneypotTriggers($request);
            
            if (!empty($triggers)) {
                // Log the honeypot trigger
                \Log::channel('ddos_attacks')->warning('HONEYPOT TRIGGERED', [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'triggers' => $triggers,
                    'url' => $request->fullUrl(),
                    'timestamp' => now()->toISOString()
                ]);
                
                // Return a fake success response to confuse bots
                return response()->json([
                    'success' => true,
                    'message' => 'Request processed successfully',
                    'redirect' => '/'
                ], 200);
            }
        }
        
        $response = $next($request);
        
        // Add honeypot fields to HTML responses
        if ($response->headers->get('content-type') && 
            str_contains($response->headers->get('content-type'), 'text/html')) {
            
            $content = $response->getContent();
            $honeypotHtml = $this->honeypotService->generateHoneypotHtml();
            
            // Inject honeypot fields before closing form tags
            $content = preg_replace(
                '/(<\/form>)/i',
                $honeypotHtml . '$1',
                $content
            );
            
            $response->setContent($content);
        }
        
        return $response;
    }
}
