<section class="cag-home-section cag-home-trust" data-cag-reveal>
    <div class="cag-home-container">
        <div class="cag-home-trust__grid">
            <div class="cag-home-trust__card cag-home-trust__card--rating cag-reveal__item" style="--reveal-i: 0">
                <strong class="cag-home-trust__value">{{ ($trust['rating'] ?? null) ?: '—' }}</strong>
                <div class="cag-home-trust__approved">
                    <span class="cag-home-trust__approved-text">{{ __('homepage.trust_shopper_approved') }}</span>
                    <span class="cag-home-trust__approved-badge" aria-hidden="true">
                        <i class="fas fa-check"></i>
                    </span>
                </div>
                @if(!empty($trust['reviews_label']))
                    <a href="#cag-home-reviews" class="cag-home-trust__reviews-link">
                        {{ $trust['reviews_label'] }}
                    </a>
                @endif
            </div>

            <div class="cag-home-trust__card cag-reveal__item" style="--reveal-i: 1">
                <span class="cag-home-trust__icon" aria-hidden="true">
                    <i class="fas fa-check"></i>
                </span>
                <strong class="cag-home-trust__value">{{ ($trust['bookings'] ?? null) ?: '—' }}</strong>
                <span class="cag-home-trust__label">{{ __('homepage.trust_bookings_label') }}</span>
            </div>

            <div class="cag-home-trust__card cag-reveal__item" style="--reveal-i: 2">
                <span class="cag-home-trust__icon" aria-hidden="true">
                    <i class="far fa-clock"></i>
                </span>
                <strong class="cag-home-trust__value">{{ __('homepage.trust_reply_title') }}</strong>
                <span class="cag-home-trust__label">{{ __('homepage.trust_reply_text') }}</span>
            </div>

            <div class="cag-home-trust__card cag-reveal__item" style="--reveal-i: 3">
                <span class="cag-home-trust__icon" aria-hidden="true">
                    <i class="fas fa-shield-alt"></i>
                </span>
                <strong class="cag-home-trust__value">{{ __('homepage.trust_cancel_title') }}</strong>
                <span class="cag-home-trust__label">{{ __('homepage.trust_cancel_text') }}</span>
            </div>

            <div class="cag-home-trust__card cag-reveal__item" style="--reveal-i: 4">
                <span class="cag-home-trust__icon" aria-hidden="true">
                    <i class="fas fa-fish"></i>
                </span>
                <strong class="cag-home-trust__value">{{ ($trust['offers'] ?? null) ?: '450+' }}</strong>
                <span class="cag-home-trust__label">{{ __('homepage.trust_offers_label') }}</span>
            </div>

            <div class="cag-home-trust__card cag-reveal__item" style="--reveal-i: 5">
                <span class="cag-home-trust__icon" aria-hidden="true">
                    <i class="fas fa-globe-europe"></i>
                </span>
                <strong class="cag-home-trust__value">{{ ($trust['countries'] ?? null) ?: '—' }}</strong>
                <span class="cag-home-trust__label">{{ __('homepage.trust_countries_label') }}</span>
            </div>
        </div>
    </div>
</section>
