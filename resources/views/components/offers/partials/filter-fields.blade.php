@php
    /** @var \App\Domain\Offers\OfferListingFilter $filter */
    $speciesInputPrefix = $speciesInputPrefix ?? 'offers-species';
    $methodsInputPrefix = $methodsInputPrefix ?? 'offers-methods';
    $waterInputPrefix = $waterInputPrefix ?? 'offers-water';
    $durationInputPrefix = $durationInputPrefix ?? 'offers-duration';
@endphp

@if($filter->isVacation() && is_callable($vacationUrl ?? null))
    @php
        $activeVacation = $filter->vacation ?? 'all';
    @endphp
    <div class="{{ $fieldClass }} offers-filters__vacation-type" data-offers-vacation-type>
        <div
            class="offers-filters__vacation-type-btns"
            role="group"
            aria-label="{{ __('offers.filter_vacation_type') }}"
        >
            <a
                href="{{ $activeVacation === 'trip' ? $vacationUrl('all') : $vacationUrl('trip') }}"
                class="offers-filters__vacation-type-btn {{ $activeVacation === 'trip' ? 'is-active' : '' }}"
                @if($activeVacation === 'trip') aria-pressed="true" @else aria-pressed="false" @endif
            >
                {{ __('offers.filter_trips') }}
            </a>
            <a
                href="{{ $activeVacation === 'camp' ? $vacationUrl('all') : $vacationUrl('camp') }}"
                class="offers-filters__vacation-type-btn {{ $activeVacation === 'camp' ? 'is-active' : '' }}"
                @if($activeVacation === 'camp') aria-pressed="true" @else aria-pressed="false" @endif
            >
                {{ __('offers.filter_camps') }}
            </a>
        </div>
    </div>
@endif

