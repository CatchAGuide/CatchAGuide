{{-- Shared guests stepper for offers search (site header + legacy newheader). --}}
@once
@php
    $offersPersonsLabels = [
        'one' => trans_choice('offers.persons_count', 1, ['count' => ':count']),
        'other' => trans_choice('offers.persons_count', 2, ['count' => ':count']),
    ];
@endphp
<script>
(function () {
    var labels = @json($offersPersonsLabels);

    function formatPersonsLabel(count) {
        var template = count === 1 ? labels.one : labels.other;
        return template.replace(':count', String(count));
    }

    function syncStepper(root) {
        var input = root.querySelector('[data-offers-persons-input]');
        var label = root.querySelector('[data-offers-persons-label]');
        var minus = root.querySelector('[data-offers-persons-delta="-1"]');
        var plus = root.querySelector('[data-offers-persons-delta="1"]');
        if (!input || !label) {
            return;
        }
        var value = Math.max(1, Math.min(20, parseInt(input.value, 10) || 1));
        input.value = String(value);
        label.textContent = formatPersonsLabel(value);
        if (minus) {
            minus.disabled = value <= 1;
        }
        if (plus) {
            plus.disabled = value >= 20;
        }
    }

    document.addEventListener('click', function (event) {
        var button = event.target.closest('[data-offers-persons-delta]');
        if (!button) {
            return;
        }
        var root = button.closest('[data-offers-persons-stepper]');
        if (!root) {
            return;
        }
        event.preventDefault();
        var input = root.querySelector('[data-offers-persons-input]');
        if (!input) {
            return;
        }
        var delta = parseInt(button.getAttribute('data-offers-persons-delta'), 10) || 0;
        var next = Math.max(1, Math.min(20, (parseInt(input.value, 10) || 1) + delta));
        input.value = String(next);
        syncStepper(root);
    });

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-offers-persons-stepper]').forEach(syncStepper);
    });

    if (document.readyState !== 'loading') {
        document.querySelectorAll('[data-offers-persons-stepper]').forEach(syncStepper);
    }

    document.addEventListener('submit', function (event) {
        var form = event.target;
        if (!(form instanceof HTMLFormElement)) {
            return;
        }
        if (!form.matches('#offers-filters-form, .vacation-filters-offcanvas__form, [data-offers-sort-form]')) {
            return;
        }
        var stepperInput = document.querySelector('[data-offers-persons-input]');
        if (!stepperInput) {
            return;
        }
        var guests = String(Math.max(1, Math.min(20, parseInt(stepperInput.value, 10) || 1)));
        var existing = form.querySelector('input[name="num_guests"]');
        if (existing) {
            existing.value = guests;
            return;
        }
        var input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'num_guests';
        input.value = guests;
        form.appendChild(input);
    });
})();
</script>
@endonce
