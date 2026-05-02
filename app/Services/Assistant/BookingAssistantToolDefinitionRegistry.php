<?php

namespace App\Services\Assistant;

/**
 * OpenAI-style tool definitions for the booking assistant (single source of truth).
 */
final class BookingAssistantToolDefinitionRegistry
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public function definitions(): array
    {
        return [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'search_catalog',
                    'description' => 'Search published guidings, vacation packages, and fishing camps by keywords (fish species, region, city, method). Returns titles, URLs, and short snippets only.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'query' => ['type' => 'string', 'description' => 'Search keywords in user language'],
                            'types' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'string',
                                    'enum' => ['guiding', 'vacation', 'camp'],
                                ],
                                'description' => 'Optional filter. Omit to search all types.',
                            ],
                            'limit' => ['type' => 'integer', 'description' => 'Max results (default 8, capped server-side)'],
                        ],
                        'required' => ['query'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'search_faq',
                    'description' => 'Search official FAQ entries (policies, booking basics, account help).',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'query' => ['type' => 'string'],
                            'limit' => ['type' => 'integer'],
                        ],
                        'required' => ['query'],
                    ],
                ],
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'search_blog',
                    'description' => 'Search magazine / blog articles by topic or keywords.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'query' => ['type' => 'string'],
                            'limit' => ['type' => 'integer'],
                        ],
                        'required' => ['query'],
                    ],
                ],
            ],
        ];
    }
}
