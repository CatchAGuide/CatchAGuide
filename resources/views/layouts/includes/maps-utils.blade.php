{{-- 
    Centralized Google Maps Utilities
    This file provides OOP-style Google Maps functionality sitewide
    Load MarkerClusterer once and reuse across all pages
--}}

<!-- Load MarkerClusterer library once (with fallback CDNs) -->
<script>
(function() {
    // Only load if not already loaded
    if (window.MarkerClusterer || (window.markerClusterer && window.markerClusterer.MarkerClusterer)) {
        return;
    }

    const cdnUrls = [
        "https://unpkg.com/@googlemaps/markerclusterer@2.3.1/dist/index.umd.js",
        "https://cdn.jsdelivr.net/npm/@googlemaps/markerclusterer@2.3.1/dist/index.umd.js"
    ];

    function loadMarkerClusterer(index) {
        if (index >= cdnUrls.length) {
            // Silently fail - maps will work without clustering
            return;
        }

        const script = document.createElement('script');
        script.src = cdnUrls[index];
        script.onload = function() {
            // Check which format was loaded and normalize it
            if (window.markerClusterer) {
                if (window.markerClusterer.MarkerClusterer) {
                    window.MarkerClusterer = window.markerClusterer.MarkerClusterer;
                } else if (typeof window.markerClusterer === 'function') {
                    window.MarkerClusterer = window.markerClusterer;
                } else if (window.markerClusterer.default) {
                    window.MarkerClusterer = window.markerClusterer.default;
                }
            }
            
            // If still not set, try next CDN
            if (!window.MarkerClusterer) {
                loadMarkerClusterer(index + 1);
            }
        };
        script.onerror = function() {
            loadMarkerClusterer(index + 1);
        };
        document.head.appendChild(script);
    }

    loadMarkerClusterer(0);
})();
</script>

<script>
/**
 * GoogleMapsManager - Centralized OOP-style Google Maps utility
 * Provides reusable methods for map initialization, marker clustering, and Places autocomplete
 */
