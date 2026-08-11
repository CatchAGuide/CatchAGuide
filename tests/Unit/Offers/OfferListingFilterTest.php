<?php

namespace Tests\Unit\Offers;

use App\Domain\Offers\OfferListingFilter;
use Tests\TestCase;

class OfferListingFilterTest extends TestCase
{
    public function test_defaults_to_all_type(): void
    {
        $filter = OfferListingFilter::fromRequest([]);

        $this->assertSame('all', $filter->type);
        $this->assertSame('all', $filter->vacation);
        $this->assertSame([], $filter->speciesIds);
        $this->assertSame([], $filter->speciesNames);
        $this->assertNull($filter->country);
        $this->assertTrue($filter->showsTours());
        $this->assertTrue($filter->showsTrips());
        $this->assertTrue($filter->showsCamps());
        $this->assertFalse($filter->isVacation());
    }

    public function test_parses_type_and_shared_filters(): void
    {
        $filter = OfferListingFilter::fromRequest([
            'type' => 'tour',
            'species' => ['12', '7'],
            'country' => 'Germany',
            'sortby' => 'price-asc',
            'place' => 'Berlin',
            'placeLat' => '52.52',
            'placeLng' => '13.40',
            'num_guests' => '3',
            'place_types' => '["country"]',
            'bounds_ne_lat' => '55.1',
            'bounds_ne_lng' => '15.0',
            'bounds_sw_lat' => '47.2',
            'bounds_sw_lng' => '5.8',
            'country_short' => 'DE',
        ]);

        $this->assertSame('tour', $filter->type);
        $this->assertSame('all', $filter->vacation);
        $this->assertSame([12, 7], $filter->speciesIds);
        $this->assertSame([], $filter->speciesNames);
        $this->assertTrue($filter->hasSpeciesFilter());
        $this->assertSame('germany', $filter->country);
        $this->assertSame('price-asc', $filter->sortBy);
        $this->assertSame('Berlin', $filter->place);
        $this->assertSame(52.52, $filter->placeLat);
        $this->assertSame(13.40, $filter->placeLng);
        $this->assertSame(3, $filter->numGuests);
        $this->assertSame(['country'], $filter->placeTypes);
        $this->assertSame(55.1, $filter->boundsNeLat);
        $this->assertSame('DE', $filter->countryShort);
        $this->assertTrue($filter->showsTours());
        $this->assertFalse($filter->showsTrips());
        $this->assertFalse($filter->showsCamps());
    }

    public function test_vacation_type_defaults_to_all_subfilter(): void
    {
        $filter = OfferListingFilter::fromRequest(['type' => 'vacation']);

        $this->assertSame('vacation', $filter->type);
        $this->assertSame('all', $filter->vacation);
        $this->assertTrue($filter->isVacation());
        $this->assertFalse($filter->showsTours());
        $this->assertTrue($filter->showsTrips());
        $this->assertTrue($filter->showsCamps());
    }

    public function test_vacation_subfilters_limit_trips_or_camps(): void
    {
        $trips = OfferListingFilter::fromRequest([
            'type' => 'vacation',
            'vacation' => 'trip',
        ]);
        $camps = OfferListingFilter::fromRequest([
            'type' => 'vacation',
            'vacation' => 'camp',
        ]);

        $this->assertTrue($trips->showsTrips());
        $this->assertFalse($trips->showsCamps());
        $this->assertFalse($trips->showsTours());

        $this->assertFalse($camps->showsTrips());
        $this->assertTrue($camps->showsCamps());
        $this->assertFalse($camps->showsTours());
    }

    public function test_legacy_type_trip_and_camp_remap_to_vacation_subfilter(): void
    {
        $trip = OfferListingFilter::fromRequest(['type' => 'trip']);
        $camp = OfferListingFilter::fromRequest(['type' => 'camp']);

        $this->assertSame('vacation', $trip->type);
        $this->assertSame('trip', $trip->vacation);
        $this->assertTrue($trip->showsTrips());
        $this->assertFalse($trip->showsCamps());

        $this->assertSame('vacation', $camp->type);
        $this->assertSame('camp', $camp->vacation);
        $this->assertFalse($camp->showsTrips());
        $this->assertTrue($camp->showsCamps());
    }

    public function test_vacation_subfilter_ignored_when_primary_is_not_vacation(): void
    {
        $filter = OfferListingFilter::fromRequest([
            'type' => 'tour',
            'vacation' => 'trip',
        ]);

        $this->assertSame('tour', $filter->type);
        $this->assertSame('all', $filter->vacation);
    }

    public function test_num_guests_defaults_to_two_and_is_clamped(): void
    {
        $this->assertSame(2, OfferListingFilter::fromRequest([])->numGuests);
        $this->assertSame(2, OfferListingFilter::fromRequest(['num_guests' => '0'])->numGuests);
        $this->assertSame(20, OfferListingFilter::fromRequest(['num_guests' => '99'])->numGuests);
        $this->assertSame(4, OfferListingFilter::fromRequest(['num_persons' => '4'])->numGuests);
    }

    public function test_invalid_type_falls_back_to_all(): void
    {
        $filter = OfferListingFilter::fromRequest(['type' => 'boats']);

        $this->assertSame('all', $filter->type);
    }

