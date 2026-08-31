@php
    $label = $label ?? __('partner.cta_become');
    $class = $class ?? 'cag-partner-hub__btn cag-partner-hub__btn--primary';
    $onboardingEnabled = (bool) config('guide_onboarding.new_onboarding_enabled');
@endphp
@if($onboardingEnabled && auth()->check())
    <a href="{{ route('guide.onboarding') }}" class="{{ $class }}">{{ $label }}</a>
@elseif($onboardingEnabled)
    <a href="#" class="{{ $class }}" data-bs-toggle="modal" data-bs-target="#guideApplicationModal">{{ $label }}</a>
@else
    <a href="{{ route('login') }}" class="{{ $class }}">{{ $label }}</a>
@endif
