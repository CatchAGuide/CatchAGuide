<?php

namespace Tests\Feature\Maps;

use Tests\TestCase;

class MapBasemapConfigTest extends TestCase
{
    public function test_default_basemap_is_openstreetmap_without_an_api_key(): void
    {
        $url = (string) config('services.maps.tile_url');
        $attribution = (string) config('services.maps.attribution');

        $this->assertStringContainsString('tile.openstreetmap.org', $url);
        $this->assertStringNotContainsString('cartocdn', $url);
        $this->assertStringNotContainsString('openfreemap', $url);
        $this->assertStringNotContainsString('key=', strtolower($url));
        $this->assertStringContainsString('openstreetmap.org', strtolower($attribution));
        $this->assertStringNotContainsString('CARTO', $attribution);
    }

    public function test_leaflet_brand_prefix_is_disabled_on_maps(): void
    {
        $source = (string) file_get_contents(resource_path('js/maps/MapsManager.js'));

        $this->assertMatchesRegularExpression(
            '/L\.Control\.Attribution\.mergeOptions\(\s*\{\s*prefix:\s*false/',
            $source
        );
    }
}
