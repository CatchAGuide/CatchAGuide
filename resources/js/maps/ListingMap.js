/**
 * ListingMap — multi-marker listing / modal map with clustering + AJAX setMarkers
 */
import mapsManager, { L } from './MapsManager';
import markerFactory from './MarkerFactory';

class ListingMap {
  /**
   * @param {HTMLElement} el
   * @param {Object} [overrideOptions]
   */
  constructor(el, overrideOptions = {}) {
    this.el = el;
    this.map = null;
    this.cluster = null;
    this.markers = [];
    this._initialized = false;
    this.options = { ...this._parseOptions(el), ...overrideOptions };
  }

  _readJsonScript(el, attr) {
    const id = el.id;
    if (!id) {
      return null;
    }
    const safeId =
      typeof CSS !== 'undefined' && typeof CSS.escape === 'function' ? CSS.escape(id) : id.replace(/"/g, '\\"');
    const script = document.querySelector(`script[type="application/json"][${attr}="${safeId}"]`);
    if (!script || !script.textContent) {
      return null;
    }
    return JSON.parse(script.textContent.trim());
  }

  _parseOptions(el) {
    const ds = el.dataset;
    let markers = [];
    let center = null;

    try {
      const fromScript = this._readJsonScript(el, 'data-cag-maps-markers');
      if (Array.isArray(fromScript)) {
        markers = fromScript;
      } else if (ds.markers) {
        // Legacy fallback (small payloads only)
        markers = JSON.parse(ds.markers);
      }
    } catch (e) {
      console.warn('ListingMap: invalid markers JSON', e);
    }

    try {
      const fromScript = this._readJsonScript(el, 'data-cag-maps-center');
      if (fromScript && typeof fromScript === 'object') {
        center = fromScript;
      } else if (ds.center) {
        center = JSON.parse(ds.center);
      }
    } catch (e) {
      /* ignore */
    }

    return {
      markers,
      center: center || mapsManager.config.defaultCenter,
      cluster: ds.cluster !== 'false' && ds.cluster !== '0',
      fitPrimaryBounds: ds.fitPrimaryBounds !== 'false' && ds.fitPrimaryBounds !== '0',
      showGrayNearby: ds.showGrayNearby !== 'false' && ds.showGrayNearby !== '0',
      singleZoom: ds.singleZoom != null ? parseInt(ds.singleZoom, 10) : 12,
      defaultZoom: ds.defaultZoom != null ? parseInt(ds.defaultZoom, 10) : mapsManager.config.defaultZoom,
      layout: ds.layout || 'modal',
      modalId: ds.modalId || null,
      lazyModal: ds.lazyModal !== 'false' && ds.lazyModal !== '0',
      updatable: ds.updatable !== 'false' && ds.updatable !== '0',
      instanceKey: ds.instanceKey || el.id || 'listingMap',
    };
  }

  init() {
    if (this._initialized) {
      mapsManager.resizeMap(this.map);
      return this;
    }

    const start = () => this._create();

    if (this.options.layout === 'modal' && this.options.lazyModal && this.options.modalId) {
      const modal = document.getElementById(this.options.modalId);
      if (modal) {
        modal.addEventListener('shown.bs.modal', () => {
          if (!this._initialized) {
            start();
          } else {
            mapsManager.resizeMap(this.map);
          }
        });
      } else {
        start();
      }
    } else {
      start();
    }

    if (this.options.updatable) {
      window.__cagListingMaps = window.__cagListingMaps || {};
      window.__cagListingMaps[this.options.instanceKey] = this;
      // Back-compat for guidings filters — optional enrich hook from page scripts
      if (this.options.instanceKey === 'guidings' || this.el.id === 'map') {
        window.updateMapWithGuidings = (guidings) => {
          const enrich = window.__cagEnrichGuidingsForMap;
          const payload = typeof enrich === 'function' ? enrich(guidings) : guidings;
          this.setMarkersFromGuidings(payload);
        };
      }
    }

    return this;
  }

  _create() {
    if (this._initialized) {
      return;
    }

    this.map = mapsManager.initMap(this.el, {
      center: this.options.center,
      zoom: this.options.defaultZoom,
      scrollWheelZoom: true,
      dragging: true,
    });

    this._initialized = true;
    this.setMarkers(this.options.markers);
    mapsManager.resizeMap(this.map);
  }

  clearMarkers() {
    if (this.cluster) {
      this.cluster.clearLayers();
      if (this.map) {
        this.map.removeLayer(this.cluster);
      }
      this.cluster = null;
    }
    this.markers.forEach((m) => {
      if (this.map && this.map.hasLayer(m)) {
        this.map.removeLayer(m);
      }
    });
    this.markers = [];
  }

  /**
   * @param {Array<{id?:*, lat:number, lng:number, variant?:string, popupHtml?:string, title?:string}>} items
   */
  setMarkers(items) {
    if (!this.map) {
      this.options.markers = items || [];
      return;
    }

    this.clearMarkers();
    const list = Array.isArray(items) ? items : [];
    const uniqueCoords = [];
    const primaryLatLngs = [];
    const leafletMarkers = [];

    list.forEach((item) => {
      if (item.lat == null || item.lng == null) {
        return;
      }

      let lat = parseFloat(item.lat);
      let lng = parseFloat(item.lng);
      if (Number.isNaN(lat) || Number.isNaN(lng)) {
        return;
      }

      const isDup = uniqueCoords.some((c) => c.lat === lat && c.lng === lng);
      if (isDup) {
        lat += markerFactory.getRandomOffset();
        lng += markerFactory.getRandomOffset();
      } else {
        uniqueCoords.push({ lat: parseFloat(item.lat), lng: parseFloat(item.lng) });
      }

      const variant = item.variant || 'primary';
      if (!this.options.showGrayNearby && variant === 'gray') {
        return;
      }

      const marker = markerFactory.createMarker({
        position: { lat, lng },
        variant,
        title: item.title || '',
        popupHtml: item.popupHtml || null,
        zIndexOffset: variant === 'gray' ? 100 : 0,
      });

      // Close other popups when opening one
      marker.on('click', () => {
        leafletMarkers.forEach((m) => {
          if (m !== marker && m.isPopupOpen && m.isPopupOpen()) {
            m.closePopup();
          }
        });
      });

      leafletMarkers.push(marker);
      this.markers.push(marker);

      if (variant !== 'gray') {
        primaryLatLngs.push(L.latLng(parseFloat(item.lat), parseFloat(item.lng)));
      }
    });

    if (this.options.cluster && leafletMarkers.length) {
      this.cluster = mapsManager.createMarkerClusterer({
        map: this.map,
        markers: leafletMarkers,
      });
    } else {
      leafletMarkers.forEach((m) => m.addTo(this.map));
    }

    if (this.options.fitPrimaryBounds && primaryLatLngs.length) {
      this._fitPrimary(primaryLatLngs);
    }

    mapsManager.resizeMap(this.map);
  }

  _fitPrimary(latLngs) {
    const unique = new Set(latLngs.map((ll) => `${ll.lat},${ll.lng}`));
    if (unique.size === 1) {
      this.map.setView(latLngs[0], this.options.singleZoom);
      return;
    }
    const bounds = L.latLngBounds(latLngs);
    this.map.fitBounds(bounds, { padding: [40, 40] });
  }

  /**
   * AJAX remap from guidings filter payload (server objects).
   * Expects items with lat, lng, and optional popupHtml / id / title / location / price.
   */
  setMarkersFromGuidings(guidings) {
    const markers = (guidings || []).map((g) => {
      if (g.popupHtml) {
        return {
          id: g.id,
          lat: g.lat,
          lng: g.lng,
          variant: g.variant || (g.is_gray || g.isGray ? 'gray' : 'primary'),
          popupHtml: g.popupHtml,
          title: g.title,
        };
      }

      const title = g.title || '';
      const location = g.location || '';
      const price = g.lowest_price != null ? g.lowest_price : g.price;
      const url = g.url || g.link || '#';
      const image = g.thumbnail || g.thumbnail_path || g.image || '';
      const priceLine =
        price != null && price !== ''
          ? `<div class="cag-map-popup__price"><span class="fw-bold">ab ${price}€</span> p.P.</div>`
          : '';

      const popupHtml = `
        <div class="cag-map-popup__card">
          ${image ? `<img class="cag-map-popup__image" src="${image}" alt="">` : ''}
          <div class="cag-map-popup__body">
            <a class="text-decoration-none" href="${url}">
              <h5 class="cag-map-popup__title">${title}</h5>
            </a>
            <div class="cag-map-popup__location">${location}</div>
            ${priceLine}
          </div>
        </div>`;

      return {
        id: g.id,
        lat: g.lat,
        lng: g.lng,
        variant: g.variant || 'primary',
        popupHtml,
        title,
      };
    });

    this.setMarkers(markers);
  }

  invalidate() {
    mapsManager.resizeMap(this.map);
  }
}

export default ListingMap;
export { ListingMap };
