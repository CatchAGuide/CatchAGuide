<?php

namespace App\Console\Commands;

use App\Models\Guiding;
use App\Services\Media\ListingMediaStorageRegistry;
use App\Services\Media\MediaEnvironmentResolver;
use Illuminate\Console\Command;

class MigrateListingImagesToObjectStorage extends Command
{
    protected $signature = 'media:migrate-to-object-storage
                            {listing=guiding : Listing key to migrate (guiding, vacation, accommodation, camp, rental_boat, special_offer, trip)}
                            {--dry-run : Show what would be migrated without uploading}
                            {--limit=0 : Max records to process (0 = all)}';

    protected $description = 'Upload existing local listing images to object storage (DigitalOcean Spaces)';

    public function handle(ListingMediaStorageRegistry $registry, MediaEnvironmentResolver $envResolver): int
    {
        $listingKey = (string) $this->argument('listing');

        if ($listingKey !== 'guiding') {
            $this->warn("Migration is implemented for guiding first. Extend this command for [{$listingKey}] when ready.");

            return self::FAILURE;
        }

        $storage = $registry->forListing('guiding');
        $dryRun = (bool) $this->option('dry-run');
        $limit = (int) $this->option('limit');

        $this->info('Bucket prefix: ' . $envResolver->bucketPrefix());
        $this->newLine();

        $query = Guiding::query()
            ->where(function ($q) {
                $q->whereNotNull('thumbnail_path')
                    ->orWhereNotNull('gallery_images');
            });

        if ($limit > 0) {
            $query->limit($limit);
        }

        $migrated = 0;
        $skipped = 0;

        foreach ($query->cursor() as $guiding) {
            $paths = $this->collectPaths($guiding);

            foreach ($paths as $path) {
                if (app('media.object_storage')->exists($path)) {
                    $skipped++;
                    continue;
                }

                if (! app('media.local_storage')->exists($path)) {
                    $this->line("Missing locally, skipping: {$path}");
                    $skipped++;
                    continue;
                }

                if ($dryRun) {
                    $this->line("[dry-run] Would migrate: {$path}");
                    $migrated++;
                    continue;
                }

                $contents = app('media.local_storage')->read($path);
                if ($contents === '') {
                    $skipped++;
                    continue;
                }

                app('media.object_storage')->write($path, $contents, ['visibility' => 'public']);
                $this->info("Migrated: {$path}");
                $migrated++;
            }
        }

        $this->newLine();
        $this->info("Done. Migrated: {$migrated}, skipped: {$skipped}" . ($dryRun ? ' (dry run)' : ''));

        return self::SUCCESS;
    }

    private function collectPaths(Guiding $guiding): array
    {
        $paths = [];

        if (! empty($guiding->thumbnail_path)) {
            $paths[] = $guiding->thumbnail_path;
        }

        $gallery = $guiding->gallery_images;
        if (is_string($gallery)) {
            $gallery = json_decode($gallery, true) ?? [];
        }

        if (is_array($gallery)) {
            foreach ($gallery as $item) {
                if (is_string($item) && $item !== '') {
                    $paths[] = $item;
                }
            }
        }

        return array_values(array_unique($paths));
    }
}
