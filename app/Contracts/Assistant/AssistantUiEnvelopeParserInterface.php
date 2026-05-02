<?php

namespace App\Contracts\Assistant;

use App\Services\Assistant\Dto\ParsedAssistantEnvelope;

interface AssistantUiEnvelopeParserInterface
{
    /**
     * Extract visible text and optional UI payload from raw assistant output.
     */
    public function parse(string $rawAssistantText): ParsedAssistantEnvelope;
}
