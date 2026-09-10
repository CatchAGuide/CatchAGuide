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
use App\Services\Media\ListingGalleryDeduplicator;
use App\Services\Media\MediaTrashService;
use App\Services\RentalBoat\RentalBoatCacheService;
use App\Services\SpecialOffer\SpecialOfferCacheService;
use App\Services\Trip\TripCacheService;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;

class DedupeListingGalleriesCommand extends Command
{
    protected $signature = 'media:dedupe-listing-galleries
        {--listing= : Limit to one listing key (camp, trip, accommodation, rental_boat, special_offer, guiding)}
        {--id= : Limit to one entity id}
        {--dry-run : Show changes without writing}
        {--trash : After backing up the current gallery, move removed live files into `_trash` (never deletes without a backup copy)}';

    protected $description = 'Remove duplicate listing gallery_images entries caused by re-saving existing previews';

    /** @var array<string, array{model: class-string<Model>, attribute: string, encode_json: bool}> */
    private const LISTINGS = [
        'camp' => ['model' => Camp::class, 'attribute' => 'gallery_images', 'encode_json' => false],
        'trip' => ['model' => Trip::class, 'attribute' => 'gallery_images', 'encode_json' => false],
        'accommodation' => ['model' => Accommodation::class, 'attribute' => 'gallery_images', 'encode_json' => false],
        'rental_boat' => ['model' => RentalBoat::class, 'attribute' => 'gallery_images', 'encode_json' => false],
        'special_offer' => ['model' => SpecialOffer::class, 'attribute' => 'gallery_images', 'encode_json' => false],
        'guiding' => ['model' => Guiding::class, 'attribute' => 'gallery_images', 'encode_json' => true],
    ];

    public function handle(ListingGalleryDeduplicator $deduplicator, MediaTrashService $trashService): int
    {
        $listingFilter = $this->option('listing');
        $idFilter = $this->option('id') !== null ? (int) $this->option('id') : null;
        $dryRun = (bool) $this->option('dry-run');
        $trash = (bool) $this->option('trash');

        if ($listingFilter !== null && $listingFilter !== '' && ! isset(self::LISTINGS[$listingFilter])) {
            $this->error('Unknown --listing. Valid: ' . implode(', ', array_keys(self::LISTINGS)));

            return self::FAILURE;
        }

        $targets = $listingFilter
            ? [$listingFilter => self::LISTINGS[$listingFilter]]
            : self::LISTINGS;

        $changedCount = 0;
        $removedFiles = 0;

        foreach ($targets as $listingKey => $config) {
            /** @var class-string<Model> $modelClass */
            $modelClass = $config['model'];
            $query = $modelClass::query()->orderBy('id');

            if ($idFilter !== null) {
                $query->whereKey($idFilter);
            }

            $query->chunkById(50, function ($rows) use (
                $deduplicator,
                $trashService,
                $listingKey,
                $config,
                $dryRun,
                $trash,
                &$changedCount,
                &$removedFiles
            ) {
                foreach ($rows as $row) {
                    $attribute = $config['attribute'];
                    $rawGallery = $row->{$attribute};
                    $result = $deduplicator->dedupe($rawGallery, $row->thumbnail_path ?? null);

                    if (! $result['changed']) {
                        continue;
                    }

                    $changedCount++;
                    $removedFiles += count($result['removed']);

                    $this->line(sprintf(
                        '%s#%d: %d → %d (remove %d)%s',
                        $listingKey,
                        $row->getKey(),
                        $this->galleryCount($rawGallery),
                        count($result['gallery']),
                        count($result['removed']),
                        $dryRun ? ' [dry-run]' : ''
                    ));

                    if ($dryRun) {
                        continue;
                    }

                    // Always snapshot the current gallery into `_trash` before changing DB.
                    $snapshotPaths = array_values(array_filter([
                        ...(is_array($rawGallery) ? $rawGallery : (json_decode((string) $rawGallery, true) ?: [])),
                        $row->thumbnail_path ?? null,
                    ], static fn ($path) => is_string($path) && $path !== ''));
                    $trashService->backupMany($snapshotPaths);

                    $galleryValue = $config['encode_json']
                        ? json_encode(array_values($result['gallery']))
                        : array_values($result['gallery']);

                    $row->forceFill([
                        $attribute => $galleryValue,
                        'thumbnail_path' => $result['thumbnail'] !== '' ? $result['thumbnail'] : $row->thumbnail_path,
                    ])->save();

                    if ($trash && $result['removed'] !== []) {
                        $keptLive = array_values(array_filter(
                            [...$result['gallery'], $result['thumbnail']],
                            static fn ($path) => is_string($path) && $path !== '' && media_exists($path)
                        ));

                        // Refuse to remove live files when the kept gallery is not actually live.
                        if ($keptLive === [] && $result['gallery'] !== []) {
                            $this->warn(sprintf(
                                '%s#%d: skipped --trash because kept gallery files are missing on storage (backup already saved)',
                                $listingKey,
                                $row->getKey()
                            ));
                        } else {
                            media_trash_paths($result['removed'], $keptLive !== [] ? $keptLive : array_filter([
                                ...$result['gallery'],
                                $result['thumbnail'],
                            ]));
                        }
                    }

                    $this->clearListingCache($listingKey, (int) $row->getKey());
                }
            });
        }

        $this->info(sprintf(
            'Done. Listings changed: %d. Gallery paths removed from DB: %d.%s',
            $changedCount,
            $removedFiles,
            $dryRun ? ' (dry-run)' : ''
        ));

        return self::SUCCESS;
    }

    private function galleryCount(mixed $gallery): int
    {
        if (is_string($gallery)) {
            $gallery = json_decode($gallery, true) ?? [];
        }

        return is_array($gallery) ? count($gallery) : 0;
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
