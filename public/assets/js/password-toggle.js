/**
 * Adds a show/hide password button to every password input on the page.
 */
(function () {
    'use strict';

    var SHOW_LABEL = 'Show password';
    var HIDE_LABEL = 'Hide password';

    var EYE_SVG =
        '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' +
        '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>' +
        '<circle cx="12" cy="12" r="3"></circle>' +
        '</svg>';

    var EYE_OFF_SVG =
        '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' +
        '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"></path>' +
        '<path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"></path>' +
        '<path d="M14.12 14.12a3 3 0 1 1-4.24-4.24"></path>' +
        '<line x1="1" y1="1" x2="23" y2="23"></line>' +
        '</svg>';

    function createToggleButton(input) {
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'cag-password-toggle__btn';
        btn.setAttribute('aria-label', SHOW_LABEL);
        btn.setAttribute('tabindex', '0');
        btn.innerHTML = EYE_SVG;

        btn.addEventListener('click', function (event) {
            event.preventDefault();
            var showing = input.type === 'text';
            input.type = showing ? 'password' : 'text';
            btn.innerHTML = showing ? EYE_SVG : EYE_OFF_SVG;
            btn.setAttribute('aria-label', showing ? SHOW_LABEL : HIDE_LABEL);
            btn.setAttribute('aria-pressed', showing ? 'false' : 'true');
        });

        return btn;
    }

    function enhance(input) {
        if (!input || input.dataset.passwordToggle === 'ready') {
            return;
        }

        if (input.getAttribute('data-no-password-toggle') !== null) {
            return;
        }

        // Already wrapped
        if (input.closest('.cag-password-toggle')) {
            input.dataset.passwordToggle = 'ready';
            return;
        }

        var adminGroup = input.closest('.admin-auth__input-group');
        if (adminGroup) {
            if (!adminGroup.querySelector('.cag-password-toggle__btn')) {
                adminGroup.appendChild(createToggleButton(input));
            }
            input.dataset.passwordToggle = 'ready';
            return;
        }

        var wrapper = document.createElement('div');
        wrapper.className = 'cag-password-toggle';

        var parent = input.parentNode;
        parent.insertBefore(wrapper, input);
        wrapper.appendChild(input);
        wrapper.appendChild(createToggleButton(input));

        input.dataset.passwordToggle = 'ready';
    }

    function init(root) {
        var scope = root && root.querySelectorAll ? root : document;
        scope.querySelectorAll('input[type="password"]').forEach(enhance);
    }

    function onReady(fn) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fn);
        } else {
            fn();
        }
    }

    onReady(function () {
        init(document);

        document.addEventListener('shown.bs.modal', function (event) {
            if (event && event.target) {
                init(event.target);
            }
        });
    });

    window.CAGPasswordToggle = { init: init };
})();
