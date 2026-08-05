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
        $this->assertNull($filter->species);
        $this->assertNull($filter->country);
        $this->assertTrue($filter->showsTours());
        $this->assertTrue($filter->showsTrips());
        $this->assertTrue($filter->showsCamps());
    }

    public function test_parses_type_and_shared_filters(): void
    {
        $filter = OfferListingFilter::fromRequest([
            'type' => 'tour',
            'species' => 'Pike',
            'country' => 'Germany',
            'sortby' => 'price-asc',
            'place' => 'Berlin',
            'placeLat' => '52.52',
            'placeLng' => '13.40',
        ]);

        $this->assertSame('tour', $filter->type);
        $this->assertSame('Pike', $filter->species);
        $this->assertSame('germany', $filter->country);
        $this->assertSame('price-asc', $filter->sortBy);
        $this->assertSame('Berlin', $filter->place);
        $this->assertSame(52.52, $filter->placeLat);
        $this->assertSame(13.40, $filter->placeLng);
        $this->assertTrue($filter->showsTours());
        $this->assertFalse($filter->showsTrips());
        $this->assertFalse($filter->showsCamps());
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
        $all = OfferListingFilter::fromRequest(['type' => 'all'])->toVacationFilter();

        $this->assertSame('trips', $trip->pillar);
        $this->assertSame('camps', $camp->pillar);
        $this->assertSame('all', $all->pillar);
    }
}
