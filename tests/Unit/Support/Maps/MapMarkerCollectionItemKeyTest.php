<?php

namespace Tests\Unit\Support\Maps;

use App\Support\Maps\MapMarkerCollection;
use Tests\TestCase;

class MapMarkerCollectionItemKeyTest extends TestCase
{
    public function test_item_key_is_module_scoped(): void
    {
        $this->assertSame('tour:13', MapMarkerCollection::itemKey('guiding', 13));
        $this->assertSame('trip:13', MapMarkerCollection::itemKey('trip', 13));
        $this->assertSame('camp:2', MapMarkerCollection::itemKey('camp', 2));
    }

    public function test_module_fields_include_stable_key(): void
    {
        $fields = MapMarkerCollection::moduleFields('trip', 7);

        $this->assertSame('trip', $fields['module']);
        $this->assertSame('trip:7', $fields['key']);
        $this->assertNotEmpty($fields['moduleLabel']);
    }

    public function test_mixed_type_markers_with_same_numeric_id_have_distinct_keys(): void
    {
        $tour = (object) [
            'id' => 2,
            'slug' => 'tour-two',
            'title' => 'Tour Two',
            'lat' => 48.1,
            'lng' => 11.5,
            'location' => 'Munich',
            'thumbnail_path' => null,
            'price' => 100,
        ];
        $trip = (object) [
            'id' => 2,
            'slug' => 'trip-two',
            'title' => 'Trip Two',
            'latitude' => 52.5,
            'longitude' => 13.4,
            'location' => 'Berlin',
            'thumbnail_path' => null,
            'price_per_person' => 150,
            'currency' => 'EUR',
        ];
        $camp = (object) [
            'id' => 2,
            'slug' => 'camp-two',
            'title' => 'Camp Two',
            'latitude' => 47.2,
            'longitude' => 11.3,
            'location' => 'Innsbruck',
            'thumbnail_path' => null,
            'city' => 'Innsbruck',
        ];

        $markers = array_merge(
            MapMarkerCollection::fromGuidings([$tour]),
            MapMarkerCollection::fromTrips([$trip]),
            MapMarkerCollection::fromCamps([$camp]),
        );

        $this->assertCount(3, $markers);
        $keys = array_column($markers, 'key');
        $this->assertSame(['tour:2', 'trip:2', 'camp:2'], $keys);
        $this->assertCount(3, array_unique($keys));
    }
}
