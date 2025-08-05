<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Vacation;
use App\Models\Language;
use Illuminate\Support\Facades\Cache;

class DebugVacationTranslationCommand extends Command
{
    protected $signature = 'debug:vacation-translation {vacation_id} {item_id} {relation_type} {language}';
    protected $description = 'Debug specific vacation relation translation';

    public function handle()
    {
        $vacationId = $this->argument('vacation_id');
        $itemId = $this->argument('item_id');
        $relationType = $this->argument('relation_type');
        $language = $this->argument('language');
        
        $vacation = Vacation::find($vacationId);
        if (!$vacation) {
            $this->error("Vacation not found");
            return 1;
        }
        
        $this->info("=== DEBUG VACATION RELATION TRANSLATION ===");
        $this->line("Vacation ID: " . $vacationId);
        $this->line("Item ID: " . $itemId);
        $this->line("Relation Type: " . $relationType);
        $this->line("Language: " . $language);
        $this->line("Vacation source language: " . ($vacation->language ?: 'NULL'));
        
        // Check the language comparison
        $languageMatch = $vacation->language === $language;
        $this->line("Language match (vacation->language === language): " . ($languageMatch ? 'TRUE' : 'FALSE'));
        
        if ($languageMatch) {
            $this->error("❌ Will return original item because languages match");
            return 0;
        }
        
        // Check database translation
        $translation = Language::where([
            'source_id' => $itemId,
            'type' => 'vacation_' . $relationType,
            'language' => $language
        ])->first();
        
        if (!$translation) {
            $this->error("❌ No translation found in database");
            return 0;
        }
        
        $this->info("✅ Translation found (ID: " . $translation->id . ")");
        $this->line("Translation title: " . ($translation->title ?? 'N/A'));
        
        $translatedData = json_decode($translation->json_data, true);
        $this->line("Translated data keys: " . implode(', ', array_keys($translatedData)));
        
        if (isset($translatedData['title'])) {
            $this->line("Translated title from JSON: " . $translatedData['title']);
        }
        
        // Check cache
        $cacheKey = 'vacation_relation_translation_' . $itemId . '_' . $relationType . '_' . $language;
        $this->line("Cache key: " . $cacheKey);
        
        $cachedData = Cache::get($cacheKey);
        if ($cachedData) {
            $this->line("Cached data title: " . ($cachedData->title ?? 'N/A'));
        } else {
            $this->line("No cached data");
        }
        
        return 0;
    }
}