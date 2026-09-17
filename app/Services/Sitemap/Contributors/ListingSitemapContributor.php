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
            ->get(['id', 'slug', 'updated_at']);

        // Both locale domains publish the same guiding under the same slug (see the
        // hreflang tags rendered on the offer page itself), so the alternate-language
        // URL only needs the other domain's base swapped in.
        $localeBaseUrls = [
            'en' => rtrim((string) config('cag.en_app_url'), '/'),
            'de' => rtrim((string) config('cag.de_app_url'), '/'),
        ];

        return $guidings->map(function (Guiding $guiding) use ($context, $localeBaseUrls) {
            $alternates = [];
            foreach ($localeBaseUrls as $hreflang => $baseUrl) {
                if ($baseUrl === '') {
                    continue;
                }
                $alternates[$hreflang] = $this->encoder->join($baseUrl, ['guidings', 'offer', $guiding->slug]);
            }
            if (isset($alternates['en'])) {
                $alternates['x-default'] = $alternates['en'];
            }

            return SitemapEntry::make(
                $this->encoder->join($context->baseUrl, ['guidings', 'offer', $guiding->slug]),
                'monthly',
                0.7,
                $guiding->updated_at?->toAtomString(),
                $alternates,
            );
        });
    }
}
