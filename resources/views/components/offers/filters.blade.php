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
    'regionRedirectOptions' => null,
    'regionRedirectCurrent' => null,
    'regionRedirectAllUrl' => null,
    'speciesRedirectOptions' => null,
    'speciesRedirectCurrent' => null,
    'speciesRedirectAllUrl' => null,
    'methodRedirectOptions' => null,
    'methodRedirectCurrent' => null,
    'methodRedirectAllUrl' => null,
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
    $regionRedirectOptions = $regionRedirectOptions instanceof Collection ? $regionRedirectOptions : collect($regionRedirectOptions ?? []);
    $speciesRedirectOptions = $speciesRedirectOptions instanceof Collection ? $speciesRedirectOptions : collect($speciesRedirectOptions ?? []);
    $methodRedirectOptions = $methodRedirectOptions instanceof Collection ? $methodRedirectOptions : collect($methodRedirectOptions ?? []);
    // Species/methods pages keep the normal multi-select widget (unlike the single-value
    // country redirect); the "switch to exactly one item" decision happens client-side by
    // counting the final species[]/methods[] selection at submit time, so here we only need
    // an id -> url lookup (covering the current page's own item too) for that one dimension.
    $offersPrimaryDimension = $speciesRedirectOptions->isNotEmpty() ? 'species' : ($methodRedirectOptions->isNotEmpty() ? 'methods' : null);
    $offersPrimaryOptions = match ($offersPrimaryDimension) {
        'species' => $speciesRedirectOptions,
        'methods' => $methodRedirectOptions,
        default => collect(),
    };
    $offersPrimaryMap = $offersPrimaryOptions->mapWithKeys(fn ($row) => [(string) $row['id'] => $row['url']]);
    $offersPrimaryAllUrl = match ($offersPrimaryDimension) {
        'species' => $speciesRedirectAllUrl ?? null,
        'methods' => $methodRedirectAllUrl ?? null,
        default => null,
    };
    // The multi-select widget already renders its own live species[]/methods[] hidden inputs;
    // excluding them here (plus 'type', which the explicit $activeType block below also renders)
    // avoids emitting the same hidden field twice on locked pages.
    $lockedParamsForHiddenFields = collect($lockedParams)->except(['type', 'species', 'methods'])->all();
    $activeType = $filter->type ?? 'all';
    $activeVacation = $filter->vacation ?? 'all';
    $isVacation = $activeType === 'vacation';
    $showTourFacets = $filter->showsTourFacets();
    $showCampFacets = $filter->showsCampFacets();
    $showTripFacets = $filter->showsTripFacets();
    // The country field is auto-filled and locked whenever the header place
    // search is active (see filter-fields.blade.php's $regionLockedByPlace),
    // so it must not count as a user-chosen filter in that case — otherwise
    // every place search would show a "clear filters" control that clears
    // nothing visible.
    $countryLockedByPlace = $filter->hasPlaceSearch();
    $activeFilterCount = collect(array_merge(['species', 'country', 'sortby', 'type', 'vacation', 'num_guests'], $facetKeys))
        ->reject(fn ($key) => $key === 'country' && $countryLockedByPlace)
        ->filter(fn ($key) => filled(request()->get($key)))
        ->count();
    $sidebarFilterKeys = array_merge(['species', 'country', 'sortby', 'vacation'], $facetKeys);
    $hasSidebarFilters = collect($sidebarFilterKeys)
        ->reject(fn ($key) => $key === 'country' && $countryLockedByPlace)
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

    @foreach($lockedParamsForHiddenFields as $key => $value)
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
    @if($filter->numGuests !== null)
        <input type="hidden" name="num_guests" value="{{ $filter->numGuests }}">
    @endif
    @if($offersPrimaryDimension)
        <script type="application/json" data-offers-primary-redirect-config>
            @json(['dimension' => $offersPrimaryDimension, 'map' => $offersPrimaryMap, 'allUrl' => $offersPrimaryAllUrl])
        </script>
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
                'methodsInputPrefix' => 'offers-methods-sidebar',
                'waterInputPrefix' => 'offers-water-sidebar',
                'durationInputPrefix' => 'offers-duration-sidebar',
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
                'regionRedirectOptions' => $regionRedirectOptions,
                'regionRedirectCurrent' => $regionRedirectCurrent,
                'regionRedirectAllUrl' => $regionRedirectAllUrl,
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
@include('components.offers.partials.multi-select-script')
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
            const locationToastStorageKey = 'offersNearestLocationToast';
            const locationMessages = {
                requesting: @json(__('offers.location_requesting')),
                denied: @json(__('offers.location_denied')),
                unavailable: @json(__('offers.location_unavailable')),
                unsupported: @json(__('offers.location_unsupported')),
                fallbackRecommended: @json(__('offers.location_fallback_recommended')),
            };

            function notifyLocation(type, message) {
                if (typeof toastr === 'undefined' || !message) {
                    return;
                }
                toastr.options = {
                    closeButton: true,
                    progressBar: true,
                    timeOut: type === 'info' ? 4000 : 8000,
                    extendedTimeOut: 2000,
                    positionClass: 'toast-top-right',
                };
                if (typeof toastr[type] === 'function') {
                    toastr[type](message);
                }
            }

            function queueLocationToast(type, message) {
                try {
                    sessionStorage.setItem(locationToastStorageKey, JSON.stringify({
                        type: type,
                        message: message,
                    }));
                } catch (e) {
                    // Ignore storage failures; in-page toasts still work when we do not navigate.
                }
            }

            function flushQueuedLocationToast() {
                try {
                    const raw = sessionStorage.getItem(locationToastStorageKey);
                    if (!raw) {
                        return;
                    }
                    sessionStorage.removeItem(locationToastStorageKey);
                    const payload = JSON.parse(raw);
                    notifyLocation(payload.type, payload.message);
                } catch (e) {
                    // Ignore invalid payloads.
                }
            }

            flushQueuedLocationToast();

            function existingNearestCoords() {
                const params = new URLSearchParams(window.location.search);
                const lat = params.get('user_lat') || params.get('placeLat');
                const lng = params.get('user_lng') || params.get('placeLng');
                if (!lat || !lng) {
                    return null;
                }
                return { lat: lat, lng: lng };
            }

            function currentSortValue() {
                const params = new URLSearchParams(window.location.search);
                return params.get('sortby') || 'recommended';
            }

            function restoreSortControls() {
                const previous = currentSortValue() === 'nearest' && !existingNearestCoords()
                    ? 'recommended'
                    : currentSortValue();
                document.querySelectorAll('[data-offers-sort-select]').forEach(function (select) {
                    select.value = previous;
                });
            }

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

            function locationErrorMessage(error) {
                if (!error || typeof error.code === 'undefined') {
                    return locationMessages.unavailable;
                }
                // 1 = PERMISSION_DENIED, 2 = POSITION_UNAVAILABLE, 3 = TIMEOUT
                if (error.code === 1) {
                    return locationMessages.denied;
                }
                return locationMessages.unavailable;
            }

            function handleNearestLocationFailure(message, fallbackCoords) {
                if (fallbackCoords) {
                    queueLocationToast('warning', message);
                    navigateWithSort('nearest', fallbackCoords);
                    return;
                }

                notifyLocation('warning', message);
                notifyLocation('info', locationMessages.fallbackRecommended);
                restoreSortControls();
            }

            function applyNearestSort() {
                const fallbackCoords = existingNearestCoords();

                if (!navigator.geolocation) {
                    handleNearestLocationFailure(locationMessages.unsupported, fallbackCoords);
                    return;
                }

                notifyLocation('info', locationMessages.requesting);

                navigator.geolocation.getCurrentPosition(
                    function (position) {
                        navigateWithSort('nearest', {
                            lat: position.coords.latitude,
                            lng: position.coords.longitude,
                        });
                    },
                    function (error) {
                        handleNearestLocationFailure(locationErrorMessage(error), fallbackCoords);
                    },
                    {
                        enableHighAccuracy: false,
                        timeout: 8000,
                        // Short cache: re-selecting nearest still rechecks, but can reuse a recent fix.
                        maximumAge: 60000,
                    }
                );
            }

            function applySort(sortValue) {
                if (sortValue === 'nearest') {
                    applyNearestSort();
                    return;
                }
                navigateWithSort(sortValue);
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

                @foreach($lockedParamsForHiddenFields as $key => $value)
                    @if(is_array($value))
                        @foreach($value as $v)
                            <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
                        @endforeach
                    @else
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endif
                @endforeach

                <input type="hidden" name="type" id="offersOffcanvasTypeInput" value="{{ $activeType !== 'all' ? $activeType : '' }}">
                <input type="hidden" name="vacation" id="offersOffcanvasVacationInput" value="{{ $isVacation && $activeVacation !== 'all' ? $activeVacation : '' }}">
                <input type="hidden" name="sortby" value="{{ $currentSort }}">
                @if($filter->numGuests !== null)
                    <input type="hidden" name="num_guests" value="{{ $filter->numGuests }}">
                @endif
                @if($offersPrimaryDimension)
                    <script type="application/json" data-offers-primary-redirect-config>
                        @json(['dimension' => $offersPrimaryDimension, 'map' => $offersPrimaryMap, 'allUrl' => $offersPrimaryAllUrl])
                    </script>
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
                                'interactive' => true,
                            ])
                        </div>
                    </div>
                @endif

                @include('components.offers.partials.filter-fields', [
                    'selectClass' => 'form-select',
                    'fieldClass' => 'mb-3',
                    'speciesInputPrefix' => 'offers-species-offcanvas',
                    'methodsInputPrefix' => 'offers-methods-offcanvas',
                    'waterInputPrefix' => 'offers-water-offcanvas',
                    'durationInputPrefix' => 'offers-duration-offcanvas',
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
                    'interactive' => true,
                    'regionRedirectOptions' => $regionRedirectOptions,
                    'regionRedirectCurrent' => $regionRedirectCurrent,
                    'regionRedirectAllUrl' => $regionRedirectAllUrl,
                ])

                <button type="submit" class="btn btn-orange w-100">{{ __('offers.apply_filters') }}</button>
                @if($hasSidebarFilters)
                    <a href="{{ $clearFiltersUrl }}" class="btn btn-outline-secondary w-100 mt-2" data-offers-clear-filters>
                        {{ __('offers.clear_filters') }}
                    </a>
                @endif
            </form>
            @include('components.offers.partials.multi-select-script')
        </div>
    </div>
