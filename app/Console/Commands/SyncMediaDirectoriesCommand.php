<?php

namespace App\Console\Commands;

use App\Services\Media\MediaEnvironmentResolver;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class SyncMediaDirectoriesCommand extends Command
{
    protected $signature = 'media:sync-directories
                            {sources?* : Local folder paths (relative to --base or absolute). Omit when using --from-listing-media}
                            {--from-listing-media : Sync all listing folders marked migrate in config/media_storage.php}
                            {--prefix= : Bucket folder prefix (production|staging). Default: APP_ENV prefix}
                            {--base= : Local root to resolve relative sources (default: public/)}
                            {--map=* : Remap local folder to bucket path, e.g. guidings-images:guidings-images}
                            {--skip-existing : Do not overwrite files already in the bucket}
                            {--dry-run : Show what would be uploaded}
                            {--limit=0 : Max files to upload (0 = all)}';

    protected $description = 'Upload local media directories directly to DigitalOcean Spaces (run on the server where files live)';

    public function handle(MediaEnvironmentResolver $envResolver): int
    {
        if ((string) config('media_storage.disk') === (string) config('media_storage.local_disk')) {
            $this->error('MEDIA_STORAGE_DISK is set to local. Configure DO_SPACES_* in .env first.');

            return self::FAILURE;
        }

        $bucketPrefix = (string) ($this->option('prefix') ?: $envResolver->bucketPrefix());
        $basePath = rtrim((string) ($this->option('base') ?: public_path()), '/\\');
        $mappings = $this->resolveMappings($basePath);

        if ($mappings === []) {
            $this->error('No source directories. Pass folder names or use --from-listing-media.');

            return self::FAILURE;
        }

        $disk = Storage::disk((string) config('media_storage.disk'));
        $dryRun = (bool) $this->option('dry-run');
        $skipExisting = (bool) $this->option('skip-existing');
        $limit = (int) $this->option('limit');
        $visibility = (string) config('media_storage.object_visibility', 'public');

        $this->info("Local base: {$basePath}");
        $this->info("Bucket prefix: {$bucketPrefix}/");
        $this->newLine();

        $uploaded = 0;
        $skipped = 0;
        $missing = 0;
        $failed = 0;

        foreach ($mappings as $localDir => $bucketFolder) {
            if (! is_dir($localDir)) {
                $this->warn("Local directory not found, skipping: {$localDir}");
                $missing++;
                continue;
            }

            $this->info("Syncing: {$localDir} → {$bucketPrefix}/{$bucketFolder}/");

            $files = $this->collectFiles($localDir);

            if ($files === []) {
                $this->line('  (empty)');
                continue;
            }

            $bar = $this->output->createProgressBar(count($files));
            $bar->start();

            foreach ($files as $absolutePath) {
                if ($limit > 0 && $uploaded + $failed >= $limit) {
                    break 2;
                }

                $relativePath = ltrim(str_replace('\\', '/', substr($absolutePath, strlen($localDir))), '/');
                $objectKey = $bucketPrefix . '/' . trim($bucketFolder, '/') . '/' . $relativePath;

                if ($skipExisting && $disk->exists($objectKey)) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                if ($dryRun) {
                    $uploaded++;
                    $bar->advance();
                    continue;
                }

                try {
                    $stream = fopen($absolutePath, 'rb');
                    if ($stream === false) {
                        $failed++;
                        $bar->advance();
                        continue;
                    }

                    $disk->put($objectKey, $stream, ['visibility' => $visibility]);
                    if (is_resource($stream)) {
                        fclose($stream);
                    }
                    $disk->setVisibility($objectKey, $visibility);
                    $uploaded++;
                } catch (\Throwable $e) {
                    $failed++;
                    $this->newLine();
                    $this->warn("  Failed: {$objectKey} — {$e->getMessage()}");
                }

                $bar->advance();
            }

            $bar->finish();
            $this->newLine();
        }

        $this->newLine();
        $this->info(sprintf(
            'Done. Uploaded: %d, skipped (exists): %d, missing dirs: %d, failed: %d%s',
            $uploaded,
            $skipped,
            $missing,
            $failed,
            $dryRun ? ' (dry run)' : ''
        ));

        if (! $dryRun && $uploaded > 0) {
            $this->line('Run media:make-objects-public --prefix=' . $bucketPrefix . ' if any files need public ACL.');
        }

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * @return array<string, string> local absolute path => bucket folder name
     */
    private function resolveMappings(string $basePath): array
    {
        $explicitMaps = $this->parseMapOptions();
        $mappings = [];

        if ($this->option('from-listing-media')) {
            foreach (config('media_storage.sitewide_folders.listing_media', []) as $folder => $meta) {
                if (! ($meta['migrate'] ?? false)) {
                    continue;
                }

                $localName = (string) $folder;
                $bucketFolder = $explicitMaps[$localName] ?? $localName;
                $mappings[$this->resolveLocalPath($basePath, $localName)] = $bucketFolder;
            }
        }

        foreach ($this->argument('sources') as $source) {
            $source = (string) $source;
            $localName = basename(str_replace('\\', '/', trim($source, '/\\')));
            $bucketFolder = $explicitMaps[$localName] ?? $explicitMaps[$source] ?? $localName;
            $mappings[$this->resolveLocalPath($basePath, $source)] = $bucketFolder;
        }

        return $mappings;
    }

    /**
     * @return array<string, string> local folder name => bucket folder
     */
    private function parseMapOptions(): array
    {
        $maps = [];

        foreach ((array) $this->option('map') as $entry) {
            if (! str_contains($entry, ':')) {
                continue;
            }

            [$local, $remote] = array_map('trim', explode(':', $entry, 2));
            if ($local !== '' && $remote !== '') {
                $maps[$local] = $remote;
            }
        }

        return $maps;
    }

    private function resolveLocalPath(string $basePath, string $source): string
    {
        $source = str_replace('\\', '/', $source);

        if ($this->isAbsolutePath($source)) {
            return rtrim($source, '/\\');
        }

        return rtrim($basePath, '/\\') . '/' . ltrim($source, '/\\');
    }

    private function isAbsolutePath(string $path): bool
    {
        return str_starts_with($path, '/')
            || str_starts_with($path, '\\')
            || (strlen($path) > 1 && ctype_alpha($path[0]) && $path[1] === ':');
    }

    /**
     * @return array<int, string>
     */
    private function collectFiles(string $directory): array
    {
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if (! $file->isFile()) {
                continue;
            }

            $files[] = $file->getPathname();
        }

        sort($files);

        return $files;
    }
}
