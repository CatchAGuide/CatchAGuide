<?php

namespace Tests\Feature\Destination;

use App\Domain\Offers\OfferListingFilter;
use App\Domain\Offers\ViewModels\OfferCatalogViewModel;
use App\Models\Country;
use App\Models\CountryTranslation;
use App\Services\Offers\OfferCatalogPageService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\URL;
use Mockery;
use Tests\TestCase;

class DestinationOffersCatalogTest extends TestCase
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

    private function createCountry(string $slugPrefix, array $filters = []): Country
    {
        $country = Country::query()->create([
            'name' => 'Spanien',
            'slug' => $slugPrefix.'-'.uniqid(),
            'countrycode' => 'ES',
            'filters' => $filters !== [] ? $filters : [
                'place' => 'Spain',
                'placeLat' => '40.4',
                'placeLng' => '-3.7',
                'country' => 'Spain',
            ],
        ]);

        CountryTranslation::query()->create([
            'country_id' => $country->id,
            'language' => app()->getLocale(),
            'title' => 'Fishing in Spain',
            'sub_title' => 'Discover Spanish waters',
            'introduction' => 'Intro text for Spain.',
            'content' => 'Body content for Spain.',
        ]);

        return $country->fresh(['translations']);
    }

    public function test_destination_country_renders_offers_catalog_chips_and_filters(): void
    {
        $country = $this->createCountry('spanien-offers-test');

        $this->bindDestinationCatalog(fn () => $this->viewModel(
            type: 'all',
            catalogUrl: route('destination.country', ['country' => $country->slug]),
            lockDestinationScope: true,
            country: 'spanien',
            place: 'Spain',
            cards: collect([
                $this->card('tour', 'Spain Tour'),
                $this->card('trip', 'Spain Trip'),
                $this->card('camp', 'Spain Camp'),
            ]),
        ));

        $response = $this->get(route('destination.country', ['country' => $country->slug]));

        $response->assertOk();
        $response->assertSee('data-offers-type-filter', false);
        $response->assertSee(__('offers.filter_all'), false);
        $response->assertSee(__('offers.filter_tours'), false);
        $response->assertSee(__('offers.filter_vacations'), false);
        $response->assertSee('data-offers-list', false);
        $response->assertSee('data-offer-type="tour"', false);
        $response->assertSee('data-offer-type="trip"', false);
        $response->assertSee('data-offer-type="camp"', false);
        $response->assertSee(__('offers.sort_recommended'), false);
        $response->assertDontSee(__('offers.all_countries'), false);
        $response->assertSee('name="country"', false);
        $response->assertSee('name="placeLat"', false);
        $response->assertSee('value="40.4"', false);
    }

    public function test_destination_type_toggle_stays_on_destination_url(): void
    {
        $base = 'http://localhost/destination/spanien';
        $vm = $this->viewModel(
            type: 'all',
            catalogUrl: $base,
            lockDestinationScope: true,
            country: 'spanien',
            place: 'Spain',
        );

        $urls = $vm->typeToggleUrls();
        $this->assertStringStartsWith($base, $urls['tour']);
        $this->assertStringContainsString('type=tour', $urls['tour']);
        $this->assertStringContainsString('country=spanien', $urls['tour']);
        $this->assertStringNotContainsString('/offers?', $urls['tour']);

        $vacationUrls = $vm->vacationToggleUrls();
        $this->assertStringStartsWith($base, $vacationUrls['trip']);
        $this->assertStringContainsString('type=vacation', $vacationUrls['trip']);
        $this->assertStringContainsString('vacation=trip', $vacationUrls['trip']);
    }

    public function test_destination_vacation_type_renders_subfilters(): void
    {
        $country = $this->createCountry('spanien-vac-test', [
            'place' => 'Spain',
            'placeLat' => '40.4',
            'placeLng' => '-3.7',
        ]);

        $this->bindDestinationCatalog(fn () => $this->viewModel(
            type: 'vacation',
            vacation: 'trip',
            catalogUrl: route('destination.country', ['country' => $country->slug]),
            lockDestinationScope: true,
            country: 'spanien',
            cards: collect([$this->card('trip', 'Only Trip')]),
        ));

        $response = $this->get(route('destination.country', [
            'country' => $country->slug,
            'type' => 'vacation',
            'vacation' => 'trip',
        ]));

        $response->assertOk();
        $response->assertSee('data-offers-vacation-subfilter', false);
        $response->assertSee('data-offer-type="trip"', false);
        $response->assertDontSee('data-offer-type="tour"', false);
    }

    /**
     * @param  callable(): OfferCatalogViewModel  $factory
     */
    private function bindDestinationCatalog(callable $factory): void
    {
        $mock = Mockery::mock(OfferCatalogPageService::class);
        $mock->shouldReceive('buildForDestination')->andReturnUsing($factory);
        $this->app->instance(OfferCatalogPageService::class, $mock);
    }

    private function viewModel(
        string $type = 'all',
        string $vacation = 'all',
        $cards = null,
        ?string $place = null,
        ?string $country = null,
        ?string $catalogUrl = null,
        bool $lockDestinationScope = false,
    ): OfferCatalogViewModel {
        $cards = $cards ?? collect();
        $filter = OfferListingFilter::fromRequest(array_filter([
            'type' => $type,
            'vacation' => $vacation !== 'all' ? $vacation : null,
            'place' => $place,
            'country' => $country,
            'placeLat' => $lockDestinationScope ? '40.4' : null,
            'placeLng' => $lockDestinationScope ? '-3.7' : null,
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
            speciesOptions: collect(['Pike']),
            countries: $lockDestinationScope ? collect() : collect([['slug' => 'germany', 'name' => 'Germany']]),
            methodOptions: collect(),
            waterOptions: collect(),
            tourDurationOptions: collect(),
            tripDurationOptions: collect(),
            accommodationTypeOptions: collect(),
            faq: collect(),
            mapMarkers: [],
            suggestedCards: collect(),
            catalogUrl: $catalogUrl,
            lockDestinationScope: $lockDestinationScope,
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
