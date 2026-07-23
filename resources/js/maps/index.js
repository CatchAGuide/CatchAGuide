/**
 * CAG Maps bundle — Leaflet product/listing maps + deferred Places shim
 */
import mapsManager from './MapsManager';
import markerFactory from './MarkerFactory';
import ProductMap from './ProductMap';
import ListingMap from './ListingMap';
import placesAutocompleteService from './PlacesAutocompleteService';

window.CAGMaps = {
  MapsManager: mapsManager,
  MarkerFactory: markerFactory,
  ProductMap,
  ListingMap,
  Places: placesAutocompleteService,
};

/**
 * Compatibility shim — existing Blade code expects GoogleMapsManager for Places helpers.
 * Map creation should use CAGMaps / data-maps-* components; initMap still works via Leaflet.
 */
window.GoogleMapsManager = {
  config: mapsManager.config,
  waitForGoogleMaps(callback) {
    // For Places callers: ensure Places loaded then callback
    placesAutocompleteService
      .ensureLoaded()
      .then(() => {
        if (typeof callback === 'function') callback();
      })
      .catch(() => {
        if (typeof callback === 'function') callback();
      });
  },
  waitUntilReady(callback) {
    mapsManager.waitUntilReady(callback);
  },
  initMap(containerId, options) {
    return mapsManager.initMap(containerId, options);
  },
  createMarker(options) {
    const variant = options.variant || (options.content ? 'gray' : 'primary');
    return Promise.resolve(
      markerFactory.createMarker({
        map: options.map,
        position: options.position,
        variant,
        title: options.title,
        popupHtml: options.popupHtml,
      })
    );
  },
  createMarkerClusterer(options) {
    return mapsManager.createMarkerClusterer(options);
  },
  initAutocomplete(inputId, onPlaceChanged, opts) {
    return placesAutocompleteService.initAutocomplete(inputId, onPlaceChanged, opts);
  },
  extractLocationData(place) {
    return placesAutocompleteService.extractLocationData(place);
  },
  fillGeosearchFormFields(form, locationData, place) {
    return placesAutocompleteService.fillGeosearchFormFields(form, locationData, place);
  },
  resizeMap(map) {
    mapsManager.resizeMap(map);
  },
  initMapOnModalShow(modalId, initCallback) {
    mapsManager.initMapOnModalShow(modalId, initCallback);
  },
  ensurePlacesLoaded() {
    return placesAutocompleteService.ensureLoaded();
  },
};

function bootProductMaps(root) {
  const scope = root || document;
  scope.querySelectorAll('[data-maps-product]').forEach((el) => {
    if (el._cagProductMap) return;
    el._cagProductMap = new ProductMap(el).init();
  });
}

function bootListingMaps(root) {
  const scope = root || document;
  scope.querySelectorAll('[data-maps-listing]').forEach((el) => {
    if (el._cagListingMap) return;
    el._cagListingMap = new ListingMap(el).init();
  });
}

function bootAll(root) {
  bootProductMaps(root);
  bootListingMaps(root);
}

window.CAGMaps.boot = bootAll;
window.CAGMaps.bootProductMaps = bootProductMaps;
window.CAGMaps.bootListingMaps = bootListingMaps;

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => bootAll());
} else {
  bootAll();
}

export {
  mapsManager,
  markerFactory,
  ProductMap,
  ListingMap,
  placesAutocompleteService,
  bootAll,
};
