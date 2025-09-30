<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CleanUrlParameters
{
    /**
     * Handle an incoming request and clean malformed URL parameters.
     * 
     * This middleware fixes HTML-encoded parameter names like 'amp;placeLat'
     * that occur when & gets encoded as &amp; and treated as part of the parameter name.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $queryParams = $request->query();
        $hasEncodedParams = false;
        $cleanedParams = [];
        
        // Check and clean parameter names
        foreach ($queryParams as $key => $value) {
            // Remove 'amp;' prefix that appears from HTML entity encoding issues
            $cleanedKey = preg_replace('/^(amp;)+/', '', $key);
            
            if ($cleanedKey !== $key) {
                $hasEncodedParams = true;
            }
            
            // Keep only the first occurrence of each cleaned key
            if (!isset($cleanedParams[$cleanedKey]) || empty($cleanedParams[$cleanedKey])) {
                $cleanedParams[$cleanedKey] = $value;
            }
        }
        
        // If we found HTML-encoded parameters, redirect to clean URL
        if ($hasEncodedParams) {
            $cleanUrl = $request->url();
            
            if (!empty($cleanedParams)) {
                $cleanUrl .= '?' . http_build_query($cleanedParams);
            }
            
            // Use 301 permanent redirect for SEO benefits
            return redirect($cleanUrl, 301);
        }
        
        return $next($request);
    }
}
