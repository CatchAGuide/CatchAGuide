@props([
    'priceDisplay' => null,
    'priceSuffix' => null,
    'priceNote' => null,
    'ctaLabel',
    'variant' => null,
])

<div @class(['listing-mobile-book', 'listing-mobile-book--stacked' => $variant === 'stacked']) role="region" aria-label="{{ $ctaLabel }}">
    <div class="listing-mobile-book__inner">
        <div class="listing-mobile-book__bar">
            <div class="listing-mobile-book__top">
                <div class="listing-mobile-book__price">
                    <span class="listing-mobile-book__amount">{{ $priceDisplay ?: '—' }}</span>
                    @if($priceSuffix)
                        <span class="listing-mobile-book__unit">{{ $priceSuffix }}</span>
                    @endif
                </div>
                @if($priceNote)
                    <span class="listing-mobile-book__note">{{ $priceNote }}</span>
                @endif
            </div>
            <button type="button" {{ $attributes->class(['listing-mobile-book__cta']) }}>
                <span class="listing-mobile-book__cta-text">{{ $ctaLabel }}</span>
                <span class="listing-mobile-book__cta-arrow" aria-hidden="true">→</span>
            </button>
        </div>
    </div>
</div>
