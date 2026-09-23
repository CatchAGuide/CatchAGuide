<?php

namespace App\Services\Sitemap\Contributors;

use App\Contracts\Sitemap\SitemapContributorInterface;
use App\Domain\Vacation\CountrySlug;
use App\Models\CategoryEntity;
use App\Repositories\Guiding\GuidingCategoryAvailabilityRepository;
use App\Services\Sitemap\SitemapContext;
use App\Services\Sitemap\SitemapEntry;
use App\Services\Sitemap\SitemapPathEncoder;
use Illuminate\Support\Collection;

/**
 * Tours-side destination country pages (guidings/{country}) — the sibling of
 * DestinationSitemapContributor's /destination/{country} entries, using the same
 * hasGuidingsForCountry() inventory gate GuidingDestinationController::index() already applies
 * so a country with zero tours never gets a thin page submitted (see CLAUDE.md's "SEO / catalog
 * page conventions"). These were confirmed missing from every sitemap during the Sept 2026 audit.
 */
final class GuidingDestinationSitemapContributor implements SitemapContributorInterface
{
    public function __construct(
        private readonly SitemapPathEncoder $encoder,
        private readonly GuidingCategoryAvailabilityRepository $guidingAvailability,
    ) {}

    public function key(): string
    {
        return 'guiding-destinations';
    }

    public function fileName(string $lang): string
    {
        return '/sitemap_guiding_destinations_' . $lang . '.xml';
    }

    public function entries(SitemapContext $context): Collection
    {
        $entries = collect([
            SitemapEntry::make(
                $this->encoder->join($context->baseUrl, ['guidings', 'countries']),
                'weekly',
                0.75,
            ),
        ]);

        $seenSlugs = [];
        $countries = CategoryEntity::countries()
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->get(['slug', 'countrycode']);

        foreach ($countries as $country) {
            if (! $this->guidingAvailability->hasGuidingsForCountry($country->slug, $country->countrycode)) {
                continue;
            }

            $canonicalSlug = CountrySlug::canonicalize($country->slug) ?? $country->slug;
            if (isset($seenSlugs[$canonicalSlug])) {
                continue;
            }
            $seenSlugs[$canonicalSlug] = true;

            $entries->push(SitemapEntry::make(
                $this->encoder->join($context->baseUrl, ['guidings', $canonicalSlug]),
                'monthly',
                0.7,
            ));
        }

        return $entries;
    }
}
