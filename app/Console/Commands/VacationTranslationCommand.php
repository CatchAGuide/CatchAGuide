<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Vacation;
use App\Services\Translation\VacationTranslationService;
use App\Services\AdminChangeTracker;
use Illuminate\Support\Facades\DB;

class VacationTranslationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vacation:translate 
                            {--vacation=* : Specific vacation IDs to translate}
                            {--language=* : Target languages to translate to (e.g., en,es,fr)}
                            {--detect-language : Detect and update source language for all vacations}
                            {--force : Force retranslation even if translations exist}
                            {--relations : Also translate related models (accommodations, boats, etc.)}
                            {--admin-changes : Only process vacations with admin changes}
                            {--dry-run : Show what would be translated without actually doing it}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Translate vacation content using Gemini AI. Defaults to EN and DE languages. Use --admin-changes to only process vacations with admin changes, or --vacation=1,2,3 for specific IDs.';

    private VacationTranslationService $translationService;
    private AdminChangeTracker $changeTracker;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(VacationTranslationService $translationService, AdminChangeTracker $changeTracker)
    {
        parent::__construct();
        $this->translationService = $translationService;
        $this->changeTracker = $changeTracker;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting Vacation Translation Process...');
        $this->newLine();

        // Handle language detection first if requested
        if ($this->option('detect-language')) {
            $this->handleLanguageDetection();
        }

        // Get target languages (defaults to EN and DE if not specified)
        $targetLanguages = $this->getTargetLanguages();

        // Get vacations to process
        $vacations = $this->getVacationsToProcess();
        if ($vacations->isEmpty()) {
            $this->warn('No vacations found to process.');
            return 0;
        }

        $this->info("Processing {$vacations->count()} vacation(s) for languages: " . implode(', ', $targetLanguages));
        $this->newLine();

        $dryRun = $this->option('dry-run');
        $force = $this->option('force');
        $includeRelations = $this->option('relations');

        $progressBar = $this->output->createProgressBar($vacations->count() * count($targetLanguages));
        $progressBar->start();

        $results = [
            'translated' => 0,
            'skipped' => 0,
            'failed' => 0
        ];

        foreach ($vacations as $vacation) {
            foreach ($targetLanguages as $targetLanguage) {
                $progressBar->advance();

                try {
                    // Skip if same language as source
                    if ($vacation->language === $targetLanguage) {
                        $results['skipped']++;
                        continue;
                    }

                    // Check if translation needed
                    if (!$force && !$this->translationService->hasSignificantChanges($vacation, $targetLanguage)) {
                        // Check if translation exists
                        $existing = $this->translationService->getTranslatedVacation($vacation, $targetLanguage);
                        if ($existing) {
                            $results['skipped']++;
                            continue;
                        }
                    }

                    if ($dryRun) {
                        $this->line("\nWould translate: {$vacation->title} (ID: {$vacation->id}) to {$targetLanguage}");
                        $results['translated']++;
                        continue;
                    }

                    // Perform translation
                    $success = $this->translationService->translateVacation($vacation, $targetLanguage);
                    
                    if ($success) {
                        // Translate relations if requested
                        if ($includeRelations) {
                            $this->translationService->translateVacationRelations($vacation, $targetLanguage);
                        }
                        
                        // Update content timestamp
                        $this->translationService->markContentUpdated($vacation);
                        
                        $results['translated']++;
                    } else {
                        $results['failed']++;
                    }

                } catch (\Exception $e) {
                    $results['failed']++;
                    $this->error("\nFailed to translate vacation {$vacation->id} to {$targetLanguage}: {$e->getMessage()}");
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
     * Handle language detection for all vacations
     */
    private function handleLanguageDetection(): void
    {
        $this->info('Detecting languages for all vacations...');
        
        $vacations = Vacation::whereNull('language')
                            ->orWhere('language', '')
                            ->get();

        if ($vacations->isEmpty()) {
            $this->info('All vacations already have language detected.');
            return;
        }

        $progressBar = $this->output->createProgressBar($vacations->count());
        $progressBar->start();

        $detected = 0;
        foreach ($vacations as $vacation) {
            try {
                $detectedLanguage = $this->translationService->detectVacationLanguage($vacation);
                $vacation->update(['language' => $detectedLanguage]);
                $detected++;
            } catch (\Exception $e) {
                $this->error("Failed to detect language for vacation {$vacation->id}: {$e->getMessage()}");
            }
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
        $this->info("Language detection completed. Detected language for {$detected} vacation(s).");
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
     * Get vacations to process based on options
     */
    private function getVacationsToProcess()
    {
        $vacationIds = $this->option('vacation');
        
        // If specific vacation IDs are provided, use those
        if (!empty($vacationIds)) {
            return Vacation::whereIn('id', $vacationIds)
                          ->where('status', true)
                          ->with(['accommodations', 'boats', 'packages', 'guidings', 'extras'])
                          ->get();
        }

        // If admin-changes option is used, get only vacations with admin changes
        if ($this->option('admin-changes')) {
            $vacationIds = $this->changeTracker->getVacationsNeedingTranslation();
            
            if (empty($vacationIds)) {
                return collect(); // Return empty collection
            }
            
            return Vacation::whereIn('id', $vacationIds)
                          ->where('status', true)
                          ->with(['accommodations', 'boats', 'packages', 'guidings', 'extras'])
                          ->get();
        }

        // Default: get all active vacations
        return Vacation::where('status', true)
                      ->with(['accommodations', 'boats', 'packages', 'guidings', 'extras'])
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
