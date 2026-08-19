<section class="vacation-hub__consultation" data-analytics-vacation-rail="consultation">
    <div class="vacation-hub__consultation-copy">
        <p class="vacation-hub__consultation-eyebrow">{{ __('vacations.hub_consultation_eyebrow') }}</p>
        <h2 class="vacation-hub__consultation-title">{{ __('vacations.hub_consultation_title') }}</h2>
        <p class="vacation-hub__consultation-lead">{{ __('vacations.hub_consultation_lead') }}</p>

        <ul class="vacation-hub__consultation-checklist">
            @foreach(config('vacations.hub_consultation_checklist', []) as $point)
                <li class="vacation-hub__consultation-point">
                    <i class="fas {{ $point['icon'] }}" aria-hidden="true"></i>
                    <span>{{ __($point['text_key']) }}</span>
                </li>
            @endforeach
        </ul>
    </div>

    <div class="vacation-hub__consultation-card">
        <span class="vacation-hub__consultation-avatar" aria-hidden="true">
            <i class="fas fa-headset"></i>
        </span>
        <div class="vacation-hub__consultation-contact">
            <div class="vacation-hub__consultation-name">{{ __('vacations.hub_consultation_contact_name') }}</div>
            <a href="tel:+49{{ config('cag.contact_num') }}" class="vacation-hub__consultation-phone">
                +49 (0) {{ config('cag.contact_num') }}
            </a>
            <div class="vacation-hub__consultation-note">{{ __('vacations.hub_consultation_response_note') }}</div>
        </div>
    </div>

    <a href="{{ route('additional.contact') }}" class="vacation-hub__consultation-cta">
        {{ __('vacations.hub_consultation_cta') }}
    </a>
</section>
