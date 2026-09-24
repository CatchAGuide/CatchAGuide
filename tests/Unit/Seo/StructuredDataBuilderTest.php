<?php

namespace Tests\Unit\Seo;

use App\Models\Camp;
use App\Models\Trip;
use App\Services\Seo\StructuredDataBuilder;
use Tests\TestCase;

class StructuredDataBuilderTest extends TestCase
{
    private function builder(): StructuredDataBuilder
    {
        return app(StructuredDataBuilder::class);
    }

    public function test_breadcrumb_list_starts_at_home_and_links_the_current_page_last(): void
    {
        $list = $this->builder()->breadcrumbList([
            ['label' => 'Reiseziele', 'url' => 'https://catchaguide.de/destination'],
            ['label' => '<b>Niederlande</b>', 'url' => null],
            ['label' => '   '],
        ], 'https://catchaguide.de/destination/niederlande');

        $this->assertSame('BreadcrumbList', $list['@type']);
        $this->assertCount(3, $list['itemListElement'], 'Blank labels are skipped');
        $this->assertSame(route('welcome'), $list['itemListElement'][0]['item']);
        $this->assertSame([1, 2, 3], array_column($list['itemListElement'], 'position'));
        $this->assertSame('Niederlande', $list['itemListElement'][2]['name'], 'HTML is stripped from labels');
        $this->assertSame('https://catchaguide.de/destination/niederlande', $list['itemListElement'][2]['item']);
    }

    public function test_trip_is_a_tourist_trip_with_an_offer_only_when_priced(): void
    {
        $trip = new Trip([
            'title' => 'Big Game Malediven',
            'description' => '<p>Sieben   Tage</p>',
            'country' => 'Malediven',
            'provider_name' => 'Ocean Guides',
            'price_per_person' => 1890,
            'currency' => 'EUR',
        ]);

        $data = $this->builder()->trip($trip, 'https://catchaguide.de/vacations/trips/big-game');

        $this->assertSame('TouristTrip', $data['@type']);
        $this->assertSame('Sieben Tage', $data['description']);
        $this->assertSame(1890.0, $data['offers']['price']);
        $this->assertSame('Ocean Guides', $data['provider']['name']);
        $this->assertArrayNotHasKey('image', $data, 'Empty fields are omitted, not null');

        $unpriced = $this->builder()->trip(new Trip(['title' => 'Ohne Preis']), 'https://catchaguide.de/vacations/trips/x');
        $this->assertArrayNotHasKey('offers', $unpriced);
    }

    public function test_camp_is_a_lodging_business_with_iso_country_and_geo(): void
    {
        $camp = new Camp([
            'title' => 'Camp Port Massaluca',
            'city' => 'La Pobla de Massaluca',
            'country' => 'spanien',
        ]);
        $camp->latitude = 41.18;
        $camp->longitude = 0.35;

        $data = $this->builder()->camp($camp, 'https://catchaguide.de/vacations/camps/port-massaluca');

        $this->assertSame('LodgingBusiness', $data['@type']);
        $this->assertSame('ES', $data['address']['addressCountry']);
        $this->assertArrayNotHasKey('addressRegion', $data['address']);
        $this->assertSame(41.18, $data['geo']['latitude']);

        $noLocation = $this->builder()->camp(new Camp(['title' => 'Irgendwo']), 'https://catchaguide.de/vacations/camps/x');
        $this->assertArrayNotHasKey('address', $noLocation);
        $this->assertArrayNotHasKey('geo', $noLocation);
    }
}
