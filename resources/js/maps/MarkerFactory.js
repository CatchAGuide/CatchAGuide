/**
 * MarkerFactory — primary / gray / trip / camp / tour / price-chip Leaflet divIcons
 */
import mapsManager, { L } from './MapsManager';

class MarkerFactory {
  /**
   * Canonical map color key: gray | tour | trip | camp
   * @param {string} [value]
   * @param {string} [module]
   * @returns {'gray'|'tour'|'trip'|'camp'}
   */
  resolveColorVariant(value, module) {
    const variant = String(value || '')
      .toLowerCase()
      .trim();
    if (variant === 'gray') {
      return 'gray';
    }
    const raw = String(module || value || '')
      .toLowerCase()
      .trim();
    if (raw === 'gray') return 'gray';
    if (raw === 'trip' || raw === 'trips') return 'trip';
    if (raw === 'camp' || raw === 'camps' || raw === 'vacation') return 'camp';
    // guiding / tour / primary / empty → tour (brand coral)
    return 'tour';
  }

  formatPriceChip(price, locale = 'de') {
    if (price == null || price === '') {
      return null;
    }
    const num = Number(price);
    if (!Number.isFinite(num) || num <= 0) {
      return null;
    }
    try {
      return new Intl.NumberFormat(locale === 'en' ? 'en-GB' : 'de-DE', {
        style: 'currency',
        currency: 'EUR',
        maximumFractionDigits: 0,
      }).format(num);
    } catch (e) {
      return `€${Math.round(num)}`;
    }
  }

  createIcon(variant = 'tour', options = {}) {
    const normalized = this.resolveColorVariant(variant, options.module);
    const isGray = normalized === 'gray';
    const priceLabel = options.priceLabel || null;
    const selected = !!options.selected;

    if (priceLabel && !isGray) {
      const safe = String(priceLabel)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
      const selectedClass = selected ? ' cag-map-chip--selected' : '';
      const width = Math.min(120, Math.max(52, String(priceLabel).length * 8 + 28));
      return L.divIcon({
        className: `leaflet-div-icon cag-map-chip cag-map-chip--${normalized}${selectedClass}`,
        html: `<div class="cag-map-chip__inner"><span class="cag-map-chip__price">${safe}</span></div>`,
        iconSize: [width, 32],
        iconAnchor: [Math.round(width / 2), 16],
        popupAnchor: [0, -18],
      });
    }

    return L.divIcon({
      className: `leaflet-div-icon cag-map-pin cag-map-pin--${normalized}${selected ? ' cag-map-pin--selected' : ''}`,
      html: `<div class="cag-map-pin__inner"><span class="cag-map-pin__glyph" aria-hidden="true"></span></div>`,
      iconSize: isGray ? [32, 44] : [28, 40],
      iconAnchor: isGray ? [16, 40] : [14, 36],
      popupAnchor: [0, -34],
    });
  }

  /**
   * @param {Object} options
   * @param {L.Map} options.map
   * @param {{lat:number,lng:number}|L.LatLng} options.position
   * @param {string} [options.variant]
   * @param {string} [options.module]
   * @param {string} [options.title]
   * @param {string} [options.popupHtml]
   * @param {Object} [options.popupOptions]
   * @param {number} [options.zIndexOffset]
   * @param {string|null} [options.priceLabel]
   * @param {boolean} [options.priceChip]
   * @param {boolean} [options.selected]
   * @returns {L.Marker}
   */
  createMarker(options = {}) {
    const pos = options.position;
    const lat = typeof pos.lat === 'function' ? pos.lat() : pos.lat;
    const lng = typeof pos.lng === 'function' ? pos.lng() : pos.lng;
    const colorVariant = this.resolveColorVariant(options.variant, options.module);
    const priceLabel =
      options.priceChip && options.priceLabel
        ? options.priceLabel
        : options.priceChip && options.price != null
          ? this.formatPriceChip(options.price, mapsManager.config.locale || 'de')
          : null;

    const marker = L.marker([lat, lng], {
      icon: this.createIcon(colorVariant, {
        priceLabel,
        selected: options.selected,
        module: options.module,
      }),
      title: options.title || '',
      zIndexOffset: options.zIndexOffset != null ? options.zIndexOffset : colorVariant === 'gray' ? 100 : 0,
      riseOnHover: true,
    });

    marker._cagPriceLabel = priceLabel;
    marker._cagPriceChip = !!priceLabel;
    marker.options.cagVariant = colorVariant;
    marker.options.cagModule = options.module || colorVariant;

    if (options.map) {
      marker.addTo(options.map);
    }

    if (options.popupHtml) {
      marker.bindPopup(options.popupHtml, {
        className: 'cag-map-popup',
        maxWidth: 220,
        ...(options.popupOptions || {}),
      });
    }

    return marker;
  }

  setSelected(marker, selected) {
    if (!marker) return;
    const variant = (marker.options && marker.options.cagVariant) || 'tour';
    const priceLabel = marker._cagPriceLabel || null;
    marker.setIcon(
      this.createIcon(variant, {
        priceLabel: marker._cagPriceChip ? priceLabel : null,
        selected: !!selected,
        module: marker.options && marker.options.cagModule,
      })
    );
    const el = marker.getElement && marker.getElement();
    if (el) {
      el.classList.toggle('cag-map-chip--selected', !!selected && !!marker._cagPriceChip);
      el.classList.toggle('cag-map-pin--selected', !!selected && !marker._cagPriceChip);
      el.classList.toggle('cag-map-pin--active', !!selected);
    }
  }

  getRandomOffset(amount = 0.008) {
    return (Math.random() - 0.5) * amount;
  }
}

const markerFactory = new MarkerFactory();
export default markerFactory;
export { MarkerFactory };
