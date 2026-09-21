@once
<script>
(function () {
    function openSheet(id) {
        var panel = document.getElementById(id);
        if (!panel) {
            return;
        }
        var backdrop = document.querySelector('[data-mobile-search-sheet-backdrop="' + id + '"]');
        var trigger = document.querySelector('[data-mobile-search-sheet-open="' + id + '"]');

        panel.classList.add('is-open');
        panel.setAttribute('aria-hidden', 'false');
        if (backdrop) {
            backdrop.classList.add('is-open');
        }
        if (trigger) {
            trigger.setAttribute('aria-expanded', 'true');
        }
        document.body.classList.add('is-mobile-search-open');
        if (typeof window.__cagInitHeaderPlaces === 'function') {
            window.__cagInitHeaderPlaces();
        }
    }

    function closeSheet(panel) {
        var id = panel.id;
        var backdrop = document.querySelector('[data-mobile-search-sheet-backdrop="' + id + '"]');
        var trigger = document.querySelector('[data-mobile-search-sheet-open="' + id + '"]');

        panel.classList.remove('is-open');
        panel.setAttribute('aria-hidden', 'true');
        if (backdrop) {
            backdrop.classList.remove('is-open');
        }
        if (trigger) {
            trigger.setAttribute('aria-expanded', 'false');
        }
        document.body.classList.remove('is-mobile-search-open');
    }

    function closeAllSheets() {
        var openPanels = document.querySelectorAll('[data-mobile-search-sheet].is-open');
        for (var i = 0; i < openPanels.length; i++) {
            closeSheet(openPanels[i]);
        }
    }

    document.addEventListener('click', function (event) {
        var chip = event.target.closest('[data-mobile-search-chip]');
        if (chip) {
            var form = chip.closest('form');
            if (!form) {
                return;
            }
            var chipLabel = chip.getAttribute('data-mobile-search-chip') || '';
            var chipValue = chip.getAttribute('data-mobile-search-chip-value') || chipLabel;
            var placeInput = form.querySelector('input[name="place"]');
            var countrySelect = form.querySelector('select[name="country"]');
            if (placeInput) {
                placeInput.value = chipLabel;
                placeInput.dispatchEvent(new Event('input', { bubbles: true }));
                ['placeLat', 'placeLng', 'city', 'country', 'region'].forEach(function (name) {
                    var hidden = form.querySelector('input[name="' + name + '"]');
                    if (hidden) {
                        hidden.value = '';
                    }
                });
            } else if (countrySelect && chipValue) {
                var needle = chipValue.toLowerCase();
                for (var i = 0; i < countrySelect.options.length; i++) {
                    var option = countrySelect.options[i];
                    if ((option.value || '').toLowerCase() === needle
                        || (option.text || '').trim().toLowerCase() === needle) {
                        countrySelect.value = option.value;
                        countrySelect.dispatchEvent(new Event('change', { bubbles: true }));
                        break;
                    }
                }
            }
            return;
        }

        var openTrigger = event.target.closest('[data-mobile-search-sheet-open]');
        if (openTrigger) {
            openSheet(openTrigger.getAttribute('data-mobile-search-sheet-open'));
            return;
        }

        var closeControl = event.target.closest('[data-mobile-search-sheet-close]');
        if (closeControl) {
            var panel = closeControl.closest('[data-mobile-search-sheet]');
            if (panel) {
                closeSheet(panel);
                return;
            }
            closeAllSheets();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeAllSheets();
        }
    });
})();
</script>
@endonce
