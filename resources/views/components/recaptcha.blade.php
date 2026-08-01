{{-- The "id" prop lets JavaScript address one specific widget on pages that hold several. --}}
@props(['id' => null])

@php
    $siteKey = (string) config('recaptcha.api_site_key', '');
    $attrs = (array) config('recaptcha.tag_attributes', []);
@endphp

@if($siteKey !== '')
    @once
        @php
            $lang = app()->getLocale();
            $domain = config('recaptcha.api_domain', 'www.google.com');
        @endphp

        <script>
            window.RecaptchaWidget = (function () {
                var SELECTOR = '[data-recaptcha]';
                var ready = false;

                function callbackFor(name) {
                    return name && typeof window[name] === 'function' ? window[name] : undefined;
                }

                function render(el) {
                    if (el.dataset.recaptchaWidgetId !== undefined) {
                        return;
                    }

                    try {
                        el.dataset.recaptchaWidgetId = window.grecaptcha.render(el, {
                            sitekey: el.dataset.sitekey,
                            theme: el.dataset.theme || undefined,
                            size: el.dataset.size || undefined,
                            tabindex: el.dataset.tabindex ? parseInt(el.dataset.tabindex, 10) : undefined,
                            callback: callbackFor(el.dataset.callback),
                            'expired-callback': callbackFor(el.dataset.expiredCallback),
                            'error-callback': callbackFor(el.dataset.errorCallback)
                        });
                    } catch (e) {
                        // One broken widget must not stop the remaining ones from rendering.
                    }
                }

                function renderAll() {
                    if (!ready || !window.grecaptcha || !window.grecaptcha.render) {
                        return;
                    }

                    Array.prototype.forEach.call(document.querySelectorAll(SELECTOR), render);
                }

                // Each widget must be addressed by its own id: a page can hold several of them
                // (e.g. the register modal in the header plus a form in the page body) and the
                // argument-less grecaptcha calls always act on the first one that was rendered.
                function RecaptchaWidget(scope) {
                    if (!(this instanceof RecaptchaWidget)) {
                        return new RecaptchaWidget(scope);
                    }

                    this.scope = scope;
                }

                RecaptchaWidget.prototype.element = function () {
                    var scope = this.scope;

                    if (typeof scope === 'string') {
                        scope = document.querySelector(scope);
                    }

                    if (!scope) {
                        return document.querySelector(SELECTOR);
                    }

                    return scope.matches(SELECTOR) ? scope : scope.querySelector(SELECTOR);
                };

                RecaptchaWidget.prototype.widgetId = function () {
                    var el = this.element();
                    var id = el ? el.dataset.recaptchaWidgetId : undefined;

                    return id === undefined ? null : Number(id);
                };

                RecaptchaWidget.prototype.getResponse = function () {
                    var id = this.widgetId();

                    if (id !== null && window.grecaptcha && window.grecaptcha.getResponse) {
                        try {
                            return window.grecaptcha.getResponse(id) || '';
                        } catch (e) {
                            // Fall through to reading the token straight from the DOM.
                        }
                    }

                    var el = this.element();
                    var field = el ? el.querySelector('textarea[name="g-recaptcha-response"]') : null;

                    return field ? field.value : '';
                };

                RecaptchaWidget.prototype.reset = function () {
                    var id = this.widgetId();

                    if (id === null || !window.grecaptcha || !window.grecaptcha.reset) {
                        return this;
                    }

                    try {
                        window.grecaptcha.reset(id);
                    } catch (e) {
                        // Widget was removed from the DOM in the meantime.
                    }

                    return this;
                };

                RecaptchaWidget.prototype.requireToken = function (onMissing) {
                    if (!this.element() || this.getResponse()) {
                        return true;
                    }

                    if (typeof onMissing === 'function') {
                        onMissing();
                    }

                    return false;
                };

                RecaptchaWidget.prototype.bindModal = function (modal, options) {
                    var self = this;
                    var settings = options || {};

                    if (modal) {
                        modal.addEventListener('shown.bs.modal', function () {
                            self.reset();

                            if (typeof settings.onShow === 'function') {
                                settings.onShow();
                            }
                        });
                    }

                    return this;
                };

                RecaptchaWidget.renderAll = renderAll;

                window.recaptchaWidgetOnload = function () {
                    ready = true;
                    renderAll();

                    if (document.readyState === 'loading') {
                        document.addEventListener('DOMContentLoaded', renderAll);
                    }
                };

                return RecaptchaWidget;
            })();
        </script>
        <script src="https://{{ $domain }}/recaptcha/api.js?hl={{ urlencode($lang) }}&amp;onload=recaptchaWidgetOnload&amp;render=explicit" async defer></script>
    @endonce

    <div
        class="g-recaptcha"
        @if(!empty($id)) id="{{ $id }}" @endif
        data-recaptcha
        data-sitekey="{{ $siteKey }}"
        @if(!empty($attrs['theme'])) data-theme="{{ $attrs['theme'] }}" @endif
        @if(!empty($attrs['size'])) data-size="{{ $attrs['size'] }}" @endif
        @if(isset($attrs['tabindex'])) data-tabindex="{{ $attrs['tabindex'] }}" @endif
        @if(!empty($attrs['callback'])) data-callback="{{ $attrs['callback'] }}" @endif
        @if(!empty($attrs['expired-callback'])) data-expired-callback="{{ $attrs['expired-callback'] }}" @endif
        @if(!empty($attrs['error-callback'])) data-error-callback="{{ $attrs['error-callback'] }}" @endif
    ></div>
@endif
