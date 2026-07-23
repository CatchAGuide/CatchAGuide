/**
 * ProductMap — single-marker PDP / product page map
 */
import mapsManager from './MapsManager';
import markerFactory from './MarkerFactory';

class ProductMap {
  /**
   * @param {HTMLElement} el
   */
  constructor(el) {
    this.el = el;
    this.map = null;
    this.marker = null;
    this.options = this._parseOptions(el);
  }

    _parseOptions(el) {
    const ds = el.dataset;
    const popupTpl = el.querySelector('template[data-maps-popup]');
    return {
      lat: parseFloat(ds.lat),
      lng: parseFloat(ds.lng),
      zoom: ds.zoom != null ? parseInt(ds.zoom, 10) : 10,
      title: ds.title || '',
      popupHtml: popupTpl ? popupTpl.innerHTML.trim() : null,
      scrollWheel: ds.scrollWheel === 'true' || ds.scrollWheel === '1',
      dragging: ds.dragging !== 'false' && ds.dragging !== '0',
      markerVariant: ds.markerVariant || 'primary',
      onMarkerClick: ds.onMarkerClick || 'popup',
      modalTarget: ds.modalTarget || null,
      lazy: ds.lazy !== 'false' && ds.lazy !== '0',
    };
  }

  init() {
    if (this.map || !this.el) {
      return this;
    }

    if (Number.isNaN(this.options.lat) || Number.isNaN(this.options.lng)) {
      console.warn('ProductMap: invalid lat/lng', this.options);
      return this;
    }

    const start = () => this._create();

    if (this.options.lazy && typeof IntersectionObserver !== 'undefined') {
      const observer = new IntersectionObserver(
        (entries) => {
          if (entries.some((e) => e.isIntersecting)) {
            observer.disconnect();
            start();
          }
        },
        { rootMargin: '120px' }
      );
      observer.observe(this.el);
    } else {
      start();
    }

    return this;
  }

  _create() {
    this.map = mapsManager.initMap(this.el, {
      center: { lat: this.options.lat, lng: this.options.lng },
      zoom: this.options.zoom,
      scrollWheelZoom: this.options.scrollWheel,
      dragging: this.options.dragging,
    });

    this.marker = markerFactory.createMarker({
      map: this.map,
      position: { lat: this.options.lat, lng: this.options.lng },
      variant: this.options.markerVariant,
      title: this.options.title,
      popupHtml:
        this.options.onMarkerClick === 'popup'
          ? this.options.popupHtml || (this.options.title ? `<strong>${this._escape(this.options.title)}</strong>` : null)
          : null,
    });

    if (this.options.onMarkerClick === 'modal' && this.options.modalTarget) {
      this.marker.on('click', () => {
        const modalEl = document.querySelector(this.options.modalTarget);
        if (modalEl && window.bootstrap && window.bootstrap.Modal) {
          window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
        } else if (modalEl && typeof window.jQuery !== 'undefined') {
          window.jQuery(modalEl).modal('show');
        }
      });
    }

    mapsManager.resizeMap(this.map);
  }

  _escape(str) {
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
  }

  invalidate() {
    mapsManager.resizeMap(this.map);
  }
}

export default ProductMap;
export { ProductMap };
