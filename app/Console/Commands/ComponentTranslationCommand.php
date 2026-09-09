<?php

namespace App\Console\Commands;

use App\Services\Translation\ComponentTranslationService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ComponentTranslationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'component:translate
                            {--model=* : Which master-data lookups to translate (e.g. bathroom-amenity,facility). Default: all}
                            {--force : Retranslate EN from DE even when EN is already filled in}
                            {--report-missing : List rows with an incomplete DE/EN pair without translating}
                            {--list-models : List available --model keys and exit}
                            {--dry-run : Show what would change without writing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Translate the small master-data lookup tables (amenities, extras, requirements, boat types, target fish, etc.) that back dropdowns across guidings, vacations, accommodations and rental boats.';

    public function __construct(private ComponentTranslationService $translationService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        if ($this->option('list-models')) {
            $this->listModels();

            return 0;
        }

        $keys = $this->translationService->resolveKeys($this->option('model'));

        if (empty($keys)) {
            $this->error('No valid --model keys given. Run with --list-models to see available keys.');

            return 1;
        }

        if ($this->option('report-missing')) {
            $this->reportMissing($keys);

            return 0;
        }

        $this->info('Starting component/master-data translation...');
        $this->newLine();

        $force = (bool) $this->option('force');
        $dryRun = (bool) $this->option('dry-run');

        $rows = [];
        $totals = ['translated' => 0, 'skipped' => 0, 'failed' => 0];

        foreach ($keys as $key) {
            $label = $this->translationService->labelFor($key);
            $records = $this->translationService->rowsToProcess($key, $force);

            $translated = 0;
            $skipped = 0;
            $failed = 0;

            foreach ($records as $record) {
                $work = $this->translationService->pendingFieldWork($record, $key, $force);

                if (empty($work)) {
                    $skipped++;
                    continue;
                }

                $recordChanged = false;

                foreach ($work as $item) {
                    $targetField = $item['to'] === ComponentTranslationService::TARGET_LANGUAGE
                        ? $item['en_field']
                        : $item['de_field'];

                    if ($dryRun) {
                        $this->line("  Would translate [{$key}#{$record->id}] {$item['de_field']}/{$item['en_field']} ({$item['from']}→{$item['to']}): "
                            .Str::limit($item['text'], 60));
                        $recordChanged = true;
                        continue;
                    }

                    $translatedText = $this->translationService->translateField($item['text'], $item['from'], $item['to']);

                    if ($translatedText === null) {
                        $failed++;
                        $this->error("  Failed [{$key}#{$record->id}] {$item['de_field']}/{$item['en_field']} ({$item['from']}→{$item['to']})");
                        continue;
                    }

                    $record->setAttribute($targetField, $translatedText);
                    $recordChanged = true;
                }

                if ($recordChanged) {
                    $translated++;
                    if (! $dryRun) {
                        // Plain save (not saveQuietly) so models using the
                        // Cacheable trait (Water/Method/Target/Inclussion)
                        // still invalidate their cached lookups.
                        $record->save();
                    }
                } else {
                    $skipped++;
                }
            }

            $rows[] = [$key, $label, $records->count(), $translated, $skipped, $failed];
            $totals['translated'] += $translated;
            $totals['skipped'] += $skipped;
            $totals['failed'] += $failed;
        }

        $this->newLine();
        $this->table(['Key', 'Label', 'Candidates', $dryRun ? 'Would translate' : 'Translated', 'Skipped', 'Failed'], $rows);
        $this->newLine();

        $summary = $dryRun
            ? "Dry run completed. {$totals['translated']} row(s) would be updated."
            : "Completed. {$totals['translated']} row(s) updated.";
        $this->info($summary);

        if ($totals['failed'] > 0) {
            $this->warn("WARNING: {$totals['failed']} field translation(s) failed. Check logs for details.");
        }

        return $totals['failed'] > 0 ? 1 : 0;
    }

    private function listModels(): void
    {
        $rows = [];
        foreach (ComponentTranslationService::REGISTRY as $key => $config) {
            $fields = collect($config['fields'])->map(fn ($pair) => "{$pair[0]}/{$pair[1]}")->implode(', ');
            $rows[] = [$key, $config['label'], $fields];
        }

        $this->table(['Key', 'Label', 'Fields (de/en)'], $rows);
    }

    /**
     * @param  array<int, string>  $keys
     */
    private function reportMissing(array $keys): void
    {
        $rows = [];
        $ambiguousRows = [];

        foreach ($keys as $key) {
            $records = $this->translationService->rowsToProcess($key, false);

            foreach ($records as $record) {
                $rawName = Str::limit((string) ($record->getRawOriginal('name') ?? $record->getRawOriginal('name_de') ?? ''), 40);

                $work = $this->translationService->pendingFieldWork($record, $key, false);
                if (! empty($work)) {
                    $reasons = collect($work)->map(fn ($item) => "{$item['de_field']}/{$item['en_field']}:{$item['from']}→{$item['to']}")->implode(', ');
                    $rows[] = [$key, $record->id, $rawName, $reasons];
                }

                $ambiguous = $this->translationService->ambiguousDuplicates($record, $key);
                if (! empty($ambiguous)) {
                    $fields = collect($ambiguous)->map(fn ($item) => "{$item['de_field']}/{$item['en_field']}")->implode(', ');
                    $ambiguousRows[] = [$key, $record->id, $rawName, $fields];
                }
            }
        }

        if (empty($rows)) {
            $this->info('All selected master-data lookups have complete, non-ambiguous DE/EN pairs.');
        } else {
            $this->table(['Key', 'ID', 'Name (raw)', 'Missing (field:direction)'], $rows);
            $this->newLine();
            $this->info('Total: '.count($rows).' row(s) needing translation work.');
            $this->info('Run: php artisan component:translate --model=<key> to translate only these.');
        }

        if (! empty($ambiguousRows)) {
            $this->newLine();
            $this->warn('DE and EN are identical on these rows but the table has no reliable auto-fix direction (proper nouns/shared terms are common) — review by hand:');
            $this->table(['Key', 'ID', 'Name (raw)', 'Duplicate field'], $ambiguousRows);
        }
    }
}
