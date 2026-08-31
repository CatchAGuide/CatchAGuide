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
    // guiding / tour / primary / empty → tour (ink slate identity)
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
    const isOther = normalized === 'gray';
    const priceLabel = options.priceLabel || null;
    const selected = !!options.selected;
    const viewed = !!options.viewed;
    const pillMode = !!options.pillMode;

    if (priceLabel) {
      return this._createPillIcon(normalized, priceLabel, { selected, viewed, isOther });
    }

    // Listing/search maps: shape carries meaning (pill = priced offer, circle =
    // cluster) so an unpriced item still renders as a small dot, never a plain pin.
    if (pillMode) {
      return this._createDotIcon(normalized, { selected, isOther });
    }

    return L.divIcon({
      className: `leaflet-div-icon cag-map-pin cag-map-pin--${normalized}${selected ? ' cag-map-pin--selected' : ''}`,
      html: `<div class="cag-map-pin__inner"><span class="cag-map-pin__glyph" aria-hidden="true"></span></div>`,
      iconSize: isOther ? [32, 44] : [28, 40],
      iconAnchor: isOther ? [16, 40] : [14, 36],
      popupAnchor: [0, -34],
    });
  }

  /**
   * Price pill divIcon. Geometry/hit-area matches the map marker spec: a small
   * visual pill (22px / 19px for "other") centred inside a >=44x44 tap target.
   */
  _createPillIcon(variant, priceLabel, { selected = false, viewed = false, isOther = false } = {}) {
    const safe = String(priceLabel)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');

    const classes = [
      'leaflet-div-icon',
      'cag-map-chip',
      `cag-map-chip--${variant}`,
      isOther ? 'cag-map-chip--other' : '',
      selected ? 'cag-map-chip--selected' : '',
      viewed ? 'cag-map-chip--viewed' : '',
    ]
      .filter(Boolean)
      .join(' ');

    const visualHeight = isOther ? 20 : 24;
    const visualWidth = Math.min(
      136,
      Math.max(isOther ? 36 : 44, String(priceLabel).length * (isOther ? 7 : 8) + (isOther ? 22 : 28))
    );
    const hitWidth = Math.max(visualWidth, 44);
    const hitHeight = 44;

    return L.divIcon({
      className: classes,
      html: `<div class="cag-map-chip__inner"><span class="cag-map-chip__price">${safe}</span></div>`,
      iconSize: [hitWidth, hitHeight],
      iconAnchor: [Math.round(hitWidth / 2), Math.round(hitHeight / 2)],
      popupAnchor: [0, -(Math.round(visualHeight / 2) + 10)],
    });
  }

  /**
   * No-price fallback divIcon — a small colour dot, centred inside a 44x44 tap target.
   */
  _createDotIcon(variant, { selected = false, isOther = false } = {}) {
    const classes = [
      'leaflet-div-icon',
      'cag-map-dot',
      `cag-map-dot--${variant}`,
      isOther ? 'cag-map-dot--other' : '',
      selected ? 'cag-map-dot--selected' : '',
    ]
      .filter(Boolean)
      .join(' ');

    return L.divIcon({
      className: classes,
      html: `<span class="cag-map-dot__inner" aria-hidden="true"></span>`,
      iconSize: [44, 44],
      iconAnchor: [22, 22],
      popupAnchor: [0, -14],
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
   * @param {boolean} [options.pillMode] Listing/search map: always pill or dot, never a plain pin.
   * @param {boolean} [options.selected]
   * @returns {L.Marker}
   */
  createMarker(options = {}) {
    const pos = options.position;
    const lat = typeof pos.lat === 'function' ? pos.lat() : pos.lat;
    const lng = typeof pos.lng === 'function' ? pos.lng() : pos.lng;
    const colorVariant = this.resolveColorVariant(options.variant, options.module);
    const isOther = colorVariant === 'gray';
    const pillMode = !!options.pillMode;
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
        pillMode,
      }),
      title: options.title || '',
      // z-order (bottom -> top): "other" offers, then priced type markers.
      zIndexOffset: options.zIndexOffset != null ? options.zIndexOffset : isOther ? 0 : 100,
      riseOnHover: true,
    });

    marker._cagPriceLabel = priceLabel;
    marker._cagPriceChip = !!priceLabel;
    marker._cagPillMode = pillMode;
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
        pillMode: !!marker._cagPillMode,
      })
    );
    const el = marker.getElement && marker.getElement();
    if (el) {
      if (marker._cagPriceChip) {
        el.classList.toggle('cag-map-chip--selected', !!selected);
      } else if (marker._cagPillMode) {
        el.classList.toggle('cag-map-dot--selected', !!selected);
      } else {
        el.classList.toggle('cag-map-pin--selected', !!selected);
        el.classList.toggle('cag-map-pin--active', !!selected);
      }
    }
  }

  getRandomOffset(amount = 0.008) {
    return (Math.random() - 0.5) * amount;
  }
}

const markerFactory = new MarkerFactory();
export default markerFactory;
export { MarkerFactory };
