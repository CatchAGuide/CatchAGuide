<?php

namespace Tests\Unit\Offers;

use App\Domain\Offers\DestinationOfferScope;
use App\Models\City;
use App\Models\Country;
use App\Models\Region;
use Tests\TestCase;

class DestinationOfferScopeTest extends TestCase
{
    public function test_merges_country_scope_and_locks_country_slug(): void
    {
        $country = new Country([
            'name' => 'Spanien',
            'slug' => 'spanien',
            'countrycode' => 'ES',
            'filters' => [
                'place' => 'Spain',
                'placeLat' => '40.4',
                'placeLng' => '-3.7',
                'country' => 'Spain',
                'bounds_ne_lat' => '43.8',
                'bounds_ne_lng' => '4.3',
                'bounds_sw_lat' => '36.0',
                'bounds_sw_lng' => '-9.3',
            ],
        ]);

        $merged = DestinationOfferScope::mergeIntoRequest(
            ['country' => 'germany', 'type' => 'tour', 'species' => 'Pike'],
            $country,
        );

        $this->assertSame('spanien', $merged['country']);
        $this->assertSame('ES', $merged['country_short']);
        $this->assertSame('Spain', $merged['place']);
        $this->assertSame(40.4, $merged['placeLat']);
        $this->assertSame(-3.7, $merged['placeLng']);
        $this->assertSame(['country'], $merged['place_types']);
        $this->assertSame('tour', $merged['type']);
        $this->assertSame('Pike', $merged['species']);
        $this->assertArrayNotHasKey('city', $merged);
        $this->assertArrayNotHasKey('region', $merged);
    }

    public function test_merges_region_and_city_scopes(): void
    {
        $country = new Country([
            'name' => 'Spanien',
            'slug' => 'spanien',
            'countrycode' => 'ES',
            'filters' => [],
        ]);
        $region = new Region([
            'name' => 'Andalucía',
            'slug' => 'andalucia',
            'filters' => [
                'place' => 'Andalusia',
                'placeLat' => '37.5',
                'placeLng' => '-4.5',
                'region' => 'Andalusia',
            ],
        ]);
        $city = new City([
            'name' => 'Málaga',
            'slug' => 'malaga',
            'filters' => [
                'place' => 'Malaga',
                'placeLat' => '36.7',
                'placeLng' => '-4.4',
                'city' => 'Malaga',
                'region' => 'Andalusia',
            ],
        ]);

        $regionMerged = DestinationOfferScope::mergeIntoRequest([], $country, $region);
        $this->assertSame('spanien', $regionMerged['country']);
        $this->assertSame('Andalusia', $regionMerged['region']);
        $this->assertSame('Andalusia', $regionMerged['place']);
        $this->assertSame(['administrative_area_level_1'], $regionMerged['place_types']);
        $this->assertArrayNotHasKey('city', $regionMerged);

        $cityMerged = DestinationOfferScope::mergeIntoRequest([], $country, $region, $city);
        $this->assertSame('Malaga', $cityMerged['city']);
        $this->assertSame('Andalusia', $cityMerged['region']);
        $this->assertSame('Malaga', $cityMerged['place']);
        $this->assertSame(['locality'], $cityMerged['place_types']);
        $this->assertSame(36.7, $cityMerged['placeLat']);
    }

    public function test_coordinates_from_destination_filters(): void
    {
        $country = new Country([
            'name' => 'Latvia',
            'slug' => 'lettland',
            'filters' => [
                'placeLat' => '56.88',
                'placeLng' => '24.60',
            ],
        ]);

        $this->assertSame(
            ['lat' => 56.88, 'lng' => 24.60],
            DestinationOfferScope::coordinatesFrom($country)
        );

        $missing = new Country([
            'name' => 'Latvia',
            'slug' => 'lettland',
            'filters' => [],
        ]);
        $this->assertNull(DestinationOfferScope::coordinatesFrom($missing));
    }
}
