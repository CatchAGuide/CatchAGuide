<?php

namespace Tests\Unit\Presenters\Vacation;

use App\Models\Camp;
use App\Models\Trip;
use App\Presenters\Vacation\CampCardPresenter;
use App\Presenters\Vacation\TripCardPresenter;
use App\Services\Translation\ListingViewTranslationService;
use App\Services\Vacation\VacationReviewStats;
use Mockery;
use Tests\TestCase;

class CampTripCardRatingTest extends TestCase
{
    /**
     * @param  array{rating: float, count: int}|null  $listingStats
     */
    private function stats(?array $listingStats = null): VacationReviewStats
    {
        $stats = Mockery::mock(VacationReviewStats::class);
        $stats->shouldReceive('forListing')->andReturn($listingStats);

        return $stats;
    }

    public function test_camp_card_shows_the_camps_own_review_score(): void
    {
        $viewTranslation = Mockery::mock(ListingViewTranslationService::class);
        $viewTranslation->shouldReceive('applyToModel')->once();

        $camp = Mockery::mock(Camp::class)->makePartial();
        $camp->id = 11;
        $camp->title = 'Test Camp';
        $camp->slug = 'test-camp';
        $camp->thumbnail_path = null;
        $camp->location = 'Austria';
        $camp->target_fish = [];
        $camp->shouldReceive('getLowestAccommodationOrOfferPrice')->andReturn(120.0);
        $camp->shouldReceive('relationLoaded')->andReturn(true);
        $camp->setRelation('rentalBoats', collect());
        $camp->setRelation('guidings', collect());
        $camp->setRelation('facilities', collect());
        $camp->setRelation('accommodations', collect());
        $camp->setRelation('specialOffers', collect());

        $stats = Mockery::mock(VacationReviewStats::class);
        $stats->shouldReceive('forListing')->with('camp', 11)->andReturn(['rating' => 9.8, 'count' => 12]);

        $card = (new CampCardPresenter($viewTranslation, $stats))->presentListRow($camp);

        $this->assertSame(9.8, $card['rating']);
        $this->assertSame(12, $card['review_count']);
        $this->assertNull($card['trust']);
    }

    public function test_trip_card_shows_the_trips_own_review_score(): void
    {
        $viewTranslation = Mockery::mock(ListingViewTranslationService::class);
        $viewTranslation->shouldReceive('applyToModel')->once();

        $trip = Mockery::mock(Trip::class)->makePartial();
        $trip->id = 22;
        $trip->title = 'Test Trip';
        $trip->slug = 'test-trip';
        $trip->thumbnail_path = null;
        $trip->location = 'Norway';
        $trip->currency = 'EUR';
        $trip->price_per_person = 900;
        $trip->duration_days = 5;
        $trip->duration_nights = null;
        $trip->group_size_min = 2;
        $trip->group_size_max = 4;
        $trip->included = [];
        $trip->shouldReceive('getTargetSpeciesNames')->andReturn([]);
        $trip->shouldReceive('getFishingMethodNames')->andReturn([]);

        $stats = Mockery::mock(VacationReviewStats::class);
        $stats->shouldReceive('forListing')->with('trip', 22)->andReturn(['rating' => 10.0, 'count' => 7]);

        $card = (new TripCardPresenter($viewTranslation, $stats))->presentListRow($trip);

        $this->assertSame(10.0, $card['rating']);
        $this->assertSame(7, $card['review_count']);
    }

    public function test_camp_list_row_does_not_expose_borrowed_guide_ratings(): void
    {
        $viewTranslation = Mockery::mock(ListingViewTranslationService::class);
        $viewTranslation->shouldReceive('applyToModel')->once();

        $camp = Mockery::mock(Camp::class)->makePartial();
        $camp->id = 11;
        $camp->title = 'Test Camp';
        $camp->slug = 'test-camp';
        $camp->thumbnail_path = null;
        $camp->location = 'Austria';
        $camp->target_fish = [];
        $camp->shouldReceive('getLowestAccommodationOrOfferPrice')->andReturn(120.0);
        $camp->shouldReceive('relationLoaded')->andReturn(true);
        $camp->setRelation('rentalBoats', collect());
        $camp->setRelation('guidings', collect([(object) ['id' => 1]]));
        $camp->setRelation('facilities', collect());
        $camp->setRelation('accommodations', collect());
        $camp->setRelation('specialOffers', collect());

        $presenter = new CampCardPresenter($viewTranslation, $this->stats());
        $card = $presenter->presentListRow($camp);

        $this->assertNull($card['trust']);
        $this->assertNull($card['rating']);
        $this->assertSame(0, $card['review_count']);
        $this->assertNull($card['image_badge']);
    }

