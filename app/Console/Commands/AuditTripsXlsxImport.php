<?php

namespace App\Console\Commands;

use App\Models\Trip;
use App\Services\Trip\TripXlsxImporter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class AuditTripsXlsxImport extends Command
{
    protected $signature = 'trips:audit-xlsx
                            {path : Directory or single .xlsx file}
                            {--user-id= : Only compare trips owned by this user}';

    protected $description = 'Verify XLSX trip templates are fully mapped and match imported DB records';

    public function __construct(
        private TripXlsxImporter $importer
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $files = $this->resolveFiles($this->argument('path'));
        if ($files === []) {
            $this->error('No .xlsx files found.');

            return self::FAILURE;
        }

        $formFields = array_keys(config('trips.fields'));
        $notInXlsx = [
            'gallery_images',
            'thumbnail_path',
            'provider_photo',
            'latitude',
            'longitude',
            'country',
            'city',
            'region',
            'provider_name',
        ];
        $coverage = [];
        $issues = 0;

        $this->info('Field alignment: XLSX labels → config/trips.php form fields');
        $this->newLine();

        foreach ($files as $file) {
            $basename = basename($file);
            $parsed = $this->importer->parseFile($file);
            $tripData = $parsed['trip'];

            $query = Trip::where('title', $tripData['title'] ?? '');
            if ($this->option('user-id')) {
                $query->where('user_id', (int) $this->option('user-id'));
            }
            $dbTrip = $query->orderByDesc('id')->first();

            $this->line("<fg=cyan>{$basename}</>");

            if (! $dbTrip) {
                $this->warn('  No DB trip found for title: ' . ($tripData['title'] ?? '(empty)'));
                $issues++;
                continue;
            }

            $this->line("  DB trip #{$dbTrip->id} ({$dbTrip->slug})");

            foreach ($formFields as $field) {
                if (in_array($field, $notInXlsx, true)) {
                    continue;
                }

                $hasParsed = $this->fieldHasValue($field, $tripData, $parsed['availability_dates']);
                $coverage[$field] = ($coverage[$field] ?? 0) + ($hasParsed ? 1 : 0);

                if (! $hasParsed) {
                    continue;
                }

                if (! $this->dbFieldMatches($field, $tripData, $parsed['availability_dates'], $dbTrip)) {
                    $this->warn("  Mismatch on {$field}");
                    $issues++;
                }
            }

            $this->newLine();
        }

        $this->info('Coverage across ' . count($files) . ' file(s) — how many files have data per form field:');
        foreach ($formFields as $field) {
            if (in_array($field, $notInXlsx, true)) {
                $this->line("  {$field}: n/a (not in XLSX)");
                continue;
            }
            $count = $coverage[$field] ?? 0;
            $this->line(sprintf('  %-30s %2d / %d', $field, $count, count($files)));
        }

        $this->newLine();
        $this->line('Not in XLSX (add manually in admin): ' . implode(', ', $notInXlsx));

        if ($issues > 0) {
            $this->warn("Audit finished with {$issues} issue(s).");

            return self::FAILURE;
        }

        $this->info('Audit passed — all parsed data matches DB records.');

        return self::SUCCESS;
    }

    private function fieldHasValue(string $field, array $tripData, array $availabilityDates): bool
    {
        return match ($field) {
            'availability'    => count($availabilityDates) > 0,
            'trip_schedule' => count($tripData['trip_schedule'] ?? []) > 0,
            'trip_highlights' => count($tripData['trip_highlights']['items'] ?? []) > 0,
            'additional_info' => count(array_filter(
                $tripData['additional_info'] ?? [],
                fn ($item) => ($item['enabled'] ?? false) && ! empty($item['details'])
            )) > 0,
            default => $this->scalarOrArrayHasValue($tripData[$field] ?? null),
        };
    }

    private function dbFieldMatches(string $field, array $tripData, array $availabilityDates, Trip $dbTrip): bool
    {
        return match ($field) {
            'availability' => $dbTrip->availabilityDates()->count() === count($availabilityDates),
            'trip_schedule' => count($dbTrip->trip_schedule ?? []) === count($tripData['trip_schedule'] ?? []),
            'trip_highlights' => count($dbTrip->trip_highlights['items'] ?? []) === count($tripData['trip_highlights']['items'] ?? []),
            'additional_info' => $this->normalizedJson($dbTrip->additional_info) === $this->normalizedJson($tripData['additional_info']),
            'price_per_person', 'price_single_room_addition' => (float) ($tripData[$field] ?? 0) === (float) ($dbTrip->{$field} ?? 0),
            default => json_encode($dbTrip->{$field} ?? null) === json_encode($tripData[$field] ?? null),
        };
    }

    private function scalarOrArrayHasValue(mixed $value): bool
    {
        if (is_array($value)) {
            return count($value) > 0;
        }

        return $value !== null && $value !== '';
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

    private function normalizedJson(mixed $value): string
    {
        return json_encode($this->sortKeysRecursive($value), JSON_UNESCAPED_UNICODE);
    }

    private function sortKeysRecursive(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        if (array_is_list($value)) {
            return array_map(fn ($item) => $this->sortKeysRecursive($item), $value);
        }

        ksort($value);

        return array_map(fn ($item) => $this->sortKeysRecursive($item), $value);
    }
}
