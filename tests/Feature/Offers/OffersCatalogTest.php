<?php

namespace Tests\Feature\Offers;

use App\Domain\Offers\OfferListingFilter;
use App\Domain\Offers\ViewModels\OfferCatalogViewModel;
use App\Services\Offers\OfferCatalogPageService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\URL;
use Mockery;
use Tests\TestCase;

class OffersCatalogTest extends TestCase
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

    public function test_offers_index_renders_chips_list_map_and_faq(): void
    {
        $this->bindCatalog(fn () => $this->viewModel(
            type: 'all',
            cards: collect([
                $this->card('tour', 'Dawn Pike Tour'),
                $this->card('trip', 'Sweden Multi-Day'),
                $this->card('camp', 'Lodge Weekend'),
            ]),
            markers: [
                [
                    'id' => 1,
                    'lat' => 52.5,
                    'lng' => 13.4,
                    'pillar' => 'tour',
                    'variant' => 'tour',
                    'title' => 'Dawn Pike Tour',
                    'badge' => 'Tour',
                    'url' => '/guidings/1/dawn',
                ],
            ],
        ));

        $response = $this->get(route('offers.index'));

        $response->assertOk();
        $response->assertSee('cag-site-nav', false);
        $response->assertSee('cag-site-nav-shell', false);
        $response->assertSee('cag-site-nav--overlay', false);
        $response->assertDontSee('navbar-custom short-header long-header is-offers', false);
        $response->assertSee('offers-page-header', false);
        $response->assertSee('offers-page-header__hero', false);
        $response->assertSee('data-offers-header-search', false);
        $response->assertSee('data-offers-header-shell', false);
        $response->assertSee('offersCatalogSearchPlace', false);
        $response->assertSee('offers-page-header__segment--where', false);
        $response->assertSee('offers-page-header__segment--who', false);
        $response->assertSee('data-offers-persons-stepper', false);
        $response->assertSee(__('offers.search_where'), false);
        $response->assertSee(__('offers.nav_label'), false);
        $response->assertSee(__('offers.filter_all'), false);
        $this->assertStringContainsString(
            "'offersCatalogSearchPlace'",
            (string) file_get_contents(resource_path('js/maps/places-entry.js'))
        );
        $response->assertSee(__('offers.filter_tours'), false);
        $response->assertSee(__('offers.filter_vacations'), false);
        $response->assertDontSee('data-offers-vacation-subfilter', false);
        $response->assertSee('offers-catalog__toolbar', false);
        $response->assertSee('data-offers-type-filter', false);
        $response->assertSee('data-offers-list', false);
        $response->assertSee('data-offer-type="tour"', false);
        $response->assertSee('data-offer-type="trip"', false);
        $response->assertSee('data-offer-type="camp"', false);
        $response->assertSee('offersCatalogMapModal', false);
        $response->assertSee('data-bs-target="#offersCatalogMapModal"', false);
        $response->assertSee('data-offers-faq', false);
        $response->assertSee(__('offers.faq_title'), false);
        $response->assertSee(__('offers.sort_recommended'), false);
        $response->assertSee(__('offers.sort_newest'), false);
        $response->assertSee(__('offers.sort_nearest'), false);
        $response->assertSee(__('offers.sort_price_asc'), false);
        $response->assertSee(__('offers.sort_price_desc'), false);
        $response->assertSee('offersNearestLocationToast', false);
        $response->assertSee('applyNearestSort', false);
        $response->assertSee('navigator.geolocation.getCurrentPosition', false);
        $response->assertSee(__('offers.filter_show_all'), false);
        $response->assertSee('<select name="country"', false);
    }

    public function test_species_filter_renders_tagify_dropdown_control(): void
    {
        $this->bindCatalog(fn () => $this->viewModel(
            type: 'all',
            speciesOptions: collect([
                ['id' => 5, 'name' => 'Pike'],
                ['id' => 8, 'name' => 'Perch'],
            ]),
            speciesIds: [5],
        ));

        $response = $this->get(route('offers.index', ['species' => [5]]));

        $response->assertOk();
        $response->assertSee(__('offers.filter_species'), false);
        $response->assertSee('data-offers-multi-select', false);
        $response->assertSee('data-offers-multi-toggle', false);
        $response->assertSee('data-offers-multi-checkbox', false);
        $response->assertSee('name="species[]"', false);
        $response->assertSee('value="5"', false);
        $response->assertSee('Pike', false);
        $response->assertSee('offers-multi-select__tag', false);
        $response->assertSee(__('offers.filter_species_search'), false);
        $response->assertDontSee('<select name="species"', false);
    }

    public function test_tour_type_renders_method_water_and_duration_filters(): void
    {
        $this->bindCatalog(fn () => $this->viewModel(
            type: 'tour',
            methodOptions: collect([['id' => 1, 'name' => 'Fly Fishing']]),
            waterOptions: collect([['id' => 2, 'name' => 'Lake']]),
            tourDurationOptions: collect([['value' => 'full_day', 'label' => 'Full Day']]),
        ));

        $response = $this->get(route('offers.index', ['type' => 'tour']));

        $response->assertOk();
        $response->assertSee(__('offers.filter_method'), false);
        $response->assertSee(__('offers.filter_water_type'), false);
        $response->assertSee(__('offers.filter_duration'), false);
        $response->assertSee(__('offers.filter_method_placeholder'), false);
        $response->assertSee(__('offers.filter_water_placeholder'), false);
        $response->assertSee(__('offers.filter_duration_placeholder'), false);
        $response->assertSee('data-input-name="methods[]"', false);
        $response->assertSee('data-input-name="water[]"', false);
        $response->assertSee('data-input-name="duration_types[]"', false);
        $response->assertSee('data-offers-multi-select', false);
        $response->assertDontSee('<select name="methods"', false);
        $response->assertDontSee('<select name="water"', false);
        $response->assertDontSee('<select name="duration_types"', false);
        $response->assertDontSee('name="accommodation_type"', false);
        $response->assertDontSee('name="has_guiding"', false);
    }

    public function test_camp_subfilter_renders_accommodation_guiding_and_boat_filters(): void
    {
        $this->bindCatalog(fn () => $this->viewModel(
            type: 'vacation',
            vacation: 'camp',
            accommodationTypeOptions: collect([['id' => 3, 'name' => 'Cabin']]),
        ));

        $response = $this->get(route('offers.index', [
            'type' => 'vacation',
            'vacation' => 'camp',
        ]));

        $response->assertOk();
        $response->assertSee(__('offers.filter_accommodation_type'), false);
        $response->assertSee(__('offers.filter_guiding'), false);
        $response->assertSee(__('offers.filter_rental_boat'), false);
        $response->assertSee('name="accommodation_type"', false);
        $response->assertSee('name="has_guiding"', false);
        $response->assertSee('name="has_rental_boat"', false);
        $response->assertDontSee('name="methods"', false);
        $response->assertDontSee('name="duration"', false);
    }

    public function test_trip_subfilter_renders_duration_bucket_filter(): void
    {
        $this->bindCatalog(fn () => $this->viewModel(
            type: 'vacation',
            vacation: 'trip',
            tripDurationOptions: collect([['value' => '1-3', 'label' => '1–3 days']]),
        ));

        $response = $this->get(route('offers.index', [
            'type' => 'vacation',
            'vacation' => 'trip',
        ]));

        $response->assertOk();
        $response->assertSee(__('offers.filter_duration'), false);
        $response->assertSee('name="duration"', false);
        $response->assertDontSee('name="methods"', false);
        $response->assertDontSee('name="has_guiding"', false);
    }

    public function test_map_teaser_result_count_matches_listings_total(): void
    {
        $this->bindCatalog(fn () => $this->viewModel(
            type: 'vacation',
            vacation: 'trip',
            cards: collect([$this->card('trip', 'Mapped Trip')]),
            markers: [
                [
                    'id' => 10,
                    'lat' => 39.5,
                    'lng' => -0.4,
                    'pillar' => 'trip',
                    'variant' => 'trip',
                    'title' => 'Mapped Trip',
                    'badge' => 'Trip',
                    'url' => '/vacations/trips/mapped',
                ],
            ],
            toursTotal: 0,
            tripsTotal: 26,
            campsTotal: 0,
        ));

        $response = $this->get(route('offers.index', [
            'type' => 'vacation',
            'vacation' => 'trip',
        ]));

        $response->assertOk();
        $response->assertSee(__('offers.filter_trips').' (26)', false);
        $response->assertSee('26 '.translate('results'), false);
        $response->assertDontSee('1 '.translate('result'), false);
    }

    public function test_vacation_pillar_renders_subtle_vacation_type_toggle(): void
    {
        $this->bindCatalog(fn () => $this->viewModel(
            type: 'vacation',
            vacation: 'trip',
            speciesOptions: collect([
                ['id' => 5, 'name' => 'Pike'],
            ]),
            tripsTotal: 3,
            campsTotal: 2,
        ));

        $response = $this->get(route('offers.index', [
            'type' => 'vacation',
            'vacation' => 'trip',
        ]));

        $response->assertOk();
        $response->assertSee('data-offers-vacation-type', false);
        $response->assertSee('offers-filters__vacation-type-btns', false);
        $response->assertSee('offers-filters__vacation-type-btn', false);
        $response->assertSee(__('offers.filter_trips'), false);
        $response->assertSee(__('offers.filter_camps'), false);
        $response->assertDontSee('vacation-filters__pillar-btn--trips', false);
        $response->assertDontSee(__('offers.filter_vacation_all'), false);
        $response->assertDontSee('<select name="vacation"', false);
    }

    public function test_clear_filters_link_appears_when_sidebar_filters_active(): void
    {
        $this->bindCatalog(fn () => $this->viewModel(
            type: 'vacation',
            vacation: 'camp',
            country: 'spain',
            speciesOptions: collect([
                ['id' => 5, 'name' => 'Pike'],
            ]),
            speciesIds: [5],
        ));

        $response = $this->get(route('offers.index', [
            'type' => 'vacation',
            'vacation' => 'camp',
            'country' => 'spain',
            'species' => [5],
            'place' => 'Spain',
            'placeLat' => '40.4',
            'placeLng' => '-3.7',
        ]));

        $response->assertOk();
        $response->assertSee(__('offers.clear_filters'), false);
        $response->assertSee('data-offers-clear-filters', false);

        $clearHref = null;
        if (preg_match('/data-offers-clear-filters[^>]*href="([^"]+)"/', $response->getContent(), $m)
            || preg_match('/href="([^"]+)"[^>]*data-offers-clear-filters/', $response->getContent(), $m)) {
            $clearHref = html_entity_decode($m[1], ENT_QUOTES);
        }

        $this->assertNotNull($clearHref);
        $this->assertStringContainsString('type=vacation', $clearHref);
        $this->assertStringContainsString('place=Spain', $clearHref);
        $this->assertStringNotContainsString('vacation=camp', $clearHref);
        $this->assertStringNotContainsString('country=spain', $clearHref);
        $this->assertStringNotContainsString('species', $clearHref);
    }

    public function test_clear_filters_link_hidden_when_no_sidebar_filters(): void
    {
        $this->bindCatalog(fn () => $this->viewModel(
            type: 'vacation',
        ));

        $response = $this->get(route('offers.index', ['type' => 'vacation']));

        $response->assertOk();
        $response->assertDontSee('data-offers-clear-filters', false);
    }

    public function test_vacation_chip_extends_trips_camps_inline_and_shows_consolidated_cards(): void
    {
        $this->bindCatalog(fn () => $this->viewModel(
            type: 'vacation',
            cards: collect([
                $this->card('trip', 'Vacation Trip'),
                $this->card('camp', 'Vacation Camp'),
            ]),
            toursTotal: 3,
            tripsTotal: 2,
            campsTotal: 1,
        ));

        $response = $this->get(route('offers.index', ['type' => 'vacation']));

        $response->assertOk();
        $response->assertSee('data-offers-vacation-subfilter', false);
        $response->assertSee('offers-filters__vacation-extend', false);
        $response->assertDontSee(__('offers.filter_vacations_all'), false);
        $response->assertSee(__('offers.filter_trips'), false);
        $response->assertSee(__('offers.filter_camps'), false);
        $response->assertSee(__('offers.filter_vacations').' (3)', false);
        $response->assertSee('data-offer-type="trip"', false);
        $response->assertSee('data-offer-type="camp"', false);
        $response->assertDontSee('data-offer-type="tour"', false);
    }

    public function test_type_filter_limits_cards_to_requested_module(): void
    {
        $this->bindCatalog(function () {
            $type = request()->query('type', 'all');
            $vacation = request()->query('vacation', 'all');

            $cards = match (true) {
                $type === 'tour' => collect([$this->card('tour', 'Only Tour')]),
                $type === 'vacation' && $vacation === 'trip' => collect([$this->card('trip', 'Only Trip')]),
                $type === 'vacation' && $vacation === 'camp' => collect([$this->card('camp', 'Only Camp')]),
                $type === 'vacation' => collect([
                    $this->card('trip', 'Vacation Trip'),
                    $this->card('camp', 'Vacation Camp'),
                ]),
                // Legacy type=trip|camp still remapped by filter; mock mirrors service intent.
                $type === 'trip' => collect([$this->card('trip', 'Only Trip')]),
                $type === 'camp' => collect([$this->card('camp', 'Only Camp')]),
                default => collect([
                    $this->card('tour', 'Mixed Tour'),
                    $this->card('trip', 'Mixed Trip'),
                ]),
            };

            return $this->viewModel(
                type: $type,
                vacation: is_string($vacation) ? $vacation : 'all',
                cards: $cards,
            );
        });

        $tour = $this->get(route('offers.index', ['type' => 'tour']));
        $tour->assertOk();
        $tour->assertSee('data-offer-type="tour"', false);
        $tour->assertSee('Only Tour');
        $tour->assertDontSee('data-offer-type="trip"', false);
        $tour->assertDontSee('data-offer-type="camp"', false);

        $vacationTrips = $this->get(route('offers.index', [
            'type' => 'vacation',
            'vacation' => 'trip',
        ]));
        $vacationTrips->assertOk();
        $vacationTrips->assertSee('data-offers-vacation-subfilter', false);
        $vacationTrips->assertSee('data-offer-type="trip"', false);
        $vacationTrips->assertSee('Only Trip');
        $vacationTrips->assertDontSee('data-offer-type="tour"', false);
        $vacationTrips->assertDontSee('data-offer-type="camp"', false);

        $legacyTrip = $this->get(route('offers.index', ['type' => 'trip']));
        $legacyTrip->assertOk();
        $legacyTrip->assertSee('data-offers-vacation-subfilter', false);
        $legacyTrip->assertSee('data-offer-type="trip"', false);
        $legacyTrip->assertSee('Only Trip');
    }

    public function test_offers_index_shows_place_context_without_duplicate_title(): void
    {
        $this->bindCatalog(fn () => $this->viewModel(
            type: 'all',
            cards: collect([$this->card('tour', 'Dawn Pike Tour')]),
            place: 'Germany',
        ));

        $response = $this->get(route('offers.index', ['place' => 'Germany']));

        $response->assertOk();
        $response->assertSee('data-offers-place', false);
        $response->assertSee('Germany', false);
        $response->assertDontSee('class="offers-catalog__title', false);
    }

    public function test_offers_index_shows_merged_suggested_offers_when_empty(): void
    {
        $this->bindCatalog(fn () => $this->viewModel(
            type: 'all',
            cards: collect(),
            place: 'Nowhere',
            suggested: collect([
                $this->card('tour', 'Nearby Suggested Tour'),
                $this->card('trip', 'Nearby Suggested Trip'),
                $this->card('camp', 'Nearby Suggested Camp'),
            ]),
            toursTotal: 1,
            tripsTotal: 1,
            campsTotal: 1,
        ));

        $response = $this->get(route('offers.index', [
            'place' => 'Nowhere',
            'placeLat' => '50.1',
            'placeLng' => '8.6',
        ]));

        $response->assertOk();
        $response->assertSee('data-offers-empty', false);
        $response->assertSee('data-offers-suggested', false);
        $response->assertSee('Nearby Suggested Tour', false);
        $response->assertSee('Nearby Suggested Trip', false);
        $response->assertSee('Nearby Suggested Camp', false);
        $response->assertSee('data-offer-type="tour"', false);
        $response->assertSee('data-offer-type="trip"', false);
        $response->assertSee('data-offer-type="camp"', false);
        $response->assertSee(__('offers.suggested_near', ['place' => 'Nowhere']), false);
        $response->assertSee(__('offers.filter_all').' (3)', false);
        $response->assertSee(__('offers.filter_tours').' (1)', false);
        $response->assertSee(__('offers.filter_vacations').' (2)', false);
    }

    public function test_offers_index_shows_merged_suggested_offers_when_sparse_results(): void
    {
        $this->bindCatalog(fn () => $this->viewModel(
            type: 'all',
            cards: collect([$this->card('tour', 'Main Tour')]),
            place: 'Düsseldorf',
            suggested: collect([
                $this->card('tour', 'Nearby Extra Tour'),
                $this->card('trip', 'Nearby Extra Trip'),
                $this->card('camp', 'Nearby Extra Camp'),
            ]),
            toursTotal: 2,
            tripsTotal: 1,
            campsTotal: 1,
        ));

        $response = $this->get(route('offers.index', [
            'place' => 'Düsseldorf',
            'placeLat' => '51.2',
            'placeLng' => '6.8',
        ]));

        $response->assertOk();
        $response->assertSee('Main Tour', false);
        $response->assertSee('data-offers-suggested', false);
        $response->assertSee('Nearby Extra Tour', false);
        $response->assertSee('Nearby Extra Trip', false);
        $response->assertSee('Nearby Extra Camp', false);
        $response->assertSee(__('offers.filter_all').' (4)', false);
        $response->assertSee(__('offers.filter_tours').' (2)', false);
        $response->assertSee(__('offers.filter_vacations').' (2)', false);
    }

    public function test_type_toggle_urls_preserve_place_and_omit_vacation_on_primary(): void
    {
        $vm = $this->viewModel(
            type: 'vacation',
            vacation: 'trip',
            place: 'Spain',
            country: 'spain',
            numGuests: 2,
        );

        $urls = $vm->typeToggleUrls();
        $this->assertStringContainsString('place=Spain', $urls['all']);
        $this->assertStringContainsString('type=tour', $urls['tour']);
        $this->assertStringContainsString('type=vacation', $urls['vacation']);
        $this->assertStringNotContainsString('vacation=', $urls['vacation']);
        $this->assertStringNotContainsString('vacation=', $urls['all']);

        $vacationUrls = $vm->vacationToggleUrls();
        $this->assertStringContainsString('type=vacation', $vacationUrls['all']);
        $this->assertStringNotContainsString('vacation=', $vacationUrls['all']);
        $this->assertStringContainsString('vacation=trip', $vacationUrls['trip']);
        $this->assertStringContainsString('vacation=camp', $vacationUrls['camp']);
    }

    public function test_type_toggle_urls_use_custom_catalog_base_url(): void
    {
        $vm = $this->viewModel(
            type: 'all',
            place: 'Spain',
            country: 'spanien',
        );
        $vm = new OfferCatalogViewModel(
            filter: $vm->filter,
            listings: $vm->listings,
            cards: $vm->cards,
            toursTotal: $vm->toursTotal,
            tripsTotal: $vm->tripsTotal,
            campsTotal: $vm->campsTotal,
            listingsTotal: $vm->listingsTotal,
            speciesOptions: $vm->speciesOptions,
            countries: $vm->countries,
            methodOptions: $vm->methodOptions,
            waterOptions: $vm->waterOptions,
            tourDurationOptions: $vm->tourDurationOptions,
            tripDurationOptions: $vm->tripDurationOptions,
            accommodationTypeOptions: $vm->accommodationTypeOptions,
            faq: $vm->faq,
            mapMarkers: $vm->mapMarkers,
            suggestedCards: $vm->suggestedCards,
            catalogUrl: 'http://localhost/destination/spanien',
            lockDestinationScope: true,
        );

        $urls = $vm->typeToggleUrls();
        $this->assertStringStartsWith('http://localhost/destination/spanien', $urls['tour']);
        $this->assertStringContainsString('type=tour', $urls['tour']);
        $this->assertSame('http://localhost/destination/spanien', $vm->filterAction());
        $this->assertArrayHasKey('country', $vm->lockedScopeParams());
    }

    public function test_offers_cards_render_uniform_specs_tags_and_availability(): void
    {
        $this->bindCatalog(fn () => $this->viewModel(
            type: 'all',
            cards: collect([
                array_merge($this->card('tour', 'Tour Card'), [
                    'whats_included_title' => __('offers.included_heading'),
                    'listing_cta' => __('offers.cta_tour'),
                ]),
                array_merge($this->card('trip', 'Trip Card'), [
                    'water_label' => null,
                    'boat_label' => null,
                    'methods_label' => 'Spinning',
                    'rating' => null,
                    'review_count' => 0,
                    'verified' => true,
                    'listing_cta' => __('vacations.see_more'),
                ]),
                array_merge($this->card('camp', 'Camp Card'), [
                    'water_label' => null,
                    'boat_label' => null,
                    'rating' => null,
                    'review_count' => 0,
                    'verified' => true,
                    'listing_availability' => [
                        ['label' => 'Guiding', 'available' => true],
                        ['label' => 'Boat', 'available' => false],
                    ],
                    'listing_cta' => __('vacations.see_more'),
                ]),
            ]),
        ));

        $response = $this->get(route('offers.index'));

        $response->assertOk();
        $response->assertSee('data-offers-card-specs', false);
        $response->assertSee('clock-new.svg', false);
        $response->assertSee('user-new.svg', false);
        $response->assertSee('data-offers-card-tags', false);
        $response->assertSee('Pike', false);
        $response->assertSee('data-offers-card-availability', false);
        $response->assertSee(__('offers.included_heading'), false);
        $response->assertSee(__('offers.cta_tour'), false);
        $response->assertSee(__('vacations.see_more'), false);
        $response->assertSee(__('vacations.verified_short'), false);
        $response->assertSee('offers-gallery-modal', false);
        $response->assertSee('offers-gallery-modal__dock', false);
        $response->assertSee('offers-gallery-modal__title', false);
        $response->assertSee('data-offers-gallery-stage', false);
        $response->assertSee('data-offers-gallery-modal-image', false);
    }

    /**
     * @param  callable(): OfferCatalogViewModel  $factory
     */
    private function bindCatalog(callable $factory): void
    {
        $mock = Mockery::mock(OfferCatalogPageService::class);
        $mock->shouldReceive('build')->andReturnUsing($factory);
        $this->app->instance(OfferCatalogPageService::class, $mock);
    }

    private function viewModel(
        string $type = 'all',
        string $vacation = 'all',
        $cards = null,
        array $markers = [],
        ?string $place = null,
        ?string $country = null,
        ?int $numGuests = null,
        $suggested = null,
        ?int $toursTotal = null,
        ?int $tripsTotal = null,
        ?int $campsTotal = null,
        $methodOptions = null,
        $waterOptions = null,
        $tourDurationOptions = null,
        $tripDurationOptions = null,
        $accommodationTypeOptions = null,
        $speciesOptions = null,
        array $speciesIds = [],
    ): OfferCatalogViewModel {
        $cards = $cards ?? collect();
        $suggested = $suggested ?? collect();
        $filter = OfferListingFilter::fromRequest(array_filter([
            'type' => $type,
            'vacation' => $vacation !== 'all' ? $vacation : null,
            'place' => $place,
            'country' => $country,
            'num_guests' => $numGuests,
            'species' => $speciesIds !== [] ? $speciesIds : null,
        ], fn ($v) => $v !== null && $v !== ''));
        $paginator = new LengthAwarePaginator(
            $cards->map(fn ($card) => ['type' => $card['type'], 'model' => null])->all(),
            $cards->count(),
            9,
            1,
            ['path' => route('offers.index')],
        );

        // Defaults match a mixed catalog page when callers omit explicit totals.
        $toursTotal ??= 3;
        $tripsTotal ??= 2;
        $campsTotal ??= 1;
        $resolvedType = $filter->type;
        $resolvedVacation = $filter->vacation;
        $listingsTotal = match (true) {
            $resolvedType === 'tour' => $toursTotal,
            $resolvedType === 'vacation' && $resolvedVacation === 'trip' => $tripsTotal,
            $resolvedType === 'vacation' && $resolvedVacation === 'camp' => $campsTotal,
            $resolvedType === 'vacation' => $tripsTotal + $campsTotal,
            default => $toursTotal + $tripsTotal + $campsTotal,
        };

        return new OfferCatalogViewModel(
            filter: $filter,
            listings: $paginator,
            cards: $cards,
            toursTotal: $toursTotal,
            tripsTotal: $tripsTotal,
            campsTotal: $campsTotal,
            listingsTotal: $listingsTotal,
            speciesOptions: $speciesOptions ?? collect([['id' => 1, 'name' => 'Pike']]),
            countries: collect([['slug' => 'germany', 'name' => 'Germany']]),
            methodOptions: $methodOptions ?? collect(),
            waterOptions: $waterOptions ?? collect(),
            tourDurationOptions: $tourDurationOptions ?? collect(),
            tripDurationOptions: $tripDurationOptions ?? collect(),
            accommodationTypeOptions: $accommodationTypeOptions ?? collect(),
            faq: collect([
                (object) [
                    'question' => __('offers.faq_q1'),
                    'answer' => __('offers.faq_a1'),
                ],
            ]),
            mapMarkers: $markers,
            suggestedCards: $suggested,
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
