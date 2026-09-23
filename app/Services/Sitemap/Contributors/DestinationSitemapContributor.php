<?php

namespace App\Services\Sitemap\Contributors;

use App\Contracts\Sitemap\SitemapContributorInterface;
use App\Domain\Vacation\CountrySlug;
use App\Models\CategoryEntity;
use App\Services\Sitemap\SitemapContext;
use App\Services\Sitemap\SitemapEntry;
use App\Services\Sitemap\SitemapPathEncoder;
use Illuminate\Support\Collection;

final class DestinationSitemapContributor implements SitemapContributorInterface
{
    public function __construct(
        private readonly SitemapPathEncoder $encoder,
    ) {}

    public function key(): string
    {
        return 'destinations';
    }

    public function fileName(string $lang): string
    {
        return '/sitemap_destinations_' . $lang . '.xml';
    }

    public function entries(SitemapContext $context): Collection
    {
        $entries = collect([
            SitemapEntry::make(
                $this->encoder->join($context->baseUrl, ['destination']),
                'weekly',
                0.8,
            ),
        ]);

        // Sitemap entries must list the canonical (lowercase) slug directly — otherwise Google
        // discovers a URL whose only job is to 301 elsewhere, which is exactly the "Discovered/
        // Page with redirect" pattern DestinationCountryController::show() now avoids for direct
        // requests (see CLAUDE.md's "SEO / catalog page conventions").
        $seenSlugs = [];
        foreach (CategoryEntity::countries()->whereNotNull('slug')->where('slug', '!=', '')->get(['slug']) as $country) {
            $canonicalSlug = CountrySlug::canonicalize($country->slug) ?? $country->slug;
            if (isset($seenSlugs[$canonicalSlug])) {
                continue;
            }
            $seenSlugs[$canonicalSlug] = true;

            $entries->push(SitemapEntry::make(
                $this->encoder->join($context->baseUrl, ['destination', $canonicalSlug]),
                'monthly',
                0.7,
            ));
        }

        return $entries;
    }
}
