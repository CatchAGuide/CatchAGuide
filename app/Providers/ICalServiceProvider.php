<?php

namespace App\Providers;

use App\Services\ICalService;
use Illuminate\Support\ServiceProvider;

class ICalServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(ICalService::class, function ($app) {
            return new ICalService();
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