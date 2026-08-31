<?php

namespace Tests\Feature\Category;

use App\Domain\CategoryPage\CategoryPageEntityType;
use App\Domain\CategoryPage\CategoryPageScope;
use App\Domain\Offers\OfferListingFilter;
use App\Domain\Offers\ViewModels\OfferCatalogViewModel;
use App\Enums\GuideStatus;
use App\Models\CategoryPage;
use App\Models\FishingType;
use App\Models\Guiding;
use App\Models\Language;
use App\Models\Method;
use App\Models\Target;
use App\Models\User;
use App\Services\Offers\OfferCatalogPageService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Mockery;
use Tests\TestCase;

/**
 * The "switch species / switch method" behavior on target-fish and method category pages
 * (mirrors the existing country switch on /guidings/{country}): the species/methods field
 * stays the normal multi-select widget, and a small id -> url config embedded in the form
 * drives the client-side decision of whether to jump to a sibling category page or fall back
 * to /offers once the user ends up with a given selection.
 */
class CategoryRedirectFilterTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://localhost']);
        URL::forceRootUrl('http://localhost');
        Cache::forget('guiding_category_availability_v1');

        $this->withoutMiddleware([
            \Illuminate\Routing\Middleware\ThrottleRequests::class,
            \App\Http\Middleware\DDoSProtectionMiddleware::class,
        ]);
    }

    private function createTargetFishPage(string $slugPrefix, string $scope, string $nameEn): CategoryPage
    {
        $target = new Target();
        $target->forceFill([
            'name' => $nameEn,
            'name_en' => $nameEn,
        ])->save();

        $page = CategoryPage::query()->create([
            'name' => $nameEn,
            'type' => 'Targets',
            'slug' => $slugPrefix.'-'.uniqid(),
            'source_id' => $target->id,
            'is_favorite' => false,
        ]);

        Language::query()->create([
            'source_id' => (string) $target->id,
            'type' => CategoryPageEntityType::TARGET_FISH,
            'scope' => $scope,
            'language' => app()->getLocale(),
            'title' => $nameEn.' fishing',
            'sub_title' => 'Subtitle',
            'introduction' => 'Intro',
            'content' => 'Body',
            'faq_title' => '',
        ]);

        return $page->fresh();
    }

    private function createMethodPage(string $slugPrefix, string $nameEn): CategoryPage
    {
        $method = new Method();
        $method->forceFill([
            'name' => $nameEn,
            'name_en' => $nameEn,
        ])->save();

        $page = CategoryPage::query()->create([
            'name' => $nameEn,
            'type' => 'Methods',
            'slug' => $slugPrefix.'-'.uniqid(),
            'source_id' => $method->id,
            'is_favorite' => false,
        ]);

        Language::query()->create([
            'source_id' => (string) $method->id,
            'type' => CategoryPageEntityType::METHOD,
            'scope' => CategoryPageScope::TOURS,
            'language' => app()->getLocale(),
            'title' => $nameEn.' method',
            'sub_title' => 'Subtitle',
            'introduction' => 'Intro',
            'content' => 'Body',
            'faq_title' => '',
        ]);

        return $page->fresh();
    }

    private function createTour(array $overrides = []): Guiding
    {
        $user = User::factory()->create([
            'is_guide' => 1,
            'guide_status' => GuideStatus::VERIFIED,
        ]);

        $guiding = new Guiding();
        $guiding->forceFill(array_merge([
            'title' => 'Redirect Tour '.uniqid(),
            'slug' => 'redirect-tour-'.uniqid(),
            'location' => 'Somewhere',
            'status' => 1,
            'max_guests' => 4,
            'duration' => 4,
            'fishing_type_id' => FishingType::query()->value('id'),
            'user_id' => $user->id,
        ], $overrides))->save();

        return $guiding;
    }

    /**
     * @param  array<int, array{id:int,name:string}>  $speciesOptions
     */
    private function bindTargetFishCatalog(int $speciesId, array $speciesOptions, string $type = 'tour', bool $lockTourScope = true, bool $lockVacationScope = false): void
    {
        $filter = OfferListingFilter::fromRequest(['type' => $type, 'species' => [$speciesId]]);
        $vm = new OfferCatalogViewModel(
            filter: $filter,
            listings: new LengthAwarePaginator([], 0, 9, 1, ['path' => 'http://localhost']),
            cards: collect(),
            toursTotal: 0,
            tripsTotal: 0,
            campsTotal: 0,
            listingsTotal: 0,
            speciesOptions: collect($speciesOptions),
            countries: collect(),
            methodOptions: collect(),
            waterOptions: collect(),
            tourDurationOptions: collect(),
            tripDurationOptions: collect(),
            accommodationTypeOptions: collect(),
            faq: collect(),
            mapMarkers: [],
            suggestedCards: collect(),
            catalogUrl: null,
            lockSpeciesScope: true,
            lockTourScope: $lockTourScope,
            lockVacationScope: $lockVacationScope,
        );

        $mock = Mockery::mock(OfferCatalogPageService::class);
        $mock->shouldReceive('buildForTargetFish')->andReturn($vm);
        $this->app->instance(OfferCatalogPageService::class, $mock);
    }

    /**
     * @param  array<int, array{id:int,name:string}>  $methodOptions
     */
    private function bindMethodCatalog(int $methodId, array $methodOptions): void
    {
        $filter = OfferListingFilter::fromRequest(['type' => 'tour', 'methods' => [$methodId]]);
        $vm = new OfferCatalogViewModel(
            filter: $filter,
            listings: new LengthAwarePaginator([], 0, 9, 1, ['path' => 'http://localhost']),
            cards: collect(),
            toursTotal: 0,
            tripsTotal: 0,
            campsTotal: 0,
            listingsTotal: 0,
            speciesOptions: collect(),
            countries: collect(),
            methodOptions: collect($methodOptions),
            waterOptions: collect(),
            tourDurationOptions: collect(),
            tripDurationOptions: collect(),
            accommodationTypeOptions: collect(),
            faq: collect(),
            mapMarkers: [],
            suggestedCards: collect(),
            catalogUrl: null,
            lockTourScope: true,
            lockMethodScope: true,
        );

        $mock = Mockery::mock(OfferCatalogPageService::class);
        $mock->shouldReceive('buildForMethod')->andReturn($vm);
        $this->app->instance(OfferCatalogPageService::class, $mock);
    }

    /**
     * @return array{dimension: string, map: array<string,string>, allUrl: ?string}
     */
    private function extractPrimaryRedirectConfig(string $html): array
    {
        preg_match('/data-offers-primary-redirect-config>\s*(\{.*?\})\s*<\/script>/s', $html, $matches);
        $this->assertNotEmpty($matches, 'Expected a data-offers-primary-redirect-config script tag');

        return json_decode($matches[1], true);
    }

    /**
     * Isolate the desktop sidebar form's own markup, so hidden-field-count assertions aren't
     * thrown off by the separate (and legitimately independent) offcanvas form on the same page.
     */
    private function extractSidebarFormHtml(string $html): string
    {
        preg_match('/<form[^>]*id="offers-filters-form"[^>]*>(.*?)<\/form>/s', $html, $matches);
        $this->assertNotEmpty($matches, 'Expected the #offers-filters-form sidebar form');

        return $matches[1];
    }

    public function test_tours_target_fish_page_keeps_species_multiselect_and_config_includes_current_and_siblings(): void
    {
        $pike = $this->createTargetFishPage('pike-redirect', CategoryPageScope::TOURS, 'Pike');
        $zander = $this->createTargetFishPage('zander-redirect', CategoryPageScope::TOURS, 'Zander');
        $pikeId = (int) $pike->source_id;
        $zanderId = (int) $zander->source_id;

        $this->createTour(['target_fish' => json_encode([$zanderId])]);

        $this->bindTargetFishCatalog($pikeId, [
            ['id' => $pikeId, 'name' => 'Pike'],
            ['id' => $zanderId, 'name' => 'Zander'],
        ]);

        $response = $this->get(route('guidings.targets', ['slug' => $pike->slug]));

        $response->assertOk();
        $html = $response->getContent();

        // Multi-select widget renders (not a locked single value) with both the current
        // species and its sibling selectable.
        $response->assertSee('data-input-name="species[]"', false);
        $response->assertSee('offers-species-sidebar-opt-'.$pikeId, false);
        $response->assertSee('offers-species-sidebar-opt-'.$zanderId, false);

        // Exactly one species[] hidden input in the sidebar form on initial load (no duplicate
        // from lockedParams now that the multi-select is the single source of truth for it).
        $sidebarHtml = $this->extractSidebarFormHtml($html);
        $this->assertSame(1, preg_match_all('/<input[^>]*\bname="species\[\]"/', $sidebarHtml));
        $this->assertSame(1, preg_match_all('/<input[^>]*\bname="type"/', $sidebarHtml));

        $config = $this->extractPrimaryRedirectConfig($html);
        $this->assertSame('species', $config['dimension']);
        $this->assertSame(route('guidings.targets', ['slug' => $pike->slug]), $config['map'][(string) $pikeId]);
        $this->assertSame(route('guidings.targets', ['slug' => $zander->slug]), $config['map'][(string) $zanderId]);
        $this->assertSame(route('guidings.targets.index'), $config['allUrl']);
    }

    public function test_tours_target_fish_redirect_map_excludes_species_with_no_tours(): void
    {
        $pike = $this->createTargetFishPage('pike-avail', CategoryPageScope::TOURS, 'Pike');
        $unavailable = $this->createTargetFishPage('carp-no-tours', CategoryPageScope::TOURS, 'Carp');
        $pikeId = (int) $pike->source_id;

        // No tour references $unavailable's target id.
        $this->bindTargetFishCatalog($pikeId, [['id' => $pikeId, 'name' => 'Pike']]);

        $response = $this->get(route('guidings.targets', ['slug' => $pike->slug]));

        $response->assertOk();
        $config = $this->extractPrimaryRedirectConfig($response->getContent());
        $this->assertArrayHasKey((string) $pikeId, $config['map']);
        $this->assertArrayNotHasKey((string) $unavailable->source_id, $config['map']);
    }

    /**
     * Regression test for the "wels" bug: a target-fish page whose own Target row has since
     * been deleted (source_id now dangling) must still get a working redirect config for
     * itself, built from the CategoryPage's own title/slug rather than the missing source.
     */
    public function test_tours_target_fish_redirect_config_survives_orphaned_source_target(): void
    {
        $pike = $this->createTargetFishPage('pike-orphaned', CategoryPageScope::TOURS, 'Pike');
        $pikeId = (int) $pike->source_id;
        Target::whereKey($pikeId)->delete();

        $this->bindTargetFishCatalog($pikeId, []);

        $response = $this->get(route('guidings.targets', ['slug' => $pike->slug]));

        $response->assertOk();
        $config = $this->extractPrimaryRedirectConfig($response->getContent());
        $this->assertSame('species', $config['dimension']);
        $this->assertSame(route('guidings.targets', ['slug' => $pike->slug]), $config['map'][(string) $pikeId]);
    }

    public function test_vacations_target_fish_page_config_includes_siblings_without_availability_gating(): void
    {
        $pike = $this->createTargetFishPage('pike-vac-redirect', CategoryPageScope::VACATIONS, 'Pike');
        $zander = $this->createTargetFishPage('zander-vac-redirect', CategoryPageScope::VACATIONS, 'Zander');
        $pikeId = (int) $pike->source_id;
        $zanderId = (int) $zander->source_id;

        // No tours at all for either species: vacations scope must not availability-gate.
        $this->bindTargetFishCatalog(
            $pikeId,
            [['id' => $pikeId, 'name' => 'Pike'], ['id' => $zanderId, 'name' => 'Zander']],
            type: 'vacation',
            lockTourScope: false,
            lockVacationScope: true,
        );

        $response = $this->get(route('vacations.targets', ['slug' => $pike->slug]));

        $response->assertOk();
        $config = $this->extractPrimaryRedirectConfig($response->getContent());
        $this->assertSame('species', $config['dimension']);
        $this->assertSame(route('vacations.targets', ['slug' => $zander->slug]), $config['map'][(string) $zanderId]);
        $this->assertSame(route('vacations.index'), $config['allUrl']);
    }

    public function test_guidings_methods_page_keeps_multiselect_and_config_includes_current_and_siblings(): void
    {
        $spin = $this->createMethodPage('spin-redirect', 'Spinning');
        $fly = $this->createMethodPage('fly-redirect', 'FlyFishing');
        $spinId = (int) $spin->source_id;
        $flyId = (int) $fly->source_id;

        $this->createTour(['fishing_methods' => json_encode([$flyId])]);

        $this->bindMethodCatalog($spinId, [
            ['id' => $spinId, 'name' => 'Spinning'],
            ['id' => $flyId, 'name' => 'FlyFishing'],
        ]);

        $response = $this->get(route('guidings.methods.show', ['slug' => $spin->slug]));

        $response->assertOk();
        $html = $response->getContent();
        $response->assertSee('data-input-name="methods[]"', false);

        $sidebarHtml = $this->extractSidebarFormHtml($html);
        $this->assertSame(1, preg_match_all('/<input[^>]*\bname="methods\[\]"/', $sidebarHtml));

        $config = $this->extractPrimaryRedirectConfig($html);
        $this->assertSame('methods', $config['dimension']);
        $this->assertSame(route('guidings.methods.show', ['slug' => $spin->slug]), $config['map'][(string) $spinId]);
        $this->assertSame(route('guidings.methods.show', ['slug' => $fly->slug]), $config['map'][(string) $flyId]);
        $this->assertSame(route('guidings.methods'), $config['allUrl']);
    }

    /**
     * Regression test for the same "wels"-shaped bug on the methods pillar: a method page
     * whose own Method row has since been deleted must still get a working redirect config.
     */
    public function test_guidings_methods_redirect_config_survives_orphaned_source_method(): void
    {
        $spin = $this->createMethodPage('spin-orphaned', 'Spinning');
        $spinId = (int) $spin->source_id;
        Method::whereKey($spinId)->delete();

        $this->bindMethodCatalog($spinId, []);

        $response = $this->get(route('guidings.methods.show', ['slug' => $spin->slug]));

        $response->assertOk();
        $config = $this->extractPrimaryRedirectConfig($response->getContent());
        $this->assertSame('methods', $config['dimension']);
        $this->assertSame(route('guidings.methods.show', ['slug' => $spin->slug]), $config['map'][(string) $spinId]);
    }
}
