<?php

namespace App\Http\Middleware;

use App\Services\Vacation\VacationRedirectResolver;
use Closure;
use Illuminate\Http\Request;

class VacationRedirectMiddleware
{
    public function __construct(
        private VacationRedirectResolver $resolver,
    ) {}

    public function handle(Request $request, Closure $next)
    {
        $target = $this->resolver->resolve($request);

        if ($target !== null && $target !== '/' . trim($request->path(), '/')) {
            return redirect($target, 301);
        }

        return $next($request);
    }
}