@endif

@once
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var offersFilterBtn = document.getElementById('offersSfmFilterBtn');
        if (offersFilterBtn) {
            offersFilterBtn.addEventListener('click', function (event) {
                event.stopPropagation();
            });
        }

        var offersRegionRedirectSelector = '[data-offers-region-redirect]';

        document.querySelectorAll(offersRegionRedirectSelector).forEach(function (select) {
            select.dataset.initialValue = select.value;
        });

        var offersIndexUrl = @json(route('offers.index'));

        // Fields that count as "another filter is active" once the country redirect select
        // changes, or once a species/methods-locked page's multi-select ends up with a single
        // value. Each dimension excludes its own field group from the check — e.g. the
        // currently locked country/city/region isn't "another" filter when the country select
        // itself is what changed. num_guests always carries a default value (see
        // OfferListingFilter::DEFAULT_GUESTS), so it only counts once it differs from that.
        var offersRedirectAlwaysFields = ['water[]', 'duration_types[]', 'duration', 'accommodation_type', 'has_guiding', 'has_rental_boat', 'sortby', 'num_guests'];
        var offersRedirectCountryFields = ['country', 'country_short', 'city', 'region', 'place', 'placeLat', 'placeLng', 'bounds_ne_lat', 'bounds_ne_lng', 'bounds_sw_lat', 'bounds_sw_lng', 'place_types'];
        var offersRedirectSpeciesFields = ['species[]'];
        var offersRedirectMethodFields = ['methods[]'];
        var offersRedirectDefaults = { num_guests: @json((string) \App\Domain\Offers\OfferListingFilter::DEFAULT_GUESTS) };

        function offersRedirectFieldHasValue(el) {
            if (!el) {
                return false;
            }
            if (el.type === 'checkbox' || el.type === 'radio') {
                return el.checked;
            }
            var value = (el.value || '').trim();
            if (value === '') {
                return false;
            }
            var defaultValue = offersRedirectDefaults[el.name];

            return defaultValue === undefined || value !== defaultValue;
        }

        function offersRedirectOtherFiltersActive(form, dimension) {
            var tracked = offersRedirectAlwaysFields.slice();
            if (dimension !== 'country') {
                tracked = tracked.concat(offersRedirectCountryFields);
            }
            if (dimension !== 'species') {
                tracked = tracked.concat(offersRedirectSpeciesFields);
            }
            if (dimension !== 'methods') {
                tracked = tracked.concat(offersRedirectMethodFields);
            }

            var elements = form.querySelectorAll('input[name], select[name]');
            for (var i = 0; i < elements.length; i++) {
                if (tracked.indexOf(elements[i].name) !== -1 && offersRedirectFieldHasValue(elements[i])) {
                    return true;
                }
            }

            return false;
        }

        function offersRedirectSetField(form, name, value) {
            form.querySelectorAll('input[name="' + name + '"]').forEach(function (el) {
                el.remove();
            });
            if (!value) {
                return;
            }
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = value;
            form.appendChild(input);
        }

        function offersRedirectApplyCountry(form, value) {
            offersRedirectCountryFields.forEach(function (name) {
                offersRedirectSetField(form, name, null);
            });
            offersRedirectSetField(form, 'country', value);
        }

        function offersPrimaryRedirectConfig(form) {
            var node = form.querySelector('[data-offers-primary-redirect-config]');
            if (!node) {
                return null;
            }
            try {
                return JSON.parse(node.textContent || '{}');
            } catch (e) {
                return null;
            }
        }

        function offersPrimaryFieldValues(form, dimension) {
            var fieldName = dimension === 'species' ? 'species[]' : 'methods[]';
            var values = [];
            form.querySelectorAll('input[name="' + fieldName + '"]').forEach(function (el) {
                if (el.value) {
                    values.push(el.value);
                }
            });
            return values.sort();
        }

        // Snapshot the initial species[]/methods[] selection so "did the primary facet actually
        // change" can be judged the same way the country select's dataset.initialValue is: a page
        // whose current item can't be pre-checked in the multi-select (e.g. its catalog entry is
        // missing — see the "wels" bug) would otherwise always read as "0 selected", wrongly
        // treated as a real, deliberate change on every submit.
        document.querySelectorAll('#offers-filters-form, .vacation-filters-offcanvas__form').forEach(function (form) {
            var initialConfig = offersPrimaryRedirectConfig(form);
            if (initialConfig && initialConfig.dimension) {
                form.dataset.offersPrimaryInitialValues = JSON.stringify(offersPrimaryFieldValues(form, initialConfig.dimension));
            }
        });

        document.querySelectorAll('#offers-filters-form, .vacation-filters-offcanvas__form').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                // Country/region: re-evaluated on every submit, not just when the select itself
                // changes — adding another filter (e.g. methods) while staying on the same
                // country must also fall through to /offers, exactly like species/methods below.
                var regionSelect = form.querySelector(offersRegionRedirectSelector);
                if (regionSelect) {
                    var countryChanged = !!regionSelect.value && regionSelect.value !== regionSelect.dataset.initialValue;
                    var countryOtherActive = offersRedirectOtherFiltersActive(form, 'country');

                    if (countryChanged || countryOtherActive) {
                        event.preventDefault();

                        if (countryChanged && !countryOtherActive) {
                            window.location.href = regionSelect.value;
                            return;
                        }

                        if (countryChanged) {
                            var chosenOption = regionSelect.selectedOptions[0];
                            offersRedirectApplyCountry(form, chosenOption ? chosenOption.dataset.value : '');
                        }
                        form.action = offersIndexUrl;
                        form.submit();
                        return;
                    }
                }

                // Species/methods: the normal multi-select stays in place; the decision is based
                // on how many values it ends up with at submit time (exactly one -> jump to that
                // item's own page when nothing else is active; otherwise -> /offers with everything).
                var config = offersPrimaryRedirectConfig(form);
                if (!config || !config.dimension) {
                    return;
                }

                var values = offersPrimaryFieldValues(form, config.dimension);
                var initialValues = form.dataset.offersPrimaryInitialValues
                    ? JSON.parse(form.dataset.offersPrimaryInitialValues)
                    : [];
                var primaryChanged = JSON.stringify(values) !== JSON.stringify(initialValues);
                var otherActive = offersRedirectOtherFiltersActive(form, config.dimension);
                var map = config.map || {};

                if (!primaryChanged && !otherActive) {
                    return;
                }

                event.preventDefault();

                if (!otherActive && values.length === 1 && map[values[0]]) {
                    window.location.href = map[values[0]];
                    return;
                }

                if (!otherActive && values.length === 0) {
                    window.location.href = config.allUrl || offersIndexUrl;
                    return;
                }

                form.action = offersIndexUrl;
                form.submit();
            });
        });

        (function () {
            var offcanvas = document.getElementById('offersFiltersOffcanvas');
            var pillarGroup = offcanvas ? offcanvas.querySelector('[data-offers-pillar-group]') : null;
            if (!offcanvas || !pillarGroup) {
                return;
            }

            var typeInput = document.getElementById('offersOffcanvasTypeInput');
            var vacationInput = document.getElementById('offersOffcanvasVacationInput');
            var extendGroup = pillarGroup.querySelector('[data-offers-vacation-subfilter]');
            var vacationTypeBlock = offcanvas.querySelector('[data-offers-vacation-type]');
            var typeButtons = pillarGroup.querySelectorAll('[data-pillar-type]');
            var vacationButtons = offcanvas.querySelectorAll('[data-pillar-vacation]');
            var facetSections = offcanvas.querySelectorAll('[data-offers-facet-section]');

            function setPillarState(type, vacation) {
                if (typeInput) {
                    typeInput.value = type === 'all' ? '' : type;
                }
                if (vacationInput) {
                    vacationInput.value = (type === 'vacation' && vacation !== 'all') ? vacation : '';
                }

                typeButtons.forEach(function (btn) {
                    var btnType = btn.dataset.pillarType;
                    var isVacationBtn = btnType === 'vacation';
                    var isActive = btnType === type && !(isVacationBtn && vacation !== 'all');
                    btn.classList.toggle('is-active', isActive);
                    if (isVacationBtn) {
                        btn.classList.toggle('is-vacation-context', type === 'vacation');
                    }
                });

                vacationButtons.forEach(function (btn) {
                    var isActive = btn.dataset.pillarVacation === vacation;
                    btn.classList.toggle('is-active', isActive);
                    if (btn.hasAttribute('aria-pressed')) {
                        btn.setAttribute('aria-pressed', isActive ? 'true' : 'false');
                    }
                });

                if (extendGroup) {
                    extendGroup.classList.toggle('d-none', type !== 'vacation');
                }
                if (vacationTypeBlock) {
                    vacationTypeBlock.classList.toggle('d-none', type !== 'vacation');
                }

                facetSections.forEach(function (section) {
                    var facetType = section.dataset.offersFacetSection;
                    var matches = (facetType === 'tour' && type === 'tour')
                        || (facetType === 'camp' && type === 'vacation' && vacation === 'camp')
                        || (facetType === 'trip' && type === 'vacation' && vacation === 'trip');
                    section.classList.toggle('d-none', !matches);
                });
            }

            typeButtons.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    setPillarState(btn.dataset.pillarType, 'all');
                });
            });

            vacationButtons.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    setPillarState('vacation', btn.dataset.pillarVacation);
                });
            });
        })();

        const regionLockMessage = @json(__('offers.filter_country_locked_by_place'));
        const placeFormSelectors = '[data-offers-header-search], [data-category-header-search]';
        const locationCarrySelectors = [
            placeFormSelectors,
            '#offers-filters-form',
            '.vacation-filters-offcanvas__form',
            '[data-offers-sort-form]',
        ].join(', ');

        function listingPlaceHasText() {
            const forms = document.querySelectorAll(placeFormSelectors);
            for (let i = 0; i < forms.length; i++) {
                const place = forms[i].querySelector('input[name="place"]');
                if (place && String(place.value || '').trim() !== '') {
                    return true;
                }
            }
            return false;
        }

        function clearCarriedLocationFields() {
            if (typeof window.clearListingLocationFields !== 'function') {
                return;
            }
            document.querySelectorAll(locationCarrySelectors).forEach(function (form) {
                window.clearListingLocationFields(form);
            });
            document.querySelectorAll('[data-offers-region-select]').forEach(function (select) {
                select.value = '';
            });
        }

        function disposeTooltip(el) {
            if (!el || typeof bootstrap === 'undefined' || !bootstrap.Tooltip) {
                return;
            }
            const instance = bootstrap.Tooltip.getInstance(el);
            if (instance) {
                instance.dispose();
            }
        }

        function initTooltip(el, container) {
            if (!el || typeof bootstrap === 'undefined' || !bootstrap.Tooltip) {
                return;
            }
            disposeTooltip(el);
            new bootstrap.Tooltip(el, {
                trigger: 'hover focus click',
                container: container || 'body',
                placement: 'top',
            });
        }

        function tooltipContainerFor(el) {
            return el.closest('.offcanvas') || 'body';
        }

        function setRegionLocked(field, locked) {
            const select = field.querySelector('[data-offers-region-select]');
            const control = field.querySelector('[data-offers-region-control]');
            const tip = field.querySelector('[data-offers-region-tip]');
            if (!select) {
                return;
            }

            field.classList.toggle('is-locked', locked);
            select.disabled = locked;
            select.setAttribute('aria-disabled', locked ? 'true' : 'false');

            if (locked) {
                if (control) {
                    control.setAttribute('data-bs-toggle', 'tooltip');
                    control.setAttribute('data-bs-placement', 'top');
                    control.setAttribute('data-bs-trigger', 'hover focus click');
                    control.setAttribute('title', regionLockMessage);
                    initTooltip(control, tooltipContainerFor(control));
                }
                if (tip) {
                    tip.hidden = false;
                    initTooltip(tip, tooltipContainerFor(tip));
                }
                return;
            }

            field.querySelectorAll('[data-offers-region-hidden]').forEach(function (hidden) {
                hidden.remove();
            });
            if (control) {
                disposeTooltip(control);
                control.removeAttribute('data-bs-toggle');
                control.removeAttribute('title');
            }
            if (tip) {
                disposeTooltip(tip);
                tip.hidden = true;
            }
        }

        function headerHasLeftoverGeo() {
            const forms = document.querySelectorAll(placeFormSelectors);
            for (let i = 0; i < forms.length; i++) {
                const form = forms[i];
                const lat = form.querySelector('input[name="placeLat"]');
                const lng = form.querySelector('input[name="placeLng"]');
                const city = form.querySelector('input[name="city"]');
                const region = form.querySelector('input[name="region"]');
                if ((lat && lat.value) || (lng && lng.value) || (city && city.value) || (region && region.value)) {
                    return true;
                }
            }
            return false;
        }

        function syncRegionLock() {
            const locked = listingPlaceHasText();
            if (! locked && headerHasLeftoverGeo()) {
                clearCarriedLocationFields();
            }
            document.querySelectorAll('[data-offers-region-field]').forEach(function (field) {
                setRegionLocked(field, locked);
            });
        }

        document.querySelectorAll(placeFormSelectors + ' input[name="place"]').forEach(function (input) {
            ['input', 'change', 'keyup', 'paste'].forEach(function (eventName) {
                input.addEventListener(eventName, syncRegionLock);
            });
        });

        syncRegionLock();

        document.addEventListener('cag:place-search-changed', syncRegionLock);

        const offcanvas = document.getElementById('offersFiltersOffcanvas');
        if (offcanvas) {
            offcanvas.addEventListener('shown.bs.offcanvas', function () {
                document.querySelectorAll('#offersFiltersOffcanvas [data-offers-region-field].is-locked').forEach(function (field) {
                    const control = field.querySelector('[data-offers-region-control]');
                    const tip = field.querySelector('[data-offers-region-tip]');
                    if (control) {
                        initTooltip(control, offcanvas);
                    }
                    if (tip) {
                        initTooltip(tip, offcanvas);
                    }
                });
            });
        }
    });
    </script>
@endonce
