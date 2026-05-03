<?php

namespace App\Services\Assistant;

use App\Contracts\Assistant\AssistantUiEnvelopeParserInterface;
use App\Contracts\Assistant\AssistantUiPayloadNormalizerInterface;
use App\Services\Assistant\Dto\ParsedAssistantEnvelope;

final class AssistantUiEnvelopeParser implements AssistantUiEnvelopeParserInterface
{
    public function __construct(
        private BalancedJsonObjectExtractor $jsonExtractor,
        private AssistantUiPayloadNormalizerInterface $uiNormalizer,
    ) {
    }

    public function parse(string $rawAssistantText): ParsedAssistantEnvelope
    {
        $original = trim($rawAssistantText);
        if ($original === '') {
            return new ParsedAssistantEnvelope('', null);
        }

        $stripped = $this->stripMarkdownCodeFence($original);
        $parsed = $this->decodeJsonEnvelopeCandidate($stripped);
        if ($parsed !== null) {
            return $parsed;
        }

        foreach ($this->jsonExtractor->extractCandidatesContainingUiOrCards($stripped) as $jsonStr) {
            $parsed = $this->decodeJsonEnvelopeCandidate($jsonStr);
            if ($parsed === null || $parsed->ui === null) {
                continue;
            }
            $prefix = trim(str_replace($jsonStr, '', $stripped));
            $content = $parsed->content !== '' ? $parsed->content : ($prefix !== '' ? $prefix : __('booking-assistant.widget_listings_intro'));

            return new ParsedAssistantEnvelope($content, $parsed->ui);
        }

        if (preg_match('/(?P<json>\{[\s\S]*?"ui"[\s\S]*\})\s*$/', $stripped, $m)) {
            $json = (string) ($m['json'] ?? '');
            $decoded2 = json_decode($json, true);
            if (is_array($decoded2)) {
                $ui = isset($decoded2['ui']) && is_array($decoded2['ui']) ? $decoded2['ui'] : null;
                $cleanText = trim(preg_replace('/\s*' . preg_quote($json, '/') . '\s*$/', '', $stripped) ?? $stripped);
                if ($cleanText === '' && isset($decoded2['content']) && is_string($decoded2['content'])) {
                    $cleanText = trim($decoded2['content']);
                }
                if ($cleanText === '' && $ui !== null) {
                    $cleanText = __('booking-assistant.widget_listings_intro');
                }

                return new ParsedAssistantEnvelope($cleanText, $this->uiNormalizer->normalize($ui));
            }
        }

        return new ParsedAssistantEnvelope($original, null);
    }

    private function stripMarkdownCodeFence(string $text): string
    {
        $t = trim($text);
        if (preg_match('/^```(?:json)?\s*(.+?)\s*```$/is', $t, $m)) {
            return trim((string) ($m[1] ?? ''));
        }

        return $t;
    }

    private function decodeJsonEnvelopeCandidate(string $candidate): ?ParsedAssistantEnvelope
    {
        $candidate = trim($candidate);
        if ($candidate === '' || ! str_starts_with($candidate, '{')) {
            return null;
        }

        $decoded = json_decode($candidate, true);
        if (! is_array($decoded)) {
            return null;
        }

        $ui = null;
        if (isset($decoded['ui']) && is_array($decoded['ui'])) {
            $ui = $decoded['ui'];
        } elseif (isset($decoded['cards']) && is_array($decoded['cards'])) {
            $ui = ['cards' => $decoded['cards']];
        }

        $normalizedUi = $this->uiNormalizer->normalize($ui);
        if ($normalizedUi !== null) {
            $hasCards = isset($normalizedUi['cards']) && is_array($normalizedUi['cards']) && count($normalizedUi['cards']) > 0;
            $hasQr = isset($normalizedUi['quick_replies']) && is_array($normalizedUi['quick_replies']) && count($normalizedUi['quick_replies']) > 0;
            if (! $hasCards && ! $hasQr) {
                $normalizedUi = null;
            }
        }

        $content = null;
        if (isset($decoded['content']) && is_string($decoded['content'])) {
            $content = trim($decoded['content']);
        } elseif (isset($decoded['message']) && is_string($decoded['message'])) {
            $content = trim($decoded['message']);
        }

        if ($normalizedUi === null) {
            if (is_string($content) && $content !== '') {
                return new ParsedAssistantEnvelope($content, null);
            }

            return null;
        }

        if ($content === null || $content === '') {
            $content = __('booking-assistant.widget_listings_intro');
        }

        return new ParsedAssistantEnvelope($content, $normalizedUi);
    }
}
