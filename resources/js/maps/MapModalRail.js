import { itemKey } from './mapItemIdentity';

const MOBILE_MQ = '(max-width: 991.98px)';
const SHEET_SNAPS = {
  peek: 0.2,
  mid: 0.58,
  full: 0.88,
};
const SHEET_PEEK_MIN = 132;

/**
 * MapModalRail — viewport-synced listing rail + docked selection (not map overlay).
 * On mobile the rail is a draggable bottom sheet over a full-screen map.
 */
export default class MapModalRail {
  /**
   * @param {HTMLElement} modal
   * @param {import('./ListingMap').default} listingMap
   */
  constructor(modal, listingMap) {
    this.modal = modal;
    this.listingMap = listingMap;
    this.rail = modal.querySelector('[data-map-modal-rail]');
    this.listEl = modal.querySelector('[data-map-rail-list]');
    this.emptyEl = modal.querySelector('[data-map-rail-empty]');
    this.selectionEl = modal.querySelector('[data-map-selection]');
    this.countEls = modal.querySelectorAll('[data-map-viewport-count]');
    this.toggleBtn = modal.querySelector('[data-map-rail-toggle]');
    this.toggleLabel = modal.querySelector('[data-map-rail-toggle-label]');
    this.handleEl = modal.querySelector('[data-map-rail-handle]');
    this.headingEl = modal.querySelector('[data-map-rail-heading]');
    this.fabBtn = modal.querySelector('[data-map-sheet-fab]');
    this.fabLabel = modal.querySelector('[data-map-sheet-fab-label]');
    this.i18n = this._parseI18n();
    this._itemsById = new Map();
    this._selectedId = null;
    this._railOpen = false;
    this._snap = 'mid';
    this._snapBeforeSelection = 'mid';
    this._drag = null;
    this._mq = typeof window !== 'undefined' && window.matchMedia ? window.matchMedia(MOBILE_MQ) : null;

    if (!this.rail || !this.listEl) {
      return;
    }

    this._bind();
  }

  _parseI18n() {
    try {
      const raw = this.modal.getAttribute('data-maps-i18n');
      return raw ? JSON.parse(raw) : {};
    } catch (e) {
      return {};
    }
  }

  _isMobile() {
    return !!(this._mq && this._mq.matches);
  }

