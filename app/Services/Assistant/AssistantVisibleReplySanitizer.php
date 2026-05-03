<?php

namespace App\Services\Assistant;

use App\Contracts\Assistant\AssistantVisibleReplySanitizerInterface;

final class AssistantVisibleReplySanitizer implements AssistantVisibleReplySanitizerInterface
{
    public function __construct(
        private BalancedJsonObjectExtractor $jsonExtractor,
    ) {
    }

    public function sanitize(string $content, ?array $ui): string
    {
        if (! $this->uiHasListingCards($ui)) {
            return $content;
        }

        $t = trim($content);
        for ($guard = 0; $guard < 16; $guard++) {
            $found = false;
            foreach ($this->jsonExtractor->extractCandidatesContainingUiOrCards($t) as $jsonStr) {
                $decoded = json_decode($jsonStr, true);
                if (! is_array($decoded)) {
                    continue;
                }
                $cards = null;
                if (isset($decoded['ui']['cards']) && is_array($decoded['ui']['cards'])) {
                    $cards = $decoded['ui']['cards'];
                } elseif (isset($decoded['cards']) && is_array($decoded['cards'])) {
                    $cards = $decoded['cards'];
                }
                if ($cards === null || $cards === []) {
                    continue;
                }
                $t = trim(str_replace($jsonStr, '', $t));
                $t = preg_replace('/\s{2,}/u', ' ', $t) ?? $t;
                $found = true;
                break;
            }
            if (! $found) {
                break;
            }
        }

        if (preg_match('/\{\s*"(?:content|ui|cards)"/u', $t, $m, PREG_OFFSET_CAPTURE)) {
            $at = $m[0][1];
            if ($at > 0) {
                $t = trim(substr($t, 0, $at));
            } elseif ($at === 0 && $this->looksLikeLeakedEnvelopeJson($t)) {
                // Whole bubble is a (possibly truncated) JSON envelope; model text is unusable for json_decode.
                $t = $this->extractEnvelopeContentOrIntro($t);
            }
        }

        if ($this->looksLikeLeakedEnvelopeJson($t)) {
            $t = $this->extractEnvelopeContentOrIntro($t);
        }

        $t = trim($t);
        if ($t === '') {
            return __('booking-assistant.widget_listings_intro');
        }

        return $t;
    }

    /**
     * Pull human "content" from a broken/truncated assistant JSON envelope when cards are shown from tool fallback.
     */
    private function extractEnvelopeContentOrIntro(string $t): string
    {
        $extracted = $this->extractJsonObjectContentField($t);
        if ($extracted !== null && $extracted !== '') {
            return trim($extracted);
        }

        return __('booking-assistant.widget_listings_intro');
    }

    private function looksLikeLeakedEnvelopeJson(string $t): bool
    {
        $t = trim($t);
        if ($t === '' || $t[0] !== '{') {
            return false;
        }

        return str_contains($t, '"ui"') || str_contains($t, '"cards"');
    }

    /**
     * Reads the JSON string value for the top-level "content" key without requiring valid JSON for the rest of the document.
     */
    private function extractJsonObjectContentField(string $t): ?string
    {
        if (! preg_match('/"content"\s*:\s*"/u', $t, $m, PREG_OFFSET_CAPTURE)) {
            return null;
        }
        $start = $m[0][1] + strlen($m[0][0]);

        return $this->readJsonStringBytes($t, $start);
    }

    private function readJsonStringBytes(string $t, int $i): string
    {
        $len = strlen($t);
        $out = '';
        $escape = false;
        for (; $i < $len; $i++) {
            $ch = $t[$i];
            if ($escape) {
                $out .= match ($ch) {
                    '"', '\\' => $ch,
                    '/' => '/',
                    'b' => "\x08",
                    'f' => "\f",
                    'n' => "\n",
                    'r' => "\r",
                    't' => "\t",
                    'u' => $this->readJsonUnicodeEscape($t, $i + 1),
                    default => $ch,
                };
                if ($ch === 'u') {
                    $i += 4;
                }
                $escape = false;

                continue;
            }
            if ($ch === '\\') {
                $escape = true;

                continue;
            }
            if ($ch === '"') {
                break;
            }
            $out .= $ch;
        }

        return $out;
    }

    private function readJsonUnicodeEscape(string $t, int $hexStart): string
    {
        if (strlen($t) < $hexStart + 4) {
            return '';
        }
        $hex = substr($t, $hexStart, 4);
        if (! ctype_xdigit($hex)) {
            return '';
        }
        $code = (int) hexdec($hex);

        return mb_chr($code, 'UTF-8') ?: '';
    }

    /**
     * @param  array<string, mixed>|null  $ui
     */
    private function uiHasListingCards(?array $ui): bool
    {
        if ($ui === null) {
            return false;
        }

        return isset($ui['cards']) && is_array($ui['cards']) && count($ui['cards']) > 0;
    }
}
