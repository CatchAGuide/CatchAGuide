@php
    $copy = 'guidings.voucher_landing.';
    $heroSlides = [
        asset('assets/images/vouchers/landing-hero-a.webp'),
        asset('assets/images/vouchers/landing-hero-b.webp'),
    ];
@endphp
<div class="cag-site-nav-shell vl-hero-shell" data-vl-nav-shell>
    @include('layouts.partials.site-nav', ['overlay' => true, 'idPrefix' => 'voucher'])

    <section class="vl-hero" id="top" data-vl-hero>
        <div class="vl-hero__media" aria-hidden="true">
            @foreach($heroSlides as $index => $src)
                <img
                    class="vl-hero__image{{ $index === 0 ? ' is-active' : '' }}{{ $index === 1 ? ' vl-hero__image--b' : '' }}"
                    src="{{ $src }}"
                    alt=""
                    width="1920"
                    height="1440"
                    @if($index === 0) fetchpriority="high" @else loading="lazy" @endif
                    data-vl-hero-slide
                >
            @endforeach
        </div>
        <div class="vl-hero__shade" aria-hidden="true"></div>
        @if($xmas)
            <div class="vl-hero__glow" aria-hidden="true"></div>
        @endif
        <div class="vl-hero__inner">
            <div class="vl-hero__copy">
                <p class="vl-hero__eyebrow">{{ __($copy.($xmas ? 'eyebrow_xmas' : 'eyebrow')) }}</p>
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
