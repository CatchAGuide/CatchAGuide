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
    'updatable' => true,
    'lazyModal' => true,
    'interactivePreview' => false,
])

@php
    $title = $title ?? __('vacations.map_modal_title');
@endphp

<div class="modal fade map-modal" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
    <div class="modal-dialog map-modal__dialog">
        <div class="modal-content map-modal__content">

            <div class="map-modal__header">
                <div class="map-modal__header-left">
                    <span class="map-modal__pin-icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                        </svg>
                    </span>
                    <div>
                        <h6 class="map-modal__title" id="{{ $modalId }}Label">{{ $title }}</h6>
                        @if($resultCount !== null && (int) $resultCount > 0)
                            <span class="map-modal__subtitle">
                                {{ $resultCount }} {{ (int) $resultCount === 1 ? translate('result') : translate('results') }}
                            </span>
                        @endif
                    </div>
                </div>
                <button type="button" class="map-modal__close" data-bs-dismiss="modal" aria-label="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>

            <x-maps.listing
                class="map-modal__map"
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
                :lazy-modal="$lazyModal"
                :updatable="$updatable"
                :interactive-preview="$interactivePreview"
            />

        </div>
    </div>
</div>
