<?php

namespace App\Services\Sitemap;

use App\Contracts\Sitemap\SitemapContributorInterface;
use App\Services\Seo\LocalePathMapper;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

final class SitemapGenerator
{
    /**
     * Child files from the pre-Sept-2026 layout. Deleted on every run so they 404 instead of
     * lingering with stale URLs next to the new set.
     *
     * @var list<string>
     */
    private const LEGACY_FILES = [
        '/sitemap_%s.xml',
        '/sitemap_listing_%s.xml',
        '/sitemap_vacations_%s.xml',
        '/sitemap_categories_%s.xml',
        '/sitemap_destinations_%s.xml',
        '/sitemap_guiding_destinations_%s.xml',
        '/sitemap_fishing_magazine_%s.xml',
        '/sitemap_routes.xml',
    ];

    /**
     * @param  iterable<SitemapContributorInterface>  $contributors
     */
    public function __construct(
        private readonly iterable $contributors,
        private readonly SitemapXmlWriter $writer,
        private readonly ?LocalePathMapper $localePaths = null,
        private readonly array $localeBaseUrls = [],
    ) {}

    /**
     * @return array{files: list<string>, counts: array<string, int>}
     */
    public function generateForLanguage(string $lang, string $baseUrl): array
    {
        $context = new SitemapContext(rtrim($baseUrl, '/'), $lang);
        $children = [];
        $counts = [];

        // Content lookups (CMS copy, hub-grid labels) resolve against the app locale.
        $previousLocale = app()->getLocale();
        app()->setLocale($lang);

        try {
            $this->writeChildren($context, $children, $counts);
        } finally {
            app()->setLocale($previousLocale);
        }

        $indexPath = '/sitemap_index_' . $lang . '.xml';
        $this->writer->writeIndex($indexPath, $children);
        $counts['index'] = count($children);

        $this->deleteLegacyFiles($lang);

        return [
            'files' => array_merge(array_keys($children), [$context->baseUrl . '/sitemaps' . $indexPath]),
            'counts' => $counts,
        ];
    }

    /**
     * @param  array<string, ?string>  $children
     * @param  array<string, int>  $counts
     */
    private function writeChildren(SitemapContext $context, array &$children, array &$counts): void
    {
        $lang = $context->lang;

        foreach ($this->contributors as $contributor) {
            $fileName = $contributor->fileName($lang);
            $entries = $contributor->entries($context)
                ->map(fn (SitemapEntry $entry) => $this->withDefaultAlternates($entry, $context));
            $result = $this->writer->writeUrlset($fileName, $entries);
            $counts[$contributor->key()] = $result['count'];
            $children[$context->baseUrl . '/sitemaps' . $fileName] = $result['lastmod'];
            Log::info('sitemap.generated', [
                'lang' => $lang,
                'key' => $contributor->key(),
                'file' => $fileName,
                'count' => $result['count'],
            ]);
        }
    }

    /**
     * Every localized entry gets the same en/de/x-default set the page's own <head> declares
     * (components/seo/hreflang.blade.php), so sitemap and page never disagree.
     */
    private function withDefaultAlternates(SitemapEntry $entry, SitemapContext $context): SitemapEntry
    {
        if (! $entry->localized || $entry->alternates !== [] || $this->localePaths === null
            || ! isset($this->localeBaseUrls['en'], $this->localeBaseUrls['de'])) {
            return $entry;
        }

        if (! str_starts_with($entry->loc, $context->baseUrl)) {
            return $entry;
        }

        $path = ltrim(substr($entry->loc, strlen($context->baseUrl)), '/');
        $en = $this->localePaths->alternateUrl($this->localeBaseUrls['en'], $path, $context->lang, 'en');
        $de = $this->localePaths->alternateUrl($this->localeBaseUrls['de'], $path, $context->lang, 'de');

        return $entry->withAlternates(['en' => $en, 'de' => $de, 'x-default' => $en]);
    }

    private function deleteLegacyFiles(string $lang): void
    {
        $disk = Storage::disk('sitemaps');
        foreach (self::LEGACY_FILES as $pattern) {
            $file = sprintf($pattern, $lang);
            if ($disk->exists($file)) {
                $disk->delete($file);
            }
        }
    }
}
