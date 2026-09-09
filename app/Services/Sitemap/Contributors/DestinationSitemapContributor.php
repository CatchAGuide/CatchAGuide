<?php

namespace App\Services\Sitemap\Contributors;

use App\Contracts\Sitemap\SitemapContributorInterface;
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

        foreach (CategoryEntity::countries()->whereNotNull('slug')->where('slug', '!=', '')->get(['slug']) as $country) {
            $entries->push(SitemapEntry::make(
                $this->encoder->join($context->baseUrl, ['destination', $country->slug]),
                'monthly',
                0.7,
            ));
        }

        return $entries;
    }
}
