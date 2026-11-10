<?php

namespace App\Services\Camp;

/**
 * Turns free-text camp "best travel times" into title/description pairs for the PDP.
 *
 * Editors write seasons in a few natural shapes:
 * - "Season: prose" on one line (most common in admin)
 * - Season title on one line, prose on the next
 * - Alternating title / prose blocks separated by blank lines
 */
class CampBestTravelTimesParser
{
    /**
     * @return list<array{title: string, description: string}>
     */
    public function parse(mixed $value): array
    {
        $text = $this->normalizeToText($value);

        if ($text === '') {
            return [];
        }

        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $sections = preg_split('/\n\s*\n/', trim($text), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $parsed = [];

        foreach ($sections as $section) {
            $lines = array_values(array_filter(array_map('trim', explode("\n", $section)), fn (string $line) => $line !== ''));

            if ($lines === []) {
                continue;
            }

            if (count($lines) === 1) {
                $parsed[] = $this->splitTitleDescription($lines[0]);
                continue;
            }

            $title = array_shift($lines);
            $description = implode(' ', $lines);

            if ($description === '') {
                $parsed[] = $this->splitTitleDescription($title);
                continue;
            }

            $parsed[] = [
                'title' => rtrim($title, " \t:"),
                'description' => $description,
            ];
        }

        return $this->pairAlternatingTitleOnlyBlocks($parsed);
    }

    /**
     * Structured month/note rows from legacy fixtures / JSON arrays.
     *
     * @param  list<mixed>  $items
     */
    public function isStructuredMonthNoteList(array $items): bool
    {
        if ($items === []) {
            return false;
        }

        foreach ($items as $item) {
            if (! is_array($item) || ! isset($item['month'], $item['note'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return list<array{title: string, description: string}>
     */
    private function pairAlternatingTitleOnlyBlocks(array $parsed): array
    {
        $paired = [];
        $count = count($parsed);

        for ($i = 0; $i < $count; $i++) {
            $item = $parsed[$i];
            $next = $parsed[$i + 1] ?? null;

            if (
                $item['description'] === ''
                && is_array($next)
                && $next['description'] === ''
                && mb_strlen($item['title']) <= 100
                && mb_strlen($next['title']) > mb_strlen($item['title'])
                && mb_strlen($next['title']) >= 60
            ) {
                $paired[] = [
                    'title' => rtrim($item['title'], " \t:"),
                    'description' => $next['title'],
                ];
                $i++;
                continue;
            }

            $paired[] = [
                'title' => rtrim($item['title'], " \t:"),
                'description' => $item['description'],
            ];
        }

        return $paired;
    }

    /**
     * @return array{title: string, description: string}
     */
    private function splitTitleDescription(string $line): array
    {
        if (preg_match('/^(.{1,80}?):\s+(.+)$/u', $line, $matches) === 1) {
            return [
                'title' => rtrim($matches[1], " \t:"),
                'description' => trim($matches[2]),
            ];
        }

        return [
            'title' => rtrim($line, " \t:"),
            'description' => '',
        ];
    }

    private function normalizeToText(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $this->normalizeToText($decoded);
            }

            return trim($value);
        }

        if (! is_array($value)) {
            return trim((string) $value);
        }

        if ($this->isStructuredMonthNoteList($value)) {
            $lines = [];
            foreach ($value as $item) {
                $month = trim((string) ($item['month'] ?? ''));
                $note = trim((string) ($item['note'] ?? ''));
                if ($month === '' && $note === '') {
                    continue;
                }
                $lines[] = $note === '' ? $month : ($month === '' ? $note : "{$month}: {$note}");
            }

            return implode("\n\n", $lines);
        }

        $parts = [];
        foreach ($value as $item) {
            if (is_string($item) && trim($item) !== '') {
                $parts[] = trim($item);
                continue;
            }
            if (is_array($item)) {
                $title = trim((string) ($item['title'] ?? $item['month'] ?? ''));
                $description = trim((string) ($item['description'] ?? $item['note'] ?? ''));
                if ($title === '' && $description === '') {
                    continue;
                }
                $parts[] = $description === '' ? $title : ($title === '' ? $description : "{$title}: {$description}");
            }
        }

        return implode("\n\n", $parts);
    }
}
