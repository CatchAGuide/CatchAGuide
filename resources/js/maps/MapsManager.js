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

  createMarkerClusterer({ map, markers }) {
    const cluster = L.markerClusterGroup({
      showCoverageOnHover: false,
      maxClusterRadius: 50,
      spiderfyOnMaxZoom: true,
      iconCreateFunction: (clusterGroup) => this._createClusterIcon(clusterGroup),
    });

    if (Array.isArray(markers) && markers.length) {
      markers.forEach((m) => cluster.addLayer(m));
    }

    cluster.on('clusterclick', (e) => {
      const currentZoom = map.getZoom();
      map.setView(e.latlng, Math.min(currentZoom + 2, map.getMaxZoom()));
    });

    map.addLayer(cluster);
    return cluster;
  }

  _createClusterIcon(clusterGroup) {
    const count = clusterGroup.getChildCount();
    let size = 'small';
    let dim = 42;
    if (count >= 25) {
      size = 'large';
      dim = 56;
    } else if (count >= 8) {
      size = 'medium';
      dim = 48;
    }

    const typeCounts = { tour: 0, trip: 0, camp: 0 };
    clusterGroup.getAllChildMarkers().forEach((m) => {
      const key = (m.options && m.options.cagVariant) || 'tour';
      if (key === 'trip' || key === 'camp' || key === 'tour') {
        typeCounts[key] += 1;
      } else if (key !== 'gray') {
        typeCounts.tour += 1;
      }
    });

    const dominant =
      Object.keys(typeCounts).sort((a, b) => typeCounts[b] - typeCounts[a])[0] || 'tour';
    const mixed =
      [typeCounts.tour > 0, typeCounts.trip > 0, typeCounts.camp > 0].filter(Boolean).length > 1;
    const typeClass = mixed ? 'cag-map-cluster--mixed' : `cag-map-cluster--${dominant}`;

    return L.divIcon({
      html: `
        <span class="cag-map-cluster__ring" aria-hidden="true"></span>
        <span class="cag-map-cluster__ring cag-map-cluster__ring--delay" aria-hidden="true"></span>
        <span class="cag-map-cluster__core">
          <span class="cag-map-cluster__count">${count}</span>
          <span class="cag-map-cluster__hint" aria-hidden="true">+</span>
        </span>`,
      className: `leaflet-div-icon marker-cluster marker-cluster-${size} cag-map-cluster cag-map-cluster--${size} ${typeClass}`,
      iconSize: L.point(dim, dim),
      iconAnchor: [Math.round(dim / 2), Math.round(dim / 2)],
    });
  }
}

const mapsManager = new MapsManager();
export default mapsManager;
export { MapsManager, L };
