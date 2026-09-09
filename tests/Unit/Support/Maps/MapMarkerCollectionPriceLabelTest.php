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
            'duration' => 8,
            'duration_type' => 'full_day',
            'max_guests' => 4,
            'cached_average_rating' => 4.8,
            'cached_review_count' => 12,
            'is_boat' => 1,
        ];

        $markers = MapMarkerCollection::fromGuidings([$guiding]);

        $this->assertCount(1, $markers);
        $this->assertSame(120, $markers[0]['price']);
        $this->assertNotNull($markers[0]['priceLabel']);
        $this->assertStringContainsString('120', $markers[0]['priceLabel']);
        $this->assertStringNotContainsString('p.P.', (string) $markers[0]['priceLabel']);
        $this->assertSame(MapMarkerCollection::MODULE_TOUR, $markers[0]['module']);
        $this->assertNotEmpty($markers[0]['moduleLabel']);
        $this->assertSame(4.8, $markers[0]['rating']);
        $this->assertSame(12, $markers[0]['reviewCount']);
        $this->assertNotEmpty($markers[0]['durationLabel']);
        $this->assertNotEmpty($markers[0]['guestsLabel']);
    }

    public function test_from_guidings_includes_gallery_images_for_carousel(): void
    {
        $guiding = (object) [
            'id' => 7,
            'slug' => 'gallery-trip',
            'title' => 'Gallery Guiding',
            'lat' => 48.1,
            'lng' => 11.5,
            'location' => 'Munich',
            'thumbnail_path' => 'https://cdn.example/thumb.jpg',
            'gallery_images' => [
                'https://cdn.example/gallery-1.jpg',
                'https://cdn.example/gallery-2.jpg',
            ],
            'price' => 90,
        ];

        $markers = MapMarkerCollection::fromGuidings([$guiding]);

        $this->assertSame('https://cdn.example/thumb.jpg', $markers[0]['image']);
        $this->assertSame([
            'https://cdn.example/thumb.jpg',
            'https://cdn.example/gallery-1.jpg',
            'https://cdn.example/gallery-2.jpg',
        ], $markers[0]['images']);
    }

    public function test_from_guidings_appends_search_query_to_product_url(): void
    {
        $guiding = (object) [
            'id' => 9,
            'slug' => 'rhine-perch',
            'title' => 'Rhine Perch',
            'lat' => 51.22,
            'lng' => 6.77,
            'location' => 'Düsseldorf',
            'thumbnail_path' => null,
            'price' => 200,
        ];

        $markers = MapMarkerCollection::fromGuidings([$guiding], [], [
            'place' => 'Düsseldorf, Deutschland',
            'num_guests' => 3,
        ]);

        $query = [];
        parse_str((string) parse_url($markers[0]['url'], PHP_URL_QUERY), $query);

        $this->assertSame('3', $query['num_guests']);
        $this->assertSame('Düsseldorf, Deutschland', $query['place']);
    }

    public function test_from_trips_appends_search_query_to_product_url(): void
    {
        $trip = (object) [
            'id' => 8,
            'slug' => 'valencia-bass',
            'title' => 'Valencia Bass',
            'latitude' => 39.47,
            'longitude' => -0.38,
            'location' => 'Valencia',
            'thumbnail_path' => null,
            'price_per_person' => 1500,
            'currency' => 'EUR',
        ];

        $markers = MapMarkerCollection::fromTrips([$trip], [
            'country' => 'spain',
            'num_guests' => 3,
        ]);

        $query = [];
        parse_str((string) parse_url($markers[0]['url'], PHP_URL_QUERY), $query);

        $this->assertSame('3', $query['num_guests']);
        $this->assertSame('spain', $query['country']);
    }

    public function test_from_camps_appends_search_query_to_product_url(): void
    {
        $camp = (object) [
            'id' => 5,
            'slug' => 'danube-camp',
            'title' => 'Danube Camp',
            'latitude' => 48.2,
            'longitude' => 16.3,
            'location' => 'Vienna',
            'thumbnail_path' => null,
        ];

        $markers = MapMarkerCollection::fromCamps([$camp], [
            'country' => 'austria',
            'num_guests' => 2,
        ]);

        $query = [];
        parse_str((string) parse_url($markers[0]['url'], PHP_URL_QUERY), $query);

        $this->assertSame('2', $query['num_guests']);
        $this->assertSame('austria', $query['country']);
    }

    public function test_normalize_module_maps_guiding_to_tour(): void
    {
        $this->assertSame('tour', MapMarkerCollection::normalizeModule('guiding'));
        $this->assertSame('trip', MapMarkerCollection::normalizeModule('trip'));
        $this->assertSame('camp', MapMarkerCollection::normalizeModule('camps'));
    }
}
