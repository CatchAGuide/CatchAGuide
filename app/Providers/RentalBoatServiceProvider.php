<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\RentalBoat\RentalBoatDataProcessor;
use App\Services\RentalBoat\RentalBoatRequirementsProcessor;
use App\Services\RentalBoat\RentalBoatInformationProcessor;
use App\Services\RentalBoat\RentalBoatPricingProcessor;
use App\Services\RentalBoat\RentalBoatExtrasProcessor;
use App\Services\RentalBoat\RentalBoatImageProcessor;
use App\Services\RentalBoat\RentalBoatSeoService;
use App\Services\RentalBoat\RentalBoatCacheService;

class RentalBoatServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register all rental boat services as singletons for better performance
        $this->app->singleton(RentalBoatRequirementsProcessor::class);
        $this->app->singleton(RentalBoatInformationProcessor::class);
        $this->app->singleton(RentalBoatPricingProcessor::class);
        $this->app->singleton(RentalBoatExtrasProcessor::class);
        $this->app->singleton(RentalBoatImageProcessor::class);
        $this->app->singleton(RentalBoatSeoService::class);
        $this->app->singleton(RentalBoatCacheService::class);
        
        // RentalBoatDataProcessor needs other processors injected
        $this->app->singleton(RentalBoatDataProcessor::class, function ($app) {
            return new RentalBoatDataProcessor(
                $app->make(RentalBoatRequirementsProcessor::class),
                $app->make(RentalBoatInformationProcessor::class),
                $app->make(RentalBoatPricingProcessor::class),
                $app->make(RentalBoatExtrasProcessor::class)
            );
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

