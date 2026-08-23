<?php

namespace Tests\Unit\Offers;

use App\Domain\Offers\DestinationOfferGeoScope;
use App\Models\CategoryEntity;
use App\Models\Guiding;
use Tests\TestCase;

class DestinationOfferGeoScopeTest extends TestCase
{
    public function test_country_scope_matches_slug_and_iso_columns(): void
    {
        $country = new CategoryEntity([
            'type' => 'country',
            'name' => 'Spain',
            'slug' => 'spanien',
            'countrycode' => 'ES',
        ]);

        $query = Guiding::query();
        DestinationOfferGeoScope::apply($query, $country, includeCountryIso: true);

        $sql = $query->toSql();
        $bindings = $query->getBindings();

        $this->assertStringContainsString('LOWER(country)', $sql);
        $this->assertStringContainsString('LOWER(country_iso)', $sql);
        $this->assertContains('spanien', $bindings);
        $this->assertContains('es', $bindings);
    }

    public function test_region_and_city_add_place_column_constraints(): void
    {
        $country = new CategoryEntity([
            'type' => 'country',
            'name' => 'Spain',
            'slug' => 'spanien',
            'countrycode' => 'ES',
        ]);
        $region = new CategoryEntity([
            'type' => 'region',
            'name' => 'Catalonia',
            'slug' => 'catalonia',
            'filters' => ['place' => 'Catalonia'],
        ]);
        $city = new CategoryEntity([
            'type' => 'city',
            'name' => 'Barcelona',
            'slug' => 'barcelona',
            'filters' => ['city' => 'Barcelona'],
        ]);

        $regionQuery = Guiding::query();
        DestinationOfferGeoScope::apply($regionQuery, $country, $region);
        $this->assertStringContainsString('LOWER(region)', $regionQuery->toSql());
        $this->assertContains('catalonia', $regionQuery->getBindings());

        $cityQuery = Guiding::query();
        DestinationOfferGeoScope::apply($cityQuery, $country, $region, $city);
        $this->assertStringContainsString('LOWER(city)', $cityQuery->toSql());
        $this->assertStringNotContainsString('LOWER(region)', $cityQuery->toSql());
        $this->assertContains('barcelona', $cityQuery->getBindings());
    }
}
