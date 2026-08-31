/**
 * LandmarkLayer — sparse OSM POIs via cached Laravel proxy
 */
import mapsManager, { L } from './MapsManager';

export default class LandmarkLayer {
  /**
   * @param {import('./ListingMap').default} listingMap
   */
  constructor(listingMap) {
    this.listingMap = listingMap;
    this.layer = null;
    this._timer = null;
    this._lastKey = '';
    this._abort = null;
    this.minZoom = (mapsManager.config && mapsManager.config.landmarksMinZoom) || 10;
    this.url = (mapsManager.config && mapsManager.config.landmarksUrl) || '/maps/landmarks';
  }

  attach() {
    if (!this.listingMap.map) return;
    this.layer = L.layerGroup().addTo(this.listingMap.map);
    this.listingMap.map.on('moveend', () => this.scheduleFetch());
    this.listingMap.map.on('zoomend', () => this.scheduleFetch());
    this.scheduleFetch();
  }

  scheduleFetch() {
    if (this._timer) clearTimeout(this._timer);
    this._timer = setTimeout(() => this.fetch(), 350);
  }

  clear() {
    if (this.layer) {
      this.layer.clearLayers();
    }
    this._lastKey = '';
  }

  async fetch() {
    const map = this.listingMap.map;
    if (!map || !this.layer) return;

    const zoom = map.getZoom();
    if (zoom < this.minZoom) {
      this.clear();
      return;
    }

    const b = map.getBounds();
    const sw = b.getSouthWest();
    const ne = b.getNorthEast();
    const key = [
      zoom,
      sw.lat.toFixed(2),
      sw.lng.toFixed(2),
      ne.lat.toFixed(2),
      ne.lng.toFixed(2),
    ].join('|');

    if (key === this._lastKey) {
      return;
    }

    if (this._abort) {
      this._abort.abort();
    }
    this._abort = typeof AbortController !== 'undefined' ? new AbortController() : null;

    const params = new URLSearchParams({
      sw_lat: String(sw.lat),
      sw_lng: String(sw.lng),
      ne_lat: String(ne.lat),
      ne_lng: String(ne.lng),
      zoom: String(Math.round(zoom)),
    });

    try {
      const res = await fetch(`${this.url}?${params.toString()}`, {
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        signal: this._abort ? this._abort.signal : undefined,
      });
      if (!res.ok) {
        return;
      }
      const data = await res.json();
      const landmarks = Array.isArray(data.landmarks) ? data.landmarks : [];
      this._lastKey = key;
      this.render(landmarks);
    } catch (e) {
      if (e && e.name === 'AbortError') return;
      // Fail soft — map works without POIs
    }
  }

  render(landmarks) {
    if (!this.layer) return;
    this.layer.clearLayers();

    landmarks.forEach((lm) => {
      const lat = parseFloat(lm.lat);
      const lng = parseFloat(lm.lng);
      if (Number.isNaN(lat) || Number.isNaN(lng)) return;

      const category = lm.category || 'attraction';
      const name = lm.name || '';
      const marker = L.marker([lat, lng], {
        icon: L.divIcon({
          className: `leaflet-div-icon cag-map-landmark cag-map-landmark--${category}`,
          html: `<span class="cag-map-landmark__dot" aria-hidden="true"></span>${
            name ? `<span class="cag-map-landmark__label">${this._escape(name)}</span>` : ''
          }`,
          iconSize: [120, 28],
          iconAnchor: [8, 8],
        }),
        interactive: false,
        keyboard: false,
        zIndexOffset: -200,
      });
      this.layer.addLayer(marker);
    });
  }

  _escape(value) {
    return String(value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }
}