    public function test_trip_list_row_does_not_expose_borrowed_guide_ratings(): void
    {
        $viewTranslation = Mockery::mock(ListingViewTranslationService::class);
        $viewTranslation->shouldReceive('applyToModel')->once();

        $trip = Mockery::mock(Trip::class)->makePartial();
        $trip->id = 22;
        $trip->title = 'Test Trip';
        $trip->slug = 'test-trip';
        $trip->thumbnail_path = null;
        $trip->location = 'Norway';
        $trip->currency = 'EUR';
        $trip->price_per_person = 900;
        $trip->duration_days = 5;
        $trip->duration_nights = null;
        $trip->group_size_min = 2;
        $trip->group_size_max = 4;
        $trip->included = [];
        $trip->shouldReceive('getTargetSpeciesNames')->andReturn([]);
        $trip->shouldReceive('getFishingMethodNames')->andReturn([]);

        $presenter = new TripCardPresenter($viewTranslation, $this->stats());
        $card = $presenter->presentListRow($trip);

        $this->assertNull($card['trust']);
        $this->assertNull($card['rating']);
        $this->assertSame(0, $card['review_count']);
    }

    /**
     * Search/guest-count context must never land on this crawlable card link (see CLAUDE.md's
     * "SEO / catalog page conventions") — it's restored on the detail page from session
     * (ListingSearchStateService) instead. The $query argument is accepted for call-site
     * compatibility but must not affect the URL.
     */
    public function test_trip_list_row_url_never_carries_search_query(): void
    {
        $viewTranslation = Mockery::mock(ListingViewTranslationService::class);
        $viewTranslation->shouldReceive('applyToModel')->once();

        $trip = Mockery::mock(Trip::class)->makePartial();
        $trip->id = 22;
        $trip->title = 'Test Trip';
        $trip->slug = 'test-trip';
        $trip->thumbnail_path = null;
        $trip->location = 'Norway';
        $trip->currency = 'EUR';
        $trip->price_per_person = 900;
        $trip->duration_days = 5;
        $trip->duration_nights = null;
        $trip->group_size_min = 2;
        $trip->group_size_max = 4;
        $trip->included = [];
        $trip->shouldReceive('getTargetSpeciesNames')->andReturn([]);
        $trip->shouldReceive('getFishingMethodNames')->andReturn([]);

        $card = (new TripCardPresenter($viewTranslation, $this->stats()))->presentListRow($trip, 3, [
            'country' => 'spain',
            'num_guests' => 3,
        ]);

        $this->assertSame(route('vacations.trips.show', ['slug' => 'test-trip']), $card['url']);
        $this->assertNull(parse_url($card['url'], PHP_URL_QUERY));
    }

    public function test_camp_list_row_url_never_carries_search_query(): void
    {
        $viewTranslation = Mockery::mock(ListingViewTranslationService::class);
        $viewTranslation->shouldReceive('applyToModel')->once();

        $camp = Mockery::mock(Camp::class)->makePartial();
        $camp->id = 11;
        $camp->title = 'Test Camp';
        $camp->slug = 'test-camp';
        $camp->thumbnail_path = null;
        $camp->location = 'Austria';
        $camp->target_fish = [];
        $camp->shouldReceive('getLowestAccommodationOrOfferPrice')->andReturn(120.0);
        $camp->shouldReceive('relationLoaded')->andReturn(true);
        $camp->setRelation('rentalBoats', collect());
        $camp->setRelation('guidings', collect());
        $camp->setRelation('facilities', collect());
        $camp->setRelation('accommodations', collect());
        $camp->setRelation('specialOffers', collect());

        $card = (new CampCardPresenter($viewTranslation, $this->stats()))->presentListRow($camp, null, [
            'country' => 'spain',
            'num_guests' => 4,
        ]);

        $this->assertSame(route('vacations.camps.show', ['slug' => 'test-camp']), $card['url']);
        $this->assertNull(parse_url($card['url'], PHP_URL_QUERY));
    }
}
