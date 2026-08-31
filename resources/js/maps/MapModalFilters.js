/**
 * MapModalFilters — chip bar synced to existing listing filter form (#filterContainer)
 */
const CHIP_SECTIONS = {
  filters: null,
  duration: ['duration_types[]', 'duration_'],
  price: ['price_min', 'price_max'],
  target_fish: ['target_fish[]', 'fish_'],
  methods: ['methods[]', 'method_'],
  people: ['num_persons', 'persons_'],
};

export default class MapModalFilters {
  /**
   * @param {HTMLElement} modal
   */
  constructor(modal) {
    this.modal = modal;
    this.formId = modal.getAttribute('data-map-filter-form') || 'filterContainer';
    this.chipsRoot = modal.querySelector('[data-map-filter-chips]');
    this.panel = modal.querySelector('[data-map-filter-panel]');
    this.panelBody = modal.querySelector('[data-map-filter-panel-body]');
    this.panelTitle = modal.querySelector('[data-map-filter-panel-title]');
    this.activeChip = null;

    if (!this.chipsRoot || !this.panel) {
      return;
    }

    this._bind();
    this.refreshChipStates();
  }

  get form() {
    return document.getElementById(this.formId);
  }

  _bind() {
    this.chipsRoot.querySelectorAll('[data-map-chip]').forEach((btn) => {
      btn.addEventListener('click', () => {
        const key = btn.getAttribute('data-map-chip');
        if (this.activeChip === key && !this.panel.hidden) {
          this.closePanel();
          return;
        }
        this.openPanel(key, btn);
      });
    });

    const closeBtn = this.modal.querySelector('[data-map-filter-panel-close]');
    if (closeBtn) {
      closeBtn.addEventListener('click', () => this.closePanel());
    }

    const applyBtn = this.modal.querySelector('[data-map-filter-apply]');
    if (applyBtn) {
      applyBtn.addEventListener('click', () => {
        this._commitPanelToForm();
        this.closePanel();
        this._triggerFormFilter();
      });
    }

    const clearBtn = this.modal.querySelector('[data-map-filter-clear]');
    if (clearBtn) {
      clearBtn.addEventListener('click', () => {
        this._clearSectionInPanel();
      });
    }

    this.modal.addEventListener('shown.bs.modal', () => this.refreshChipStates());

    const form = this.form;
    if (form) {
      form.addEventListener('change', () => this.refreshChipStates());
    }
  }

  openPanel(key, chipBtn) {
    const form = this.form;
    if (!form) {
      return;
    }

    this.activeChip = key;
    this.chipsRoot.querySelectorAll('[data-map-chip]').forEach((b) => {
      b.setAttribute('aria-expanded', b === chipBtn ? 'true' : 'false');
      b.classList.toggle('is-open', b === chipBtn);
    });

    const title = chipBtn ? chipBtn.textContent.trim() : key;
    if (this.panelTitle) {
      this.panelTitle.textContent = title;
    }

    this.panelBody.innerHTML = this._buildPanelHtml(key, form);
    this.panel.hidden = false;
    this.modal.classList.add('map-modal--filters-open');
  }

  closePanel() {
    this.panel.hidden = true;
    this.modal.classList.remove('map-modal--filters-open');
    this.activeChip = null;
    this.chipsRoot.querySelectorAll('[data-map-chip]').forEach((b) => {
      b.setAttribute('aria-expanded', 'false');
      b.classList.remove('is-open');
    });
  }

  _buildPanelHtml(key, form) {
    if (key === 'price') {
      const min = form.querySelector('#price_min_main, [name="price_min"]');
      const max = form.querySelector('#price_max_main, [name="price_max"]');
      const minVal = min ? min.value : '';
      const maxVal = max ? max.value : '';
      return `
        <div class="map-modal__filter-price">
          <label>
            <span>Min €</span>
            <input type="number" inputmode="numeric" data-panel-price-min value="${this._escape(minVal)}" min="0" step="1">
          </label>
          <label>
            <span>Max €</span>
            <input type="number" inputmode="numeric" data-panel-price-max value="${this._escape(maxVal)}" min="0" step="1">
          </label>
        </div>`;
    }

    if (key === 'filters') {
      return this._cloneCheckboxGroups(form, [
        'target_fish[]',
        'methods[]',
        'water[]',
        'duration_types[]',
        'num_persons',
      ]);
    }

    const meta = CHIP_SECTIONS[key];
    if (!meta) {
      return '<p class="map-modal__filter-empty">—</p>';
    }

    const name = meta[0];
    return this._cloneCheckboxGroups(form, [name]);
  }

