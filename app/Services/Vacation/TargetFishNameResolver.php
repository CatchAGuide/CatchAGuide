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
        if (is_array($item) && isset($item['name']) && trim((string) $item['name']) !== '') {
            $id = isset($item['id']) && is_numeric($item['id']) ? (int) $item['id'] : null;

            if ($id && $map->has($id) && $map->get($id)?->name) {
                return ['id' => $id, 'name' => (string) $map->get($id)->name];
            }

            $matched = $this->matchByName((string) $item['name'], $map);
            if ($matched !== null) {
                return $matched;
            }

            return ['id' => $id, 'name' => (string) $item['name']];
        }

        if (is_numeric($item)) {
            $target = $map->get((int) $item);
            if ($target && $target->name) {
                return ['id' => (int) $target->id, 'name' => (string) $target->name];
            }

            return null;
        }

        if (is_string($item) && trim($item) !== '') {
            $matched = $this->matchByName($item, $map);
            if ($matched !== null) {
                return $matched;
            }

            return ['id' => null, 'name' => trim($item)];
        }

        return null;
    }

    /**
     * @param  Collection<int, Target>  $map
     * @return array{id: int, name: string}|null
     */
    private function matchByName(string $name, Collection $map): ?array
    {
        $needle = mb_strtolower(trim($name));

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
                if (! empty($item['name']) && is_string($item['name']) && ! is_numeric($item['name'])) {
                    $names[] = trim($item['name']);
                }
                continue;
            }

            if (is_numeric($item)) {
                $ids[] = (int) $item;
                continue;
            }

            if (is_string($item) && trim($item) !== '') {
                $names[] = trim($item);
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

        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $raw = $decoded;
            } else {
                $raw = array_map('trim', explode(',', $raw));
            }
        }

        if (! is_array($raw)) {
            return [];
        }

        return array_values(array_filter($raw, function ($item) {
            if (is_array($item)) {
                return trim((string) ($item['name'] ?? $item['value'] ?? '')) !== ''
                    || (isset($item['id']) && is_numeric($item['id']));
            }

            return $item !== null && $item !== '';
        }));
    }
}
