/**
 * ListingMap — multi-marker listing / modal map with clustering, viewport rail, price chips
 */
import mapsManager, { L } from './MapsManager';
import markerFactory from './MarkerFactory';
import LandmarkLayer from './LandmarkLayer';
import MapModalRail from './MapModalRail';
import MapModalFilters from './MapModalFilters';
import { itemKey, itemsMatch } from './mapItemIdentity';

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
    this._selectedMarker = null;
    this._detachedPreview = null;
    this._clusterPreviewEl = null;
    this._hoverOpenTimer = null;
    this._hoverCloseTimer = null;
    this._viewportTimer = null;
    this._userInteracted = false;
    this._viewportListeners = [];
    this._selectionListeners = [];
    this._previewListeners = [];
    this._landmarkLayer = null;
    this._overlayPadding = { top: 0, bottom: 0 };
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
      priceChips: ds.priceChips === 'true' || ds.priceChips === '1',
      landmarks: ds.landmarks === 'true' || ds.landmarks === '1',
      viewportRail: ds.viewportRail === 'true' || ds.viewportRail === '1',
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
            this.emitViewportChange();
          }
        });
        this._bootModalHelpers(modal);
      } else {
        start();
      }
    } else {
      start();
    }

    if (this.options.updatable) {
      window.__cagListingMaps = window.__cagListingMaps || {};
      window.__cagListingMaps[this.options.instanceKey] = this;
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
          this.setMarkersFromGuidings(payload, { preserveView: this._userInteracted });
        };
      }
    }

    return this;
  }

  _bootModalHelpers(modal) {
    if (modal._cagMapHelpersBooted) {
      return;
    }
    modal._cagMapHelpersBooted = true;

    if (this.options.viewportRail) {
      modal._cagMapRail = new MapModalRail(modal, this);
    }
    if (modal.getAttribute('data-map-show-chips') === 'true') {
      modal._cagMapFilters = new MapModalFilters(modal);
    }
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

    this.map.on('dragend', () => {
      if (this._programmaticView) return;
      this._userInteracted = true;
    });
    this.map.on('zoomend', () => {
      // fitBounds also fires zoomend; mark interaction only after first paint settle
      if (this._programmaticView) return;
      if (this._markersReady) {
        this._userInteracted = true;
      }
    });

    if (this.options.viewportRail) {
      this.map.on('moveend', () => this._scheduleViewportEmit());
      this.map.on('zoomend', () => this._scheduleViewportEmit());
      this._scheduleViewportEmit();
    }

    if (this.options.interactivePreview) {
      this.map.on('click', () => {
        this._clearStickyPreviews();
        if (this.options.viewportRail) {
          this.clearSelection();
        }
      });
      this.map.on('movestart', () => {
        if (this._ignoreMoveClose) return;
        if (this._activePreviewMarker && !this._activePreviewMarker._cagSticky) {
          this._closePreview(this._activePreviewMarker);
        }
      });
    } else if (this.options.viewportRail) {
      this.map.on('click', () => this.clearSelection());
    }

    if (this.options.landmarks) {
      this._landmarkLayer = new LandmarkLayer(this);
      this._landmarkLayer.attach();
    }

    this._markersReady = true;
  }

  onViewportChange(fn) {
    if (typeof fn === 'function') {
      this._viewportListeners.push(fn);
    }
  }

  onSelectionChange(fn) {
    if (typeof fn === 'function') {
      this._selectionListeners.push(fn);
    }
  }

  onPreviewChange(fn) {
    if (typeof fn === 'function') {
      this._previewListeners.push(fn);
    }
  }

  _emitPreview(item) {
    this._previewListeners.forEach((fn) => {
      try {
        fn(item || null);
      } catch (e) {
        /* ignore */
      }
    });
  }

  _scheduleViewportEmit() {
    if (this._viewportTimer) clearTimeout(this._viewportTimer);
    this._viewportTimer = setTimeout(() => this.emitViewportChange(), 220);
  }

  emitViewportChange() {
    const payload = this.getVisiblePrimaryItems();
    this._viewportListeners.forEach((fn) => {
      try {
        fn(payload);
      } catch (e) {
        /* ignore */
      }
    });
  }

  getPrimaryItems() {
    return this.markers
      .filter((m) => m.options && m.options.cagVariant !== 'gray' && m._cagItem)
      .map((m) => m._cagItem);
  }

  getVisiblePrimaryItems() {
    if (!this.map) {
      const items = this._dedupeItems(this.getPrimaryItems());
      return { items, ids: items.map((i) => itemKey(i)), count: items.length };
    }

    const bounds = this.map.getBounds();
    const items = [];
    this.markers.forEach((m) => {
      if (!m._cagItem || (m.options && m.options.cagVariant === 'gray')) return;
      const ll = m.getLatLng();
      if (bounds.contains(ll)) {
        items.push(m._cagItem);
      }
    });
    const unique = this._dedupeItems(items);
    return {
      items: unique,
      ids: unique.map((i) => itemKey(i)),
      count: unique.length,
    };
  }

  _dedupeItems(items) {
    const seen = new Set();
    const out = [];
    (items || []).forEach((item) => {
      const key = itemKey(item);
      if (!key) return;
      if (seen.has(key)) return;
      seen.add(key);
      out.push(item);
    });
    return out;
  }

  _findMarkerById(id) {
    return this.markers.find((m) => m._cagItem && itemsMatch(m._cagItem, id));
  }

  clearMarkers() {
    this._clearHoverTimers();
    this._closeDetachedPreview();
    this._setClusterPreviewHighlight(null);
    this._activePreviewMarker = null;
    this._selectedMarker = null;

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
   * @param {Array} items
   * @param {{fit?: boolean|null}} [opts]
   */
  setMarkers(items, opts = {}) {
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
    const railMode = this.options.viewportRail;
    const usePriceChips = this.options.priceChips;
    const locale = mapsManager.config.locale || 'de';
    const priceTpl = mapsManager.config.priceFromTemplate || 'From :price';

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

      const isGray = item.variant === 'gray' || item.is_gray || item.isGray;
      const colorVariant = isGray
        ? 'gray'
        : markerFactory.resolveColorVariant(item.variant || item.pillar, item.module || item.pillar);
      if (!this.options.showGrayNearby && isGray) {
        return;
      }

      const chipPrice =
        item.price != null
          ? markerFactory.formatPriceChip(item.price, locale)
          : null;
      if (!item.priceLabel && chipPrice) {
        item.priceLabel = priceTpl.replace(':price', chipPrice);
      }

      const popupHtml =
        item.popupHtml ||
        (interactive ? this.buildInteractivePreviewHtml(item) : this.buildPopupHtml(item));
      const previewWidth = interactive ? this._previewCardWidth() : 220;
      const popupOptions = interactive
        ? {
            className: `cag-map-popup cag-map-popup--interactive${item.pillar ? ` cag-map-popup--${item.pillar}` : ''}`,
            maxWidth: previewWidth,
            minWidth: Math.min(236, previewWidth),
            closeButton: false,
            autoPan: true,
            autoPanPadding: [36, 36],
            offset: [0, -6],
          }
        : undefined;

      const marker = markerFactory.createMarker({
        position: { lat, lng },
        variant: colorVariant,
        module: item.module || item.pillar || colorVariant,
        title: item.title || '',
        popupHtml: popupHtml || null,
        popupOptions,
        zIndexOffset: isGray ? 100 : 0,
        priceChip: usePriceChips && !isGray,
        price: item.price,
        priceLabel: chipPrice,
      });
      marker.options = marker.options || {};
      marker.options.cagVariant = colorVariant;
      marker._cagItem = item;
      marker._cagSticky = false;

      if (railMode && interactive) {
        this._bindRailSelectionWithPreview(marker);
      } else if (railMode) {
        this._bindRailSelection(marker);
      } else if (interactive) {
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

      if (isGray) {
        grayMarkers.push(marker);
        return;
      }

      primaryMarkers.push(marker);
      primaryLatLngs.push(L.latLng(parseFloat(item.lat), parseFloat(item.lng)));
    });

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

    const shouldFit =
      opts.fit != null ? opts.fit : this.options.fitPrimaryBounds && !this._userInteracted && !opts.preserveView;

    if (shouldFit && primaryLatLngs.length) {
      this._fitPrimary(primaryLatLngs);
    }

    mapsManager.resizeMap(this.map);
    this.emitViewportChange();
    // Re-check after layout/fitBounds settle so the rail matches visible pins
    this._scheduleViewportEmit();
  }

  _bindRailSelection(marker) {
    marker.on('click', (e) => {
      if (e && e.originalEvent) {
        L.DomEvent.stopPropagation(e.originalEvent);
      }
      this.selectMarker(marker, { pan: false, source: 'map' });
    });

    if (!this._canHover) {
      return;
    }

    marker.on('mouseover', () => {
      if (this._selectedMarker === marker) return;
      markerFactory.setSelected(marker, true);
    });
    marker.on('mouseout', () => {
      if (this._selectedMarker === marker) return;
      markerFactory.setSelected(marker, false);
    });
  }

  /**
   * Rail highlight + interactive map popup (click opens both).
   */
  _bindRailSelectionWithPreview(marker) {
    this._bindInteractivePreview(marker);

    marker.off('click');
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
      if (!this._isMobileSheet() && !marker.isPopupOpen()) {
        marker.openPopup();
      }
      this.selectMarker(marker, { pan: false, source: 'map' });
      if (this._isMobileSheet()) {
        this._panIntoVisibleArea(marker);
      } else {
        this._panPopupIntoView(marker);
      }
    });
  }

  selectById(id, opts = {}) {
    const marker = this._findMarkerById(id);
    if (marker) {
      this.selectMarker(marker, opts);
    }
  }

  /**
   * Zoom/spiderfy to a pin from the rail (button or double-click).
   * Shows the real marker popup after the pin is visible.
   */
  zoomToById(id) {
    const marker = this._findMarkerById(id);
    if (!marker) return;
    this._closeDetachedPreview();
    this._setClusterPreviewHighlight(null);
    this.selectMarker(marker, { pan: true, allowZoom: true, source: 'rail-zoom' });
  }

  highlightById(id, on) {
    if (this._selectedMarker && itemsMatch(this._selectedMarker._cagItem, id)) {
      return;
    }
    const marker = this._findMarkerById(id);
    if (marker) {
      markerFactory.setSelected(marker, !!on);
    }
  }

  /**
   * Hover from rail: show pin popup in place — never pan/zoom.
   * If the pin is inside a cluster, open a detached preview on the cluster.
   * Sticky (clicked) previews stay open until outside click / other selection.
   */
  previewById(id, on = true) {
    const marker = this._findMarkerById(id);
    if (!marker) return;

    if (!on) {
      // Sticky/selected click preview must survive leaving the rail card
      if (marker._cagSticky || this._selectedMarker === marker) {
        markerFactory.setSelected(marker, true);
        return;
      }

      markerFactory.setSelected(marker, false);
      if (marker.isPopupOpen && marker.isPopupOpen()) {
        marker.closePopup();
      }
      if (this._detachedPreview && this._detachedPreview._cagDetachedFor === marker) {
        this._closeDetachedPreview();
        this._setClusterPreviewHighlight(null);
      }
      if (this._activePreviewMarker === marker) {
        this._activePreviewMarker = null;
      }

      if (this._selectedMarker && this._selectedMarker._cagSticky) {
        this._restoreStickyPreview();
      } else {
        this._emitPreview(null);
      }
      return;
    }

    // While a clicked sticky preview is open, ignore hover on other cards
    if (
      this._selectedMarker &&
      this._selectedMarker._cagSticky &&
      this._selectedMarker !== marker
    ) {
      return;
    }

    // Close other non-sticky marker popups
    this.markers.forEach((m) => {
      if (m !== marker && m.isPopupOpen && m.isPopupOpen() && !m._cagSticky) {
        m.closePopup();
        if (this._selectedMarker !== m) {
          markerFactory.setSelected(m, false);
        }
      }
    });

    markerFactory.setSelected(marker, true);
    this._activePreviewMarker = marker;
    this._emitPreview(marker._cagItem || null);

    const visible = this._isMarkerVisibleOnMap(marker);
    if (visible && marker.getPopup && marker.getPopup()) {
      this._closeDetachedPreview();
      this._setClusterPreviewHighlight(null);
      if (!marker.isPopupOpen()) {
        marker.openPopup();
      }
      this._hydratePreviewImages(marker);
      this._wirePreviewPointerBridge(marker);
      this._wirePreviewCarousel(marker);
      this._ensurePopupVisible(marker);
      return;
    }

    // Pin is clustered — show detached preview (hover or sticky)
    this._openDetachedPreview(marker);
  }

  _restoreStickyPreview() {
    const marker = this._selectedMarker;
    if (!marker || !marker._cagSticky) return;

    markerFactory.setSelected(marker, true);
    this._activePreviewMarker = marker;
    this._emitPreview(marker._cagItem || null);

    if (this._isMarkerVisibleOnMap(marker) && marker.getPopup && marker.getPopup()) {
      this._closeDetachedPreview();
      this._setClusterPreviewHighlight(null);
      if (!marker.isPopupOpen()) {
        marker.openPopup();
      }
      this._hydratePreviewImages(marker);
      this._wirePreviewPointerBridge(marker);
      this._wirePreviewCarousel(marker);
      this._ensurePopupVisible(marker);
      return;
    }

    this._openDetachedPreview(marker);
  }

  _getClusterParent(marker) {
    if (!marker || !this.cluster || typeof this.cluster.getVisibleParent !== 'function') {
      return null;
    }
    try {
      const parent = this.cluster.getVisibleParent(marker);
      if (parent && parent !== marker) {
        return parent;
      }
    } catch (e) {
      /* ignore */
    }
    return null;
  }

  _setClusterPreviewHighlight(clusterMarker) {
    if (this._clusterPreviewEl) {
      this._clusterPreviewEl.classList.remove('cag-map-cluster--preview');
      this._clusterPreviewEl = null;
    }
    if (!clusterMarker) return;
    const el = clusterMarker.getElement && clusterMarker.getElement();
    if (el) {
      el.classList.add('cag-map-cluster--preview');
      this._clusterPreviewEl = el;
    }
  }

  _openDetachedPreview(marker) {
    if (!this.map || !marker) return;

    const item = marker._cagItem || {};
    const html =
      (marker.getPopup && marker.getPopup() && marker.getPopup().getContent && marker.getPopup().getContent()) ||
      this.buildInteractivePreviewHtml(item);
    if (!html) return;

    this._closeDetachedPreview();

    const clusterParent = this._getClusterParent(marker);
    this._setClusterPreviewHighlight(clusterParent);

    const latlng = clusterParent && clusterParent.getLatLng
      ? clusterParent.getLatLng()
      : marker.getLatLng();

    const previewWidth = this._previewCardWidth();
    const pillar = item.pillar || item.module || '';
    const placement = this._resolvePopupPlacement(latlng, { offsetHeight: 300 });
    const offsetY = placement === 'below' ? 316 : -18;
    const belowClass = placement === 'below' ? ' cag-map-popup--below' : '';
    const popup = L.popup({
      className: `cag-map-popup cag-map-popup--interactive cag-map-popup--detached${belowClass}${
        pillar ? ` cag-map-popup--${pillar}` : ''
      }`,
      maxWidth: previewWidth,
      minWidth: Math.min(236, previewWidth),
      closeButton: false,
      autoPan: true,
      autoPanPadding: [36, 36],
      offset: [0, offsetY],
    })
      .setLatLng(latlng)
      .setContent(html);

    this._detachedPreview = popup;
    popup._cagDetachedFor = marker;

    popup.on('remove', () => {
      if (this._detachedPreview === popup) {
        this._detachedPreview = null;
      }
    });

    popup.openOn(this.map);

    // Allow DOM to mount before wiring carousel / lazy images
    requestAnimationFrame(() => {
      if (this._detachedPreview !== popup) return;
      const el = popup.getElement && popup.getElement();
      if (!el) return;
      this._hydratePreviewImagesFromEl(el);
      this._wirePreviewCarouselFromEl(marker, el);
      this._wireDetachedPreviewBridge(marker, el);
      this._ensurePopupVisible(popup);
    });
  }

  _closeDetachedPreview() {
    if (!this._detachedPreview) return;
    const popup = this._detachedPreview;
    this._detachedPreview = null;
    if (this.map) {
      this.map.closePopup(popup);
    }
  }

  _wireDetachedPreviewBridge(marker, el) {
    if (!el || el._cagDetachedBridgeWired) return;
    el._cagDetachedBridgeWired = true;

    const dismiss = el.querySelector('.cag-map-preview__dismiss');
    if (dismiss) {
      L.DomEvent.on(dismiss, 'click', (e) => {
        L.DomEvent.stop(e);
        marker._cagSticky = false;
        this.clearSelection();
      });
    }

    // Keep map click-to-dismiss from firing when interacting with the popup
    L.DomEvent.disableClickPropagation(el);
    L.DomEvent.on(el, 'click', (e) => {
      L.DomEvent.stopPropagation(e);
    });
  }

  _isMarkerVisibleOnMap(marker) {
    if (!marker || !this.map) return false;
    if (this.cluster && typeof this.cluster.getVisibleParent === 'function') {
      try {
        const parent = this.cluster.getVisibleParent(marker);
        return parent === marker;
      } catch (e) {
        /* fall through */
      }
    }
    return this.map.hasLayer(marker);
  }

  selectMarker(marker, opts = {}) {
    if (!marker) return;

    if (this._selectedMarker && this._selectedMarker !== marker) {
      markerFactory.setSelected(this._selectedMarker, false);
    }

    this._selectedMarker = marker;
    markerFactory.setSelected(marker, true);

    const fromRail = opts.source === 'rail';
    const fromRailZoom = opts.source === 'rail-zoom';
    // Preview-only rail interactions never zoom; "Show on map" / double-click do
    const allowZoom = fromRailZoom || (!fromRail && opts.allowZoom !== false);

    const openRailPreview = () => {
      if (this._isMobileSheet()) {
        if (fromRailZoom || opts.pan || opts.source === 'map') {
          this._panIntoVisibleArea(marker);
        }
        return;
      }
      if (!this.options.interactivePreview || (!fromRail && !fromRailZoom)) return;
      this.markers.forEach((m) => {
        if (m !== marker) {
          m._cagSticky = false;
          if (m.isPopupOpen && m.isPopupOpen()) {
            m.closePopup();
          }
        }
      });
      marker._cagSticky = true;
      this._closeDetachedPreview();
      this._setClusterPreviewHighlight(null);
      if (this._isMarkerVisibleOnMap(marker)) {
        if (!marker.isPopupOpen()) {
          marker.openPopup();
        }
        this._hydratePreviewImages(marker);
        this._wirePreviewPointerBridge(marker);
        this._wirePreviewCarousel(marker);
        this._ensurePopupVisible(marker);
      } else if (fromRail) {
        // Keep detached preview when not zooming into a cluster
        this._openDetachedPreview(marker);
      }
    };

    if (allowZoom && this.cluster && typeof this.cluster.zoomToShowLayer === 'function') {
      try {
        this.cluster.zoomToShowLayer(marker, () => {
          markerFactory.setSelected(marker, true);
          openRailPreview();
        });
      } catch (e) {
        if (opts.pan && this.map) {
          this.map.panTo(marker.getLatLng(), { animate: true });
        }
        openRailPreview();
      }
    } else {
      if (opts.pan && this.map) {
        this.map.panTo(marker.getLatLng(), { animate: true });
      }
      openRailPreview();
    }

    const item = marker._cagItem || null;
    this._selectionListeners.forEach((fn) => {
      try {
        fn(item, opts);
      } catch (e) {
        /* ignore */
      }
    });
  }

  clearSelection() {
    if (this._selectedMarker) {
      markerFactory.setSelected(this._selectedMarker, false);
      this._selectedMarker = null;
    }
    this._clearStickyPreviews();
    this._setClusterPreviewHighlight(null);
    this._activePreviewMarker = null;
    this._emitPreview(null);
    this._selectionListeners.forEach((fn) => {
      try {
        fn(null);
      } catch (e) {
        /* ignore */
      }
    });
  }

  _bindInteractivePreview(marker) {
    const setPinActive = (active) => {
      const el = marker.getElement && marker.getElement();
      if (!el) return;
      el.classList.toggle('cag-map-pin--active', !!active);
      el.classList.toggle('cag-map-chip--selected', !!active && marker._cagPriceChip);
    };

    marker.on('popupopen', () => {
      this._hydratePreviewImages(marker);
      this._wirePreviewPointerBridge(marker);
      this._wirePreviewCarousel(marker);
      setPinActive(true);
      this._activePreviewMarker = marker;
      this._emitPreview(marker._cagItem || null);
      this._ensurePopupVisible(marker);
    });

    marker.on('popupclose', () => {
      setPinActive(false);
      // Keep sticky flag while this listing remains the rail selection
      if (this._selectedMarker !== marker) {
        marker._cagSticky = false;
      }
      if (this._activePreviewMarker === marker) {
        this._activePreviewMarker = null;
      }
      if (this._selectedMarker !== marker) {
        this._emitPreview(null);
      }
    });

    marker.on('click', (e) => {
      if (e && e.originalEvent) {
        L.DomEvent.stopPropagation(e.originalEvent);
      }
      this._closeDetachedPreview();
      this._setClusterPreviewHighlight(null);
      this.markers.forEach((m) => {
        if (m !== marker) {
          m._cagSticky = false;
          if (m.isPopupOpen && m.isPopupOpen()) {
            m.closePopup();
          }
        }
      });
      marker._cagSticky = true;
      if (!this._isMobileSheet() && !marker.isPopupOpen()) {
        marker.openPopup();
      }
      setPinActive(true);
      this.selectMarker(marker, { pan: false, source: 'map' });
      if (this._isMobileSheet()) {
        this._panIntoVisibleArea(marker);
      } else {
        this._panPopupIntoView(marker);
      }
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
        this._emitPreview(marker._cagItem || null);
      }, 70);
    });

    marker.on('mouseout', () => {
      this._clearHoverTimers();
      this._hoverCloseTimer = setTimeout(() => {
        if (!marker._cagSticky && !marker._cagPointerOnPopup) {
          this._closePreview(marker);
          if (this._selectedMarker !== marker) {
            this._emitPreview(null);
          }
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

    L.DomEvent.disableClickPropagation(el);
  }

  _hydratePreviewImages(marker) {
    const popup = marker.getPopup && marker.getPopup();
    const el = popup && popup.getElement && popup.getElement();
    this._hydratePreviewImagesFromEl(el);
  }

  _hydratePreviewImagesFromEl(el) {
    if (!el) return;
    el.querySelectorAll('img[data-src]').forEach((img) => {
      if (img.getAttribute('src')) return;
      const src = img.getAttribute('data-src');
      if (!src) return;
      img.onload = () => {
        img.classList.add('is-loaded');
        // Re-fit after image expands the card height
        if (this._detachedPreview) {
          this._ensurePopupVisible(this._detachedPreview);
        } else if (this._activePreviewMarker) {
          this._ensurePopupVisible(this._activePreviewMarker);
        }
      };
      img.onerror = () => img.classList.add('is-error');
      img.setAttribute('src', src);
      img.removeAttribute('data-src');
    });
  }

  _wirePreviewCarousel(marker) {
    const popup = marker.getPopup && marker.getPopup();
    const root = popup && popup.getElement && popup.getElement();
    this._wirePreviewCarouselFromEl(marker, root);
  }

  _wirePreviewCarouselFromEl(marker, root) {
    if (!root) return;

    const carousel = root.querySelector('[data-cag-preview-carousel]');
    if (!carousel || carousel._cagCarouselWired) {
      return;
    }
    carousel._cagCarouselWired = true;

    const slides = Array.from(carousel.querySelectorAll('[data-cag-preview-slide]'));
    if (slides.length < 2) {
      return;
    }

    let index = Math.max(
      0,
      slides.findIndex((s) => s.classList.contains('is-active'))
    );
    if (index < 0) index = 0;

    const dots = Array.from(carousel.querySelectorAll('[data-cag-preview-dot]'));
    const prevBtn = carousel.querySelector('[data-cag-preview-prev]');
    const nextBtn = carousel.querySelector('[data-cag-preview-next]');
    const counter = carousel.querySelector('[data-cag-preview-counter]');

    const show = (nextIndex) => {
      index = ((nextIndex % slides.length) + slides.length) % slides.length;
      slides.forEach((slide, i) => {
        slide.classList.toggle('is-active', i === index);
      });
      dots.forEach((dot, i) => {
        dot.classList.toggle('is-active', i === index);
        dot.setAttribute('aria-current', i === index ? 'true' : 'false');
      });
      if (counter) {
        counter.textContent = `${index + 1}/${slides.length}`;
      }
      this._hydratePreviewImagesFromEl(root);
    };

    const bindNav = (btn, delta) => {
      if (!btn) return;
      L.DomEvent.on(btn, 'click', (e) => {
        L.DomEvent.stop(e);
        if (marker) marker._cagSticky = true;
        show(index + delta);
      });
    };

    bindNav(prevBtn, -1);
    bindNav(nextBtn, 1);

    dots.forEach((dot, i) => {
      L.DomEvent.on(dot, 'click', (e) => {
        L.DomEvent.stop(e);
        if (marker) marker._cagSticky = true;
        show(i);
      });
    });

    show(index);
  }

  _panPopupIntoView(marker) {
    this._ensurePopupVisible(marker);
  }

  /**
   * Keep preview fully inside the map viewport.
   * Prefer flipping below when clipped at the top, then pan any remaining overflow.
   * @param {L.Marker|L.Popup} source
   */
  _ensurePopupVisible(source) {
    if (!this.map || !source) return;

    const popup = typeof source.getPopup === 'function' ? source.getPopup() : source;
    if (!popup) return;

    const run = () => {
      if (!this.map) return;

      const stillDetached = popup === this._detachedPreview;
      const stillMarkerPopup =
        typeof source.getPopup === 'function' &&
        source.getPopup() === popup &&
        source.isPopupOpen &&
        source.isPopupOpen();
      if (!stillDetached && typeof source.getPopup === 'function' && !stillMarkerPopup) {
        return;
      }
      if (!stillDetached && typeof source.getPopup !== 'function' && popup !== this._detachedPreview) {
        return;
      }

      const el = popup.getElement && popup.getElement();
      if (!el) return;

      const root =
        el.classList && el.classList.contains('leaflet-popup')
          ? el
          : el.closest
            ? el.closest('.leaflet-popup')
            : el;
      if (!root) return;

      const mapRect = this.map.getContainer().getBoundingClientRect();
      let rect = root.getBoundingClientRect();
      const edgePad = 18;
      const clippedTop = rect.top < mapRect.top + edgePad;
      const clippedBottom = rect.bottom > mapRect.bottom - edgePad;
      const height = Math.max(root.offsetHeight || 0, 240);

      // If the card is cut off at the top, force it below the pin
      if (clippedTop && !clippedBottom) {
        this._applyPopupPlacement(popup, root, 'below', height);
      } else {
        const latlng = popup.getLatLng && popup.getLatLng();
        if (latlng) {
          const placement = this._resolvePopupPlacement(latlng, root);
          this._applyPopupPlacement(popup, root, placement, height);
        }
      }

      // Leaflet built-in pan as a first pass
      try {
        if (typeof popup._adjustPan === 'function') {
          const prevAutoPan = popup.options.autoPan;
          popup.options.autoPan = true;
          popup.options.autoPanPadding = L.point(36, 36);
          popup._adjustPan();
          popup.options.autoPan = prevAutoPan;
        }
      } catch (e) {
        /* ignore */
      }

      this._panPopupElementIntoView(root);
    };

    requestAnimationFrame(() => requestAnimationFrame(run));
    if (this._popupVisibilityTimers) {
      this._popupVisibilityTimers.forEach((t) => clearTimeout(t));
    }
    this._popupVisibilityTimers = [setTimeout(run, 80), setTimeout(run, 280), setTimeout(run, 560)];
  }

  _resolvePopupPlacement(latlng, el) {
    const pt = this.map.latLngToContainerPoint(latlng);
    const size = this.map.getSize();
    const height = Math.max((el && el.offsetHeight) || 0, 260);
    const pad = 24;
    const spaceAbove = pt.y - pad;
    const spaceBelow = size.y - pt.y - pad;

    // Flip when the card would not fit above the pin
    if (spaceAbove < height && spaceBelow >= Math.min(height, spaceAbove + 40)) {
      return 'below';
    }
    return 'above';
  }

  _applyPopupPlacement(popup, el, placement, height) {
    const h = height || (el && el.offsetHeight) || 280;
    // Leaflet anchors the tip at latlng; positive offset.y shifts the card downward.
    // To place the whole card under the pin, offset by roughly the card height.
    const offsetY = placement === 'below' ? Math.round(h) + 16 : -8;
    popup.options.offset = L.point(0, offsetY);

    const root =
      el && el.classList && el.classList.contains('leaflet-popup')
        ? el
        : el && el.closest
          ? el.closest('.leaflet-popup')
          : el;
    if (root && root.classList) {
      root.classList.toggle('cag-map-popup--below', placement === 'below');
    }

    if (typeof popup.options.className === 'string') {
      popup.options.className = popup.options.className
        .replace(/\s*cag-map-popup--below\b/g, '')
        .trim();
      if (placement === 'below') {
        popup.options.className += ' cag-map-popup--below';
      }
    }

    if (typeof popup._updatePosition === 'function') {
      popup._updatePosition();
    }
  }

  _panPopupElementIntoView(el) {
    if (!this.map || !el) return;

    const pad = { top: 20, right: 24, bottom: 24, left: 24 };
    const rect = el.getBoundingClientRect();
    const mapRect = this.map.getContainer().getBoundingClientRect();
    let dx = 0;
    let dy = 0;

    if (rect.left < mapRect.left + pad.left) {
      dx = rect.left - (mapRect.left + pad.left);
    } else if (rect.right > mapRect.right - pad.right) {
      dx = rect.right - (mapRect.right - pad.right);
    }

    if (rect.top < mapRect.top + pad.top) {
      dy = rect.top - (mapRect.top + pad.top);
    } else if (rect.bottom > mapRect.bottom - pad.bottom) {
      dy = rect.bottom - (mapRect.bottom - pad.bottom);
    }

    if (dx === 0 && dy === 0) return;

    this._ignoreMoveClose = true;
    this.map.panBy([dx, dy], { animate: true, duration: 0.3 });
    if (this._ignoreMoveCloseTimer) {
      clearTimeout(this._ignoreMoveCloseTimer);
    }
    this._ignoreMoveCloseTimer = setTimeout(() => {
      this._ignoreMoveClose = false;
    }, 400);
  }

  _closePreview(marker) {
    if (!marker) return;
    const el = marker.getElement && marker.getElement();
    if (el) {
      el.classList.remove('cag-map-pin--active');
      el.classList.remove('cag-map-chip--selected');
    }
    if (marker.isPopupOpen && marker.isPopupOpen()) {
      marker.closePopup();
    }
    if (this._detachedPreview && this._detachedPreview._cagDetachedFor === marker) {
      this._closeDetachedPreview();
      this._setClusterPreviewHighlight(null);
    }
  }

  _clearStickyPreviews() {
    this._closeDetachedPreview();
    this._setClusterPreviewHighlight(null);
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

  _isMobileSheet() {
    return !!(
      this.options.viewportRail &&
      typeof window !== 'undefined' &&
      window.matchMedia &&
      window.matchMedia('(max-width: 991.98px)').matches
    );
  }

  setOverlayPadding(pad = {}) {
    this._overlayPadding = {
      top: Math.max(0, pad.top || 0),
      bottom: Math.max(0, pad.bottom || 0),
    };
    this.refitIfPristine();
  }

  refitIfPristine() {
    if (this._userInteracted || !this.map || !this._initialized) return;
    const latLngs = this.markers
      .filter((m) => m.options && m.options.cagVariant !== 'gray' && m.getLatLng)
      .map((m) => m.getLatLng());
    if (latLngs.length) {
      this._fitPrimary(latLngs);
    }
  }

  _fitPadding() {
    const overlay = this._overlayPadding || {};
    return {
      paddingTopLeft: [24, 24 + (overlay.top || 0)],
      paddingBottomRight: [24, 24 + (overlay.bottom || 0)],
    };
  }

  _fitPrimary(latLngs) {
    const unique = new Set(latLngs.map((ll) => `${ll.lat},${ll.lng}`));
    const pad = this._fitPadding();
    this._beginProgrammaticView();
    if (unique.size === 1) {
      this.map.setView(latLngs[0], this.options.singleZoom);
      const dy = ((this._overlayPadding && this._overlayPadding.bottom) || 0)
        - ((this._overlayPadding && this._overlayPadding.top) || 0);
      if (dy) {
        this.map.panBy([0, dy / 2], { animate: false });
      }
      return;
    }
    const bounds = L.latLngBounds(latLngs);
    this.map.fitBounds(bounds, pad);
  }

  _beginProgrammaticView() {
    this._programmaticView = true;
    if (this._programmaticViewTimer) clearTimeout(this._programmaticViewTimer);
    this._programmaticViewTimer = setTimeout(() => {
      this._programmaticView = false;
    }, 500);
  }

  _panIntoVisibleArea(marker) {
    if (!this.map || !marker || typeof marker.getLatLng !== 'function') return;
    const overlay = this._overlayPadding || {};
    const size = this.map.getSize();
    const top = overlay.top || 0;
    const bottom = overlay.bottom || 0;
    const visibleH = Math.max(80, size.y - top - bottom);
    const target = this.map.latLngToContainerPoint(marker.getLatLng());
    const desiredY = top + visibleH * 0.5;
    const dx = target.x - size.x / 2;
    const dy = target.y - desiredY;
    if (Math.abs(dx) > 8 || Math.abs(dy) > 8) {
      this._ignoreMoveClose = true;
      this._beginProgrammaticView();
      this.map.panBy([dx, dy], { animate: true });
      if (this._ignoreMoveCloseTimer) clearTimeout(this._ignoreMoveCloseTimer);
      this._ignoreMoveCloseTimer = setTimeout(() => {
        this._ignoreMoveClose = false;
      }, 400);
    }
  }

  /**
   * AJAX remap from guidings filter payload (server objects).
   */
  setMarkersFromGuidings(guidings, opts = {}) {
    const locale = mapsManager.config.locale || 'de';
    const priceTpl = mapsManager.config.priceFromTemplate || 'From :price';

    const markers = (guidings || []).map((g) => {
      const price = g.lowest_price != null ? g.lowest_price : g.price;
      const normalizedPrice = price != null && price !== '' && Number(price) > 0 ? price : null;
      const chip = normalizedPrice != null ? markerFactory.formatPriceChip(normalizedPrice, locale) : null;
      const pillar = g.pillar || g.module || 'guiding';
      const module =
        g.module ||
        (pillar === 'trip' ? 'trip' : pillar === 'camp' ? 'camp' : 'tour');

      return {
        id: g.id,
        key: g.key || `${module}:${g.id}`,
        lat: g.lat,
        lng: g.lng,
        variant: g.variant || (g.is_gray || g.isGray ? 'gray' : 'primary'),
        pillar,
        module,
        moduleLabel: g.moduleLabel || g.badge || '',
        title: g.title || '',
        location: g.location || '',
        price: normalizedPrice,
        priceLabel: g.priceLabel || (chip ? priceTpl.replace(':price', chip) : null),
        badge: g.badge || g.moduleLabel || '',
        cta: g.cta || '',
        url: g.url || g.link || '#',
        image: g.thumbnail || g.thumbnail_path || g.image || '',
        images: Array.isArray(g.images) ? g.images : [],
        durationLabel: g.durationLabel || g.duration_label || '',
        guestsLabel: g.guestsLabel || g.guests_label || '',
        maxGuests: g.maxGuests || g.max_guests || null,
        rating: g.rating != null ? g.rating : null,
        reviewCount: g.reviewCount != null ? g.reviewCount : g.review_count != null ? g.review_count : null,
        boatLabel: g.boatLabel || g.boat_label || '',
      };
    });

    this.setMarkers(markers, opts);
  }

  buildPopupHtml(item = {}) {
    const title = this._escape(item.title || '');
    const location = this._escape(item.location || '');
    const url = this._escape(item.url || item.link || '#');
    const image = this._escape(item.image || item.thumbnail || item.thumbnail_path || '');
    const priceLabel = this._escape(item.priceLabel || '');
    if (!title && !image && !location && !priceLabel) {
      return null;
    }

    const priceLine = priceLabel ? `<div class="cag-map-popup__price"><span class="fw-bold">${priceLabel}</span></div>` : '';

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

  buildInteractivePreviewHtml(item = {}) {
    const title = this._escape(item.title || '');
    const location = this._escape(item.location || '');
    const url = this._escape(item.url || item.link || '#');
    const images = this._previewImages(item);
    const image = images[0] || '';
    const badge = this._escape(item.badge || '');
    const cta = this._escape(item.cta || '');
    const priceLabel = this._escape(item.priceLabel || '');
    const i18n = this._previewI18n();
    const prevLabel = this._escape(i18n.prev || 'Previous image');
    const nextLabel = this._escape(i18n.next || 'Next image');
    const pillar =
      item.pillar === 'trip' || item.pillar === 'camp' || item.pillar === 'tour' || item.pillar === 'guiding'
        ? item.pillar === 'guiding'
          ? 'tour'
          : item.pillar
        : '';
    const badgeTone = pillar === 'trip' || pillar === 'camp' || pillar === 'tour' ? pillar : 'primary';

    if (!title && !image && !location && !priceLabel) {
      return null;
    }

    const priceBlock = priceLabel ? `<div class="cag-map-preview__price">${priceLabel}</div>` : '';
    const cardWidth = this._previewCardWidth();
    const hasCarousel = images.length > 1;

    const slidesHtml = images.length
      ? images
          .map(
            (src, i) => `
            <div class="cag-map-preview__slide${i === 0 ? ' is-active' : ''}" data-cag-preview-slide>
              <img class="cag-map-preview__image" data-src="${this._escape(src)}" alt="" decoding="async" width="${cardWidth}" height="150">
            </div>`
          )
          .join('')
      : '';

    const navHtml = hasCarousel
      ? `
          <button type="button" class="cag-map-preview__nav cag-map-preview__nav--prev" data-cag-preview-prev aria-label="${prevLabel}" tabindex="0">
            <span aria-hidden="true">&#8249;</span>
          </button>
          <button type="button" class="cag-map-preview__nav cag-map-preview__nav--next" data-cag-preview-next aria-label="${nextLabel}" tabindex="0">
            <span aria-hidden="true">&#8250;</span>
          </button>
          <div class="cag-map-preview__dots" role="tablist">
            ${images
              .map(
                (_, i) =>
                  `<button type="button" class="cag-map-preview__dot${i === 0 ? ' is-active' : ''}" data-cag-preview-dot aria-label="${i + 1}" aria-current="${i === 0 ? 'true' : 'false'}" tabindex="0"></button>`
              )
              .join('')}
          </div>
          <span class="cag-map-preview__counter" data-cag-preview-counter>1/${images.length}</span>`
      : '';

    const mediaInner = images.length
      ? `<div class="cag-map-preview__carousel" data-cag-preview-carousel>
            <a class="cag-map-preview__media-link" href="${url}" tabindex="-1">${slidesHtml}</a>
            ${navHtml}
          </div>`
      : '';

    return `
      <div class="cag-map-preview" data-pillar="${pillar}" style="width:${cardWidth}px">
        <button type="button" class="cag-map-preview__dismiss" aria-label="Close" tabindex="0">&times;</button>
        <div class="cag-map-preview__media${image ? '' : ' cag-map-preview__media--empty'}">
          ${mediaInner}
          ${badge ? `<span class="cag-map-preview__badge cag-map-preview__badge--${badgeTone}">${badge}</span>` : ''}
        </div>
        <a class="cag-map-preview__link" href="${url}">
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

  _previewImages(item = {}) {
    const list = [];
    const seen = Object.create(null);
    const push = (src) => {
      if (!src || typeof src !== 'string') return;
      const trimmed = src.trim();
      if (!trimmed || seen[trimmed]) return;
      seen[trimmed] = true;
      list.push(trimmed);
    };

    if (Array.isArray(item.images)) {
      item.images.forEach(push);
    }
    push(item.image || item.thumbnail || item.thumbnail_path || '');
    return list.slice(0, 5);
  }

  _previewI18n() {
    if (this._previewI18nCache) {
      return this._previewI18nCache;
    }
    try {
      const modal = this.el && this.el.closest ? this.el.closest('[data-maps-i18n]') : null;
      const raw = modal && modal.getAttribute('data-maps-i18n');
      this._previewI18nCache = raw ? JSON.parse(raw) : {};
    } catch (e) {
      this._previewI18nCache = {};
    }
    return this._previewI18nCache;
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
