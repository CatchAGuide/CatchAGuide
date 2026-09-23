<?php

namespace App\Services\Sitemap\Contributors;

use App\Contracts\Sitemap\SitemapContributorInterface;
use App\Models\Thread;
use App\Services\Seo\LocalePathMapper;
use App\Services\Sitemap\SitemapContext;
use App\Services\Sitemap\SitemapEntry;
use App\Services\Sitemap\SitemapPathEncoder;
use Illuminate\Support\Collection;

final class MagazineSitemapContributor implements SitemapContributorInterface
{
    public function __construct(
        private readonly LocalePathMapper $localePathMapper,
        private readonly SitemapPathEncoder $encoder,
    ) {}

    public function key(): string
    {
        return 'magazine';
    }

    public function fileName(string $lang): string
    {
        return '/sitemap-magazine-' . $lang . '.xml';
    }

    public function entries(SitemapContext $context): Collection
    {
        $prefix = $this->localePathMapper->magazinePrefix($context->lang);
        $threads = Thread::query()
            ->where('language', $context->lang)
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->orderBy('id')
            ->get(['slug', 'updated_at'])
            ->unique('slug');

        $entries = collect([SitemapEntry::make(
            $this->encoder->join($context->baseUrl, [$prefix]),
            $threads->max('updated_at')?->toAtomString(),
        )]);

        // Articles are written per language with unrelated slugs — the same path on the other
        // domain redirects to its magazine index — so they carry no hreflang alternates.
        foreach ($threads as $thread) {
            $entries->push(SitemapEntry::make(
                $this->encoder->join($context->baseUrl, [$prefix, $thread->slug]),
                $thread->updated_at?->toAtomString(),
                localized: false,
            ));
        }

        return $entries;
    }
}
