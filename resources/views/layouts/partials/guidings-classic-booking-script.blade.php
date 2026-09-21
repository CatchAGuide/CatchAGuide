@once
<script data-guidings-classic-booking-script>
(function () {
    if (window.__guidingsClassicBookInit) {
        return;
    }
    window.__guidingsClassicBookInit = true;

    function formatPrice(price) {
        return parseFloat(price).toFixed(2).replace(/\.00$/, '');
    }

    document.addEventListener('DOMContentLoaded', function () {
        var form = document.querySelector('[data-guidings-classic-book]');
        var personSelect = document.getElementById('personSelect');
        if (!form || !personSelect) {
            return;
        }

        var priceCalculation = document.getElementById('priceCalculation');
        var basePrice = document.querySelector('.base-price');
        var personCount = document.querySelector('.person-count');
        var peopleText = document.querySelector('.people-text');
        var totalPrice = document.querySelector('.total-price');
        var fromText = document.querySelector('.from-text');
        var perGuidingText = document.querySelector('.per-guiding-text');
        var priceItem = document.querySelector('.price-item');
        var isPerPerson = {{ $guiding->price_type === 'per_person' ? 'true' : 'false' }};
        var personLabel = @json(__('booking.person'));
        var peopleLabel = @json(__('booking.people'));
        var perPersonCopy = @json(__('booking.per_person_for_a_tour_of'));
        var notChargedCopy = @json(__('booking.you_wont_be_charged_yet'));
        var pleaseSelectCopy = @json(__('booking.please_select_number_of_people'));
        var reserveLabel = @json(__('booking.reserve_now'));
        var reserveForLabel = @json(__('booking.reserve_for_date'));
        var locale = @json(str_replace('_', '-', app()->getLocale()));

        function peopleWord(count) {
            return parseInt(count, 10) === 1 ? personLabel : peopleLabel;
        }

        function applySelection() {
            var selectedOption = personSelect.options[personSelect.selectedIndex];
            var price = selectedOption ? selectedOption.getAttribute('data-price') : null;
            var persons = selectedOption ? selectedOption.value : '';

            if (!price || !persons || personSelect.selectedIndex === 0) {
                return;
            }

            if (priceCalculation) {
                priceCalculation.style.display = 'block';
            }
            if (fromText) {
                fromText.style.display = 'none';
            }
            if (perGuidingText) {
                perGuidingText.style.display = '';
            }
            if (totalPrice) {
                totalPrice.textContent = formatPrice(price) + '€';
            }
            if (personCount) {
                personCount.textContent = persons;
            }
            if (peopleText) {
                peopleText.textContent = peopleWord(persons);
            }

            if (isPerPerson && basePrice) {
                basePrice.textContent = formatPrice(Math.round(price / persons)) + '€';
            } else if (priceItem) {
                priceItem.innerHTML = formatPrice(price / persons) + '€ ' + perPersonCopy
                    + ' <span class="person-count">' + persons + '</span> ' + peopleWord(persons) + notChargedCopy;
            }
        }

        personSelect.addEventListener('change', applySelection);
        if (personSelect.value) {
            applySelection();
        }

        form.addEventListener('submit', function (event) {
            if (personSelect.value && personSelect.selectedIndex !== 0) {
                return;
            }

            event.preventDefault();
            var selectContainer = personSelect.closest('.booking-select');
            var existingBubble = document.querySelector('.validation-bubble');
            if (existingBubble) {
                existingBubble.remove();
            }

            var validationBubble = document.createElement('div');
            validationBubble.className = 'validation-bubble';
            validationBubble.textContent = pleaseSelectCopy;
            validationBubble.style.cssText = 'position:absolute;top:-45px;right:0;z-index:1000;background:#dc3545;color:#fff;padding:8px 12px;border-radius:6px;font-size:12px;white-space:nowrap;';
            if (selectContainer) {
                selectContainer.style.position = 'relative';
                selectContainer.appendChild(validationBubble);
            }
            personSelect.classList.add('is-invalid');
            setTimeout(function () {
                if (validationBubble.parentNode) {
                    validationBubble.remove();
                }
            }, 3000);
            personSelect.addEventListener('change', function () {
                personSelect.classList.remove('is-invalid');
                var bubble = document.querySelector('.validation-bubble');
                if (bubble) {
                    bubble.remove();
                }
            }, { once: true });
        });

        window.addEventListener('dateSelected', function (event) {
            var selectedDate = event.detail && event.detail.date;
            var reserveButton = document.getElementById('reserveButton');
            var selectedDateInput = document.getElementById('selectedDateInput');
            if (!selectedDate || !reserveButton || !selectedDateInput) {
                return;
            }
            var date = new Date(selectedDate);
            reserveButton.textContent = reserveForLabel + ' ' + date.toLocaleDateString(locale, {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            selectedDateInput.value = selectedDate;
        });

        window.addEventListener('dateDeselected', function () {
            var reserveButton = document.getElementById('reserveButton');
            var selectedDateInput = document.getElementById('selectedDateInput');
            if (reserveButton) {
                reserveButton.textContent = reserveLabel;
            }
            if (selectedDateInput) {
                selectedDateInput.value = '';
            }
        });
    });
})();
</script>
@endonce
