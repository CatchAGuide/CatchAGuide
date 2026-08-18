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
    'showRail' => true,
    'showFilterChips' => true,
    'priceChips' => true,
    'landmarks' => false,
    'filterFormId' => 'filterContainer',
])

@php
    $title = $title ?? __('vacations.map_modal_title');
    $mapsI18n = [
        'in_map_area' => __('destination.map_in_area', ['count' => ':count']),
        'in_map_area_one' => __('destination.map_in_area_one', ['count' => ':count']),
        'in_map_area_zero' => __('destination.map_in_area_zero'),
        'view_details' => __('vacations.view_details'),
        'close_map' => __('destination.close_map'),
        'show_list' => __('destination.map_show_list'),
        'hide_list' => __('destination.map_hide_list'),
        'show_map' => __('destination.map_show_map'),
        'filters' => __('destination.filter_by'),
        'duration' => translate('Duration'),
        'price' => translate('Your budget'),
        'target_fish' => translate('Target Fish'),
        'methods' => translate('Methods'),
        'people' => translate('Number of People'),
        'clear_filters' => __('destination.map_clear_filters'),
        'apply' => __('destination.map_apply_filters'),
        'price_from' => __('destination.map_price_from', ['price' => ':price']),
        'reviews' => __('destination.map_reviews', ['count' => ':count']),
        'prev' => __('destination.map_prev_image'),
        'next' => __('destination.map_next_image'),
        'show_on_map' => __('destination.map_show_on_map'),
        'landmark_airport' => __('destination.landmark_airport'),
        'landmark_harbour' => __('destination.landmark_harbour'),
        'landmark_park' => __('destination.landmark_park'),
        'landmark_attraction' => __('destination.landmark_attraction'),
        'landmark_town' => __('destination.landmark_town'),
    ];
@endphp

<div
    class="modal fade map-modal map-modal--split"
    id="{{ $modalId }}"
    tabindex="-1"
    aria-labelledby="{{ $modalId }}Label"
    aria-hidden="true"
    data-map-modal
    data-map-show-rail="{{ $showRail ? 'true' : 'false' }}"
    data-map-show-chips="{{ $showFilterChips ? 'true' : 'false' }}"
    data-map-filter-form="{{ $filterFormId }}"
    data-maps-i18n='@json($mapsI18n)'
