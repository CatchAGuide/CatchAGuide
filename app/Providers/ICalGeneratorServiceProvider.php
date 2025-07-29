<?php

namespace App\Providers;

use App\Services\ICalGeneratorService;
use Illuminate\Support\ServiceProvider;

class ICalGeneratorServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(ICalGeneratorService::class, function ($app) {
            return new ICalGeneratorService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
} 