window.GoogleMapsManager = (function() {
    'use strict';

    const GoogleMapsManager = {
        // Configuration
        config: {
            defaultZoom: 5,
            defaultCenter: { lat: 51.165691, lng: 10.451526 },
            maxWaitAttempts: 50,
            waitInterval: 200,
            mapId: "{{ config('services.google_maps.map_id', 'DEMO_MAP_ID') }}"
        },

        // Cache for loaded libraries
        _libraries: {
            maps: null,
            marker: null,
            places: null
        },

        // Cache for MarkerClusterer
        _markerClusterer: null,

        /**
         * Wait for Google Maps API to be ready
         * @param {Function} callback - Function to call when API is ready
         * @param {number} maxAttempts - Maximum number of attempts
         * @param {number} interval - Interval between attempts in ms
         */
        waitForGoogleMaps: function(callback, maxAttempts, interval) {
            maxAttempts = maxAttempts || this.config.maxWaitAttempts;
            interval = interval || this.config.waitInterval;
            
            let attempts = 0;
            const timer = setInterval(() => {
                if (window.google && google.maps && google.maps.importLibrary) {
                    clearInterval(timer);
                    callback();
                } else if (++attempts >= maxAttempts) {
                    clearInterval(timer);
                    console.warn('Google Maps JS API not available after waiting.');
                    if (callback) callback(); // Still call callback to prevent hanging
                }
            }, interval);
        },

        /**
         * Load Google Maps library
         * @param {string} libraryName - Name of library ('maps', 'marker', 'places')
         * @returns {Promise} Promise that resolves with library exports
         */
        loadLibrary: async function(libraryName) {
            if (this._libraries[libraryName]) {
                return this._libraries[libraryName];
            }

            try {
                const library = await google.maps.importLibrary(libraryName);
                this._libraries[libraryName] = library;
                return library;
            } catch (error) {
                console.error(`Failed to load Google Maps library: ${libraryName}`, error);
                throw error;
            }
        },

        /**
         * Get MarkerClusterer class (with fallback and retry)
         * @returns {Function|null} MarkerClusterer class or null if unavailable
         */
        getMarkerClusterer: function() {
            if (this._markerClusterer) {
                return this._markerClusterer;
            }

            // Try multiple ways MarkerClusterer might be exposed
            if (window.MarkerClusterer) {
                this._markerClusterer = window.MarkerClusterer;
                return this._markerClusterer;
            }

            if (window.markerClusterer) {
                if (window.markerClusterer.MarkerClusterer) {
                    this._markerClusterer = window.markerClusterer.MarkerClusterer;
                    return this._markerClusterer;
                }
                // Sometimes it's the default export
                if (typeof window.markerClusterer === 'function') {
                    this._markerClusterer = window.markerClusterer;
                    return this._markerClusterer;
                }
            }

            // Check if it's being loaded (wait a bit)
            if (document.querySelector('script[src*="markerclusterer"]')) {
                // Script is loading, return null for now (will be retried)
                return null;
            }

            return null;
        },

        /**
         * Initialize a Google Map
         * @param {string|HTMLElement} containerId - ID or element of map container
         * @param {Object} options - Map options (zoom, center, mapId, etc.)
         * @returns {Promise<Object>} Promise that resolves with map instance
         */
        initMap: async function(containerId, options) {
            const container = typeof containerId === 'string' 
                ? document.getElementById(containerId) 
                : containerId;

            if (!container) {
                throw new Error(`Map container not found: ${containerId}`);
            }

            const { Map } = await this.loadLibrary('maps');
            
            const defaultOptions = {
                zoom: this.config.defaultZoom,
                center: this.config.defaultCenter,
                mapId: this.config.mapId,
                streetViewControl: false,
                mapTypeControl: false,
                clickableIcons: false
            };

            const mapOptions = Object.assign({}, defaultOptions, options);
            
            // Ensure mapId is set (required for vector maps and AdvancedMarkerElement)
            // Only override if mapId is empty string, null, undefined, or DEMO_MAP_ID placeholder
            if (!mapOptions.mapId || mapOptions.mapId === '' || mapOptions.mapId === 'DEMO_MAP_ID') {
                // Use configured mapId from env (should be '1feca0c53ba86197' from .env)
                // Only use DEMO_MAP_ID if env variable is not set
                mapOptions.mapId = (this.config.mapId && this.config.mapId !== 'DEMO_MAP_ID') 
                    ? this.config.mapId 
                    : 'DEMO_MAP_ID';
            }
            
            const map = new Map(container, mapOptions);

            return map;
        },

        /**
         * Create an Advanced Marker
         * @param {Object} options - Marker options (position, map, etc.)
         * @returns {Promise<Object>} Promise that resolves with marker instance
         */
        createMarker: async function(options) {
            const { AdvancedMarkerElement } = await this.loadLibrary('marker');
            return new AdvancedMarkerElement(options);
        },

        /**
         * Create marker clusterer
         * @param {Object} options - Clusterer options { markers, map }
         * @returns {Object|null} MarkerClusterer instance or null if unavailable
         */
        createMarkerClusterer: function(options) {
            const MarkerClusterer = this.getMarkerClusterer();
            
            if (!MarkerClusterer) {
                // Silently return null if not available (don't log warning - it's expected on some pages)
                return null;
            }

            try {
                return new MarkerClusterer(options);
            } catch (error) {
                console.error('Failed to create MarkerClusterer:', error);
                return null;
            }
        },

        /**
         * Initialize Places Autocomplete
         * @param {string|HTMLElement} inputId - ID or element of input field
         * @param {Function} onPlaceChanged - Callback when place is selected
         * @returns {Object|null} Autocomplete instance or null if unavailable
         */
        initAutocomplete: function(inputId, onPlaceChanged) {
            if (!window.google || !google.maps || !google.maps.places) {
                // Silently return if Places library not available (don't log warning)
                return null;
            }

            const input = typeof inputId === 'string' 
                ? document.getElementById(inputId) 
                : inputId;

            if (!input) {
                // Silently return if input doesn't exist (page might not have this input)
                return null;
            }

            try {
                const autocomplete = new google.maps.places.Autocomplete(input);
                
                if (onPlaceChanged) {
                    autocomplete.addListener('place_changed', function() {
                        const place = autocomplete.getPlace();
                        if (place && place.geometry && place.geometry.location) {
                            onPlaceChanged(place);
                        }
                    });
                }

                return autocomplete;
            } catch (error) {
                console.error('Failed to initialize Places Autocomplete:', error);
                return null;
            }
        },

        /**
         * Extract location data from Places API place object
         * @param {Object} place - Google Places place object
         * @returns {Object} Extracted location data { lat, lng, city, country, region, name }
         */
        extractLocationData: function(place) {
            const location = {
                lat: place.geometry.location.lat(),
                lng: place.geometry.location.lng(),
                city: '',
                country: '',
                region: '',
                name: place.name || ''
            };

            const addressComponents = place.address_components || [];
            const placeTypes = place.types || [];
            
            // Extract components first
            addressComponents.forEach(component => {
                const types = component.types || [];
                if (types.includes('locality') || types.includes('sublocality') || 
                    types.includes('postal_town') || types.includes('natural_feature')) {
                    location.city = component.long_name;
                }
                if (types.includes('country')) {
                    location.country = component.long_name;
                }
                if (types.includes('administrative_area_level_1')) {
                    location.region = component.long_name;
                }
            });

            // Check if this is a country-level result:
            // - Has country but no city/region components
            // - OR place types indicate it's a country-level result
            const hasCountry = !!location.country;
            const hasCity = !!location.city;
            const hasRegion = !!location.region;
            const isCountryLevelResult = hasCountry && !hasCity && !hasRegion;
            
            // Only set city from name if it's not a country-level result
            // This prevents setting city = country name when searching for a country
            if (!location.city && location.name && !isCountryLevelResult) {
                // Additional check: don't set city if name equals country
                if (location.country && location.name.toLowerCase() === location.country.toLowerCase()) {
                    // This is definitely a country-level search, leave city empty
                    location.city = '';
                } else {
                    location.city = location.name;
                }
            }

            return location;
        },

        /**
         * Trigger map resize (useful after modal/show operations)
         * @param {Object} map - Google Map instance
         */
        resizeMap: function(map) {
            if (map && google && google.maps && google.maps.event) {
                setTimeout(() => {
                    google.maps.event.trigger(map, 'resize');
                }, 100);
            }
        },

        /**
         * Initialize map when modal is shown
         * @param {string} modalId - ID of Bootstrap modal
         * @param {Function} initCallback - Function to initialize map
         */
        initMapOnModalShow: function(modalId, initCallback) {
            document.addEventListener('DOMContentLoaded', () => {
                const modal = document.getElementById(modalId);
                if (!modal) {
                    console.warn(`Modal not found: ${modalId}`);
                    return;
                }

                modal.addEventListener('shown.bs.modal', () => {
                    this.waitForGoogleMaps(() => {
                        if (initCallback) {
                            initCallback();
                        }
                    });
                });
            });
        }
    };

    return GoogleMapsManager;
})();
</script>
