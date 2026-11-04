<?php

namespace App\Services\Vacation;

use App\Models\Vacation;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;

/**
 * Saves a vacation's accommodations, boats, packages, guidings and extras from the admin form.
 *
 * Rows are updated in place by id. Deleting and recreating them on every save gave each item a
 * new id, which cut the link from existing bookings (package_id, guiding_id, ...) and from the
 * stored translations (languages.source_id).
 */
class VacationComponentSync
{
    public function sync(Vacation $vacation, Request $request): void
    {
        $this->syncRelation($vacation->accommodations(), $request->input('accommodations', []), fn (array $item) => [
            ...$this->priced($item),
            'dynamic_fields' => json_encode([
                'prices' => array_values($item['prices'] ?? []),
                'bed_count' => $item['bed_count'] ?? '',
                'living_area' => $item['living_area'] ?? '',
                'min_rental_days' => $item['min_rental_days'] ?? '',
                'facilities' => $item['facilities'] ?? '',
            ]),
        ]);

        $this->syncRelation($vacation->boats(), $request->input('boats', []), fn (array $item) => [
            ...$this->priced($item),
            'dynamic_fields' => json_encode([
                'prices' => array_values($item['prices'] ?? []),
                'facilities' => $item['facilities'] ?? '',
            ]),
        ]);

        $this->syncRelation($vacation->packages(), $request->input('packages', []), fn (array $item) => [
            ...$this->priced($item),
            'dynamic_fields' => json_encode([
                'prices' => array_values($item['prices'] ?? []),
                'catering_info' => $item['catering_info'] ?? '',
            ]),
        ]);

        $this->syncRelation($vacation->guidings(), $request->input('guidings', []), fn (array $item) => [
            ...$this->priced($item),
            'dynamic_fields' => json_encode([
                'prices' => array_values($item['prices'] ?? []),
            ]),
        ]);

        $this->syncRelation($vacation->extras(), $request->input('extras', []), fn (array $item) => [
            'description' => (string) ($item['description'] ?? ''),
            'price' => (float) ($item['price'] ?? 0),
            'type' => $item['price_type'] ?? 'per_person',
        ], requiresTitle: false);
    }

    /**
     * @param  mixed  $items  the submitted rows, keyed by the form's card index
     * @param  callable(array): array  $attributes
     */
    private function syncRelation(HasMany $relation, mixed $items, callable $attributes, bool $requiresTitle = true): void
    {
        $existing = $relation->get()->keyBy('id');
        $kept = [];

        foreach (is_array($items) ? $items : [] as $item) {
            if (! is_array($item) || $this->isBlank($item, $requiresTitle)) {
                continue;
            }

            $id = (int) ($item['id'] ?? 0);
            $row = $existing->get($id);

            if ($row) {
                $row->update($attributes($item));
                $kept[] = $row->id;

                continue;
            }

            $kept[] = $relation->create($attributes($item))->id;
        }

        $existing->except($kept)->each->delete();
    }

    /**
     * An "add" click the admin never filled in. The columns are NOT NULL, so saving it
     * failed the whole vacation update.
     */
    private function isBlank(array $item, bool $requiresTitle): bool
    {
        $description = trim(strip_tags((string) ($item['description'] ?? '')));

        if (! $requiresTitle) {
            return $description === '';
        }

        return $description === '' && trim((string) ($item['title'] ?? '')) === '';
    }

    /**
     * @return array{title: ?string, description: string, capacity: int}
     */
    private function priced(array $item): array
    {
        $title = trim((string) ($item['title'] ?? ''));

        return [
            'title' => $title !== '' ? $title : null,
            'description' => (string) ($item['description'] ?? ''),
            'capacity' => max(1, (int) ($item['capacity'] ?? 1)),
        ];
    }
}
