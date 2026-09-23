<?php

namespace App\Services\Sitemap\Contributors;

use App\Contracts\Sitemap\SitemapContributorInterface;
use App\Domain\CategoryPage\CategoryPageEntityType;
use App\Domain\CategoryPage\CategoryPageScope;
use App\Domain\Vacation\CountrySlug;
use App\Models\CategoryEntity;
use App\Services\Seo\CatalogInventoryGate;
use App\Services\Sitemap\CategoryPageSitemapSource;
use App\Services\Sitemap\SitemapContext;
use App\Services\Sitemap\SitemapEntry;
use App\Services\Sitemap\SitemapLastmod;
use App\Services\Sitemap\SitemapPathEncoder;
use Illuminate\Support\Collection;

/**
 * sitemap-facets-global-{lang}.xml — the cross-type hubs: /destination with its gated
 * country/region/city pages, and /targets with its species pages.
 */
final class GlobalFacetSitemapContributor implements SitemapContributorInterface
{
    public function __construct(
        private readonly SitemapPathEncoder $encoder,
        private readonly CatalogInventoryGate $gate,
        private readonly CategoryPageSitemapSource $categoryPages,
        private readonly SitemapLastmod $lastmod,
    ) {}

    public function key(): string
    {
        return 'facets-global';
    }

    public function fileName(string $lang): string
    {
        return '/sitemap-facets-global-' . $lang . '.xml';
    }

    public function entries(SitemapContext $context): Collection
    {
        $entries = collect([SitemapEntry::make($this->encoder->join($context->baseUrl, ['destination']))]);

        $countries = CategoryEntity::countries()
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->orderBy('id')
            ->get()
            ->unique(fn (CategoryEntity $country) => CountrySlug::canonicalize($country->slug));

        foreach ($countries as $country) {
            if (! $this->gate->destinationIndexable($country)) {
                continue;
            }
            $countrySlug = CountrySlug::canonicalize($country->slug);
            $entries->push($this->geoEntry($context, ['destination', $countrySlug], CategoryPageEntityType::GEO_COUNTRY, $country));

            $regions = CategoryEntity::regions()->where('country_id', $country->id)->whereNotNull('slug')->orderBy('id')->get();
            foreach ($regions as $region) {
                if ($this->gate->destinationIndexable($country, $region)) {
                    $entries->push($this->geoEntry($context, ['destination', $countrySlug, $region->slug], CategoryPageEntityType::GEO_REGION, $region));
                }

                $cities = CategoryEntity::cities()->where('region_id', $region->id)->whereNotNull('slug')->orderBy('id')->get();
                foreach ($cities as $city) {
                    if ($this->gate->destinationIndexable($country, $region, $city)) {
                        $entries->push($this->geoEntry($context, ['destination', $countrySlug, $region->slug, $city->slug], CategoryPageEntityType::GEO_CITY, $city));
                    }
                }
            }
        }

        $entries->push(SitemapEntry::make($this->encoder->join($context->baseUrl, ['targets'])));

        foreach ($this->categoryPages->targetPages() as $page) {
            if ($this->categoryPages->globalTargetIsLive($page, $context->lang)) {
                $entries->push(SitemapEntry::make(
                    $this->encoder->join($context->baseUrl, ['targets', $page->slug]),
                    $this->categoryPages->lastmod($page, CategoryPageScope::GLOBAL, $context->lang),
                ));
            }
        }

        return $entries;
    }

    /**
     * @param  list<string>  $segments
     */
    private function geoEntry(SitemapContext $context, array $segments, string $entityType, CategoryEntity $entity): SitemapEntry
    {
        return SitemapEntry::make(
            $this->encoder->join($context->baseUrl, $segments),
            $this->lastmod->forContent($entityType, CategoryPageScope::GLOBAL, $entity->id, $context->lang, $entity->updated_at),
        );
    }
}
