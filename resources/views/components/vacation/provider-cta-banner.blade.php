<section class="vacation-hub__provider-cta" data-analytics-vacation-rail="provider-cta">
    <div class="vacation-hub__provider-cta-copy">
        <h3 class="vacation-hub__provider-cta-title">{{ __('vacations.provider_cta_title') }}</h3>
        <p class="vacation-hub__provider-cta-text">{{ __('vacations.provider_cta_text') }}</p>
    </div>

    @if(config('guide_onboarding.new_onboarding_enabled'))
        @auth
            <a href="{{ route('guide.onboarding') }}" class="vacation-hub__provider-cta-btn">
                {{ __('vacations.provider_cta_button') }}
            </a>
        @else
            <a href="#" class="vacation-hub__provider-cta-btn" data-bs-toggle="modal" data-bs-target="#guideApplicationModal">
                {{ __('vacations.provider_cta_button') }}
            </a>
        @endauth
    @else
        <a href="{{ route('login') }}" class="vacation-hub__provider-cta-btn">
            {{ __('vacations.provider_cta_button') }}
        </a>
    @endif
</section>
