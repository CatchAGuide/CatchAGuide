@props([
    'filter',
    'tripsTotal' => null,
    'campsTotal' => null,
    'speciesOptions' => collect(),
    'accommodationTypeOptions' => collect(),
    'countries' => collect(),
    'showPillarToggles' => true,
    'showMobileToolbar' => true,
    'showMapButton' => false,
    'mapInSidebar' => false,
    'variant' => 'default',
    'showDesktop' => true,
    'action' => null,
    'omitPillarFromQuery' => false,
    'pillarLinks' => null,
    'renderSection' => 'all',
])

@php
    use App\Domain\Vacation\VacationListingFilter;
    use Illuminate\Support\Collection;

    $action = $action ?? url()->current();
    $total = ($tripsTotal ?? 0) + ($campsTotal ?? 0);
    $query = request()->except(['page', 'pillar']);
    $speciesOptions = $speciesOptions instanceof Collection ? $speciesOptions : collect($speciesOptions ?? []);
    $accommodationTypeOptions = $accommodationTypeOptions instanceof Collection ? $accommodationTypeOptions : collect($accommodationTypeOptions ?? []);
    $countries = $countries instanceof Collection ? $countries : collect($countries ?? []);
    $tripDurationOptions = collect(VacationListingFilter::TRIP_DURATION_BUCKETS)->map(fn (string $value) => [
        'value' => $value,
        'label' => __('vacations.filter_duration_'.$value),
    ]);
    $showTripDurationFilter = $filter->showsTripDurationFilter();
    $showCampFacets = $filter->showsCampFacets();
    $campFacetKeys = VacationListingFilter::CAMP_FACET_KEYS;
    $managedFilterKeys = array_merge(['species', 'country', 'duration'], $campFacetKeys);
    $offcanvasManagedKeys = array_merge(['species', 'country', 'sortby', 'pillar', 'duration'], $campFacetKeys);
    $activePillar = $filter->pillar ?? 'all';
    $activeFilterCount = collect($offcanvasManagedKeys)
        ->filter(fn ($key) => $key === 'pillar' && $omitPillarFromQuery ? false : filled(request()->get($key)))
        ->count();
    $hasSidebarFilters = collect($managedFilterKeys)
        ->filter(fn ($key) => filled(request()->get($key)))
        ->isNotEmpty();
    $clearFiltersUrl = (function () use ($action, $query, $managedFilterKeys, $activePillar, $omitPillarFromQuery) {
        $params = collect($query)->except($managedFilterKeys)->all();
        if ($activePillar !== 'all' && ! $omitPillarFromQuery) {
            $params['pillar'] = $activePillar;
        }

        return $action.($params ? '?'.http_build_query($params) : '');
    })();
    $pillarUrl = function (string $pillar) use ($action, $query, $pillarLinks, $campFacetKeys) {
        if (is_array($pillarLinks) && isset($pillarLinks[$pillar])) {
            return $pillarLinks[$pillar];
        }

        $params = $query;
        if ($pillar !== 'trips') {
            unset($params['duration']);
        }
        if ($pillar !== 'camps') {
            foreach ($campFacetKeys as $key) {
                unset($params[$key]);
            }
        }

        return $action.'?'.http_build_query(array_merge($params, ['pillar' => $pillar]));
    };
    $pillarBaseUrl = fn (string $pillar) => explode('?', $pillarUrl($pillar), 2)[0];
    $showPillarFilters = $showPillarToggles && ($tripsTotal ?? 0) > 0 && ($campsTotal ?? 0) > 0;
    $showToolbar = in_array($renderSection, ['all', 'toolbar'], true);
    $showSidebar = in_array($renderSection, ['all', 'sidebar'], true);
    $showMobile = $showMobileToolbar && in_array($renderSection, ['all', 'mobile'], true);
    $showOffcanvas = $showMobileToolbar && in_array($renderSection, ['all', 'offcanvas'], true);
    $currentSort = $filter->sortBy ?? '';
@endphp

