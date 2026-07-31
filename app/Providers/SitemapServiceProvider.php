<?php

namespace App\Providers;

use App\Services\Seo\LocalePathMapper;
use App\Services\Seo\SeoRobotsPolicy;
use App\Services\Sitemap\Contributors\CategorySitemapContributor;
use App\Services\Sitemap\Contributors\DestinationSitemapContributor;
use App\Services\Sitemap\Contributors\ListingSitemapContributor;
use App\Services\Sitemap\Contributors\MagazineSitemapContributor;
use App\Services\Sitemap\Contributors\MainSitemapContributor;
use App\Services\Sitemap\Contributors\VacationSitemapContributor;
use App\Services\Sitemap\SitemapGenerator;
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

        $this->app->tag([
            ListingSitemapContributor::class,
            MagazineSitemapContributor::class,
            VacationSitemapContributor::class,
            CategorySitemapContributor::class,
            DestinationSitemapContributor::class,
            MainSitemapContributor::class,
        ], 'sitemap.contributors');

        $this->app->singleton(SitemapGenerator::class, function ($app) {
            return new SitemapGenerator(
                $app->tagged('sitemap.contributors'),
                $app->make(SitemapXmlWriter::class),
            );
        });
    }
}
