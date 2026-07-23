@props([
    'modalId' => 'mapModal',
    'title' => null,
    'resultCount' => null,
    'mapId' => 'map',
    'markers' => [],
    'center' => null,
    'instanceKey' => 'listing',
    'cluster' => true,
    'showGrayNearby' => true,
    'singleZoom' => 12,
    'defaultZoom' => 5,
])

@php
    $title = $title ?? translate('Map');
@endphp

<div class="modal fade map-modal" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content border-0">
            <div class="modal-header border-0">
                <h5 class="modal-title" id="{{ $modalId }}Label">
                    {{ $title }}
                    @if($resultCount !== null)
                        <span class="text-muted fw-normal">({{ $resultCount }})</span>
                    @endif
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0" style="height: calc(100vh - 56px);">
                <x-maps.listing
                    :markers="$markers"
                    layout="modal"
                    :modal-id="$modalId"
                    :map-id="$mapId"
                    height="100%"
                    :center="$center"
                    :instance-key="$instanceKey"
                    :cluster="$cluster"
                    :show-gray-nearby="$showGrayNearby"
                    :single-zoom="$singleZoom"
                    :default-zoom="$defaultZoom"
                    :lazy-modal="true"
                    :updatable="true"
                />
            </div>
        </div>
    </div>
</div>
