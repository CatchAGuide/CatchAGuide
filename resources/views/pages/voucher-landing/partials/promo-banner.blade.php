{{--
    Gift voucher promo banner (design 5a: dark band + ticket). Homepage and /guidings landing.
    Links to the voucher landing page; the ticket is decorative.
--}}
@php($copy = 'guidings.voucher_landing.')
<section class="cag-home-section vp-banner-section" data-analytics-section="voucher-promo-banner">
    <div class="cag-home-container">
        <div class="vp-banner">
            <div class="vp-banner__copy">
                <h2 class="vp-banner__title">{{ __($copy.'promo_title') }}</h2>
                <p class="vp-banner__text">{{ __($copy.'promo_text') }}</p>
                <a href="{{ route('guidings.voucher') }}" class="vp-banner__cta">
                    {{ __($copy.'promo_cta') }}
                    @include('pages.home.partials.cag-icon', ['name' => 'arrow', 'size' => 15])
                </a>
            </div>
            <div class="vp-banner__visual" aria-hidden="true">
                <div class="vp-ticket">
                    <div class="vp-ticket__card">
                        <div class="vp-ticket__body">
                            <span class="vp-ticket__kicker">{{ __($copy.'card_kicker') }}</span>
                            <span class="vp-ticket__brand">CATCH A GUIDE</span>
                            <span class="vp-ticket__amount">{{ __($copy.'promo_amount') }}</span>
                            <span class="vp-ticket__where">{{ __($copy.'card_where_open') }}</span>
                            <span class="vp-ticket__serial">{{ __($copy.'card_number', ['code' => 'CAG-V-6YSHXS']) }}</span>
                            <span class="vp-ticket__diamond vp-ticket__diamond--a"></span>
                            <span class="vp-ticket__diamond vp-ticket__diamond--b"></span>
                        </div>
                        <div class="vp-ticket__stub">
                            <span class="vp-ticket__side">{{ __($copy.'card_side') }}</span>
                            <span class="vp-ticket__ribbon"></span>
                        </div>
                        <span class="vp-ticket__shine"></span>
                    </div>
                    <span class="vp-ticket__bow vp-ticket__bow--l"></span>
                    <span class="vp-ticket__bow vp-ticket__bow--r"></span>
                    <span class="vp-ticket__knot"></span>
                </div>
            </div>
        </div>
    </div>
</section>
