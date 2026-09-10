<?php

namespace App\Console\Commands;

use App\Models\Accommodation;
use App\Models\Camp;
use App\Models\Guiding;
use App\Models\RentalBoat;
use App\Models\SpecialOffer;
use App\Models\Trip;
use App\Services\Accommodation\AccommodationCacheService;
use App\Services\Camp\CampCacheService;
use App\Services\Media\ListingMediaPathBuilder;
use App\Services\Media\MediaTrashService;
use App\Services\RentalBoat\RentalBoatCacheService;
use App\Services\SpecialOffer\SpecialOfferCacheService;
use App\Services\Trip\TripCacheService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;

/**
 * Restore gallery files that are referenced in DB but missing live (still in media trash).
 */
class RestoreMissingListingGalleriesCommand extends Command
{
    protected $signature = 'media:restore-missing-galleries
        {--listing= : Limit to one listing key}
        {--id= : Limit to one entity id}
        {--dry-run : Show what would be restored}';

    protected $description = 'Restore missing listing gallery images from media trash by basename';

    /** @var array<string, array{model: class-string<Model>, folder: string, attribute: string, encode_json: bool}> */
    private const LISTINGS = [
        'camp' => ['model' => Camp::class, 'folder' => 'camps', 'attribute' => 'gallery_images', 'encode_json' => false],
        'trip' => ['model' => Trip::class, 'folder' => 'trips', 'attribute' => 'gallery_images', 'encode_json' => false],
        'accommodation' => ['model' => Accommodation::class, 'folder' => 'accommodations', 'attribute' => 'gallery_images', 'encode_json' => false],
        'rental_boat' => ['model' => RentalBoat::class, 'folder' => 'rental-boats', 'attribute' => 'gallery_images', 'encode_json' => false],
        'special_offer' => ['model' => SpecialOffer::class, 'folder' => 'special-offers', 'attribute' => 'gallery_images', 'encode_json' => false],
        'guiding' => ['model' => Guiding::class, 'folder' => 'guidings', 'attribute' => 'gallery_images', 'encode_json' => true],
    ];

    public function handle(MediaTrashService $trash, ListingMediaPathBuilder $paths): int
    {
        $listingFilter = $this->option('listing');
        $idFilter = $this->option('id') !== null ? (int) $this->option('id') : null;
        $dryRun = (bool) $this->option('dry-run');

        if ($listingFilter !== null && $listingFilter !== '' && ! isset(self::LISTINGS[$listingFilter])) {
            $this->error('Unknown --listing. Valid: ' . implode(', ', array_keys(self::LISTINGS)));

            return self::FAILURE;
        }

        $targets = $listingFilter
            ? [$listingFilter => self::LISTINGS[$listingFilter]]
            : self::LISTINGS;

        $restoredTotal = 0;
        $listingsFixed = 0;

        foreach ($targets as $listingKey => $config) {
            /** @var class-string<Model> $modelClass */
            $modelClass = $config['model'];
            $query = $modelClass::query()->orderBy('id');
            if ($idFilter !== null) {
                $query->whereKey($idFilter);
            }

            $query->chunkById(25, function ($rows) use (
                $trash,
                $paths,
                $listingKey,
                $config,
                $dryRun,
                &$restoredTotal,
                &$listingsFixed
            ) {
                foreach ($rows as $row) {
                    $attribute = $config['attribute'];
                    $raw = $row->{$attribute};
                    $gallery = is_string($raw)
                        ? (json_decode($raw, true) ?: [])
                        : (is_array($raw) ? $raw : []);

                    if ($gallery === []) {
                        continue;
                    }

                    $missing = [];
                    foreach ($gallery as $path) {
                        if (is_string($path) && $path !== '' && ! media_exists($path)) {
                            $missing[] = $path;
                        }
                    }

                    if ($missing === []) {
                        continue;
                    }

                    $liveDirectory = $paths->entityDirectory($listingKey, (int) $row->getKey());

                    if ($dryRun) {
                        $this->line(sprintf(
                            '%s#%d: %d missing of %d [dry-run]',
                            $listingKey,
                            $row->getKey(),
                            count($missing),
                            count($gallery)
                        ));
                        $listingsFixed++;
                        $restoredTotal += count($missing);

                        continue;
                    }

                    $result = $trash->restoreMissingGallery(
                        $gallery,
                        $config['folder'],
                        (int) $row->getKey(),
                        $liveDirectory
                    );

                    if ($result['restored'] === []) {
                        $this->warn(sprintf(
                            '%s#%d: %d missing, none restored from trash',
                            $listingKey,
                            $row->getKey(),
                            count($missing)
                        ));

                        continue;
                    }

                    $galleryValue = $config['encode_json']
                        ? json_encode(array_values($result['gallery']))
                        : array_values($result['gallery']);

                    $thumb = (string) ($row->thumbnail_path ?? '');
                    if ($thumb === '' || ! media_exists($thumb)) {
                        $thumb = $result['gallery'][0] ?? $thumb;
                    }

                    $row->forceFill([
                        $attribute => $galleryValue,
                        'thumbnail_path' => $thumb,
                    ])->save();

                    $this->clearListingCache($listingKey, (int) $row->getKey());

                    $count = count($result['restored']);
                    $restoredTotal += $count;
                    $listingsFixed++;
                    $this->line(sprintf(
                        '%s#%d: restored %d file(s)',
                        $listingKey,
                        $row->getKey(),
                        $count
                    ));
                }
            });
        }

        $this->info(sprintf(
            'Done. Listings touched: %d. Files restored: %d.%s',
            $listingsFixed,
            $restoredTotal,
            $dryRun ? ' (dry-run)' : ''
        ));

        return self::SUCCESS;
    }

    private function clearListingCache(string $listingKey, int $id): void
    {
        match ($listingKey) {
            'camp' => tap(app(CampCacheService::class), function (CampCacheService $cache) use ($id) {
                $cache->clearCampCache($id);
                $cache->clearCampsListCache();
            }),
            'trip' => tap(app(TripCacheService::class), function (TripCacheService $cache) use ($id) {
                $cache->clearTripCache($id);
                $cache->clearTripsListCache();
            }),
            'accommodation' => app(AccommodationCacheService::class)->clearAccommodationCache($id),
            'rental_boat' => app(RentalBoatCacheService::class)->clearRentalBoatCache($id),
            'special_offer' => tap(app(SpecialOfferCacheService::class), function (SpecialOfferCacheService $cache) use ($id) {
                $cache->clearSpecialOfferCache($id);
                $cache->clearSpecialOffersListCache();
            }),
            default => null,
        };
    }
}
