/**
 * Geographic cluster grid (degrees). Shared by GridMarkerCluster and PHP tests.
 *
 * Cell size is 360 / 2^(zoom+2): 90° at z0, 22.5° at z2, ~0.7° at z7.
 * Pins are bucketed by lat/lng, never by map pixels — pixel grids collapse
 * longitude at world zoom and draw a vertical line down the Prime Meridian.
 */
export function clusterCellSizeDegrees(zoom) {
  const z = Math.max(0, Math.round(Number(zoom)) || 0);
  return 360 / 2 ** (z + 2);
}

export function wrapLng(lng) {
  let wrapped = Number(lng);
  if (!Number.isFinite(wrapped)) {
    return 0;
  }
  while (wrapped < -180) {
    wrapped += 360;
  }
  while (wrapped > 180) {
    wrapped -= 360;
  }
  return wrapped;
}

export function clusterCellKey(lat, lng, zoom) {
  const span = clusterCellSizeDegrees(zoom);
  const safeLat = Math.max(-90, Math.min(90, Number(lat) || 0));
  const safeLng = wrapLng(lng);
  const x = Math.floor((safeLng + 180) / span);
  const y = Math.floor((safeLat + 90) / span);
  return `${x}:${y}`;
}
