/**
 * Stable identity for mixed offer map markers.
 * Tours, trips, and camps share numeric IDs, so viewport counts and rail
 * selection must key on module + id (e.g. trip:2 vs camp:2).
 */

export function normalizeModule(value) {
  const v = String(value || '')
    .toLowerCase()
    .trim();
  if (v === 'trip' || v === 'trips') return 'trip';
  if (v === 'camp' || v === 'camps' || v === 'vacation') return 'camp';
  if (v === 'guiding' || v === 'tour' || v === 'primary') return 'tour';
  return 'tour';
}

/**
 * @param {{ id?: number|string, key?: string, module?: string, pillar?: string }|null|undefined} item
 * @returns {string|null}
 */
export function itemKey(item) {
  if (!item || item.id == null) return null;
  if (item.key != null && String(item.key) !== '') {
    return String(item.key);
  }
  return `${normalizeModule(item.module || item.pillar)}:${item.id}`;
}

/**
 * Match a marker item against a composite key (module:id) or legacy bare id.
 * @param {{ id?: number|string, key?: string, module?: string, pillar?: string }|null|undefined} item
 * @param {string|number|null|undefined} idOrKey
 */
export function itemsMatch(item, idOrKey) {
  if (!item || item.id == null || idOrKey == null) return false;
  const needle = String(idOrKey);
  const key = itemKey(item);
  if (key === needle) return true;
  // Legacy callers may pass a bare numeric id (tour-only maps).
  if (!needle.includes(':')) {
    return String(item.id) === needle;
  }
  return false;
}
