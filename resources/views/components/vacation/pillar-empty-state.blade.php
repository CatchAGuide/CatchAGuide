@props(['country', 'pillar', 'countryName' => null])

@php
    $displayCountry = $countryName ?? ucfirst(str_replace('-', ' ', $country));
    $bodyKey = $pillar === 'trip' ? 'vacations.empty_state_body_trip' : 'vacations.empty_state_body_camp';
    $icon = $pillar === 'trip' ? 'fa-suitcase-rolling' : 'fa-campground';
@endphp

<div class="vacation-pillar-empty" data-analytics-vacation-empty data-pillar="{{ $pillar }}" data-country="{{ $country }}">
    <div class="vacation-pillar-empty__icon" aria-hidden="true">
        <i class="fas {{ $icon }}"></i>
    </div>
    <p class="vacation-pillar-empty__body">{{ __($bodyKey, ['country' => $displayCountry]) }}</p>

    @if(session('vacation_interest_success'))
        <p class="vacation-pillar-empty__success">{{ session('vacation_interest_success') }}</p>
    @else
        <form method="post" action="{{ route('vacations.interest.store') }}" class="vacation-pillar-empty__form">
            @csrf
            <input type="hidden" name="country" value="{{ $country }}">
            <input type="hidden" name="pillar" value="{{ $pillar }}">
            <div class="vacation-pillar-empty__fields">
                <input type="email" name="email" class="form-control" placeholder="{{ __('contact.email') }}" required>
                <button type="submit" class="btn btn-orange">{{ __('vacations.notify_me') }}</button>
            </div>
        </form>
    @endif
</div>
