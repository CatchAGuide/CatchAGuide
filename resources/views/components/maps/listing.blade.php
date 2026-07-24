@props([
    'markers' => [],
    'layout' => 'modal', // modal | embedded
    'modalId' => 'mapModal',
    'height' => null,
    'cluster' => true,
    'fitPrimaryBounds' => true,
    'showGrayNearby' => true,
    'singleZoom' => 12,
    'defaultZoom' => 5,
    'center' => null,
    'lazyModal' => true,
    'updatable' => true,
    'interactivePreview' => false,
    'instanceKey' => 'listing',
    'mapId' => 'map',
    'class' => '',
])

@php
    $center = $center ?: config('services.maps.default_center');
    $heightStyle = $height ?: ($layout === 'modal' ? '100%' : '480px');
    $centerPayload = [
        'lat' => (float) ($center['lat'] ?? $center['latitude'] ?? config('services.maps.default_center.lat')),
        'lng' => (float) ($center['lng'] ?? $center['longitude'] ?? config('services.maps.default_center.lng')),
    ];
    // HEX_TAG / HEX_AMP keep popup HTML safe inside <script type="application/json">
    $markersJson = json_encode(
        array_values($markers),
        JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS
    );
    $centerJson = json_encode($centerPayload, JSON_UNESCAPED_UNICODE);
@endphp

<x-maps.assets />

{{-- Markers live in a JSON script (not a data-* attribute) so popup HTML cannot break attribute parsing --}}
<script type="application/json" data-cag-maps-markers="{{ $mapId }}">{!! $markersJson !!}</script>
<script type="application/json" data-cag-maps-center="{{ $mapId }}">{!! $centerJson !!}</script>

<div
    id="{{ $mapId }}"
    {{ $attributes->class(['cag-map', 'cag-map--listing', $class]) }}
    style="height: {{ $heightStyle }};"
    data-maps-listing
    data-cluster="{{ $cluster ? 'true' : 'false' }}"
    data-fit-primary-bounds="{{ $fitPrimaryBounds ? 'true' : 'false' }}"
    data-show-gray-nearby="{{ $showGrayNearby ? 'true' : 'false' }}"
    data-single-zoom="{{ $singleZoom }}"
    data-default-zoom="{{ $defaultZoom }}"
    data-layout="{{ $layout }}"
    data-modal-id="{{ $modalId }}"
    data-lazy-modal="{{ $lazyModal ? 'true' : 'false' }}"
    data-updatable="{{ $updatable ? 'true' : 'false' }}"
    data-interactive-preview="{{ $interactivePreview ? 'true' : 'false' }}"
    data-instance-key="{{ $instanceKey }}"
></div>
