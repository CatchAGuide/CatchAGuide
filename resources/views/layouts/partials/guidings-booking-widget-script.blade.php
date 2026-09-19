<script data-guidings-booking-widget-script>
(function () {
    if (window.__guidingsBookWidgetInit) {
        return;
    }
    window.__guidingsBookWidgetInit = true;

    function formatBookPrice(amount) {
        return parseFloat(amount).toFixed(2).replace(/\.00$/, '') + '€';
    }

    function optionsFor(root) {
        try {
            var parsed = JSON.parse(root.getAttribute('data-guidings-book-options') || '[]');
            return Array.isArray(parsed) ? parsed : [];
        } catch (error) {
            return [];
        }
    }

    function applySelection(root, index) {
        var options = optionsFor(root);
        if (!options.length) {
            return;
        }

        var nextIndex = Math.max(0, Math.min(index, options.length - 1));
        var option = options[nextIndex];
        var singular = root.getAttribute('data-guidings-book-person-singular') || '';
        var plural = root.getAttribute('data-guidings-book-person-plural') || '';
        var personInput = root.querySelector('[data-guidings-book-person]');
        var priceEl = root.querySelector('[data-guidings-book-price]');
        var perPersonEl = root.querySelector('[data-guidings-book-per-person]');
        var breakdownEl = root.querySelector('[data-guidings-book-breakdown]');
        var labelEl = root.querySelector('[data-guidings-book-label]');
        var minusBtn = root.querySelector('[data-guidings-book-delta="-1"]');
        var plusBtn = root.querySelector('[data-guidings-book-delta="1"]');
        var personCount = parseInt(option.person, 10) || 1;
        var perPersonAmount = Math.round(parseFloat(option.amount) / personCount);

        root.setAttribute('data-guidings-book-index', String(nextIndex));

        if (personInput) {
            personInput.value = String(personCount);
        }
        if (priceEl) {
            priceEl.textContent = formatBookPrice(option.amount);
        }
        if (perPersonEl) {
            perPersonEl.textContent = formatBookPrice(perPersonAmount);
        }
        if (breakdownEl) {
            breakdownEl.hidden = personCount <= 1;
        }
        if (labelEl) {
            labelEl.textContent = personCount + ' ' + (personCount === 1 ? singular : plural);
        }
        if (minusBtn) {
            minusBtn.disabled = nextIndex <= 0;
        }
        if (plusBtn) {
            plusBtn.disabled = nextIndex >= options.length - 1;
        }
    }

    function setDateOnWidgets(selectedDate) {
        document.querySelectorAll('[data-guidings-book]').forEach(function (root) {
            var dateInput = root.querySelector('[data-guidings-book-date]');
            var ctaText = root.querySelector('[data-guidings-book-cta-text]');
            var defaultLabel = root.getAttribute('data-guidings-book-reserve-label') || '';

            if (dateInput) {
                dateInput.value = selectedDate || '';
            }
            if (!ctaText) {
                return;
            }
            if (!selectedDate) {
                ctaText.textContent = defaultLabel;
                return;
            }

            var locale = root.getAttribute('data-guidings-book-locale') || undefined;
            var date = new Date(selectedDate);
            var formattedDate = date.toLocaleDateString(locale, {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            var prefix = root.getAttribute('data-guidings-book-reserve-for') || defaultLabel;
            ctaText.textContent = prefix + ' ' + formattedDate;
        });
    }

    document.addEventListener('click', function (event) {
        var button = event.target.closest('[data-guidings-book-delta]');
        if (!button) {
            return;
        }

        var root = button.closest('[data-guidings-book]');
        if (!root) {
            return;
        }

        var currentIndex = parseInt(root.getAttribute('data-guidings-book-index') || '0', 10) || 0;
        var delta = parseInt(button.getAttribute('data-guidings-book-delta') || '0', 10) || 0;
        applySelection(root, currentIndex + delta);
    });

    window.addEventListener('dateSelected', function (event) {
        var selectedDate = event.detail && event.detail.date ? event.detail.date : '';
        if (selectedDate) {
            setDateOnWidgets(selectedDate);
        }
    });

    window.addEventListener('dateDeselected', function () {
        setDateOnWidgets('');
    });
})();
</script>
