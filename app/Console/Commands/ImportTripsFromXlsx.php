<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Trip\TripXlsxImporter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ImportTripsFromXlsx extends Command
{
    protected $signature = 'trips:import-xlsx
                            {path : Directory or single .xlsx file}
                            {--user-id= : Owner user ID (defaults to first admin)}
                            {--status=draft : Trip status (draft or active)}
                            {--update : Update existing trips matched by slug}
                            {--dry-run : Parse files without writing to the database}';

    protected $description = 'Import trip listings from Angelreise XLSX templates into the database';

    public function __construct(
        private TripXlsxImporter $importer
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $path = $this->argument('path');
        $dryRun = (bool) $this->option('dry-run');
        $status = $this->option('status') ?: 'draft';

        if (! in_array($status, ['draft', 'active'], true)) {
            $this->error('Status must be "draft" or "active".');

            return self::FAILURE;
        }

        $files = $this->resolveFiles($path);
        if ($files === []) {
            $this->error("No .xlsx files found at: {$path}");

            return self::FAILURE;
        }

        $userId = $this->resolveUserId();
        if ($userId === null) {
            return self::FAILURE;
        }

        $this->info(sprintf(
            '%s %d trip template(s) as %s for user #%d',
            $dryRun ? 'Would import' : 'Importing',
            count($files),
            $status,
            $userId
        ));
        $this->newLine();

        $imported = 0;
        $failed = 0;

        foreach ($files as $file) {
            $basename = basename($file);

            try {
                if ($dryRun) {
                    $parsed = $this->importer->parseFile($file);
                    $title = $parsed['trip']['title'] ?? '(no title)';
                    $dates = count($parsed['availability_dates']);
                    $this->line("  [dry-run] {$basename} → \"{$title}\" ({$dates} availability date(s))");
                    $imported++;
                    continue;
                }

                $trip = $this->importer->importFile(
                    $file,
                    $userId,
                    $status,
                    (bool) $this->option('update')
                );

                $this->line("  ✓ {$basename} → trip #{$trip->id} ({$trip->slug})");
                $imported++;
            } catch (\Throwable $e) {
                $this->error("  ✗ {$basename}: {$e->getMessage()}");
                $failed++;
            }
        }

        $this->newLine();
        $this->info("Done. Imported: {$imported}, failed: {$failed}");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * @return list<string>
     */
    private function resolveFiles(string $path): array
    {
        if (is_file($path) && str_ends_with(strtolower($path), '.xlsx')) {
            return [$path];
        }

        if (! is_dir($path)) {
            return [];
        }

        return collect(File::allFiles($path))
            ->map(fn ($file) => $file->getPathname())
            ->filter(fn (string $file) => str_ends_with(strtolower($file), '.xlsx'))
            ->sort()
            ->values()
            ->all();
    }

    private function resolveUserId(): ?int
    {
        if ($this->option('user-id')) {
            $user = User::find((int) $this->option('user-id'));
            if (! $user) {
                $this->error('User not found for --user-id=' . $this->option('user-id'));

                return null;
            }

            return $user->id;
        }

        $user = User::query()->orderBy('id')->first();
        if (! $user) {
            $this->error('No users found. Create a user first or pass --user-id=');

            return null;
        }

        $this->warn("Using user #{$user->id} ({$user->email}) as trip owner.");

        return $user->id;
    }
}
