<?php

namespace App\Http\Middleware;

use App\Domain\Vacation\CountrySlug;
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

        if ($target !== null) {
            $current = '/' . trim(CountrySlug::decode($request->path()), '/');
            $targetPath = parse_url($target, PHP_URL_PATH) ?: $target;
            $targetDecoded = '/' . trim(CountrySlug::decode($targetPath), '/');

            // Only encoding differences (österreich vs %C3%B6sterreich) should skip the redirect.
            // Case and space→hyphen differences still redirect once to the canonical URL.
            if ($current !== $targetDecoded) {
                return redirect($target, 301);
            }
        }

        return $next($request);
    }
}
