/**
 * Sitewide Places Autocomplete only (no Leaflet / Dynamic Maps).
 */
import placesAutocompleteService from './PlacesAutocompleteService';

window.CAGPlaces = placesAutocompleteService;

window.GoogleMapsManager = window.GoogleMapsManager || {
  waitForGoogleMaps(callback) {
    placesAutocompleteService
      .ensureLoaded()
      .then(() => {
        if (typeof callback === 'function') callback();
      })
      .catch(() => {
        if (typeof callback === 'function') callback();
      });
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
  ensurePlacesLoaded() {
    return placesAutocompleteService.ensureLoaded();
  },
};

// Merge if maps.js already defined a fuller manager
if (window.CAGMaps && window.CAGMaps.Places) {
  Object.assign(window.GoogleMapsManager, {
    initAutocomplete: (...args) => placesAutocompleteService.initAutocomplete(...args),
    extractLocationData: (p) => placesAutocompleteService.extractLocationData(p),
    fillGeosearchFormFields: (...args) => placesAutocompleteService.fillGeosearchFormFields(...args),
  });
}

function bindHeaderPlacesDeferred() {
  const inputIds = [
    'searchPlace',
    'searchPlaceMobile',
    'searchPlaceDesktop',
    'searchPlaceHeaderDesktop',
    'searchPlaceShortDesktop',
  ];
  placesAutocompleteService.bindDeferredInputs(inputIds, () => {
    // Header wiring is done in scripts.blade.php via initializeGooglePlaces after ensureLoaded
    if (typeof window.__cagInitHeaderPlaces === 'function') {
      window.__cagInitHeaderPlaces();
    }
  });
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', bindHeaderPlacesDeferred);
} else {
  bindHeaderPlacesDeferred();
}

export { placesAutocompleteService };
