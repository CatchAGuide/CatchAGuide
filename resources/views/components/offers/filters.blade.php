@props([
    'filter',
    'toursTotal' => 0,
    'tripsTotal' => 0,
    'campsTotal' => 0,
    'speciesOptions' => collect(),
    'countries' => collect(),
    'methodOptions' => collect(),
    'waterOptions' => collect(),
    'tourDurationOptions' => collect(),
    'tripDurationOptions' => collect(),
    'accommodationTypeOptions' => collect(),
    'showTypeToggles' => true,
    'showMobileToolbar' => true,
    'showMapButton' => false,
    'showDesktop' => true,
    'action' => null,
    'typeLinks' => null,
    'vacationLinks' => null,
    'renderSection' => 'all',
    'mapModalId' => 'offersCatalogMapModal',
    'lockedParams' => [],
])

@php
    use App\Domain\Offers\OfferListingFilter;
    use Illuminate\Support\Collection;

    $action = $action ?? route('offers.index');
    $total = ($toursTotal ?? 0) + ($tripsTotal ?? 0) + ($campsTotal ?? 0);
    $vacationsTotal = ($tripsTotal ?? 0) + ($campsTotal ?? 0);
    $facetKeys = OfferListingFilter::PRODUCT_FACET_KEYS;
    $lockedParams = is_array($lockedParams ?? null) ? $lockedParams : [];
    $lockedKeys = array_keys($lockedParams);
    $managedKeys = array_merge(['species', 'country', 'sortby', 'type', 'vacation', 'num_guests'], $facetKeys, $lockedKeys);
    $query = request()->except(['page', 'type', 'vacation']);
    $speciesOptions = $speciesOptions instanceof Collection ? $speciesOptions : collect($speciesOptions ?? []);
    $countries = $countries instanceof Collection ? $countries : collect($countries ?? []);
    $methodOptions = $methodOptions instanceof Collection ? $methodOptions : collect($methodOptions ?? []);
    $waterOptions = $waterOptions instanceof Collection ? $waterOptions : collect($waterOptions ?? []);
    $tourDurationOptions = $tourDurationOptions instanceof Collection ? $tourDurationOptions : collect($tourDurationOptions ?? []);
    $tripDurationOptions = $tripDurationOptions instanceof Collection ? $tripDurationOptions : collect($tripDurationOptions ?? []);
    $accommodationTypeOptions = $accommodationTypeOptions instanceof Collection ? $accommodationTypeOptions : collect($accommodationTypeOptions ?? []);
    $activeType = $filter->type ?? 'all';
    $activeVacation = $filter->vacation ?? 'all';
    $isVacation = $activeType === 'vacation';
    $showTourFacets = $filter->showsTourFacets();
    $showCampFacets = $filter->showsCampFacets();
    $showTripFacets = $filter->showsTripFacets();
    $activeFilterCount = collect(array_merge(['species', 'country', 'sortby', 'type', 'vacation', 'num_guests'], $facetKeys))
        ->filter(fn ($key) => filled(request()->get($key)))
        ->count();
    $sidebarFilterKeys = array_merge(['species', 'country', 'sortby', 'vacation'], $facetKeys);
    $hasSidebarFilters = collect($sidebarFilterKeys)
        ->filter(fn ($key) => filled(request()->get($key)) && ! in_array($key, $lockedKeys, true))
        ->isNotEmpty();
    $clearFiltersUrl = (function () use ($action, $query, $lockedParams, $activeType, $sidebarFilterKeys) {
        $params = collect($query)->except($sidebarFilterKeys)->all();
        foreach ($lockedParams as $key => $value) {
            $params[$key] = $value;
        }
        if ($activeType !== 'all') {
            $params['type'] = $activeType;
        }

        return $action.($params ? '?'.http_build_query($params) : '');
    })();
    $typeUrl = function (string $type) use ($action, $query, $typeLinks, $facetKeys) {
        if (is_array($typeLinks) && isset($typeLinks[$type])) {
            return $typeLinks[$type];
        }

        $params = collect($query)->except($facetKeys)->all();
        if ($type !== 'all') {
            $params['type'] = $type;
        }

        return $action.($params ? '?'.http_build_query($params) : '');
    };
    $vacationUrl = function (string $vacation) use ($action, $query, $vacationLinks, $facetKeys) {
        if (is_array($vacationLinks) && isset($vacationLinks[$vacation])) {
            return $vacationLinks[$vacation];
        }

        $params = collect($query)->except($facetKeys)->all();
        $params['type'] = 'vacation';
        if ($vacation !== 'all') {
            $params['vacation'] = $vacation;
        }

        return $action.'?'.http_build_query($params);
    };
    $showToolbar = $showTypeToggles && in_array($renderSection, ['all', 'toolbar'], true);
    $showSidebar = in_array($renderSection, ['all', 'sidebar'], true);
    $showMobile = $showMobileToolbar && in_array($renderSection, ['all', 'mobile'], true);
    $showOffcanvas = $showMobileToolbar && in_array($renderSection, ['all', 'offcanvas'], true);
    $currentSort = $filter->sortBy ?? '';
