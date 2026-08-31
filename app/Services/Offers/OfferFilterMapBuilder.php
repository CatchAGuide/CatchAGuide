<?php

namespace App\Services\Offers;

use App\Domain\Vacation\CountrySlug;
use App\Models\Camp;
use App\Models\Guiding;
use App\Models\Target;
use App\Models\Trip;
use Illuminate\Support\Collection;

/**
 * Precomputes "target fish => listing ids" maps for tours, trips, and camps.
 * Mirrors GuidingFilterMapBuilder so /offers can filter without JSON LIKE scans.
 */
class OfferFilterMapBuilder
{
    public function fingerprint(): string
    {
        $tours = Guiding::query()
            ->publiclyVisible()
            ->selectRaw('COUNT(*) AS total, COALESCE(MAX(guidings.updated_at), 0) AS last_touched, COALESCE(MAX(guidings.id), 0) AS last_id')
            ->first();

        $trips = Trip::query()
            ->where('status', 'active')
            ->selectRaw('COUNT(*) AS total, COALESCE(MAX(updated_at), 0) AS last_touched, COALESCE(MAX(id), 0) AS last_id')
            ->first();

        $camps = Camp::query()
            ->where('status', 'active')
            ->selectRaw('COUNT(*) AS total, COALESCE(MAX(updated_at), 0) AS last_touched, COALESCE(MAX(id), 0) AS last_id')
            ->first();

        return md5(implode('|', [
            $tours->total ?? 0,
            $tours->last_touched ?? '',
            $tours->last_id ?? 0,
            $trips->total ?? 0,
            $trips->last_touched ?? '',
            $trips->last_id ?? 0,
            $camps->total ?? 0,
            $camps->last_touched ?? '',
            $camps->last_id ?? 0,
        ]));
    }

    public function build(): array
    {
        $targets = Target::query()->get(['id', 'name', 'name_en']);
        $knownIds = $targets->pluck('id')->map(fn ($id) => (int) $id)->all();
        $nameToId = $this->buildNameLookup($targets);

        $map = [
            'tours' => [
                'targets' => array_fill_keys($knownIds, []),
                'custom_targets' => [],
            ],
            'trips' => [
                'targets' => array_fill_keys($knownIds, []),
                'custom_targets' => [],
            ],
            'camps' => [
                'targets' => array_fill_keys($knownIds, []),
                'custom_targets' => [],
            ],
            'targets_by_country' => [],
            'custom_targets_by_country' => [],
            'custom_target_labels' => [],
        ];

        $tourCount = 0;
        Guiding::query()
            ->publiclyVisible()
            ->select(['id', 'target_fish', 'country', 'country_iso'])
            ->orderBy('id')
            ->chunkById(500, function (Collection $guidings) use (&$map, &$tourCount, $knownIds, $nameToId) {
                foreach ($guidings as $guiding) {
                    $tourCount++;
                    $resolved = $this->resolveSpecies(
                        $guiding->getAttributes()['target_fish'] ?? $guiding->target_fish,
                        $knownIds,
                        $nameToId
                    );
                    $this->indexListingSpecies(
                        $map,
                        'tours',
                        (int) $guiding->id,
                        $resolved,
                        $guiding->country,
                        $guiding->country_iso
                    );
                }
            });

        $tripCount = 0;
        Trip::query()
            ->where('status', 'active')
            ->select(['id', 'target_species', 'country'])
            ->orderBy('id')
            ->chunkById(500, function (Collection $trips) use (&$map, &$tripCount, $knownIds, $nameToId) {
                foreach ($trips as $trip) {
                    $tripCount++;
                    $raw = $trip->getAttributes()['target_species'] ?? $trip->target_species;
                    $resolved = $this->resolveSpecies($raw, $knownIds, $nameToId);
                    $this->indexListingSpecies($map, 'trips', (int) $trip->id, $resolved, $trip->country, null);
                }
            });

        $campCount = 0;
        Camp::query()
            ->where('status', 'active')
            ->select(['id', 'target_fish', 'country'])
            ->orderBy('id')
            ->chunkById(500, function (Collection $camps) use (&$map, &$campCount, $knownIds, $nameToId) {
                foreach ($camps as $camp) {
                    $campCount++;
                    $raw = $camp->getAttributes()['target_fish'] ?? $camp->target_fish;
                    $resolved = $this->resolveSpecies($raw, $knownIds, $nameToId);
                    $this->indexListingSpecies($map, 'camps', (int) $camp->id, $resolved, $camp->country, null);
                }
            });

        foreach (['tours', 'trips', 'camps'] as $pillar) {
            $map[$pillar]['targets'] = array_filter(
                $map[$pillar]['targets'],
                fn ($ids) => $ids !== []
            );
        }

        foreach ($map['targets_by_country'] as $country => $targetSet) {
            $map['targets_by_country'][$country] = array_map('intval', array_keys($targetSet));
        }

        foreach ($map['custom_targets_by_country'] as $country => $customSet) {
            $map['custom_targets_by_country'][$country] = array_keys($customSet);
        }

        $map['metadata'] = [
            'generated_at' => now()->toISOString(),
            'total_tours' => $tourCount,
            'total_trips' => $tripCount,
            'total_camps' => $campCount,
            'counts' => [
                'tours' => array_map('count', $map['tours']['targets']),
                'trips' => array_map('count', $map['trips']['targets']),
                'camps' => array_map('count', $map['camps']['targets']),
                'custom_tours' => array_map('count', $map['tours']['custom_targets']),
                'custom_trips' => array_map('count', $map['trips']['custom_targets']),
                'custom_camps' => array_map('count', $map['camps']['custom_targets']),
                'countries' => count($map['targets_by_country']),
            ],
        ];

        return $map;
    }

