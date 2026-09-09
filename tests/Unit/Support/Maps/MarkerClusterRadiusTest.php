<?php

namespace Tests\Unit\Support\Maps;

use App\Support\Maps\MarkerClusterRadius;
use Tests\TestCase;

class MarkerClusterRadiusTest extends TestCase
{
    public function test_cell_size_is_capped_at_low_zoom(): void
    {
        // Zoom is floored at MIN_CLUSTER_ZOOM (4), so world-zoom cells never
        // grow coarser than the zoom-4 size — see class docblock.
        $this->assertSame(5.625, MarkerClusterRadius::cellSizeDegrees(0));
        $this->assertSame(5.625, MarkerClusterRadius::cellSizeDegrees(2));
        $this->assertSame(5.625, MarkerClusterRadius::cellSizeDegrees(4));
    }

    public function test_cell_size_shrinks_with_zoom_above_the_cap(): void
    {
        $this->assertEqualsWithDelta(0.703125, MarkerClusterRadius::cellSizeDegrees(7), 0.0001);
        $this->assertLessThan(
            MarkerClusterRadius::cellSizeDegrees(4),
            MarkerClusterRadius::cellSizeDegrees(7)
        );
    }

    public function test_world_zoom_keeps_neighbouring_countries_from_merging_into_one_giant_cluster(): void
    {
        // Regression: an uncapped 22.5° cell at zoom 2 merged Netherlands,
        // Belgium, Germany, Denmark and Sweden into a single cluster whose
        // averaged position landed over northern Germany, making the other
        // countries look listing-free.
        $amsterdam = MarkerClusterRadius::cellKey(52.37, 4.89, 2);
        $berlin = MarkerClusterRadius::cellKey(52.52, 13.405, 2);
        $stockholm = MarkerClusterRadius::cellKey(59.33, 18.06, 2);

        $this->assertNotSame($amsterdam, $berlin);
        $this->assertNotSame($amsterdam, $stockholm);
        $this->assertNotSame($berlin, $stockholm);
    }

    public function test_world_zoom_keeps_europe_and_west_africa_in_different_cells(): void
    {
        $berlin = MarkerClusterRadius::cellKey(52.52, 13.405, 2);
        $accra = MarkerClusterRadius::cellKey(5.6, -0.19, 2);
        $dakar = MarkerClusterRadius::cellKey(14.7, -17.4, 2);

        $this->assertNotSame($berlin, $accra);
        $this->assertNotSame($berlin, $dakar);
    }

    public function test_world_zoom_keeps_netherlands_and_miami_in_different_cells(): void
    {
        $amsterdam = MarkerClusterRadius::cellKey(52.37, 4.89, 2);
        $miami = MarkerClusterRadius::cellKey(25.76, -80.19, 2);

        $this->assertNotSame($amsterdam, $miami);
        $this->assertNotSame($this->cellX($amsterdam), $this->cellX($miami));
    }

    public function test_neighbourhood_zoom_keeps_nearby_dutch_cities_local(): void
    {
        $amsterdam = MarkerClusterRadius::cellKey(52.37, 4.89, 7);
        $rotterdam = MarkerClusterRadius::cellKey(51.92, 4.48, 7);

        $this->assertNotSame($amsterdam, $rotterdam);
        $this->assertEqualsWithDelta(4.89, $this->cellLngCenter(4.89, 7), 0.8);
    }

    public function test_js_uses_lat_lng_cells_not_mercator_pixels(): void
    {
        $grid = (string) file_get_contents(resource_path('js/maps/GridMarkerCluster.js'));
        $helper = (string) file_get_contents(resource_path('js/maps/clusterGrid.js'));
        $manager = (string) file_get_contents(resource_path('js/maps/MapsManager.js'));

        $this->assertStringContainsString('clusterCellKey(ll.lat, ll.lng, zoom)', $grid);
        $this->assertStringNotContainsString('map.project(', $grid);
        $this->assertStringContainsString('360 / 2 ** (effectiveZ + 2)', $helper);
        $this->assertStringContainsString('new GridMarkerCluster', $manager);
        $this->assertStringNotContainsString('L.markerClusterGroup', $manager);
    }

    private function cellX(string $key): string
    {
        return explode(':', $key)[0];
    }

    private function cellLngCenter(float $lng, int $zoom): float
    {
        $span = MarkerClusterRadius::cellSizeDegrees($zoom);
        $x = (int) floor((MarkerClusterRadius::wrapLng($lng) + 180) / $span);

        return $x * $span - 180 + $span / 2;
    }
}