@endphp

@if($showToolbar)
    <div class="offers-filters__type-stack offers-filters__type-stack--toolbar" data-offers-type-filter>
        @include('components.offers.partials.type-pillars', [
            'groupClass' => 'offers-filters__type-group offers-filters__type-group--toolbar vacation-filters__pillar-group',
            'typeUrl' => $typeUrl,
            'vacationUrl' => $vacationUrl,
            'activeType' => $activeType,
            'activeVacation' => $activeVacation,
            'isVacation' => $isVacation,
            'total' => $total,
            'toursTotal' => $toursTotal,
            'tripsTotal' => $tripsTotal,
            'campsTotal' => $campsTotal,
            'vacationsTotal' => $vacationsTotal,
        ])
    </div>
@endif

@if($showSidebar)
<form method="get" action="{{ $action }}" class="offers-filters vacation-filters vacation-filters--sidebar" id="offers-filters-form">
    @foreach($query as $key => $value)
        @if(in_array($key, $managedKeys, true))
            @continue
        @endif
        @if(is_array($value))
            @foreach($value as $v)
                <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
            @endforeach
        @else
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endif
    @endforeach

    @foreach($lockedParams as $key => $value)
        @if(is_array($value))
            @foreach($value as $v)
                <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
            @endforeach
        @else
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endif
    @endforeach

    @if($activeType !== 'all')
        <input type="hidden" name="type" value="{{ $activeType }}">
    @endif
    @if($isVacation && $activeVacation !== 'all')
        <input type="hidden" name="vacation" value="{{ $activeVacation }}">
    @endif

    @if($showTypeToggles && $renderSection === 'all')
        <div class="offers-filters__type-stack" data-offers-type-filter>
            @include('components.offers.partials.type-pillars', [
                'groupClass' => 'offers-filters__type-group vacation-filters__pillar-group',
                'typeUrl' => $typeUrl,
                'vacationUrl' => $vacationUrl,
                'activeType' => $activeType,
                'activeVacation' => $activeVacation,
                'isVacation' => $isVacation,
                'total' => $total,
                'toursTotal' => $toursTotal,
                'tripsTotal' => $tripsTotal,
                'campsTotal' => $campsTotal,
                'vacationsTotal' => $vacationsTotal,
            ])
        </div>
    @endif

    @if($showDesktop)
    <div class="vacation-filters__desktop">
        <div class="vacation-filters__sidebar-stack">
            @include('components.offers.partials.filter-fields', [
                'selectClass' => 'form-select form-select-sm',
                'fieldClass' => 'vacation-filters__field',
                'speciesInputPrefix' => 'offers-species-sidebar',
                'filter' => $filter,
                'countries' => $countries,
                'speciesOptions' => $speciesOptions,
                'methodOptions' => $methodOptions,
                'waterOptions' => $waterOptions,
                'tourDurationOptions' => $tourDurationOptions,
                'tripDurationOptions' => $tripDurationOptions,
                'accommodationTypeOptions' => $accommodationTypeOptions,
                'showTourFacets' => $showTourFacets,
                'showCampFacets' => $showCampFacets,
                'showTripFacets' => $showTripFacets,
                'vacationUrl' => $vacationUrl,
            ])

            <div class="vacation-filters__actions mt-2">
                <button type="submit" class="btn btn-sm btn-primary w-100">{{ __('offers.apply_filters') }}</button>
                @if($hasSidebarFilters)
                    <a href="{{ $clearFiltersUrl }}" class="btn btn-sm btn-outline-secondary w-100 mt-2" data-offers-clear-filters>
                        {{ __('offers.clear_filters') }}
                    </a>
                @endif
            </div>
        </div>
    </div>
    @endif
</form>
@include('components.offers.partials.species-tagify-script')
@endif

