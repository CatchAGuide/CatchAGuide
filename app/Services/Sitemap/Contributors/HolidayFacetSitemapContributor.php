<?php

namespace App\Services\Sitemap\Contributors;

use App\Contracts\Sitemap\SitemapContributorInterface;
use App\Domain\CategoryPage\CategoryPageEntityType;
use App\Domain\CategoryPage\CategoryPageScope;
use App\Domain\Vacation\CountrySlug;
use App\Models\CategoryEntity;
use App\Repositories\Vacation\VacationDestinationRepository;
use App\Services\Seo\CatalogInventoryGate;
use App\Services\Sitemap\CategoryPageSitemapSource;
use App\Services\Sitemap\SitemapContext;
use App\Services\Sitemap\SitemapEntry;
use App\Services\Sitemap\SitemapLastmod;
use App\Services\Sitemap\SitemapPathEncoder;
use Illuminate\Support\Collection;

/**
 * sitemap-facets-holidays-{lang}.xml — vacation hubs and facets: /vacations/countries +
 * countries, /vacations/targets + species, and per pillar /vacations/{camps|trips}/countries +
 * countries and /vacations/{camps|trips}/targets/{species}.
 */
final class HolidayFacetSitemapContributor implements SitemapContributorInterface
{
    /** Route pillar => [scope for country copy, species-page pillar] */
    private const PILLARS = [
        'camps' => [CategoryPageScope::CAMPS, 'camp'],
        'trips' => [CategoryPageScope::TRIPS, 'trip'],
    ];

    public function __construct(
        private readonly SitemapPathEncoder $encoder,
        private readonly CatalogInventoryGate $gate,
        private readonly VacationDestinationRepository $destinations,
        private readonly CategoryPageSitemapSource $categoryPages,
        private readonly SitemapLastmod $lastmod,
    ) {}

    public function key(): string
    {
        return 'facets-holidays';
    }

    public function fileName(string $lang): string
    {
        return '/sitemap-facets-holidays-' . $lang . '.xml';
    }

    public function entries(SitemapContext $context): Collection
    {
        $countries = $this->countries();

        $entries = collect([SitemapEntry::make($this->encoder->join($context->baseUrl, ['vacations', 'countries']))]);
        foreach ($countries as $slug => $country) {
            if ($this->gate->vacationCountryIndexable($slug)) {
                $entries->push($this->countryEntry($context, ['vacations', $slug], CategoryPageScope::VACATIONS, $country));
            }
        }

        $entries->push(SitemapEntry::make($this->encoder->join($context->baseUrl, ['vacations', 'targets'])));
        foreach ($this->categoryPages->targetPages() as $page) {
            if ($this->categoryPages->vacationTargetIsLive($page, $context->lang)) {
                $entries->push(SitemapEntry::make(
                    $this->encoder->join($context->baseUrl, ['vacations', 'targets', $page->slug]),
                    $this->categoryPages->lastmod($page, CategoryPageScope::VACATIONS, $context->lang),
                ));
            }
        }

        foreach (self::PILLARS as $pillar => [$scope, $speciesPillar]) {
            $entries->push(SitemapEntry::make($this->encoder->join($context->baseUrl, ['vacations', $pillar, 'countries'])));
            foreach ($countries as $slug => $country) {
                if ($this->gate->vacationCountryIndexable($slug, $pillar)) {
                    $entries->push($this->countryEntry($context, ['vacations', $pillar, $slug], $scope, $country));
                }
            }

            foreach ($this->categoryPages->targetPages() as $page) {
                if ($this->categoryPages->vacationTargetIsLive($page, $context->lang, $speciesPillar)) {
                    $entries->push(SitemapEntry::make(
                        $this->encoder->join($context->baseUrl, ['vacations', $pillar, 'targets', $page->slug]),
                        $this->categoryPages->lastmod($page, CategoryPageScope::VACATIONS, $context->lang),
                    ));
                }
            }
        }

        return $entries;
    }

    /**
     * Canonical country slug => its CategoryEntity row (null when the country is known only
     * through listings), for every country on the vacations hub grid.
     *
     * @return array<string, ?CategoryEntity>
     */
    private function countries(): array
    {
        $countries = [];
        foreach ($this->destinations->countriesForHubGrid() as $row) {
            $slug = CountrySlug::canonicalize($row['slug']) ?? $row['slug'];
            $countries[$slug] = $row['destination'] ?? null;
        }
        ksort($countries);

        return $countries;
    }

    /**
     * @param  list<string>  $segments
     */
    private function countryEntry(SitemapContext $context, array $segments, string $scope, ?CategoryEntity $country): SitemapEntry
    {
        $lastmod = $country === null
            ? null
            : $this->lastmod->forContent(CategoryPageEntityType::GEO_COUNTRY, $scope, $country->id, $context->lang, $country->updated_at);

        return SitemapEntry::make($this->encoder->join($context->baseUrl, $segments), $lastmod);
    }
}
