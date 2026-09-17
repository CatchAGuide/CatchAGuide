<?php

namespace App\Services\Vacation;

use App\Models\Target;
use Illuminate\Support\Collection;

/**
 * Resolves stored camp/listing target-fish values to locale-aware {id, name} rows.
 *
 * Numeric IDs and names that match Target.name / Target.name_en come from the
 * targets table (same pattern as Guiding::getTargetFishNames and Trip).
 * Unmatched custom strings keep id = null so the view can run translate().
 *
 * Camp target_fish is array-cast. Legacy saves stored a CSV string, so the raw
 * column is a JSON string ("Flussbarsch,Hecht,\u00c4sche") — wrapping quotes
 * and unicode escapes must be unwrapped before matching names.
 */
class TargetFishNameResolver
{
    /**
     * @param  mixed  $raw
     * @param  Collection<int, Target>|null  $targetsMap
     * @return array<int, array{id: int|null, name: string}>
     */
    public function resolve(mixed $raw, ?Collection $targetsMap = null): array
    {
        $items = $this->normalize($raw);

        if ($items === []) {
            return [];
        }

        $map = $targetsMap ?? $this->loadTargets($items);

        return collect($items)
            ->map(fn ($item) => $this->mapItem($item, $map))
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, Target>  $map
     * @return array{id: int|null, name: string}|null
     */
    private function mapItem(mixed $item, Collection $map): ?array
    {
        if (is_array($item)) {
            $label = trim((string) ($item['name'] ?? $item['value'] ?? ''));
            if ($label === '') {
                if (isset($item['id']) && is_numeric($item['id'])) {
                    $item = (int) $item['id'];
                } else {
                    return null;
                }
            } else {
                $id = isset($item['id']) && is_numeric($item['id']) ? (int) $item['id'] : null;
                $name = $this->sanitizeLabel($label);

                if ($id && $map->has($id) && $map->get($id)?->name) {
                    return ['id' => $id, 'name' => (string) $map->get($id)->name];
                }

                $matched = $this->matchByName($name, $map);
                if ($matched !== null) {
                    return $matched;
                }

                return $name !== '' ? ['id' => $id, 'name' => $name] : null;
            }
        }

        if (is_numeric($item) && ! is_string($item)) {
            $target = $map->get((int) $item);
            if ($target && $target->name) {
                return ['id' => (int) $target->id, 'name' => (string) $target->name];
            }

            return null;
        }

        if (is_numeric($item) && is_string($item) && ctype_digit(trim($item))) {
            $target = $map->get((int) $item);
            if ($target && $target->name) {
                return ['id' => (int) $target->id, 'name' => (string) $target->name];
            }

            return null;
        }

        if (is_string($item) && trim($item) !== '') {
            $name = $this->sanitizeLabel($item);
            if ($name === '') {
                return null;
            }

            $matched = $this->matchByName($name, $map);
            if ($matched !== null) {
                return $matched;
            }

            return ['id' => null, 'name' => $name];
        }

        return null;
    }

    /**
     * @param  Collection<int, Target>  $map
     * @return array{id: int, name: string}|null
     */
    private function matchByName(string $name, Collection $map): ?array
    {
        $needle = mb_strtolower($this->sanitizeLabel($name));

        if ($needle === '') {
            return null;
        }

        foreach ($map as $target) {
            $attributes = $target->getAttributes();
            $de = mb_strtolower(trim((string) ($attributes['name'] ?? '')));
            $en = mb_strtolower(trim((string) ($attributes['name_en'] ?? '')));

            if ($needle === $de || ($en !== '' && $needle === $en)) {
                return ['id' => (int) $target->id, 'name' => (string) $target->name];
            }
        }

        return null;
    }

    /**
     * @param  list<mixed>  $items
     * @return Collection<int, Target>
     */
    private function loadTargets(array $items): Collection
    {
        $ids = [];
        $names = [];

        foreach ($items as $item) {
            if (is_array($item)) {
                if (isset($item['id']) && is_numeric($item['id'])) {
                    $ids[] = (int) $item['id'];
                }
                $label = $item['name'] ?? $item['value'] ?? null;
                if (is_string($label) && ! is_numeric($label)) {
                    $clean = $this->sanitizeLabel($label);
                    if ($clean !== '') {
                        $names[] = $clean;
                    }
                }
                continue;
            }

            if (is_numeric($item) && (is_int($item) || is_float($item) || ctype_digit(trim((string) $item)))) {
                $ids[] = (int) $item;
                continue;
            }

            if (is_string($item) && trim($item) !== '') {
                $clean = $this->sanitizeLabel($item);
                if ($clean !== '') {
                    $names[] = $clean;
                }
            }
        }

        $ids = array_values(array_unique($ids));
        $names = array_values(array_unique($names));

        if ($ids === [] && $names === []) {
            return collect();
        }

        return Target::query()
            ->where(function ($query) use ($ids, $names) {
                if ($ids !== []) {
                    $query->whereIn('id', $ids);
                }

                if ($names !== []) {
                    $method = $ids !== [] ? 'orWhere' : 'where';
                    $query->{$method}(function ($inner) use ($names) {
                        $inner->whereIn('name', $names)->orWhereIn('name_en', $names);
                    });
                }
            })
            ->get()
            ->keyBy('id');
    }

    /**
     * @return list<mixed>
     */
    private function normalize(mixed $raw): array
    {
        if ($raw === null || $raw === '') {
            return [];
        }

        if (is_int($raw) || is_float($raw)) {
            return [$raw];
        }

        if (is_array($raw)) {
            // Array cast of a CSV string can yield a one-item array containing the CSV.
            if (count($raw) === 1 && (is_string($raw[0]) && (str_contains($raw[0], ',') || $this->looksLikeJson((string) $raw[0])))) {
                return $this->normalize($raw[0]);
            }

            return $this->filterItems(array_values($raw));
        }

        if (! is_string($raw)) {
            return [];
        }

        $trimmed = trim($raw);
        if ($trimmed === '') {
            return [];
        }

        $decoded = json_decode($trimmed, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            if (is_array($decoded)) {
                return $this->normalize($decoded);
            }

            // JSON-encoded CSV: "\"Flussbarsch,Hecht,\u00c4sche\""
            if (is_string($decoded) && $decoded !== '' && $decoded !== $trimmed) {
                return $this->normalize($decoded);
            }

            if (is_int($decoded) || is_float($decoded)) {
                return [$decoded];
            }
        }

        if (str_contains($trimmed, ',')) {
            return $this->filterItems(array_map('trim', explode(',', $trimmed)));
        }

        return $this->filterItems([$trimmed]);
    }

    /**
     * @param  list<mixed>  $items
     * @return list<mixed>
     */
    private function filterItems(array $items): array
    {
        return array_values(array_filter($items, function ($item) {
            if (is_array($item)) {
                $name = $item['name'] ?? $item['value'] ?? '';
                if (is_string($name) && $this->sanitizeLabel($name) !== '') {
                    return true;
                }

                return isset($item['id']) && is_numeric($item['id']);
            }

            if (is_string($item)) {
                return $this->sanitizeLabel($item) !== '';
            }

            return $item !== null && $item !== '';
        }));
    }

    private function looksLikeJson(string $value): bool
    {
        $trimmed = ltrim($value);

        return $trimmed !== '' && ($trimmed[0] === '[' || $trimmed[0] === '{' || $trimmed[0] === '"');
    }

    /**
     * Strip JSON/HTML leftover encoding from a stored fish name.
     */
    private function sanitizeLabel(string $name): string
    {
        $name = trim($name);
        if ($name === '') {
            return '';
        }

        $name = html_entity_decode($name, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $name = trim($name);

        if (str_contains($name, '\\u')) {
            $decoded = json_decode('"'.str_replace('"', '\\"', $name).'"');
            if (is_string($decoded) && $decoded !== '') {
                $name = $decoded;
            }
        }

        $name = trim($name);
        $name = preg_replace('/^["\']+|["\']+$/u', '', $name) ?? $name;

        return trim($name);
    }
}
