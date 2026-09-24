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
use App\Services\Sitemap\SitemapListingFreshness;
use App\Services\Sitemap\SitemapPathEncoder;
use Illuminate\Support\Collection;

/**
 * sitemap-facets-tours-{lang}.xml — tour hubs and their facets: /guidings/countries + countries,
 * /guidings/targets + species, /guidings/methods + methods. Regions and cities are in
 * sitemap-geo-tours (TourGeoSitemapContributor) so the geo layer is measured on its own.
 */
final class TourFacetSitemapContributor implements SitemapContributorInterface
{
    public function __construct(
        private readonly SitemapPathEncoder $encoder,
        private readonly CatalogInventoryGate $gate,
        private readonly CategoryPageSitemapSource $categoryPages,
        private readonly SitemapLastmod $lastmod,
        private readonly SitemapListingFreshness $freshness,
    ) {}

    public function key(): string
    {
        return 'facets-tours';
    }

    public function fileName(string $lang): string
    {
        return '/sitemap-facets-tours-' . $lang . '.xml';
    }

    public function entries(SitemapContext $context): Collection
    {
        $entries = collect([SitemapEntry::make($this->encoder->join($context->baseUrl, ['guidings', 'countries']))]);

        $countries = CategoryEntity::countries()
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->orderBy('id')
            ->get()
            ->unique(fn (CategoryEntity $country) => CountrySlug::canonicalize($country->slug));

        foreach ($countries as $country) {
            if ($this->gate->guidingDestinationIndexable($country)) {
                $entries->push(SitemapEntry::make(
                    $this->encoder->join($context->baseUrl, ['guidings', CountrySlug::canonicalize($country->slug)]),
                    $this->lastmod->forContent(CategoryPageEntityType::GEO_COUNTRY, CategoryPageScope::TOURS, $country->id, $context->lang, $country->updated_at, $this->freshness->tourCountry($country->slug, $country->countrycode)),
                ));
            }
        }

        $entries->push(SitemapEntry::make($this->encoder->join($context->baseUrl, ['guidings', 'targets'])));
        foreach ($this->categoryPages->targetPages() as $page) {
            if ($this->categoryPages->tourTargetIsLive($page, $context->lang)) {
                $entries->push(SitemapEntry::make(
                    $this->encoder->join($context->baseUrl, ['guidings', 'targets', $page->slug]),
                    $this->categoryPages->lastmod($page, CategoryPageScope::TOURS, $context->lang, $this->freshness->tourTarget((int) $page->source_id)),
                ));
            }
        }

        $entries->push(SitemapEntry::make($this->encoder->join($context->baseUrl, ['guidings', 'methods'])));
        foreach ($this->categoryPages->methodPages() as $page) {
            if ($this->categoryPages->methodIsLive($page, $context->lang)) {
                $entries->push(SitemapEntry::make(
                    $this->encoder->join($context->baseUrl, ['guidings', 'methods', $page->slug]),
                    $this->categoryPages->lastmod($page, CategoryPageScope::TOURS, $context->lang, $this->freshness->tourMethod((int) $page->source_id)),
                ));
            }
        }

        return $entries;
    }
}