    /**
     * @param  array{ids: list<int>, customs: array<string, string>}  $resolved
     */
    private function indexListingSpecies(
        array &$map,
        string $pillar,
        int $listingId,
        array $resolved,
        ?string $country,
        ?string $countryIso,
    ): void {
        foreach ($resolved['ids'] as $targetId) {
            $map[$pillar]['targets'][$targetId][] = $listingId;
        }

        foreach ($resolved['customs'] as $key => $label) {
            $map[$pillar]['custom_targets'][$key][] = $listingId;
            if (! isset($map['custom_target_labels'][$key])) {
                $map['custom_target_labels'][$key] = $label;
            }
        }

        $this->indexCountryTargets(
            $map['targets_by_country'],
            $country,
            $countryIso,
            $resolved['ids']
        );
        $this->indexCountryCustomTargets(
            $map['custom_targets_by_country'],
            $country,
            $countryIso,
            array_keys($resolved['customs'])
        );
    }

    /**
     * @param  array<string, array<int, true>>  $targetsByCountry
     * @param  list<int>  $targetIds
     */
    private function indexCountryTargets(array &$targetsByCountry, ?string $country, ?string $countryIso, array $targetIds): void
    {
        if ($targetIds === []) {
            return;
        }

        foreach ($this->countryIndexKeys($country, $countryIso) as $key) {
            foreach ($targetIds as $targetId) {
                $targetsByCountry[$key][$targetId] = true;
            }
        }
    }

    /**
     * @param  array<string, array<string, true>>  $customByCountry
     * @param  list<string>  $customKeys
     */
    private function indexCountryCustomTargets(array &$customByCountry, ?string $country, ?string $countryIso, array $customKeys): void
    {
        if ($customKeys === []) {
            return;
        }

        foreach ($this->countryIndexKeys($country, $countryIso) as $countryKey) {
            foreach ($customKeys as $customKey) {
                $customByCountry[$countryKey][$customKey] = true;
            }
        }
    }

    /**
     * @return list<string>
     */
    private function countryIndexKeys(?string $country, ?string $countryIso): array
    {
        if (($country === null || trim($country) === '') && ($countryIso === null || trim($countryIso) === '')) {
            return [];
        }

        $canonical = CountrySlug::canonicalize($country) ?? CountrySlug::canonicalize($countryIso);
        if ($canonical === null) {
            return [];
        }

        $keys = [];
        foreach (CountrySlug::storageVariants($canonical, $countryIso) as $variant) {
            $key = mb_strtolower(trim($variant), 'UTF-8');
            if ($key !== '') {
                $keys[] = $key;
            }
        }

        return $keys;
    }

