<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Vacation;
use App\Models\Language;

class TestVacationTranslationRetrievalCommand extends Command
{
    protected $signature = 'test:vacation-translation-retrieval {vacation_id=1}';
    protected $description = 'Test if vacation translations are being retrieved correctly';

    public function handle()
    {
        $vacationId = $this->argument('vacation_id');
        $this->info("Testing translation retrieval for vacation ID: {$vacationId}");
        
        // Get the vacation
        $vacation = Vacation::find($vacationId);
        if (!$vacation) {
            $this->error("Vacation {$vacationId} not found");
            return 1;
        }
        
        $this->line("Vacation: {$vacation->title}");
        $this->line("Source language: " . ($vacation->language ?: 'NOT SET'));
        $this->line("Current app locale: " . app()->getLocale());
        
        // Check if translations exist in database
        $this->info("\n--- Checking database for translations ---");
        $translations = Language::where('source_id', $vacationId)
                               ->where('type', 'vacations')
                               ->get();
        
        if ($translations->isEmpty()) {
            $this->error("No translations found in database");
            return 1;
        }
        
        foreach ($translations as $translation) {
            $this->line("Found translation: {$translation->language} (updated: {$translation->updated_at})");
            $this->line("Title: " . ($translation->title ?: 'NOT SET'));
            
            // Show first 100 characters of JSON data
            $jsonData = $translation->json_data;
            if ($jsonData) {
                $this->line("JSON data (first 100 chars): " . substr($jsonData, 0, 100) . '...');
            } else {
                $this->error("JSON data is empty");
            }
        }
        
        // Test the getTranslation method
        $this->info("\n--- Testing getTranslation method ---");
        $languages = ['en', 'de'];
        
        foreach ($languages as $lang) {
            $translation = $vacation->getTranslation($lang);
            if ($translation) {
                $this->line("✅ getTranslation('{$lang}') returned translation ID: {$translation->id}");
            } else {
                $this->line("❌ getTranslation('{$lang}') returned null");
            }
        }
        
        // Test the getTranslatedData method
        $this->info("\n--- Testing getTranslatedData method ---");
        foreach ($languages as $lang) {
            $this->line("Testing language: {$lang}");
            $translatedData = $vacation->getTranslatedData($lang);
            
            if ($translatedData) {
                $this->line("✅ getTranslatedData('{$lang}') returned data");
                $this->line("Title: " . ($translatedData->title ?? 'NOT SET'));
                $this->line("Description length: " . (isset($translatedData->surroundings_description) ? strlen($translatedData->surroundings_description) : 0) . ' characters');
                
                // Check specific translated fields
                $fieldsToCheck = ['best_travel_times', 'target_fish', 'travel_included', 'included_services'];
                foreach ($fieldsToCheck as $field) {
                    if (isset($translatedData->$field)) {
                        $value = is_array($translatedData->$field) ? implode(', ', $translatedData->$field) : $translatedData->$field;
                        $this->line("{$field}: " . substr($value, 0, 50) . (strlen($value) > 50 ? '...' : ''));
                    }
                }
            } else {
                $this->error("❌ getTranslatedData('{$lang}') returned null");
            }
        }
        
        // Clear cache and test again
        $this->info("\n--- Clearing cache and testing again ---");
        \Cache::flush();
        
        $translatedData = $vacation->getTranslatedData('en');
        if ($translatedData) {
            $this->line("✅ After cache clear - getTranslatedData('en') works");
            $this->line("Title: " . ($translatedData->title ?? 'NOT SET'));
        } else {
            $this->error("❌ After cache clear - getTranslatedData('en') still returns null");
        }
        
        return 0;
    }
}