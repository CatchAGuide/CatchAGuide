<?php

namespace Tests\Unit\Seo;

use App\Models\CategoryEntity;
use App\Repositories\Guiding\GuidingCategoryAvailabilityRepository;
use App\Repositories\Vacation\VacationDestinationRepository;
use App\Services\Homepage\HomepageMixedOfferSelector;
use App\Services\Offers\OfferCatalogPageService;
use App\Services\Seo\CatalogInventoryGate;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Tests\TestCase;

class CatalogInventoryGateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    private function gate(
        ?VacationDestinationRepository $vacations = null,
        ?GuidingCategoryAvailabilityRepository $guidings = null,
        ?OfferCatalogPageService $catalog = null,
        ?HomepageMixedOfferSelector $mixed = null,
    ): CatalogInventoryGate {
        return new CatalogInventoryGate(
            $vacations ?? Mockery::mock(VacationDestinationRepository::class),
            $guidings ?? Mockery::mock(GuidingCategoryAvailabilityRepository::class),
            $catalog ?? Mockery::mock(OfferCatalogPageService::class),
            $mixed ?? Mockery::mock(HomepageMixedOfferSelector::class),
        );
    }

    private function entity(int $id, string $type): CategoryEntity
    {
        $entity = new CategoryEntity(['type' => $type, 'slug' => "{$type}-{$id}", 'countrycode' => 'XX']);
        $entity->id = $id;

        return $entity;
    }

    public function test_vacation_country_needs_one_listing_of_the_requested_pillar(): void
    {
        $vacations = Mockery::mock(VacationDestinationRepository::class);
        $vacations->shouldReceive('hubGridCountry')->with('norwegen')->andReturn(['camps' => 1, 'trips' => 0]);
        $vacations->shouldReceive('hubGridCountry')->with('malediven')->andReturn(['camps' => 0, 'trips' => 0]);
        $vacations->shouldReceive('hubGridCountry')->with('nirgendwo')->andReturn(null);
        $gate = $this->gate(vacations: $vacations);

        $this->assertTrue($gate->vacationCountryIndexable('norwegen'));
        $this->assertTrue($gate->vacationCountryIndexable('norwegen', 'camps'));
        $this->assertFalse($gate->vacationCountryIndexable('norwegen', 'trips'));
        // Known only through CMS copy — renders a placeholder, must not be indexable.
        $this->assertFalse($gate->vacationCountryIndexable('malediven'));
        $this->assertFalse($gate->vacationCountryIndexable('nirgendwo'));
    }

    public function test_tour_country_needs_one_tour_but_regions_and_cities_need_three(): void
    {
        $country = $this->entity(9001, 'country');
        $busyRegion = $this->entity(9002, 'region');
        $thinRegion = $this->entity(9003, 'region');
        $thinCity = $this->entity(9004, 'city');

        $guidings = Mockery::mock(GuidingCategoryAvailabilityRepository::class);
        $guidings->shouldReceive('hasGuidingsForCountry')->andReturn(true);
        $catalog = Mockery::mock(OfferCatalogPageService::class);
        $catalog->shouldReceive('countToursForDestination')->andReturnUsing(
            fn ($c, ?CategoryEntity $r = null, ?CategoryEntity $ci = null) => match (true) {
                $ci !== null => 2,
                $r?->id === $busyRegion->id => 3,
                default => 2,
            }
        );
        $gate = $this->gate(guidings: $guidings, catalog: $catalog);

        $this->assertTrue($gate->guidingDestinationIndexable($country));
        $this->assertTrue($gate->guidingDestinationIndexable($country, $busyRegion));
        $this->assertFalse($gate->guidingDestinationIndexable($country, $thinRegion));
        $this->assertFalse($gate->guidingDestinationIndexable($country, $busyRegion, $thinCity));
    }

    public function test_destination_counts_tours_camps_and_trips_with_geo_threshold(): void
    {
        $country = $this->entity(9101, 'country');
        $region = $this->entity(9102, 'region');

        $mixed = Mockery::mock(HomepageMixedOfferSelector::class);
        $mixed->shouldReceive('countForDestination')->andReturnUsing(
            fn ($c, ?CategoryEntity $r = null) => $r === null ? 1 : 2
        );
        $gate = $this->gate(mixed: $mixed);

        $this->assertTrue($gate->destinationIndexable($country));
        $this->assertFalse($gate->destinationIndexable($country, $region));
    }
}
