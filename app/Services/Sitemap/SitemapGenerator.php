<?php

namespace App\Services\Sitemap;

use App\Contracts\Sitemap\SitemapContributorInterface;
use Illuminate\Support\Facades\Log;

final class SitemapGenerator
{
    /**
     * @param  iterable<SitemapContributorInterface>  $contributors
     */
    public function __construct(
        private readonly iterable $contributors,
        private readonly SitemapXmlWriter $writer,
    ) {}

    /**
     * @return array{files: list<string>, counts: array<string, int>}
     */
    public function generateForLanguage(string $lang, string $baseUrl): array
    {
        $context = new SitemapContext(rtrim($baseUrl, '/'), $lang);
        $childUrls = [];
        $counts = [];

        foreach ($this->contributors as $contributor) {
            $fileName = $contributor->fileName($lang);
            $entries = $contributor->entries($context);
            $count = $this->writer->writeUrlset($fileName, $entries);
            $counts[$contributor->key()] = $count;
            $childUrls[] = $context->baseUrl . '/sitemaps' . $fileName;
            Log::info('sitemap.generated', [
                'lang' => $lang,
                'key' => $contributor->key(),
                'file' => $fileName,
                'count' => $count,
            ]);
        }

        $indexPath = '/sitemap_index_' . $lang . '.xml';
        $this->writer->writeIndex($indexPath, $childUrls);
        $counts['index'] = count($childUrls);

        return [
            'files' => array_merge($childUrls, [$context->baseUrl . '/sitemaps' . $indexPath]),
            'counts' => $counts,
        ];
    }
}
