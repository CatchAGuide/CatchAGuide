<?php

namespace App\Services\Assistant\Dto;

/**
 * Structured assistant reply extracted from raw model text (before catalog merge / sanitization).
 */
final readonly class ParsedAssistantEnvelope
{
    /**
     * @param  array<string, mixed>|null  $ui
     */
    public function __construct(
        public string $content,
        public ?array $ui,
    ) {
    }
}
