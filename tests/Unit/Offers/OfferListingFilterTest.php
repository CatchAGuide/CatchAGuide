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
        $this->assertFalse($filter->hasPlaceSearch());
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
        $this->assertTrue($filter->hasPlaceSearch());
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

    public function test_num_guests_defaults_to_one_and_is_clamped(): void
    {
        $this->assertSame(1, OfferListingFilter::fromRequest([])->numGuests);
        $this->assertSame(1, OfferListingFilter::fromRequest(['num_guests' => '0'])->numGuests);
        $this->assertSame(20, OfferListingFilter::fromRequest(['num_guests' => '99'])->numGuests);
        $this->assertSame(4, OfferListingFilter::fromRequest(['num_persons' => '4'])->numGuests);
    }

    public function test_header_carry_params_keep_filters_and_drop_geo_and_guests(): void
    {
        $carry = OfferListingFilter::headerCarryParams([
            'type' => 'tour',
            'species' => ['8', '5'],
            'sortby' => 'newest',
            'num_guests' => '4',
            'country' => 'lettland',
            'place' => 'Latvia',
            'placeLat' => '56.8',
            'page' => '2',
            'methods' => ['3'],
        ], [
            'species' => [8],
            'country' => 'lettland',
        ]);

        $this->assertSame([8], $carry['species']);
        $this->assertSame('tour', $carry['type']);
        $this->assertSame('newest', $carry['sortby']);
        $this->assertSame(['3'], $carry['methods']);
        $this->assertArrayNotHasKey('num_guests', $carry);
        $this->assertArrayNotHasKey('country', $carry);
        $this->assertArrayNotHasKey('place', $carry);
        $this->assertArrayNotHasKey('placeLat', $carry);
        $this->assertArrayNotHasKey('page', $carry);
    }

    public function test_has_place_search_requires_place_text_and_coordinates(): void
    {
        $this->assertFalse(OfferListingFilter::fromRequest(['place' => 'Berlin'])->hasPlaceSearch());
        $this->assertFalse(OfferListingFilter::fromRequest([
            'placeLat' => '51.2',
            'placeLng' => '6.8',
        ])->hasPlaceSearch());
        $this->assertTrue(OfferListingFilter::fromRequest([
            'place' => 'Düsseldorf',
            'placeLat' => '51.2',
            'placeLng' => '6.8',
        ])->hasPlaceSearch());
    }

    public function test_product_page_query_keeps_place_geo_and_guests(): void
    {
        $filter = OfferListingFilter::fromRequest([
            'place' => 'Düsseldorf, Deutschland',
            'placeLat' => '51.2277',
            'placeLng' => '6.7735',
            'city' => 'Düsseldorf',
            'country' => 'germany',
            'region' => 'Nordrhein-Westfalen',
            'num_guests' => '3',
        ]);

        $query = $filter->productPageQuery();

        $this->assertSame('Düsseldorf, Deutschland', $query['place']);
        $this->assertSame(51.2277, $query['placeLat']);
        $this->assertSame(6.7735, $query['placeLng']);
        $this->assertSame('Düsseldorf', $query['city']);
        $this->assertSame('germany', $query['country']);
        $this->assertSame(3, $query['num_guests']);
    }

    public function test_product_page_query_from_input_omits_guests_unless_present(): void
    {
        $withoutGuests = OfferListingFilter::productPageQueryFromInput([
            'place' => 'Berlin',
            'placeLat' => '52.52',
            'placeLng' => '13.40',
        ]);

        $this->assertSame('Berlin', $withoutGuests['place']);
        $this->assertArrayNotHasKey('num_guests', $withoutGuests);

        $withGuests = OfferListingFilter::productPageQueryFromInput([
            'num_guests' => '3',
        ]);

        $this->assertSame(3, $withGuests['num_guests']);
        $this->assertArrayNotHasKey('place', $withGuests);
    }

    public function test_empty_place_drops_orphan_geo_params(): void
    {
        $filter = OfferListingFilter::fromRequest([
            'place' => '',
            'placeLat' => '40.4',
            'placeLng' => '-3.7',
            'city' => 'Madrid',
            'country' => 'spain',
            'region' => 'Community of Madrid',
            'country_short' => 'ES',
            'place_types' => '["country"]',
            'bounds_ne_lat' => '43.8',
            'bounds_ne_lng' => '4.3',
            'bounds_sw_lat' => '36.0',
            'bounds_sw_lng' => '-9.3',
            'species' => ['8'],
        ]);

        $this->assertNull($filter->place);
        $this->assertNull($filter->placeLat);
        $this->assertNull($filter->placeLng);
        $this->assertNull($filter->city);
        $this->assertNull($filter->country);
        $this->assertNull($filter->region);
        $this->assertNull($filter->countryShort);
        $this->assertSame([], $filter->placeTypes);
        $this->assertNull($filter->boundsNeLat);
        $this->assertFalse($filter->hasPlaceSearch());
        $this->assertSame([8], $filter->speciesIds);
    }

    public function test_country_filter_kept_when_place_is_absent(): void
    {
        $filter = OfferListingFilter::fromRequest(['country' => 'spain']);

        $this->assertSame('spain', $filter->country);
        $this->assertFalse($filter->hasPlaceSearch());
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

    public function test_to_vacation_filter_keeps_trip_duration(): void
    {
        $filter = OfferListingFilter::fromRequest([
            'type' => 'vacation',
            'vacation' => 'trip',
            'duration' => '8+',
        ])->toVacationFilter();

        $this->assertSame('trips', $filter->pillar);
        $this->assertSame('8+', $filter->tripDuration);
    }

    public function test_to_vacation_filter_keeps_camp_facets(): void
    {
        $filter = OfferListingFilter::fromRequest([
            'type' => 'vacation',
            'vacation' => 'camp',
            'accommodation_type' => '3',
            'has_guiding' => '1',
            'has_rental_boat' => '0',
        ])->toVacationFilter();

        $this->assertSame('camps', $filter->pillar);
        $this->assertSame(3, $filter->accommodationTypeId);
        $this->assertTrue($filter->hasGuiding);
        $this->assertFalse($filter->hasRentalBoat);
    }

    public function test_to_vacation_filter_keeps_num_guests(): void
    {
        $filter = OfferListingFilter::fromRequest([
            'type' => 'vacation',
            'num_guests' => '4',
        ])->toVacationFilter();

        $this->assertSame(4, $filter->numGuests);
        $this->assertSame(['num_guests' => 4], $filter->productPageQuery());
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
            'place' => 'Madrid',
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
