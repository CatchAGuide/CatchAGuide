<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Guiding;
use App\Models\Language;
use App\Services\Translation\GuidingTranslationService;
use Illuminate\Support\Str;

class GuidingTranslationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'guiding:translate 
                            {--guiding=* : Specific guiding IDs to translate}
                            {--language=* : Target languages to translate to (e.g., en,es,fr)}
                            {--detect-language : Audit/fix guidings.language from main content (EN/DE heuristic); alone = language only, no translate}
                            {--mismatches-only : With --detect-language, only show/update rows where detected differs from current}
                            {--force : Force retranslation even if translations exist}
                            {--admin-changes : Only process guidings with admin changes}
                            {--missing-only : Only process guidings that have no translation for at least one target language}
                            {--report-missing : List guidings missing translations (no translation performed)}
                            {--dry-run : Show what would change without writing (applies to detect-language and translate)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Translate guiding content (defaults EN/DE). Use --detect-language to audit/fix source language only; add --missing-only/--admin-changes/--force to translate after detection. Use --dry-run to preview.';

    private GuidingTranslationService $translationService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(GuidingTranslationService $translationService)
    {
        parent::__construct();
        $this->translationService = $translationService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting Guiding Translation Process...');
        $this->newLine();

        // Handle language detection first if requested
        if ($this->option('detect-language')) {
            $this->handleLanguageDetection();

            // Language-only mode: do not continue into translation unless explicitly asked
            if ($this->isDetectLanguageOnly()) {
                return 0;
            }

            $this->newLine();
            $this->info('Continuing with translation after source-language check...');
            $this->newLine();
        }

        // Get target languages (defaults to EN and DE if not specified)
        $targetLanguages = $this->getTargetLanguages();

        // Report missing translations only (no translation)
        if ($this->option('report-missing')) {
            $this->reportMissingTranslations($targetLanguages);
            return 0;
        }

        // Get guidings to process
        $guidings = $this->getGuidingsToProcess($targetLanguages);
        if ($guidings->isEmpty()) {
            $this->warn('No guidings found to process.');
            return 0;
        }

        $this->info("Processing {$guidings->count()} guiding(s) for languages: " . implode(', ', $targetLanguages));
        $this->newLine();

        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        $progressBar = $this->output->createProgressBar($guidings->count() * count($targetLanguages));
        $progressBar->start();

        $results = [
            'translated' => 0,
            'skipped' => 0,
            'failed' => 0
        ];

        foreach ($guidings as $guiding) {
            // Reload language in case --detect-language just updated it
            $guiding->refresh();

            foreach ($targetLanguages as $targetLanguage) {
                $progressBar->advance();

                try {
                    // Skip if same language as source
                    if ($guiding->language === $targetLanguage) {
                        $results['skipped']++;
                        continue;
                    }

                    // Check if translation needed
                    if (!$force && !$this->translationService->hasSignificantChanges($guiding, $targetLanguage)) {
                        // Check if translation exists
                        $existing = $this->translationService->getTranslatedGuiding($guiding, $targetLanguage);
                        if ($existing) {
                            $results['skipped']++;
                            continue;
                        }
                    }

                    if ($dryRun) {
                        $this->line("\nWould translate: {$guiding->title} (ID: {$guiding->id}) to {$targetLanguage}");
                        $results['translated']++;
                        continue;
                    }

                    // Perform translation
                    $success = $this->translationService->translateGuiding($guiding, $targetLanguage);
                    
                    if ($success) {                        
                        // Update content timestamp
                        $this->translationService->markContentUpdated($guiding);
                        
                        $results['translated']++;
                    } else {
                        $results['failed']++;
                    }

                } catch (\Exception $e) {
                    $results['failed']++;
                    $this->error("\nFailed to translate guiding {$guiding->id} to {$targetLanguage}: {$e->getMessage()}");
                }
            }
        }

        $progressBar->finish();
        $this->newLine(2);

        // Display results
        $this->displayResults($results, $dryRun);

        return 0;
    }

    /**
     * True when --detect-language should run alone (no translation step).
     */
    private function isDetectLanguageOnly(): bool
    {
        return ! $this->option('missing-only')
            && ! $this->option('admin-changes')
            && ! $this->option('force')
            && ! $this->option('report-missing');
    }

    /**
     * Audit/fix guidings.language from main content (EN/DE heuristic).
     * Respects --dry-run, --mismatches-only, and --guiding.
     */
    private function handleLanguageDetection(): void
    {
        $dryRun = (bool) $this->option('dry-run');
        $mismatchesOnly = (bool) $this->option('mismatches-only');

        $query = Guiding::query()->orderBy('id');

        $guidingIds = $this->parseGuidingIds();
        if (! empty($guidingIds)) {
            $query->whereIn('id', $guidingIds);
        } else {
            $query->where('status', 1);
        }

        $guidings = $query->get([
            'id', 'title', 'description', 'additional_information', 'meeting_point',
            'desc_course_of_action', 'desc_meeting_point', 'desc_starting_time',
            'desc_tour_unique', 'language', 'status',
        ]);

        if ($guidings->isEmpty()) {
            $this->warn('No guidings found for language detection.');
            return;
        }

        $this->info(($dryRun ? '[DRY RUN] ' : '[APPLY] ').'Checking source language for '.$guidings->count().' guiding(s)...');
        $this->comment('Uses content heuristic (EN/DE). Uncertain rows are left unchanged (no forced de fallback).');
        $this->newLine();

        $rows = [];
        $stats = [
            'checked' => 0,
            'match' => 0,
            'mismatch' => 0,
            'uncertain' => 0,
            'would_update' => 0,
            'updated' => 0,
            'to_en' => 0,
            'to_de' => 0,
        ];

        foreach ($guidings as $guiding) {
            $stats['checked']++;
            $result = $this->translationService->detectSourceLanguageFromContent($guiding);
            $current = strtolower((string) ($guiding->language ?: 'de'));
            $detected = $result['language'];
            $isMismatch = $detected !== null && $detected !== $current;

            if ($detected === null) {
                $stats['uncertain']++;
            } elseif ($isMismatch) {
                $stats['mismatch']++;
            } else {
                $stats['match']++;
            }

            if ($mismatchesOnly && ! $isMismatch) {
                continue;
            }

            $hasDeTranslation = Language::where('source_id', (string) $guiding->id)
                ->where('type', 'guidings')
                ->where('language', 'de')
                ->exists();
            $hasEnTranslation = Language::where('source_id', (string) $guiding->id)
                ->where('type', 'guidings')
                ->where('language', 'en')
                ->exists();

            $action = 'keep';
            if ($isMismatch) {
                $action = $dryRun ? 'would_update' : 'updated';
                $stats['would_update']++;
                if ($detected === 'en') {
                    $stats['to_en']++;
                } elseif ($detected === 'de') {
                    $stats['to_de']++;
                }

                if (! $dryRun) {
                    $guiding->language = $detected;
                    $guiding->save();
                    $stats['updated']++;
                }
            }

            $rows[] = [
                $guiding->id,
                $current,
                $detected ?? '?',
                $result['confidence'],
                $hasEnTranslation ? 'yes' : 'no',
                $hasDeTranslation ? 'yes' : 'no',
                $action,
                Str::limit((string) $guiding->title, 45),
                $result['reason'],
            ];
        }

        if (empty($rows)) {
            $this->info($mismatchesOnly ? 'No source-language mismatches found.' : 'Nothing to show.');
        } else {
            $this->table(
                ['ID', 'Current', 'Detected', 'Conf.', 'EN row', 'DE row', 'Action', 'Title', 'Why'],
                $rows
            );
        }

        $this->newLine();
        $this->table(['Metric', 'Count'], [
            ['Checked', $stats['checked']],
            ['Already correct', $stats['match']],
            ['Mismatches', $stats['mismatch']],
            ['Uncertain (left unchanged)', $stats['uncertain']],
            ['Would set → EN', $stats['to_en']],
            ['Would set → DE', $stats['to_de']],
            [$dryRun ? 'Would update' : 'Updated', $dryRun ? $stats['would_update'] : $stats['updated']],
        ]);

        if ($dryRun && $stats['would_update'] > 0) {
            $this->newLine();
            $this->info('Dry-run only. To write language fixes:');
            $this->line('  php artisan guiding:translate --detect-language --mismatches-only');
        }
    }

    /**
     * @return array<int, int>
     */
    private function parseGuidingIds(): array
    {
        $guidingIds = $this->option('guiding');
        if (empty($guidingIds)) {
            return [];
        }

        $ids = [];
        foreach ($guidingIds as $raw) {
            foreach (explode(',', (string) $raw) as $id) {
                $id = trim($id);
                if ($id !== '') {
                    $ids[] = (int) $id;
                }
            }
        }

        return $ids;
    }

    /**
     * Get target languages from options
     */
    private function getTargetLanguages(): array
    {
        $languages = $this->option('language');
        
        if (empty($languages)) {
            // Default to English and German (your current language choices)
            $this->info('No languages specified, using defaults: EN, DE');
            return ['en', 'de'];
        }

        // Handle comma-separated values
        $result = [];
        foreach ($languages as $lang) {
            $result = array_merge($result, explode(',', $lang));
        }

        return array_map('trim', $result);
    }

    /**
     * Get guidings to process based on options
     *
     * @param array|null $targetLanguages Required when using --missing-only
     */
    private function getGuidingsToProcess(?array $targetLanguages = null)
    {
        $guidingIds = $this->parseGuidingIds();
        
        // If specific guiding IDs are provided, use those
        if (!empty($guidingIds)) {
            return Guiding::whereIn('id', $guidingIds)
                          ->where('status', 1)
                          ->with(['user', 'guidingTargets', 'guidingMethods', 'guidingWaters'])
                          ->get();
        }

        // If admin-changes option is used, get only guidings with admin changes
        if ($this->option('admin-changes')) {
            $guidingIds = $this->translationService->getGuidingsNeedingTranslation();
            
            if (empty($guidingIds)) {
                return collect(); // Return empty collection
            }
            
            return Guiding::whereIn('id', $guidingIds)
                          ->where('status', 1)
                          ->with(['user', 'guidingTargets', 'guidingMethods', 'guidingWaters'])
                          ->get();
        }

        // Only guidings that have no translation for at least one target language
        if ($this->option('missing-only') && $targetLanguages) {
            return $this->translationService->getGuidingsMissingTranslations($targetLanguages);
        }

        // Default: get all active guidings
        return Guiding::where('status', 1)
                      ->with(['user', 'guidingTargets', 'guidingMethods', 'guidingWaters'])
                      ->get();
    }

    /**
     * Output a report of guidings missing translations for the given target languages.
     */
    private function reportMissingTranslations(array $targetLanguages): void
    {
        $guidings = $this->translationService->getGuidingsMissingTranslations($targetLanguages);

        if ($guidings->isEmpty()) {
            $this->info('All active guidings have translations for: ' . implode(', ', $targetLanguages));
            return;
        }

        $this->info('Guidings missing at least one translation for [' . implode(', ', $targetLanguages) . ']:');
        $this->newLine();

        $rows = $guidings->map(function ($guiding) use ($targetLanguages) {
            $missing = [];
            foreach ($targetLanguages as $lang) {
                if (!$this->translationService->hasTranslation($guiding, $lang)) {
                    $missing[] = $lang;
                }
            }
            return [
                $guiding->id,
                Str::limit($guiding->title ?? '-', 50),
                $guiding->language ?? '-',
                implode(', ', $missing),
            ];
        })->toArray();

        $this->table(['ID', 'Title', 'Source lang', 'Missing languages'], $rows);
        $this->newLine();
        $this->info("Total: {$guidings->count()} guiding(s) with missing translations.");
        $this->info('Run: php artisan guiding:translate --missing-only [--language=en,de] to translate them.');
    }

    /**
     * Display translation results
     */
    private function displayResults(array $results, bool $dryRun): void
    {
        $this->info('Translation Results:');
        $this->table(
            ['Status', 'Count'],
            [
                [$dryRun ? 'Would Translate' : 'Translated', $results['translated']],
                ['Skipped', $results['skipped']],
                ['Failed', $results['failed']]
            ]
        );

        if ($results['translated'] > 0) {
            $message = $dryRun 
                ? "Dry run completed. {$results['translated']} translations would be performed."
                : "Successfully completed {$results['translated']} translation(s).";
            $this->info($message);
        }

        if ($results['failed'] > 0) {
            $this->warn("WARNING: {$results['failed']} translation(s) failed. Check logs for details.");
        }
    }
}
