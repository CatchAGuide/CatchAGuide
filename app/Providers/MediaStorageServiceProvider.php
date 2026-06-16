<?php



namespace App\Providers;



use App\Contracts\Media\ListingMediaStorageInterface;

use App\Contracts\Media\MediaProcessorInterface;

use App\Contracts\Storage\ObjectStorageInterface;

use App\Services\Media\ConfigurableListingMediaStorage;

use App\Services\Media\ListingGalleryImageProcessor;

use App\Services\Media\ListingImageUploadService;

use App\Services\Media\ListingMediaPathBuilder;

use App\Services\Media\ListingMediaRelocator;

use App\Services\Media\ListingMediaStorageRegistry;

use App\Services\Media\ManagedMediaPathMatcher;

use App\Services\Media\MediaEnvironmentResolver;

use App\Services\Media\MediaPathResolver;

use App\Services\Media\MediaUrlResolver;

use App\Services\Media\MediaWriteStorageResolver;

use App\Services\Media\WebpMediaProcessor;

use App\Services\Storage\LaravelDiskStorage;

use App\Services\Storage\PrefixedObjectStorage;

use Illuminate\Support\ServiceProvider;



class MediaStorageServiceProvider extends ServiceProvider

{

    public function register(): void

    {

        $this->app->singleton(MediaEnvironmentResolver::class);

        $this->app->singleton(ManagedMediaPathMatcher::class);

        $this->app->singleton(ListingMediaPathBuilder::class);

        $this->app->singleton(ListingMediaRelocator::class);

        $this->app->singleton(ListingGalleryImageProcessor::class);

        $this->app->singleton(ListingImageUploadService::class);



        $this->app->singleton(MediaWriteStorageResolver::class, function ($app) {

            return new MediaWriteStorageResolver(

                $app->make('media.object_storage'),

                $app->make('media.local_storage'),

            );

        });



        $this->app->singleton('media.local_storage', function () {

            return new LaravelDiskStorage(

                (string) config('media_storage.local_disk', 'public'),

                mirrorToPublicPath: true,

            );

        });



        $this->app->singleton('media.object_storage.inner', function () {

            return new LaravelDiskStorage(

                (string) config('media_storage.disk', 'do_spaces'),

                mirrorToPublicPath: false,

            );

        });



        $this->app->singleton('media.object_storage', function ($app) {

            return new PrefixedObjectStorage(

                $app->make('media.object_storage.inner'),

                $app->make(MediaEnvironmentResolver::class),

            );

        });



        $this->app->bind(ObjectStorageInterface::class, function ($app) {

            return $app->make('media.object_storage');

        });



        $this->app->singleton(MediaProcessorInterface::class, WebpMediaProcessor::class);



        $this->app->singleton(MediaPathResolver::class, function ($app) {

            return new MediaPathResolver(

                $app->make('media.local_storage'),

                $app->make('media.object_storage'),

                $app->make(MediaEnvironmentResolver::class),

            );

        });



        $this->app->singleton(MediaUrlResolver::class, function ($app) {

            return new MediaUrlResolver(

                $app->make('media.object_storage'),

                $app->make('media.local_storage'),

                $app->make(MediaPathResolver::class),

                $app->make(ManagedMediaPathMatcher::class),

                $app->make(MediaEnvironmentResolver::class),

                $app->make(MediaWriteStorageResolver::class),

            );

        });



        $this->app->singleton(ListingMediaStorageRegistry::class, function ($app) {

            $registry = new ListingMediaStorageRegistry();



            foreach (array_keys(config('media_storage.listing_folders', [])) as $listingKey) {

                $registry->register($this->makeListingStorage($app, $listingKey));

            }



            return $registry;

        });

    }



    private function makeListingStorage($app, string $listingKey): ListingMediaStorageInterface

    {

        return new ConfigurableListingMediaStorage(

            $listingKey,

            $app->make('media.object_storage'),

            $app->make('media.local_storage'),

            $app->make(MediaProcessorInterface::class),

            $app->make(MediaUrlResolver::class),

            $app->make(MediaPathResolver::class),

            $app->make(MediaWriteStorageResolver::class),

            $app->make(ListingMediaPathBuilder::class),

        );

    }



    public function boot(): void

    {

        //

    }

}


