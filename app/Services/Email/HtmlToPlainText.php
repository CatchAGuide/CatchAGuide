<?php

namespace App\Services\Email;

/**
 * Derives the text/plain alternative for an HTML email. Spam filters penalize HTML-only
 * messages, and every mailable here renders a Blade HTML view without a text view.
 */
class HtmlToPlainText
{
    public function convert(string $html): string
    {
        // Drop parts that never render as body text.
        $text = preg_replace('#<(head|style|script|title)\b[^>]*>.*?</\1>#is', '', $html) ?? $html;
        $text = preg_replace('#<!--.*?-->#s', '', $text) ?? $text;

        // Template source formatting is not content; line breaks come from markup only.
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        // Keep link targets readable: "Label (https://...)".
        $text = preg_replace_callback(
            '#<a\b[^>]*href\s*=\s*(["\'])(.*?)\1[^>]*>(.*?)</a>#is',
            function (array $m): string {
                $url = html_entity_decode(trim($m[2]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $label = trim(strip_tags($m[3]));

                if ($url === '' || str_starts_with($url, '#') || str_starts_with(strtolower($url), 'javascript:')) {
                    return $label;
                }
                if (str_starts_with(strtolower($url), 'mailto:')) {
                    $url = substr($url, 7);
                }
                if ($label === '' || $label === $url) {
                    return $url;
                }

                return $label . ' (' . $url . ')';
            },
            $text
        ) ?? $text;

        // Block-level boundaries become line breaks.
        $text = preg_replace('#<br\s*/?>#i', "\n", $text) ?? $text;
        $text = preg_replace('#<li\b[^>]*>#i', "\n- ", $text) ?? $text;
        $text = preg_replace('#</(p|div|tr|table|h[1-6]|ul|ol|li|blockquote|section|header|footer)>#i', "\n", $text) ?? $text;
        $text = preg_replace('#</t[dh]>#i', ' ', $text) ?? $text;

        $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = str_replace("\u{00A0}", ' ', $text);

        // Collapse layout whitespace from table-based templates.
        $lines = array_map(fn (string $line) => trim(preg_replace('/[ \t]+/', ' ', $line) ?? $line), explode("\n", str_replace("\r", '', $text)));
        $text = preg_replace("/\n{3,}/", "\n\n", implode("\n", $lines)) ?? '';

        return trim($text);
    }
}
