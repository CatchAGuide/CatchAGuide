<?php

namespace App\Services\Guide\Area;

use App\Models\GuideTag;
use App\Models\User;

class GuideTagService
{
    /** @return array<int, string> suggestion keys for a kind */
    public function suggestionKeys(string $kind): array
    {
        return array_values((array) config('guide_area.tags.'.$kind, []));
    }

    /** @return array<string, string> key => translated label */
    public function suggestions(string $kind): array
    {
        $out = [];
        foreach ($this->suggestionKeys($kind) as $key) {
            $out[$key] = __('guide_area.tags.'.$kind.'.'.$key);
        }

        return $out;
    }

    public function isSuggestion(string $kind, string $value): bool
    {
        return in_array($value, $this->suggestionKeys($kind), true);
    }

    public function label(string $kind, string $value): string
    {
        return $this->isSuggestion($kind, $value) ? __('guide_area.tags.'.$kind.'.'.$value) : $value;
    }

    /** @return array<string, array<int, string>> kind => values in display order */
    public function valuesFor(User $user): array
    {
        $out = array_fill_keys(GuideTag::kinds(), []);
        foreach ($user->guideTags()->get() as $tag) {
            $out[$tag->kind][] = $tag->value;
        }

        return $out;
    }

    /**
     * Replace a guide's tags of one kind with $values (order kept, duplicates dropped).
     * A typed value that matches a suggestion's label is stored as that suggestion's key.
     *
     * @param  array<int, string>  $values
     */
    public function sync(User $user, string $kind, array $values): void
    {
        $labels = array_map('mb_strtolower', $this->suggestions($kind));
        $clean = [];
        foreach ($values as $value) {
            $value = trim((string) $value);
            if ($value === '') {
                continue;
            }
            if (! $this->isSuggestion($kind, $value)) {
                $key = array_search(mb_strtolower($value), $labels, true);
                $value = $key !== false ? (string) $key : $value;
            }
            $clean[mb_strtolower($value)] = $value;
        }
        $clean = array_values($clean);

        $user->guideTags()->where('kind', $kind)->whereNotIn('value', $clean ?: [''])->delete();

        foreach ($clean as $position => $value) {
            GuideTag::updateOrCreate(
                ['user_id' => $user->id, 'kind' => $kind, 'value' => $value],
                ['is_custom' => ! $this->isSuggestion($kind, $value), 'position' => $position]
            );
        }
    }
}
