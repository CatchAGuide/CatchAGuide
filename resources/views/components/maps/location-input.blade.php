@props([
    'name' => 'location',
    'id' => 'location',
    'value' => '',
    'placeholder' => null,
    'label' => null,
    'tooltip' => null,
    'required' => false,
    'class' => 'form-control',
    'types' => ['geocode'],
    // Linked field names / ids
    'latName' => 'latitude',
    'latId' => 'latitude',
    'lat' => '',
    'lngName' => 'longitude',
    'lngId' => 'longitude',
    'lng' => '',
    'countryName' => 'country',
    'countryId' => 'country',
    'country' => '',
    'cityName' => 'city',
    'cityId' => 'city',
    'city' => '',
    'regionName' => 'region',
    'regionId' => 'region',
    'region' => '',
    'postalName' => null,
    'postalId' => null,
    'postal' => '',
    'fillInput' => 'formatted_address', // formatted_address | name | keep
    'showLabel' => true,
    'wrapperClass' => 'form-group',
])

@php
    $placeholder = $placeholder ?? __('Enter location');
@endphp

<div {{ $attributes->only('class')->class([$wrapperClass]) }}>
    @if($showLabel && $label)
        <label for="{{ $id }}" class="form-label fw-bold fs-5">
            {{ $label }}
            @if($tooltip)
                <i class="fas fa-info-circle ms-2 fs-6" data-bs-toggle="tooltip" data-bs-placement="top"
                   title="{{ $tooltip }}"></i>
            @endif
        </label>
    @endif

    <input
        type="search"
        class="{{ $class }}"
        id="{{ $id }}"
        name="{{ $name }}"
        value="{{ $value }}"
        placeholder="{{ $placeholder }}"
        @if($required) required @endif
        autocomplete="off"
        data-places-location
        data-places-types='@json($types)'
        data-places-lat="#{{ $latId }}"
        data-places-lng="#{{ $lngId }}"
        data-places-country="#{{ $countryId }}"
        data-places-city="#{{ $cityId }}"
        data-places-region="#{{ $regionId }}"
        @if($postalId) data-places-postal="#{{ $postalId }}" @endif
        data-places-fill-input="{{ $fillInput }}"
    >

    <input type="hidden" name="{{ $latName }}" id="{{ $latId }}" value="{{ $lat }}">
    <input type="hidden" name="{{ $lngName }}" id="{{ $lngId }}" value="{{ $lng }}">
    <input type="hidden" name="{{ $countryName }}" id="{{ $countryId }}" value="{{ $country }}">
    <input type="hidden" name="{{ $cityName }}" id="{{ $cityId }}" value="{{ $city }}">
    <input type="hidden" name="{{ $regionName }}" id="{{ $regionId }}" value="{{ $region }}">
    @if($postalName && $postalId)
        <input type="hidden" name="{{ $postalName }}" id="{{ $postalId }}" value="{{ $postal }}">
    @endif
</div>
