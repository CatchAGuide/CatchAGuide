<?php

namespace App\Http\Middleware;

use App\Support\BookingAssistantVisibility;
use Closure;
use Illuminate\Http\Request;

class EnsureBookingAssistantAccessible
{
    public function handle(Request $request, Closure $next)
    {
        if (! BookingAssistantVisibility::canAccessChatApi()) {
            abort(404);
        }

        return $next($request);
    }
}
