<?php

namespace App\Contracts\Assistant;

use App\Services\Assistant\LLMGenerationResult;

interface LLMClientInterface
{
    /**
     * @param  array<int, array<string, mixed>>  $messages  OpenAI-compatible chat messages
     * @param  array<int, array<string, mixed>>|null  $tools  Tool definitions (function calling)
     * @param  array<string, mixed>  $options  e.g. max_tokens, temperature
     */
    public function chat(array $messages, ?array $tools, array $options = []): LLMGenerationResult;
}
