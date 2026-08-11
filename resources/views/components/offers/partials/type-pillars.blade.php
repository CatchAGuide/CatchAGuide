@php
    $groupClass = $groupClass ?? 'offers-filters__type-group vacation-filters__pillar-group';
    // Vacations chip = "all vacations"; full active only then. Soft context when trip/camp filtered.
    $vacationChipActive = $isVacation && $activeVacation === 'all';
@endphp
<div class="{{ $groupClass }}" role="group">
    <a href="{{ $typeUrl('all') }}"
       class="offers-filters__type-btn offers-filters__type-btn--all vacation-filters__pillar-btn {{ $activeType === 'all' ? 'is-active' : '' }}">
        {{ __('offers.filter_all') }} ({{ $total }})
    </a>
    <a href="{{ $typeUrl('tour') }}"
       class="offers-filters__type-btn offers-filters__type-btn--tour vacation-filters__pillar-btn {{ $activeType === 'tour' ? 'is-active' : '' }}">
        {{ __('offers.filter_tours') }} ({{ $toursTotal }})
    </a>
    <a href="{{ $typeUrl('vacation') }}"
       class="offers-filters__type-btn offers-filters__type-btn--vacation vacation-filters__pillar-btn {{ $vacationChipActive ? 'is-active' : '' }} {{ $isVacation ? 'is-vacation-context' : '' }}">
        {{ __('offers.filter_vacations') }} ({{ $vacationsTotal }})
    </a>

    @if($isVacation)
        <span class="offers-filters__vacation-extend" data-offers-vacation-subfilter role="group" aria-label="{{ __('offers.filter_vacations') }}">
            <a href="{{ $vacationUrl('trip') }}"
               class="offers-filters__type-btn offers-filters__type-btn--trip vacation-filters__pillar-btn {{ $activeVacation === 'trip' ? 'is-active' : '' }}">
                {{ __('offers.filter_trips') }} ({{ $tripsTotal }})
            </a>
            <a href="{{ $vacationUrl('camp') }}"
               class="offers-filters__type-btn offers-filters__type-btn--camp vacation-filters__pillar-btn {{ $activeVacation === 'camp' ? 'is-active' : '' }}">
                {{ __('offers.filter_camps') }} ({{ $campsTotal }})
            </a>
        </span>
    @endif
</div>
