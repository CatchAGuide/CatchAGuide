<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Guiding;
use App\Services\Translation\GuidingTranslationService;
use Illuminate\Support\Facades\DB;

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
                            {--detect-language : Detect and update source language for all guidings}
                            {--force : Force retranslation even if translations exist}
                            {--admin-changes : Only process guidings with admin changes}
                            {--dry-run : Show what would be translated without actually doing it}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Translate guiding content using Gemini AI. Defaults to EN and DE languages. Use --admin-changes to only process guidings with admin changes, or --guiding=1,2,3 for specific IDs.';

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
        }

        // Get target languages (defaults to EN and DE if not specified)
        $targetLanguages = $this->getTargetLanguages();

        // Get guidings to process
        $guidings = $this->getGuidingsToProcess();
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
     * Handle language detection for all guidings
     */
    private function handleLanguageDetection(): void
    {
        $this->info('Detecting languages for all guidings...');
        
        $guidings = Guiding::where('status', 1)->get();

        if ($guidings->isEmpty()) {
            $this->info('All guidings already have language detected.');
            return;
        }

        $progressBar = $this->output->createProgressBar($guidings->count());
        $progressBar->start();

        $detected = 0;
        foreach ($guidings as $guiding) {
            try {
                $detectedLanguage = $this->translationService->detectGuidingLanguage($guiding);
                $guiding->update(['language' => $detectedLanguage]);
                $detected++;
            } catch (\Exception $e) {
                $this->error("Failed to detect language for guiding {$guiding->id}: {$e->getMessage()}");
            }
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
        $this->info("Language detection completed. Detected language for {$detected} guiding(s).");
        $this->newLine();
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
     */
    private function getGuidingsToProcess()
    {
        $guidingIds = $this->option('guiding');
        
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

        // Default: get all active guidings
        return Guiding::where('status', 1)
                      ->with(['user', 'guidingTargets', 'guidingMethods', 'guidingWaters'])
                      ->get();
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