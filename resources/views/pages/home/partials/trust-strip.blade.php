<section class="cag-home-section cag-home-trust" data-cag-reveal>
    <div class="cag-home-container">
        <div class="cag-home-trust__strip">
            <div class="cag-home-trust__lead cag-reveal__item" style="--reveal-i: 0">
                <div class="cag-home-trust__score">
                    <strong class="cag-home-trust__rating">{{ ($trust['rating'] ?? null) ?: '—' }}</strong>
                    <span class="cag-home-trust__approved-badge" aria-hidden="true">
                        <i class="fas fa-check"></i>
                    </span>
                </div>
                <div class="cag-home-trust__lead-copy">
                    <span class="cag-home-trust__approved-text">{{ __('homepage.trust_angler_approved') }}</span>
                    @if(!empty($trust['reviews_label']))
                        <a href="#cag-home-reviews" class="cag-home-trust__reviews-link">
                            {{ $trust['reviews_label'] }}
                        </a>
                    @endif
                </div>
            </div>

            <div class="cag-home-trust__primary">
                <div class="cag-home-trust__stats" role="list">
                    <div class="cag-home-trust__stat cag-reveal__item" style="--reveal-i: 1" role="listitem">
                        <strong class="cag-home-trust__value">{{ ($trust['bookings'] ?? null) ?: '—' }}</strong>
                        <span class="cag-home-trust__label">{{ __('homepage.trust_bookings_label') }}</span>
                    </div>
                    <div class="cag-home-trust__stat cag-reveal__item" style="--reveal-i: 2" role="listitem">
                        <strong class="cag-home-trust__value">{{ ($trust['offers'] ?? null) ?: '450+' }}</strong>
                        <span class="cag-home-trust__label">{{ __('homepage.trust_offers_label') }}</span>
                    </div>
                    <div class="cag-home-trust__stat cag-reveal__item" style="--reveal-i: 3" role="listitem">
                        <strong class="cag-home-trust__value">{{ ($trust['countries'] ?? null) ?: '—' }}</strong>
                        <span class="cag-home-trust__label">{{ __('homepage.trust_countries_label') }}</span>
                    </div>
                </div>

                <ul class="cag-home-trust__assurances cag-reveal__item" style="--reveal-i: 4">
                    <li class="cag-home-trust__assurance">
                        <span class="cag-home-trust__assurance-icon" aria-hidden="true">
                            <i class="far fa-clock"></i>
                        </span>
                        <span class="cag-home-trust__assurance-text">
                            <strong>{{ __('homepage.trust_reply_title') }}</strong>
                            {{ __('homepage.trust_reply_text') }}
                        </span>
                    </li>
                    <li class="cag-home-trust__assurance">
                        <span class="cag-home-trust__assurance-icon" aria-hidden="true">
                            <i class="fas fa-shield-alt"></i>
                        </span>
                        <span class="cag-home-trust__assurance-text">
                            <strong>{{ __('homepage.trust_cancel_title') }}</strong>
                            {{ __('homepage.trust_cancel_text') }}
                        </span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>
