<?php

namespace App\Services\CategoryPage;

use App\Domain\Vacation\CountrySlug;
use App\Models\Camp;
use App\Models\CategoryEntity;
use App\Models\CategoryPage;
use App\Models\Guiding;
use App\Models\Trip;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Temporary category-tile image when the CMS thumbnail is empty: one listing
 * photo from matching tours first, then camps/trips. The map is cached so
 * rails and indexes do not query listings per tile.
 */
class CategoryListingThumbnailFallback
{
    public const KIND_COUNTRY = 'country';

    public const KIND_REGION = 'region';

    public const KIND_CITY = 'city';

    public const KIND_TARGET = 'target';

    public const KIND_METHOD = 'method';

    public const CACHE_KEY = 'category_listing_thumbnails_v1';

    private const CACHE_MINUTES = 60;

    private const PLACEHOLDER = 'assets/images/300x300.png';

    private ?array $index = null;

    public function url(
        ?string $ownPath,
        string $kind,
        string|int $identifier,
        ?string $countryIso = null,
    ): string {
        return media_url($this->path($ownPath, $kind, $identifier, $countryIso), self::PLACEHOLDER);
    }

    public function path(
        ?string $ownPath,
        string $kind,
        string|int $identifier,
        ?string $countryIso = null,
    ): ?string {
        if (filled($ownPath)) {
            return $ownPath;
        }

        return $this->listingPath($kind, $identifier, $countryIso);
    }

    public function listingPath(string $kind, string|int $identifier, ?string $countryIso = null): ?string
    {
        $index = $this->index();

        return match ($kind) {
            self::KIND_COUNTRY => $this->countryFromIndex($index, (string) $identifier, $countryIso),
            self::KIND_REGION => $this->geoFromIndex($index['regions'], (string) $identifier),
            self::KIND_CITY => $this->geoFromIndex($index['cities'], (string) $identifier),
            self::KIND_TARGET => $index['targets'][(int) $identifier] ?? null,
            self::KIND_METHOD => $index['methods'][(int) $identifier] ?? null,
            default => null,
        };
    }

    public function forEntity(CategoryEntity $entity): string
    {
        return match ($entity->type) {
            'country' => $this->url(
                $entity->thumbnail_path,
                self::KIND_COUNTRY,
                (string) $entity->slug,
                $entity->countrycode,
            ),
            'region' => $this->urlWithCountryFallback(
                $entity,
                self::KIND_REGION,
                (string) $entity->slug,
            ),
            'city' => $this->urlWithCountryFallback(
                $entity,
                self::KIND_CITY,
                (string) $entity->slug,
            ),
            default => media_url($entity->thumbnail_path, self::PLACEHOLDER),
        };
    }

    public function forPage(CategoryPage $page): string
    {
        $kind = strtolower((string) $page->type) === 'methods'
            ? self::KIND_METHOD
            : self::KIND_TARGET;

        return $this->url($page->thumbnail_path, $kind, (int) $page->source_id);
    }

    /**
     * @return array{
     *     countries: array<string, string>,
     *     regions: array<string, string>,
     *     cities: array<string, string>,
     *     targets: array<int, string>,
     *     methods: array<int, string>
     * }
     */
    private function index(): array
    {
        if ($this->index !== null) {
            return $this->index;
        }

        return $this->index = Cache::remember(
            self::CACHE_KEY,
            now()->addMinutes(self::CACHE_MINUTES),
            fn () => $this->build(),
        );
    }

    /**
     * @return array{
     *     countries: array<string, string>,
     *     regions: array<string, string>,
     *     cities: array<string, string>,
     *     targets: array<int, string>,
     *     methods: array<int, string>
     * }
     */
    private function build(): array
    {
        $index = [
            'countries' => [],
            'regions' => [],
            'cities' => [],
            'targets' => [],
            'methods' => [],
        ];

        Guiding::query()
            ->publiclyVisible()
            ->whereNotNull('thumbnail_path')
            ->where('thumbnail_path', '!=', '')
            ->select(['id', 'country', 'country_iso', 'region', 'city', 'target_fish', 'fishing_methods', 'thumbnail_path'])
            ->orderBy('id')
            ->chunkById(500, function (Collection $guidings) use (&$index) {
                foreach ($guidings as $guiding) {
                    $this->ingestListing($index, $guiding->getAttributes(), overwrite: true);
                }
            });

        Camp::query()
            ->where('status', 'active')
            ->whereNotNull('thumbnail_path')
            ->where('thumbnail_path', '!=', '')
            ->select(['id', 'country', 'region', 'city', 'target_fish', 'thumbnail_path'])
            ->orderBy('id')
            ->chunkById(500, function (Collection $camps) use (&$index) {
                foreach ($camps as $camp) {
                    $this->ingestListing($index, $camp->getAttributes(), overwrite: false);
                }
            });

        Trip::query()
            ->where('status', 'active')
            ->whereNotNull('thumbnail_path')
            ->where('thumbnail_path', '!=', '')
            ->select(['id', 'country', 'region', 'city', 'target_species', 'fishing_methods', 'thumbnail_path'])
            ->orderBy('id')
            ->chunkById(500, function (Collection $trips) use (&$index) {
                foreach ($trips as $trip) {
                    $attributes = $trip->getAttributes();
                    if (! array_key_exists('target_fish', $attributes) && array_key_exists('target_species', $attributes)) {
                        $attributes['target_fish'] = $attributes['target_species'];
                    }
                    $this->ingestListing($index, $attributes, overwrite: false);
                }
            });

        return $index;
    }

