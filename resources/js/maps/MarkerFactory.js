/**
 * MarkerFactory — primary / gray / trip / camp Leaflet divIcon pins + popup binding
 */
import mapsManager, { L } from './MapsManager';

class MarkerFactory {
  createIcon(variant = 'primary') {
    const normalized = ['gray', 'trip', 'camp'].includes(variant) ? variant : 'primary';
    const isGray = normalized === 'gray';
    return L.divIcon({
      className: `leaflet-div-icon cag-map-pin cag-map-pin--${normalized}`,
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
   * @param {string} [options.title]
   * @param {string} [options.popupHtml]
   * @param {Object} [options.popupOptions]
   * @param {number} [options.zIndexOffset]
   * @returns {L.Marker}
   */
  createMarker(options = {}) {
    const pos = options.position;
    const lat = typeof pos.lat === 'function' ? pos.lat() : pos.lat;
    const lng = typeof pos.lng === 'function' ? pos.lng() : pos.lng;
    const variant = options.variant || 'primary';

    const marker = L.marker([lat, lng], {
      icon: this.createIcon(variant),
      title: options.title || '',
      zIndexOffset: options.zIndexOffset != null ? options.zIndexOffset : variant === 'gray' ? 100 : 0,
      riseOnHover: true,
    });

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

  getRandomOffset(amount = 0.008) {
    return (Math.random() - 0.5) * amount;
  }
}

const markerFactory = new MarkerFactory();
export default markerFactory;
export { MarkerFactory };
