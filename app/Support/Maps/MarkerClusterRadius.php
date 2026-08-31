<?php

namespace App\Support\Maps;

/**
 * Geographic cluster grid used by listing maps (GridMarkerCluster).
 *
 * Cell size is 360 / 2^(zoom+2) degrees so pins are bucketed by lat/lng,
 * not map pixels. A pixel grid at world zoom collapses longitude and draws
 * every pin on a vertical line down the Prime Meridian.
 */
final class MarkerClusterRadius
{
    public static function cellSizeDegrees(int|float $zoom): float
    {
        $z = max(0, (int) round($zoom));

        return 360 / (2 ** ($z + 2));
    }

    public static function wrapLng(float $lng): float
    {
        $wrapped = $lng;
        while ($wrapped < -180) {
            $wrapped += 360;
        }
        while ($wrapped > 180) {
            $wrapped -= 360;
        }

        return $wrapped;
    }

    public static function cellKey(float $lat, float $lng, int|float $zoom): string
    {
        $span = self::cellSizeDegrees($zoom);
        $safeLat = max(-90.0, min(90.0, $lat));
        $safeLng = self::wrapLng($lng);
        $x = (int) floor(($safeLng + 180) / $span);
        $y = (int) floor(($safeLat + 90) / $span);

        return $x.':'.$y;
    }
}
