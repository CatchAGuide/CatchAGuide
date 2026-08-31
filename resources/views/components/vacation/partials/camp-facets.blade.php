@php
    $inputPrefix = $inputPrefix ?? 'vacation-camp';
    $interactive = $interactive ?? false;
@endphp

@if($interactive || $showCampFacets)
<div @if($interactive) data-vacation-facet-section="camp" @endif @if($interactive && ! $showCampFacets) class="d-none" @endif>
    @if($accommodationTypeOptions->isNotEmpty())
        <div class="{{ $fieldClass }}">
            <label class="form-label">{{ __('vacations.filter_accommodation_type') }}</label>
            <select name="accommodation_type" class="{{ $selectClass }}">
                <option value="">{{ __('vacations.filter_show_all') }}</option>
                @foreach($accommodationTypeOptions as $type)
                    <option value="{{ $type['id'] }}" @selected(($filter->accommodationTypeId ?? null) === (int) $type['id'])>
                        {{ $type['name'] }}
                    </option>
                @endforeach
            </select>
        </div>
    @endif

    <div class="{{ $fieldClass }} vacation-filters__toggles" role="group" aria-label="{{ __('vacations.filter_camps_only') }}">
        <div class="vacation-filters__check-btn">
            <input
                type="checkbox"
                name="has_guiding"
                id="{{ $inputPrefix }}-has-guiding"
                value="1"
                class="vacation-filters__check-input"
                data-vacation-facet-toggle
                @checked($filter->hasGuiding === true)
            >
            <label for="{{ $inputPrefix }}-has-guiding" class="vacation-filters__check-label">
                {{ __('vacations.filter_guiding') }}
            </label>
        </div>

        <div class="vacation-filters__check-btn">
            <input
                type="checkbox"
                name="has_rental_boat"
                id="{{ $inputPrefix }}-has-rental-boat"
                value="1"
                class="vacation-filters__check-input"
                data-vacation-facet-toggle
                @checked($filter->hasRentalBoat === true)
            >
            <label for="{{ $inputPrefix }}-has-rental-boat" class="vacation-filters__check-label">
                {{ __('vacations.filter_rental_boat') }}
            </label>
        </div>
    </div>
</div>
@endif