    public function test_to_vacation_filter_maps_type_to_pillar(): void
    {
        $trip = OfferListingFilter::fromRequest(['type' => 'trip'])->toVacationFilter();
        $camp = OfferListingFilter::fromRequest(['type' => 'camp'])->toVacationFilter();
        $vacationAll = OfferListingFilter::fromRequest(['type' => 'vacation'])->toVacationFilter();
        $all = OfferListingFilter::fromRequest(['type' => 'all'])->toVacationFilter();

        $this->assertSame('trips', $trip->pillar);
        $this->assertSame('camps', $camp->pillar);
        $this->assertSame('all', $vacationAll->pillar);
        $this->assertSame('all', $all->pillar);
    }

    public function test_parses_tour_facets_only_for_tour_type(): void
    {
        $tour = OfferListingFilter::fromRequest([
            'type' => 'tour',
            'methods' => ['12', '4'],
            'water' => ['7'],
            'duration_types' => ['full_day', 'half_day', 'invalid'],
        ]);

        $this->assertSame([12, 4], $tour->methodIds);
        $this->assertSame([7], $tour->waterIds);
        $this->assertSame(['full_day', 'half_day'], $tour->durationTypes);
        $this->assertTrue($tour->showsTourFacets());

        $legacyScalar = OfferListingFilter::fromRequest([
            'type' => 'tour',
            'methods' => '12',
            'water' => '7',
            'duration_types' => 'full_day',
        ]);
        $this->assertSame([12], $legacyScalar->methodIds);
        $this->assertSame([7], $legacyScalar->waterIds);
        $this->assertSame(['full_day'], $legacyScalar->durationTypes);

        $ignored = OfferListingFilter::fromRequest([
            'type' => 'vacation',
            'vacation' => 'trip',
            'methods' => '12',
            'water' => '7',
            'duration_types' => 'full_day',
        ]);

        $this->assertSame([], $ignored->methodIds);
        $this->assertSame([], $ignored->waterIds);
        $this->assertSame([], $ignored->durationTypes);
        $this->assertFalse($ignored->showsTourFacets());
    }

    public function test_parses_camp_facets_only_for_camp_subfilter(): void
    {
        $camp = OfferListingFilter::fromRequest([
            'type' => 'vacation',
            'vacation' => 'camp',
            'accommodation_type' => '3',
            'has_guiding' => '1',
            'has_rental_boat' => '0',
        ]);

        $this->assertSame(3, $camp->accommodationTypeId);
        $this->assertTrue($camp->hasGuiding);
        $this->assertFalse($camp->hasRentalBoat);
        $this->assertTrue($camp->showsCampFacets());

        $ignored = OfferListingFilter::fromRequest([
            'type' => 'tour',
            'accommodation_type' => '3',
            'has_guiding' => '1',
            'has_rental_boat' => '0',
        ]);

        $this->assertNull($ignored->accommodationTypeId);
        $this->assertNull($ignored->hasGuiding);
        $this->assertNull($ignored->hasRentalBoat);
    }

    public function test_parses_trip_duration_bucket_only_for_trip_subfilter(): void
    {
        $trip = OfferListingFilter::fromRequest([
            'type' => 'vacation',
            'vacation' => 'trip',
            'duration' => '4-7',
        ]);

        $this->assertSame('4-7', $trip->tripDuration);
        $this->assertTrue($trip->showsTripFacets());

        $invalid = OfferListingFilter::fromRequest([
            'type' => 'vacation',
            'vacation' => 'trip',
            'duration' => 'two-weeks',
        ]);
        $this->assertNull($invalid->tripDuration);

        $ignored = OfferListingFilter::fromRequest([
            'type' => 'vacation',
            'duration' => '4-7',
        ]);
        $this->assertNull($ignored->tripDuration);
    }

    public function test_parses_sort_options_and_user_location(): void
    {
        $nearest = OfferListingFilter::fromRequest([
            'sortby' => 'nearest',
            'user_lat' => '52.52',
            'user_lng' => '13.40',
        ]);
        $this->assertSame('nearest', $nearest->sortBy);
        $this->assertSame('nearest', $nearest->effectiveSortBy());
        $this->assertSame(['lat' => 52.52, 'lng' => 13.40], $nearest->nearestOrigin());

        $recommended = OfferListingFilter::fromRequest([]);
        $this->assertNull($recommended->sortBy);
        $this->assertSame('recommended', $recommended->effectiveSortBy());

        $invalid = OfferListingFilter::fromRequest(['sortby' => 'popularity']);
        $this->assertNull($invalid->sortBy);
        $this->assertSame('recommended', $invalid->effectiveSortBy());

        $fromPlace = OfferListingFilter::fromRequest([
            'sortby' => 'nearest',
            'placeLat' => '40.4',
            'placeLng' => '-3.7',
        ]);
        $this->assertSame(['lat' => 40.4, 'lng' => -3.7], $fromPlace->nearestOrigin());
    }

    public function test_parses_legacy_species_name_and_mixed_checkbox_values(): void
    {
        $legacy = OfferListingFilter::fromRequest(['species' => 'Pike']);
        $this->assertSame([], $legacy->speciesIds);
        $this->assertSame(['Pike'], $legacy->speciesNames);
        $this->assertTrue($legacy->hasSpeciesFilter());

        $mixed = OfferListingFilter::fromRequest(['species' => ['3', 'Hecht', '']]);
        $this->assertSame([3], $mixed->speciesIds);
        $this->assertSame(['Hecht'], $mixed->speciesNames);
    }
}
