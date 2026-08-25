<?php

namespace Tests\Feature\Category;

use App\Domain\CategoryPage\CategoryPageEntityType;
use App\Domain\CategoryPage\CategoryPageScope;
use App\Domain\Offers\OfferListingFilter;
use App\Domain\Offers\ViewModels\OfferCatalogViewModel;
use App\Models\CategoryPage;
use App\Models\Language;
use App\Models\Target;
use App\Services\Homepage\HomepageMixedOfferSelector;
use App\Services\Offers\OfferCatalogPageService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\URL;
use Mockery;
use Tests\TestCase;

class TargetFishOffersCatalogTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.url' => 'http://localhost']);
        URL::forceRootUrl('http://localhost');

        $this->withoutMiddleware([
            \Illuminate\Routing\Middleware\ThrottleRequests::class,
            \App\Http\Middleware\DDoSProtectionMiddleware::class,
        ]);
    }

    private function createTargetFishPage(string $slugPrefix, string $scope = CategoryPageScope::GLOBAL): CategoryPage
    {
        $target = new Target();
        $target->forceFill([
            'name' => 'Hecht',
            'name_en' => 'Pike',
        ])->save();

        $page = CategoryPage::query()->create([
            'name' => 'Pike',
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
            'title' => strtoupper($scope).' Pike fishing',
            'sub_title' => 'Scoped subtitle '.$scope,
            'introduction' => 'Intro text for pike category ('.$scope.').',
            'content' => 'Body content for pike ('.$scope.').',
            'faq_title' => '',
        ]);

        return $page->fresh();
    }

    public function test_target_fish_category_renders_offer_modules_for_global_scope(): void
    {
        $page = $this->createTargetFishPage('pike-offers-test');

        $mock = Mockery::mock(HomepageMixedOfferSelector::class);
        $mock->shouldReceive('byModuleForTargetFish')
            ->once()
            ->withArgs(fn ($speciesId) => (int) $speciesId === (int) $page->source_id)
            ->andReturn([
                'tour' => collect([$this->moduleCard('tour', 'Pike Tour')]),
                'camp' => collect([$this->moduleCard('camp', 'Pike Camp')]),
                'trip' => collect([$this->moduleCard('trip', 'Pike Trip')]),
            ]);
        $this->app->instance(HomepageMixedOfferSelector::class, $mock);

        $response = $this->get(route('targets.show', [
            'slug' => $page->slug,
        ]));

        $response->assertOk();
        $response->assertSee('GLOBAL Pike fishing', false);
        $response->assertSee('data-offer-module="tour"', false);
        $response->assertSee('data-offer-module="camp"', false);
        $response->assertSee('data-offer-module="trip"', false);
        $response->assertSee('Pike Tour', false);
        $response->assertSee('Pike Camp', false);
        $response->assertSee('Pike Trip', false);
        $response->assertDontSee('data-offers-type-filter', false);
        $response->assertDontSee('id="guidings-list"', false);
        $response->assertSee('cag-site-nav--overlay', false);
        $response->assertSee('data-category-header-shell', false);
        $response->assertSee('offers-page-header__hero', false);
        $response->assertDontSee('navbar-custom short-header long-header', false);
    }

    public function test_tours_target_fish_page_uses_tours_scope_and_locks_catalog(): void
    {
        $page = $this->createTargetFishPage('pike-tours-test', CategoryPageScope::TOURS);

        $this->bindTargetFishCatalog(fn () => $this->viewModel(
            type: 'tour',
            catalogUrl: route('guidings.targets', ['slug' => $page->slug]),
            speciesIds: [(int) $page->source_id],
            cards: collect([$this->card('tour', 'Pike Tour')]),
            lockTourScope: true,
        ));

        $response = $this->get(route('guidings.targets', ['slug' => $page->slug]));

        $response->assertOk();
        $response->assertSee('TOURS Pike fishing', false);
        $response->assertSee('data-offer-type="tour"', false);
        $response->assertSee('name="type"', false);
        $response->assertSee('value="tour"', false);
        $response->assertSee('cag-site-nav--overlay', false);
        $response->assertDontSee('hero-tour.webp', false);
        $response->assertSee('data-category-header-shell', false);
        $response->assertDontSee('navbar-custom short-header long-header', false);
        $response->assertSee('action="'.url('/guidings/alloffers').'"', false);
        $response->assertDontSee('action="'.url('/offers').'"', false);
        $response->assertDontSee('guidings-page-header__segment--fish', false);
    }

    public function test_vacations_target_fish_page_uses_vacations_scope_and_locks_catalog(): void
    {
        $page = $this->createTargetFishPage('pike-vacations-test', CategoryPageScope::VACATIONS);

        $this->bindTargetFishCatalog(fn () => $this->viewModel(
            type: 'vacation',
            catalogUrl: route('vacations.targets', ['slug' => $page->slug]),
            speciesIds: [(int) $page->source_id],
            cards: collect([
                $this->card('trip', 'Pike Trip'),
                $this->card('camp', 'Pike Camp'),
            ]),
            lockVacationScope: true,
        ));

        $response = $this->get(route('vacations.targets', ['slug' => $page->slug]));

        $response->assertOk();
        $response->assertSee('VACATIONS Pike fishing', false);
        $response->assertSee('data-offer-type="trip"', false);
        $response->assertSee('name="type"', false);
        $response->assertSee('value="vacation"', false);
        $response->assertSee('cag-site-nav--overlay', false);
        $response->assertSee('data-category-header-shell', false);
        $response->assertSee('offers-page-header__hero', false);
        $response->assertDontSee('navbar-custom short-header long-header', false);
        $response->assertSee('action="'.url('/vacations').'"', false);
    }

    public function test_vacations_target_fish_page_404s_when_content_missing_for_scope(): void
    {
        $page = $this->createTargetFishPage('pike-vacations-missing', CategoryPageScope::TOURS);

        $response = $this->get(route('vacations.targets', ['slug' => $page->slug]));

        $response->assertNotFound();
    }

    public function test_build_for_target_fish_receives_route_scope(): void
    {
        $page = $this->createTargetFishPage('pike-scope-arg-test', CategoryPageScope::TOURS);

        $mock = Mockery::mock(OfferCatalogPageService::class);
        $mock->shouldReceive('buildForTargetFish')
            ->once()
            ->withArgs(function ($request, $speciesId, $scope) use ($page) {
                return (int) $speciesId === (int) $page->source_id
                    && $scope === CategoryPageScope::TOURS;
            })
            ->andReturn($this->viewModel(
                type: 'tour',
                catalogUrl: route('guidings.targets', ['slug' => $page->slug]),
                speciesIds: [(int) $page->source_id],
                lockTourScope: true,
            ));
        $this->app->instance(OfferCatalogPageService::class, $mock);

        $this->get(route('guidings.targets', ['slug' => $page->slug]))->assertOk();
    }

    public function test_target_fish_type_toggle_stays_on_category_url_and_keeps_species(): void
    {
        $base = 'http://localhost/targets/pike';
        $vm = $this->viewModel(
            type: 'all',
            catalogUrl: $base,
            speciesIds: [7],
        );

        $urls = $vm->typeToggleUrls();
        $this->assertStringStartsWith($base, $urls['tour']);
        $this->assertStringContainsString('type=tour', $urls['tour']);
        $this->assertStringContainsString('species%5B0%5D=7', $urls['tour']);
        $this->assertStringNotContainsString('/offers?', $urls['tour']);

        $vacationUrls = $vm->vacationToggleUrls();
        $this->assertStringStartsWith($base, $vacationUrls['trip']);
        $this->assertStringContainsString('type=vacation', $vacationUrls['trip']);
        $this->assertStringContainsString('vacation=trip', $vacationUrls['trip']);
        $this->assertStringContainsString('species%5B0%5D=7', $vacationUrls['trip']);
    }

    /**
     * @param  callable(): OfferCatalogViewModel  $factory
     */
    private function bindTargetFishCatalog(callable $factory): void
    {
        $mock = Mockery::mock(OfferCatalogPageService::class);
        $mock->shouldReceive('buildForTargetFish')->andReturnUsing($factory);
        $this->app->instance(OfferCatalogPageService::class, $mock);
    }

    /**
     * @param  list<int>  $speciesIds
     */
    private function viewModel(
        string $type = 'all',
        string $vacation = 'all',
        $cards = null,
        ?string $catalogUrl = null,
        array $speciesIds = [1],
        bool $lockTourScope = false,
        bool $lockVacationScope = false,
        ?string $place = null,
        ?float $placeLat = null,
        ?float $placeLng = null,
    ): OfferCatalogViewModel {
        $cards = $cards ?? collect();
        $filter = OfferListingFilter::fromRequest(array_filter([
            'type' => $type,
            'vacation' => $vacation !== 'all' ? $vacation : null,
            'species' => $speciesIds,
            'place' => $place,
            'placeLat' => $placeLat,
            'placeLng' => $placeLng,
        ], fn ($v) => $v !== null && $v !== ''));

        $paginator = new LengthAwarePaginator(
            $cards->map(fn ($card) => ['type' => $card['type'], 'model' => null])->all(),
            $cards->count(),
            9,
            1,
            ['path' => $catalogUrl ?? route('offers.index')],
        );

        return new OfferCatalogViewModel(
            filter: $filter,
            listings: $paginator,
            cards: $cards,
            toursTotal: 1,
            tripsTotal: 1,
            campsTotal: 1,
            listingsTotal: $cards->count() ?: 3,
            speciesOptions: collect(),
            countries: collect([['slug' => 'germany', 'name' => 'Germany']]),
            methodOptions: collect(),
            waterOptions: collect(),
            tourDurationOptions: collect(),
            tripDurationOptions: collect(),
            accommodationTypeOptions: collect(),
            faq: collect(),
            mapMarkers: [],
            suggestedCards: collect(),
            catalogUrl: $catalogUrl,
            lockSpeciesScope: true,
            lockTourScope: $lockTourScope,
            lockVacationScope: $lockVacationScope,
        );
    }

    /**
     * Shape produced by the *CardPresenter::present() methods used for
     * homepage / destination / target-fish offer module rails.
     *
     * @return array<string, mixed>
     */
    private function moduleCard(string $type, string $title): array
    {
        return [
            'type' => $type,
            'id' => crc32($title),
            'title' => $title,
            'url' => '/offers/'.$type,
            'image' => '/images/placeholder_guide.jpg',
            'gallery_images' => ['/images/placeholder_guide.jpg'],
            'badge' => ucfirst($type),
            'badge_class' => $type,
            'location' => 'Test Location',
            'price_amount' => '€100',
            'price_unit' => 'person',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function card(string $type, string $title): array
    {
        return [
            'type' => $type,
            'id' => crc32($title),
            'title' => $title,
            'url' => '/offers/'.$type,
            'image' => '/images/placeholder_guide.jpg',
            'gallery_images' => ['/images/placeholder_guide.jpg'],
            'badge' => ucfirst($type === 'tour' ? 'Tour' : $type),
            'badge_class' => $type,
            'location' => 'Test Location',
            'listing_price_display' => '€100',
            'listing_price_prefix' => 'from',
            'listing_price_suffix' => '/ person',
            'listing_cta' => 'View',
            'cta' => 'View',
            'target_fish_tags' => ['Pike'],
            'target_fish_tags_extra' => 0,
            'listing_included' => ['Rod & reel'],
            'duration_label' => '8 Hours',
            'guests_label' => 'Max 4 Personen',
            'water_label' => 'Lake',
            'boat_label' => 'Boat',
            'rating' => 9.5,
            'review_count' => 2,
        ];
    }
}
