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
    this.grayLayer = null;
    this.markers = [];
    this._initialized = false;
    this._activePreviewMarker = null;
    this._hoverOpenTimer = null;
    this._hoverCloseTimer = null;
    this._canHover = typeof window !== 'undefined' && window.matchMedia
      ? window.matchMedia('(hover: hover) and (pointer: fine)').matches
      : true;
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
      interactivePreview: ds.interactivePreview === 'true' || ds.interactivePreview === '1',
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
      // Back-compat for listing filters — optional enrich hook from page scripts
      if (
        this.options.instanceKey === 'guidings' ||
        this.options.instanceKey === 'category-country' ||
        this.options.instanceKey === 'category-show' ||
        this.options.instanceKey === 'category' ||
        this.el.id === 'map'
      ) {
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

    if (this.options.interactivePreview) {
      this.map.on('click', () => this._clearStickyPreviews());
      this.map.on('movestart', () => {
        if (this._activePreviewMarker && !this._activePreviewMarker._cagSticky) {
          this._closePreview(this._activePreviewMarker);
        }
      });
    }
  }

  clearMarkers() {
    this._clearHoverTimers();
    this._activePreviewMarker = null;

    if (this.cluster) {
      this.cluster.clearLayers();
      if (this.map) {
        this.map.removeLayer(this.cluster);
      }
      this.cluster = null;
    }
    if (this.grayLayer) {
      this.grayLayer.clearLayers();
      if (this.map) {
        this.map.removeLayer(this.grayLayer);
      }
      this.grayLayer = null;
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
    const primaryMarkers = [];
    const grayMarkers = [];
    const interactive = this.options.interactivePreview;

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

      const variant = item.variant || item.pillar || 'primary';
      if (!this.options.showGrayNearby && variant === 'gray') {
        return;
      }

      const popupHtml = item.popupHtml || (interactive ? this.buildInteractivePreviewHtml(item) : this.buildPopupHtml(item));
      const previewWidth = interactive ? this._previewCardWidth() : 220;
      const popupOptions = interactive
        ? {
            className: `cag-map-popup cag-map-popup--interactive${item.pillar ? ` cag-map-popup--${item.pillar}` : ''}`,
            maxWidth: previewWidth,
            minWidth: Math.min(236, previewWidth),
            closeButton: false,
            // Hover should feel instant; pan only when the user pins a card (click/tap)
            autoPan: false,
            offset: [0, -6],
          }
        : undefined;

      const marker = markerFactory.createMarker({
        position: { lat, lng },
        variant,
        title: item.title || '',
        popupHtml,
        popupOptions,
        zIndexOffset: variant === 'gray' ? 100 : 0,
      });
      marker.options = marker.options || {};
      marker.options.cagVariant = variant;
      marker._cagItem = item;
      marker._cagSticky = false;

      if (interactive) {
        this._bindInteractivePreview(marker);
      } else {
        marker.on('click', () => {
          this.markers.forEach((m) => {
            if (m !== marker && m.isPopupOpen && m.isPopupOpen()) {
              m.closePopup();
            }
          });
        });
      }

      this.markers.push(marker);

      if (variant === 'gray') {
        grayMarkers.push(marker);
        return;
      }

      primaryMarkers.push(marker);
      primaryLatLngs.push(L.latLng(parseFloat(item.lat), parseFloat(item.lng)));
    });

    // Cluster only filtered/primary results so nearby (gray) pins never inflate cluster counts
    if (this.options.cluster && primaryMarkers.length) {
      this.cluster = mapsManager.createMarkerClusterer({
        map: this.map,
        markers: primaryMarkers,
      });
    } else {
      primaryMarkers.forEach((m) => m.addTo(this.map));
    }

    if (grayMarkers.length) {
      this.grayLayer = L.layerGroup(grayMarkers).addTo(this.map);
    }

    if (this.options.fitPrimaryBounds && primaryLatLngs.length) {
      this._fitPrimary(primaryLatLngs);
    }

    mapsManager.resizeMap(this.map);
  }

  _bindInteractivePreview(marker) {
    const setPinActive = (active) => {
      const el = marker.getElement && marker.getElement();
      if (!el) return;
      el.classList.toggle('cag-map-pin--active', !!active);
    };

    marker.on('popupopen', () => {
      this._hydratePreviewImages(marker);
      this._wirePreviewPointerBridge(marker);
      setPinActive(true);
      this._activePreviewMarker = marker;
    });

    marker.on('popupclose', () => {
      setPinActive(false);
      marker._cagSticky = false;
      if (this._activePreviewMarker === marker) {
        this._activePreviewMarker = null;
      }
    });

    marker.on('click', (e) => {
      if (e && e.originalEvent) {
        L.DomEvent.stopPropagation(e.originalEvent);
      }
      this.markers.forEach((m) => {
        if (m !== marker) {
          m._cagSticky = false;
          if (m.isPopupOpen && m.isPopupOpen()) {
            m.closePopup();
          }
        }
      });
      marker._cagSticky = true;
      if (!marker.isPopupOpen()) {
        marker.openPopup();
      }
      setPinActive(true);
      this._panPopupIntoView(marker);
    });

    if (!this._canHover) {
      return;
    }

    marker.on('mouseover', () => {
      this._clearHoverTimers();
      this._hoverOpenTimer = setTimeout(() => {
        if (this._activePreviewMarker && this._activePreviewMarker !== marker && this._activePreviewMarker._cagSticky) {
          return;
        }
        this.markers.forEach((m) => {
          if (m !== marker && m.isPopupOpen && m.isPopupOpen() && !m._cagSticky) {
            m.closePopup();
          }
        });
        if (!marker.isPopupOpen()) {
          marker.openPopup();
        }
        setPinActive(true);
      }, 70);
    });

    marker.on('mouseout', () => {
      this._clearHoverTimers();
      this._hoverCloseTimer = setTimeout(() => {
        if (!marker._cagSticky && !marker._cagPointerOnPopup) {
          this._closePreview(marker);
        }
      }, 140);
    });
  }

  _wirePreviewPointerBridge(marker) {
    const popup = marker.getPopup && marker.getPopup();
    const el = popup && popup.getElement && popup.getElement();
    if (!el || el._cagBridgeWired) {
      return;
    }
    el._cagBridgeWired = true;

    L.DomEvent.on(el, 'mouseenter', () => {
      marker._cagPointerOnPopup = true;
      this._clearHoverTimers();
    });
    L.DomEvent.on(el, 'mouseleave', () => {
      marker._cagPointerOnPopup = false;
      this._clearHoverTimers();
      this._hoverCloseTimer = setTimeout(() => {
        if (!marker._cagSticky) {
          this._closePreview(marker);
        }
      }, 120);
    });

    const dismiss = el.querySelector('.cag-map-preview__dismiss');
    if (dismiss) {
      L.DomEvent.on(dismiss, 'click', (e) => {
        L.DomEvent.stop(e);
        marker._cagSticky = false;
        this._closePreview(marker);
      });
    }

    // Keep popup clicks from bubbling to the map (which would clear sticky)
    L.DomEvent.disableClickPropagation(el);
  }

  _hydratePreviewImages(marker) {
    const popup = marker.getPopup && marker.getPopup();
    const el = popup && popup.getElement && popup.getElement();
    if (!el) return;

    el.querySelectorAll('img[data-src]').forEach((img) => {
      if (img.getAttribute('src')) return;
      const src = img.getAttribute('data-src');
      if (!src) return;
      img.onload = () => img.classList.add('is-loaded');
      img.onerror = () => img.classList.add('is-error');
      img.setAttribute('src', src);
      img.removeAttribute('data-src');
    });
  }

  _panPopupIntoView(marker) {
    if (!this.map || !marker) return;
    const popup = marker.getPopup && marker.getPopup();
    const el = popup && popup.getElement && popup.getElement();
    if (!el) return;

    const pad = { x: 28, y: 88 };
    const rect = el.getBoundingClientRect();
    const mapRect = this.map.getContainer().getBoundingClientRect();
    let dx = 0;
    let dy = 0;

    if (rect.left < mapRect.left + pad.x) {
      dx = rect.left - (mapRect.left + pad.x);
    } else if (rect.right > mapRect.right - pad.x) {
      dx = rect.right - (mapRect.right - pad.x);
    }
    if (rect.top < mapRect.top + pad.y) {
      dy = rect.top - (mapRect.top + pad.y);
    } else if (rect.bottom > mapRect.bottom - pad.y) {
      dy = rect.bottom - (mapRect.bottom - pad.y);
    }

    if (dx !== 0 || dy !== 0) {
      this.map.panBy([dx, dy], { animate: true, duration: 0.25 });
    }
  }

  _closePreview(marker) {
    if (!marker) return;
    const el = marker.getElement && marker.getElement();
    if (el) el.classList.remove('cag-map-pin--active');
    if (marker.isPopupOpen && marker.isPopupOpen()) {
      marker.closePopup();
    }
  }

  _clearStickyPreviews() {
    this.markers.forEach((m) => {
      m._cagSticky = false;
      if (m.isPopupOpen && m.isPopupOpen()) {
        m.closePopup();
      }
    });
  }

  _clearHoverTimers() {
    if (this._hoverOpenTimer) {
      clearTimeout(this._hoverOpenTimer);
      this._hoverOpenTimer = null;
    }
    if (this._hoverCloseTimer) {
      clearTimeout(this._hoverCloseTimer);
      this._hoverCloseTimer = null;
    }
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
      const price = g.lowest_price != null ? g.lowest_price : g.price;
      const normalizedPrice = price != null && price !== '' && Number(price) > 0 ? price : null;

      // Prefer structured fields so interactive preview can render; ignore legacy popupHtml
      return {
        id: g.id,
        lat: g.lat,
        lng: g.lng,
        variant: g.variant || (g.is_gray || g.isGray ? 'gray' : 'primary'),
        pillar: g.pillar || 'guiding',
        title: g.title || '',
        location: g.location || '',
        price: normalizedPrice,
        priceLabel: g.priceLabel || (normalizedPrice != null ? `ab ${normalizedPrice}€ p.P.` : null),
        badge: g.badge || '',
        cta: g.cta || '',
        url: g.url || g.link || '#',
        image: g.thumbnail || g.thumbnail_path || g.image || '',
      };
    });

    this.setMarkers(markers);
  }

  /**
   * Build popup card HTML from structured marker fields (avoids server-side Blade per pin).
   */
  buildPopupHtml(item = {}) {
    const title = this._escape(item.title || '');
    const location = this._escape(item.location || '');
    const url = this._escape(item.url || item.link || '#');
    const image = this._escape(item.image || item.thumbnail || item.thumbnail_path || '');
    const price = item.price != null && item.price !== '' ? item.price : null;
    if (!title && !image && !location && price == null) {
      return null;
    }

    const priceLine =
      price != null
        ? `<div class="cag-map-popup__price"><span class="fw-bold">ab ${this._escape(String(price))}€</span> p.P.</div>`
        : '';

    return `
      <div class="cag-map-popup__card">
        ${image ? `<img class="cag-map-popup__image" src="${image}" alt="">` : ''}
        <div class="cag-map-popup__body">
          <a class="text-decoration-none" href="${url}">
            <h5 class="cag-map-popup__title">${title}</h5>
          </a>
          ${location ? `<div class="cag-map-popup__location">${location}</div>` : ''}
          ${priceLine}
        </div>
      </div>`;
  }

  /**
   * Rich interactive preview card — images hydrate on first open (keeps map boot light).
   * Shared by vacations + guidings listing maps.
   */
  buildInteractivePreviewHtml(item = {}) {
    const title = this._escape(item.title || '');
    const location = this._escape(item.location || '');
    const url = this._escape(item.url || item.link || '#');
    const image = this._escape(item.image || item.thumbnail || item.thumbnail_path || '');
    const badge = this._escape(item.badge || '');
    const cta = this._escape(item.cta || '');
    const priceLabel = this._escape(item.priceLabel || '');
    const pillar =
      item.pillar === 'trip' || item.pillar === 'camp' || item.pillar === 'guiding' ? item.pillar : '';
    const badgeTone = pillar === 'trip' || pillar === 'camp' ? pillar : 'primary';
    const price = item.price != null && item.price !== '' ? item.price : null;

    if (!title && !image && !location && !priceLabel && price == null) {
      return null;
    }

    const fallbackPrice =
      !priceLabel && price != null
        ? `<span class="cag-map-preview__price-value">ab ${this._escape(String(price))}€</span>`
        : '';

    const priceBlock =
      priceLabel || fallbackPrice
        ? `<div class="cag-map-preview__price">${priceLabel || fallbackPrice}</div>`
        : '';

    const cardWidth = this._previewCardWidth();

    return `
      <div class="cag-map-preview" data-pillar="${pillar}" style="width:${cardWidth}px">
        <button type="button" class="cag-map-preview__dismiss" aria-label="Close" tabindex="0">&times;</button>
        <a class="cag-map-preview__link" href="${url}">
          <div class="cag-map-preview__media${image ? '' : ' cag-map-preview__media--empty'}">
            ${
              image
                ? `<img class="cag-map-preview__image" data-src="${image}" alt="" decoding="async" width="${cardWidth}" height="150">`
                : ''
            }
            ${badge ? `<span class="cag-map-preview__badge cag-map-preview__badge--${badgeTone}">${badge}</span>` : ''}
          </div>
          <div class="cag-map-preview__body">
            <h5 class="cag-map-preview__title">${title}</h5>
            ${location ? `<div class="cag-map-preview__location"><span aria-hidden="true"></span>${location}</div>` : ''}
            <div class="cag-map-preview__footer">
              ${priceBlock}
              ${cta ? `<span class="cag-map-preview__cta">${cta}</span>` : ''}
            </div>
          </div>
        </a>
      </div>`;
  }

  /** @deprecated Use buildInteractivePreviewHtml */
  buildVacationPreviewHtml(item = {}) {
    return this.buildInteractivePreviewHtml(item);
  }

  _previewCardWidth() {
    if (typeof window === 'undefined') {
      return 260;
    }
    const vw = window.innerWidth || 1024;
    if (vw <= 360) return Math.max(210, vw - 56);
    if (vw <= 480) return Math.max(230, Math.min(260, vw - 48));
    return 260;
  }

  _escape(value) {
    return String(value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  invalidate() {
    mapsManager.resizeMap(this.map);
  }
}

export default ListingMap;
export { ListingMap };
