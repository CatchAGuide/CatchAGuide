<?php

namespace App\Services\Sitemap;

use App\Domain\Vacation\BookableListingPolicy;
use App\Domain\Vacation\CountrySlug;
use App\Models\Camp;
use App\Models\Guiding;
use App\Models\Trip;
use Illuminate\Support\Collection;

/**
 * When a facet page's listing set last changed: the newest updated_at among the listings the
 * page shows. Combined with the page's own content date (SitemapLastmod) so a facet's lastmod
 * moves when a tour/camp/trip in it changes, and only then. Built in one pass per listing type.
 *
 * Covered: tour country/species/method, vacation country (all or per pillar). Not covered —
 * content date only: vacation species (stored as free-text names, no reliable id) and
 * region/city pages (matched by geo search, not a column).
 */
class SitemapListingFreshness
{
    /** @var array{country: array<string, string>, target: array<int, string>, method: array<int, string>}|null */
    private ?array $tours = null;

    /** @var array<string, array<string, string>>|null pillar (camps|trips) => canonical country => newest */
    private ?array $vacations = null;

    public function __construct(
        private readonly BookableListingPolicy $policy,
    ) {}

    public function tourCountry(string $slug, ?string $countryCode = null): ?string
    {
        $countries = $this->tours()['country'];
        $newest = null;
        foreach (CountrySlug::storageVariants($slug, $countryCode) as $variant) {
            $newest = $this->newer($newest, $countries[mb_strtolower(trim($variant), 'UTF-8')] ?? null);
        }

        return $newest;
    }

    public function tourTarget(int $targetId): ?string
    {
        return $this->tours()['target'][$targetId] ?? null;
    }

    public function tourMethod(int $methodId): ?string
    {
        return $this->tours()['method'][$methodId] ?? null;
    }

    /**
     * @param  ?string  $pillar  'camps', 'trips', or null for both
     */
    public function vacationCountry(string $slug, ?string $pillar = null): ?string
    {
        $slug = CountrySlug::canonicalize($slug) ?? $slug;
        $newest = null;
        foreach ($pillar === null ? ['camps', 'trips'] : [$pillar] as $key) {
            $newest = $this->newer($newest, $this->vacations()[$key][$slug] ?? null);
        }

        return $newest;
    }

    /**
     * @return array{country: array<string, string>, target: array<int, string>, method: array<int, string>}
     */
    private function tours(): array
    {
        if ($this->tours !== null) {
            return $this->tours;
        }

        $this->tours = ['country' => [], 'target' => [], 'method' => []];
        Guiding::query()
            ->publiclyVisible()
            ->select(['guidings.id', 'country', 'country_iso', 'target_fish', 'fishing_methods', 'guidings.updated_at'])
            ->orderBy('guidings.id')
            ->chunkById(500, function (Collection $guidings) {
                foreach ($guidings as $guiding) {
                    $attributes = $guiding->getAttributes();
                    $updated = (string) ($attributes['updated_at'] ?? '');
                    if ($updated === '') {
                        continue;
                    }

                    $country = $attributes['country'] ?? null;
                    if (is_string($country) && trim($country) !== '') {
                        foreach (CountrySlug::storageVariants($country, $attributes['country_iso'] ?? null) as $variant) {
                            $this->bump($this->tours['country'], mb_strtolower(trim($variant), 'UTF-8'), $updated);
                        }
                    }
                    foreach ($this->decodeIds($attributes['target_fish'] ?? null) as $id) {
                        $this->bump($this->tours['target'], $id, $updated);
                    }
                    foreach ($this->decodeIds($attributes['fishing_methods'] ?? null) as $id) {
                        $this->bump($this->tours['method'], $id, $updated);
                    }
                }
            }, 'guidings.id', 'id');

        return $this->tours;
    }

    /**
     * @return array<string, array<string, string>>
     */
    private function vacations(): array
    {
        if ($this->vacations !== null) {
            return $this->vacations;
        }

        $this->vacations = ['camps' => [], 'trips' => []];
        foreach (['camps' => Camp::class, 'trips' => Trip::class] as $pillar => $model) {
            $model::query()
                ->where('status', $this->policy->activeStatus())
                ->whereNotNull('country')
                ->toBase()
                ->selectRaw('country, MAX(updated_at) as last_updated')
                ->groupBy('country')
                ->get()
                ->each(function ($row) use ($pillar) {
                    $slug = CountrySlug::canonicalize((string) $row->country);
                    if ($slug !== null && $row->last_updated !== null) {
                        $this->bump($this->vacations[$pillar], $slug, (string) $row->last_updated);
                    }
                });
        }

        return $this->vacations;
    }

    private function bump(array &$map, string|int $key, string $timestamp): void
    {
        $map[$key] = $this->newer($map[$key] ?? null, $timestamp);
    }

    private function newer(?string $a, ?string $b): ?string
    {
        if ($a === null) {
            return $b;
        }
        if ($b === null) {
            return $a;
        }

        return strtotime($b) > strtotime($a) ? $b : $a;
    }

    /**
     * @return list<int>
     */
    private function decodeIds(mixed $raw): array
    {
        $decoded = is_array($raw) ? $raw : json_decode((string) $raw, true);
        if (! is_array($decoded)) {
            return [];
        }

        return array_values(array_unique(array_filter(array_map(
            static fn ($value) => is_numeric($value) ? (int) $value : null,
            $decoded,
        ))));
    }
}
