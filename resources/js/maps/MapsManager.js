/**
 * MapsManager — Leaflet facade (tiles, ready, invalidateSize)
 */
import L from 'leaflet';
import 'leaflet.markercluster';

// Fix default marker icon paths broken by webpack
import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';

delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
  iconRetinaUrl: markerIcon2x,
  iconUrl: markerIcon,
  shadowUrl: markerShadow,
});

L.Control.Attribution.mergeOptions({
  prefix: false,
});

const DEFAULT_CONFIG = {
  tileUrl: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
  attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
  defaultCenter: { lat: 51.165691, lng: 10.451526 },
  defaultZoom: 5,
};

class MapsManager {
  constructor() {
    this.config = { ...DEFAULT_CONFIG, ...(window.CAG_MAPS_CONFIG || {}) };
    this._ready = true;
  }

  get L() {
    return L;
  }

  waitUntilReady(callback) {
    if (typeof callback === 'function') {
      callback();
    }
  }

  /** @deprecated alias for GoogleMapsManager.waitForGoogleMaps */
  waitForGoogleMaps(callback) {
    this.waitUntilReady(callback);
  }

  createTileLayer() {
    return L.tileLayer(this.config.tileUrl || DEFAULT_CONFIG.tileUrl, {
      attribution: this.config.attribution || DEFAULT_CONFIG.attribution,
      maxZoom: 19,
    });
  }

  /**
   * @param {string|HTMLElement} containerId
   * @param {Object} options
   * @returns {L.Map}
   */
  initMap(containerId, options = {}) {
    const container =
      typeof containerId === 'string' ? document.getElementById(containerId) : containerId;

    if (!container) {
      throw new Error(`Map container not found: ${containerId}`);
    }

    if (container._leaflet_id) {
      // Already initialized — return existing map if stored
      if (container._cagMap) {
        return container._cagMap;
      }
    }

    const center = options.center || this.config.defaultCenter;
    const zoom = options.zoom != null ? options.zoom : this.config.defaultZoom;
    const lat = typeof center.lat === 'function' ? center.lat() : center.lat;
    const lng = typeof center.lng === 'function' ? center.lng() : center.lng;

    const map = L.map(container, {
      zoomControl: options.zoomControl !== false,
      scrollWheelZoom: options.scrollWheelZoom === true,
      dragging: options.dragging !== false,
      attributionControl: true,
    }).setView([lat, lng], zoom);

    this.createTileLayer().addTo(map);
    container._cagMap = map;

    // Invalidate after layout (modals / lazy)
    setTimeout(() => map.invalidateSize(), 50);

    return map;
  }

  resizeMap(map) {
    if (map && typeof map.invalidateSize === 'function') {
      setTimeout(() => map.invalidateSize(), 100);
    }
  }

