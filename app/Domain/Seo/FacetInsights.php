<?php

namespace App\Domain\Seo;

/**
 * Facts aggregated from the listings a facet page (country, region, city, species, method)
 * shows — the raw material for its generated summary, facts box, FAQ and meta description.
 * Plain arrays throughout so it caches cheaply; names are already localized.
 */
final class FacetInsights
{
    public const KIND_PLACE = 'place';

    public const KIND_SPECIES = 'species';

    public const KIND_METHOD = 'method';

    /**
     * @param  string  $kind  place|species|method
     * @param  string  $subject  localized name of the place/species/method
     * @param  ?string  $placeType  country|region|city (places only)
     * @param  ?string  $countryIso  ISO 3166-1 alpha-2 (country places only, for "in den Niederlanden")
     * @param  array{tour: int, trip: int, camp: int}  $counts
     * @param  ?array{low: int, median: int, high: int}  $tourPrices  per person
     * @param  ?array{low: int, median: int, high: int}  $tripPrices  per person
     * @param  list<array{name: string, count: int, url: ?string}>  $species
     * @param  list<array{name: string, count: int, url: ?string}>  $methods
     * @param  list<array{name: string, count: int}>  $waters
     * @param  list<int>  $seasonMonths  months (1-12) with the widest choice, ascending
     * @param  list<array{name: string, count: int, url: ?string}>  $countries  (species/method facets)
     */
    public function __construct(
        public readonly string $kind,
        public readonly string $subject,
        public readonly ?string $placeType,
        public readonly ?string $countryIso,
        public readonly array $counts,
        public readonly int $providers,
        public readonly ?array $tourPrices,
        public readonly ?array $tripPrices,
        public readonly array $species,
        public readonly array $methods,
        public readonly array $waters,
        public readonly array $seasonMonths,
        public readonly array $countries,
    ) {}

    public function total(): int
    {
        return array_sum($this->counts);
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }

    public static function fromArray(array $data): self
    {
        return new self(...$data);
    }
}
