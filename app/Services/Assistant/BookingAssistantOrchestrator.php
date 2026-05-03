<?php

namespace App\Services\Assistant;

use App\Contracts\Assistant\LLMClientInterface;

final class BookingAssistantOrchestrator
{
    public function __construct(
        private LLMClientInterface $llm,
        private AssistantToolExecutor $toolExecutor,
    ) {
    }

    /**
     * @param  array<int, array{role: string, content: string}>  $messages
     * @return array{content: string, error: ?string, ui?: array<string, mixed>}
     */
    public function run(array $messages, ?string $pageContext): array
    {
        $maxMessages = (int) config('booking_assistant.max_messages', 16);
        $messages = array_slice($messages, -$maxMessages);

        $system = $this->buildSystemPrompt($pageContext);
        $conversation = array_merge(
            [['role' => 'system', 'content' => $system]],
            $messages
        );

        $tools = $this->toolDefinitions();
        $options = [
            'max_tokens' => (int) config('booking_assistant.max_output_tokens', 512),
            'temperature' => (float) config('booking_assistant.temperature', 0.4),
        ];

        $maxIterations = (int) config('booking_assistant.max_tool_iterations', 3);
        $lastCatalogCards = [];

        for ($i = 0; $i < $maxIterations; $i++) {
            $result = $this->llm->chat($conversation, $tools, $options);

            if (in_array($result->finishReason, ['unavailable', 'rate_limited'], true) && !$result->hasToolCalls()) {
                return [
                    'content' => $result->finishReason === 'rate_limited'
                        ? __('booking-assistant.rate_limited')
                        : __('booking-assistant.unavailable'),
                    'error' => null,
                ];
            }

            if (!$result->hasToolCalls()) {
                $text = trim((string) ($result->assistantContent ?? ''));
                if ($text === '') {
                    return [
                        'content' => __('booking-assistant.empty_response'),
                        'error' => null,
                    ];
                }

                [$content, $ui] = $this->parseUiEnvelope($text);
                $ui = $this->mergeFallbackUi($ui, $lastCatalogCards);
                $ui = $this->normalizeUi($ui);

                return $ui === null
                    ? ['content' => $content, 'error' => null]
                    : ['content' => $content, 'error' => null, 'ui' => $ui];
            }

            $rawAssistant = $result->rawAssistantMessage;
            if (!is_array($rawAssistant)) {
                return [
                    'content' => __('booking-assistant.tool_format_error'),
                    'error' => 'tool_format',
                ];
            }

            $conversation[] = $rawAssistant;

            foreach ($result->toolCalls as $toolCall) {
                if (!is_array($toolCall)) {
                    continue;
                }
                $id = isset($toolCall['id']) && is_string($toolCall['id']) ? $toolCall['id'] : '';
                $function = isset($toolCall['function']) && is_array($toolCall['function']) ? $toolCall['function'] : [];
                $name = isset($function['name']) && is_string($function['name']) ? $function['name'] : '';
                $argumentsRaw = $function['arguments'] ?? '{}';
                $arguments = is_string($argumentsRaw) ? json_decode($argumentsRaw, true) : $argumentsRaw;
                if (!is_array($arguments)) {
                    $arguments = [];
                }

                $payload = $this->toolExecutor->execute($name, $arguments);

                if ($name === 'search_catalog') {
                    $decoded = json_decode($payload, true);
                    if (is_array($decoded)) {
                        // Tool returns list of compact search result rows
                        $lastCatalogCards = array_values(array_filter(array_map(function ($row) {
                            if (!is_array($row)) {
                                return null;
                            }

                            $title = isset($row['title']) && is_string($row['title']) ? trim($row['title']) : '';
                            $url = isset($row['url']) && is_string($row['url']) ? trim($row['url']) : '';
                            if ($title === '' || $url === '') {
                                return null;
                            }

                            $price = null;
                            if (isset($row['min_price']) && (is_numeric($row['min_price']) || is_string($row['min_price']))) {
                                $price = (float) $row['min_price'];
                            }

                            $currency = isset($row['currency']) && is_string($row['currency']) && $row['currency'] !== ''
                                ? $row['currency']
                                : 'EUR';

                            return [
                                'type' => isset($row['type']) && is_string($row['type']) ? $row['type'] : null,
                                'title' => $title,
                                'url' => $url,
                                'price' => $price,
                                'currency' => $currency,
                                'snippet' => isset($row['snippet']) && is_string($row['snippet']) ? $row['snippet'] : '',
                                'image' => isset($row['image']) && is_string($row['image']) ? $row['image'] : null,
                            ];
                        }, $decoded)));
                    }
                }
                $conversation[] = [
                    'role' => 'tool',
                    'tool_call_id' => $id,
                    'content' => $payload,
                ];
            }
        }

        return [
            'content' => __('booking-assistant.iteration_cap'),
            'error' => 'iteration_cap',
        ];
    }

