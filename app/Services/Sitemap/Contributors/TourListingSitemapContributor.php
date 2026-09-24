<?php

namespace App\Services\Sitemap\Contributors;

use App\Contracts\Sitemap\SitemapContributorInterface;
use App\Models\Guiding;
use App\Services\Sitemap\SitemapContext;
use App\Services\Sitemap\SitemapEntry;
use App\Services\Sitemap\SitemapPathEncoder;
use Illuminate\Support\Collection;

/**
 * sitemap-tours-{lang}.xml — every publicly visible /guidings/offer/{slug}. Uses the same
 * publiclyVisible() scope the offer page serves from, so expired/hidden tours drop out instead
 * of being submitted as dead URLs.
 */
final class TourListingSitemapContributor implements SitemapContributorInterface
{
    public function __construct(
        private readonly SitemapPathEncoder $encoder,
    ) {}

    public function key(): string
    {
        return 'tours';
    }

    public function fileName(string $lang): string
    {
        return '/sitemap-tours-' . $lang . '.xml';
    }

    public function entries(SitemapContext $context): Collection
    {
        return Guiding::query()
            ->publiclyVisible()
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->orderBy('id')
            ->get(['id', 'slug', 'updated_at'])
            ->unique('slug')
            ->map(fn (Guiding $guiding) => SitemapEntry::make(
                $this->encoder->join($context->baseUrl, ['guidings', 'offer', $guiding->slug]),
                $guiding->updated_at?->toAtomString(),
            ))
            ->values();
    }
}