@if($showMobile)
    <div class="sfm-bar vacation-filters__sfm-bar offers-filters__sfm-bar">
        <div class="sfm-bar__item">
            <div class="dropdown w-100">
                <button type="button"
                        class="sfm-bar__btn dropdown-toggle w-100"
                        data-bs-toggle="dropdown"
                        data-bs-auto-close="true"
                        aria-expanded="false">
                    <span class="sfm-bar__icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM5 10a1 1 0 011-1h8a1 1 0 110 2H6a1 1 0 01-1-1zM7 15a1 1 0 011-1h4a1 1 0 110 2H8a1 1 0 01-1-1z"/>
                        </svg>
                    </span>
                    <span class="sfm-bar__label">@lang('message.sortby')</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-start sfm-bar__dropdown">
                    <li>
                        <a class="dropdown-item offers-mobile-sort-option {{ ($currentSort === '' || $currentSort === 'recommended') ? 'active' : '' }}"
                           href="javascript:void(0)"
                           data-sort="recommended">{{ __('offers.sort_recommended') }}</a>
                    </li>
                    <li>
                        <a class="dropdown-item offers-mobile-sort-option {{ $currentSort === 'newest' ? 'active' : '' }}"
                           href="javascript:void(0)"
                           data-sort="newest">{{ __('offers.sort_newest') }}</a>
                    </li>
                    <li>
                        <a class="dropdown-item offers-mobile-sort-option {{ $currentSort === 'nearest' ? 'active' : '' }}"
                           href="javascript:void(0)"
                           data-sort="nearest">{{ __('offers.sort_nearest') }}</a>
                    </li>
                    <li>
                        <a class="dropdown-item offers-mobile-sort-option {{ $currentSort === 'price-asc' ? 'active' : '' }}"
                           href="javascript:void(0)"
                           data-sort="price-asc">{{ __('offers.sort_price_asc') }}</a>
                    </li>
                    <li>
                        <a class="dropdown-item offers-mobile-sort-option {{ $currentSort === 'price-desc' ? 'active' : '' }}"
                           href="javascript:void(0)"
                           data-sort="price-desc">{{ __('offers.sort_price_desc') }}</a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="sfm-bar__divider"></div>

        <div class="sfm-bar__item">
            <button type="button"
                    class="sfm-bar__btn"
                    id="offersSfmFilterBtn"
                    data-bs-toggle="offcanvas"
                    data-bs-target="#offersFiltersOffcanvas"
                    aria-controls="offersFiltersOffcanvas">
                <span class="sfm-bar__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L13 10.414V17a1 1 0 01-1.447.894l-4-2A1 1 0 017 15v-4.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd"/>
                    </svg>
                </span>
                <span class="sfm-bar__label">@lang('message.filter')</span>
                <span class="sfm-bar__badge">{{ $activeFilterCount > 0 ? $activeFilterCount : '' }}</span>
            </button>
        </div>

        @if($showMapButton)
            <div class="sfm-bar__divider"></div>
            <div class="sfm-bar__item">
                <button type="button"
                        class="sfm-bar__btn sfm-bar__btn--map"
                        data-bs-toggle="modal"
                        data-bs-target="#{{ $mapModalId }}">
                    <span class="sfm-bar__icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                        </svg>
                    </span>
                    <span class="sfm-bar__label">@lang('offers.show_on_map')</span>
                </button>
            </div>
        @endif
    </div>

    @once
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            function navigateWithSort(sortValue, coords) {
                const urlParams = new URLSearchParams(window.location.search);
                if (sortValue) {
                    urlParams.set('sortby', sortValue);
                } else {
                    urlParams.delete('sortby');
                }
                if (coords) {
                    urlParams.set('user_lat', String(coords.lat));
                    urlParams.set('user_lng', String(coords.lng));
                }
                if (sortValue !== 'nearest') {
                    urlParams.delete('user_lat');
                    urlParams.delete('user_lng');
                }
                const query = urlParams.toString();
                window.location.href = query
                    ? `${window.location.pathname}?${query}`
                    : window.location.pathname;
            }

            function applySort(sortValue) {
                if (sortValue !== 'nearest') {
                    navigateWithSort(sortValue);
                    return;
                }

                const existingLat = new URLSearchParams(window.location.search).get('user_lat')
                    || new URLSearchParams(window.location.search).get('placeLat');
                const existingLng = new URLSearchParams(window.location.search).get('user_lng')
                    || new URLSearchParams(window.location.search).get('placeLng');
                if (existingLat && existingLng) {
                    navigateWithSort(sortValue, { lat: existingLat, lng: existingLng });
                    return;
                }

                if (!navigator.geolocation) {
                    navigateWithSort(sortValue);
                    return;
                }

                navigator.geolocation.getCurrentPosition(
                    function (position) {
                        navigateWithSort(sortValue, {
                            lat: position.coords.latitude,
                            lng: position.coords.longitude,
                        });
                    },
                    function () {
                        navigateWithSort(sortValue);
                    },
                    { enableHighAccuracy: false, timeout: 8000, maximumAge: 300000 }
                );
            }

            document.querySelectorAll('.offers-mobile-sort-option').forEach(function (option) {
                option.addEventListener('click', function (event) {
                    event.preventDefault();
                    applySort(this.dataset.sort || 'recommended');
                });
            });

            document.querySelectorAll('[data-offers-sort-select]').forEach(function (select) {
                select.addEventListener('change', function () {
                    applySort(this.value || 'recommended');
                });
            });
        });
        </script>
    @endonce
