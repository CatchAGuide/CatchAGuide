<?php

namespace App\Console\Commands;

use App\Models\Guiding;
use App\Services\Media\ListingMediaPathBuilder;
use App\Services\Media\MediaPathResolver;
use Illuminate\Console\Command;

class MigrateGuidingImagesToIdFoldersCommand extends Command
{
    protected $signature = 'media:migrate-guiding-images-to-folders
                            {--dry-run : Show what would change without moving files or updating the database}
                            {--limit=0 : Max guidings to process (0 = all)}';

    protected $description = 'Move legacy guiding image paths into assets/images/guidings/{guiding-id}/ and update DB paths';

    public function handle(ListingMediaPathBuilder $paths, MediaPathResolver $pathResolver): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $limit = (int) $this->option('limit');

        $this->info('Target layout: assets/images/guidings/{guiding-id}/filename.webp');
        $this->info('Local storage root: ' . public_path());
        $this->newLine();

        if ($dryRun) {
            $this->warn('Dry run — no files or database rows will be changed.');
            $this->newLine();
        }

        $query = Guiding::query()
            ->where(function ($q) {
                $q->whereNotNull('thumbnail_path')
                    ->orWhereNotNull('gallery_images');
            });

        if ($limit > 0) {
            $query->limit($limit);
        }

        $moved = 0;
        $updated = 0;
        $skipped = 0;
        $missing = 0;
        $guidingsUpdated = 0;

        foreach ($query->cursor() as $guiding) {
            $guidingChanged = false;
            $thumbnail = $this->relocatePath($guiding, (string) ($guiding->thumbnail_path ?? ''), $paths, $pathResolver, $dryRun, $moved, $missing, $skipped);

            if ($thumbnail !== (string) ($guiding->thumbnail_path ?? '')) {
                $guiding->thumbnail_path = $thumbnail !== '' ? $thumbnail : null;
                $guidingChanged = true;
                $updated++;
            }

            $gallery = $guiding->gallery_images;
            if (is_string($gallery)) {
                $gallery = json_decode($gallery, true) ?? [];
            }

            if (! is_array($gallery)) {
                $gallery = [];
            }

            $newGallery = [];

            foreach ($gallery as $item) {
                if (! is_string($item) || $item === '') {
                    continue;
                }

                $relocated = $this->relocatePath($guiding, $item, $paths, $pathResolver, $dryRun, $moved, $missing, $skipped);

                if ($relocated !== $item) {
                    $guidingChanged = true;
                    $updated++;
                }

                $newGallery[] = $relocated;
            }

            if ($guidingChanged) {
                $guidingsUpdated++;

                if (! $dryRun) {
                    $guiding->gallery_images = json_encode(array_values($newGallery));
                    $guiding->saveQuietly();
                }
            }
        }

        $this->newLine();
        $this->info(sprintf(
            'Done%s. Guidings updated: %d, paths relocated: %d, DB path updates: %d, skipped: %d, missing files: %d',
            $dryRun ? ' (dry run)' : '',
            $guidingsUpdated,
            $moved,
            $updated,
            $skipped,
            $missing
        ));

        return self::SUCCESS;
    }

    private function relocatePath(
        Guiding $guiding,
        string $path,
        ListingMediaPathBuilder $paths,
        MediaPathResolver $pathResolver,
        bool $dryRun,
        int &$moved,
        int &$missing,
        int &$skipped,
    ): string {
        $path = trim($path);

        if ($path === '') {
            return '';
        }

        $normalized = $pathResolver->normalizePath($path);
        $target = $paths->migrateLegacyListingPath('guiding', (int) $guiding->id, $normalized);

        if ($target === null) {
            $skipped++;

            return $path;
        }

        if ($dryRun) {
            $exists = $pathResolver->exists($normalized) ? 'exists' : 'missing';
            $this->line("[dry-run] Guiding {$guiding->id}: {$normalized} -> {$target} ({$exists})");
            $moved++;

            return $target;
        }

        if (! $pathResolver->exists($normalized)) {
            $this->warn("Missing file for guiding {$guiding->id}: {$normalized}");
            $missing++;

            return $target;
        }

        $finalPath = media_move($normalized, $target);
        $this->info("Guiding {$guiding->id}: {$normalized} -> {$finalPath}");
        $moved++;

        return $finalPath;
    }
}
