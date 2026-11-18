<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * /guide/* pages and /api/guide/* (redesigned guide dashboard): guides with
 * users.guide_area_beta only, while the global kill switch config('guide_area.enabled') is on.
 * Pages send everyone else to the old page; the API answers 401 / 403 JSON.
 */
class EnsureGuideAreaAccess
{
    /** Old page for each new page; anything else goes to the old guide profile. */
    private const FALLBACK = [
        'guide.area.overview' => 'profile.index',
        'guide.area.bookings' => 'profile.bookings',
        'guide.area.calendar' => 'profile.calendar',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $api = $request->is('api/*');
        $user = $request->user() ?? $request->user('web');

        if ($user === null) {
            return $api
                ? response()->json(['message' => __('guide_area.api.unauthenticated')], 401)
                : redirect()->guest(route('login'));
        }

        if (! $user->canViewGuideTools()) {
            return $api
                ? response()->json(['message' => __('guide_area.api.not_a_guide')], 403)
                : redirect()->route('profile.becomeguide');
        }

        if (! $user->usesGuideArea()) {
            return $api
                ? response()->json(['message' => __('guide_area.api.not_enabled')], 403)
                : redirect()->route(self::FALLBACK[$request->route()?->getName()] ?? 'profile.guide-profile');
        }

        return $next($request);
    }
}
