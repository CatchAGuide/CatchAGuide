<?php

namespace App\Services\Translation;

use App\Models\Vacation;
use App\Models\Language;
use App\Services\AdminChangeTracker;
use App\Services\Translation\Concerns\HandlesTranslatableListFields;
use App\Services\Translation\Support\FishingCopyGoogleTranslator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class VacationTranslationService
{
    use HandlesTranslatableListFields;

    /**
     * Scalar (plain string) translatable fields. NOTE: AdminChangeTracker's field list
     * (accommodation_description, boat_description, basic_fishing_description, catering_info,
     * amenities, equipment) is stale — migration 2025_01_13_135643_remove_fields_to_t_vations
     * dropped every one of those columns from `vacations`. This list matches the columns that
     * actually exist today.
     */
    private const SCALAR_FIELDS = [
        'title',
        'surroundings_description',
        'water_distance',
        'shopping_distance',
        'travel_included',
        'airport_distance',
    ];

    /**
     * JSON-array translatable fields (flattened into indexed keys for translation, then
     * reconstructed).
     */
    private const LIST_FIELDS = [
        'best_travel_times',
        'target_fish',
        'travel_options',
        'included_services',
        'additional_services',
    ];

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
     * Check if the vacation's translatable content has changed since the stored translation was
     * generated, using the same content-hash approach as ListingTranslationService/
     * GuidingTranslationService rather than the admin change-history log.
     */
    public function hasSignificantChanges(Vacation $vacation, string $targetLanguage): bool
    {
        $translation = Language::where([
            'source_id' => $vacation->id,
            'type' => 'vacations',
            'language' => $targetLanguage,
        ])->first();

        if (! $translation) {
            return true;
        }

        $currentHash = md5(serialize($this->getTranslatableFields($vacation)));

        return $translation->content !== $currentHash;
    }

    /**
     * Whether this vacation needs translation work for a target language (missing or outdated).
     */
    public function needsTranslationUpdate(Vacation $vacation, string $targetLanguage, ?string $fromLanguage = null): bool
    {
        $fromLanguage ??= $vacation->language ?: 'de';

        if ($fromLanguage === $targetLanguage) {
            return false;
        }

        return $this->hasSignificantChanges($vacation, $targetLanguage);
    }

    /**
     * @return array<string, string>
     */
    public function getTranslatableFields(Vacation $vacation): array
    {
        $fields = $this->collectScalarFields($vacation, self::SCALAR_FIELDS);

        foreach (self::LIST_FIELDS as $listField) {
            $fields = array_merge($fields, $this->collectListField($vacation, $listField));
        }

        return $fields;
    }

    /**
     * @param  array<string, string>  $translatedFields
     * @return array<string, mixed>
     */
    private function reconstructFields(Vacation $vacation, array $translatedFields): array
    {
        $reconstructed = [];

        foreach (self::LIST_FIELDS as $listField) {
            $decoded = $this->decodeValue($vacation->{$listField} ?? null);

            if (! is_array($decoded)) {
                continue;
            }

            $reconstructed[$listField] = $this->reconstructIndexedArray($decoded, $listField, $translatedFields);
        }

        foreach ($translatedFields as $key => $value) {
            if (! preg_match('/_\d+$/', $key)) {
                $reconstructed[$key] = $value;
            }
        }

        return $reconstructed;
    }

    /**
     * Translate vacation to target language and save to Language table. Uses the free Google
     * engine by default; pass $engine = 'gemini' to force the paid engine for a one-off
     * higher-quality re-translation.
     */
    public function translateVacation(Vacation $vacation, string $targetLanguage, bool $force = false, ?string $engine = null): bool
    {
        try {
            $sourceLanguage = $vacation->language ?: 'de';

            if ($sourceLanguage === $targetLanguage) {
                return true;
            }

            if (! $force && ! $this->hasSignificantChanges($vacation, $targetLanguage)) {
                return true;
            }

            $fields = $this->getTranslatableFields($vacation);

            if ($fields === []) {
                return true;
            }

            $translatedFields = $engine === 'gemini'
                ? $this->translateFieldsWithGemini($fields, $targetLanguage, $sourceLanguage)
                : (new FishingCopyGoogleTranslator())->batchTranslate($fields, $targetLanguage, $sourceLanguage);

            $storedFields = $this->reconstructFields($vacation, $translatedFields);

            Language::updateOrCreate(
                [
                    'source_id' => $vacation->id,
                    'type' => 'vacations',
                    'language' => $targetLanguage,
                ],
                [
                    'title' => $storedFields['title'] ?? $vacation->title ?? null,
                    'json_data' => $storedFields,
                    'content' => md5(serialize($fields)),
                    'updated_at' => now(),
                ]
            );

            // Mark the vacation as translated to this language (kept for the existing
            // AdminChangeTracker-backed stats dashboard; no longer used as the translation gate).
            (new AdminChangeTracker())->markVacationTranslated($vacation, $targetLanguage);

            Cache::forget('vacation_translation_' . $vacation->id . '_' . $targetLanguage);

            return true;
        } catch (\Throwable $e) {
            Log::error('Vacation translation failed', [
                'vacation_id' => $vacation->id,
                'target_language' => $targetLanguage,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * @param  array<string, string>  $fields
     * @return array<string, string>
     */
    private function translateFieldsWithGemini(array $fields, string $toLanguage, string $fromLanguage): array
    {
        try {
            return TranslationEngineFactory::make('gemini')->batchTranslate($fields, $toLanguage, $fromLanguage);
        } catch (\Throwable $e) {
            Log::error('Gemini vacation translation failed, falling back to Google Translate', [
                'error' => $e->getMessage(),
            ]);

            return (new FishingCopyGoogleTranslator())->batchTranslate($fields, $toLanguage, $fromLanguage);
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