  initMapOnModalShow(modalId, initCallback) {
    const run = () => {
      const modal = document.getElementById(modalId);
      if (!modal) {
        console.warn(`Modal not found: ${modalId}`);
        return;
      }
      modal.addEventListener('shown.bs.modal', () => {
        this.waitUntilReady(() => {
          if (typeof initCallback === 'function') {
            initCallback();
          }
        });
      });
    };

    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', run);
    } else {
      run();
    }
  }

  createMarkerClusterer({ map, markers, muted = false }) {
    const cluster = L.markerClusterGroup({
      showCoverageOnHover: false,
      maxClusterRadius: 60,
      // We own click/keypress handling below instead of the plugin defaults,
      // so a single click can jump straight to the cluster's real bounds.
      spiderfyOnMaxZoom: false,
      zoomToBoundsOnClick: false,
      iconCreateFunction: (clusterGroup) => this._createClusterIcon(clusterGroup, muted),
    });

    if (Array.isArray(markers) && markers.length) {
      markers.forEach((m) => cluster.addLayer(m));
    }

    cluster.on('clusterclick clusterkeypress', (e) => this._onClusterActivate(e, map));

    map.addLayer(cluster);
    return cluster;
  }

  /**
   * Single click/keypress on a cluster: zoom straight to its true bounds instead of
   * stepping the zoom level up a click at a time. Pins sitting on the same (or
   * near-identical) coordinates never separate by zooming further in, so spiderfy
   * them open immediately rather than forcing the user to reach max zoom first.
   */
  _onClusterActivate(e, map) {
    if (e.type === 'clusterkeypress' && e.originalEvent && e.originalEvent.keyCode !== 13) {
      return;
    }

    const cluster = e.layer;
    const isTightCluster = cluster.getChildCount() > 1 && map.getBoundsZoom(cluster.getBounds()) >= map.getMaxZoom();

    if (isTightCluster) {
      cluster.spiderfy();
    } else {
      cluster.zoomToBounds({ paddingTopLeft: [24, 24], paddingBottomRight: [24, 24] });
    }

    if (e.originalEvent && e.originalEvent.keyCode === 13) {
      map._container.focus();
    }
  }

  /**
   * Canonical map colour key for a module: gray -> other | tour -> tours | camp -> camps | trip -> holidays
   * @param {string} module
   */
  _clusterSpecKey(module) {
    return { tour: 'tours', camp: 'camps', trip: 'holidays', gray: 'other' }[module] || 'tours';
  }

  /**
   * Fixed-size cluster circle. When a cluster mixes offer types, up to 2 further
   * types render as equally-sized plain circles behind the dominant (front) one —
   * tie-break order tours > camps > holidays, offsets per the cluster spec.
   */
  _createClusterIcon(clusterGroup, muted = false) {
    const count = clusterGroup.getChildCount();

    if (muted) {
      return L.divIcon({
        html: `<span class="cag-map-cluster__core"><span class="cag-map-cluster__count">${count}</span></span>`,
        className: 'leaflet-div-icon marker-cluster-small cag-map-cluster cag-map-cluster--muted',
        iconSize: L.point(26, 26),
        iconAnchor: [13, 13],
      });
    }

    const PRIORITY = { tour: 0, camp: 1, trip: 2 };
    const typeCounts = { tour: 0, camp: 0, trip: 0 };
    clusterGroup.getAllChildMarkers().forEach((m) => {
      const key = (m.options && m.options.cagVariant) || 'tour';
      if (key === 'tour' || key === 'camp' || key === 'trip') {
        typeCounts[key] += 1;
      } else if (key !== 'gray') {
        typeCounts.tour += 1;
      }
    });

    const present = Object.keys(typeCounts)
      .filter((key) => typeCounts[key] > 0)
      .sort((a, b) => typeCounts[b] - typeCounts[a] || PRIORITY[a] - PRIORITY[b]);

    const dominant = present[0] || 'tour';
    const edges = present.slice(1, 3);

    const edgesHtml = edges
      .map(
        (type, i) =>
          `<span class="cag-map-cluster__edge cag-map-cluster__edge--${i + 1}" style="background:var(--map-${this._clusterSpecKey(type)})" aria-hidden="true"></span>`
      )
      .join('');

    // Bounding box grows to the right/up as stacked edges are added; the front
    // circle always stays anchored at the cluster's true geographic point.
    let minTop = 0;
    let maxRight = 32;
    if (edges.length >= 1) {
      minTop = Math.min(minTop, -4);
      maxRight = Math.max(maxRight, 9 + 32);
    }
    if (edges.length >= 2) {
      minTop = Math.min(minTop, -9);
      maxRight = Math.max(maxRight, 18 + 32);
    }
    const width = maxRight;
    const height = 32 - minTop;
    const anchor = [16, 16 - minTop];

    return L.divIcon({
      html: `
        ${edgesHtml}
        <span class="cag-map-cluster__core" style="background:var(--map-${this._clusterSpecKey(dominant)})">
          <span class="cag-map-cluster__count">${count}</span>
        </span>`,
      className: `leaflet-div-icon marker-cluster-small cag-map-cluster cag-map-cluster--${dominant}`,
      iconSize: L.point(width, height),
      iconAnchor: anchor,
    });
  }
}

const mapsManager = new MapsManager();
export default mapsManager;
export { MapsManager, L };
