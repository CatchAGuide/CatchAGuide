<?php

namespace App\Providers;

use App\Services\Seo\LocalePathMapper;
use App\Services\Seo\SeoRobotsPolicy;
use App\Services\Sitemap\CategoryPageSitemapSource;
use App\Services\Sitemap\Contributors\GlobalFacetSitemapContributor;
use App\Services\Sitemap\Contributors\HolidayFacetSitemapContributor;
use App\Services\Sitemap\Contributors\HolidayListingSitemapContributor;
use App\Services\Sitemap\Contributors\MagazineSitemapContributor;
use App\Services\Sitemap\Contributors\PagesSitemapContributor;
use App\Services\Sitemap\Contributors\TourFacetSitemapContributor;
use App\Services\Sitemap\Contributors\TourGeoSitemapContributor;
use App\Services\Sitemap\Contributors\TourListingSitemapContributor;
use App\Services\Sitemap\SitemapGenerator;
use App\Services\Sitemap\SitemapLastmod;
use App\Services\Sitemap\SitemapListingFreshness;
use App\Services\Sitemap\SitemapPathEncoder;
use App\Services\Sitemap\SitemapXmlWriter;
use Illuminate\Support\ServiceProvider;

class SitemapServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(LocalePathMapper::class);
        $this->app->singleton(SeoRobotsPolicy::class);
        $this->app->singleton(SitemapPathEncoder::class);
        $this->app->singleton(SitemapXmlWriter::class);
        $this->app->singleton(SitemapLastmod::class);
        $this->app->singleton(SitemapListingFreshness::class);
        $this->app->singleton(CategoryPageSitemapSource::class);

        // The eight child files of /sitemap.xml, one per page type. Each URL belongs to exactly
        // one of them (enforced by SitemapNoDuplicateUrlsTest) so Search Console's per-sitemap
        // indexing report is unambiguous. File names are part of that report's history — don't
        // rename them.
        $this->app->tag([
            PagesSitemapContributor::class,
            TourListingSitemapContributor::class,
            HolidayListingSitemapContributor::class,
            GlobalFacetSitemapContributor::class,
            TourFacetSitemapContributor::class,
            TourGeoSitemapContributor::class,
            HolidayFacetSitemapContributor::class,
            MagazineSitemapContributor::class,
        ], 'sitemap.contributors');

        $this->app->singleton(SitemapGenerator::class, function ($app) {
            return new SitemapGenerator(
                $app->tagged('sitemap.contributors'),
                $app->make(SitemapXmlWriter::class),
                $app->make(LocalePathMapper::class),
                [
                    'en' => rtrim((string) config('cag.en_app_url'), '/'),
                    'de' => rtrim((string) config('cag.de_app_url'), '/'),
                ],
            );
        });
    }
}
