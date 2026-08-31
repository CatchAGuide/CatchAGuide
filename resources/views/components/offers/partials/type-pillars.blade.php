@php
    $groupClass = $groupClass ?? 'offers-filters__type-group vacation-filters__pillar-group';
    // Vacations chip = "all vacations"; full active only then. Soft context when trip/camp filtered.
    $vacationChipActive = $isVacation && $activeVacation === 'all';
    // Interactive mode (mobile filter modal): pillars only update local state; navigation
    // is deferred to the modal's "Apply Filters" submit instead of firing on every tap.
    $interactive = $interactive ?? false;
    $showTours = $toursTotal > 0 || $activeType === 'tour';
    $showVacations = $vacationsTotal > 0 || $isVacation;
    $showTrips = $tripsTotal > 0 || $activeVacation === 'trip';
    $showCamps = $campsTotal > 0 || $activeVacation === 'camp';
    $showVacationSubfilters = $showVacations && ($showTrips || $showCamps);
@endphp
<div class="{{ $groupClass }}" role="group" @if($interactive) data-offers-pillar-group @endif>
    @if($interactive)
        <button type="button" data-pillar-type="all"
           class="offers-filters__type-btn offers-filters__type-btn--all vacation-filters__pillar-btn {{ $activeType === 'all' ? 'is-active' : '' }}">
            {{ __('offers.filter_all') }} ({{ $total }})
        </button>
        @if($showTours)
        <button type="button" data-pillar-type="tour"
           class="offers-filters__type-btn offers-filters__type-btn--tour vacation-filters__pillar-btn {{ $activeType === 'tour' ? 'is-active' : '' }}">
            {{ __('offers.filter_tours') }} ({{ $toursTotal }})
        </button>
        @endif
        @if($showVacations)
        <button type="button" data-pillar-type="vacation"
           class="offers-filters__type-btn offers-filters__type-btn--vacation vacation-filters__pillar-btn {{ $vacationChipActive ? 'is-active' : '' }} {{ $isVacation ? 'is-vacation-context' : '' }}">
            {{ __('offers.filter_vacations') }} ({{ $vacationsTotal }})
        </button>
        @endif

        @if($showVacationSubfilters)
        <span class="offers-filters__vacation-extend {{ $isVacation ? '' : 'd-none' }}" data-offers-vacation-subfilter role="group" aria-label="{{ __('offers.filter_vacations') }}">
            @if($showTrips)
            <button type="button" data-pillar-vacation="trip"
               class="offers-filters__type-btn offers-filters__type-btn--trip vacation-filters__pillar-btn {{ $activeVacation === 'trip' ? 'is-active' : '' }}">
                {{ __('offers.filter_trips') }} ({{ $tripsTotal }})
            </button>
            @endif
            @if($showCamps)
            <button type="button" data-pillar-vacation="camp"
               class="offers-filters__type-btn offers-filters__type-btn--camp vacation-filters__pillar-btn {{ $activeVacation === 'camp' ? 'is-active' : '' }}">
                {{ __('offers.filter_camps') }} ({{ $campsTotal }})
            </button>
            @endif
        </span>
        @endif
    @else
        <a href="{{ $typeUrl('all') }}"
           class="offers-filters__type-btn offers-filters__type-btn--all vacation-filters__pillar-btn {{ $activeType === 'all' ? 'is-active' : '' }}">
            {{ __('offers.filter_all') }} ({{ $total }})
        </a>
        @if($showTours)
        <a href="{{ $typeUrl('tour') }}"
           class="offers-filters__type-btn offers-filters__type-btn--tour vacation-filters__pillar-btn {{ $activeType === 'tour' ? 'is-active' : '' }}">
            {{ __('offers.filter_tours') }} ({{ $toursTotal }})
        </a>
        @endif
        @if($showVacations)
        <a href="{{ $typeUrl('vacation') }}"
           class="offers-filters__type-btn offers-filters__type-btn--vacation vacation-filters__pillar-btn {{ $vacationChipActive ? 'is-active' : '' }} {{ $isVacation ? 'is-vacation-context' : '' }}">
            {{ __('offers.filter_vacations') }} ({{ $vacationsTotal }})
        </a>
        @endif

        @if($isVacation && $showVacationSubfilters)
            <span class="offers-filters__vacation-extend" data-offers-vacation-subfilter role="group" aria-label="{{ __('offers.filter_vacations') }}">
                @if($showTrips)
                <a href="{{ $vacationUrl('trip') }}"
                   class="offers-filters__type-btn offers-filters__type-btn--trip vacation-filters__pillar-btn {{ $activeVacation === 'trip' ? 'is-active' : '' }}">
                    {{ __('offers.filter_trips') }} ({{ $tripsTotal }})
                </a>
                @endif
                @if($showCamps)
                <a href="{{ $vacationUrl('camp') }}"
                   class="offers-filters__type-btn offers-filters__type-btn--camp vacation-filters__pillar-btn {{ $activeVacation === 'camp' ? 'is-active' : '' }}">
                    {{ __('offers.filter_camps') }} ({{ $campsTotal }})
                </a>
                @endif
            </span>
        @endif
    @endif
</div>
