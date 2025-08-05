<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Vacation;
use App\Models\Language;

class DiagnoseVacationDisplayCommand extends Command
{
    protected $signature = 'diagnose:vacation-display {vacation_id=1}';
    protected $description = 'Diagnose why vacation translations are not displaying';

    public function handle()
    {
        $vacationId = $this->argument('vacation_id');
        
        $this->info("=== VACATION TRANSLATION DISPLAY DIAGNOSIS ===");
        $this->line("Vacation ID: {$vacationId}");
        
        // Step 1: Check if vacation exists
        $vacation = Vacation::with(['accommodations', 'boats', 'packages', 'guidings', 'extras'])
                           ->find($vacationId);
        
        if (!$vacation) {
            $this->error("❌ Vacation not found");
            return 1;
        }
        
        $this->info("✅ Vacation found: {$vacation->title}");
        $this->line("   Source language: " . ($vacation->language ?: 'NOT SET'));
        
        // Step 2: Check relation counts
        $this->info("\n=== RELATION COUNTS ===");
        $this->line("Accommodations: " . $vacation->accommodations->count());
        $this->line("Boats: " . $vacation->boats->count());
        $this->line("Packages: " . $vacation->packages->count());
        $this->line("Guidings: " . $vacation->guidings->count());
        $this->line("Extras: " . $vacation->extras->count());
        
        // Step 3: Check database translations
        $this->info("\n=== DATABASE TRANSLATIONS ===");
        $translationTypes = [
            'vacation_accommodation',
            'vacation_boat',
            'vacation_package', 
            'vacation_guiding',
            'vacation_extra'
        ];
        
        foreach ($translationTypes as $type) {
            $count = Language::where('type', $type)->count();
            $enCount = Language::where('type', $type)->where('language', 'en')->count();
            $this->line("{$type}: {$count} total, {$enCount} EN");
        }
        
        // Step 4: Check specific accommodation translations
        if ($vacation->accommodations->count() > 0) {
            $this->info("\n=== ACCOMMODATION TRANSLATION DETAILS ===");
            foreach ($vacation->accommodations as $index => $accommodation) {
                $this->line("\n--- Accommodation #{$index + 1} (ID: {$accommodation->id}) ---");
                $this->line("Title: " . ($accommodation->title ?? 'N/A'));
                
                // Check for EN translation
                $enTranslation = Language::where([
                    'source_id' => $accommodation->id,
                    'type' => 'vacation_accommodation',
                    'language' => 'en'
                ])->first();
                
                if ($enTranslation) {
                    $this->info("✅ EN translation found (ID: {$enTranslation->id})");
                    $this->line("   Title: " . ($enTranslation->title ?? 'N/A'));
                    $this->line("   Updated: {$enTranslation->updated_at}");
                    
                    $translatedData = json_decode($enTranslation->json_data, true);
                    if ($translatedData) {
                        $this->line("   Translated keys: " . implode(', ', array_keys($translatedData)));
                        
                        // Show first few characters of translated title and description
                        if (isset($translatedData['title'])) {
                            $this->line("   Translated title: " . substr($translatedData['title'], 0, 50) . '...');
                        }
                        if (isset($translatedData['description'])) {
                            $this->line("   Translated desc: " . substr($translatedData['description'], 0, 50) . '...');
                        }
                    }
                } else {
                    $this->error("❌ No EN translation found");
                }
            }
        }
        
        // Step 5: Test getTranslatedAccommodations method
        $this->info("\n=== TESTING getTranslatedAccommodations() ===");
        
        // Test with different locales
        foreach (['en', 'de'] as $locale) {
            $this->line("\n--- Testing locale: {$locale} ---");
            app()->setLocale($locale);
            $this->line("App locale set to: " . app()->getLocale());
            
            try {
                $translatedAccommodations = $vacation->getTranslatedAccommodations($locale);
                $this->line("Returned count: " . $translatedAccommodations->count());
                
                if ($translatedAccommodations->count() > 0) {
                    $first = $translatedAccommodations->first();
                    $this->line("First item title: " . ($first->title ?? 'N/A'));
                    $this->line("First item desc: " . substr($first->description ?? 'N/A', 0, 50) . '...');
                    
                    // Check dynamic fields
                    if (isset($first->dynamic_fields)) {
                        $dynamicFields = is_string($first->dynamic_fields) ? 
                            json_decode($first->dynamic_fields, true) : 
                            $first->dynamic_fields;
                        
                        if (is_array($dynamicFields)) {
                            $this->line("Dynamic fields keys: " . implode(', ', array_keys($dynamicFields)));
                        } else {
                            $this->line("Dynamic fields type: " . gettype($first->dynamic_fields));
                            $this->line("Dynamic fields value: " . var_export($first->dynamic_fields, true));
                        }
                    }
                } else {
                    $this->error("❌ No translated accommodations returned");
                }
            } catch (\Exception $e) {
                $this->error("❌ Error in getTranslatedAccommodations: " . $e->getMessage());
                $this->line("Stack trace: " . $e->getTraceAsString());
            }
        }
        
        // Step 6: Check cache
        $this->info("\n=== CACHE CHECK ===");
        if ($vacation->accommodations->count() > 0) {
            $firstAccommodation = $vacation->accommodations->first();
            $cacheKey = 'vacation_relation_translation_' . $firstAccommodation->id . '_accommodation_en';
            $this->line("Cache key: {$cacheKey}");
            
            $cachedData = \Cache::get($cacheKey);
            if ($cachedData) {
                $this->info("✅ Cache contains data");
                $this->line("Cached title: " . ($cachedData->title ?? 'N/A'));
            } else {
                $this->line("❌ No cached data found");
            }
        }
        
        // Step 7: Test controller data
        $this->info("\n=== CONTROLLER DATA SIMULATION ===");
        app()->setLocale('en');
        $this->line("Simulating VacationsController::show with locale 'en'");
        
        try {
            $translatedVacation = $vacation->getTranslatedData('en');
            $translatedAccommodations = $vacation->getTranslatedAccommodations('en');
            $translatedBoats = $vacation->getTranslatedBoats('en');
            $translatedPackages = $vacation->getTranslatedPackages('en');
            $translatedGuidings = $vacation->getTranslatedGuidings('en');
            $translatedExtras = $vacation->getTranslatedExtras('en');
            
            $this->line("Controller would pass to view:");
            $this->line("- translatedVacation->title: " . ($translatedVacation->title ?? 'N/A'));
            $this->line("- translatedAccommodations count: " . $translatedAccommodations->count());
            $this->line("- translatedBoats count: " . $translatedBoats->count());
            $this->line("- translatedPackages count: " . $translatedPackages->count());
            $this->line("- translatedGuidings count: " . $translatedGuidings->count());
            $this->line("- translatedExtras count: " . $translatedExtras->count());
            
        } catch (\Exception $e) {
            $this->error("❌ Error simulating controller: " . $e->getMessage());
        }
        
        return 0;
    }
}