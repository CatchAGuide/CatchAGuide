<?php

namespace App\Services\Translation;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use App\Services\DDoSProtectionService;

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
        // DDoS Protection: Check rate limits and validate input
        $protectionService = app(DDoSProtectionService::class);
        $config = [
            'context' => 'gemini',
            'limits' => [
                'minute' => 10,
                'hour' => 100,
                'day' => 500
            ],
            'validate_input' => true,
            'block_threshold' => 10,
            'block_multiplier' => 30,
            'max_block_duration' => 1800
        ];
        
        $result = $protectionService->shouldBlockRequest(request(), $config);
        if ($result['blocked']) {
            throw new TranslationException('Rate limit exceeded. Please try again later.');
        }

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