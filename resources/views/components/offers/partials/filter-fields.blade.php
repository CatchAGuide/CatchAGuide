@php
    /** @var \App\Domain\Offers\OfferListingFilter $filter */
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
    <div class="{{ $fieldClass }}">
        <label class="form-label">{{ __('offers.filter_species') }}</label>
        <select name="species" class="{{ $selectClass }}">
            <option value="">{{ __('vacations.select') }}</option>
            @foreach($speciesOptions as $species)
                <option value="{{ $species }}" @selected(($filter->species ?? '') === $species)>{{ $species }}</option>
            @endforeach
        </select>
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
