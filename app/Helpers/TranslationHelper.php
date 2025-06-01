<?php

namespace App\Helpers;

use App\Services\Translation\GeminiTranslationService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class TranslationHelper
{
    private static $prompts;
    private static $translator;
    private static $language = ['de' => 'German', 'en' => 'English', 'es' => 'Spanish', 'fr' => 'French', 'it' => 'Italian', 'ja' => 'Japanese', 'ko' => 'Korean', 'pt' => 'Portuguese', 'ru' => 'Russian', 'zh' => 'Chinese'];

    public static function init()
    {
        if (!self::$prompts) {
            self::$prompts = json_decode(
                File::get(config_path('translation_prompts.json')), 
                true
            );
        }

        if (!self::$translator) {
            self::$translator = new GeminiTranslationService();
        }
    }

    public static function getLanguageName(string $language): string
    {
        return self::$language[$language] ?? $language;
    }

    public static function simpleBatchTranslate(array $texts, string $toLanguage, string $fromLanguage = 'en')
    {
        self::init();

        $fromLanguageName = self::getLanguageName($fromLanguage);
        $toLanguageName = self::getLanguageName($toLanguage);
        $forTranslate = json_encode($texts);

        $prompt = "Translate the following text in this JSON object field {$forTranslate} from {$fromLanguageName} to {$toLanguageName} while keeping the JSON structure and keys as return/response";

        Log::info($prompt);

        return self::$translator->translate($prompt, $fromLanguage, $toLanguage);
    }

    public static function batchTranslate(array $texts, string $toLanguage, string $fromLanguage = 'en', string $context = 'destination'): array
    {
        self::init();

        // Get context-specific instructions from prompts
        $contextInstructions = self::getContextInstructions($context);

        // Prepare the structured content for translation
        $structuredContent = [];
        foreach ($texts as $key => $text) {
            if (!empty($text)) {
                $structuredContent[$key] = [
                    'text' => $text,
                    'type' => $key, // This helps identify the type of content
                    'instructions' => self::getSpecificPrompt($context, $key)
                ];
            }
        }

        if (empty($structuredContent)) {
            return $texts;
        }

        // Create a JSON string of the content to translate
        $jsonContent = json_encode($structuredContent, JSON_PRETTY_PRINT);

        // Create a single prompt that includes context-specific instructions
        // $prompt = "Please translate the content from {$jsonContent} from German to English";
        $prompt = "You are translating content for a tourism and fishing website.
                   Translate the following JSON content from {$fromLanguage} to {$toLanguage}.
                   
                   Important instructions:
                   {$contextInstructions}
                   
                   For each item, follow its specific instructions in the 'instructions' field.
                   Maintain the exact JSON structure and keys.
                   Only translate the 'text' values, keep everything else unchanged.
                   Return only the translated JSON, nothing else.

                   Content to translate:
                   {$jsonContent}";

        try {
            $translatedJson = self::$translator->translate($prompt, $fromLanguage, $toLanguage);
            
            // Clean the response by removing Markdown code block markers if present
            $translatedJson = preg_replace('/^```json\s*|\s*```\s*$/m', '', $translatedJson);
            
            $translatedData = json_decode($translatedJson, true);

            // Validate the response
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON response from translation service');
            }

            // Extract just the translated texts
            $result = [];
            foreach ($texts as $key => $originalValue) {
                $result[$key] = $translatedData[$key]['text'] ?? $originalValue;
            }

            return $result;
        } catch (\Exception $e) {
            \Log::error('Batch translation failed: ' . $e->getMessage());
            return $texts; // Return original texts if translation fails
        }
    }

    private static function getContextInstructions(string $context): string
    {
        // Get general instructions for the context
        if (isset(self::$prompts[$context])) {
            $contextData = self::$prompts[$context];
            $instructions = [];
            
            foreach ($contextData as $type => $data) {
                if (isset($data['instructions'])) {
                    $instructions[] = "- For {$type}: {$data['instructions']}";
                }
            }

            return implode("\n", $instructions);
        }

        return self::$prompts['default']['instructions'] ?? '';
    }

    private static function getSpecificPrompt(string $context, string $key): string
    {
        if (isset(self::$prompts[$context][$key]['prompt'])) {
            return self::$prompts[$context][$key]['prompt'];
        }
        return self::$prompts['default']['prompt'];
    }
} 