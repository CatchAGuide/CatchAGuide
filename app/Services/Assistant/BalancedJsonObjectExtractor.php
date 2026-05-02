<?php

namespace App\Services\Assistant;

/**
 * Locates JSON object substrings (balanced braces) that may contain assistant UI payloads.
 */
final class BalancedJsonObjectExtractor
{
    /**
     * @return list<string>
     */
    public function extractCandidatesContainingUiOrCards(string $text): array
    {
        $len = strlen($text);
        $found = [];
        for ($i = 0; $i < $len; $i++) {
            if ($text[$i] !== '{') {
                continue;
            }
            $end = $this->findMatchingBraceEnd($text, $i);
            if ($end === null) {
                continue;
            }
            $slice = substr($text, $i, $end - $i + 1);
            if (strlen($slice) < 12) {
                continue;
            }
            if (! str_contains($slice, '"ui"') && ! str_contains($slice, '"cards"')) {
                continue;
            }
            $found[] = $slice;
        }
        usort($found, static fn (string $a, string $b): int => strlen($b) <=> strlen($a));
        $out = [];
        foreach ($found as $f) {
            if (! in_array($f, $out, true)) {
                $out[] = $f;
            }
        }

        return $out;
    }

    public function findMatchingBraceEnd(string $s, int $start): ?int
    {
        $len = strlen($s);
        if ($start >= $len || $s[$start] !== '{') {
            return null;
        }
        $depth = 0;
        $inString = false;
        $q = '';
        $escape = false;
        for ($i = $start; $i < $len; $i++) {
            $ch = $s[$i];
            if ($inString) {
                if ($escape) {
                    $escape = false;

                    continue;
                }
                if ($ch === '\\') {
                    $escape = true;

                    continue;
                }
                if ($ch === $q) {
                    $inString = false;
                }

                continue;
            }
            if ($ch === '"' || $ch === "'") {
                $inString = true;
                $q = $ch;

                continue;
            }
            if ($ch === '{') {
                $depth++;
            } elseif ($ch === '}') {
                $depth--;
                if ($depth === 0) {
                    return $i;
                }
            }
        }

        return null;
    }
}
