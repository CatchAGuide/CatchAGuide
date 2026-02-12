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

      if (env('APP_ENV') == 'production') {
        $domain = $request->getHost();
        // Normalize so both www.catchaguide.de and catchaguide.de get correct locale
        $normalizedDomain = str_replace('www.', '', $domain);

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
