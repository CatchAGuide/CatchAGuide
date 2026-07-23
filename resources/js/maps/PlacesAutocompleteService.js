/**
 * PlacesAutocompleteService — deferred Google Places Autocomplete (no Dynamic Maps)
 */
const PLACE_FIELDS = ['place_id', 'geometry', 'address_components', 'name', 'formatted_address', 'types'];

class PlacesAutocompleteService {
  constructor() {
    this._loading = null;
    this._apiKey = (window.CAG_MAPS_CONFIG && window.CAG_MAPS_CONFIG.googleMapsApiKey) || '';
  }

  isPlacesReady() {
    return !!(window.google && google.maps && google.maps.places);
  }

  /**
   * Load Places library once (no map/marker libraries — avoids Dynamic Maps).
   * @returns {Promise<void>}
   */
  ensureLoaded() {
    if (this.isPlacesReady()) {
      return Promise.resolve();
    }

    if (this._loading) {
      return this._loading;
    }

    if (!this._apiKey) {
      console.error('GOOGLE_MAPS_API_KEY is not set — Places Autocomplete will not work.');
      return Promise.reject(new Error('Missing Google Maps API key'));
    }

    this._loading = new Promise((resolve, reject) => {
      const existing = document.querySelector('script[data-cag-places="1"]');
      if (existing) {
        const check = setInterval(() => {
          if (this.isPlacesReady()) {
            clearInterval(check);
            resolve();
          }
        }, 100);
        setTimeout(() => {
          clearInterval(check);
          if (!this.isPlacesReady()) {
            reject(new Error('Places timed out'));
          }
        }, 15000);
        return;
      }

      window.__cagPlacesCallback = () => {
        resolve();
      };

      const script = document.createElement('script');
      script.dataset.cagPlaces = '1';
      script.async = true;
      script.defer = true;
      script.src = `https://maps.googleapis.com/maps/api/js?key=${encodeURIComponent(this._apiKey)}&libraries=places&callback=__cagPlacesCallback&loading=async`;
      script.onerror = () => reject(new Error('Failed to load Google Places'));
      document.head.appendChild(script);
    });

    return this._loading;
  }

  /**
   * Bind autocomplete on focus of inputs (deferred load).
   * @param {string[]} inputIds
   * @param {Function} onReady - called with PlacesAutocompleteService after load
   */
  bindDeferredInputs(inputIds, onReady) {
    const ids = inputIds || [];
    ids.forEach((id) => {
      const el = typeof id === 'string' ? document.getElementById(id) : id;
      if (!el || el.dataset.cagPlacesBound === '1') {
        return;
      }
      el.dataset.cagPlacesBound = '1';

      const loadAndInit = () => {
        this.ensureLoaded()
          .then(() => {
            if (typeof onReady === 'function') {
              onReady(this);
            }
          })
          .catch((err) => console.warn(err));
      };

      el.addEventListener('focus', loadAndInit, { once: false });
      el.addEventListener('click', loadAndInit, { once: false });
    });
  }

  /**
   * @param {string|HTMLElement} inputId
   * @param {Function} onPlaceChanged
   * @param {Object} [opts] - { types: string[] }
   * @returns {google.maps.places.Autocomplete|null}
   */
  initAutocomplete(inputId, onPlaceChanged, opts = {}) {
    if (!this.isPlacesReady()) {
      return null;
    }

    const input = typeof inputId === 'string' ? document.getElementById(inputId) : inputId;
    if (!input) {
      return null;
    }

    if (input._cagAutocomplete) {
      return input._cagAutocomplete;
    }

    try {
      const options = {
        fields: PLACE_FIELDS,
      };
      if (opts.types) {
        options.types = opts.types;
      }

      const autocomplete = new google.maps.places.Autocomplete(input, options);

      if (typeof autocomplete.setFields === 'function') {
        autocomplete.setFields(PLACE_FIELDS);
      }

      if (onPlaceChanged) {
        autocomplete.addListener('place_changed', () => {
          const place = autocomplete.getPlace();
          if (place && place.geometry && place.geometry.location) {
            onPlaceChanged(place);
          }
        });
      }

      input._cagAutocomplete = autocomplete;
      return autocomplete;
    } catch (error) {
      console.error('Failed to initialize Places Autocomplete:', error);
      return null;
    }
  }

  extractLocationData(place) {
    const location = {
      lat: place.geometry.location.lat(),
      lng: place.geometry.location.lng(),
      city: '',
      country: '',
      region: '',
      postal_code: '',
      country_short: '',
      region_short: '',
      name: place.name || '',
    };

    const addressComponents = place.address_components || [];
    const placeTypes = place.types || [];

    addressComponents.forEach((component) => {
      const types = component.types || [];
      if (
        types.includes('locality') ||
        types.includes('sublocality') ||
        types.includes('postal_town') ||
        types.includes('natural_feature')
      ) {
        location.city = component.long_name;
      }
      if (types.includes('country')) {
        location.country = component.long_name;
        location.country_short = component.short_name || '';
      }
      if (types.includes('administrative_area_level_1')) {
        location.region = component.long_name;
        location.region_short = component.short_name || '';
      }
      if (types.includes('postal_code')) {
        location.postal_code = component.long_name;
      }
    });

    const hasCountry = !!location.country;
    const hasCity = !!location.city;
    const hasRegion = !!location.region;
    const isCountryLevelResult = hasCountry && !hasCity && !hasRegion;

    if (!location.city && location.name && !isCountryLevelResult) {
      if (location.country && location.name.toLowerCase() === location.country.toLowerCase()) {
        location.city = '';
      } else {
        location.city = location.name;
      }
    }

    location.place_types = placeTypes;

    const boundsSource = place.geometry && (place.geometry.viewport || place.geometry.bounds);
    if (boundsSource) {
      const ne = boundsSource.getNorthEast ? boundsSource.getNorthEast() : boundsSource.northeast;
      const sw = boundsSource.getSouthWest ? boundsSource.getSouthWest() : boundsSource.southwest;
      if (ne && sw) {
        location.bounds_ne_lat = typeof ne.lat === 'function' ? ne.lat() : ne.lat;
        location.bounds_ne_lng = typeof ne.lng === 'function' ? ne.lng() : ne.lng;
        location.bounds_sw_lat = typeof sw.lat === 'function' ? sw.lat() : sw.lat;
        location.bounds_sw_lng = typeof sw.lng === 'function' ? sw.lng() : sw.lng;
      }
    }

    return location;
  }

  fillGeosearchFormFields(form, locationData, place) {
    if (!form || !locationData) {
      return;
    }
    const setField = (name, value) => {
      const el = form.querySelector('[data-geosearch="' + name + '"], [name="' + name + '"]');
      if (el) {
        el.value = value != null && value !== undefined ? value : '';
      }
    };
    setField('bounds_ne_lat', locationData.bounds_ne_lat);
    setField('bounds_ne_lng', locationData.bounds_ne_lng);
    setField('bounds_sw_lat', locationData.bounds_sw_lat);
    setField('bounds_sw_lng', locationData.bounds_sw_lng);
    setField('country_short', locationData.country_short);
    const types = (place && place.types) || locationData.place_types || [];
    setField('place_types', JSON.stringify(types));
  }
}

const placesAutocompleteService = new PlacesAutocompleteService();
export default placesAutocompleteService;
export { PlacesAutocompleteService, PLACE_FIELDS };
