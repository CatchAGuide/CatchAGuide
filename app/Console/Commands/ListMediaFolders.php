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
        $this->info('Current bucket prefix: ' . $environmentResolver->bucketPrefix());
        $this->newLine();

        $rows = [];

        foreach (config('media_storage.sitewide_folders', []) as $group => $folders) {
            foreach ($folders as $folder => $meta) {
                $rows[] = [
                    $group,
                    $folder,
                    ($meta['migrate'] ?? false) ? 'yes' : 'no',
                    $meta['listing'] ?? ($meta['notes'] ?? ''),
                ];
            }
        }

        $this->table(
            ['Group', 'Folder', 'Object storage', 'Listing / notes'],
            $rows
        );

        return self::SUCCESS;
    }
}
