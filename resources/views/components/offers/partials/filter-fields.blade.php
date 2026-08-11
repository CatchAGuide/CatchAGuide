@php
    /** @var \App\Domain\Offers\OfferListingFilter $filter */
    $speciesInputPrefix = $speciesInputPrefix ?? 'offers-species';
    $selectedSpeciesIds = collect($filter->speciesIds ?? [])->map(fn ($id) => (int) $id)->all();
    $speciesWhitelist = $speciesOptions
        ->filter(fn ($species) => is_array($species) && (int) ($species['id'] ?? 0) > 0)
        ->map(fn ($species) => [
            'value' => (string) $species['id'],
            'label' => (string) ($species['name'] ?? ''),
        ])
        ->values()
        ->all();
    $selectedSpecies = collect($speciesWhitelist)
        ->filter(fn ($species) => in_array((int) $species['value'], $selectedSpeciesIds, true))
        ->values()
        ->all();
    $speciesSelectData = [
        'options' => $speciesWhitelist,
        'selected' => array_map('strval', $selectedSpeciesIds),
        'placeholder' => __('offers.filter_species_placeholder'),
        'removeLabel' => __('offers.remove_filter'),
    ];
@endphp

@if($countries->isNotEmpty())
    <div class="{{ $fieldClass }}">
        <label class="form-label">{{ __('offers.filter_country') }}</label>
        <select name="country" class="{{ $selectClass }}">
            <option value="">{{ __('offers.all_countries') }}</option>
            @foreach($countries as $row)
                <option value="{{ $row['slug'] }}" @selected(($filter->country ?? '') === $row['slug'])>
                    {{ translate($row['name']) }}
                </option>
            @endforeach
        </select>
    </div>
@endif

