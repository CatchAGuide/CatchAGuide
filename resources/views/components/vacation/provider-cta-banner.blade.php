{{-- Reuses cag-home-partner__* styles (home.scss) — same layout as guidings landing's provider-cta. --}}
<section class="vacation-hub__provider-cta cag-home-partner" data-analytics-vacation-rail="provider-cta">
    <div class="cag-home-container cag-home-partner__inner">
        <div class="cag-home-partner__intro">
            <p class="cag-home-partner__eyebrow">{{ __('vacations.provider_cta_eyebrow') }}</p>
            <h2 class="cag-home-partner__title">{{ __('vacations.provider_cta_title') }}</h2>
            <p class="cag-home-partner__text">{{ __('vacations.provider_cta_text') }}</p>
            <div class="cag-home-partner__actions">
                @if(config('guide_onboarding.new_onboarding_enabled'))
                    @auth
                        <a href="{{ route('guide.onboarding') }}" class="cag-home-btn cag-home-btn--coral">
                            {{ __('vacations.provider_cta_button') }}
                            @include('pages.home.partials.cag-icon', ['name' => 'arrow', 'size' => 15])
                        </a>
                    @else
                        <a href="#" class="cag-home-btn cag-home-btn--coral" data-bs-toggle="modal" data-bs-target="#guideApplicationModal">
                            {{ __('vacations.provider_cta_button') }}
                            @include('pages.home.partials.cag-icon', ['name' => 'arrow', 'size' => 15])
                        </a>
                    @endauth
                @else
                    <a href="{{ route('login') }}" class="cag-home-btn cag-home-btn--coral">
                        {{ __('vacations.provider_cta_button') }}
                        @include('pages.home.partials.cag-icon', ['name' => 'arrow', 'size' => 15])
                    </a>
                @endif
                <a href="{{ route('additional.partner') }}" class="cag-home-partner__learn-more">
                    {{ __('vacations.provider_cta_secondary') }}
                </a>
            </div>
        </div>

        <div class="cag-home-partner__cards">
            <article class="cag-home-partner__card">
                <span class="cag-home-partner__card-icon" aria-hidden="true">€</span>
                <h3 class="cag-home-partner__card-title">{{ __('vacations.provider_cta_card_risk_title') }}</h3>
                <p class="cag-home-partner__card-text">{{ __('vacations.provider_cta_card_risk_text') }}</p>
            </article>
            <article class="cag-home-partner__card">
                <span class="cag-home-partner__card-icon" aria-hidden="true">◎</span>
                <h3 class="cag-home-partner__card-title">{{ __('vacations.provider_cta_card_demand_title') }}</h3>
                <p class="cag-home-partner__card-text">{{ __('vacations.provider_cta_card_demand_text', ['countries' => $countryCount ?? 0]) }}</p>
            </article>
            <article class="cag-home-partner__card">
                <span class="cag-home-partner__card-icon" aria-hidden="true">≡</span>
                <h3 class="cag-home-partner__card-title">{{ __('vacations.provider_cta_card_control_title') }}</h3>
                <p class="cag-home-partner__card-text">{{ __('vacations.provider_cta_card_control_text') }}</p>
            </article>
        </div>
    </div>
</section>
