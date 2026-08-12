<?php

namespace Tests\Feature\Category;

use App\Domain\Offers\OfferListingFilter;
use App\Domain\Offers\ViewModels\OfferCatalogViewModel;
use App\Models\CategoryPage;
use App\Models\Language;
use App\Models\Target;
use App\Services\Offers\OfferCatalogPageService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\URL;
use Mockery;
use Tests\TestCase;

class TargetFishOffersCatalogTest extends TestCase
{
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

    private function createTargetFishPage(string $slugPrefix): CategoryPage
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
            'source_id' => $page->id,
            'type' => 'category_page',
            'scope' => 'global',
            'language' => app()->getLocale(),
            'title' => 'Pike fishing',
            'sub_title' => 'Guided pike tours and vacations',
            'introduction' => 'Intro text for pike category.',
            'content' => 'Body content for pike.',
            'faq_title' => '',
        ]);

        return $page->fresh();
    }

    public function test_target_fish_category_renders_offers_catalog_with_vacations(): void
    {
        $page = $this->createTargetFishPage('pike-offers-test');

        $this->bindTargetFishCatalog(fn () => $this->viewModel(
            type: 'all',
            catalogUrl: route('category.targets', ['type' => 'targets', 'slug' => $page->slug]),
            speciesIds: [(int) $page->source_id],
            cards: collect([
                $this->card('tour', 'Pike Tour'),
                $this->card('trip', 'Pike Trip'),
                $this->card('camp', 'Pike Camp'),
            ]),
        ));

        $response = $this->get(route('category.targets', [
            'type' => 'targets',
            'slug' => $page->slug,
        ]));

        $response->assertOk();
        $response->assertSee('data-offers-type-filter', false);
        $response->assertSee(__('offers.filter_all'), false);
        $response->assertSee(__('offers.filter_tours'), false);
        $response->assertSee(__('offers.filter_vacations'), false);
        $response->assertSee('data-offers-list', false);
        $response->assertSee('data-offer-type="tour"', false);
        $response->assertSee('data-offer-type="trip"', false);
        $response->assertSee('data-offer-type="camp"', false);
        $response->assertSee('name="species[]"', false);
        $response->assertSee('value="'.$page->source_id.'"', false);
        $response->assertDontSee('offers-filters__species', false);
        $response->assertDontSee('id="guidings-list"', false);
    }

    public function test_target_fish_type_toggle_stays_on_category_url_and_keeps_species(): void
    {
        $base = 'http://localhost/category-page/targets/pike';
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
    ): OfferCatalogViewModel {
        $cards = $cards ?? collect();
        $filter = OfferListingFilter::fromRequest(array_filter([
            'type' => $type,
            'vacation' => $vacation !== 'all' ? $vacation : null,
            'species' => $speciesIds,
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
        );
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
