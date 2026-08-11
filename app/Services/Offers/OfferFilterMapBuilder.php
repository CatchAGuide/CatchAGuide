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
            'tours' => ['targets' => array_fill_keys($knownIds, [])],
            'trips' => ['targets' => array_fill_keys($knownIds, [])],
            'camps' => ['targets' => array_fill_keys($knownIds, [])],
            'targets_by_country' => [],
        ];

        $tourCount = 0;
        Guiding::query()
            ->publiclyVisible()
            ->select(['id', 'target_fish', 'country', 'country_iso'])
            ->orderBy('id')
            ->chunkById(500, function (Collection $guidings) use (&$map, &$tourCount, $knownIds, $nameToId) {
                foreach ($guidings as $guiding) {
                    $tourCount++;
                    $targetIds = $this->resolveTargetIds($guiding->getAttributes()['target_fish'] ?? $guiding->target_fish, $knownIds, $nameToId);
                    foreach ($targetIds as $targetId) {
                        $map['tours']['targets'][$targetId][] = (int) $guiding->id;
                    }
                    $this->indexCountryTargets(
                        $map['targets_by_country'],
                        $guiding->country,
                        $guiding->country_iso,
                        $targetIds
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
                    $targetIds = $this->resolveTargetIds($raw, $knownIds, $nameToId);
                    foreach ($targetIds as $targetId) {
                        $map['trips']['targets'][$targetId][] = (int) $trip->id;
                    }
                    $this->indexCountryTargets($map['targets_by_country'], $trip->country, null, $targetIds);
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
                    $targetIds = $this->resolveTargetIds($raw, $knownIds, $nameToId);
                    foreach ($targetIds as $targetId) {
                        $map['camps']['targets'][$targetId][] = (int) $camp->id;
                    }
                    $this->indexCountryTargets($map['targets_by_country'], $camp->country, null, $targetIds);
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

        $map['metadata'] = [
            'generated_at' => now()->toISOString(),
            'total_tours' => $tourCount,
            'total_trips' => $tripCount,
            'total_camps' => $campCount,
            'counts' => [
                'tours' => array_map('count', $map['tours']['targets']),
                'trips' => array_map('count', $map['trips']['targets']),
                'camps' => array_map('count', $map['camps']['targets']),
                'countries' => count($map['targets_by_country']),
            ],
        ];

        return $map;
    }

    /**
     * @param  array<string, array<int, true>>  $targetsByCountry
     * @param  list<int>  $targetIds
     */
    private function indexCountryTargets(array &$targetsByCountry, ?string $country, ?string $countryIso, array $targetIds): void
    {
        if ($targetIds === [] || ($country === null || trim($country) === '') && ($countryIso === null || trim($countryIso) === '')) {
            return;
        }

        $canonical = CountrySlug::canonicalize($country) ?? CountrySlug::canonicalize($countryIso);
        if ($canonical === null) {
            return;
        }

        foreach (CountrySlug::storageVariants($canonical, $countryIso) as $variant) {
            $key = mb_strtolower(trim($variant), 'UTF-8');
            if ($key === '') {
                continue;
            }
            foreach ($targetIds as $targetId) {
                $targetsByCountry[$key][$targetId] = true;
            }
        }
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
     * @param  array<int, int>  $knownIds
     * @param  array<string, int>  $nameToId
     * @return list<int>
     */
    private function resolveTargetIds(mixed $raw, array $knownIds, array $nameToId): array
    {
        $items = $this->normalizeRawSpecies($raw);
        if ($items === []) {
            return [];
        }

        $ids = [];
        $knownLookup = array_fill_keys($knownIds, true);

        foreach ($items as $item) {
            if (is_array($item)) {
                if (isset($item['id']) && is_numeric($item['id'])) {
                    $id = (int) $item['id'];
                    if (isset($knownLookup[$id])) {
                        $ids[$id] = $id;
                    }
                }
                $name = $item['name'] ?? $item['value'] ?? null;
                if (is_numeric($name) && isset($knownLookup[(int) $name])) {
                    $ids[(int) $name] = (int) $name;
                } elseif (is_string($name) && $name !== '') {
                    $key = mb_strtolower(trim($name), 'UTF-8');
                    if (isset($nameToId[$key])) {
                        $ids[$nameToId[$key]] = $nameToId[$key];
                    }
                }
                continue;
            }

            if (is_numeric($item) && isset($knownLookup[(int) $item])) {
                $ids[(int) $item] = (int) $item;
                continue;
            }

            if (is_string($item) && trim($item) !== '') {
                $key = mb_strtolower(trim($item), 'UTF-8');
                if (isset($nameToId[$key])) {
                    $ids[$nameToId[$key]] = $nameToId[$key];
                }
            }
        }

        return array_values($ids);
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
