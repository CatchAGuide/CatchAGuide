<?php

namespace App\Services\Translation;

use App\Models\Guiding;
use App\Models\Language;
use App\Helpers\TranslationHelper;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Stichoza\GoogleTranslate\GoogleTranslate;

class GuidingTranslationService
{
    private GeminiTranslationService $translator;

    public function __construct()
    {
        $this->translator = new GeminiTranslationService();
    }

    /**
     * Detect the language of guiding content using Gemini
     */
    public function detectGuidingLanguage(Guiding $guiding): string
    {
        try {
            // Get sample text from guiding for language detection
            $sampleTexts = array_filter([
                $guiding->title,
                $guiding->description,
                $guiding->additional_information,
                $guiding->meeting_point,
                $guiding->desc_course_of_action,
                $guiding->desc_meeting_point,
                $guiding->desc_starting_time,
                $guiding->desc_tour_unique,
                $guiding->inclusions,
                $guiding->requirements,
                $guiding->recommendations,
                $guiding->other_information,
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
            Log::error('Language detection failed for guiding', [
                'guiding_id' => $guiding->id,
                'error' => $e->getMessage()
            ]);
            return 'de'; // Default fallback
        }
    }

    /**
     * Translate guiding content to target language
     */
    public function translateGuiding(Guiding $guiding, string $targetLanguage): bool
    {
        try {
            $fromLanguage = $guiding->language ?? 'de';
            
            if ($fromLanguage === $targetLanguage) {
                return true; // No translation needed
            }

            // Prepare translatable fields
            $translatableFields = $this->getTranslatableFields($guiding);
            
            if (empty($translatableFields)) {
                return true; // Nothing to translate
            }

            // Use Google Translate for batch translation
            $translatedFields = $this->batchTranslateWithGoogle(
                $translatableFields,
                $targetLanguage,
                $fromLanguage
            );

            // Store the translation
            $this->storeTranslation($guiding, $targetLanguage, $translatedFields);

            return true;
        } catch (\Exception $e) {
            Log::error('Guiding translation failed', [
                'guiding_id' => $guiding->id,
                'target_language' => $targetLanguage,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get translatable fields from guiding
     */
    private function getTranslatableFields(Guiding $guiding): array
    {
        $fields = [];

        // Main content fields
        if ($guiding->title) {
            $fields['title'] = $guiding->title;
        }
        if ($guiding->description) {
            $fields['description'] = $guiding->description;
        }
        if ($guiding->additional_information) {
            $fields['additional_information'] = $guiding->additional_information;
        }
        if ($guiding->meeting_point) {
            $fields['meeting_point'] = $guiding->meeting_point;
        }

        // Description fields
        if ($guiding->desc_course_of_action) {
            $fields['desc_course_of_action'] = $guiding->desc_course_of_action;
        }
        if ($guiding->desc_meeting_point) {
            $fields['desc_meeting_point'] = $guiding->desc_meeting_point;
        }
        if ($guiding->desc_starting_time) {
            $fields['desc_starting_time'] = $guiding->desc_starting_time;
        }
        if ($guiding->desc_tour_unique) {
            $fields['desc_tour_unique'] = $guiding->desc_tour_unique;
        }

        // JSON fields that might contain translatable text
        if ($guiding->inclusions) {
            $inclusions = is_string($guiding->inclusions) ? json_decode($guiding->inclusions, true) : $guiding->inclusions;
            if (is_array($inclusions)) {
                foreach ($inclusions as $key => $value) {
                    if (is_string($value) && !is_numeric($value)) {
                        $fields["inclusions_{$key}"] = $value;
                    }
                }
            }
        }

        if ($guiding->requirements) {
            $requirements = is_string($guiding->requirements) ? json_decode($guiding->requirements, true) : $guiding->requirements;
            if (is_array($requirements)) {
                foreach ($requirements as $key => $value) {
                    if (is_array($value) && isset($value['value']) && is_string($value['value'])) {
                        $fields["requirements_{$key}"] = $value['value'];
                    } elseif (is_string($value) && !is_numeric($value)) {
                        $fields["requirements_{$key}"] = $value;
                    }
                }
            }
        }

        if ($guiding->recommendations) {
            $recommendations = is_string($guiding->recommendations) ? json_decode($guiding->recommendations, true) : $guiding->recommendations;
            if (is_array($recommendations)) {
                foreach ($recommendations as $key => $value) {
                    if (is_array($value) && isset($value['value']) && is_string($value['value'])) {
                        $fields["recommendations_{$key}"] = $value['value'];
                    } elseif (is_string($value) && !is_numeric($value)) {
                        $fields["recommendations_{$key}"] = $value;
                    }
                }
            }
        }

        if ($guiding->other_information) {
            $otherInfo = is_string($guiding->other_information) ? json_decode($guiding->other_information, true) : $guiding->other_information;
            if (is_array($otherInfo)) {
                foreach ($otherInfo as $key => $value) {
                    if (is_array($value) && isset($value['value']) && is_string($value['value'])) {
                        $fields["other_information_{$key}"] = $value['value'];
                    } elseif (is_string($value) && !is_numeric($value)) {
                        $fields["other_information_{$key}"] = $value;
                    }
                }
            }
        }

        if ($guiding->pricing_extra) {
            $pricingExtra = is_string($guiding->pricing_extra) ? json_decode($guiding->pricing_extra, true) : $guiding->pricing_extra;
            if (is_array($pricingExtra)) {
                foreach ($pricingExtra as $key => $value) {
                    if (is_array($value) && isset($value['name']) && is_string($value['name']) && !is_numeric($value['name'])) {
                        $fields["pricing_extra_{$key}_name"] = $value['name'];
                    }
                }
            }
        }

        return $fields;
    }

    /**
     * Store translation in database
     */
    private function storeTranslation(Guiding $guiding, string $targetLanguage, array $translatedFields): void
    {
        // Reconstruct JSON fields from individual translated fields
        $reconstructedFields = $this->reconstructJsonFields($guiding, $translatedFields);
        
        Language::updateOrCreate(
            [
                'source_id' => $guiding->id,
                'type' => 'guidings',
                'language' => $targetLanguage,
                'title' => $reconstructedFields['title'],
            ],
            [
                'json_data' => json_encode($reconstructedFields),
                'content' => md5(serialize($this->getTranslatableFields($guiding))),
                'updated_at' => now()
            ]
        );

        // Clear cache for this translation
        $cacheKey = 'guiding_translation_' . $guiding->id . '_' . $targetLanguage;
        Cache::forget($cacheKey);
    }

    /**
     * Reconstruct JSON fields from individual translated fields
     */
    private function reconstructJsonFields(Guiding $guiding, array $translatedFields): array
    {
        $reconstructed = [];
        
        // Process JSON fields that were broken down into individual translations
        $jsonFields = ['inclusions', 'requirements', 'recommendations', 'other_information', 'pricing_extra'];
        
        foreach ($jsonFields as $jsonField) {
            if ($guiding->$jsonField) {
                $original = is_string($guiding->$jsonField) ? json_decode($guiding->$jsonField, true) : $guiding->$jsonField;
                if (is_array($original)) {
                    $reconstructedJson = $original;
                    
                    foreach ($original as $key => $value) {
                        $translatedKey = "{$jsonField}_{$key}";
                        
                        if (isset($translatedFields[$translatedKey])) {
                            if (is_array($value) && isset($value['value'])) {
                                $reconstructedJson[$key]['value'] = $translatedFields[$translatedKey];
                            } else {
                                $reconstructedJson[$key] = $translatedFields[$translatedKey];
                            }
                            // Remove from main array to avoid duplication
                            unset($translatedFields[$translatedKey]);
                        }
                        
                        // Handle pricing_extra name specifically
                        if ($jsonField === 'pricing_extra') {
                            $nameKey = "{$jsonField}_{$key}_name";
                            if (isset($translatedFields[$nameKey])) {
                                $reconstructedJson[$key]['name'] = $translatedFields[$nameKey];
                                unset($translatedFields[$nameKey]);
                            }
                        }
                    }
                    
                    $reconstructed[$jsonField] = $reconstructedJson;
                }
            }
        }
        
        // Add remaining non-JSON fields
        $reconstructed = array_merge($reconstructed, $translatedFields);
        
        return $reconstructed;
    }

    /**
     * Check if guiding has significant changes requiring retranslation
     */
    public function hasSignificantChanges(Guiding $guiding, string $targetLanguage): bool
    {
        $translation = Language::where([
            'source_id' => $guiding->id,
            'type' => 'guidings',
            'language' => $targetLanguage
        ])->first();

        if (!$translation) {
            return true; // No translation exists
        }

        // Compare content hash
        $currentHash = md5(serialize($this->getTranslatableFields($guiding)));
        return $translation->content !== $currentHash;
    }

    /**
     * Get translated guiding data
     */
    public function getTranslatedGuiding(Guiding $guiding, string $targetLanguage): ?array
    {
        if ($guiding->language === $targetLanguage) {
            return null; // Return null for same language, use original
        }

        $cacheKey = 'guiding_translation_' . $guiding->id . '_' . $targetLanguage;
        
        return Cache::remember($cacheKey, 3600, function() use ($guiding, $targetLanguage) {
            $translation = Language::where([
                'source_id' => $guiding->id,
                'type' => 'guidings',
                'language' => $targetLanguage
            ])->first();

            if ($translation && $translation->json_data) {
                if (is_array($translation->json_data)) {
                    return $translation->json_data;
                }
                return json_decode($translation->json_data, true);
            }

            return null;
        });
    }

    /**
     * Store relation translations
     */
    private function storeRelationTranslations(Guiding $guiding, string $targetLanguage, array $translatedRelations): void
    {
        Language::updateOrCreate(
            [
                'source_id' => $guiding->id,
                'type' => 'guidings_relations',
                'language' => $targetLanguage
            ],
            [
                'json_data' => json_encode($translatedRelations),
                'updated_at' => now()
            ]
        );
    }

    /**
     * Mark content as updated (for tracking purposes)
     */
    public function markContentUpdated(Guiding $guiding): void
    {
        $guiding->touch();
        
        // Clear all translation caches for this guiding
        $languages = ['en', 'de', 'es', 'fr', 'it'];
        foreach ($languages as $language) {
            $cacheKey = 'guiding_translation_' . $guiding->id . '_' . $language;
            Cache::forget($cacheKey);
        }
    }

    /**
     * Get guidings that need translation based on admin changes
     */
    public function getGuidingsNeedingTranslation(): array
    {
        // Return guidings that have been updated recently
        return Guiding::where('updated_at', '>', Carbon::now()->subDays(7))
            ->where('status', 1)
            ->pluck('id')
            ->toArray();
    }

    /**
     * Batch translate fields using Google Translate
     */
    private function batchTranslateWithGoogle(array $fields, string $toLanguage, string $fromLanguage = 'de'): array
    {
        $translatedFields = [];

        foreach ($fields as $key => $text) {
            if (empty($text)) {
                $translatedFields[$key] = $text;
                continue;
            }

            try {
                $translated = GoogleTranslate::trans($text, $toLanguage, $fromLanguage);

                // Apply custom replacements
                if (strpos($translated, 'Führungen')) {
                    $translated = str_replace('Führungen', 'Angelguidings', $translated);
                }

                if (strpos($translated, 'Führung')) {
                    $translated = str_replace('Führung', 'guiding', $translated);
                }

                $translatedFields[$key] = ucfirst($translated);
            } catch (\Exception $e) {
                Log::error('Google Translate failed for field', [
                    'key' => $key,
                    'error' => $e->getMessage()
                ]);
                // Keep original text if translation fails
                $translatedFields[$key] = $text;
            }
        }

        return $translatedFields;
    }
} 