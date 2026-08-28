<?php

namespace App\Repositories\Guiding;

use App\Domain\Vacation\CountrySlug;
use App\Models\Guiding;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Which countries, target-fish, and methods have at least one publicly visible
 * guiding (tour). Used to gate the tours-pillar category listings/nav so a
 * country/target/method with zero tours does not appear there, even when it
 * has vacation (trip/camp) listings.
 */
class GuidingCategoryAvailabilityRepository
{
    private const CACHE_KEY = 'guiding_category_availability_v1';

    private const CACHE_MINUTES = 60;

    private ?array $data = null;

    public function hasGuidingsForCountry(string $slug, ?string $countryShort = null): bool
    {
        $countries = $this->data()['countries'];

        foreach (CountrySlug::storageVariants($slug, $countryShort) as $variant) {
            $key = mb_strtolower(trim($variant), 'UTF-8');
            if ($key !== '' && isset($countries[$key])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return list<int>
     */
    public function targetIdsWithGuidings(): array
    {
        return array_keys($this->data()['targets']);
    }

    /**
     * @return list<int>
     */
    public function methodIdsWithGuidings(): array
    {
        return array_keys($this->data()['methods']);
    }

    public function hasGuidingsForTarget(int $targetId): bool
    {
        return isset($this->data()['targets'][$targetId]);
    }

    public function hasGuidingsForMethod(int $methodId): bool
    {
        return isset($this->data()['methods'][$methodId]);
    }

    /**
     * @return array{countries: array<string, true>, targets: array<int, true>, methods: array<int, true>}
     */
    private function data(): array
    {
        if ($this->data !== null) {
            return $this->data;
        }

        return $this->data = Cache::remember(
            self::CACHE_KEY,
            now()->addMinutes(self::CACHE_MINUTES),
            fn () => $this->build(),
        );
    }

    /**
     * @return array{countries: array<string, true>, targets: array<int, true>, methods: array<int, true>}
     */
    private function build(): array
    {
        $countries = [];
        $targets = [];
        $methods = [];

        Guiding::query()
            ->publiclyVisible()
            ->select(['id', 'country', 'country_iso', 'target_fish', 'fishing_methods'])
            ->orderBy('id')
            ->chunkById(500, function (Collection $guidings) use (&$countries, &$targets, &$methods) {
                foreach ($guidings as $guiding) {
                    // target_fish/fishing_methods also name HasMany relations on the model,
                    // so read the raw column value to avoid triggering relation resolution.
                    $attributes = $guiding->getAttributes();

                    $country = $attributes['country'] ?? null;
                    if (is_string($country) && trim($country) !== '') {
                        foreach (CountrySlug::storageVariants($country, $attributes['country_iso'] ?? null) as $variant) {
                            $key = mb_strtolower(trim($variant), 'UTF-8');
                            if ($key !== '') {
                                $countries[$key] = true;
                            }
                        }
                    }

                    foreach ($this->decodeIds($attributes['target_fish'] ?? null) as $id) {
                        $targets[$id] = true;
                    }

                    foreach ($this->decodeIds($attributes['fishing_methods'] ?? null) as $id) {
                        $methods[$id] = true;
                    }
                }
            });

        return [
            'countries' => $countries,
            'targets' => $targets,
            'methods' => $methods,
        ];
    }

    /**
     * @return list<int>
     */
    private function decodeIds(mixed $raw): array
    {
        if ($raw === null || $raw === '') {
            return [];
        }

        $decoded = is_array($raw) ? $raw : json_decode((string) $raw, true);
        if (! is_array($decoded)) {
            return [];
        }

        return array_values(array_unique(array_filter(array_map(
            static fn ($value) => is_numeric($value) ? (int) $value : null,
            $decoded,
        ), static fn ($value) => $value !== null)));
    }
}
