{{--
    Cookie consent banner, shared by layouts/app, app-v2 and app-v2-1.
    Writes default_cookie (a choice was made) plus accept_analytics / accept_advertising, which
    layouts/partials/tag-manager-head turns into Consent Mode defaults on the next page load. So a choice
    that changes consent reloads the page. Any link to #cookie-settings (the footer has one) reopens it,
    because withdrawing consent must be as easy as giving it.
--}}
<style>
    #cookie-consent-banner {
        position: fixed;
        inset: 0;
        background-color: rgba(20, 20, 20, 0.5);
        display: none;
        justify-content: center;
        align-items: center;
        padding: 1rem;
        z-index: 999;
    }

    #cookie-consent-banner.is-open {
        display: flex;
        animation: cookie-consent-fade-in 0.2s ease-out;
    }

    #cookie-consent-banner .cookie-consent__card {
        max-width: 32rem;
        width: 100%;
        max-height: calc(100vh - 2rem);
        overflow-y: auto;
        font-size: 16px;
    }

    #cookie-consent-banner .cookie-consent__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    #cookie-consent-banner .cookie-consent__actions .btn {
        flex: 1 1 10rem;
    }

    #cookie-consent-banner .cookie-consent__settings-link {
        background: none;
        border: 0;
        padding: 0;
        text-decoration: underline;
    }

    #cookie-consent-banner .consent-preferences {
        display: none;
    }

    #cookie-consent-banner.is-preferences .consent-main {
        display: none;
    }

    #cookie-consent-banner.is-preferences .consent-preferences {
        display: block;
    }

    @keyframes cookie-consent-fade-in {
        from { opacity: 0; }
        to { opacity: 1; }
    }
</style>

<div id="cookie-consent-banner" role="dialog" aria-modal="true" aria-labelledby="cookie-consent-title">
    <div class="cookie-consent__card bg-white p-4 shadow-lg card border-0 rounded-0">
        <h4 id="cookie-consent-title" class="fw-bolder mb-2">@lang('cookie.header')</h4>

        <div class="consent-main">
            <p class="text-dark my-2">
                @lang('cookie.intro')
                <a class="color-primary" href="{{ route('law.data-protection') }}"><u>@lang('cookie.policy-link')</u></a>
            </p>

            <div class="cookie-consent__actions mt-3">
                <button type="button" id="cookie-accept" class="btn theme-primary rounded-0">@lang('cookie.accept-all-btn')</button>
                <button type="button" id="cookie-decline" class="btn btn-outline-dark rounded-0">@lang('cookie.reject-all-btn')</button>
            </div>
            <div class="mt-3 text-center">
                <button type="button" id="cookie-adjust-preferences" class="cookie-consent__settings-link text-dark">@lang('cookie.few-btn')</button>
            </div>
        </div>

        <div class="consent-preferences text-dark my-2">
            <form id="cookie-preferences-form">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="functional-cookie" checked disabled>
                    <label class="form-check-label" for="functional-cookie"><strong>@lang('cookie.functional')</strong></label>
                    <p><small>@lang('cookie.functional-msg')</small></p>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="analytics-cookie">
                    <label class="form-check-label" for="analytics-cookie"><strong>@lang('cookie.analytical')</strong></label>
                    <p><small>@lang('cookie.analytical-msg')</small></p>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="advertising-cookie">
                    <label class="form-check-label" for="advertising-cookie"><strong>@lang('cookie.advertising')</strong></label>
                    <p><small>@lang('cookie.advertising-msg')</small></p>
                </div>
            </form>
            <div class="cookie-consent__actions">
                <button id="preference-close" type="button" class="btn btn-outline-dark rounded-0">@lang('cookie.back-btn')</button>
                <button id="save-cookie-preferences" type="button" class="btn theme-primary rounded-0">@lang('cookie.save-preference-btn')</button>
            </div>
        </div>
    </div>
</div>

<script>
    (function (d) {
        var banner = d.getElementById('cookie-consent-banner');
        if (!banner) {
            return;
        }

        var ONE_YEAR = 365;

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

        // Consent as this page loaded it (tag-manager-head read the same cookies).
        var loaded = { analytics: getCookie('accept_analytics'), ads: getCookie('accept_advertising') };

        function open(preferences) {
            d.getElementById('analytics-cookie').checked = getCookie('accept_analytics');
            d.getElementById('advertising-cookie').checked = getCookie('accept_advertising');
            banner.classList.toggle('is-preferences', !!preferences);
            banner.classList.add('is-open');
        }

        function save(analytics, ads) {
            setCookie('default_cookie', ONE_YEAR);
            analytics ? setCookie('accept_analytics', ONE_YEAR) : deleteCookie('accept_analytics');
            ads ? setCookie('accept_advertising', ONE_YEAR) : deleteCookie('accept_advertising');
            banner.classList.remove('is-open');

            if (analytics !== loaded.analytics || ads !== loaded.ads) {
                window.location.reload();
            }
        }

        d.getElementById('cookie-accept').addEventListener('click', function () {
            save(true, true);
        });

        d.getElementById('cookie-decline').addEventListener('click', function () {
            save(false, false);
        });

        d.getElementById('cookie-adjust-preferences').addEventListener('click', function () {
            open(true);
        });

        d.getElementById('preference-close').addEventListener('click', function () {
            banner.classList.remove('is-preferences');
        });

        d.getElementById('save-cookie-preferences').addEventListener('click', function () {
            save(d.getElementById('analytics-cookie').checked, d.getElementById('advertising-cookie').checked);
        });

        d.addEventListener('click', function (event) {
            var link = event.target.closest && event.target.closest('a[href="#cookie-settings"]');
            if (link) {
                event.preventDefault();
                open(false);
            }
        });

        if (!getCookie('default_cookie')) {
            open(false);
        }
    })(document);
</script>
