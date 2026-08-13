<?php

namespace Tests\Unit\Services\CategoryPage;

use App\Domain\CategoryPage\CategoryPageEntityType;
use App\Domain\CategoryPage\CategoryPageScope;
use App\Models\CategoryPage;
use App\Models\Language;
use App\Models\Target;
use App\Services\CategoryPage\CategoryPageContentService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CategoryPageContentServiceTest extends TestCase
{
    use DatabaseTransactions;

    private CategoryPageContentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(CategoryPageContentService::class);
    }

    private function createTargetPage(): CategoryPage
    {
        $target = new Target();
        $target->forceFill([
            'name' => 'Test Pike '.uniqid(),
            'name_en' => 'Test Pike',
        ])->save();

        return CategoryPage::query()->create([
            'name' => $target->name,
            'type' => 'Targets',
            'slug' => 'test-pike-'.uniqid(),
            'source_id' => $target->id,
            'is_favorite' => false,
        ]);
    }

    public function test_upsert_creates_scoped_language_rows(): void
    {
        $page = $this->createTargetPage();

        $this->service->upsert($page, CategoryPageScope::GLOBAL, 'de', [
            'title' => 'Global DE title',
            'sub_title' => 'Global DE sub',
            'introduction' => 'Intro',
            'content' => 'Body',
            'faq_title' => 'FAQ',
        ], [
            ['question' => 'Q1', 'answer' => 'A1'],
        ]);

        $this->assertDatabaseHas('languages', [
            'source_id' => (string) $page->source_id,
            'type' => CategoryPageEntityType::TARGET_FISH,
            'scope' => CategoryPageScope::GLOBAL,
            'language' => 'de',
            'title' => 'Global DE title',
        ]);

        $this->assertDatabaseHas('faqs', [
            'source_id' => (string) $page->source_id,
            'page' => CategoryPageEntityType::TARGET_FISH,
            'scope' => CategoryPageScope::GLOBAL,
            'language' => 'de',
            'question' => 'Q1',
        ]);
    }

    public function test_resolve_for_display_prefers_global_over_tours(): void
    {
        $page = $this->createTargetPage();

        Language::query()->create([
            'source_id' => (string) $page->source_id,
            'type' => CategoryPageEntityType::TARGET_FISH,
            'scope' => CategoryPageScope::TOURS,
            'language' => 'en',
            'title' => 'Tours title',
            'sub_title' => 'Tours sub',
        ]);

        Language::query()->create([
            'source_id' => (string) $page->source_id,
            'type' => CategoryPageEntityType::TARGET_FISH,
            'scope' => CategoryPageScope::GLOBAL,
            'language' => 'en',
            'title' => 'Global title',
            'sub_title' => 'Global sub',
        ]);

        $resolved = $this->service->resolveForDisplay($page, CategoryPageScope::GLOBAL, 'en');

        $this->assertSame('Global title', $resolved?->title);
    }

    public function test_resolve_for_display_falls_back_to_tours_when_global_empty(): void
    {
        $page = $this->createTargetPage();

        Language::query()->create([
            'source_id' => (string) $page->source_id,
            'type' => CategoryPageEntityType::TARGET_FISH,
            'scope' => CategoryPageScope::TOURS,
            'language' => 'en',
            'title' => 'Tours fallback title',
            'sub_title' => 'Tours sub',
        ]);

        $resolved = $this->service->resolveForDisplay($page, CategoryPageScope::GLOBAL, 'en');

        $this->assertSame('Tours fallback title', $resolved?->title);
    }

    public function test_completeness_tracks_scopes_independently(): void
    {
        $page = $this->createTargetPage();

        $this->service->upsert($page, CategoryPageScope::VACATIONS, 'de', [
            'title' => 'Vacations DE',
            'sub_title' => 'Sub',
        ]);

        $completeness = $this->service->completenessForScopes($page, [
            CategoryPageScope::GLOBAL,
            CategoryPageScope::VACATIONS,
        ]);

        $this->assertFalse($completeness[CategoryPageScope::GLOBAL]['de']);
        $this->assertTrue($completeness[CategoryPageScope::VACATIONS]['de']);
    }

    public function test_filled_locales_from_completeness(): void
    {
        $locales = $this->service->filledLocalesFromCompleteness([
            CategoryPageScope::GLOBAL => ['de' => false, 'en' => false],
            CategoryPageScope::TOURS => ['de' => true, 'en' => false],
            CategoryPageScope::VACATIONS => ['de' => false, 'en' => true],
        ]);

        $this->assertSame(['de', 'en'], $locales);
    }

    public function test_trips_and_camps_do_not_inherit_vacations_content(): void
    {
        $countryId = (string) random_int(900000, 999999);

        Language::query()->create([
            'source_id' => $countryId,
            'type' => CategoryPageEntityType::GEO_COUNTRY,
            'scope' => CategoryPageScope::VACATIONS,
            'language' => 'en',
            'title' => 'Vacations Spain',
            'sub_title' => 'Vacation subtitle',
            'introduction' => 'Vacation intro',
            'content' => 'Vacation body',
            'faq_title' => 'Vacation FAQ',
        ]);

        $trips = $this->service->resolveEntityForDisplay(
            CategoryPageEntityType::GEO_COUNTRY,
            $countryId,
            CategoryPageScope::TRIPS,
            'en',
        );
        $camps = $this->service->resolveEntityForDisplay(
            CategoryPageEntityType::GEO_COUNTRY,
            $countryId,
            CategoryPageScope::CAMPS,
            'en',
        );
        $vacations = $this->service->resolveEntityForDisplay(
            CategoryPageEntityType::GEO_COUNTRY,
            $countryId,
            CategoryPageScope::VACATIONS,
            'en',
        );

        $this->assertSame('Vacations Spain', $vacations?->title);
        $this->assertNull($trips);
        $this->assertNull($camps);
    }

    public function test_global_strict_mode_does_not_fall_back_to_tours(): void
    {
        $countryId = (string) random_int(900000, 999999);

        Language::query()->create([
            'source_id' => $countryId,
            'type' => CategoryPageEntityType::GEO_COUNTRY,
            'scope' => CategoryPageScope::TOURS,
            'language' => 'en',
            'title' => 'Tours Spain Title',
            'sub_title' => 'Tours sub',
            'introduction' => 'Tours intro',
            'content' => 'Tours body',
            'faq_title' => 'Tours FAQ',
        ]);

        $withFallback = $this->service->resolveEntityForDisplay(
            CategoryPageEntityType::GEO_COUNTRY,
            $countryId,
            CategoryPageScope::GLOBAL,
            'en',
            null,
            true,
        );
        $strict = $this->service->resolveEntityForDisplay(
            CategoryPageEntityType::GEO_COUNTRY,
            $countryId,
            CategoryPageScope::GLOBAL,
            'en',
            null,
            false,
        );

        $this->assertSame('Tours Spain Title', $withFallback?->title);
        $this->assertNull($strict);
    }
}