    /**
     * @return array{0:string,1:array<string,mixed>|null}
     */
    private function parseUiEnvelope(string $text): array
    {
        $text = trim($text);
        if ($text === '') {
            return ['', null];
        }

        // Case 1: pure JSON envelope
        $decoded = json_decode($text, true);
        if (is_array($decoded)) {
            $content = $decoded['content'] ?? $decoded['message'] ?? null;
            $ui = $decoded['ui'] ?? null;

            if (is_string($content) && trim($content) !== '') {
                return [trim($content), $this->normalizeUi(is_array($ui) ? $ui : null)];
            }
        }

        // Case 2: assistant text + trailing JSON block (common when models "explain" then paste JSON)
        // Try to extract the last {...} block that contains a "ui" key.
        if (preg_match('/(?P<json>\{[\s\S]*?"ui"[\s\S]*?\})\s*$/', $text, $m)) {
            $json = $m['json'] ?? '';
            $decoded2 = is_string($json) ? json_decode($json, true) : null;
            if (is_array($decoded2)) {
                $ui = $decoded2['ui'] ?? $decoded2;
                $cleanText = trim(preg_replace('/\s*' . preg_quote($json, '/') . '\s*$/', '', $text) ?? $text);
                if ($cleanText === '') {
                    $cleanText = isset($decoded2['content']) && is_string($decoded2['content']) ? trim($decoded2['content']) : '';
                }

                return [$cleanText, $this->normalizeUi(is_array($ui) ? $ui : null)];
            }
        }

        return [$text, null];
    }

    /**
     * Normalize UI payload so the frontend doesn't render JSON/object blobs.
     *
     * @param  array<string, mixed>|null  $ui
     * @return array<string, mixed>|null
     */
    private function normalizeUi(?array $ui): ?array
    {
        if ($ui === null) {
            return null;
        }

        // Normalize quick replies: allow ["Text", ...] or [{text:"Text"}, ...]
        if (isset($ui['quick_replies']) && is_array($ui['quick_replies'])) {
            $ui['quick_replies'] = array_values(array_filter(array_map(function ($item) {
                if (is_string($item)) {
                    return trim($item);
                }
                if (is_array($item) && isset($item['text']) && is_string($item['text'])) {
                    return trim($item['text']);
                }
                return null;
            }, $ui['quick_replies']), static fn ($v) => is_string($v) && $v !== ''));
        }

        // Normalize cards: ensure title/url/snippet are strings (avoid JSON blobs)
        if (isset($ui['cards']) && is_array($ui['cards'])) {
            $ui['cards'] = array_values(array_filter(array_map(function ($card) {
                if (!is_array($card)) {
                    return null;
                }
                $title = isset($card['title']) && is_string($card['title']) ? trim($card['title']) : '';
                $url = isset($card['url']) && is_string($card['url']) ? trim($card['url']) : '';
                if ($title === '' || $url === '') {
                    return null;
                }

                return [
                    'type' => isset($card['type']) && is_string($card['type']) ? $card['type'] : null,
                    'title' => $title,
                    'url' => $url,
                    'price' => isset($card['price']) && is_numeric($card['price']) ? (float) $card['price'] : null,
                    'min_price' => isset($card['min_price']) && is_numeric($card['min_price']) ? (float) $card['min_price'] : null,
                    'currency' => isset($card['currency']) && is_string($card['currency']) ? $card['currency'] : 'EUR',
                    'snippet' => isset($card['snippet']) && is_string($card['snippet']) ? $card['snippet'] : '',
                    'image' => isset($card['image']) && is_string($card['image']) ? $card['image'] : null,
                ];
            }, $ui['cards'])));
        }

        return $ui;
    }

    /**
     * @param  array<string, mixed>|null  $ui
     * @param  array<int, array<string, mixed>>  $fallbackCards
     * @return array<string, mixed>|null
     */
    private function mergeFallbackUi(?array $ui, array $fallbackCards): ?array
    {
        $ui = $ui ?? [];

        $hasCards = isset($ui['cards']) && is_array($ui['cards']) && count($ui['cards']) > 0;
        if (!$hasCards && $fallbackCards !== []) {
            $ui['cards'] = array_slice($fallbackCards, 0, 6);
            $ui['quick_replies'] = $ui['quick_replies'] ?? [
                'Show more options',
                'Different destination',
                'Different dates',
                'Budget under €200',
            ];
        }

        return $ui === [] ? null : $ui;
    }

    private function buildSystemPrompt(?string $pageContext): string
    {
        $locale = app()->getLocale();
        $site = config('app.name', 'Catch a Guide');
        $ctx = $pageContext ? trim($pageContext) : '';

        $lines = [
            __('booking-assistant.system_intro', ['site' => $site, 'locale' => $locale]),
            __('booking-assistant.system_tools'),
            __('booking-assistant.system_links', [
                'faq' => url('/faq'),
                'contact' => url('/contact'),
                'guidings' => url('/guidings'),
                'vacations' => url('/vacations'),
            ]),
        ];

        if ($ctx !== '') {
            $lines[] = __('booking-assistant.system_page_context', ['context' => $ctx]);
        }

        $lines[] = __('booking-assistant.system_rules');

        return implode("\n\n", $lines);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function toolDefinitions(): array
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
