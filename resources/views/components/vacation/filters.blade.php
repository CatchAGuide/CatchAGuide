@props([
    'filter',
    'tripsTotal' => null,
    'campsTotal' => null,
    'speciesOptions' => collect(),
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
    use Illuminate\Support\Collection;

    $action = $action ?? url()->current();
    $total = ($tripsTotal ?? 0) + ($campsTotal ?? 0);
    $query = request()->except(['page', 'pillar']);
    $speciesOptions = $speciesOptions instanceof Collection ? $speciesOptions : collect($speciesOptions ?? []);
    $countries = $countries instanceof Collection ? $countries : collect($countries ?? []);
    $activePillar = $filter->pillar ?? 'all';
    $activeFilterCount = collect(['species', 'country', 'sortby', 'pillar'])
        ->filter(fn ($key) => $key === 'pillar' && $omitPillarFromQuery ? false : filled(request()->get($key)))
        ->count();
    $pillarUrl = function (string $pillar) use ($action, $query, $pillarLinks) {
        if (is_array($pillarLinks) && isset($pillarLinks[$pillar])) {
            return $pillarLinks[$pillar];
        }

        return $action.'?'.http_build_query(array_merge($query, ['pillar' => $pillar]));
    };
    $showPillarFilters = $showPillarToggles && ($tripsTotal ?? 0) > 0 && ($campsTotal ?? 0) > 0;
    $showSidebar = in_array($renderSection, ['all', 'sidebar'], true);
    $showMobile = $showMobileToolbar && in_array($renderSection, ['all', 'mobile'], true);
    $showOffcanvas = $showMobileToolbar && in_array($renderSection, ['all', 'offcanvas'], true);
    $currentSort = $filter->sortBy ?? '';
@endphp

@if($showSidebar)
<form method="get" action="{{ $action }}" class="vacation-filters vacation-filters--{{ $variant }}" id="vacation-filters-form{{ $variant === 'mobile' ? '-mobile' : '' }}">
    @foreach($query as $key => $value)
        @if(is_array($value))
            @foreach($value as $v)
                <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
            @endforeach
        @elseif($key !== 'species' && $key !== 'country' && $key !== 'sortby')
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
                        <option value="">{{ __('vacations.all_region') }}</option>
                        @foreach($countries as $row)
                            <option value="{{ $row['slug'] }}" @selected(($filter->country ?? '') === $row['slug'])>
                                {{ translate($row['name']) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            @if($speciesOptions->isNotEmpty())
                <div class="{{ $variant === 'sidebar' ? 'vacation-filters__field' : 'col-md-3' }}">
                    <label class="form-label">{{ __('vacations.filter_species') }}</label>
                    <select name="species" class="form-select form-select-sm">
                        <option value="">{{ __('vacations.select') }}</option>
                        @foreach($speciesOptions as $species)
                            <option value="{{ $species }}" @selected(($filter->species ?? '') === $species)>{{ $species }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="{{ $variant === 'sidebar' ? 'vacation-filters__field' : 'col-md-3' }}">
                <label class="form-label">{{ __('vacations.filter_sort') }}</label>
                <select name="sortby" class="form-select form-select-sm">
                    <option value="">{{ __('message.newest') }}</option>
                    <option value="price-asc" @selected(($filter->sortBy ?? '') === 'price-asc')>@lang('message.lowprice')</option>
                    <option value="price-desc" @selected(($filter->sortBy ?? '') === 'price-desc')>{{ __('trips.catalog_sort_price_desc') }}</option>
                </select>
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

    @once
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            const filterBtn = document.getElementById('vacationSfmFilterBtn');
            if (filterBtn) {
                filterBtn.addEventListener('click', function (event) {
                    event.stopPropagation();
                });
            }

            document.querySelectorAll('.vacation-mobile-sort-option').forEach(function (option) {
                option.addEventListener('click', function (event) {
                    event.preventDefault();

                    const urlParams = new URLSearchParams(window.location.search);
                    const sortValue = this.dataset.sort;

                    if (sortValue) {
                        urlParams.set('sortby', sortValue);
                    } else {
                        urlParams.delete('sortby');
                    }

                    const query = urlParams.toString();
                    window.location.href = query
                        ? `${window.location.pathname}?${query}`
                        : window.location.pathname;
                });
            });
        });
        </script>
    @endonce
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
                    @if(is_array($value))
                        @foreach($value as $v)
                            <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
                        @endforeach
                    @elseif(! in_array($key, ['species', 'country', 'sortby', 'pillar'], true))
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endif
                @endforeach

                @if($showPillarFilters)
                    <div class="mb-3">
                        <label class="form-label">{{ __('vacations.filter_show_all') }}</label>
                        @if(is_array($pillarLinks))
                            <div class="vacation-filters__pillar-group vacation-filters__pillar-group--mobile" role="group">
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
                            <option value="">{{ __('vacations.all_region') }}</option>
                            @foreach($countries as $row)
                                <option value="{{ $row['slug'] }}" @selected(($filter->country ?? '') === $row['slug'])>
                                    {{ translate($row['name']) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                @if($speciesOptions->isNotEmpty())
                    <div class="mb-3">
                        <label class="form-label">{{ __('vacations.filter_species') }}</label>
                        <select name="species" class="form-select">
                            <option value="">{{ __('vacations.select') }}</option>
                            @foreach($speciesOptions as $species)
                                <option value="{{ $species }}" @selected(($filter->species ?? '') === $species)>{{ $species }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="mb-3">
                    <label class="form-label">{{ __('vacations.filter_sort') }}</label>
                    <select name="sortby" class="form-select">
                        <option value="">{{ __('message.newest') }}</option>
                        <option value="price-asc" @selected(($filter->sortBy ?? '') === 'price-asc')>@lang('message.lowprice')</option>
                        <option value="price-desc" @selected(($filter->sortBy ?? '') === 'price-desc')>{{ __('trips.catalog_sort_price_desc') }}</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-orange w-100 vacation-filters-offcanvas__submit">@lang('message.Search')</button>
            </form>
        </div>
    </div>
@endif