    /**
     * @param  Collection<int, Target>  $targets
     * @return array<string, int>
     */
    private function buildNameLookup(Collection $targets): array
    {
        $lookup = [];

        foreach ($targets as $target) {
            $attrs = $target->getAttributes();
            foreach (['name', 'name_en'] as $field) {
                $name = mb_strtolower(trim((string) ($attrs[$field] ?? '')), 'UTF-8');
                if ($name !== '') {
                    $lookup[$name] = (int) $target->id;
                }
            }
        }

        return $lookup;
    }

    /**
     * Map known catalog ids/names and keep unmatched free-text as custom labels.
     * Does not read legacy guidings.target_fish_sonstiges.
     *
     * @param  array<int, int>  $knownIds
     * @param  array<string, int>  $nameToId
     * @return array{ids: list<int>, customs: array<string, string>}
     */
    private function resolveSpecies(mixed $raw, array $knownIds, array $nameToId): array
    {
        $items = $this->normalizeRawSpecies($raw);
        if ($items === []) {
            return ['ids' => [], 'customs' => []];
        }

        $ids = [];
        $customs = [];
        $knownLookup = array_fill_keys($knownIds, true);

        foreach ($items as $item) {
            if (is_array($item)) {
                $matched = false;
                if (isset($item['id']) && is_numeric($item['id'])) {
                    $id = (int) $item['id'];
                    if (isset($knownLookup[$id])) {
                        $ids[$id] = $id;
                        $matched = true;
                    }
                }
                $name = $item['name'] ?? $item['value'] ?? null;
                if (is_numeric($name) && isset($knownLookup[(int) $name])) {
                    $ids[(int) $name] = (int) $name;
                    $matched = true;
                } elseif (is_string($name) && trim($name) !== '') {
                    $label = trim($name);
                    $key = mb_strtolower($label, 'UTF-8');
                    if (isset($nameToId[$key])) {
                        $ids[$nameToId[$key]] = $nameToId[$key];
                        $matched = true;
                    } elseif (! $matched) {
                        $customs[$key] = $label;
                    }
                }
                continue;
            }

            if (is_numeric($item) && isset($knownLookup[(int) $item])) {
                $ids[(int) $item] = (int) $item;
                continue;
            }

            if (is_string($item) && trim($item) !== '') {
                $label = trim($item);
                $key = mb_strtolower($label, 'UTF-8');
                if (isset($nameToId[$key])) {
                    $ids[$nameToId[$key]] = $nameToId[$key];
                } else {
                    $customs[$key] = $label;
                }
            }
        }

        return [
            'ids' => array_values($ids),
            'customs' => $customs,
        ];
    }

    /**
     * Accept JSON arrays, Tagify objects, and camp CSV strings ("Hecht,Wels,Zander").
     *
     * @return list<mixed>
     */
    private function normalizeRawSpecies(mixed $raw): array
    {
        if ($raw === null || $raw === '') {
            return [];
        }

        if (is_array($raw)) {
            // Camp cast sometimes yields a single CSV string inside a 1-item array.
            if (count($raw) === 1 && is_string($raw[0]) && str_contains($raw[0], ',')) {
                return array_values(array_filter(array_map('trim', explode(',', $raw[0]))));
            }

            return $raw;
        }

        if (! is_string($raw)) {
            return [];
        }

        $trimmed = trim($raw);
        if ($trimmed === '') {
            return [];
        }

        $decoded = json_decode($trimmed, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        // JSON-encoded plain string: "\"Hecht,Wels\""
        if (is_string($decoded) && $decoded !== '') {
            $trimmed = trim($decoded);
        }

        if (str_contains($trimmed, ',')) {
            return array_values(array_filter(array_map('trim', explode(',', $trimmed))));
        }

        return [$trimmed];
    }
}