@if($countries->isNotEmpty())
    @php
        $regionLockedByPlace = $filter->hasPlaceSearch();
        $regionLockMessage = __('offers.filter_country_locked_by_place');
        $regionSelectId = ($speciesInputPrefix ?? 'offers-species').'-country';
    @endphp
    <div class="{{ $fieldClass }} offers-filters__region{{ $regionLockedByPlace ? ' is-locked' : '' }}" data-offers-region-field>
        <label class="form-label offers-filters__region-label" for="{{ $regionSelectId }}">
            <span>{{ __('offers.filter_country') }}</span>
            <button
                type="button"
                class="offers-filters__region-tip"
                data-offers-region-tip
                data-bs-toggle="tooltip"
                data-bs-placement="top"
                data-bs-trigger="hover focus click"
                title="{{ $regionLockMessage }}"
                aria-label="{{ $regionLockMessage }}"
                @if(! $regionLockedByPlace) hidden @endif
            >
                <i class="fas fa-info-circle" aria-hidden="true"></i>
            </button>
        </label>
        <div
            class="offers-filters__region-control"
            data-offers-region-control
            @if($regionLockedByPlace)
                data-bs-toggle="tooltip"
                data-bs-placement="top"
                data-bs-trigger="hover focus click"
                title="{{ $regionLockMessage }}"
            @endif
        >
            @if($regionLockedByPlace && filled($filter->country))
                <input type="hidden" name="country" value="{{ $filter->country }}" data-offers-region-hidden>
            @endif
            <select name="country" id="{{ $regionSelectId }}" class="{{ $selectClass }}" data-offers-region-select @disabled($regionLockedByPlace) @if($regionLockedByPlace) aria-disabled="true" @endif>
                <option value="">{{ __('offers.filter_show_all') }}</option>
                @foreach($countries as $row)
                    <option value="{{ $row['slug'] }}" @selected(($filter->country ?? '') === $row['slug'])>
                        {{ translate($row['name']) }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
@endif

@include('components.offers.partials.multi-select', [
    'fieldClass' => $fieldClass,
    'wrapperClass' => 'offers-filters__species',
    'inputName' => 'species[]',
    'inputPrefix' => $speciesInputPrefix,
    'label' => __('offers.filter_species'),
    'placeholder' => __('offers.filter_species_placeholder'),
    'searchPlaceholder' => __('offers.filter_species_search'),
    'options' => $speciesOptions,
    'selectedValues' => array_merge($filter->speciesIds ?? [], $filter->speciesNames ?? []),
])

@if($showTourFacets)
    @include('components.offers.partials.multi-select', [
        'fieldClass' => $fieldClass,
        'wrapperClass' => 'offers-filters__methods',
        'inputName' => 'methods[]',
        'inputPrefix' => $methodsInputPrefix,
        'label' => __('offers.filter_method'),
        'placeholder' => __('offers.filter_method_placeholder'),
        'searchPlaceholder' => __('offers.filter_method_search'),
        'options' => $methodOptions,
        'selectedValues' => $filter->methodIds ?? [],
    ])

    @include('components.offers.partials.multi-select', [
        'fieldClass' => $fieldClass,
        'wrapperClass' => 'offers-filters__water',
        'inputName' => 'water[]',
        'inputPrefix' => $waterInputPrefix,
        'label' => __('offers.filter_water_type'),
        'placeholder' => __('offers.filter_water_placeholder'),
        'searchPlaceholder' => __('offers.filter_water_search'),
        'options' => $waterOptions,
        'selectedValues' => $filter->waterIds ?? [],
    ])

    @include('components.offers.partials.multi-select', [
        'fieldClass' => $fieldClass,
        'wrapperClass' => 'offers-filters__duration',
        'inputName' => 'duration_types[]',
        'inputPrefix' => $durationInputPrefix,
        'label' => __('offers.filter_duration'),
        'placeholder' => __('offers.filter_duration_placeholder'),
        'searchPlaceholder' => __('offers.filter_duration_search'),
        'options' => $tourDurationOptions,
        'selectedValues' => $filter->durationTypes ?? [],
    ])
@endif

@if($showCampFacets)
    @if($accommodationTypeOptions->isNotEmpty())
        <div class="{{ $fieldClass }}">
            <label class="form-label">{{ __('offers.filter_accommodation_type') }}</label>
            <select name="accommodation_type" class="{{ $selectClass }}">
                <option value="">{{ __('offers.filter_show_all') }}</option>
                @foreach($accommodationTypeOptions as $type)
                    <option value="{{ $type['id'] }}" @selected(($filter->accommodationTypeId ?? null) === (int) $type['id'])>
                        {{ $type['name'] }}
                    </option>
                @endforeach
            </select>
        </div>
    @endif

    <div class="{{ $fieldClass }}">
        <label class="form-label">{{ __('offers.filter_guiding') }}</label>
        <select name="has_guiding" class="{{ $selectClass }}">
            <option value="">{{ __('offers.filter_show_all') }}</option>
            <option value="1" @selected($filter->hasGuiding === true)>{{ __('offers.filter_yes') }}</option>
            <option value="0" @selected($filter->hasGuiding === false)>{{ __('offers.filter_no') }}</option>
        </select>
    </div>

    <div class="{{ $fieldClass }}">
        <label class="form-label">{{ __('offers.filter_rental_boat') }}</label>
        <select name="has_rental_boat" class="{{ $selectClass }}">
            <option value="">{{ __('offers.filter_show_all') }}</option>
            <option value="1" @selected($filter->hasRentalBoat === true)>{{ __('offers.filter_yes') }}</option>
            <option value="0" @selected($filter->hasRentalBoat === false)>{{ __('offers.filter_no') }}</option>
        </select>
    </div>
@endif

@if($showTripFacets && $tripDurationOptions->isNotEmpty())
    <div class="{{ $fieldClass }}">
        <label class="form-label">{{ __('offers.filter_duration') }}</label>
        <select name="duration" class="{{ $selectClass }}">
            <option value="">{{ __('offers.filter_show_all') }}</option>
            @foreach($tripDurationOptions as $duration)
                <option value="{{ $duration['value'] }}" @selected(($filter->tripDuration ?? '') === $duration['value'])>
                    {{ $duration['label'] }}
                </option>
            @endforeach
        </select>
    </div>
@endif
