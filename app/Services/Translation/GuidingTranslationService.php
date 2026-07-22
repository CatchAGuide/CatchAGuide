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

    private const DE_SOURCE_WORDS = [
        'und', 'oder', 'für', 'mit', 'auf', 'dem', 'der', 'die', 'das', 'den', 'des',
        'ein', 'eine', 'einer', 'einem', 'einen', 'ist', 'sind', 'wird', 'werden',
        'angeln', 'angel', 'stunde', 'stunden', 'ganztägig', 'halbtägig', 'tour',
        'raubfisch', 'raubfische', 'hecht', 'zander', 'barsch', 'wels', 'boot',
        'gewässer', 'see', 'fluss', 'küste', 'inklusive', 'exklusive', 'personen',
        'führung', 'führungen', 'angelguide', 'angelguiding', 'vom', 'zum', 'zur',
        'über', 'nach', 'auch', 'sowie', 'zwischen', 'während', 'bereits',
    ];

    private const EN_SOURCE_WORDS = [
        'the', 'and', 'or', 'for', 'with', 'from', 'this', 'that', 'you', 'your',
        'will', 'are', 'is', 'be', 'on', 'in', 'at', 'to', 'of', 'a', 'an',
        'fishing', 'trip', 'tour', 'tours', 'hour', 'hours', 'boat', 'guide',
        'guiding', 'catch', 'target', 'targets', 'include', 'includes', 'experience',
        'family', 'shared', 'private', 'half', 'full', 'day', 'offshore', 'inshore',
        'coast', 'lake', 'river', 'sea', 'big', 'game', 'predator', 'bottom',
    ];

    /**
     * Detect source language from guiding main content (heuristic EN/DE).
     * Returns null when uncertain — never forces "de" on failure.
     *
     * @return array{language: ?string, confidence: string, reason: string}
     */
    public function detectSourceLanguageFromContent(Guiding $guiding): array
    {
        $parts = array_filter([
            $guiding->title,
            $guiding->description,
            $guiding->additional_information,
            $guiding->meeting_point,
            $guiding->desc_course_of_action,
            $guiding->desc_meeting_point,
            $guiding->desc_starting_time,
            $guiding->desc_tour_unique,
        ], fn ($v) => is_string($v) && trim($v) !== '');

        if (empty($parts)) {
            return [
                'language' => null,
                'confidence' => 'none',
                'reason' => 'no text fields',
            ];
        }

        $text = mb_strtolower(strip_tags(implode(' ', $parts)));
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        $deScore = 0;
        $enScore = 0;
        $reasons = [];

        if (preg_match_all('/[äöüß]/u', $text, $m)) {
            $umlautHits = count($m[0]);
            $deScore += $umlautHits * 3;
            $reasons[] = "umlauts={$umlautHits}";
        }

        $tokens = preg_split('/[^a-zäöüß]+/u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $tokenCounts = array_count_values($tokens);

        $deWordHits = 0;
        foreach (self::DE_SOURCE_WORDS as $word) {
            if (isset($tokenCounts[$word])) {
                $deWordHits += $tokenCounts[$word];
            }
        }
        $enWordHits = 0;
        foreach (self::EN_SOURCE_WORDS as $word) {
            if (isset($tokenCounts[$word])) {
                $enWordHits += $tokenCounts[$word];
            }
        }

        $deScore += $deWordHits;
        $enScore += $enWordHits;
        $reasons[] = "de_words={$deWordHits}";
        $reasons[] = "en_words={$enWordHits}";

        $title = mb_strtolower((string) $guiding->title);
        $titleHasUmlaut = (bool) preg_match('/[äöüß]/u', $title);
        $titleLooksEn = ! $titleHasUmlaut && (bool) preg_match(
            '/\b(fishing|trip|tour|tours|boat|hour|hours|predator|marlin|tuna|bass)\b/u',
            $title
        );
        $titleLooksDe = $titleHasUmlaut || (bool) preg_match(
            '/\b(angeln|angel|raubfisch|stunde|stunden|tour|hecht|zander|barsch)\b/u',
            $title
        );

        if ($titleLooksEn) {
            $enScore += 8;
            $reasons[] = 'title~en';
        }
        if ($titleLooksDe) {
            $deScore += 8;
            $reasons[] = 'title~de';
        }

        if ($deScore === 0 && $enScore === 0) {
            return [
                'language' => null,
                'confidence' => 'none',
                'reason' => 'no language signals',
            ];
        }

        $diff = abs($enScore - $deScore);
        $total = max(1, $enScore + $deScore);
        $margin = $diff / $total;

        if ($diff < 3 || $margin < 0.15) {
            return [
                'language' => null,
                'confidence' => 'low',
                'reason' => 'tied '.$enScore.':'.$deScore.' ('.implode(', ', $reasons).')',
            ];
        }

        $language = $enScore > $deScore ? 'en' : 'de';
        $confidence = $margin >= 0.4 || $diff >= 10 ? 'high' : 'medium';

        return [
            'language' => $language,
            'confidence' => $confidence,
            'reason' => "{$enScore}:{$deScore} (".implode(', ', $reasons).')',
        ];
    }

    /**
     * Detect the source language of guiding content (EN/DE heuristic).
     * Falls back to current language, then "de", only when heuristic is uncertain.
     */
    public function detectGuidingLanguage(Guiding $guiding): string
    {
        $result = $this->detectSourceLanguageFromContent($guiding);
        if ($result['language']) {
            return $result['language'];
        }

        $current = strtolower((string) ($guiding->language ?? ''));
        if (in_array($current, ['en', 'de'], true)) {
            return $current;
        }

        return 'de';
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
     * Load translations for multiple guidings in one query (from Language table only).
     * Use this for lists to avoid N+1. Returns map: guiding_id => json_data array.
     *
     * @param array $guidingIds
     * @param string $targetLanguage
     * @return array<int, array>
     */
    public function getTranslatedGuidingsBatch(array $guidingIds, string $targetLanguage): array
    {
        if (empty($guidingIds)) {
            return [];
        }

        $translations = Language::where('type', 'guidings')
            ->whereIn('source_id', $guidingIds)
            ->where('language', $targetLanguage)
            ->get();

        $map = [];
        foreach ($translations as $row) {
            $id = (int) $row->source_id;
            $data = $row->json_data;
            if ($data !== null) {
                $map[$id] = is_array($data) ? $data : (array) json_decode($data, true);
            }
        }

        return $map;
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
     * Check if a guiding has an existing translation in the Language table for a given language.
     */
    public function hasTranslation(Guiding $guiding, string $targetLanguage): bool
    {
        if ($guiding->language === $targetLanguage) {
            return true; // Same as source, no translation row needed
        }

        return Language::where([
            'source_id' => $guiding->id,
            'type' => 'guidings',
            'language' => $targetLanguage
        ])->exists();
    }

    /**
     * Get guidings that are missing at least one translation for the given target languages.
     * Only considers active guidings (status = 1).
     *
     * @param array $targetLanguages e.g. ['en', 'de']
     * @return \Illuminate\Support\Collection Guiding models
     */
    public function getGuidingsMissingTranslations(array $targetLanguages): \Illuminate\Support\Collection
    {
        $guidings = Guiding::where('status', 1)
            ->with(['user', 'guidingTargets', 'guidingMethods', 'guidingWaters'])
            ->get();

        return $guidings->filter(function (Guiding $guiding) use ($targetLanguages) {
            foreach ($targetLanguages as $lang) {
                if (!$this->hasTranslation($guiding, $lang)) {
                    return true; // Missing at least one language
                }
            }
            return false;
        })->values();
    }

    /**
     * Get target languages for which this guiding has no translation (same defaults as GuidingTranslationCommand).
     *
     * @param array $targetLanguages e.g. ['en', 'de']
     * @return array Language codes that are missing for this guiding
     */
    public function getMissingLanguages(Guiding $guiding, array $targetLanguages = ['en', 'de']): array
    {
        $missing = [];
        foreach ($targetLanguages as $lang) {
            if (!$this->hasTranslation($guiding, $lang)) {
                $missing[] = $lang;
            }
        }
        return $missing;
    }

    /**
     * Default target languages used by the translation command and UI (EN, DE).
     */
    public static function defaultTargetLanguages(): array
    {
        return ['en', 'de'];
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