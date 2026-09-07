<?php

namespace App\Http\Middleware;

use App\Services\Search\ListingSearchStateService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Captures the location search submitted on a catalog page so it can be
 * restored on listing detail pages later in the session.
 */
class RememberListingSearch
{
    private const CATALOG_ROUTES = [
        'offers.index',
        'guidings.index',
        'vacations.index',
        'vacations.country',
        'vacations.all-offers',
    ];

    public function __construct(private ListingSearchStateService $searchState) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->routeIs(...self::CATALOG_ROUTES)) {
            $this->searchState->remember($request);
        }

        return $next($request);
    }
}
