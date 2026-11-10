{{--
    Gift voucher promo row (design 2a: white row with € badge). Shown in tour listings after the
    pagination, before the FAQ. Links to the voucher landing page.
--}}
@php($copy = 'guidings.voucher_landing.')
<aside class="vp-row" data-analytics-section="voucher-promo-row">
    <span class="vp-row__badge" aria-hidden="true">€</span>
    <div class="vp-row__copy">
        <p class="vp-row__title">{{ __($copy.'promo_row_title') }}</p>
        <p class="vp-row__text">{{ __($copy.'promo_row_text') }}</p>
    </div>
    <a href="{{ route('guidings.voucher') }}" class="vp-row__cta">
        {{ __($copy.'promo_row_cta') }}
        @include('pages.home.partials.cag-icon', ['name' => 'arrow', 'size' => 14])
    </a>
</aside>
