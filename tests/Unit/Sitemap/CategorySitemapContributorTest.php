<?php

namespace Tests\Unit\Sitemap;

use App\Models\CategoryPage;
use App\Services\Sitemap\Contributors\CategorySitemapContributor;
use App\Services\Sitemap\SitemapContext;
use App\Services\Sitemap\SitemapEntry;
use App\Services\Sitemap\SitemapPathEncoder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CategorySitemapContributorTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_includes_targets_and_methods_but_excludes_rows_with_bad_type(): void
    {
        $target = CategoryPage::query()->create([
            'type' => 'Targets',
            'source_id' => 1,
            'name' => 'Karpfen',
            'slug' => 'sitemap-karpfen-'.uniqid(),
        ]);
        $method = CategoryPage::query()->create([
            'type' => 'Methods',
            'source_id' => 2,
            'name' => 'Ansitzangeln',
            'slug' => 'sitemap-ansitzangeln-'.uniqid(),
        ]);
        // Stale/bad data: a row whose `type` is neither Targets nor Methods, mirroring the
        // orphaned rows found live (type set to a species slug instead of 'Targets') that had
        // no route to serve them and were showing up as 404s in Search Console.
        $orphan = CategoryPage::query()->create([
            'type' => 'karpfen',
            'source_id' => 3,
            'name' => 'Orphaned row',
            'slug' => 'blaufloassen-thunfisch-'.uniqid(),
        ]);

        $contributor = new CategorySitemapContributor(new SitemapPathEncoder());
        $locs = $contributor->entries(new SitemapContext('https://www.catchaguide.com', 'en'))
            ->map(fn (SitemapEntry $entry) => $entry->loc)
            ->all();

        $this->assertContains('https://www.catchaguide.com/targets/'.$target->slug, $locs);
        $this->assertContains('https://www.catchaguide.com/guidings/methods/'.$method->slug, $locs);
        $this->assertNotContains('https://www.catchaguide.com/category-page/karpfen/'.$orphan->slug, $locs);

        foreach ($locs as $loc) {
            $this->assertStringNotContainsString('/category-page/', $loc);
        }
    }
}
