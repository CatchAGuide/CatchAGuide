<?php

namespace Tests\Unit\Presenters\Vacation;

use App\Models\Camp;
use App\Models\Trip;
use App\Presenters\Vacation\CampCardPresenter;
use App\Presenters\Vacation\TripCardPresenter;
use App\Services\Translation\ListingViewTranslationService;
use Mockery;
use Tests\TestCase;

class CampTripCardRatingTest extends TestCase
{
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

        $presenter = new CampCardPresenter($viewTranslation);
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

        $presenter = new TripCardPresenter($viewTranslation);
        $card = $presenter->presentListRow($trip);

        $this->assertNull($card['trust']);
        $this->assertNull($card['rating']);
        $this->assertSame(0, $card['review_count']);
    }

    public function test_trip_list_row_url_keeps_search_country_and_guests(): void
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

        $card = (new TripCardPresenter($viewTranslation))->presentListRow($trip, 3, [
            'country' => 'spain',
            'num_guests' => 3,
        ]);

        $query = [];
        parse_str((string) parse_url($card['url'], PHP_URL_QUERY), $query);

        $this->assertSame('test-trip', basename((string) parse_url($card['url'], PHP_URL_PATH)));
        $this->assertSame('3', $query['num_guests']);
        $this->assertSame('spain', $query['country']);
    }

    public function test_camp_list_row_url_keeps_search_country_and_guests(): void
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

        $card = (new CampCardPresenter($viewTranslation))->presentListRow($camp, null, [
            'country' => 'spain',
            'num_guests' => 4,
        ]);

        $query = [];
        parse_str((string) parse_url($card['url'], PHP_URL_QUERY), $query);

        $this->assertSame('test-camp', basename((string) parse_url($card['url'], PHP_URL_PATH)));
        $this->assertSame('4', $query['num_guests']);
        $this->assertSame('spain', $query['country']);
    }
}
