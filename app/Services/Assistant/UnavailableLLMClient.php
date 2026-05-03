<?php

namespace App\Services\Assistant;

use App\Contracts\Assistant\LLMClientInterface;

final class UnavailableLLMClient implements LLMClientInterface
{
    public function chat(array $messages, ?array $tools, array $options = []): LLMGenerationResult
    {
        return new LLMGenerationResult(
            assistantContent: null,
            toolCalls: [],
            finishReason: 'unavailable',
            rawAssistantMessage: null,
        );
    }
}
