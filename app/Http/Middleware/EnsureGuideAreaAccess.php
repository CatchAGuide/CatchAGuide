<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * /guide/* (redesigned guide dashboard): guides with users.guide_area_beta only, while the
 * global kill switch config('guide_area.enabled') is on. Everyone else goes to the old page.
 */
class EnsureGuideAreaAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user('web');

        if ($user === null) {
            return redirect()->guest(route('login'));
        }

        if (! $user->canViewGuideTools()) {
            return redirect()->route('profile.becomeguide');
        }

        if (! $user->usesGuideArea()) {
            return redirect()->route('profile.guide-profile');
        }

        return $next($request);
    }
}
