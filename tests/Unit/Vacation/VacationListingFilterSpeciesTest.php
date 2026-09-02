<?php

namespace Tests\Unit\Vacation;

use App\Domain\Vacation\VacationListingFilter;
use Tests\TestCase;

class VacationListingFilterSpeciesTest extends TestCase
{
    public function test_normalizes_species_ids_and_legacy_names(): void
    {
        [$ids, $names] = VacationListingFilter::normalizeSpecies(['12', 'Pike', '', null, '7']);

        $this->assertSame([12, 7], $ids);
        $this->assertSame(['Pike'], $names);
    }

    public function test_from_request_parses_species_checkboxes(): void
    {
        $filter = VacationListingFilter::fromRequest([
            'pillar' => 'trips',
            'species' => ['3', '9'],
        ]);

        $this->assertSame([3, 9], $filter->speciesIds);
        $this->assertSame([], $filter->speciesNames);
        $this->assertNull($filter->species());
    }

    public function test_from_request_keeps_legacy_species_name(): void
    {
        $filter = VacationListingFilter::fromRequest([
            'species' => 'Hecht',
        ]);

        $this->assertSame([], $filter->speciesIds);
        $this->assertSame(['Hecht'], $filter->speciesNames);
        $this->assertSame('Hecht', $filter->species());
    }

    public function test_from_request_parses_trip_duration_buckets(): void
    {
        $filter = VacationListingFilter::fromRequest([
            'pillar' => 'trips',
            'duration' => '4-7',
        ]);

        $this->assertSame('4-7', $filter->tripDuration);
        $this->assertTrue($filter->showsTripDurationFilter());

        $invalid = VacationListingFilter::fromRequest([
            'pillar' => 'trips',
            'duration' => 'two-weeks',
        ]);
        $this->assertNull($invalid->tripDuration);

        $empty = VacationListingFilter::fromRequest(['pillar' => 'trips']);
        $this->assertNull($empty->tripDuration);
        $this->assertFalse(VacationListingFilter::fromRequest(['pillar' => 'camps'])->showsTripDurationFilter());
        $this->assertFalse(VacationListingFilter::fromRequest([])->showsTripDurationFilter());
    }

    public function test_from_request_parses_camp_facets_only_for_camps_pillar(): void
    {
        $camp = VacationListingFilter::fromRequest([
            'pillar' => 'camps',
            'accommodation_type' => '3',
            'has_guiding' => '1',
            'has_rental_boat' => '0',
        ]);

        $this->assertSame(3, $camp->accommodationTypeId);
        $this->assertTrue($camp->hasGuiding);
        $this->assertFalse($camp->hasRentalBoat);
        $this->assertTrue($camp->showsCampFacets());
        $this->assertSame([
            'accommodation_type' => 3,
            'has_guiding' => '1',
            'has_rental_boat' => '0',
        ], $camp->campFacetQueryParams());

        $ignored = VacationListingFilter::fromRequest([
            'pillar' => 'trips',
            'accommodation_type' => '3',
            'has_guiding' => '1',
            'has_rental_boat' => '0',
        ]);

        $this->assertNull($ignored->accommodationTypeId);
        $this->assertNull($ignored->hasGuiding);
        $this->assertNull($ignored->hasRentalBoat);
        $this->assertFalse($ignored->showsCampFacets());
        $this->assertSame([], $ignored->campFacetQueryParams());
    }

    public function test_from_request_parses_num_guests(): void
    {
        $filter = VacationListingFilter::fromRequest(['num_guests' => '4']);
        $this->assertSame(4, $filter->numGuests);

        $legacyKey = VacationListingFilter::fromRequest(['num_persons' => '2']);
        $this->assertSame(2, $legacyKey->numGuests);
    }

    public function test_from_request_num_guests_defaults_to_null_and_ignores_invalid_values(): void
    {
        $this->assertNull(VacationListingFilter::fromRequest([])->numGuests);
        $this->assertNull(VacationListingFilter::fromRequest(['num_guests' => ''])->numGuests);
        $this->assertNull(VacationListingFilter::fromRequest(['num_guests' => 'abc'])->numGuests);
        $this->assertNull(VacationListingFilter::fromRequest(['num_guests' => '0'])->numGuests);
        $this->assertNull(VacationListingFilter::fromRequest(['num_guests' => '-3'])->numGuests);
    }

    public function test_from_request_clamps_num_guests_to_max(): void
    {
        $filter = VacationListingFilter::fromRequest(['num_guests' => '999']);

        $this->assertSame(VacationListingFilter::MAX_GUESTS, $filter->numGuests);
    }
}
