/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./resources/js/maps/PlacesAutocompleteService.js"
/*!********************************************************!*\
  !*** ./resources/js/maps/PlacesAutocompleteService.js ***!
  \********************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   PLACE_FIELDS: () => (/* binding */ PLACE_FIELDS),
/* harmony export */   PlacesAutocompleteService: () => (/* binding */ PlacesAutocompleteService),
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
/**
 * PlacesAutocompleteService — deferred Google Places Autocomplete (no Dynamic Maps)
 */
var PLACE_FIELDS = ['place_id', 'geometry', 'address_components', 'name', 'formatted_address', 'types'];
var PlacesAutocompleteService = /*#__PURE__*/function () {
  function PlacesAutocompleteService() {
    _classCallCheck(this, PlacesAutocompleteService);
    this._loading = null;
    this._apiKey = window.CAG_MAPS_CONFIG && window.CAG_MAPS_CONFIG.googleMapsApiKey || '';
  }
  return _createClass(PlacesAutocompleteService, [{
    key: "isPlacesReady",
    value: function isPlacesReady() {
      return !!(window.google && google.maps && google.maps.places);
    }

    /**
     * Load Places library once (no map/marker libraries — avoids Dynamic Maps).
     * @returns {Promise<void>}
     */
  }, {
    key: "ensureLoaded",
    value: function ensureLoaded() {
      var _this = this;
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
      this._loading = new Promise(function (resolve, reject) {
        var existing = document.querySelector('script[data-cag-places="1"]');
        if (existing) {
          var check = setInterval(function () {
            if (_this.isPlacesReady()) {
              clearInterval(check);
              resolve();
            }
          }, 100);
          setTimeout(function () {
            clearInterval(check);
            if (!_this.isPlacesReady()) {
              reject(new Error('Places timed out'));
            }
          }, 15000);
          return;
        }
        window.__cagPlacesCallback = function () {
          resolve();
        };
        var script = document.createElement('script');
        script.dataset.cagPlaces = '1';
        script.async = true;
        script.defer = true;
        script.src = "https://maps.googleapis.com/maps/api/js?key=".concat(encodeURIComponent(_this._apiKey), "&libraries=places&callback=__cagPlacesCallback&loading=async");
        script.onerror = function () {
          return reject(new Error('Failed to load Google Places'));
        };
        document.head.appendChild(script);
      });
      return this._loading;
    }

    /**
     * Bind autocomplete on focus of inputs (deferred load).
     * @param {string[]} inputIds
     * @param {Function} onReady - called with PlacesAutocompleteService after load
     */
  }, {
    key: "bindDeferredInputs",
    value: function bindDeferredInputs(inputIds, onReady) {
      var _this2 = this;
      var ids = inputIds || [];
      ids.forEach(function (id) {
        var el = typeof id === 'string' ? document.getElementById(id) : id;
        if (!el || el.dataset.cagPlacesBound === '1') {
          return;
        }
        el.dataset.cagPlacesBound = '1';
        var loadAndInit = function loadAndInit() {
          _this2.ensureLoaded().then(function () {
            if (typeof onReady === 'function') {
              onReady(_this2);
            }
          })["catch"](function (err) {
            return console.warn(err);
          });
        };
        el.addEventListener('focus', loadAndInit, {
          once: false
        });
        el.addEventListener('click', loadAndInit, {
          once: false
        });
      });
    }

    /**
     * @param {string|HTMLElement} inputId
     * @param {Function} onPlaceChanged
     * @param {Object} [opts] - { types: string[] }
     * @returns {google.maps.places.Autocomplete|null}
     */
  }, {
    key: "initAutocomplete",
    value: function initAutocomplete(inputId, onPlaceChanged) {
      var opts = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : {};
      if (!this.isPlacesReady()) {
        return null;
      }
      var input = typeof inputId === 'string' ? document.getElementById(inputId) : inputId;
      if (!input) {
        return null;
      }
      if (input._cagAutocomplete) {
        return input._cagAutocomplete;
      }
      try {
        var options = {
          fields: PLACE_FIELDS
        };
        if (opts.types) {
          options.types = opts.types;
        }
        var autocomplete = new google.maps.places.Autocomplete(input, options);
        if (typeof autocomplete.setFields === 'function') {
          autocomplete.setFields(PLACE_FIELDS);
        }
        if (onPlaceChanged) {
          autocomplete.addListener('place_changed', function () {
            var place = autocomplete.getPlace();
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
  }, {
    key: "extractLocationData",
    value: function extractLocationData(place) {
      var location = {
        lat: place.geometry.location.lat(),
        lng: place.geometry.location.lng(),
        city: '',
        country: '',
        region: '',
        postal_code: '',
        country_short: '',
        region_short: '',
        name: place.name || ''
      };
      var addressComponents = place.address_components || [];
      var placeTypes = place.types || [];
      addressComponents.forEach(function (component) {
        var types = component.types || [];
        if (types.includes('locality') || types.includes('sublocality') || types.includes('postal_town') || types.includes('natural_feature')) {
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
      var hasCountry = !!location.country;
      var hasCity = !!location.city;
      var hasRegion = !!location.region;
      var isCountryLevelResult = hasCountry && !hasCity && !hasRegion;
      if (!location.city && location.name && !isCountryLevelResult) {
        if (location.country && location.name.toLowerCase() === location.country.toLowerCase()) {
          location.city = '';
        } else {
          location.city = location.name;
        }
      }
      location.place_types = placeTypes;
      var boundsSource = place.geometry && (place.geometry.viewport || place.geometry.bounds);
      if (boundsSource) {
        var ne = boundsSource.getNorthEast ? boundsSource.getNorthEast() : boundsSource.northeast;
        var sw = boundsSource.getSouthWest ? boundsSource.getSouthWest() : boundsSource.southwest;
        if (ne && sw) {
          location.bounds_ne_lat = typeof ne.lat === 'function' ? ne.lat() : ne.lat;
          location.bounds_ne_lng = typeof ne.lng === 'function' ? ne.lng() : ne.lng;
          location.bounds_sw_lat = typeof sw.lat === 'function' ? sw.lat() : sw.lat;
          location.bounds_sw_lng = typeof sw.lng === 'function' ? sw.lng() : sw.lng;
        }
      }
      return location;
    }
  }, {
    key: "fillGeosearchFormFields",
    value: function fillGeosearchFormFields(form, locationData, place) {
      if (!form || !locationData) {
        return;
      }
      var setField = function setField(name, value) {
        var el = form.querySelector('[data-geosearch="' + name + '"], [name="' + name + '"]');
        if (el) {
          el.value = value != null && value !== undefined ? value : '';
        }
      };
      setField('bounds_ne_lat', locationData.bounds_ne_lat);
      setField('bounds_ne_lng', locationData.bounds_ne_lng);
      setField('bounds_sw_lat', locationData.bounds_sw_lat);
      setField('bounds_sw_lng', locationData.bounds_sw_lng);
      setField('country_short', locationData.country_short);
      var types = place && place.types || locationData.place_types || [];
      setField('place_types', JSON.stringify(types));
    }

    /**
     * Resolve a CSS selector or element id into an input element.
     * @param {string|HTMLElement|null} ref
     * @returns {HTMLElement|null}
     */
  }, {
    key: "resolveField",
    value: function resolveField(ref) {
      if (!ref) {
        return null;
      }
      if (typeof ref !== 'string') {
        return ref;
      }
      if (ref.startsWith('#') || ref.startsWith('.') || ref.startsWith('[')) {
        return document.querySelector(ref);
      }
      return document.getElementById(ref);
    }
  }, {
    key: "setFieldValue",
    value: function setFieldValue(ref, value) {
      var el = this.resolveField(ref);
      if (el) {
        el.value = value != null && value !== undefined ? value : '';
        el.dispatchEvent(new Event('input', {
          bubbles: true
        }));
        el.dispatchEvent(new Event('change', {
          bubbles: true
        }));
      }
    }

    /**
     * Bind a form location input: deferred Places load + fill linked hidden fields.
     * Supports data attributes on the input:
     *   data-places-location
     *   data-places-types='["geocode"]'
     *   data-places-lat / lng / country / city / region / postal
     *   data-places-fill-input="formatted_address|name|keep"
     *
     * @param {string|HTMLElement} inputRef
     * @param {Object} [options]
     * @returns {void}
     */
  }, {
    key: "bindLocationField",
    value: function bindLocationField(inputRef) {
      var _this3 = this;
      var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
      var input = this.resolveField(inputRef);
      if (!input || input.dataset.cagPlacesLocationBound === '1') {
        return;
      }
      input.dataset.cagPlacesLocationBound = '1';
      var readOpt = function readOpt(key, dataKey, fallback) {
        if (options[key] != null) {
          return options[key];
        }
        if (dataKey && input.dataset[dataKey] != null && input.dataset[dataKey] !== '') {
          return input.dataset[dataKey];
        }
        return fallback;
      };
      var types = options.types;
      if (!types && input.dataset.placesTypes) {
        try {
          types = JSON.parse(input.dataset.placesTypes);
        } catch (e) {
          types = null;
        }
      }
      var fieldMap = {
        lat: readOpt('lat', 'placesLat', '#latitude'),
        lng: readOpt('lng', 'placesLng', '#longitude'),
        country: readOpt('country', 'placesCountry', '#country'),
        city: readOpt('city', 'placesCity', '#city'),
        region: readOpt('region', 'placesRegion', '#region'),
        postal: readOpt('postal', 'placesPostal', null)
      };
      var fillInputMode = readOpt('fillInput', 'placesFillInput', 'formatted_address');
      var onPlace = typeof options.onPlaceChanged === 'function' ? options.onPlaceChanged : null;
      var boot = function boot() {
        _this3.ensureLoaded().then(function () {
          _this3.initAutocomplete(input, function (place) {
            var data = _this3.extractLocationData(place);
            _this3.setFieldValue(fieldMap.lat, data.lat);
            _this3.setFieldValue(fieldMap.lng, data.lng);
            _this3.setFieldValue(fieldMap.country, data.country);
            _this3.setFieldValue(fieldMap.city, data.city);
            _this3.setFieldValue(fieldMap.region, data.region);
            if (fieldMap.postal) {
              _this3.setFieldValue(fieldMap.postal, data.postal_code);
            }
            if (fillInputMode === 'formatted_address' && place.formatted_address) {
              input.value = place.formatted_address;
            } else if (fillInputMode === 'name' && place.name) {
              input.value = place.name;
            }
            if (onPlace) {
              onPlace(place, data);
            }
            input.dispatchEvent(new CustomEvent('cag:place-selected', {
              bubbles: true,
              detail: {
                place: place,
                location: data
              }
            }));
          }, types ? {
            types: types
          } : {});
        })["catch"](function (err) {
          return console.warn('Places location bind failed:', err);
        });
      };
      input.addEventListener('focus', boot, {
        once: false
      });
      input.addEventListener('click', boot, {
        once: false
      });
    }

    /**
     * Auto-bind every [data-places-location] input in a root.
     * @param {ParentNode} [root]
     */
  }, {
    key: "bootLocationFields",
    value: function bootLocationFields(root) {
      var _this4 = this;
      var scope = root || document;
      scope.querySelectorAll('[data-places-location]').forEach(function (el) {
        _this4.bindLocationField(el);
      });
    }
  }]);
}();
var placesAutocompleteService = new PlacesAutocompleteService();
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (placesAutocompleteService);


/***/ }

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		if (!(moduleId in __webpack_modules__)) {
/******/ 			delete __webpack_module_cache__[moduleId];
/******/ 			var e = new Error("Cannot find module '" + moduleId + "'");
/******/ 			e.code = 'MODULE_NOT_FOUND';
/******/ 			throw e;
/******/ 		}
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry needs to be wrapped in an IIFE because it needs to be isolated against other modules in the chunk.
(() => {
/*!*******************************************!*\
  !*** ./resources/js/maps/places-entry.js ***!
  \*******************************************/
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   placesAutocompleteService: () => (/* reexport safe */ _PlacesAutocompleteService__WEBPACK_IMPORTED_MODULE_0__["default"])
/* harmony export */ });
/* harmony import */ var _PlacesAutocompleteService__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./PlacesAutocompleteService */ "./resources/js/maps/PlacesAutocompleteService.js");
/**
 * Sitewide Places Autocomplete only (no Leaflet / Dynamic Maps).
 */

window.CAGPlaces = _PlacesAutocompleteService__WEBPACK_IMPORTED_MODULE_0__["default"];
window.GoogleMapsManager = window.GoogleMapsManager || {
  waitForGoogleMaps: function waitForGoogleMaps(callback) {
    _PlacesAutocompleteService__WEBPACK_IMPORTED_MODULE_0__["default"].ensureLoaded().then(function () {
      if (typeof callback === 'function') callback();
    })["catch"](function () {
      if (typeof callback === 'function') callback();
    });
  },
  initAutocomplete: function initAutocomplete(inputId, onPlaceChanged, opts) {
    return _PlacesAutocompleteService__WEBPACK_IMPORTED_MODULE_0__["default"].initAutocomplete(inputId, onPlaceChanged, opts);
  },
  extractLocationData: function extractLocationData(place) {
    return _PlacesAutocompleteService__WEBPACK_IMPORTED_MODULE_0__["default"].extractLocationData(place);
  },
  fillGeosearchFormFields: function fillGeosearchFormFields(form, locationData, place) {
    return _PlacesAutocompleteService__WEBPACK_IMPORTED_MODULE_0__["default"].fillGeosearchFormFields(form, locationData, place);
  },
  ensurePlacesLoaded: function ensurePlacesLoaded() {
    return _PlacesAutocompleteService__WEBPACK_IMPORTED_MODULE_0__["default"].ensureLoaded();
  }
};

// Merge if maps.js already defined a fuller manager
if (window.CAGMaps && window.CAGMaps.Places) {
  Object.assign(window.GoogleMapsManager, {
    initAutocomplete: function initAutocomplete() {
      return _PlacesAutocompleteService__WEBPACK_IMPORTED_MODULE_0__["default"].initAutocomplete.apply(_PlacesAutocompleteService__WEBPACK_IMPORTED_MODULE_0__["default"], arguments);
    },
    extractLocationData: function extractLocationData(p) {
      return _PlacesAutocompleteService__WEBPACK_IMPORTED_MODULE_0__["default"].extractLocationData(p);
    },
    fillGeosearchFormFields: function fillGeosearchFormFields() {
      return _PlacesAutocompleteService__WEBPACK_IMPORTED_MODULE_0__["default"].fillGeosearchFormFields.apply(_PlacesAutocompleteService__WEBPACK_IMPORTED_MODULE_0__["default"], arguments);
    }
  });
}
function bindHeaderPlacesDeferred() {
  var inputIds = ['searchPlace', 'searchPlaceMobile', 'searchPlaceDesktop', 'searchPlaceHeaderDesktop', 'searchPlaceShortDesktop', 'homeHeroSearchPlace', 'offersCatalogSearchPlace'];
  _PlacesAutocompleteService__WEBPACK_IMPORTED_MODULE_0__["default"].bindDeferredInputs(inputIds, function () {
    // Header wiring is done in scripts.blade.php via initializeGooglePlaces after ensureLoaded
    if (typeof window.__cagInitHeaderPlaces === 'function') {
      window.__cagInitHeaderPlaces();
    }
  });
}
function bootAllPlacesFields() {
  _PlacesAutocompleteService__WEBPACK_IMPORTED_MODULE_0__["default"].bootLocationFields(document);
  bindHeaderPlacesDeferred();
}
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', bootAllPlacesFields);
} else {
  bootAllPlacesFields();
}

// Re-scan after dynamic form steps / Livewire morphs
document.addEventListener('cag:places-rescan', function () {
  _PlacesAutocompleteService__WEBPACK_IMPORTED_MODULE_0__["default"].bootLocationFields(document);
});
window.CAGPlaces.bindLocationField = function () {
  return _PlacesAutocompleteService__WEBPACK_IMPORTED_MODULE_0__["default"].bindLocationField.apply(_PlacesAutocompleteService__WEBPACK_IMPORTED_MODULE_0__["default"], arguments);
};
window.CAGPlaces.bootLocationFields = function () {
  return _PlacesAutocompleteService__WEBPACK_IMPORTED_MODULE_0__["default"].bootLocationFields.apply(_PlacesAutocompleteService__WEBPACK_IMPORTED_MODULE_0__["default"], arguments);
};

})();

/******/ })()
;