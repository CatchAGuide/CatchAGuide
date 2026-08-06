/**
 * MapModalRail — viewport-synced listing rail + docked selection (not map overlay)
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
    this.countEl = modal.querySelector('[data-map-viewport-count]');
    this.toggleBtn = modal.querySelector('[data-map-rail-toggle]');
    this.toggleLabel = modal.querySelector('[data-map-rail-toggle-label]');
    this.i18n = this._parseI18n();
    this._itemsById = new Map();
    this._selectedId = null;
    this._railOpen = false;

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

  _bind() {
    this.listingMap.onViewportChange((payload) => this.renderViewport(payload));
    this.listingMap.onSelectionChange((item) => this.renderSelection(item));

    this.listEl.addEventListener('click', (e) => {
      const card = e.target.closest('[data-map-rail-id]');
      if (!card) return;
      const id = card.getAttribute('data-map-rail-id');
      if (e.target.closest('a[href]') && !e.target.closest('[data-map-rail-select]')) {
        return;
      }
      e.preventDefault();
      this.listingMap.selectById(id, { pan: true, source: 'rail' });
    });

    this.listEl.addEventListener('mouseover', (e) => {
      const card = e.target.closest('[data-map-rail-id]');
      if (!card) return;
      this.listingMap.highlightById(card.getAttribute('data-map-rail-id'), true);
    });

    this.listEl.addEventListener('mouseout', (e) => {
      const card = e.target.closest('[data-map-rail-id]');
      if (!card) return;
      const related = e.relatedTarget && e.relatedTarget.closest
        ? e.relatedTarget.closest('[data-map-rail-id]')
        : null;
      if (related === card) return;
      this.listingMap.highlightById(card.getAttribute('data-map-rail-id'), false);
    });

    if (this.toggleBtn) {
      this.toggleBtn.addEventListener('click', () => this.toggleRail());
    }

    this.modal.addEventListener('shown.bs.modal', () => {
      if (window.matchMedia('(min-width: 992px)').matches) {
        this.setRailOpen(true);
      }
      this.listingMap.emitViewportChange();
    });

    this.modal.addEventListener('hidden.bs.modal', () => {
      this.setRailOpen(false);
      this.listingMap.clearSelection();
    });

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && this.modal.classList.contains('show')) {
        this.listingMap.clearSelection();
      }
    });
  }

  toggleRail() {
    this.setRailOpen(!this._railOpen);
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
    if (this.listingMap && this.listingMap.invalidate) {
      setTimeout(() => {
        this.listingMap.invalidate();
        this.listingMap.emitViewportChange();
      }, 220);
    }
  }

  setItems(items) {
    this._itemsById = new Map();
    (items || []).forEach((item) => {
      if (item && item.id != null && item.variant !== 'gray') {
        this._itemsById.set(String(item.id), item);
      }
    });
  }

  renderViewport(payload = {}) {
    const items = Array.isArray(payload.items) ? payload.items : [];
    const count = payload.count != null ? payload.count : items.length;

    if (this.countEl) {
      if (count === 0) {
        this.countEl.textContent = this.i18n.in_map_area_zero || 'No listings in map area';
      } else if (count === 1) {
        this.countEl.textContent = (this.i18n.in_map_area_one || ':count listing in map area').replace(
          ':count',
          String(count)
        );
      } else {
        this.countEl.textContent = (this.i18n.in_map_area || ':count listings in map area').replace(
          ':count',
          String(count)
        );
      }
    }

    if (!this.listEl) return;

    if (!items.length) {
      this.listEl.innerHTML = '';
      if (this.emptyEl) this.emptyEl.hidden = false;
      return;
    }

    if (this.emptyEl) this.emptyEl.hidden = true;

    const html = items
      .map((item) => {
        const id = this._escape(String(item.id));
        const selected = String(item.id) === String(this._selectedId);
        const title = this._escape(item.title || '');
        const location = this._escape(item.location || '');
        const image = this._escape(item.image || '');
        const url = this._escape(item.url || '#');
        const price =
          item.priceLabel ||
          (item.price != null
            ? (this.i18n.price_from || 'From :price').replace(':price', String(item.price))
            : '');
        return `
          <article class="map-modal__rail-card${selected ? ' is-selected' : ''}" data-map-rail-id="${id}" data-map-rail-select>
            <div class="map-modal__rail-card-media${image ? '' : ' is-empty'}">
              ${image ? `<img src="${image}" alt="" loading="lazy" decoding="async" width="96" height="72">` : ''}
            </div>
            <div class="map-modal__rail-card-body">
              <h3 class="map-modal__rail-card-title">${title}</h3>
              ${location ? `<p class="map-modal__rail-card-location">${location}</p>` : ''}
              <div class="map-modal__rail-card-footer">
                ${price ? `<span class="map-modal__rail-card-price">${this._escape(price)}</span>` : ''}
                <a class="map-modal__rail-card-link" href="${url}">${this._escape(this.i18n.view_details || 'Details')}</a>
              </div>
            </div>
          </article>`;
      })
      .join('');

    this.listEl.innerHTML = html;

    if (this._selectedId) {
      const selectedCard = this.listEl.querySelector(
        `[data-map-rail-id="${this._escapeAttrSelector(this._selectedId)}"]`
      );
      if (selectedCard) {
        selectedCard.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
      }
    }
  }

  renderSelection(item) {
    // Keep the docked strip hidden — detail lives in the map popup.
    // Rail only highlights the matching card.
    if (this.selectionEl) {
      this.selectionEl.hidden = true;
      this.selectionEl.innerHTML = '';
    }

    if (!item) {
      this._selectedId = null;
      this.listEl.querySelectorAll('.map-modal__rail-card.is-selected').forEach((el) => {
        el.classList.remove('is-selected');
      });
      return;
    }

    this._selectedId = item.id != null ? String(item.id) : null;

    this.listEl.querySelectorAll('.map-modal__rail-card').forEach((el) => {
      el.classList.toggle('is-selected', el.getAttribute('data-map-rail-id') === this._selectedId);
    });

    const selectedCard = this.listEl.querySelector(
      `[data-map-rail-id="${this._escapeAttrSelector(this._selectedId)}"]`
    );
    if (selectedCard) {
      selectedCard.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
    }

    if (!window.matchMedia('(min-width: 992px)').matches) {
      this.setRailOpen(true);
    }
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
