<?php

namespace App\Services\Assistant;

/** Normalized result from a chat-completions style API. */
final class LLMGenerationResult
{
    /**
     * @param  array<int, array<string, mixed>>  $toolCalls
     */
    public function __construct(
        public readonly ?string $assistantContent,
        public readonly array $toolCalls,
        public readonly ?string $finishReason,
        public readonly ?array $rawAssistantMessage = null,
    ) {
    }

    public function hasToolCalls(): bool
    {
        return $this->toolCalls !== [];
    }
}