@if($speciesOptions->isNotEmpty())
    <div class="{{ $fieldClass }} offers-filters__species">
        <label class="form-label" id="{{ $speciesInputPrefix }}-label">{{ __('offers.filter_species') }}</label>
        <div
            class="offers-species-select"
            data-offers-species-select
            data-input-name="species[]"
        >
            <script type="application/json" data-offers-species-data>
                @json($speciesSelectData)
            </script>

            <button
                type="button"
                class="offers-species-select__control"
                data-offers-species-toggle
                aria-haspopup="listbox"
                aria-expanded="false"
                aria-labelledby="{{ $speciesInputPrefix }}-label"
                id="{{ $speciesInputPrefix }}-toggle"
            >
                <span class="offers-species-select__tags" data-offers-species-tags>
                    @forelse($selectedSpecies as $species)
                        <span class="offers-species-select__tag" data-value="{{ $species['value'] }}">
                            <span class="offers-species-select__tag-text">{{ $species['label'] }}</span>
                            <span class="offers-species-select__tag-remove" data-offers-species-remove="{{ $species['value'] }}" aria-hidden="true">&times;</span>
                        </span>
                    @empty
                        <span class="offers-species-select__placeholder">{{ __('offers.filter_species_placeholder') }}</span>
                    @endforelse
                </span>
                <span class="offers-species-select__caret" aria-hidden="true"></span>
            </button>

            <div
                class="offers-species-select__dropdown"
                data-offers-species-dropdown
                hidden
                role="listbox"
                aria-multiselectable="true"
                aria-labelledby="{{ $speciesInputPrefix }}-label"
            >
                <div class="offers-species-select__search">
                    <input
                        type="search"
                        class="offers-species-select__search-input"
                        data-offers-species-search
                        placeholder="{{ __('offers.filter_species_search') }}"
                        autocomplete="off"
                    >
                </div>
                <div class="offers-species-select__list" data-offers-species-list>
                    @foreach($speciesWhitelist as $species)
                        @php
                            $isChecked = in_array((int) $species['value'], $selectedSpeciesIds, true);
                            $optionId = $speciesInputPrefix.'-opt-'.$species['value'];
                        @endphp
                        <label
                            class="offers-species-select__option{{ $isChecked ? ' is-checked' : '' }}"
                            data-offers-species-option
                            data-value="{{ $species['value'] }}"
                            data-label="{{ $species['label'] }}"
                            for="{{ $optionId }}"
                        >
                            <input
                                type="checkbox"
                                class="offers-species-select__checkbox"
                                id="{{ $optionId }}"
                                value="{{ $species['value'] }}"
                                @checked($isChecked)
                                data-offers-species-checkbox
                            >
                            <span class="offers-species-select__option-label">{{ $species['label'] }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="offers-species-select__inputs" data-offers-species-inputs>
                @foreach($selectedSpecies as $species)
                    <input type="hidden" name="species[]" value="{{ $species['value'] }}">
                @endforeach
            </div>
        </div>
    </div>
@endif

@if($showTourFacets)
    @if($methodOptions->isNotEmpty())
        <div class="{{ $fieldClass }}">
            <label class="form-label">{{ __('offers.filter_method') }}</label>
            <select name="methods" class="{{ $selectClass }}">
                <option value="">{{ __('offers.select_any') }}</option>
                @foreach($methodOptions as $method)
                    <option value="{{ $method['id'] }}" @selected(($filter->methodId ?? null) === (int) $method['id'])>
                        {{ $method['name'] }}
                    </option>
                @endforeach
            </select>
        </div>
    @endif

    @if($waterOptions->isNotEmpty())
        <div class="{{ $fieldClass }}">
            <label class="form-label">{{ __('offers.filter_water_type') }}</label>
            <select name="water" class="{{ $selectClass }}">
                <option value="">{{ __('offers.select_any') }}</option>
                @foreach($waterOptions as $water)
                    <option value="{{ $water['id'] }}" @selected(($filter->waterId ?? null) === (int) $water['id'])>
                        {{ $water['name'] }}
                    </option>
                @endforeach
            </select>
        </div>
    @endif

    @if($tourDurationOptions->isNotEmpty())
        <div class="{{ $fieldClass }}">
            <label class="form-label">{{ __('offers.filter_duration') }}</label>
            <select name="duration_types" class="{{ $selectClass }}">
                <option value="">{{ __('offers.select_any') }}</option>
                @foreach($tourDurationOptions as $duration)
                    <option value="{{ $duration['value'] }}" @selected(($filter->durationType ?? '') === $duration['value'])>
                        {{ $duration['label'] }}
                    </option>
                @endforeach
            </select>
        </div>
    @endif
@endif

@if($showCampFacets)
    @if($accommodationTypeOptions->isNotEmpty())
        <div class="{{ $fieldClass }}">
            <label class="form-label">{{ __('offers.filter_accommodation_type') }}</label>
            <select name="accommodation_type" class="{{ $selectClass }}">
                <option value="">{{ __('offers.select_any') }}</option>
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
            <option value="">{{ __('offers.select_any') }}</option>
            <option value="1" @selected($filter->hasGuiding === true)>{{ __('offers.filter_yes') }}</option>
            <option value="0" @selected($filter->hasGuiding === false)>{{ __('offers.filter_no') }}</option>
        </select>
    </div>

    <div class="{{ $fieldClass }}">
        <label class="form-label">{{ __('offers.filter_rental_boat') }}</label>
        <select name="has_rental_boat" class="{{ $selectClass }}">
            <option value="">{{ __('offers.select_any') }}</option>
            <option value="1" @selected($filter->hasRentalBoat === true)>{{ __('offers.filter_yes') }}</option>
            <option value="0" @selected($filter->hasRentalBoat === false)>{{ __('offers.filter_no') }}</option>
        </select>
    </div>
@endif

@if($showTripFacets && $tripDurationOptions->isNotEmpty())
    <div class="{{ $fieldClass }}">
        <label class="form-label">{{ __('offers.filter_duration') }}</label>
        <select name="duration" class="{{ $selectClass }}">
            <option value="">{{ __('offers.select_any') }}</option>
            @foreach($tripDurationOptions as $duration)
                <option value="{{ $duration['value'] }}" @selected(($filter->tripDuration ?? '') === $duration['value'])>
                    {{ $duration['label'] }}
                </option>
            @endforeach
        </select>
    </div>
@endif
