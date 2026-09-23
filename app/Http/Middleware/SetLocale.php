<?php

namespace App\Http\Middleware;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Redirect;

use Illuminate\Support\Facades\Route;

use Closure;

class SetLocale
{
    public function handle($request, Closure $next)
    {

      if (app()->environment('production')) {
        $domain = $request->getHost();
        // Normalize so both www.catchaguide.de and catchaguide.de get correct locale
        $normalizedDomain = str_replace('www.', '', $domain);

        // www and non-www previously both served 200 with no redirect between them — relying
        // entirely on the <link rel="canonical"> tag (which already points at the non-www host)
        // to avoid duplicate-content treatment. Enforce it properly with a 301 so Google (and
        // anyone with an old www link) lands on the one canonical host instead of two live copies.
        if (str_starts_with($domain, 'www.') && $request->method() === 'GET') {
          return Redirect::to($request->getScheme().'://'.$normalizedDomain.$request->getRequestUri(), 301);
        }

        if ($normalizedDomain === 'catchaguide.com') {
          \App::setLocale('en');
        } elseif ($normalizedDomain === 'catchaguide.de') {
          \App::setLocale('de');
        }
      }

      if(session()->has('locale')){
        \App::setLocale(session()->get('locale'));
      }
        
      return $next($request);
    }

}
