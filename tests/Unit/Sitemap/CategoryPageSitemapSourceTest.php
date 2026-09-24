<?php

namespace Tests\Unit\Sitemap;

use App\Domain\CategoryPage\CategoryPageScope;
use App\Models\CategoryPage;
use App\Models\Language;
use App\Repositories\Guiding\GuidingCategoryAvailabilityRepository;
use App\Services\CategoryPage\CategoryPageContentService;
use App\Services\Sitemap\CategoryPageSitemapSource;
use App\Services\Sitemap\SitemapLastmod;
use App\Services\Vacation\VacationTargetFishSelector;
use Mockery;
use Tests\TestCase;

/**
 * The sitemap only lists a species/method URL when the page itself would render — the same
 * content + listing checks TargetFishPageController and CategoryController apply before 404ing.
 */
class CategoryPageSitemapSourceTest extends TestCase
{
    private function page(int $sourceId, string $type = 'Targets'): CategoryPage
    {
        $page = new CategoryPage(['type' => $type, 'slug' => 'art-'.$sourceId, 'name' => 'Art', 'source_id' => $sourceId]);
        $page->id = $sourceId;

        return $page;
    }

    /**
     * @param  list<string>  $scopesWithContent
     */
    private function source(bool $tours, array $vacationCounts, array $scopesWithContent): CategoryPageSitemapSource
    {
        $content = Mockery::mock(CategoryPageContentService::class);
        $content->shouldReceive('resolveForDisplay')->andReturnUsing(
            fn (CategoryPage $page, string $scope) => in_array($scope, $scopesWithContent, true) ? new Language() : null
        );
        $guidings = Mockery::mock(GuidingCategoryAvailabilityRepository::class);
        $guidings->shouldReceive('hasGuidingsForTarget')->andReturn($tours);
        $guidings->shouldReceive('hasGuidingsForMethod')->andReturn($tours);
        $vacations = Mockery::mock(VacationTargetFishSelector::class);
        $vacations->shouldReceive('activeListingCounts')->andReturn($vacationCounts);

        return new CategoryPageSitemapSource($content, $guidings, $vacations, new SitemapLastmod());
    }

    public function test_species_with_only_camps_is_live_globally_on_vacations_and_camps_but_not_tours_or_trips(): void
    {
        $page = $this->page(501);
        $source = $this->source(false, ['camps' => 2, 'trips' => 0], [CategoryPageScope::GLOBAL, CategoryPageScope::TOURS, CategoryPageScope::VACATIONS]);

        $this->assertTrue($source->globalTargetIsLive($page, 'de'));
        $this->assertFalse($source->tourTargetIsLive($page, 'de'));
        $this->assertTrue($source->vacationTargetIsLive($page, 'de'));
        $this->assertTrue($source->vacationTargetIsLive($page, 'de', 'camp'));
        $this->assertFalse($source->vacationTargetIsLive($page, 'de', 'trip'));
    }

    public function test_species_without_scoped_copy_is_not_live_in_that_scope_even_with_listings(): void
    {
        $page = $this->page(502);
        // The vacations species page 404s without vacations-scoped copy (no cross-scope fallback).
        $source = $this->source(true, ['camps' => 1, 'trips' => 1], [CategoryPageScope::GLOBAL]);

        $this->assertTrue($source->globalTargetIsLive($page, 'de'));
        $this->assertFalse($source->tourTargetIsLive($page, 'de'));
        $this->assertFalse($source->vacationTargetIsLive($page, 'de'));
    }

    public function test_species_without_any_listing_is_not_live_anywhere(): void
    {
        $page = $this->page(503);
        $source = $this->source(false, ['camps' => 0, 'trips' => 0], [CategoryPageScope::GLOBAL, CategoryPageScope::TOURS, CategoryPageScope::VACATIONS]);

        $this->assertFalse($source->globalTargetIsLive($page, 'de'));
        $this->assertFalse($source->vacationTargetIsLive($page, 'de'));
    }

    public function test_method_needs_a_tour_and_content(): void
    {
        $method = $this->page(504, 'Methods');

        $this->assertTrue($this->source(true, ['camps' => 0, 'trips' => 0], [CategoryPageScope::TOURS])->methodIsLive($method, 'de'));
        $this->assertFalse($this->source(false, ['camps' => 0, 'trips' => 0], [CategoryPageScope::TOURS])->methodIsLive($method, 'de'));
        $this->assertFalse($this->source(true, ['camps' => 0, 'trips' => 0], [])->methodIsLive($method, 'de'));
    }
}