@if($showToolbar)
<form method="get" action="{{ $action }}" class="vacation-country__sort d-flex align-items-center ms-auto" data-vacation-sort-form>
    @foreach($query as $key => $value)
        @if(is_array($value))
            @foreach($value as $v)
                <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
            @endforeach
        @elseif($key !== 'sortby')
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endif
    @endforeach

    @if($activePillar !== 'all' && ! $omitPillarFromQuery)
        <input type="hidden" name="pillar" value="{{ $activePillar }}">
    @endif

    <label for="vacation-sortby" class="vacation-country__sort-label">{{ __('vacations.filter_sort') }}</label>
    <select id="vacation-sortby" name="sortby" class="form-select form-select-sm" data-vacation-sort-select>
        @include('components.vacation.partials.sort-options', ['currentSort' => $currentSort])
    </select>
</form>
@endif

@if($showSidebar)
<form method="get" action="{{ $action }}" class="vacation-filters vacation-filters--{{ $variant }}" id="vacation-filters-form{{ $variant === 'mobile' ? '-mobile' : '' }}">
    @foreach($query as $key => $value)
        @if(in_array($key, $managedFilterKeys, true))
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

    @if($activePillar !== 'all' && ! $omitPillarFromQuery)
        <input type="hidden" name="pillar" value="{{ $activePillar }}">
    @endif

    @if($showPillarFilters)
        <div class="vacation-filters__pillar-group" role="group" data-analytics-vacation-pillar-filter>
            <a href="{{ $pillarUrl('all') }}"
               class="vacation-filters__pillar-btn vacation-filters__pillar-btn--all {{ $activePillar === 'all' ? 'is-active' : '' }}">
                {{ __('vacations.filter_show_all') }} ({{ $total }})
            </a>
            <a href="{{ $pillarUrl('trips') }}"
               class="vacation-filters__pillar-btn vacation-filters__pillar-btn--trips {{ $activePillar === 'trips' ? 'is-active' : '' }}">
                {{ __('vacations.filter_trips_only') }} ({{ $tripsTotal }})
            </a>
            <a href="{{ $pillarUrl('camps') }}"
               class="vacation-filters__pillar-btn vacation-filters__pillar-btn--camps {{ $activePillar === 'camps' ? 'is-active' : '' }}">
                {{ __('vacations.filter_camps_only') }} ({{ $campsTotal }})
            </a>
        </div>
    @endif

    @if($showDesktop)
    <div class="vacation-filters__desktop {{ $variant === 'sidebar' ? '' : 'd-none d-md-block' }}">
        <div class="{{ $variant === 'sidebar' ? 'vacation-filters__sidebar-stack' : 'row g-3 align-items-end' }}">
            @if($countries->isNotEmpty())
                <div class="{{ $variant === 'sidebar' ? 'vacation-filters__field' : 'col-md-3' }}">
                    <label class="form-label">{{ __('vacations.filter_country') }}</label>
                    <select name="country" class="form-select form-select-sm">
                        <option value="">{{ __('vacations.filter_show_all') }}</option>
                        @foreach($countries as $row)
                            <option value="{{ $row['slug'] }}" @selected(($filter->country ?? '') === $row['slug'])>
                                {{ translate($row['name']) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            @include('components.offers.partials.multi-select', [
                'fieldClass' => $variant === 'sidebar' ? 'vacation-filters__field' : 'col-md-3',
                'wrapperClass' => 'vacation-filters__species',
                'inputName' => 'species[]',
                'inputPrefix' => $variant === 'sidebar' ? 'vacation-species-sidebar' : 'vacation-species-desktop',
                'label' => __('vacations.filter_species'),
                'placeholder' => __('vacations.filter_species_placeholder'),
                'searchPlaceholder' => __('vacations.filter_species_search'),
                'options' => $speciesOptions,
                'selectedValues' => array_merge($filter->speciesIds ?? [], $filter->speciesNames ?? []),
            ])

            @if($showTripDurationFilter)
                <div class="{{ $variant === 'sidebar' ? 'vacation-filters__field' : 'col-md-3' }}">
                    <label class="form-label">{{ __('vacations.filter_duration') }}</label>
                    <select name="duration" class="form-select form-select-sm">
                        <option value="">{{ __('vacations.filter_show_all') }}</option>
                        @foreach($tripDurationOptions as $duration)
                            <option value="{{ $duration['value'] }}" @selected(($filter->tripDuration ?? '') === $duration['value'])>
                                {{ $duration['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            @include('components.vacation.partials.camp-facets', [
                'fieldClass' => $variant === 'sidebar' ? 'vacation-filters__field' : 'col-md-3',
                'selectClass' => 'form-select form-select-sm',
                'inputPrefix' => 'vacation-camp-sidebar',
                'filter' => $filter,
                'accommodationTypeOptions' => $accommodationTypeOptions,
                'showCampFacets' => $showCampFacets,
            ])

            <div class="vacation-filters__actions {{ $variant === 'sidebar' ? 'mt-2' : 'col-md-auto' }}">
                <button type="submit" class="btn btn-sm btn-primary w-100">{{ __('vacations.apply_filters') }}</button>
                @if($hasSidebarFilters)
                    <a href="{{ $clearFiltersUrl }}" class="btn btn-sm btn-outline-secondary w-100 mt-2">
                        {{ __('vacations.clear_filters') }}
                    </a>
                @endif
            </div>

            @if($showMapButton && ! $mapInSidebar)
                <div class="col-md-auto ms-md-auto">
                    <button type="button"
                            class="btn btn-primary btn-sm vacation-filters__map-btn"
                            data-bs-toggle="modal"
                            data-bs-target="#vacationCountryMapModal">
                        <i class="fa fa-map-marker-alt me-1"></i>{{ __('vacations.show_on_map') }}
                    </button>
                </div>
            @endif
        </div>
    </div>
    @endif
</form>
@include('components.offers.partials.multi-select-script')
@endif

@if($showMobile)
    <div class="sfm-bar vacation-filters__sfm-bar">
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
                        <a class="dropdown-item vacation-mobile-sort-option {{ $currentSort === '' ? 'active' : '' }}"
                           href="javascript:void(0)"
                           data-sort="">{{ __('message.newest') }}</a>
                    </li>
                    <li>
                        <a class="dropdown-item vacation-mobile-sort-option {{ $currentSort === 'price-asc' ? 'active' : '' }}"
                           href="javascript:void(0)"
                           data-sort="price-asc">@lang('message.lowprice')</a>
                    </li>
                    <li>
                        <a class="dropdown-item vacation-mobile-sort-option {{ $currentSort === 'price-desc' ? 'active' : '' }}"
                           href="javascript:void(0)"
                           data-sort="price-desc">{{ __('trips.catalog_sort_price_desc') }}</a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="sfm-bar__divider"></div>

        <div class="sfm-bar__item">
            <button type="button"
                    class="sfm-bar__btn"
                    id="vacationSfmFilterBtn"
                    data-bs-toggle="offcanvas"
                    data-bs-target="#vacationFiltersOffcanvas"
                    aria-controls="vacationFiltersOffcanvas">
                <span class="sfm-bar__icon">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L13 10.414V17a1 1 0 01-1.447.894l-4-2A1 1 0 017 15v-4.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd"/>
                    </svg>
                </span>
                <span class="sfm-bar__label">@lang('message.filter')</span>
                <span class="sfm-bar__badge" id="vacation-active-filter-counter">{{ $activeFilterCount > 0 ? $activeFilterCount : '' }}</span>
            </button>
        </div>

        @if($showMapButton)
            <div class="sfm-bar__divider"></div>

            <div class="sfm-bar__item">
                <button type="button"
                        class="sfm-bar__btn sfm-bar__btn--map"
                        data-bs-toggle="modal"
                        data-bs-target="#vacationCountryMapModal">
                    <span class="sfm-bar__icon">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                        </svg>
                    </span>
                    <span class="sfm-bar__label">@lang('vacations.show_on_map')</span>
                </button>
            </div>
        @endif
    </div>
@endif

@if($showOffcanvas)
    <div class="offcanvas offcanvas-bottom vacation-filters-offcanvas" tabindex="-1" id="vacationFiltersOffcanvas" aria-labelledby="vacationFiltersOffcanvasLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="vacationFiltersOffcanvasLabel">{{ __('vacations.filter_mobile') }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <form method="get" action="{{ $action }}" class="vacation-filters-offcanvas__form">
                @foreach($query as $key => $value)
                    @if(in_array($key, $offcanvasManagedKeys, true))
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

                @if($showPillarFilters)
                    <div class="mb-3">
                        <label class="form-label">{{ __('vacations.filter_show_all') }}</label>
                        @if(is_array($pillarLinks))
                            <div class="vacation-filters__pillar-group vacation-filters__pillar-group--mobile" role="group" data-vacation-pillar-group>
                                <button type="button" data-pillar-vacation="all" data-pillar-base-url="{{ $pillarBaseUrl('all') }}"
                                   class="vacation-filters__pillar-btn vacation-filters__pillar-btn--all {{ $activePillar === 'all' ? 'is-active' : '' }}">
                                    {{ __('vacations.filter_show_all') }} ({{ $total }})
                                </button>
                                <button type="button" data-pillar-vacation="trips" data-pillar-base-url="{{ $pillarBaseUrl('trips') }}"
                                   class="vacation-filters__pillar-btn vacation-filters__pillar-btn--trips {{ $activePillar === 'trips' ? 'is-active' : '' }}">
                                    {{ __('vacations.filter_trips_only') }} ({{ $tripsTotal }})
                                </button>
                                <button type="button" data-pillar-vacation="camps" data-pillar-base-url="{{ $pillarBaseUrl('camps') }}"
                                   class="vacation-filters__pillar-btn vacation-filters__pillar-btn--camps {{ $activePillar === 'camps' ? 'is-active' : '' }}">
                                    {{ __('vacations.filter_camps_only') }} ({{ $campsTotal }})
                                </button>
                            </div>
                        @else
                            <select name="pillar" class="form-select">
                                <option value="all" @selected($activePillar === 'all')>{{ __('vacations.filter_show_all') }} ({{ $total }})</option>
                                <option value="trips" @selected($activePillar === 'trips')>{{ __('vacations.filter_trips_only') }} ({{ $tripsTotal }})</option>
                                <option value="camps" @selected($activePillar === 'camps')>{{ __('vacations.filter_camps_only') }} ({{ $campsTotal }})</option>
                            </select>
                        @endif
                    </div>
                @endif

                @if($countries->isNotEmpty())
                    <div class="mb-3">
                        <label class="form-label">{{ __('vacations.filter_country') }}</label>
                        <select name="country" class="form-select">
                            <option value="">{{ __('vacations.filter_show_all') }}</option>
                            @foreach($countries as $row)
                                <option value="{{ $row['slug'] }}" @selected(($filter->country ?? '') === $row['slug'])>
                                    {{ translate($row['name']) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                @include('components.offers.partials.multi-select', [
                    'fieldClass' => 'mb-3',
                    'wrapperClass' => 'vacation-filters__species',
                    'inputName' => 'species[]',
                    'inputPrefix' => 'vacation-species-offcanvas',
                    'label' => __('vacations.filter_species'),
                    'placeholder' => __('vacations.filter_species_placeholder'),
                    'searchPlaceholder' => __('vacations.filter_species_search'),
                    'options' => $speciesOptions,
                    'selectedValues' => array_merge($filter->speciesIds ?? [], $filter->speciesNames ?? []),
                ])

                @if($tripDurationOptions->isNotEmpty())
                    <div data-vacation-facet-section="trip" @if(! $showTripDurationFilter) class="d-none" @endif>
                        <div class="mb-3">
                            <label class="form-label">{{ __('vacations.filter_duration') }}</label>
                            <select name="duration" class="form-select">
                                <option value="">{{ __('vacations.filter_show_all') }}</option>
                                @foreach($tripDurationOptions as $duration)
                                    <option value="{{ $duration['value'] }}" @selected(($filter->tripDuration ?? '') === $duration['value'])>
                                        {{ $duration['label'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                @endif

                @include('components.vacation.partials.camp-facets', [
                    'fieldClass' => 'mb-3',
                    'selectClass' => 'form-select',
                    'inputPrefix' => 'vacation-camp-offcanvas',
                    'filter' => $filter,
                    'accommodationTypeOptions' => $accommodationTypeOptions,
                    'showCampFacets' => $showCampFacets,
                    'interactive' => true,
                ])

                <div class="mb-3">
                    <label class="form-label">{{ __('vacations.filter_sort') }}</label>
                    <select name="sortby" class="form-select">
                        @include('components.vacation.partials.sort-options', ['currentSort' => $currentSort])
                    </select>
                </div>

                <button type="submit" class="btn btn-orange w-100 vacation-filters-offcanvas__submit">@lang('message.Search')</button>
            </form>
        </div>
    </div>
    @include('components.offers.partials.multi-select-script')
@endif

@once
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        function showVacationFilterLoader() {
            if (window.PageLoader) {
                window.PageLoader.show();
            }
        }

        function bindAutoSubmit(select) {
            if (!select || select.dataset.vacationLoaderBound) {
                return;
            }
            select.dataset.vacationLoaderBound = '1';
            select.addEventListener('change', function () {
                showVacationFilterLoader();
                if (select.form) {
                    select.form.submit();
                }
            });
        }

        document.querySelectorAll('.vacation-filters select, [data-vacation-sort-select]').forEach(bindAutoSubmit);
        document.querySelectorAll('.vacation-filters [data-vacation-facet-toggle]').forEach(bindAutoSubmit);

        document.querySelectorAll('#vacation-filters-form, .vacation-filters-offcanvas__form').forEach(function (form) {
            form.addEventListener('submit', showVacationFilterLoader);
        });

        var filterBtn = document.getElementById('vacationSfmFilterBtn');
        if (filterBtn) {
            filterBtn.addEventListener('click', function (event) {
                event.stopPropagation();
            });
        }

        (function () {
            var offcanvas = document.getElementById('vacationFiltersOffcanvas');
            var form = offcanvas ? offcanvas.querySelector('.vacation-filters-offcanvas__form') : null;
            var pillarGroup = offcanvas ? offcanvas.querySelector('[data-vacation-pillar-group]') : null;
            if (!offcanvas || !form || !pillarGroup) {
                return;
            }

            var pillarButtons = pillarGroup.querySelectorAll('[data-pillar-vacation]');
            var facetSections = offcanvas.querySelectorAll('[data-vacation-facet-section]');

            function setPillarState(pillar, baseUrl) {
                if (baseUrl) {
                    form.action = baseUrl;
                }

                pillarButtons.forEach(function (btn) {
                    btn.classList.toggle('is-active', btn.dataset.pillarVacation === pillar);
                });

                facetSections.forEach(function (section) {
                    var sectionType = section.dataset.vacationFacetSection;
                    var matches = (sectionType === 'trip' && pillar === 'trips')
                        || (sectionType === 'camp' && pillar === 'camps');
                    section.classList.toggle('d-none', !matches);
                });
            }

            pillarButtons.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    setPillarState(btn.dataset.pillarVacation, btn.dataset.pillarBaseUrl);
                });
            });
        })();

        document.querySelectorAll('.vacation-mobile-sort-option').forEach(function (option) {
            option.addEventListener('click', function (event) {
                event.preventDefault();
                showVacationFilterLoader();

                var urlParams = new URLSearchParams(window.location.search);
                var sortValue = this.dataset.sort;

                if (sortValue) {
                    urlParams.set('sortby', sortValue);
                } else {
                    urlParams.delete('sortby');
                }

                var query = urlParams.toString();
                window.location.href = query
                    ? window.location.pathname + '?' + query
                    : window.location.pathname;
            });
        });
    });
    </script>
@endonce
