<?php

namespace Tests\Feature\Console\CategoryPages;

use App\Domain\CategoryPage\CategoryPageEntityType;
use App\Domain\CategoryPage\CategoryPageScope;
use App\Models\CategoryEntity;
use App\Services\CategoryPage\CategoryPageContentService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CopyGeoToursContentToGlobalCommandTest extends TestCase
{
    use DatabaseTransactions;

    private CategoryPageContentService $content;

    private CategoryEntity $region;

    private CategoryEntity $city;

    protected function setUp(): void
    {
        parent::setUp();

        $this->content = app(CategoryPageContentService::class);

        $suffix = uniqid();
        $country = CategoryEntity::countries()->create(['type' => 'country', 'name' => 'Copytest', 'slug' => 'copytest-'.$suffix]);
        $this->region = CategoryEntity::regions()->create([
            'type' => 'region', 'country_id' => $country->id, 'name' => 'Copy Region', 'slug' => 'copy-region-'.$suffix,
        ]);
        $this->city = CategoryEntity::cities()->create([
            'type' => 'city', 'country_id' => $country->id, 'region_id' => $this->region->id, 'name' => 'Copy City', 'slug' => 'copy-city-'.$suffix,
        ]);

        $this->content->upsertEntity(CategoryPageEntityType::GEO_REGION, $this->region->id, CategoryPageScope::TOURS, 'de', [
            'title' => 'Tours Region DE', 'introduction' => 'Region intro', 'fish_avail_title' => 'Fischkalender',
        ], [['question' => 'Wann?', 'answer' => 'Jetzt']]);
        $this->content->upsertEntity(CategoryPageEntityType::GEO_CITY, $this->city->id, CategoryPageScope::TOURS, 'de', [
            'title' => 'Tours City DE',
        ]);
        $this->content->upsertEntity(CategoryPageEntityType::GEO_CITY, $this->city->id, CategoryPageScope::GLOBAL, 'de', [
            'title' => 'Existing Global City DE',
        ]);
    }

    public function test_copies_tours_content_and_faqs_into_empty_global_scope(): void
    {
        $this->artisan('category-pages:copy-geo-tours-to-global')->assertSuccessful();

        $global = $this->content->findForEntity(CategoryPageEntityType::GEO_REGION, $this->region->id, CategoryPageScope::GLOBAL, 'de');
        $this->assertSame('Tours Region DE', $global->title);
        $this->assertSame('Region intro', $global->introduction);
        $this->assertSame('Fischkalender', $global->fish_avail_title);

        $faqs = $this->content->faqsForEntity(CategoryPageEntityType::GEO_REGION, $this->region->id, CategoryPageScope::GLOBAL, 'de');
        $this->assertSame(['Wann?'], $faqs->pluck('question')->all());

        // Tours copy is left in place.
        $this->assertSame('Tours Region DE', $this->content->findForEntity(CategoryPageEntityType::GEO_REGION, $this->region->id, CategoryPageScope::TOURS, 'de')->title);
    }

    public function test_keeps_filled_global_content_unless_forced(): void
    {
        $this->artisan('category-pages:copy-geo-tours-to-global')->assertSuccessful();
        $this->assertSame('Existing Global City DE', $this->globalCityTitle());

        $this->artisan('category-pages:copy-geo-tours-to-global', ['--force' => true])->assertSuccessful();
        $this->assertSame('Tours City DE', $this->globalCityTitle());
    }

    public function test_dry_run_writes_nothing(): void
    {
        $this->artisan('category-pages:copy-geo-tours-to-global', ['--dry-run' => true])->assertSuccessful();

        $this->assertNull($this->content->findForEntity(CategoryPageEntityType::GEO_REGION, $this->region->id, CategoryPageScope::GLOBAL, 'de'));
    }

    private function globalCityTitle(): ?string
    {
        return $this->content->findForEntity(CategoryPageEntityType::GEO_CITY, $this->city->id, CategoryPageScope::GLOBAL, 'de')?->title;
    }
}
