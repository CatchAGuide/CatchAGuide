@php
    $copy = 'guidings.voucher_landing.';
@endphp
<div class="cag-site-nav-shell vl-hero-shell" data-vl-nav-shell>
    @include('layouts.partials.site-nav', ['overlay' => true, 'idPrefix' => 'voucher'])

    <section class="vl-hero" id="top" data-vl-hero>
        <div class="vl-hero__media" aria-hidden="true">
            <img
                class="vl-hero__image is-active"
                src="{{ asset('assets/images/vouchers/landing-hero.webp') }}"
                alt=""
                width="1920"
                height="1440"
                fetchpriority="high"
            >
        </div>
        <div class="vl-hero__shade" aria-hidden="true"></div>
        @if($xmas)
            <div class="vl-hero__glow" aria-hidden="true"></div>
        @endif
        <div class="vl-hero__inner">
            <div class="vl-hero__copy">
                <p class="vl-hero__eyebrow">{{ __($copy.'eyebrow') }}</p>
                <h1 class="vl-hero__title">{{ __($copy.'hero_title') }}</h1>
                <p class="vl-hero__text">{{ __($copy.'hero_text') }}</p>
                <div class="vl-hero__actions">
                    <a href="#gutschein" class="vl-btn vl-btn--glow">
                        {{ __($copy.'cta') }}
                        @include('pages.voucher-landing.partials.icon', ['name' => 'arrow'])
                    </a>
                    <a href="#ablauf" class="vl-hero__link">{{ __($copy.'how_link') }}</a>
                </div>
            </div>
            <div class="vl-hero__visual">
                <div class="vl-float">
                    @include('pages.voucher-landing.partials.card', [
                        'variant' => 'hero',
                        'values' => $landing->previewDefaults(),
                        'serial' => $landing->serial(),
                    ])
                </div>
            </div>
        </div>
    </section>
</div>
