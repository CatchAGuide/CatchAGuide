<?php

namespace Tests\Unit\Sitemap;

use App\Models\CategoryPage;
use App\Models\Target;
use App\Repositories\Guiding\GuidingCategoryAvailabilityRepository;
use App\Services\Sitemap\Contributors\CategorySitemapContributor;
use App\Services\Sitemap\SitemapContext;
use App\Services\Sitemap\SitemapEntry;
use App\Services\Vacation\VacationTargetFishSelector;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
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

        $contributor = app(CategorySitemapContributor::class);
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

    /**
     * guidings/targets/{slug} and vacations/targets/{slug} render substantially different, real
     * content from the global /targets/{slug} page (confirmed live: distinct title, several
     * times the byte size) — but only for species that actually have listings in that scope,
     * gated the same way TargetFishPageController::show() gates them (see CLAUDE.md's
     * "SEO / catalog page conventions").
     */
    public function test_scoped_target_pages_are_gated_by_listing_availability(): void
    {
        $species = Target::query()->create(['name' => 'Sitemap Zander', 'name_en' => 'Sitemap Zander']);

        $withTours = CategoryPage::query()->create([
            'type' => 'Targets',
            'source_id' => $species->id,
            'name' => $species->name,
            'slug' => 'sitemap-zander-tours-'.uniqid(),
        ]);
        $withoutTours = CategoryPage::query()->create([
            'type' => 'Targets',
            'source_id' => $species->id,
            'name' => $species->name,
            'slug' => 'sitemap-zander-no-listings-'.uniqid(),
        ]);

        // The shared dev DB already has ~81 real Targets-type CategoryPage rows (confirmed via
        // the Sept 2026 audit) processed by this same loop, so these mocks must handle any
        // species id gracefully rather than only the one this test creates.
        $guidingAvailability = Mockery::mock(GuidingCategoryAvailabilityRepository::class);
        $guidingAvailability->shouldReceive('hasGuidingsForTarget')
            ->andReturnUsing(fn (int $id) => $id === $species->id);
        $vacationAvailability = Mockery::mock(VacationTargetFishSelector::class);
        $vacationAvailability->shouldReceive('hasActiveListings')->andReturn(false);

        $contributor = new CategorySitemapContributor(
            app(\App\Services\Sitemap\SitemapPathEncoder::class),
            $guidingAvailability,
            $vacationAvailability,
        );

        $locs = $contributor->entries(new SitemapContext('https://www.catchaguide.com', 'en'))
            ->map(fn (SitemapEntry $entry) => $entry->loc)
            ->all();

        $this->assertContains('https://www.catchaguide.com/guidings/targets/'.$withTours->slug, $locs);
        $this->assertContains('https://www.catchaguide.com/guidings/targets/'.$withoutTours->slug, $locs);
        $this->assertNotContains('https://www.catchaguide.com/vacations/targets/'.$withTours->slug, $locs);
        $this->assertNotContains('https://www.catchaguide.com/vacations/targets/'.$withoutTours->slug, $locs);
    }
}
