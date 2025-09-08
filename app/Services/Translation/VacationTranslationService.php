<?php

namespace App\Services\Translation;

use App\Models\Vacation;
use App\Models\Language;
use App\Helpers\TranslationHelper;
use App\Services\AdminChangeTracker;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class VacationTranslationService
{
    private GeminiTranslationService $translator;

    public function __construct()
    {
        $this->translator = new GeminiTranslationService();
    }

    /**
     * Detect the language of vacation content using Gemini
     */
    public function detectVacationLanguage(Vacation $vacation): string
    {
        try {
            // Get sample text from vacation for language detection
            $sampleTexts = array_filter([
                $vacation->title,
                $vacation->surroundings_description,
                $vacation->location,
                $vacation->travel_included
            ]);

            if (empty($sampleTexts)) {
                return 'de'; // Default fallback
            }

            $combinedText = implode('. ', array_slice($sampleTexts, 0, 3));
            
            $prompt = "Analyze the following text and determine what language it is written in. 
                       Respond with only the 2-letter ISO language code (e.g., 'de' for German, 'en' for English, 'es' for Spanish, etc.).
                       
                       Text: {$combinedText}";

            $detectedLanguage = $this->translator->translate($prompt);
            $detectedLanguage = strtolower(trim($detectedLanguage));

            // Validate the detected language is a 2-letter code
            if (preg_match('/^[a-z]{2}$/', $detectedLanguage)) {
                return $detectedLanguage;
            }

            return 'de'; // Default fallback
        } catch (\Exception $e) {
            Log::error('Language detection failed for vacation ID: ' . $vacation->id, [
                'error' => $e->getMessage()
            ]);
            return 'de';
        }
    }

    /**
     * Check if vacation content has changed significantly using change tracking
     */
    public function hasSignificantChanges(Vacation $vacation, string $targetLanguage): bool
    {
        $changeTracker = new AdminChangeTracker();
        $changedFields = $changeTracker->getChangedFieldsForVacation($vacation);
        
        // If there are any changed fields, it has significant changes
        return !empty($changedFields);
    }

    /**
     * Get only the fields that have changed since the last translation
     */
    public function getChangedTranslatableFields(Vacation $vacation, string $targetLanguage): array
    {
        $changeTracker = new AdminChangeTracker();
        return $changeTracker->getChangedFieldsForVacation($vacation);
    }



    /**
     * Translate vacation to target language and save to Language table
     */
    public function translateVacation(Vacation $vacation, string $targetLanguage, bool $force = false): bool
    {
        try {
            // Don't translate if target language is same as source
            if ($vacation->language === $targetLanguage) {
                return true;
            }

            // Check if translation already exists and is up to date
            $existingTranslation = Language::where([
                'source_id' => $vacation->id,
                'type' => 'vacations',
                'language' => $targetLanguage
            ])->first();

            if ($existingTranslation && 
                $vacation->content_updated_at &&
                $existingTranslation->updated_at >= $vacation->content_updated_at &&
                !$this->hasSignificantChanges($vacation, $targetLanguage)) {
                return true; // Translation is up to date
            }

            // Also check if translation is recent (within 24 hours) and content hasn't changed
            if ($existingTranslation && 
                $existingTranslation->updated_at >= now()->subHours(24) &&
                !$this->hasSignificantChanges($vacation, $targetLanguage)) {
                return true; // Recent translation, no need to retranslate
            }

            // Get fields to translate
            if ($force || !$existingTranslation) {
                // For force mode or initial translation, translate all translatable fields
                $translatableFields = [
                    'title', 'surroundings_description', 'best_travel_times',
                    'target_fish', 'water_distance', 'shopping_distance', 'travel_included', 'travel_options', 'included_services', 'airport_distance', 'additional_services'
                ];
                $fieldsToProcess = $translatableFields;
            } else {
                // Only translate changed fields
                $fieldsToProcess = $this->getChangedTranslatableFields($vacation, $targetLanguage);
            }
            
            // Prepare data for translation
            $dataToTranslate = [];
            foreach ($fieldsToProcess as $field) {
                $value = $vacation->$field;
                if (!empty($value)) {
                    if (is_array($value) || $this->isJsonString($value)) {
                        // Handle JSON arrays
                        $decoded = is_array($value) ? $value : json_decode($value, true);
                        if (is_array($decoded)) {
                            $dataToTranslate[$field] = $decoded;
                        } else {
                            $dataToTranslate[$field] = $value;
                        }
                    } else {
                        $dataToTranslate[$field] = $value;
                    }
                }
            }

            if (empty($dataToTranslate)) {
                return false;
            }

            // Translate using batch translation
            // $translatedData = TranslationHelper::batchTranslate(
            //     $dataToTranslate,
            //     $targetLanguage,
            //     $vacation->language,
            //     'vacations'
            // );

            // Save or update translation
            if ($existingTranslation) {
                // Merge with existing translation data
                $existingData = json_decode($existingTranslation->json_data, true) ?? [];
                // $mergedData = array_merge($existingData, $translatedData);
                
                $existingTranslation->update([
                    'title' => $translatedData['title'] ?? $existingTranslation->title,
                    'json_data' => json_encode($mergedData),
                    'updated_at' => now()
                ]);
            } else {
                Language::create([
                    'source_id' => $vacation->id,
                    'type' => 'vacations',
                    'language' => $targetLanguage,
                    'title' => $translatedData['title'] ?? null,
                    // 'json_data' => json_encode($translatedData)
                ]);
            }

            // Mark the vacation as translated to this language
            $changeTracker = new AdminChangeTracker();
            $changeTracker->markVacationTranslated($vacation, $targetLanguage);

            // Clear relevant caches
            Cache::forget('vacation_translation_' . $vacation->id . '_' . $targetLanguage);

            return true;

        } catch (\Exception $e) {
            Log::error('Vacation translation failed', [
                'vacation_id' => $vacation->id,
                'target_language' => $targetLanguage,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get translated vacation data
     */
    public function getTranslatedVacation(Vacation $vacation, string $targetLanguage): ?array
    {
        if ($vacation->language === $targetLanguage) {
            return null; // Return null for same language, use original
        }

        $cacheKey = 'vacation_translation_' . $vacation->id . '_' . $targetLanguage;
        
        return Cache::remember($cacheKey, 3600, function() use ($vacation, $targetLanguage) {
            $translation = Language::where([
                'source_id' => $vacation->id,
                'type' => 'vacations',
                'language' => $targetLanguage
            ])->first();

            if ($translation && $translation->json_data) {
                return json_decode($translation->json_data, true);
            }

            return null;
        });
    }

    /**
     * Translate vacation's related models (accommodations, boats, etc.)
     */
    public function translateVacationRelations(Vacation $vacation, string $targetLanguage, bool $force = false): bool
    {
        try {
            $relations = ['accommodations', 'boats', 'packages', 'guidings', 'extras'];
            
            foreach ($relations as $relationName) {
                $relationItems = $vacation->$relationName;
                
                            foreach ($relationItems as $item) {
                $this->translateRelationItem($item, $relationName, $targetLanguage, $vacation->language, $force);
            }
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Vacation relations translation failed', [
                'vacation_id' => $vacation->id,
                'target_language' => $targetLanguage,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Translate individual relation item
     */
    private function translateRelationItem($item, string $relationType, string $targetLanguage, string $sourceLanguage, bool $force = false): void
    {
        $relationTypeClean = rtrim($relationType, 's'); // Remove 's' from plural
        
        $existingTranslation = Language::where([
            'source_id' => $item->id,
            'type' => 'vacation_' . $relationTypeClean,
            'language' => $targetLanguage
        ])->first();

        // Skip if translation exists and is recent (unless forced)
        if (!$force && $existingTranslation && $existingTranslation->updated_at > $item->updated_at) {
            return;
        }

        // Prepare data based on model type
        $dataToTranslate = $this->getTranslatableFieldsForRelation($item, $relationTypeClean);
        
        if (empty($dataToTranslate)) {
            return;
        }

        // $translatedData = TranslationHelper::batchTranslate(
        //     $dataToTranslate,
        //     $targetLanguage,
        //     $sourceLanguage,
        //     'vacation_relations'
        // );

        if ($existingTranslation) {
            $existingTranslation->update([
                'title' => $translatedData['title'] ?? $translatedData['description'] ?? null,
                // 'json_data' => json_encode($translatedData)
            ]);
        } else {
            Language::create([
                'source_id' => $item->id,
                'type' => 'vacation_' . $relationTypeClean,
                'language' => $targetLanguage,
                'title' => $translatedData['title'] ?? $translatedData['description'] ?? null,
                // 'json_data' => json_encode($translatedData)
            ]);
        }
    }

    /**
     * Get translatable fields for different relation types
     */
    private function getTranslatableFieldsForRelation($item, string $relationType): array
    {
        $dataToTranslate = [];

        switch ($relationType) {
            case 'accommodation':
            case 'boat':
            case 'package':
            case 'guiding':
                // These models have: title, description, dynamic_fields
                if (!empty($item->title)) {
                    $dataToTranslate['title'] = $item->title;
                }
                if (!empty($item->description)) {
                    $dataToTranslate['description'] = $item->description;
                }
                
                // Handle dynamic fields
                if ($item->dynamic_fields) {
                    $dynamicFields = is_string($item->dynamic_fields) ? json_decode($item->dynamic_fields, true) : $item->dynamic_fields;
                    if (is_array($dynamicFields)) {
                        foreach ($dynamicFields as $key => $value) {
                            // Skip numeric fields like prices, capacity numbers, etc.
                            if ($key !== 'prices' && is_string($value) && !empty($value) && !is_numeric($value)) {
                                $dataToTranslate['dynamic_' . $key] = $value;
                            }
                        }
                    }
                }
                break;

            case 'extra':
                // VacationExtra has: type, description, price
                if (!empty($item->description)) {
                    $dataToTranslate['description'] = $item->description;
                }
                // Type field could be translatable (like "per_person", "per_day", etc.)
                if (!empty($item->type) && is_string($item->type)) {
                    $dataToTranslate['type'] = $item->type;
                }
                break;
                
            default:
                // Generic fallback - try to get title and description
                if (isset($item->title) && !empty($item->title)) {
                    $dataToTranslate['title'] = $item->title;
                }
                if (isset($item->description) && !empty($item->description)) {
                    $dataToTranslate['description'] = $item->description;
                }
                break;
        }

        return $dataToTranslate;
    }

    /**
     * Check if string is valid JSON
     */
    private function isJsonString(string $string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Update vacation's content_updated_at timestamp
     */
    public function markContentUpdated(Vacation $vacation): void
    {
        $vacation->update(['content_updated_at' => now()]);
    }

    /**
     * Batch translate vacations that have changes
     */
    public function batchTranslateChangedVacations(array $languages, Carbon $since = null): array
    {
        $results = [];
        $changeTracker = new AdminChangeTracker();

        // Get vacation IDs that need translation
        $vacationIds = $changeTracker->getVacationsNeedingTranslation();

        Log::info('Found vacations needing translation', [
            'count' => count($vacationIds),
            'vacation_ids' => $vacationIds
        ]);

        foreach ($vacationIds as $vacationId) {
            $vacation = Vacation::find($vacationId);
            if (!$vacation) {
                continue;
            }

            $vacationResults = [];
            foreach ($languages as $language) {
                // Skip if same as source language
                if ($vacation->language === $language) {
                    continue;
                }

                try {
                    $success = $this->translateVacation($vacation, $language, false);
                    $vacationResults[$language] = $success;
                    
                    if ($success) {
                        Log::info('Vacation translated successfully', [
                            'vacation_id' => $vacation->id,
                            'language' => $language,
                            'changed_fields' => $changeTracker->getChangedFieldsForVacation($vacation)
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('Error translating vacation', [
                        'vacation_id' => $vacation->id,
                        'language' => $language,
                        'error' => $e->getMessage()
                    ]);
                    $vacationResults[$language] = false;
                }
            }
            
            $results[$vacationId] = $vacationResults;
        }

        return $results;
    }

    /**
     * Get translation statistics
     */
    public function getTranslationStats(): array
    {
        $changeTracker = new AdminChangeTracker();
        return $changeTracker->getTranslationStats();
    }
} 