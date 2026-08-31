/**
 * Grid clustering for listing maps.
 *
 * Leaflet.markercluster uses a greedy radius that *chains* across stepping-stone
 * pins (Germany → France → Med → West Africa). The cluster's weighted centroid
 * then sits in Africa, so the Netherlands/Germany look empty until you zoom in
 * enough for the chain to break.
 *
 * This layer buckets currently-visible markers into a lat/lng degree grid.
 * A pin in Amsterdam can only share a cluster with pins in the same cell,
 * so country-level coverage stays in-country. Pixel grids are not used:
 * dividing Mercator x by a small radius at world zoom collapses longitude
 * and draws every pin on a vertical line down the Atlantic.
 */
import L from 'leaflet';
import { clusterCellKey } from './clusterGrid';

const GridMarkerCluster = L.LayerGroup.extend({
  initialize(options = {}) {
    L.LayerGroup.prototype.initialize.call(this, []);
    this.options = {
      iconCreateFunction: options.iconCreateFunction || null,
      muted: !!options.muted,
    };
    this._source = [];
    this._parentByMarker = {};
    this._onViewChange = () => this._scheduleRebuild();
    this._rebuildRaf = null;
  },

  onAdd(map) {
    L.LayerGroup.prototype.onAdd.call(this, map);
    map.on('zoomend moveend', this._onViewChange);
    this._rebuild();
  },

  onRemove(map) {
    map.off('zoomend moveend', this._onViewChange);
    if (this._rebuildRaf && typeof cancelAnimationFrame === 'function') {
      cancelAnimationFrame(this._rebuildRaf);
      this._rebuildRaf = null;
    }
    this._restoreSpiderPositions();
    L.LayerGroup.prototype.onRemove.call(this, map);
  },

  addLayer(marker) {
    if (marker && this._source.indexOf(marker) === -1) {
      this._source.push(marker);
    }
    if (this._map) {
      this._rebuild();
    }
    return this;
  },

  addLayers(markers) {
    (markers || []).forEach((marker) => {
      if (marker && this._source.indexOf(marker) === -1) {
        this._source.push(marker);
      }
    });
    if (this._map) {
      this._rebuild();
    }
    return this;
  },

  clearLayers() {
    this._restoreSpiderPositions();
    this._source = [];
    this._parentByMarker = {};
    L.LayerGroup.prototype.clearLayers.call(this);
    return this;
  },

  getVisibleParent(marker) {
    if (!marker) {
      return null;
    }
    const id = L.stamp(marker);
    return this._parentByMarker[id] || marker;
  },

  zoomToShowLayer(marker, callback) {
    const done = () => {
      if (typeof callback === 'function') {
        callback();
      }
    };
    if (!marker || !this._map) {
      done();
      return this;
    }

    const parent = this.getVisibleParent(marker);
    if (!parent || parent === marker) {
      done();
      return this;
    }

    this._activateCluster(parent, done);
    return this;
  },

  _scheduleRebuild() {
    if (typeof requestAnimationFrame !== 'function') {
      this._rebuild();
      return;
    }
    if (this._rebuildRaf) {
      cancelAnimationFrame(this._rebuildRaf);
    }
    this._rebuildRaf = requestAnimationFrame(() => {
      this._rebuildRaf = null;
      this._rebuild();
    });
  },

  _rebuild() {
    const map = this._map;
    if (!map) {
      return;
    }

    this._restoreSpiderPositions();

    const zoom = map.getZoom();
    const size = map.getSize();
    let bounds = null;
    if (size && size.x > 8 && size.y > 8 && typeof map.getBounds === 'function') {
      const raw = map.getBounds();
      if (raw && typeof raw.isValid === 'function' && raw.isValid()) {
        bounds = raw.pad(0.08);
      }
    }
    const cells = {};

    this._source.forEach((marker) => {
      if (!marker || typeof marker.getLatLng !== 'function') {
        return;
      }
      const ll = marker.getLatLng();
      if (bounds && !bounds.contains(ll)) {
        return;
      }
      const key = clusterCellKey(ll.lat, ll.lng, zoom);
      if (!cells[key]) {
        cells[key] = [];
      }
      cells[key].push(marker);
    });

    const nextLayers = [];
    const nextParents = {};

    Object.keys(cells).forEach((key) => {
      const members = cells[key];
      if (members.length === 1) {
        const marker = members[0];
        nextLayers.push(marker);
        nextParents[L.stamp(marker)] = marker;
        return;
      }
      const clusterMarker = this._makeClusterMarker(members);
      nextLayers.push(clusterMarker);
      members.forEach((marker) => {
        nextParents[L.stamp(marker)] = clusterMarker;
      });
    });

    const nextSet = {};
    nextLayers.forEach((layer) => {
      nextSet[L.stamp(layer)] = layer;
    });

    this.getLayers().forEach((layer) => {
      if (!nextSet[L.stamp(layer)]) {
        L.LayerGroup.prototype.removeLayer.call(this, layer);
      }
    });
    nextLayers.forEach((layer) => {
      if (!this.hasLayer(layer)) {
        L.LayerGroup.prototype.addLayer.call(this, layer);
      }
    });

    this._parentByMarker = nextParents;
  },

  _makeClusterMarker(members) {
    let lat = 0;
    let lng = 0;
    members.forEach((marker) => {
      const ll = marker.getLatLng();
      lat += ll.lat;
      lng += ll.lng;
    });
    lat /= members.length;
    lng /= members.length;

    const icon =
      typeof this.options.iconCreateFunction === 'function'
        ? this.options.iconCreateFunction(members)
        : undefined;

    const clusterMarker = L.marker([lat, lng], {
      icon,
      keyboard: true,
      zIndexOffset: 250,
    });
    clusterMarker._cagMembers = members;
    clusterMarker._cagIsCluster = true;
    clusterMarker.getChildCount = () => members.length;
    clusterMarker.getAllChildMarkers = () => members.slice();
    clusterMarker.getBounds = () => L.latLngBounds(members.map((m) => m.getLatLng()));
    clusterMarker.zoomToBounds = (opts) => {
      if (!this._map) return;
      this._map.fitBounds(
        clusterMarker.getBounds(),
        opts || { paddingTopLeft: [24, 24], paddingBottomRight: [24, 24] }
      );
    };
    clusterMarker.spiderfy = () => this._spiderfy(clusterMarker);

    clusterMarker.on('click', (e) => {
      if (e && e.originalEvent) {
        L.DomEvent.stopPropagation(e.originalEvent);
      }
      this._activateCluster(clusterMarker);
    });
    clusterMarker.on('keypress', (e) => {
      if (e.originalEvent && e.originalEvent.keyCode !== 13) {
        return;
      }
      this._activateCluster(clusterMarker);
      if (this._map && this._map._container) {
        this._map._container.focus();
      }
    });

    return clusterMarker;
  },

  _activateCluster(clusterMarker, callback) {
    const map = this._map;
    const members = clusterMarker && clusterMarker._cagMembers;
    const done = () => {
      if (typeof callback === 'function') {
        callback();
      }
    };
    if (!map || !members || members.length < 2) {
      done();
      return;
    }

    const bounds = L.latLngBounds(members.map((m) => m.getLatLng()));
    const atMax = map.getBoundsZoom(bounds) >= map.getMaxZoom();
    if (atMax) {
      this._spiderfy(clusterMarker);
      done();
      return;
    }

    map.once('moveend', done);
    map.fitBounds(bounds, { paddingTopLeft: [24, 24], paddingBottomRight: [24, 24] });
  },

  _spiderfy(clusterMarker) {
    const map = this._map;
    const members = clusterMarker && clusterMarker._cagMembers;
    if (!map || !members || members.length < 2) {
      return;
    }

    this._restoreSpiderPositions();
    L.LayerGroup.prototype.removeLayer.call(this, clusterMarker);

    const center = map.latLngToLayerPoint(clusterMarker.getLatLng());
    const radius = Math.max(28, 10 + members.length * 6);
    members.forEach((marker, i) => {
      const angle = (Math.PI * 2 * i) / members.length - Math.PI / 2;
      const point = L.point(
        center.x + radius * Math.cos(angle),
        center.y + radius * Math.sin(angle)
      );
      marker._cagSpiderOrig = marker.getLatLng();
      marker.setLatLng(map.layerPointToLatLng(point));
      this._parentByMarker[L.stamp(marker)] = marker;
      if (!this.hasLayer(marker)) {
        L.LayerGroup.prototype.addLayer.call(this, marker);
      }
    });
  },

  _restoreSpiderPositions() {
    this._source.forEach((marker) => {
      if (marker && marker._cagSpiderOrig) {
        marker.setLatLng(marker._cagSpiderOrig);
        delete marker._cagSpiderOrig;
      }
    });
  },
});

export default GridMarkerCluster;
