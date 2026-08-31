<?php

namespace App\Services\Sitemap\Contributors;

use App\Contracts\Sitemap\SitemapContributorInterface;
use App\Domain\Vacation\CountrySlug;
use App\Models\Camp;
use App\Models\CategoryEntity;
use App\Models\Trip;
use App\Repositories\Vacation\VacationDestinationRepository;
use App\Services\Sitemap\SitemapContext;
use App\Services\Sitemap\SitemapEntry;
use App\Services\Sitemap\SitemapPathEncoder;
use Illuminate\Support\Collection;

class VacationSitemapContributor implements SitemapContributorInterface
{
    public function __construct(
        private readonly VacationDestinationRepository $destinations,
        private readonly SitemapPathEncoder $encoder,
    ) {}

    public function key(): string
    {
        return 'vacations';
    }

    public function fileName(string $lang): string
    {
        return '/sitemap_vacations_' . $lang . '.xml';
    }

    public function entries(SitemapContext $context): Collection
    {
        app()->setLocale($context->lang);
        $entries = collect();

        $static = [
            'vacations' => ['changefreq' => 'weekly', 'priority' => 0.9],
            'vacations/trips' => ['changefreq' => 'weekly', 'priority' => 0.8],
            'vacations/camps' => ['changefreq' => 'weekly', 'priority' => 0.8],
            'vacations/all-offers' => ['changefreq' => 'weekly', 'priority' => 0.75],
        ];

        foreach ($static as $path => $meta) {
            $entries->push(SitemapEntry::make(
                $this->encoder->fromPath($context->baseUrl, $path),
                $meta['changefreq'],
                $meta['priority'],
            ));
        }

        $countrySlugs = $this->collectCountrySlugs($context->lang);

        foreach ($countrySlugs as $slug) {
            $entries->push(SitemapEntry::make(
                $this->encoder->join($context->baseUrl, ['vacations', $slug]),
                'weekly',
                0.75,
            ));
            $entries->push(SitemapEntry::make(
                $this->encoder->join($context->baseUrl, ['vacations', 'trips', $slug]),
                'weekly',
                0.7,
            ));
            $entries->push(SitemapEntry::make(
                $this->encoder->join($context->baseUrl, ['vacations', 'camps', $slug]),
                'weekly',
                0.7,
            ));
        }

        foreach (Trip::query()->where('status', 'active')->whereNotNull('slug')->where('slug', '!=', '')->get(['slug']) as $trip) {
            $entries->push(SitemapEntry::make(
                $this->encoder->join($context->baseUrl, ['vacations', 'trips', $trip->slug]),
                'monthly',
                0.7,
            ));
        }

        foreach (Camp::query()->where('status', 'active')->whereNotNull('slug')->where('slug', '!=', '')->get(['slug']) as $camp) {
            $entries->push(SitemapEntry::make(
                $this->encoder->join($context->baseUrl, ['vacations', 'camps', $camp->slug]),
                'monthly',
                0.7,
            ));
        }

        return $entries->unique(fn (SitemapEntry $entry) => $entry->loc)->values();
    }

    /**
     * @return list<string>
     */
    private function collectCountrySlugs(string $lang): array
    {
        $raw = CategoryEntity::countries()->pluck('slug')
            ->merge(Camp::query()->where('status', 'active')->whereNotNull('country')->pluck('country'))
            ->merge(Trip::query()->where('status', 'active')->whereNotNull('country')->pluck('country'));

        $slugs = [];
        foreach ($raw as $value) {
            $canonical = CountrySlug::canonicalize((string) $value);
            if ($canonical === null || $canonical === '') {
                continue;
            }
            if (! $this->destinations->isKnownCountrySlug($canonical)) {
                continue;
            }
            $slugs[$canonical] = $canonical;
        }

        return array_values($slugs);
    }
}
