{{--
    Cookie consent popup, shared by layouts/app, app-v2 and app-v2-1 (styles: resources/sass/components/_cookie-consent.scss).
    The popup only offers Accept all / Accept necessary only / Go to settings; the switches live in
    layouts/partials/cookie-settings-panel (on /cookie-settings and in Profile › Settings), which this script binds.
    Writes default_cookie (a choice was made) plus accept_analytics (Clarity) / accept_advertising (Google Ads),
    which layouts/partials/tag-manager-head turns into Consent Mode defaults on the next page load. So a choice
    that changes consent reloads the page.
--}}
<div id="cookie-consent-banner" class="cag-cookie" role="dialog" aria-modal="true" aria-labelledby="cookie-consent-title">
    <div class="cag-cookie__card">
        <div class="cag-cookie__accent" aria-hidden="true"></div>

        <div class="cag-cookie__head">
            <span class="cag-cookie__icon" aria-hidden="true"><i class="fas fa-cookie-bite"></i></span>
            <div>
                <p class="cag-cookie__eyebrow">@lang('cookie.eyebrow')</p>
                <h2 id="cookie-consent-title" class="cag-cookie__title">@lang('cookie.header')</h2>
            </div>
        </div>
        <p class="cag-cookie__text">
            @lang('cookie.intro')
            <a href="{{ route('law.data-protection') }}">@lang('cookie.policy-link')</a>
        </p>

        <div class="cag-cookie__actions">
            <button type="button" id="cookie-accept" class="cag-cookie__btn cag-cookie__btn--primary">@lang('cookie.accept-all-btn')</button>
            <button type="button" id="cookie-decline" class="cag-cookie__btn cag-cookie__btn--outline">@lang('cookie.accept-necessary-btn')</button>
            <a id="cookie-adjust-preferences" class="cag-cookie__btn cag-cookie__btn--link" href="{{ route('law.cookie-settings') }}">@lang('cookie.settings-btn')</a>
        </div>
    </div>
</div>

<script>
    (function (w, d) {
        var ONE_YEAR = 365;
        var SAVED_FLAG = 'cag_cookie_saved';

        function getCookie(name) {
            return d.cookie.split('; ').indexOf(name + '=true') !== -1;
        }

        function setCookie(name, days) {
            var expires = new Date(Date.now() + days * 864e5).toUTCString();
            var secure = location.protocol === 'https:' ? '; Secure' : '';
            d.cookie = name + '=true; expires=' + expires + '; path=/; SameSite=Lax' + secure;
        }

        function deleteCookie(name) {
            d.cookie = name + '=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/';
        }

        function current() {
            return { analytics: getCookie('accept_analytics'), marketing: getCookie('accept_advertising') };
        }

        // Consent as this page loaded it (tag-manager-head read the same cookies).
        var loaded = current();

        // Returns true when the page reloads to apply the new consent.
        function save(choice) {
            setCookie('default_cookie', ONE_YEAR);
            choice.analytics ? setCookie('accept_analytics', ONE_YEAR) : deleteCookie('accept_analytics');
            choice.marketing ? setCookie('accept_advertising', ONE_YEAR) : deleteCookie('accept_advertising');

            if (choice.analytics !== loaded.analytics || choice.marketing !== loaded.marketing) {
                w.location.reload();
                return true;
            }
            return false;
        }

        function bindPopup(banner) {
            d.getElementById('cookie-accept').addEventListener('click', function () {
                banner.classList.remove('is-open');
                save({ analytics: true, marketing: true });
            });
            d.getElementById('cookie-decline').addEventListener('click', function () {
                banner.classList.remove('is-open');
                save({ analytics: false, marketing: false });
            });

            // The settings page/profile show the switches themselves, so the popup would only cover them.
            if (!getCookie('default_cookie') && !d.querySelector('[data-cookie-settings-panel]')) {
                banner.classList.add('is-open');
            }
        }

        function bindPanel(panel) {
            var toggles = panel.querySelectorAll('[data-cookie-consent]');
            var saved = panel.querySelector('[data-cookie-saved]');
            var state = current();

            toggles.forEach(function (toggle) {
                toggle.checked = state[toggle.getAttribute('data-cookie-consent')];
            });

            function done(choice) {
                if (save(choice)) {
                    try { sessionStorage.setItem(SAVED_FLAG, '1'); } catch (e) {}
                } else {
                    saved.hidden = false;
                }
            }

            panel.querySelector('[data-cookie-save]').addEventListener('click', function () {
                var choice = {};
                toggles.forEach(function (toggle) {
                    choice[toggle.getAttribute('data-cookie-consent')] = toggle.checked;
                });
                done(choice);
            });

            panel.querySelector('[data-cookie-accept-all]').addEventListener('click', function () {
                toggles.forEach(function (toggle) { toggle.checked = true; });
                done({ analytics: true, marketing: true });
            });

            try {
                if (sessionStorage.getItem(SAVED_FLAG)) {
                    sessionStorage.removeItem(SAVED_FLAG);
                    saved.hidden = false;
                }
            } catch (e) {}
        }

        function init() {
            var banner = d.getElementById('cookie-consent-banner');
            if (banner) {
                bindPopup(banner);
            }
            d.querySelectorAll('[data-cookie-settings-panel]').forEach(bindPanel);
        }

        if (d.readyState === 'loading') {
            d.addEventListener('DOMContentLoaded', init);
        } else {
            init();
        }
    })(window, document);
</script>
