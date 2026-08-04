(function (global) {
    'use strict';

    class RecaptchaWidget {
        /**
         * @param {Element|string} root Form or container that holds the widget
         */
        constructor(root) {
            this.root = typeof root === 'string' ? document.querySelector(root) : root;
        }

        get widgetEl() {
            return this.root ? this.root.querySelector('.g-recaptcha') : null;
        }

        get widgetIndex() {
            const widget = this.widgetEl;
            if (!widget) {
                return null;
            }

            return Array.from(document.querySelectorAll('.g-recaptcha')).indexOf(widget);
        }

        token() {
            if (!this.root) {
                return '';
            }

            const field = this.root.querySelector('[name="g-recaptcha-response"]');
            if (field && field.value) {
                return field.value;
            }

            if (typeof grecaptcha === 'undefined' || typeof grecaptcha.getResponse !== 'function') {
                return '';
            }

            const index = this.widgetIndex;

            try {
                return (index !== null && index >= 0
                    ? grecaptcha.getResponse(index)
                    : grecaptcha.getResponse()) || '';
            } catch (e) {
                return grecaptcha.getResponse() || '';
            }
        }

        reset() {
            if (typeof grecaptcha === 'undefined' || typeof grecaptcha.reset !== 'function') {
                return;
            }

            const index = this.widgetIndex;

            try {
                if (index !== null && index >= 0) {
                    grecaptcha.reset(index);
                } else {
                    grecaptcha.reset();
                }
            } catch (e) {
                grecaptcha.reset();
            }
        }

        /**
         * @param {Function} [onMissing]
         * @returns {boolean}
         */
        requireToken(onMissing) {
            if (this.token()) {
                return true;
            }

            if (typeof onMissing === 'function') {
                onMissing();
            }

            return false;
        }

        /**
         * Reset when a Bootstrap modal becomes visible (widget is often flaky while hidden).
         *
         * @param {Element|string|null} modalEl
         * @param {{ onShow?: Function }} [options]
         */
        bindModal(modalEl, options) {
            const modal = typeof modalEl === 'string' ? document.querySelector(modalEl) : modalEl;
            if (!modal) {
                return;
            }

            modal.addEventListener('shown.bs.modal', () => {
                this.reset();
                if (options && typeof options.onShow === 'function') {
                    options.onShow();
                }
            });
        }
    }

    global.RecaptchaWidget = RecaptchaWidget;
})(typeof window !== 'undefined' ? window : this);
