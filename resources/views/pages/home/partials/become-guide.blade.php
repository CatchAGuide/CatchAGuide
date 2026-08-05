<section class="cag-home-partner" data-cag-reveal>
    <div class="cag-home-container cag-home-partner__inner cag-reveal__block">
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
            <a href="{{ route('additional.contact') }}" class="cag-home-btn cag-home-btn--ghost">
                {{ __('homepage.partner_cta_secondary') }}
            </a>
        </div>
    </div>
</section>
