<?php

namespace App\Support\Text;

/**
 * Repairs UTF-8 text that was decoded as Windows-1252 and re-encoded ("MÃ¶glichkeiten" instead
 * of "Möglichkeiten", "â€“" instead of "–"). Works run by run, so a string that mixes correct and
 * double-encoded text only has the broken runs converted; a run is only replaced when converting
 * it back yields valid UTF-8, so correct text is never touched.
 */
final class MojibakeRepair
{
    /**
     * A lead character of a double-encoded UTF-8 sequence (Â, Ã, â) followed by one or more
     * characters from the Windows-1252 0x80–0xBF range.
     */
    private const RUN = '/(?:[\x{00C2}\x{00C3}\x{00E2}][\x{0080}-\x{00BF}\x{0152}\x{0153}\x{0160}\x{0161}\x{0178}\x{017D}\x{017E}\x{0192}\x{02C6}\x{02DC}\x{2013}\x{2014}\x{2018}-\x{201E}\x{2020}-\x{2022}\x{2026}\x{2030}\x{2039}\x{203A}\x{20AC}\x{2122}]+)+/u';

    public static function needsRepair(?string $text): bool
    {
        return $text !== null && $text !== '' && preg_match(self::RUN, $text) === 1;
    }

    /** Some values went through the bad round trip more than once ("Ã¢€â€œ" for "–"). */
    private const MAX_PASSES = 3;

    public static function repair(?string $text): ?string
    {
        for ($pass = 0; $pass < self::MAX_PASSES && self::needsRepair($text); $pass++) {
            $repaired = self::repairOnce($text);
            if ($repaired === $text) {
                break;
            }
            $text = $repaired;
        }

        return $text;
    }

    private static function repairOnce(string $text): string
    {
        return preg_replace_callback(self::RUN, static function (array $match): string {
            $candidate = mb_convert_encoding($match[0], 'Windows-1252', 'UTF-8');

            return $candidate !== '' && mb_check_encoding($candidate, 'UTF-8')
                ? $candidate
                : $match[0];
        }, $text) ?? $text;
    }
}
