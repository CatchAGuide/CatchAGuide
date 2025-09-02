<?php

namespace App\Providers;

use App\Http\Resources\EventResource;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use App\Services\LanguageService;
use App\Services\Asset;
use App\Services\GuidingService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useBootstrap();
        EventResource::withoutWrapping();


        $this->app->singleton('language', function(){
            return new LanguageService;
        });

        $this->app->singleton('asset', function(){
            return new Asset;
        });

        $this->app->singleton('guiding', function(){
            return new GuidingService;
        });

    }
}
