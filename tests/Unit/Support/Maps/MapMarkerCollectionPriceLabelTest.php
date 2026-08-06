<?php

namespace Tests\Unit\Support\Maps;

use App\Support\Maps\MapMarkerCollection;
use Tests\TestCase;

class MapMarkerCollectionPriceLabelTest extends TestCase
{
    public function test_from_guidings_uses_translated_price_label(): void
    {
        app()->setLocale('en');

        $guiding = (object) [
            'id' => 42,
            'slug' => 'test-trip',
            'title' => 'Test Guiding',
            'lat' => 48.1,
            'lng' => 11.5,
            'location' => 'Munich',
            'thumbnail_path' => null,
            'price' => 120,
        ];

        $markers = MapMarkerCollection::fromGuidings([$guiding]);

        $this->assertCount(1, $markers);
        $this->assertSame(120, $markers[0]['price']);
        $this->assertNotNull($markers[0]['priceLabel']);
        $this->assertStringContainsString('120', $markers[0]['priceLabel']);
        $this->assertStringNotContainsString('p.P.', (string) $markers[0]['priceLabel']);
    }
}
