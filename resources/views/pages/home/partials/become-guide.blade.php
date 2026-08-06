<section class="cag-home-partner" data-cag-reveal>
    <div class="cag-home-container cag-home-partner__inner">
        <div class="cag-home-partner__intro cag-reveal__block">
            <p class="cag-home-partner__eyebrow">{{ __('homepage.partner_eyebrow') }}</p>
            <h2 class="cag-home-partner__title">{{ __('homepage.partner_title') }}</h2>
            <p class="cag-home-partner__text">{{ __('homepage.partner_text') }}</p>
            <div class="cag-home-partner__actions">
                @if(config('guide_onboarding.new_onboarding_enabled'))
                    @auth
                        <a id="become-guide-homepage" href="{{ route('guide.onboarding') }}" class="cag-home-btn cag-home-btn--coral">
                            {{ __('homepage.partner_cta_primary') }} <i class="fas fa-arrow-right" aria-hidden="true"></i>
                        </a>
                    @else
                        <a id="become-guide-homepage" href="#" class="cag-home-btn cag-home-btn--coral" data-bs-toggle="modal" data-bs-target="#guideApplicationModal">
                            {{ __('homepage.partner_cta_primary') }} <i class="fas fa-arrow-right" aria-hidden="true"></i>
                        </a>
                    @endauth
                @else
                    <a id="become-guide-homepage" href="{{ route('login') }}" class="cag-home-btn cag-home-btn--coral">
                        {{ __('homepage.partner_cta_primary') }} <i class="fas fa-arrow-right" aria-hidden="true"></i>
                    </a>
                @endif
                <a href="{{ route('additional.contact') }}" class="cag-home-partner__learn-more">
                    {{ __('homepage.partner_cta_secondary') }}
                </a>
            </div>
        </div>

        <div class="cag-home-partner__cards">
            <article class="cag-home-partner__card cag-reveal__item" style="--reveal-i: 0">
                <span class="cag-home-partner__card-icon" aria-hidden="true">
                    <i class="fas fa-euro-sign"></i>
                </span>
                <h3 class="cag-home-partner__card-title">{{ __('homepage.partner_card_risk_title') }}</h3>
                <p class="cag-home-partner__card-text">{{ __('homepage.partner_card_risk_text') }}</p>
            </article>
            <article class="cag-home-partner__card cag-reveal__item" style="--reveal-i: 1">
                <span class="cag-home-partner__card-icon" aria-hidden="true">
                    <i class="fas fa-crosshairs"></i>
                </span>
                <h3 class="cag-home-partner__card-title">{{ __('homepage.partner_card_customers_title') }}</h3>
                <p class="cag-home-partner__card-text">{{ __('homepage.partner_card_customers_text') }}</p>
            </article>
            <article class="cag-home-partner__card cag-reveal__item" style="--reveal-i: 2">
                <span class="cag-home-partner__card-icon" aria-hidden="true">
                    <i class="fas fa-sliders-h"></i>
                </span>
                <h3 class="cag-home-partner__card-title">{{ __('homepage.partner_card_control_title') }}</h3>
                <p class="cag-home-partner__card-text">{{ __('homepage.partner_card_control_text') }}</p>
            </article>
        </div>
    </div>
</section>