  _bind() {
    this.listingMap.onViewportChange((payload) => this.renderViewport(payload));
    this.listingMap.onSelectionChange((item, opts) => this.renderSelection(item, opts));
    this.listingMap.onPreviewChange((item) => this.setHoveredId(item ? itemKey(item) : null));

    this.listEl.addEventListener('click', (e) => {
      const card = e.target.closest('[data-map-rail-id]');
      if (!card) return;
      // Allow "View details" (and any real link) to navigate to the product page
      if (e.target.closest('a[href]')) {
        return;
      }
      const id = card.getAttribute('data-map-rail-id');
      // Explicit "Show on map" zooms to the pin
      if (e.target.closest('[data-map-rail-zoom]')) {
        e.preventDefault();
        e.stopPropagation();
        this.listingMap.zoomToById(id);
        return;
      }
      e.preventDefault();
      this.listingMap.selectById(id, { pan: false, allowZoom: false, source: 'rail' });
    });

    this.listEl.addEventListener('dblclick', (e) => {
      const card = e.target.closest('[data-map-rail-id]');
      if (!card) return;
      if (e.target.closest('a[href]')) return;
      e.preventDefault();
      const id = card.getAttribute('data-map-rail-id');
      this.listingMap.zoomToById(id);
    });

    this.listEl.addEventListener('mouseover', (e) => {
      const card = e.target.closest('[data-map-rail-id]');
      if (!card) return;
      const id = card.getAttribute('data-map-rail-id');
      if (this._hoverRailId === id) return;
      this._hoverRailId = id;
      this.listingMap.previewById(id, true);
    });

    this.listEl.addEventListener('mouseout', (e) => {
      const card = e.target.closest('[data-map-rail-id]');
      if (!card) return;
      const related = e.relatedTarget && e.relatedTarget.closest
        ? e.relatedTarget.closest('[data-map-rail-id]')
        : null;
      if (related === card) return;
      const id = card.getAttribute('data-map-rail-id');
      if (this._hoverRailId === id) {
        this._hoverRailId = null;
      }
      this.listingMap.previewById(id, false);
    });

    if (this.toggleBtn) {
      this.toggleBtn.addEventListener('click', () => this.toggleRail());
    }

    if (this.fabBtn) {
      this.fabBtn.addEventListener('click', () => this._toggleSheetFromFab());
    }

    if (this.selectionEl) {
      this.selectionEl.addEventListener('click', (e) => {
        if (e.target.closest('[data-map-selection-dismiss]')) {
          e.preventDefault();
          e.stopPropagation();
          this.listingMap.clearSelection();
        }
      });
    }

    this._bindSheetDrag();

    if (this._mq) {
      const onMq = () => this._syncLayoutMode();
      if (typeof this._mq.addEventListener === 'function') {
        this._mq.addEventListener('change', onMq);
      } else if (typeof this._mq.addListener === 'function') {
        this._mq.addListener(onMq);
      }
    }

    this.modal.addEventListener('shown.bs.modal', () => {
      this._syncLayoutMode(true);
      this.listingMap.emitViewportChange();
    });

    this.modal.addEventListener('hidden.bs.modal', () => {
      this._hideSelectionCard();
      this.setRailOpen(false);
      this.listingMap.clearSelection();
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && this.modal.classList.contains('show')) {
        this.listingMap.clearSelection();
      }
    });
  }

  _syncLayoutMode(fromShow) {
    if (this._isMobile()) {
      this.setSnap(fromShow ? 'mid' : this._snap || 'mid', { animate: false });
      return;
    }
    this.modal.classList.remove(
      'map-modal--sheet-peek',
      'map-modal--sheet-mid',
      'map-modal--sheet-full',
      'map-modal--has-selection',
      'map-modal--sheet-dragging'
    );
    this.rail.style.height = '';
    this.modal.style.removeProperty('--map-sheet-h');
    this.modal.style.removeProperty('--map-header-h');
    this._hideSelectionCard();
    this.setRailOpen(true);
    if (this.listingMap && this.listingMap.setOverlayPadding) {
      this.listingMap.setOverlayPadding({ top: 0, bottom: 0 });
    }
  }

  toggleRail() {
    if (this._isMobile()) {
      this.setSnap(this._snap === 'peek' ? 'mid' : 'peek');
      return;
    }
    this.setRailOpen(!this._railOpen);
  }

  _toggleSheetFromFab() {
    if (!this._isMobile()) return;
    if (this.modal.classList.contains('map-modal--has-selection')) {
      this.listingMap.clearSelection();
      this.setSnap('mid');
      return;
    }
    this.setSnap(this._snap === 'peek' ? 'mid' : 'peek');
  }

  setRailOpen(open) {
    this._railOpen = !!open;
    this.modal.classList.toggle('map-modal--rail-open', this._railOpen);
    if (this.toggleBtn) {
      this.toggleBtn.setAttribute('aria-expanded', this._railOpen ? 'true' : 'false');
    }
    if (this.toggleLabel) {
      this.toggleLabel.textContent = this._railOpen
        ? this.i18n.hide_list || 'Hide list'
        : this.i18n.show_list || 'Show list';
    }
    if (this._isMobile()) {
      this._syncFab();
      return;
    }
    if (this.listingMap && this.listingMap.invalidate) {
      setTimeout(() => {
        this.listingMap.invalidate();
        this.listingMap.emitViewportChange();
      }, 220);
    }
  }

  setSnap(snap, opts = {}) {
    const next = SHEET_SNAPS[snap] ? snap : 'mid';
    this._snap = next;
    this._railOpen = next !== 'peek';
    this.modal.classList.toggle('map-modal--rail-open', this._railOpen);

    const heights = this._snapHeights();
    this._applySheetHeight(heights[next], { animate: opts.animate !== false });
    this._syncSheetClasses();
    this._syncFab();
    this._syncOverlayPadding();

    if (this.toggleBtn) {
      this.toggleBtn.setAttribute('aria-expanded', this._railOpen ? 'true' : 'false');
    }
    if (this.toggleLabel) {
      this.toggleLabel.textContent = this._railOpen
        ? this.i18n.hide_list || 'Hide list'
        : this.i18n.show_list || 'Show list';
    }
  }

  _bodyHeight() {
    const body = this.modal.querySelector('.map-modal__body') || this.modal;
    return body.getBoundingClientRect().height || window.innerHeight;
  }

  _headerHeight() {
    const chrome = this.modal.querySelector('.map-modal__chrome');
    return chrome ? Math.round(chrome.getBoundingClientRect().height) : 0;
  }

  _snapHeights() {
    const bodyH = this._bodyHeight();
    return {
      peek: Math.max(SHEET_PEEK_MIN, Math.round(bodyH * SHEET_SNAPS.peek)),
      mid: Math.round(bodyH * SHEET_SNAPS.mid),
      full: Math.round(bodyH * SHEET_SNAPS.full),
    };
  }

  _applySheetHeight(px, opts = {}) {
    const height = Math.max(SHEET_PEEK_MIN, Math.round(px));
    this.modal.style.setProperty('--map-sheet-h', `${height}px`);
    this.rail.style.height = `${height}px`;
    if (opts.animate === false) {
      this.rail.style.transition = 'none';
      requestAnimationFrame(() => {
        this.rail.style.transition = '';
      });
    }
  }

  _syncSheetClasses() {
    this.modal.classList.toggle('map-modal--sheet-peek', this._isMobile() && this._snap === 'peek');
    this.modal.classList.toggle('map-modal--sheet-mid', this._isMobile() && this._snap === 'mid');
    this.modal.classList.toggle('map-modal--sheet-full', this._isMobile() && this._snap === 'full');
  }

  _syncFab() {
    if (!this.fabBtn) return;
    const showMap = this._snap !== 'peek';
    this.fabBtn.classList.toggle('is-list', !showMap);
    this.fabBtn.setAttribute('aria-expanded', showMap ? 'true' : 'false');
    if (this.fabLabel) {
      this.fabLabel.textContent = showMap
        ? this.i18n.show_map || 'Map'
        : this.i18n.show_list || 'Show list';
    }
  }

  _syncOverlayPadding() {
    if (!this.listingMap || !this.listingMap.setOverlayPadding) return;
    const headerH = this._headerHeight();
    this.modal.style.setProperty('--map-header-h', `${headerH}px`);
    if (!this._isMobile()) {
      this.listingMap.setOverlayPadding({ top: 0, bottom: 0 });
      return;
    }
    if (this.modal.classList.contains('map-modal--has-selection')) {
      const cardH = this.selectionEl && !this.selectionEl.hidden
        ? this.selectionEl.getBoundingClientRect().height + 24
        : 220;
      this.listingMap.setOverlayPadding({ top: headerH, bottom: cardH });
      return;
    }
    const sheetH = this.rail.getBoundingClientRect().height || this._snapHeights()[this._snap];
    this.listingMap.setOverlayPadding({ top: headerH, bottom: sheetH });
  }

  _bindSheetDrag() {
    const startEls = [this.handleEl, this.headingEl].filter(Boolean);
    if (!startEls.length) return;

    const onPointerDown = (e) => {
      if (!this._isMobile() || this.modal.classList.contains('map-modal--has-selection')) return;
      if (e.pointerType === 'mouse' && e.button !== 0) return;
      this._drag = {
        startY: e.clientY,
        startH: this.rail.getBoundingClientRect().height,
        pointerId: e.pointerId,
        lastY: e.clientY,
        lastT: Date.now(),
        velocity: 0,
      };
      this.modal.classList.add('map-modal--sheet-dragging');
      this.rail.style.transition = 'none';
      try {
        e.currentTarget.setPointerCapture(e.pointerId);
      } catch (err) {
        /* ignore */
      }
      e.preventDefault();
    };

    const onPointerMove = (e) => {
      if (!this._drag) return;
      const now = Date.now();
      const dy = this._drag.startY - e.clientY;
      const nextH = this._drag.startH + dy;
      const heights = this._snapHeights();
      const clamped = Math.max(heights.peek, Math.min(heights.full, nextH));
      const dt = Math.max(1, now - this._drag.lastT);
      this._drag.velocity = (this._drag.lastY - e.clientY) / dt;
      this._drag.lastY = e.clientY;
      this._drag.lastT = now;
      this._applySheetHeight(clamped, { animate: false });
    };

    const onPointerUp = () => {
      if (!this._drag) return;
      this.modal.classList.remove('map-modal--sheet-dragging');
      this.rail.style.transition = '';
      const height = this.rail.getBoundingClientRect().height;
      const velocity = this._drag.velocity;
      this._drag = null;
      this.setSnap(this._nearestSnap(height, velocity));
    };

    startEls.forEach((el) => {
      el.addEventListener('pointerdown', onPointerDown);
      el.addEventListener('pointermove', onPointerMove);
      el.addEventListener('pointerup', onPointerUp);
      el.addEventListener('pointercancel', onPointerUp);
    });
  }

  _nearestSnap(height, velocity = 0) {
    const heights = this._snapHeights();
    if (Math.abs(velocity) > 0.45) {
      if (velocity > 0) {
        return height > heights.mid ? 'full' : 'mid';
      }
      return height < heights.mid ? 'peek' : 'mid';
    }
    const entries = Object.entries(heights);
    let best = 'mid';
    let bestDist = Infinity;
    entries.forEach(([name, value]) => {
      const dist = Math.abs(value - height);
      if (dist < bestDist) {
        bestDist = dist;
        best = name;
      }
    });
    return best;
  }

  setItems(items) {
    this._itemsById = new Map();
    (items || []).forEach((item) => {
      const key = itemKey(item);
      if (key && item.variant !== 'gray') {
        this._itemsById.set(key, item);
      }
    });
  }

  renderViewport(payload = {}) {
    const items = Array.isArray(payload.items) ? payload.items : [];
    const count = payload.count != null ? payload.count : items.length;
    const label = this._countLabel(count);

    this.countEls.forEach((el) => {
      el.textContent = label;
    });

    if (!this.listEl) return;

    if (!items.length) {
      this.listEl.innerHTML = '';
      if (this.emptyEl) this.emptyEl.hidden = false;
      return;
    }

    if (this.emptyEl) this.emptyEl.hidden = true;

    const html = items
      .map((item) => {
        const key = itemKey(item);
        const id = this._escape(String(key || item.id));
        const selected = key != null && String(key) === String(this._selectedId);
        const title = this._escape(item.title || '');
        const location = this._escape(item.location || '');
        const image = this._escape(item.image || '');
        const url = this._escape(item.url || '#');
        const module = this._normalizeModule(item.module || item.pillar || 'tour');
        const moduleLabel = this._escape(item.moduleLabel || item.badge || '');
        const price =
          item.priceLabel ||
          (item.price != null
            ? (this.i18n.price_from || 'From :price').replace(':price', String(item.price))
            : '');
        const duration = this._escape(item.durationLabel || '');
        const guests = this._escape(item.guestsLabel || '');
        const boat = this._escape(item.boatLabel || '');
        const rating = item.rating != null && Number(item.rating) > 0 ? Number(item.rating).toFixed(1) : '';
        const reviewCount = item.reviewCount != null ? Number(item.reviewCount) : null;
        const cta = this._escape(item.cta || this.i18n.view_details || 'Details');
        const showOnMap = this._escape(this.i18n.show_on_map || 'Show on map');

        const metaBits = [duration, guests, boat].filter(Boolean);
        const metaHtml = metaBits.length
          ? `<ul class="map-modal__rail-card-meta">${metaBits
              .map((bit) => `<li>${bit}</li>`)
              .join('')}</ul>`
          : '';

        const ratingHtml = rating
          ? `<span class="map-modal__rail-card-rating">
              <span class="map-modal__rail-card-rating-value">${rating}</span>
              ${
                reviewCount != null
                  ? `<span class="map-modal__rail-card-rating-count">${this._escape(
                      (this.i18n.reviews || '(:count)').replace(':count', String(reviewCount))
                    )}</span>`
                  : ''
              }
            </span>`
          : '';

        return `
          <article class="map-modal__rail-card map-modal__rail-card--expanded${selected ? ' is-selected' : ''}" data-map-rail-id="${id}" data-map-rail-select data-map-module="${module}">
            <div class="map-modal__rail-card-media${image ? '' : ' is-empty'}">
              ${image ? `<img src="${image}" alt="" loading="lazy" decoding="async" width="128" height="112">` : ''}
              <button type="button" class="map-modal__rail-card-zoom" data-map-rail-zoom aria-label="${showOnMap}" title="${showOnMap}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                  <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                </svg>
              </button>
            </div>
            <div class="map-modal__rail-card-body">
              <div class="map-modal__rail-card-topline">
                ${moduleLabel ? `<span class="map-modal__rail-card-module map-modal__rail-card-module--${module}">${moduleLabel}</span>` : '<span></span>'}
                ${ratingHtml}
              </div>
              <h3 class="map-modal__rail-card-title">${title}</h3>
              ${location ? `<p class="map-modal__rail-card-location">${location}</p>` : ''}
              ${metaHtml}
              <div class="map-modal__rail-card-footer">
                ${price ? `<span class="map-modal__rail-card-price">${this._escape(price)}</span>` : '<span></span>'}
                <div class="map-modal__rail-card-actions">
                  <button type="button" class="map-modal__rail-card-zoom-text" data-map-rail-zoom title="${showOnMap}">${showOnMap}</button>
                  <a class="map-modal__rail-card-link" href="${url}">${cta}</a>
                </div>
              </div>
            </div>
          </article>`;
      })
      .join('');

    this.listEl.innerHTML = html;

    if (this._selectedId && !this._isMobile()) {
      const selectedCard = this.listEl.querySelector(
        `[data-map-rail-id="${this._escapeAttrSelector(this._selectedId)}"]`
      );
      if (selectedCard) {
        selectedCard.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
      }
    }
  }

  _countLabel(count) {
    if (count === 0) {
      return this.i18n.in_map_area_zero || 'No listings in map area';
    }
    if (count === 1) {
      return (this.i18n.in_map_area_one || ':count listing in map area').replace(':count', String(count));
    }
    return (this.i18n.in_map_area || ':count listings in map area').replace(':count', String(count));
  }

  renderSelection(item, opts = {}) {
    if (!item) {
      const hadSelectionCard = this.modal.classList.contains('map-modal--has-selection');
      this._selectedId = null;
      this._hideSelectionCard();
      this.listEl.querySelectorAll('.map-modal__rail-card.is-selected').forEach((el) => {
        el.classList.remove('is-selected');
      });
      if (this._isMobile() && hadSelectionCard) {
        this.setSnap(this._snapBeforeSelection || 'peek');
      } else if (this._isMobile()) {
        this._syncOverlayPadding();
      }
      return;
    }

    this._selectedId = itemKey(item);

    this.listEl.querySelectorAll('.map-modal__rail-card').forEach((el) => {
      el.classList.toggle('is-selected', el.getAttribute('data-map-rail-id') === this._selectedId);
    });

    const selectedCard = this.listEl.querySelector(
      `[data-map-rail-id="${this._escapeAttrSelector(this._selectedId)}"]`
    );
    if (selectedCard && !this._isMobile()) {
      selectedCard.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
    }

    const fromMap = opts.source === 'map' || opts.source === 'rail-zoom';
    if (this._isMobile() && fromMap) {
      this._snapBeforeSelection = this._snap;
      this._showSelectionCard(item);
      return;
    }

    if (this._isMobile() && this.selectionEl && !this.selectionEl.hidden) {
      this._showSelectionCard(item);
    }
  }

  _showSelectionCard(item) {
    if (!this.selectionEl) return;
    this.selectionEl.innerHTML = this._selectionHtml(item);
    this.selectionEl.hidden = false;
    this.modal.classList.add('map-modal--has-selection');
    this._syncOverlayPadding();
    requestAnimationFrame(() => this._syncOverlayPadding());
  }

  _hideSelectionCard() {
    if (this.selectionEl) {
      this.selectionEl.hidden = true;
      this.selectionEl.innerHTML = '';
    }
    this.modal.classList.remove('map-modal--has-selection');
  }

  _selectionHtml(item) {
    const title = this._escape(item.title || '');
    const location = this._escape(item.location || '');
    const image = this._escape(item.image || '');
    const url = this._escape(item.url || '#');
    const module = this._normalizeModule(item.module || item.pillar || 'tour');
    const moduleLabel = this._escape(item.moduleLabel || item.badge || '');
    const price =
      item.priceLabel ||
      (item.price != null
        ? (this.i18n.price_from || 'From :price').replace(':price', String(item.price))
        : '');
    const duration = this._escape(item.durationLabel || '');
    const guests = this._escape(item.guestsLabel || '');
    const boat = this._escape(item.boatLabel || '');
    const rating = item.rating != null && Number(item.rating) > 0 ? Number(item.rating).toFixed(1) : '';
    const reviewCount = item.reviewCount != null ? Number(item.reviewCount) : null;
    const cta = this._escape(item.cta || this.i18n.view_details || 'Details');
    const closeLabel = this._escape(this.i18n.close_map || 'Close');

    const metaBits = [duration, guests, boat].filter(Boolean);
    const metaHtml = metaBits.length
      ? `<ul class="map-modal__selection-meta">${metaBits.map((bit) => `<li>${bit}</li>`).join('')}</ul>`
      : '';
    const ratingHtml = rating
      ? `<span class="map-modal__selection-rating">
          <span class="map-modal__selection-rating-value">${rating}</span>
          ${
            reviewCount != null
              ? `<span class="map-modal__selection-rating-count">${this._escape(
                  (this.i18n.reviews || '(:count)').replace(':count', String(reviewCount))
                )}</span>`
              : ''
          }
        </span>`
      : '';

    return `
      <article class="map-modal__selection-card" data-map-module="${module}">
        <button type="button" class="map-modal__selection-dismiss" data-map-selection-dismiss aria-label="${closeLabel}">&times;</button>
        <a class="map-modal__selection-link" href="${url}">
          <div class="map-modal__selection-media${image ? '' : ' is-empty'}">
            ${image ? `<img src="${image}" alt="" loading="lazy" decoding="async">` : ''}
          </div>
          <div class="map-modal__selection-body">
            <div class="map-modal__selection-topline">
              ${moduleLabel ? `<span class="map-modal__rail-card-module map-modal__rail-card-module--${module}">${moduleLabel}</span>` : '<span></span>'}
              ${ratingHtml}
            </div>
            <h3 class="map-modal__selection-title">${title}</h3>
            ${location ? `<p class="map-modal__selection-location">${location}</p>` : ''}
            ${metaHtml}
            <div class="map-modal__selection-footer">
              ${price ? `<span class="map-modal__selection-price">${this._escape(price)}</span>` : '<span></span>'}
              <span class="map-modal__selection-cta">${cta}</span>
            </div>
          </div>
        </a>
      </article>`;
  }

  setHoveredId(id) {
    const hoverId = id != null ? String(id) : null;
    this.listEl.querySelectorAll('.map-modal__rail-card').forEach((el) => {
      const match = hoverId && el.getAttribute('data-map-rail-id') === hoverId;
      el.classList.toggle('is-hovered', !!match);
    });
    if (hoverId && hoverId !== String(this._selectedId || '') && !this._isMobile()) {
      const card = this.listEl.querySelector(`[data-map-rail-id="${this._escapeAttrSelector(hoverId)}"]`);
      if (card) {
        card.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
      }
    }
  }

  _normalizeModule(value) {
    const v = String(value || '').toLowerCase();
    if (v === 'trip' || v === 'trips') return 'trip';
    if (v === 'camp' || v === 'camps' || v === 'vacation') return 'camp';
    return 'tour';
  }

  _escapeAttrSelector(id) {
    if (typeof CSS !== 'undefined' && typeof CSS.escape === 'function') {
      return CSS.escape(String(id));
    }
    return String(id).replace(/\\/g, '\\\\').replace(/"/g, '\\"');
  }

  _escape(value) {
    return String(value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }
}
