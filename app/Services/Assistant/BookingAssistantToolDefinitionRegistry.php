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
                    'description' => 'Search published guidings, vacation packages, and fishing camps by keywords (fish species, region, country, city, method). Text matching does not understand prices—when the user gives a budget, you must pass max_price (and usually max_price_strict) or results will not be price-filtered. Returns titles, URLs, min_price, currency, and short snippets.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'query' => [
                                'type' => 'string',
                                'description' => 'Search keywords in user language. On budget follow-ups, keep the same destination/species terms as the last successful catalog search—do not replace the query with only a number.',
                            ],
                            'types' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'string',
                                    'enum' => ['guiding', 'vacation', 'camp'],
                                ],
                                'description' => 'Optional. Omit or leave empty to search guidings, vacation packages, and fishing camps together (default). Pass one or more values only if the user clearly asked for that product type only (e.g. only camps).',
                            ],
                            'limit' => ['type' => 'integer', 'description' => 'Max results (default 8, capped server-side)'],
                            'max_price' => [
                                'type' => 'number',
                                'description' => 'Optional cap on listing min_price in the listing currency (typically EUR). Rows without a numeric min_price are excluded when this is set.',
                            ],
                            'max_price_strict' => [
                                'type' => 'boolean',
                                'description' => 'Use with max_price. true = keep rows with min_price < max_price (user said below/under/less than). false/omit = min_price <= max_price (up to/at most/max).',
                            ],
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
