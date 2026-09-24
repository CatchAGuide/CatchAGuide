<?php

namespace App\Services\Sitemap\Contributors;

use App\Contracts\Sitemap\SitemapContributorInterface;
use App\Domain\Vacation\BookableListingPolicy;
use App\Models\Camp;
use App\Models\Trip;
use App\Repositories\Vacation\VacationDestinationRepository;
use App\Services\Sitemap\SitemapContext;
use App\Services\Sitemap\SitemapEntry;
use App\Services\Sitemap\SitemapPathEncoder;
use Illuminate\Support\Collection;

/**
 * sitemap-holidays-{lang}.xml — active trip and camp product pages
 * (/vacations/trips/{slug}, /vacations/camps/{slug}).
 */
final class HolidayListingSitemapContributor implements SitemapContributorInterface
{
    /** Path segments owned by pillar facet routes, never a product slug. */
    private const RESERVED_SLUGS = ['countries', 'targets'];

    public function __construct(
        private readonly SitemapPathEncoder $encoder,
        private readonly BookableListingPolicy $policy,
        private readonly VacationDestinationRepository $destinations,
    ) {}

    public function key(): string
    {
        return 'holidays';
    }

    public function fileName(string $lang): string
    {
        return '/sitemap-holidays-' . $lang . '.xml';
    }

    public function entries(SitemapContext $context): Collection
    {
        return $this->products(Trip::class, 'trips', $context)
            ->merge($this->products(Camp::class, 'camps', $context))
            ->values();
    }

    /**
     * @param  class-string<Trip|Camp>  $model
     * @return Collection<int, SitemapEntry>
     */
    private function products(string $model, string $pillar, SitemapContext $context): Collection
    {
        return $model::query()
            ->where('status', $this->policy->activeStatus())
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->orderBy('id')
            ->get(['id', 'slug', 'updated_at'])
            ->unique('slug')
            // vacations/{pillar}/{slug} serves the country page when the slug is also a country,
            // so a product with such a slug never renders at that URL.
            ->reject(fn ($listing) => in_array($listing->slug, self::RESERVED_SLUGS, true)
                || $this->destinations->isKnownCountrySlug($listing->slug, $pillar))
            ->map(fn ($listing) => SitemapEntry::make(
                $this->encoder->join($context->baseUrl, ['vacations', $pillar, $listing->slug]),
                $listing->updated_at?->toAtomString(),
            ))
            ->values();
    }
}
