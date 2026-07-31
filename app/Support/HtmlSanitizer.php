<?php

namespace App\Support;

/**
 * Lightweight HTML allowlist sanitizer for CMS / guide rich text.
 * Strips scripts, event handlers, and dangerous URLs while keeping basic formatting.
 */
class HtmlSanitizer
{
    private const ALLOWED_TAGS = '<p><br><br/><strong><b><em><i><u><ul><ol><li><a><h2><h3><h4><span><blockquote>';

    public static function clean(?string $html): string
    {
        if ($html === null || $html === '') {
            return '';
        }

        $cleaned = strip_tags($html, self::ALLOWED_TAGS);

        // Remove inline event handlers and style/javascript URLs.
        $cleaned = preg_replace('/\son\w+\s*=\s*(".*?"|\'.*?\'|[^\s>]+)/iu', '', $cleaned) ?? $cleaned;
        $cleaned = preg_replace('/\s(href|src)\s*=\s*([\'"])\s*javascript:[^\'"]*\2/iu', ' $1="#"', $cleaned) ?? $cleaned;
        $cleaned = preg_replace('/\s(href|src)\s*=\s*([\'"])\s*data:[^\'"]*\2/iu', ' $1="#"', $cleaned) ?? $cleaned;

        return $cleaned;
    }
}
