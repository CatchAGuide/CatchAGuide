/**
 * Loading / upload helpers for the guiding multi-step form.
 * Prevents the forever-spinner when fetch or image prep hangs.
 */
(function (global) {
    'use strict';

    var DEFAULT_FETCH_TIMEOUT_MS = 90000;
    var DEFAULT_WATCHDOG_MS = 120000;

    function setLoadingMessage(message, subMessage) {
        var loadingScreen = document.getElementById('loadingScreen');
        if (!loadingScreen) {
            return;
        }
        var texts = loadingScreen.querySelectorAll('div[style*="font-size"]');
        if (texts[0] && message) {
            texts[0].textContent = message;
        }
        if (texts[1] && typeof subMessage === 'string') {
            texts[1].textContent = subMessage;
        }
    }

    function clearLoadingWatchdog() {
        var loadingScreen = document.getElementById('loadingScreen');
        if (loadingScreen && loadingScreen._watchdogTimer) {
            clearTimeout(loadingScreen._watchdogTimer);
            loadingScreen._watchdogTimer = null;
        }
    }

    /**
     * Auto-hide the spinner if something hangs (network, PHP, Spaces, canvas).
     * @param {number} [timeoutMs]
     * @param {function(Error): void} [onTimeout]
     */
    function armLoadingWatchdog(timeoutMs, onTimeout) {
        var loadingScreen = document.getElementById('loadingScreen');
        if (!loadingScreen) {
            return;
        }
        clearLoadingWatchdog();
        var ms = timeoutMs || DEFAULT_WATCHDOG_MS;
        loadingScreen._watchdogTimer = setTimeout(function () {
            loadingScreen._watchdogTimer = null;
            var err = new Error(
                (global.GuidingFormI18n && global.GuidingFormI18n.upload_watchdog)
                || 'This is taking too long. Please try fewer or smaller images, then save again.'
            );
            if (typeof onTimeout === 'function') {
                onTimeout(err);
            } else if (typeof global.hideLoadingScreen === 'function') {
                global.hideLoadingScreen();
                var errorContainer = document.getElementById('error-container');
                if (errorContainer) {
                    errorContainer.style.display = 'block';
                    errorContainer.innerHTML = '<div class="alert alert-danger"></div>';
                    errorContainer.querySelector('.alert').textContent = err.message;
                } else {
                    alert(err.message);
                }
            }
        }, ms);
    }

    /**
     * fetch() with AbortController timeout.
     * @param {string} url
     * @param {RequestInit} [options]
     * @param {number} [timeoutMs]
     * @returns {Promise<Response>}
     */
    function fetchWithTimeout(url, options, timeoutMs) {
        var ms = timeoutMs || DEFAULT_FETCH_TIMEOUT_MS;
        var controller = new AbortController();
        var opts = Object.assign({}, options || {}, { signal: controller.signal });
        var timer = setTimeout(function () {
            controller.abort();
        }, ms);

        return fetch(url, opts).finally(function () {
            clearTimeout(timer);
        }).catch(function (error) {
            if (error && error.name === 'AbortError') {
                var seconds = Math.round(ms / 1000);
                var template = (global.GuidingFormI18n && global.GuidingFormI18n.upload_timeout)
                    || 'Upload timed out after :seconds seconds. Try fewer or smaller images.';
                throw new Error(template.replace(':seconds', String(seconds)));
            }
            throw error;
        });
    }

    global.GuidingFormLoading = {
        DEFAULT_FETCH_TIMEOUT_MS: DEFAULT_FETCH_TIMEOUT_MS,
        DEFAULT_WATCHDOG_MS: DEFAULT_WATCHDOG_MS,
        setLoadingMessage: setLoadingMessage,
        clearLoadingWatchdog: clearLoadingWatchdog,
        armLoadingWatchdog: armLoadingWatchdog,
        fetchWithTimeout: fetchWithTimeout,
    };
})(typeof window !== 'undefined' ? window : this);
