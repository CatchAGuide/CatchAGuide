<?php

namespace App\Services\Assistant;

use App\Contracts\Assistant\LLMClientInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

final class GroqHttpClient implements LLMClientInterface
{
    public function chat(array $messages, ?array $tools, array $options = []): LLMGenerationResult
    {
        $apiKey = (string) config('booking_assistant.providers.groq.api_key', '');
        if ($apiKey === '') {
            throw new RuntimeException('Groq API key is not configured.');
        }

        $baseUrl = rtrim((string) config('booking_assistant.providers.groq.base_url', 'https://api.groq.com/openai/v1'), '/');
        $model = (string) config('booking_assistant.providers.groq.model', 'llama-3.1-8b-instant');
        $timeout = (int) config('booking_assistant.http_timeout_seconds', 45);

        $payload = [
            'model' => $model,
            'messages' => $messages,
            'max_tokens' => (int) ($options['max_tokens'] ?? config('booking_assistant.max_output_tokens', 512)),
            'temperature' => (float) ($options['temperature'] ?? config('booking_assistant.temperature', 0.4)),
        ];

        if ($tools !== null && $tools !== []) {
            $payload['tools'] = $tools;
            $payload['tool_choice'] = 'auto';
        }

        $response = Http::withToken($apiKey)
            ->timeout($timeout)
            ->acceptJson()
            ->post($baseUrl . '/chat/completions', $payload);

        if (!$response->successful()) {
            Log::warning('Booking assistant Groq request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            // Treat rate limits as a temporary unavailability so the UI can recover gracefully.
            if ($response->status() === 429) {
                return new LLMGenerationResult(
                    assistantContent: null,
                    toolCalls: [],
                    finishReason: 'rate_limited',
                    rawAssistantMessage: null,
                );
            }

            throw new RuntimeException('Assistant provider returned an error.');
        }

        $data = $response->json();
        $choice = $data['choices'][0] ?? null;
        if (!is_array($choice)) {
            throw new RuntimeException('Unexpected assistant provider response.');
        }

        $message = $choice['message'] ?? [];
        if (!is_array($message)) {
            $message = [];
        }

        $content = isset($message['content']) && is_string($message['content']) ? $message['content'] : null;
        $toolCalls = isset($message['tool_calls']) && is_array($message['tool_calls']) ? $message['tool_calls'] : [];
        $finishReason = isset($choice['finish_reason']) && is_string($choice['finish_reason']) ? $choice['finish_reason'] : null;

        return new LLMGenerationResult(
            assistantContent: $content,
            toolCalls: $toolCalls,
            finishReason: $finishReason,
            rawAssistantMessage: $message,
        );
    }
}
