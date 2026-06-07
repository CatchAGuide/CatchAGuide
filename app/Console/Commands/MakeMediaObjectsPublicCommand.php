<?php

namespace App\Console\Commands;

use App\Services\Media\MediaEnvironmentResolver;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MakeMediaObjectsPublicCommand extends Command
{
    protected $signature = 'media:make-objects-public
                            {--prefix= : Bucket folder prefix (staging, production). Default: current APP_ENV prefix}
                            {--both : Process both staging and production folders}
                            {--all : Include every file type (default: image extensions only)}
                            {--dry-run : List files without changing ACL}
                            {--limit=0 : Max files to update (0 = all)}';

    protected $description = 'Set public-read ACL on DigitalOcean Spaces objects so images load in the browser';

    public function handle(MediaEnvironmentResolver $envResolver): int
    {
        if ((string) config('media_storage.disk') === (string) config('media_storage.local_disk')) {
            $this->error('MEDIA_STORAGE_DISK is set to local — nothing to update on object storage.');

            return self::FAILURE;
        }

        $prefixes = $this->resolvePrefixes($envResolver);

        if ($prefixes === []) {
            $this->error('No prefix to process.');

            return self::FAILURE;
        }

        $disk = Storage::disk((string) config('media_storage.disk'));
        $dryRun = (bool) $this->option('dry-run');
        $limit = (int) $this->option('limit');
        $updated = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($prefixes as $prefix) {
            $this->info("Scanning prefix: {$prefix}/");

            if (! $disk->exists($prefix)) {
                $this->warn("  Prefix not found, skipping: {$prefix}");
                continue;
            }

            $files = $disk->allFiles($prefix);
            $this->line('  Found ' . count($files) . ' file(s).');

            foreach ($files as $file) {
                if ($limit > 0 && $updated + $failed >= $limit) {
                    break 2;
                }

                if (! $this->option('all') && ! $this->isMediaFile($file)) {
                    $skipped++;
                    continue;
                }

                if ($dryRun) {
                    $this->line("  [dry-run] Would make public: {$file}");
                    $updated++;
                    continue;
                }

                try {
                    $disk->setVisibility($file, 'public');
                    $updated++;

                    if ($updated % 500 === 0) {
                        $this->line("  Updated {$updated} file(s)…");
                    }
                } catch (\Throwable $e) {
                    $failed++;
                    $this->warn("  Failed: {$file} — {$e->getMessage()}");
                }
            }
        }

        $this->newLine();
        $this->info(sprintf(
            'Done. Public: %d, skipped (non-media): %d, failed: %d%s',
            $updated,
            $skipped,
            $failed,
            $dryRun ? ' (dry run)' : ''
        ));

        if (! $dryRun && $updated > 0) {
            $this->line('Test a file URL in the browser (must not require login).');
        }

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * @return array<int, string>
     */
    private function resolvePrefixes(MediaEnvironmentResolver $envResolver): array
    {
        if ($this->option('both')) {
            return $envResolver->knownPrefixes();
        }

        $prefix = $this->option('prefix');

        return [(string) ($prefix ?: $envResolver->bucketPrefix())];
    }

    private function isMediaFile(string $path): bool
    {
        return (bool) preg_match('/\.(webp|jpe?g|png|gif|svg|avif|bmp|tiff?|ico|heic|heif|mp4|webm|pdf)$/i', $path);
    }
}
