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
      
        if ($domain === 'catchaguide.com') {
          \App::setLocale('en');
        } elseif ($domain === 'catchaguide.de') {
          \App::setLocale('de');
        }
      }

      if(session()->has('locale')){
        \App::setLocale(session()->get('locale'));
      }
        
      return $next($request);
    }

}
