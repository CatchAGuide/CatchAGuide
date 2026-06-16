<?php

namespace App\Console\Commands;

use App\Services\Media\MediaEnvironmentResolver;
use Illuminate\Console\Command;

class ListMediaFolders extends Command
{
    protected $signature = 'media:list-folders';

    protected $description = 'List sitewide media folders and the current object-storage bucket prefix';

    public function handle(MediaEnvironmentResolver $environmentResolver): int
    {
        $prefix = $environmentResolver->bucketPrefix();
        $localBase = rtrim(public_path(), '/\\');

        $this->info('Current bucket prefix: ' . $prefix);
        $this->info('Local base: ' . $localBase);
        $this->newLine();

        $rows = [];

        foreach (config('media_storage.sitewide_folders', []) as $group => $folders) {
            foreach ($folders as $folder => $meta) {
                $migrate = (bool) ($meta['migrate'] ?? false);
                $rows[] = [
                    $group,
                    $folder,
                    $migrate ? 'yes' : 'no',
                    $migrate ? "{$localBase}/{$folder}" : '—',
                    $migrate ? "{$prefix}/{$folder}/" : '—',
                    $meta['listing'] ?? ($meta['notes'] ?? ''),
                ];
            }
        }

        $this->table(
            ['Group', 'Folder', 'Sync', 'Local path', 'Bucket path', 'Listing / notes'],
            $rows
        );

        $synced = array_filter($rows, fn (array $row) => $row[2] === 'yes');
        $this->newLine();
        $this->info(count($synced) . ' folder(s) will sync with: php artisan media:sync-directories --from-listing-media');
        $this->line('Override bucket prefix: --prefix=production (default follows APP_ENV: ' . app()->environment() . ')');

        return self::SUCCESS;
    }
}
