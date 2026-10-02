{{--
    Camp checkout (resources/sass/page/_camp-checkout.scss). One form, two layouts:
    - mobile (< 1024px): single column, price overview at the end of the form, the
      reCAPTCHA directly under that card, and a sticky book bar (total, submit, legal line);
    - desktop (>= 1024px): form column + sticky summary aside. The reCAPTCHA sits in
      that aside, above the submit button.
    Only presentational blocks (price overview, submit + legal line) exist per layout; fields
    and the reCAPTCHA widget exist exactly once.

    Expects: $checkout (CampCheckoutViewModel).
--}}
@php
    $partials = 'pages.camp-checkout.partials';
    $icon = 'pages.modern-checkout.partials.icon';
    $product = $checkout->product();
    $pricing = $checkout->pricing();
@endphp

<div class="cc" x-data="campCheckout">
    <header class="cc-head">
        <div class="cc-head__inner">
            <a href="{{ $product['url'] }}" class="cc-head__back" aria-label="{{ __('checkout.camp.back') }}">
                @include($icon, ['name' => 'arrow-left', 'size' => 22, 'stroke' => 2])
            </a>
            <div class="cc-head__text">
                <p class="cc-head__title">{{ $product['title'] }}</p>
                @if ($product['location'] !== '')
                    <p class="cc-head__place">{{ $product['location'] }}</p>
                @endif
            </div>
        </div>
    </header>

    <div class="cc__grid">
        <form class="cc-form" novalidate @submit.prevent="submit()">
            <h1 class="visually-hidden">{{ __('checkout.camp.page_title') }}</h1>

            <div class="cc-notice">
                @include($icon, ['name' => 'shield-check', 'size' => 20, 'stroke' => 2])
                <span>{{ __('checkout.camp.notice') }}</span>
            </div>

            @include($partials.'.stay', ['checkout' => $checkout])

            @include($partials.'.extras', ['pricing' => $pricing])

            @include($partials.'.contact', ['checkout' => $checkout])

            <div class="cc-summary cc-summary--inline">
                @include($partials.'.summary')
            </div>
        </form>

        {{-- One reCAPTCHA: above the desktop submit button. On mobile the card chrome
             drops away, so the widget sits under the price card and the book bar stays separate. --}}
        <div class="cc-side">
            <aside class="cc-aside" aria-label="{{ __('checkout.camp.summary_title') }}">
                <div class="cc-product">
                    <div class="cc-product__media">
                        @if ($product['image'])
                            <img src="{{ $product['image'] }}" alt="" width="72" height="54" decoding="async">
                        @else
                            @include($icon, ['name' => 'image', 'size' => 22, 'stroke' => 1.4])
                        @endif
                    </div>
                    <div class="cc-product__body">
                        <p class="cc-product__title">{{ $product['title'] }}</p>
                        <p class="cc-product__stay" x-text="stayShort"></p>
                    </div>
                </div>

                <div class="cc-summary">
                    @include($partials.'.summary')
                </div>

                <div class="cc-captcha" x-ref="field_captcha">
                    @include($partials.'.captcha')
                </div>

                @include($partials.'.submit', ['variant' => 'aside'])
            </aside>

            <div class="cc-dock">
                <div class="cc-bar">
                    {{-- The checkbox already explains itself. Skip the generic line when that is the error. --}}
                    <p class="cc-bar__alert" x-show="hasErrors && !errors.captcha" role="alert" x-cloak>{{ __('checkout.camp.form_errors') }}</p>
                    <p class="cc-bar__alert" x-show="formError" x-text="formError" role="alert" x-cloak></p>
                    <div class="cc-bar__row">
                        <div class="cc-bar__info">
                            <p class="cc-bar__total cc-mono" x-text="totalLabel"></p>
                            <p class="cc-bar__stay" x-text="stayShort"></p>
                        </div>
                        <button type="button" class="cc-submit" @click="submit()" :disabled="loading" :aria-busy="loading.toString()">
                            <span x-show="!loading">{{ __('checkout.camp.submit') }}</span>
                            <span x-show="loading" x-cloak>{{ __('checkout.processing') }}</span>
                            @include($icon, ['name' => 'arrow-right', 'size' => 18, 'stroke' => 2])
                        </button>
                    </div>
                </div>
                @include($partials.'.legal')
            </div>
        </div>
    </div>
</div>

<script type="application/json" id="camp-checkout-config">@json($checkout->clientConfig())</script>
