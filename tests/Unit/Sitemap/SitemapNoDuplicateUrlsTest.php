<?php

namespace Tests\Unit\Sitemap;

use App\Services\Sitemap\SitemapContext;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * No URL may appear in more than one sitemap file — overlap makes Search Console's
 * per-sitemap coverage attribution ambiguous. Hub pages (/vacations, /guidings/countries)
 * were each emitted by two contributors before this was enforced.
 */
class SitemapNoDuplicateUrlsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_no_url_is_emitted_by_more_than_one_contributor(): void
    {
        foreach (['en' => 'https://www.catchaguide.com', 'de' => 'https://www.catchaguide.de'] as $lang => $baseUrl) {
            $context = new SitemapContext($baseUrl, $lang);
            $owners = [];

            foreach (app()->tagged('sitemap.contributors') as $contributor) {
                foreach ($contributor->entries($context) as $entry) {
                    $owners[$entry->loc][] = $contributor->key();
                }
            }

            $duplicates = array_filter($owners, fn (array $keys) => count($keys) > 1);

            $this->assertSame([], $duplicates, "Duplicate sitemap URLs for {$lang}");
        }
    }
}