>
    <div class="modal-dialog map-modal__dialog">
        <div class="modal-content map-modal__content">

            <div class="map-modal__chrome">
                <div class="map-modal__header">
                    <div class="map-modal__header-left">
                        <span class="map-modal__pin-icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                            </svg>
                        </span>
                        <div>
                            <h6 class="map-modal__title" id="{{ $modalId }}Label">{{ $title }}</h6>
                            <span class="map-modal__subtitle" data-map-viewport-count>
                                @if($resultCount !== null && (int) $resultCount > 0)
                                    {{ $resultCount }} {{ (int) $resultCount === 1 ? translate('result') : translate('results') }}
                                @endif
                            </span>
                        </div>
                    </div>

                    @if($showFilterChips)
                        <div class="map-modal__chips" data-map-filter-chips>
                            <button type="button" class="map-modal__chip" data-map-chip="filters" aria-expanded="false">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v1.586a1 1 0 01-.293.707l-5.414 5.414A1 1 0 0011 12.414V17a1 1 0 01-1.447.894l-2-1A1 1 0 017 16v-3.586a1 1 0 00-.293-.707L1.293 6.293A1 1 0 011 5.586V4z"/>
                                </svg>
                                <span>{{ __('destination.filter_by') }}</span>
                            </button>
                            <button type="button" class="map-modal__chip" data-map-chip="duration" aria-expanded="false">
                                <span>{{ translate('Duration') }}</span>
                            </button>
                            <button type="button" class="map-modal__chip" data-map-chip="price" aria-expanded="false">
                                <span>{{ translate('Your budget') }}</span>
                            </button>
                            <button type="button" class="map-modal__chip" data-map-chip="target_fish" aria-expanded="false">
                                <span>{{ translate('Target Fish') }}</span>
                            </button>
                            <button type="button" class="map-modal__chip" data-map-chip="methods" aria-expanded="false">
                                <span>{{ translate('Methods') }}</span>
                            </button>
                            <button type="button" class="map-modal__chip" data-map-chip="people" aria-expanded="false">
                                <span>{{ translate('Number of People') }}</span>
                            </button>
                        </div>
                    @endif

                    <div class="map-modal__header-actions">
                        @if($showRail)
                            <button
                                type="button"
                                class="map-modal__rail-toggle"
                                data-map-rail-toggle
                                aria-expanded="false"
                                aria-controls="{{ $modalId }}-rail"
                            >
                                <span data-map-rail-toggle-label>{{ __('destination.map_show_list') }}</span>
                            </button>
                        @endif
                        <button type="button" class="map-modal__close" data-bs-dismiss="modal" aria-label="{{ __('destination.close_map') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                            <span class="map-modal__close-label">{{ __('destination.close_map') }}</span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="map-modal__body">
                @if($showRail)
                    <aside class="map-modal__rail" id="{{ $modalId }}-rail" data-map-modal-rail aria-label="{{ __('destination.map_listings_rail') }}">
                        <div
                            class="map-modal__rail-grabber"
                            data-map-rail-handle
                            role="separator"
                            aria-orientation="horizontal"
                            aria-label="{{ __('destination.map_resize_list') }}"
                        >
                            <span class="map-modal__rail-handle" aria-hidden="true"></span>
                        </div>
                        <div class="map-modal__rail-heading" data-map-rail-heading>
                            <span class="map-modal__rail-heading-text" data-map-viewport-count>
                                @if($resultCount !== null && (int) $resultCount > 0)
                                    {{ $resultCount }} {{ (int) $resultCount === 1 ? translate('result') : translate('results') }}
                                @endif
                            </span>
                        </div>
                        <div class="map-modal__rail-list" data-map-rail-list></div>
                        <p class="map-modal__rail-empty" data-map-rail-empty hidden>{{ __('destination.map_in_area_zero') }}</p>
                    </aside>
                @endif

                <div class="map-modal__map-pane">
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
                        :price-chips="$priceChips"
                        :landmarks="$landmarks"
                        :viewport-rail="$showRail"
                    />
                    <div class="map-modal__selection" data-map-selection hidden></div>
                    @if($showRail)
                        <button type="button" class="map-modal__fab" data-map-sheet-fab>
                            <span class="map-modal__fab-icon map-modal__fab-icon--map" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M12 1.586l-4 4v12.828l4-4V1.586zM3.707 3.293A1 1 0 002 4v10a1 1 0 00.293.707L6 18.414V5.586L3.707 3.293zM17.707 5.293L14 1.586v12.828l2.293 2.293A1 1 0 0018 16V6a1 1 0 00-.293-.707z" clip-rule="evenodd"/>
                                </svg>
                            </span>
                            <span class="map-modal__fab-icon map-modal__fab-icon--list" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
                                </svg>
                            </span>
                            <span data-map-sheet-fab-label>{{ __('destination.map_show_map') }}</span>
                        </button>
                    @endif
                </div>
            </div>

            @if($showFilterChips)
                <div class="map-modal__filter-panel" data-map-filter-panel hidden>
                    <div class="map-modal__filter-panel-inner">
                        <div class="map-modal__filter-panel-header">
                            <strong data-map-filter-panel-title>{{ __('destination.filter_by') }}</strong>
                            <button type="button" class="map-modal__filter-panel-close" data-map-filter-panel-close aria-label="{{ __('destination.close_map') }}">&times;</button>
                        </div>
                        <div class="map-modal__filter-panel-body" data-map-filter-panel-body></div>
                        <div class="map-modal__filter-panel-footer">
                            <button type="button" class="map-modal__filter-clear" data-map-filter-clear>{{ __('destination.map_clear_filters') }}</button>
                            <button type="button" class="map-modal__filter-apply" data-map-filter-apply>{{ __('destination.map_apply_filters') }}</button>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>
