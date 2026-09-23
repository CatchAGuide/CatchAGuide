<?php

namespace App\Services\Sitemap\Contributors;

use App\Contracts\Sitemap\SitemapContributorInterface;
use App\Domain\CategoryPage\CategoryPageEntityType;
use App\Domain\CategoryPage\CategoryPageScope;
use App\Domain\Vacation\CountrySlug;
use App\Models\CategoryEntity;
use App\Services\Seo\CatalogInventoryGate;
use App\Services\Sitemap\SitemapContext;
use App\Services\Sitemap\SitemapEntry;
use App\Services\Sitemap\SitemapLastmod;
use App\Services\Sitemap\SitemapPathEncoder;
use Illuminate\Support\Collection;

/**
 * sitemap-geo-tours-{lang}.xml — /guidings/{country}/{region} and /guidings/{country}/{region}/{city}
 * pages that clear the region/city inventory gate (at least three tours). Their own file because
 * they share no path prefix a Search Console filter could isolate.
 */
final class TourGeoSitemapContributor implements SitemapContributorInterface
{
    public function __construct(
        private readonly SitemapPathEncoder $encoder,
        private readonly CatalogInventoryGate $gate,
        private readonly SitemapLastmod $lastmod,
    ) {}

    public function key(): string
    {
        return 'geo-tours';
    }

    public function fileName(string $lang): string
    {
        return '/sitemap-geo-tours-' . $lang . '.xml';
    }

    public function entries(SitemapContext $context): Collection
    {
        $entries = collect();

        $countries = CategoryEntity::countries()
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->orderBy('id')
            ->get()
            ->unique(fn (CategoryEntity $country) => CountrySlug::canonicalize($country->slug))
            ->filter(fn (CategoryEntity $country) => $this->gate->guidingDestinationIndexable($country));

        foreach ($countries as $country) {
            $countrySlug = CountrySlug::canonicalize($country->slug);
            $regions = CategoryEntity::regions()->where('country_id', $country->id)->whereNotNull('slug')->orderBy('id')->get();

            foreach ($regions as $region) {
                if ($this->gate->guidingDestinationIndexable($country, $region)) {
                    $entries->push(SitemapEntry::make(
                        $this->encoder->join($context->baseUrl, ['guidings', $countrySlug, $region->slug]),
                        $this->lastmod->forContent(CategoryPageEntityType::GEO_REGION, CategoryPageScope::TOURS, $region->id, $context->lang, $region->updated_at),
                    ));
                }

                $cities = CategoryEntity::cities()->where('region_id', $region->id)->whereNotNull('slug')->orderBy('id')->get();
                foreach ($cities as $city) {
                    if ($this->gate->guidingDestinationIndexable($country, $region, $city)) {
                        $entries->push(SitemapEntry::make(
                            $this->encoder->join($context->baseUrl, ['guidings', $countrySlug, $region->slug, $city->slug]),
                            $this->lastmod->forContent(CategoryPageEntityType::GEO_CITY, CategoryPageScope::TOURS, $city->id, $context->lang, $city->updated_at),
                        ));
                    }
                }
            }
        }

        return $entries;
    }
}
