@once
    @php
        $lang = app()->getLocale();
        $domain = config('recaptcha.api_domain', 'www.google.com');
        $siteKey = config('recaptcha.api_site_key', '');
        $attrs = (array) config('recaptcha.tag_attributes', []);
    @endphp

    @if(!empty($siteKey))
        <script src="https://{{ $domain }}/recaptcha/api.js?hl={{ urlencode($lang) }}" async defer></script>
    @endif
@endonce

@php
    $siteKey = config('recaptcha.api_site_key', '');
    $attrs = (array) config('recaptcha.tag_attributes', []);
@endphp

@if(!empty($siteKey))
    <div
        class="g-recaptcha"
        data-sitekey="{{ $siteKey }}"
        @if(!empty($attrs['theme'])) data-theme="{{ $attrs['theme'] }}" @endif
        @if(!empty($attrs['size'])) data-size="{{ $attrs['size'] }}" @endif
        @if(isset($attrs['tabindex'])) data-tabindex="{{ $attrs['tabindex'] }}" @endif
        @if(!empty($attrs['callback'])) data-callback="{{ $attrs['callback'] }}" @endif
        @if(!empty($attrs['expired-callback'])) data-expired-callback="{{ $attrs['expired-callback'] }}" @endif
        @if(!empty($attrs['error-callback'])) data-error-callback="{{ $attrs['error-callback'] }}" @endif
    ></div>
@endif

