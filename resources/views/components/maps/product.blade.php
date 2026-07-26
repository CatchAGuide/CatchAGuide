@props([
    'lat',
    'lng',
    'height' => '400px',
    'zoom' => 10,
    'title' => null,
    'popupHtml' => null,
    'scrollWheel' => false,
    'dragging' => true,
    'markerVariant' => 'primary',
    'onMarkerClick' => 'popup', // popup | modal | none
    'modalTarget' => null,
    'lazy' => true,
    'id' => null,
    'class' => '',
])

@php
    $mapId = $id ?: 'cag-product-map-' . uniqid();
@endphp

<x-maps.assets />

<div
    id="{{ $mapId }}"
    {{ $attributes->class(['cag-map', 'cag-map--product', $class]) }}
    style="height: {{ $height }};"
    data-maps-product
    data-lat="{{ $lat }}"
    data-lng="{{ $lng }}"
    data-zoom="{{ $zoom }}"
    @if($title) data-title="{{ $title }}" @endif
    data-scroll-wheel="{{ $scrollWheel ? 'true' : 'false' }}"
    data-dragging="{{ $dragging ? 'true' : 'false' }}"
    data-marker-variant="{{ $markerVariant }}"
    data-on-marker-click="{{ $onMarkerClick }}"
    @if($modalTarget) data-modal-target="{{ $modalTarget }}" @endif
    data-lazy="{{ $lazy ? 'true' : 'false' }}"
>
    @if($popupHtml && $onMarkerClick === 'popup')
        <template data-maps-popup>{!! $popupHtml !!}</template>
    @endif
</div>