  _cloneCheckboxGroups(form, names) {
    const blocks = [];
    names.forEach((name) => {
      const inputs = form.querySelectorAll(`input[name="${name}"]`);
      if (!inputs.length) return;

      const options = [];
      inputs.forEach((input) => {
        if (input.closest('.d-none') && !input.checked) {
          // still include checked hidden ones
        }
        const label = form.querySelector(`label[for="${input.id}"]`);
        const text = label ? label.textContent.replace(/\s+/g, ' ').trim() : input.value;
        options.push(`
          <label class="map-modal__filter-option">
            <input type="checkbox" data-panel-filter-name="${this._escape(name)}" value="${this._escape(input.value)}" ${input.checked ? 'checked' : ''}>
            <span>${this._escape(text)}</span>
          </label>`);
      });

      if (options.length) {
        blocks.push(`<div class="map-modal__filter-group">${options.join('')}</div>`);
      }
    });

    if (!blocks.length) {
      return '<p class="map-modal__filter-empty">—</p>';
    }
    return blocks.join('');
  }

  _commitPanelToForm() {
    const form = this.form;
    if (!form || !this.panelBody) return;

    if (this.activeChip === 'price') {
      const minInput = this.panelBody.querySelector('[data-panel-price-min]');
      const maxInput = this.panelBody.querySelector('[data-panel-price-max]');
      const formMin = form.querySelector('#price_min_main, [name="price_min"]');
      const formMax = form.querySelector('#price_max_main, [name="price_max"]');
      if (formMin && minInput) formMin.value = minInput.value;
      if (formMax && maxInput) formMax.value = maxInput.value;
      return;
    }

    const panelInputs = this.panelBody.querySelectorAll('[data-panel-filter-name]');
    const byName = {};
    panelInputs.forEach((input) => {
      const name = input.getAttribute('data-panel-filter-name');
      if (!byName[name]) byName[name] = [];
      if (input.checked) byName[name].push(input.value);
    });

    Object.keys(byName).forEach((name) => {
      const selected = new Set(byName[name]);
      form.querySelectorAll(`input[name="${name}"]`).forEach((input) => {
        const shouldCheck = selected.has(input.value);
        if (name === 'num_persons') {
          input.checked = shouldCheck;
        } else {
          input.checked = shouldCheck;
        }
      });
    });
  }

  _clearSectionInPanel() {
    if (!this.panelBody) return;
    this.panelBody.querySelectorAll('input[type="checkbox"]').forEach((input) => {
      input.checked = false;
    });
    this.panelBody.querySelectorAll('[data-panel-price-min], [data-panel-price-max]').forEach((input) => {
      input.value = '';
    });
  }

  _triggerFormFilter() {
    const form = this.form;
    if (!form) return;

    const firstCheckbox = form.querySelector('.filter-checkbox');
    if (firstCheckbox) {
      firstCheckbox.dispatchEvent(new Event('change', { bubbles: true }));
      return;
    }

    form.dispatchEvent(new Event('change', { bubbles: true }));
  }

  refreshChipStates() {
    const form = this.form;
    if (!form || !this.chipsRoot) return;

    this.chipsRoot.querySelectorAll('[data-map-chip]').forEach((btn) => {
      const key = btn.getAttribute('data-map-chip');
      let active = false;

      if (key === 'price') {
        const min = form.querySelector('#price_min_main, [name="price_min"]');
        const max = form.querySelector('#price_max_main, [name="price_max"]');
        active = !!(min && min.value) || !!(max && max.value);
      } else if (key === 'filters') {
        active = !!form.querySelector(
          'input.filter-checkbox:checked, input[name="target_fish[]"]:checked, input[name="methods[]"]:checked, input[name="duration_types[]"]:checked, input[name="num_persons"]:checked'
        );
      } else if (CHIP_SECTIONS[key]) {
        const name = CHIP_SECTIONS[key][0];
        active = !!form.querySelector(`input[name="${name}"]:checked`);
      }

      btn.classList.toggle('is-active', active);
    });
  }

  _escape(value) {
    return String(value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }
}
