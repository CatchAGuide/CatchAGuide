<?php

namespace App\Services\Sitemap\Contributors;

use App\Contracts\Sitemap\SitemapContributorInterface;
use App\Models\Guiding;
use App\Services\Sitemap\SitemapContext;
use App\Services\Sitemap\SitemapEntry;
use App\Services\Sitemap\SitemapPathEncoder;
use Illuminate\Support\Collection;

final class ListingSitemapContributor implements SitemapContributorInterface
{
    public function __construct(
        private readonly SitemapPathEncoder $encoder,
    ) {}

    public function key(): string
    {
        return 'listing';
    }

    public function fileName(string $lang): string
    {
        return '/sitemap_listing_' . $lang . '.xml';
    }

    public function entries(SitemapContext $context): Collection
    {
        $guidings = Guiding::query()
            ->where('status', 1)
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->get(['id', 'slug']);

        return $guidings->map(function (Guiding $guiding) use ($context) {
            return SitemapEntry::make(
                $this->encoder->join($context->baseUrl, ['guidings', (string) $guiding->id, $guiding->slug]),
                'monthly',
                0.7,
            );
        });
    }
}
