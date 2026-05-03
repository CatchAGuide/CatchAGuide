<?php

namespace App\Services\Assistant;

final class AssistantToolExecutor
{
    public function __construct(
        private CatalogSearchService $catalogSearch,
        private FaqKnowledgeProvider $faqKnowledge,
        private BlogIndexProvider $blogIndex,
    ) {
    }

    /**
     * @param  array<string, mixed>  $arguments  Decoded JSON arguments from the model
     */
    public function execute(string $name, array $arguments): string
    {
        $limit = (int) ($arguments['limit'] ?? config('booking_assistant.max_tool_rows', 8));

        return match ($name) {
            'search_catalog' => json_encode(
                $this->catalogSearch->search(
                    (string) ($arguments['query'] ?? ''),
                    isset($arguments['types']) && is_array($arguments['types']) ? $arguments['types'] : [],
                    $limit,
                    $this->optionalPositiveFloat($arguments['max_price'] ?? null),
                    $this->coerceBool($arguments['max_price_strict'] ?? false),
                ),
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            ),
            'search_faq' => json_encode(
                $this->faqKnowledge->search((string) ($arguments['query'] ?? ''), $limit),
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            ),
            'search_blog' => json_encode(
                $this->blogIndex->search((string) ($arguments['query'] ?? ''), $limit),
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            ),
            default => json_encode(['error' => 'unknown_tool', 'name' => $name]),
        };
    }

    private function optionalPositiveFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_int($value) || is_float($value)) {
            $n = (float) $value;

            return $n > 0 ? $n : null;
        }
        if (is_string($value) && is_numeric($value)) {
            $n = (float) $value;

            return $n > 0 ? $n : null;
        }

        return null;
    }

    private function coerceBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        if ($value === 1 || $value === '1' || $value === 'true') {
            return true;
        }

        return false;
    }
}
