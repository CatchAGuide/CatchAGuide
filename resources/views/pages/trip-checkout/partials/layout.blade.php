{{--
    Trip checkout. Shares the camp checkout's `.cc` styles and layout rules
    (resources/sass/page/_camp-checkout.scss) plus the trip-only parts in _trip-checkout.scss:
    - mobile (< 1024px): single column, price overview at the end of the form, the reCAPTCHA
      under it, and a sticky book bar (total, submit, legal line);
    - desktop (>= 1024px): form column + sticky summary aside with the reCAPTCHA and submit.
    Fields and the reCAPTCHA widget exist exactly once.

    Expects: $checkout (TripCheckoutViewModel).
--}}
@php
    $partials = 'pages.trip-checkout.partials';
    $campPartials = 'pages.camp-checkout.partials';
    $icon = 'pages.modern-checkout.partials.icon';
    $product = $checkout->product();
@endphp

<div class="cc cc--trip" x-data="tripCheckout">
    <header class="cc-head">
        <div class="cc-head__inner">
            <a href="{{ $product['url'] }}" class="cc-head__back" aria-label="{{ __('checkout.trip.back') }}">
                @include($icon, ['name' => 'arrow-left', 'size' => 22, 'stroke' => 2])
            </a>
            <div class="cc-head__text">
                <p class="cc-head__title">{{ $product['title'] }}</p>
                @if ($product['subtitle'] !== '')
                    <p class="cc-head__place">{{ $product['subtitle'] }}</p>
                @endif
            </div>
        </div>
    </header>

    <div class="cc__grid">
        <form class="cc-form" novalidate @submit.prevent="submit()">
            <h1 class="visually-hidden">{{ __('checkout.trip.page_title') }}</h1>

            <div class="cc-notice">
                @include($icon, ['name' => 'shield-check', 'size' => 20, 'stroke' => 2])
                <span>{{ __('checkout.trip.notice') }}</span>
            </div>

            @include($partials.'.date', ['checkout' => $checkout])

            @include($partials.'.party', ['checkout' => $checkout])

            @include($campPartials.'.contact', ['checkout' => $checkout, 'copy' => 'checkout.trip'])

            <div class="cc-summary cc-summary--inline">
                @include($partials.'.summary')
            </div>
        </form>

        <div class="cc-side">
            <aside class="cc-aside" aria-label="{{ __('checkout.trip.summary_title') }}">
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
                        @if ($product['subtitle'] !== '')
                            <p class="cc-product__stay">{{ $product['subtitle'] }}</p>
                        @endif
                    </div>
                </div>

                <div class="cc-trip-row">
                    @include($icon, ['name' => 'calendar', 'size' => 18, 'stroke' => 2])
                    <span class="cc-trip-row__date" x-text="sideDate"></span>
                    <span class="cc-trip-row__persons" x-text="personsLabel"></span>
                </div>

                <div class="cc-summary">
                    @include($partials.'.summary')
                </div>

                <div class="cc-captcha" x-ref="field_captcha">
                    @include($campPartials.'.captcha')
                </div>

                <div class="cc-cta" x-ref="field_submit">
                    <p class="cc-cta__alert" x-show="hasErrors" role="alert" x-cloak>{{ __('checkout.trip.form_errors') }}</p>
                    <p class="cc-cta__alert" x-show="formError" x-text="formError" role="alert" x-cloak></p>
                    @include($partials.'.submit-button')
                    @include($campPartials.'.legal')
                </div>
            </aside>

            <div class="cc-dock">
                <div class="cc-bar">
                    {{-- The checkbox already explains itself. Skip the generic line when that is the error. --}}
                    <p class="cc-bar__alert" x-show="hasErrors && !errors.captcha" role="alert" x-cloak>{{ __('checkout.trip.form_errors') }}</p>
                    <p class="cc-bar__alert" x-show="formError" x-text="formError" role="alert" x-cloak></p>
                    <div class="cc-bar__row">
                        <div class="cc-bar__info">
                            <p class="cc-bar__total cc-mono" x-text="totalLabel"></p>
                            <p class="cc-bar__stay" x-text="barSub"></p>
                        </div>
                        @include($partials.'.submit-button')
                    </div>
                </div>
                @include($campPartials.'.legal')
            </div>
        </div>
    </div>
</div>

<script type="application/json" id="trip-checkout-config">@json($checkout->clientConfig())</script>
