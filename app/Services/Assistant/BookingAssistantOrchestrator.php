<?php

namespace App\Services\Assistant;

use App\Contracts\Assistant\AssistantUiEnvelopeParserInterface;
use App\Contracts\Assistant\AssistantUiPayloadNormalizerInterface;
use App\Contracts\Assistant\AssistantVisibleReplySanitizerInterface;
use App\Contracts\Assistant\LLMClientInterface;

/**
 * Coordinates LLM calls, tool execution, and post-processing. Parsing / UI shaping / sanitization are delegated (SRP).
 */
final class BookingAssistantOrchestrator
{
    public function __construct(
        private LLMClientInterface $llm,
        private AssistantToolExecutor $toolExecutor,
        private AssistantUiEnvelopeParserInterface $envelopeParser,
        private AssistantUiPayloadNormalizerInterface $uiNormalizer,
        private AssistantVisibleReplySanitizerInterface $replySanitizer,
        private CatalogSearchResultCardMapper $catalogCardMapper,
        private BookingAssistantToolDefinitionRegistry $toolDefinitions,
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

        $tools = $this->toolDefinitions->definitions();
        $options = [
            'max_tokens' => (int) config('booking_assistant.max_output_tokens', 512),
            'temperature' => (float) config('booking_assistant.temperature', 0.4),
        ];

        $maxIterations = (int) config('booking_assistant.max_tool_iterations', 3);
        $lastCatalogCards = [];

        for ($i = 0; $i < $maxIterations; $i++) {
            $result = $this->llm->chat($conversation, $tools, $options);

            if (in_array($result->finishReason, ['unavailable', 'rate_limited'], true) && ! $result->hasToolCalls()) {
                return [
                    'content' => $result->finishReason === 'rate_limited'
                        ? __('booking-assistant.rate_limited')
                        : __('booking-assistant.unavailable'),
                    'error' => null,
                ];
            }

            if (! $result->hasToolCalls()) {
                $text = trim((string) ($result->assistantContent ?? ''));
                if ($text === '') {
                    return [
                        'content' => __('booking-assistant.empty_response'),
                        'error' => null,
                    ];
                }

                $envelope = $this->envelopeParser->parse($text);
                $content = $envelope->content;
                $ui = $envelope->ui;
                $ui = $this->uiNormalizer->mergeCatalogFallback($ui, $lastCatalogCards);
                $ui = $this->uiNormalizer->normalize($ui);
                $content = $this->replySanitizer->sanitize($content, $ui);

                return $ui === null
                    ? ['content' => $content, 'error' => null]
                    : ['content' => $content, 'error' => null, 'ui' => $ui];
            }

            $rawAssistant = $result->rawAssistantMessage;
            if (! is_array($rawAssistant)) {
                return [
                    'content' => __('booking-assistant.tool_format_error'),
                    'error' => 'tool_format',
                ];
            }

            $conversation[] = $rawAssistant;

            foreach ($result->toolCalls as $toolCall) {
                if (! is_array($toolCall)) {
                    continue;
                }
                $id = isset($toolCall['id']) && is_string($toolCall['id']) ? $toolCall['id'] : '';
                $function = isset($toolCall['function']) && is_array($toolCall['function']) ? $toolCall['function'] : [];
                $name = isset($function['name']) && is_string($function['name']) ? $function['name'] : '';
                $argumentsRaw = $function['arguments'] ?? '{}';
                $arguments = is_string($argumentsRaw) ? json_decode($argumentsRaw, true) : $argumentsRaw;
                if (! is_array($arguments)) {
                    $arguments = [];
                }

                $payload = $this->toolExecutor->execute($name, $arguments);

                if ($name === 'search_catalog') {
                    $decoded = json_decode($payload, true);
                    if (is_array($decoded)) {
                        $lastCatalogCards = $this->catalogCardMapper->mapRows($decoded);
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
}