    /**
     * @param  array{
     *     countries: array<string, string>,
     *     regions: array<string, string>,
     *     cities: array<string, string>,
     *     targets: array<int, string>,
     *     methods: array<int, string>
     * }  $index
     * @param  array<string, mixed>  $attributes
     */
    private function ingestListing(array &$index, array $attributes, bool $overwrite): void
    {
        $thumb = trim((string) ($attributes['thumbnail_path'] ?? ''));
        if ($thumb === '') {
            return;
        }

        $country = $attributes['country'] ?? null;
        $iso = isset($attributes['country_iso']) ? (string) $attributes['country_iso'] : null;
        if (is_string($country) && trim($country) !== '') {
            foreach (CountrySlug::storageVariants($country, $iso) as $variant) {
                $this->putString($index['countries'], $variant, $thumb, $overwrite);
            }
        }

        $this->putGeo($index['regions'], $attributes['region'] ?? null, $thumb, $overwrite);
        $this->putGeo($index['cities'], $attributes['city'] ?? null, $thumb, $overwrite);

        foreach ($this->decodeIds($attributes['target_fish'] ?? null) as $id) {
            $this->putInt($index['targets'], $id, $thumb, $overwrite);
        }

        foreach ($this->decodeIds($attributes['fishing_methods'] ?? null) as $id) {
            $this->putInt($index['methods'], $id, $thumb, $overwrite);
        }
    }

    /**
     * @param  array<string, string>  $map
     */
    private function putString(array &$map, string $variant, string $thumb, bool $overwrite): void
    {
        $key = mb_strtolower(trim($variant), 'UTF-8');
        if ($key === '') {
            return;
        }

        if ($overwrite || ! isset($map[$key])) {
            $map[$key] = $thumb;
        }
    }

    /**
     * @param  array<string, string>  $map
     */
    private function putGeo(array &$map, mixed $value, string $thumb, bool $overwrite): void
    {
        if (! is_string($value) || trim($value) === '') {
            return;
        }

        $key = CountrySlug::canonicalize($value) ?? mb_strtolower(trim($value), 'UTF-8');
        if ($key === '') {
            return;
        }

        if ($overwrite || ! isset($map[$key])) {
            $map[$key] = $thumb;
        }
    }

    /**
     * @param  array<int, string>  $map
     */
    private function putInt(array &$map, int $id, string $thumb, bool $overwrite): void
    {
        if ($id <= 0) {
            return;
        }

        if ($overwrite || ! isset($map[$id])) {
            $map[$id] = $thumb;
        }
    }

    /**
     * @param  array{countries: array<string, string>}  $index
     */
    private function countryFromIndex(array $index, string $slug, ?string $countryIso): ?string
    {
        foreach (CountrySlug::storageVariants($slug, $countryIso) as $variant) {
            $key = mb_strtolower(trim($variant), 'UTF-8');
            if ($key !== '' && isset($index['countries'][$key])) {
                return $index['countries'][$key];
            }
        }

        return null;
    }

    /**
     * @param  array<string, string>  $map
     */
    private function geoFromIndex(array $map, string $slug): ?string
    {
        $key = CountrySlug::canonicalize($slug) ?? mb_strtolower(trim($slug), 'UTF-8');

        return $key !== '' ? ($map[$key] ?? null) : null;
    }

    private function urlWithCountryFallback(CategoryEntity $entity, string $kind, string $slug): string
    {
        $path = $this->path($entity->thumbnail_path, $kind, $slug);
        if ($path !== null) {
            return media_url($path, self::PLACEHOLDER);
        }

        $country = $entity->country;
        if ($country !== null) {
            $fromCountry = $this->listingPath(
                self::KIND_COUNTRY,
                (string) $country->slug,
                $country->countrycode,
            );
            if ($fromCountry !== null) {
                return media_url($fromCountry, self::PLACEHOLDER);
            }
        }

        return media_url(null, self::PLACEHOLDER);
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
