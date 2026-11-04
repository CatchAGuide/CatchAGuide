<?php

namespace App\Services\Guiding;

use App\Domain\Vacation\CountrySlug;
use App\Models\CategoryEntity;
use App\Models\Guiding;
use App\Services\Homepage\HomepageCountrySelector;
use App\Services\Seo\CatalogInventoryGate;
use Illuminate\Support\Collection;

/**
 * The /guidings/{country}/{region}/{city} pages a tour belongs to, for the tour page's breadcrumb
 * and "view all" link. Only pages that pass the inventory gate are returned, so a tour page never
 * links a noindexed geo page. Replaces the old "view all" link to
 * /guidings/alloffers?place=…&placeLat=…, which gave every tour its own noindexed URL for Google
 * to crawl.
 */
class GuidingDestinationTrail
{
    /** @var Collection<int, CategoryEntity>|null */
    private ?Collection $countries = null;

    public function __construct(
        private readonly CatalogInventoryGate $gate,
        private readonly HomepageCountrySelector $countryLabels,
    ) {}

    /**
     * Country → region → city, stopping at the first level that is missing or not indexable.
     *
     * @return list<array{label: string, url: string}>
     */
    public function for(Guiding $guiding): array
    {
        $country = $this->country($guiding);
        if ($country === null || ! $this->gate->guidingDestinationIndexable($country)) {
            return [];
        }

        $countrySlug = CountrySlug::canonicalize($country->slug);
        $trail = [[
            'label' => $this->countryLabels->labelFor($country),
            'url' => route('guidings.destination', ['country' => $countrySlug]),
        ]];

        $region = $this->match(
            CategoryEntity::regions()->where('country_id', $country->id)->whereNotNull('slug')->orderBy('id')->get(),
            $guiding->region,
            'region',
        );
        if ($region === null || ! $this->gate->guidingDestinationIndexable($country, $region)) {
            return $trail;
        }

        $trail[] = [
            'label' => $region->name,
            'url' => route('guidings.destination', ['country' => $countrySlug, 'region' => $region->slug]),
        ];

        $city = $this->match(
            CategoryEntity::cities()->where('region_id', $region->id)->whereNotNull('slug')->orderBy('id')->get(),
            $guiding->city,
            'city',
        );
        if ($city === null || ! $this->gate->guidingDestinationIndexable($country, $region, $city)) {
            return $trail;
        }

        $trail[] = [
            'label' => $city->name,
            'url' => route('guidings.destination', ['country' => $countrySlug, 'region' => $region->slug, 'city' => $city->slug]),
        ];

        return $trail;
    }

    private function country(Guiding $guiding): ?CategoryEntity
    {
        $this->countries ??= CategoryEntity::countries()
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->orderBy('id')
            ->get();

        $iso = mb_strtolower(trim((string) $guiding->country_iso), 'UTF-8');
        if ($iso !== '') {
            $byIso = $this->countries->first(
                fn (CategoryEntity $country) => mb_strtolower(trim((string) $country->countrycode), 'UTF-8') === $iso
            );
            if ($byIso !== null) {
                return $byIso;
            }
        }

        $name = $this->normalized($guiding->country);
        if ($name === null) {
            return null;
        }

        return $this->countries->first(
            fn (CategoryEntity $country) => in_array($name, CountrySlug::storageVariants($country->slug, $country->countrycode), true)
        );
    }

    /**
     * Guidings store Google's place names ("Bavaria"); geo entities store a local name ("Bayern")
     * plus the Google name in their filters, so match either.
     *
     * @param  Collection<int, CategoryEntity>  $candidates
     */
    private function match(Collection $candidates, ?string $value, string $filterKey): ?CategoryEntity
    {
        $needle = $this->normalized($value);
        if ($needle === null) {
            return null;
        }

        return $candidates->first(function (CategoryEntity $entity) use ($needle, $filterKey) {
            $filters = is_array($entity->filters) ? $entity->filters : (json_decode((string) $entity->filters, true) ?: []);

            return in_array($needle, array_filter([
                $this->normalized($entity->name),
                $this->normalized($entity->slug),
                $this->normalized(is_string($filters[$filterKey] ?? null) ? $filters[$filterKey] : null),
            ]), true);
        });
    }

    private function normalized(?string $value): ?string
    {
        $value = mb_strtolower(trim((string) $value), 'UTF-8');

        return $value !== '' ? $value : null;
    }
}