@endif

@if($showOffcanvas)
    <div class="offcanvas offcanvas-bottom vacation-filters-offcanvas" tabindex="-1" id="offersFiltersOffcanvas" aria-labelledby="offersFiltersOffcanvasLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offersFiltersOffcanvasLabel">{{ __('offers.filter_mobile') }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <form method="get" action="{{ $action }}" class="vacation-filters-offcanvas__form">
                @foreach($query as $key => $value)
                    @if(in_array($key, $managedKeys, true))
                        @continue
                    @endif
                    @if(is_array($value))
                        @foreach($value as $v)
                            <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
                        @endforeach
                    @else
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endif
                @endforeach

                @foreach($lockedParams as $key => $value)
                    @if(is_array($value))
                        @foreach($value as $v)
                            <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
                        @endforeach
                    @else
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endif
                @endforeach

                @if($activeType !== 'all')
                    <input type="hidden" name="type" value="{{ $activeType }}">
                @endif
                @if($isVacation && $activeVacation !== 'all')
                    <input type="hidden" name="vacation" value="{{ $activeVacation }}">
                @endif

                @if($showTypeToggles)
                    <div class="mb-3">
                        <label class="form-label">{{ __('offers.filter_type') }}</label>
                        <div class="offers-filters__type-stack" data-offers-type-filter>
                            @include('components.offers.partials.type-pillars', [
                                'groupClass' => 'offers-filters__type-group vacation-filters__pillar-group vacation-filters__pillar-group--mobile',
                                'typeUrl' => $typeUrl,
                                'vacationUrl' => $vacationUrl,
                                'activeType' => $activeType,
                                'activeVacation' => $activeVacation,
                                'isVacation' => $isVacation,
                                'total' => $total,
                                'toursTotal' => $toursTotal,
                                'tripsTotal' => $tripsTotal,
                                'campsTotal' => $campsTotal,
                                'vacationsTotal' => $vacationsTotal,
                            ])
                        </div>
                    </div>
                @endif

                @include('components.offers.partials.filter-fields', [
                    'selectClass' => 'form-select',
                    'fieldClass' => 'mb-3',
                    'speciesInputPrefix' => 'offers-species-offcanvas',
                    'filter' => $filter,
                    'countries' => $countries,
                    'speciesOptions' => $speciesOptions,
                    'methodOptions' => $methodOptions,
                    'waterOptions' => $waterOptions,
                    'tourDurationOptions' => $tourDurationOptions,
                    'tripDurationOptions' => $tripDurationOptions,
                    'accommodationTypeOptions' => $accommodationTypeOptions,
                    'showTourFacets' => $showTourFacets,
                    'showCampFacets' => $showCampFacets,
                    'showTripFacets' => $showTripFacets,
                    'vacationUrl' => $vacationUrl,
                ])

                <div class="mb-3">
                    <label class="form-label">{{ __('offers.filter_sort') }}</label>
                    <select name="sortby" class="form-select" data-offers-sort-select>
                        @include('components.offers.partials.sort-options', ['filter' => $filter, 'currentSort' => $currentSort])
                    </select>
                </div>

                <button type="submit" class="btn btn-orange w-100">{{ __('offers.apply_filters') }}</button>
                @if($hasSidebarFilters)
                    <a href="{{ $clearFiltersUrl }}" class="btn btn-outline-secondary w-100 mt-2" data-offers-clear-filters>
                        {{ __('offers.clear_filters') }}
                    </a>
                @endif
            </form>
            @include('components.offers.partials.species-tagify-script')
        </div>
    </div>
@endif
