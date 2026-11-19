<?php

namespace App\Services\Guide\Area;

/**
 * Maps user_information.languages (a comma-separated free-text string read by public pages)
 * to picker chips and back. Known languages become ISO codes; anything else stays as typed.
 */
class GuideLanguageCatalog
{
    /** @return array<string, string> code => translated label, sorted by label */
    public function options(): array
    {
        $options = [];
        foreach (array_keys((array) config('guide_area.languages', [])) as $code) {
            $options[$code] = __('guide_area.languages.'.$code);
        }
        asort($options, SORT_NATURAL | SORT_FLAG_CASE);

        return $options;
    }

    /**
     * @return array<int, string> codes for known languages, the original text for unknown ones
     */
    public function parse(?string $stored): array
    {
        $byName = [];
        foreach ((array) config('guide_area.languages', []) as $code => $english) {
            $byName[mb_strtolower($english)] = $code;
            $byName[mb_strtolower((string) $code)] = $code;
        }
        foreach ((array) config('guide_area.language_aliases', []) as $alias => $code) {
            $byName[mb_strtolower($alias)] = $code;
        }

        $result = [];
        foreach (preg_split('/[,;\/]|\s+(?:und|and|&)\s+/iu', (string) $stored) ?: [] as $piece) {
            $piece = trim($piece);
            if ($piece === '') {
                continue;
            }
            $value = $byName[mb_strtolower($piece)] ?? $piece;
            if (! in_array($value, $result, true)) {
                $result[] = $value;
            }
        }

        return $result;
    }

    /** @param  array<int, string>  $values */
    public function format(array $values): string
    {
        $names = (array) config('guide_area.languages', []);

        return collect($values)
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->map(fn ($value) => $names[$value] ?? $value)
            ->unique()
            ->implode(', ');
    }

    public function label(string $value): string
    {
        return array_key_exists($value, (array) config('guide_area.languages', []))
            ? __('guide_area.languages.'.$value)
            : $value;
    }
}
