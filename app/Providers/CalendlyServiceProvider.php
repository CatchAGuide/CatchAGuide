<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\CalendlyService;

class CalendlyServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(CalendlyService::class, function ($app) {
            return new CalendlyService();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
