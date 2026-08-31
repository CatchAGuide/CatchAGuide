<section class="cag-home-section cag-home-trust" data-cag-reveal>
    <div class="cag-home-container">
        <div class="cag-home-trust__grid" role="list">
            <div class="cag-home-trust__cell cag-reveal__item" style="--reveal-i: 0" role="listitem">
                <div class="cag-home-trust__cell-title">
                    @include('pages.home.partials.cag-icon', ['name' => 'star', 'size' => 20])
                    <span class="cag-home-trust__cell-heading">
                        <span class="cag-home-trust__cell-line">{{ ($trust['rating'] ?? null) ?: '—' }}</span>
                        <span class="cag-home-trust__cell-line">{{ __('homepage.trust_rating_short') }}</span>
                    </span>
                </div>
                <span class="cag-home-trust__cell-sub">{{ __('homepage.trust_rating_sub') }}</span>
            </div>
            <div class="cag-home-trust__cell cag-reveal__item" style="--reveal-i: 1" role="listitem">
                <div class="cag-home-trust__cell-title">
                    @include('pages.home.partials.cag-icon', ['name' => 'pin', 'size' => 20])
                    <span class="cag-home-trust__cell-heading">
                        <span class="cag-home-trust__cell-line">{{ ($trust['offers'] ?? null) ?: '450+' }}</span>
                        <span class="cag-home-trust__cell-line">{{ __('homepage.trust_offers_heading') }}</span>
                    </span>
                </div>
                <span class="cag-home-trust__cell-sub">{{ __('homepage.trust_offers_countries', ['count' => ($trust['countries'] ?? null) ?: '24']) }}</span>
            </div>
            <div class="cag-home-trust__cell cag-reveal__item" style="--reveal-i: 2" role="listitem">
                <div class="cag-home-trust__cell-title">
                    @include('pages.home.partials.cag-icon', ['name' => 'headphones', 'size' => 20])
                    <span class="cag-home-trust__cell-heading">
                        <span class="cag-home-trust__cell-line">{{ __('homepage.trust_advice_line1') }}</span>
                        <span class="cag-home-trust__cell-line">{{ __('homepage.trust_advice_line2') }}</span>
                    </span>
                </div>
                <span class="cag-home-trust__cell-sub">{{ __('homepage.trust_advice_text') }}</span>
            </div>
            <div class="cag-home-trust__cell cag-reveal__item" style="--reveal-i: 3" role="listitem">
                <div class="cag-home-trust__cell-title">
                    @include('pages.home.partials.cag-icon', ['name' => 'shield', 'size' => 20])
                    <span class="cag-home-trust__cell-heading">
                        <span class="cag-home-trust__cell-line">{{ __('homepage.trust_partners_line1') }}</span>
                        <span class="cag-home-trust__cell-line">{{ __('homepage.trust_partners_line2') }}</span>
                    </span>
                </div>
                <span class="cag-home-trust__cell-sub">{{ __('homepage.trust_partners_text') }}</span>
            </div>
        </div>
    </div>
</section>
