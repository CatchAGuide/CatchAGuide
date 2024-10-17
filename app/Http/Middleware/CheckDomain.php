<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckDomain
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, $allowedDomain)
    {

        // Get the current domain
        $currentDomain = $request->getHost();

        // Check if the current domain matches the allowed domain
        if ($currentDomain !== $allowedDomain) {
            abort(404); // Or you can redirect to a 404 page
        }

        return $next($request);
    }
}
