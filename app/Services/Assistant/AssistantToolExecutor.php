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
}
