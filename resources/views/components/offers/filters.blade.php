@props([
    'filter',
    'toursTotal' => 0,
    'tripsTotal' => 0,
    'campsTotal' => 0,
    'speciesOptions' => collect(),
    'countries' => collect(),
    'showTypeToggles' => true,
    'showMobileToolbar' => true,
    'showMapButton' => false,
    'showDesktop' => true,
    'action' => null,
    'typeLinks' => null,
    'vacationLinks' => null,
    'renderSection' => 'all',
    'mapModalId' => 'offersCatalogMapModal',
])

@php
    use Illuminate\Support\Collection;

    $action = $action ?? route('offers.index');
    $total = ($toursTotal ?? 0) + ($tripsTotal ?? 0) + ($campsTotal ?? 0);
    $vacationsTotal = ($tripsTotal ?? 0) + ($campsTotal ?? 0);
    $query = request()->except(['page', 'type', 'vacation']);
    $speciesOptions = $speciesOptions instanceof Collection ? $speciesOptions : collect($speciesOptions ?? []);
    $countries = $countries instanceof Collection ? $countries : collect($countries ?? []);
    $activeType = $filter->type ?? 'all';
    $activeVacation = $filter->vacation ?? 'all';
    $isVacation = $activeType === 'vacation';
    $activeFilterCount = collect(['species', 'country', 'sortby', 'type', 'num_guests'])
        ->filter(fn ($key) => filled(request()->get($key)))
        ->count();
    $typeUrl = function (string $type) use ($action, $query, $typeLinks) {
        if (is_array($typeLinks) && isset($typeLinks[$type])) {
            return $typeLinks[$type];
        }

        $params = $query;
        if ($type !== 'all') {
            $params['type'] = $type;
        }

        return $action.($params ? '?'.http_build_query($params) : '');
    };
    $vacationUrl = function (string $vacation) use ($action, $query, $vacationLinks) {
        if (is_array($vacationLinks) && isset($vacationLinks[$vacation])) {
            return $vacationLinks[$vacation];
        }

        $params = $query;
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
        <div class="offers-filters__type-group offers-filters__type-group--toolbar vacation-filters__pillar-group" role="group">
            <a href="{{ $typeUrl('all') }}"
               class="offers-filters__type-btn offers-filters__type-btn--all vacation-filters__pillar-btn {{ $activeType === 'all' ? 'is-active' : '' }}">
                {{ __('offers.filter_all') }} ({{ $total }})
            </a>
            <a href="{{ $typeUrl('tour') }}"
               class="offers-filters__type-btn offers-filters__type-btn--tour vacation-filters__pillar-btn {{ $activeType === 'tour' ? 'is-active' : '' }}">
                {{ __('offers.filter_tours') }} ({{ $toursTotal }})
            </a>
            <a href="{{ $typeUrl('vacation') }}"
               class="offers-filters__type-btn offers-filters__type-btn--vacation vacation-filters__pillar-btn {{ $isVacation ? 'is-active' : '' }}">
                {{ __('offers.filter_vacations') }} ({{ $vacationsTotal }})
            </a>
        </div>

        @if($isVacation)
            <div class="offers-filters__vacation-subrow" data-offers-vacation-subfilter>
                <div class="offers-filters__type-group offers-filters__type-group--subrow vacation-filters__pillar-group" role="group">
                    <a href="{{ $vacationUrl('all') }}"
                       class="offers-filters__type-btn offers-filters__type-btn--vacation-all vacation-filters__pillar-btn {{ $activeVacation === 'all' ? 'is-active' : '' }}">
                        {{ __('offers.filter_vacations_all') }} ({{ $vacationsTotal }})
                    </a>
                    <a href="{{ $vacationUrl('trip') }}"
                       class="offers-filters__type-btn offers-filters__type-btn--trip vacation-filters__pillar-btn {{ $activeVacation === 'trip' ? 'is-active' : '' }}">
                        {{ __('offers.filter_trips') }} ({{ $tripsTotal }})
                    </a>
                    <a href="{{ $vacationUrl('camp') }}"
                       class="offers-filters__type-btn offers-filters__type-btn--camp vacation-filters__pillar-btn {{ $activeVacation === 'camp' ? 'is-active' : '' }}">
                        {{ __('offers.filter_camps') }} ({{ $campsTotal }})
                    </a>
                </div>
            </div>
        @endif
    </div>
@endif

@if($showSidebar)
<form method="get" action="{{ $action }}" class="offers-filters vacation-filters vacation-filters--sidebar" id="offers-filters-form">
    @foreach($query as $key => $value)
        @if(is_array($value))
            @foreach($value as $v)
                <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
            @endforeach
        @elseif(! in_array($key, ['species', 'country', 'sortby', 'type', 'vacation'], true))
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
            <div class="offers-filters__type-group vacation-filters__pillar-group" role="group">
                <a href="{{ $typeUrl('all') }}"
                   class="offers-filters__type-btn offers-filters__type-btn--all vacation-filters__pillar-btn {{ $activeType === 'all' ? 'is-active' : '' }}">
                    {{ __('offers.filter_all') }} ({{ $total }})
                </a>
                <a href="{{ $typeUrl('tour') }}"
                   class="offers-filters__type-btn offers-filters__type-btn--tour vacation-filters__pillar-btn {{ $activeType === 'tour' ? 'is-active' : '' }}">
                    {{ __('offers.filter_tours') }} ({{ $toursTotal }})
                </a>
                <a href="{{ $typeUrl('vacation') }}"
                   class="offers-filters__type-btn offers-filters__type-btn--vacation vacation-filters__pillar-btn {{ $isVacation ? 'is-active' : '' }}">
                    {{ __('offers.filter_vacations') }} ({{ $vacationsTotal }})
                </a>
            </div>

            @if($isVacation)
                <div class="offers-filters__vacation-subrow" data-offers-vacation-subfilter>
                    <div class="offers-filters__type-group offers-filters__type-group--subrow vacation-filters__pillar-group" role="group">
                        <a href="{{ $vacationUrl('all') }}"
                           class="offers-filters__type-btn offers-filters__type-btn--vacation-all vacation-filters__pillar-btn {{ $activeVacation === 'all' ? 'is-active' : '' }}">
                            {{ __('offers.filter_vacations_all') }} ({{ $vacationsTotal }})
                        </a>
                        <a href="{{ $vacationUrl('trip') }}"
                           class="offers-filters__type-btn offers-filters__type-btn--trip vacation-filters__pillar-btn {{ $activeVacation === 'trip' ? 'is-active' : '' }}">
                            {{ __('offers.filter_trips') }} ({{ $tripsTotal }})
                        </a>
                        <a href="{{ $vacationUrl('camp') }}"
                           class="offers-filters__type-btn offers-filters__type-btn--camp vacation-filters__pillar-btn {{ $activeVacation === 'camp' ? 'is-active' : '' }}">
                            {{ __('offers.filter_camps') }} ({{ $campsTotal }})
                        </a>
                    </div>
                </div>
            @endif
        </div>
    @endif

    @if($showDesktop)
    <div class="vacation-filters__desktop">
        <div class="vacation-filters__sidebar-stack">
            @if($countries->isNotEmpty())
                <div class="vacation-filters__field">
                    <label class="form-label">{{ __('offers.filter_country') }}</label>
                    <select name="country" class="form-select form-select-sm">
                        <option value="">{{ __('offers.all_countries') }}</option>
                        @foreach($countries as $row)
                            <option value="{{ $row['slug'] }}" @selected(($filter->country ?? '') === $row['slug'])>
                                {{ translate($row['name']) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            @if($speciesOptions->isNotEmpty())
                <div class="vacation-filters__field">
                    <label class="form-label">{{ __('offers.filter_species') }}</label>
                    <select name="species" class="form-select form-select-sm">
                        <option value="">{{ __('vacations.select') }}</option>
                        @foreach($speciesOptions as $species)
                            <option value="{{ $species }}" @selected(($filter->species ?? '') === $species)>{{ $species }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <button type="submit" class="btn btn-sm btn-primary w-100 mt-2">{{ __('offers.apply_filters') }}</button>
        </div>
    </div>
    @endif
</form>
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
                        <a class="dropdown-item offers-mobile-sort-option {{ $currentSort === '' ? 'active' : '' }}"
                           href="javascript:void(0)"
                           data-sort="">{{ __('message.newest') }}</a>
                    </li>
                    <li>
                        <a class="dropdown-item offers-mobile-sort-option {{ $currentSort === 'price-asc' ? 'active' : '' }}"
                           href="javascript:void(0)"
                           data-sort="price-asc">@lang('message.lowprice')</a>
                    </li>
                    <li>
                        <a class="dropdown-item offers-mobile-sort-option {{ $currentSort === 'price-desc' ? 'active' : '' }}"
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
            document.querySelectorAll('.offers-mobile-sort-option').forEach(function (option) {
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
    <div class="offcanvas offcanvas-bottom vacation-filters-offcanvas" tabindex="-1" id="offersFiltersOffcanvas" aria-labelledby="offersFiltersOffcanvasLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offersFiltersOffcanvasLabel">{{ __('offers.filter_mobile') }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body">
            <form method="get" action="{{ $action }}" class="vacation-filters-offcanvas__form">
                @foreach($query as $key => $value)
                    @if(is_array($value))
                        @foreach($value as $v)
                            <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
                        @endforeach
                    @elseif(! in_array($key, ['species', 'country', 'sortby', 'type', 'vacation'], true))
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
                            <div class="offers-filters__type-group vacation-filters__pillar-group vacation-filters__pillar-group--mobile" role="group">
                                <a href="{{ $typeUrl('all') }}" class="offers-filters__type-btn vacation-filters__pillar-btn {{ $activeType === 'all' ? 'is-active' : '' }}">
                                    {{ __('offers.filter_all') }} ({{ $total }})
                                </a>
                                <a href="{{ $typeUrl('tour') }}" class="offers-filters__type-btn vacation-filters__pillar-btn {{ $activeType === 'tour' ? 'is-active' : '' }}">
                                    {{ __('offers.filter_tours') }} ({{ $toursTotal }})
                                </a>
                                <a href="{{ $typeUrl('vacation') }}" class="offers-filters__type-btn offers-filters__type-btn--vacation vacation-filters__pillar-btn {{ $isVacation ? 'is-active' : '' }}">
                                    {{ __('offers.filter_vacations') }} ({{ $vacationsTotal }})
                                </a>
                            </div>

                            @if($isVacation)
                                <div class="offers-filters__vacation-subrow" data-offers-vacation-subfilter>
                                    <div class="offers-filters__type-group offers-filters__type-group--subrow vacation-filters__pillar-group vacation-filters__pillar-group--mobile" role="group">
                                        <a href="{{ $vacationUrl('all') }}" class="offers-filters__type-btn vacation-filters__pillar-btn {{ $activeVacation === 'all' ? 'is-active' : '' }}">
                                            {{ __('offers.filter_vacations_all') }} ({{ $vacationsTotal }})
                                        </a>
                                        <a href="{{ $vacationUrl('trip') }}" class="offers-filters__type-btn offers-filters__type-btn--trip vacation-filters__pillar-btn {{ $activeVacation === 'trip' ? 'is-active' : '' }}">
                                            {{ __('offers.filter_trips') }} ({{ $tripsTotal }})
                                        </a>
                                        <a href="{{ $vacationUrl('camp') }}" class="offers-filters__type-btn offers-filters__type-btn--camp vacation-filters__pillar-btn {{ $activeVacation === 'camp' ? 'is-active' : '' }}">
                                            {{ __('offers.filter_camps') }} ({{ $campsTotal }})
                                        </a>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                @if($countries->isNotEmpty())
                    <div class="mb-3">
                        <label class="form-label">{{ __('offers.filter_country') }}</label>
                        <select name="country" class="form-select">
                            <option value="">{{ __('offers.all_countries') }}</option>
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
                        <label class="form-label">{{ __('offers.filter_species') }}</label>
                        <select name="species" class="form-select">
                            <option value="">{{ __('vacations.select') }}</option>
                            @foreach($speciesOptions as $species)
                                <option value="{{ $species }}" @selected(($filter->species ?? '') === $species)>{{ $species }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="mb-3">
                    <label class="form-label">{{ __('offers.filter_sort') }}</label>
                    <select name="sortby" class="form-select">
                        <option value="">{{ __('message.newest') }}</option>
                        <option value="price-asc" @selected(($filter->sortBy ?? '') === 'price-asc')>@lang('message.lowprice')</option>
                        <option value="price-desc" @selected(($filter->sortBy ?? '') === 'price-desc')>{{ __('trips.catalog_sort_price_desc') }}</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-orange w-100">{{ __('offers.apply_filters') }}</button>
            </form>
        </div>
    </div>
@endif
