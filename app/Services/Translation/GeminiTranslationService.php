<?php

namespace App\Services\Translation;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;

class GeminiTranslationService
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.gemini.base_url') . '/' . config('services.gemini.model') . ':generateContent';
        $this->apiKey = config('services.gemini.key');
    }

    public function translate(string $text): string
    {
        try {
            $response = $this->makeTranslationRequest($text);
            
            if ($response->failed()) {
                throw new TranslationException('Gemini API error: ' . $response->body());
            }

            return $this->parseResponse($response);
        } catch (\Exception $e) {
            throw new TranslationException("Translation failed: {$e->getMessage()}");
        }
    }

    private function makeTranslationRequest(string $text): Response
    {
        return Http::post("{$this->baseUrl}?key={$this->apiKey}", [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $text]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.1,
                'topP' => 0.8,
                'topK' => 40,
                'maxOutputTokens' => 8192  // Increase max output tokens
            ]
        ]);
    }
    private function parseResponse(Response $response): string
    {
        $data = $response->json();
        
        if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            throw new TranslationException('Unexpected response format from Gemini API');
        }

        return trim($data['candidates'][0]['content']['parts'][0]['text']);
    }
} 