{{-- "Für Guides, Camps und Reiseanbieter" — reuses cag-home-partner__* styles (home.scss). --}}
<section class="cag-home-partner" data-cag-reveal>
    <div class="cag-home-container cag-home-partner__inner">
        <div class="cag-home-partner__intro cag-reveal__block">
            <p class="cag-home-partner__eyebrow">{{ __('homepage.landing_partner_eyebrow') }}</p>
            <h2 class="cag-home-partner__title">{{ __('homepage.landing_partner_title') }}</h2>
            <p class="cag-home-partner__text">{{ __('homepage.landing_partner_text') }}</p>
            <div class="cag-home-partner__actions">
                @if(config('guide_onboarding.new_onboarding_enabled'))
                    @auth
                        <a id="become-guide-landing" href="{{ route('guide.onboarding') }}" class="cag-home-btn cag-home-btn--coral">
                            {{ __('homepage.landing_partner_cta_primary') }}
                            @include('pages.home.partials.cag-icon', ['name' => 'arrow', 'size' => 15])
                        </a>
                    @else
                        <a id="become-guide-landing" href="#" class="cag-home-btn cag-home-btn--coral" data-bs-toggle="modal" data-bs-target="#guideApplicationModal">
                            {{ __('homepage.landing_partner_cta_primary') }}
                            @include('pages.home.partials.cag-icon', ['name' => 'arrow', 'size' => 15])
                        </a>
                    @endauth
                @else
                    <a id="become-guide-landing" href="{{ route('login') }}" class="cag-home-btn cag-home-btn--coral">
                        {{ __('homepage.landing_partner_cta_primary') }}
                        @include('pages.home.partials.cag-icon', ['name' => 'arrow', 'size' => 15])
                    </a>
                @endif
                <a href="{{ route('additional.partner') }}" class="cag-home-partner__learn-more">
                    {{ __('homepage.landing_partner_cta_secondary') }}
                </a>
            </div>
        </div>

        <div class="cag-home-partner__cards">
            <article class="cag-home-partner__card cag-reveal__item" style="--reveal-i: 0">
                <span class="cag-home-partner__card-icon" aria-hidden="true">€</span>
                <h3 class="cag-home-partner__card-title">{{ __('homepage.landing_partner_card_risk_title') }}</h3>
                <p class="cag-home-partner__card-text">{{ __('homepage.landing_partner_card_risk_text') }}</p>
            </article>
            <article class="cag-home-partner__card cag-reveal__item" style="--reveal-i: 1">
                <span class="cag-home-partner__card-icon" aria-hidden="true">◎</span>
                <h3 class="cag-home-partner__card-title">{{ __('homepage.landing_partner_card_demand_title') }}</h3>
                <p class="cag-home-partner__card-text">{{ __('homepage.landing_partner_card_demand_text') }}</p>
            </article>
            <article class="cag-home-partner__card cag-reveal__item" style="--reveal-i: 2">
                <span class="cag-home-partner__card-icon" aria-hidden="true">≡</span>
                <h3 class="cag-home-partner__card-title">{{ __('homepage.landing_partner_card_control_title') }}</h3>
                <p class="cag-home-partner__card-text">{{ __('homepage.landing_partner_card_control_text') }}</p>
            </article>
        </div>
    </div>
</section>
