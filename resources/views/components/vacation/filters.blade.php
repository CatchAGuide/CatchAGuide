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
@endphp

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

    @if($showMobileToolbar)
        <div class="vacation-filters__mobile-bar d-md-none">
            <button type="button"
                    class="btn btn-outline-secondary vacation-filters__mobile-btn"
                    data-bs-toggle="offcanvas"
                    data-bs-target="#vacationFiltersOffcanvas"
                    aria-controls="vacationFiltersOffcanvas">
                <i class="fa fa-filter me-1"></i>{{ __('vacations.filter_mobile') }}
                @if($activeFilterCount > 0)
                    <span class="badge rounded-pill bg-danger ms-1">{{ $activeFilterCount }}</span>
                @endif
            </button>
            @if($showMapButton)
                <button type="button"
                        class="btn btn-primary vacation-filters__mobile-btn"
                        data-bs-toggle="modal"
                        data-bs-target="#vacationCountryMapModal">
                    <i class="fa fa-map-marker-alt me-1"></i>{{ __('vacations.show_on_map') }}
                </button>
            @endif
        </div>
    @endif

    @if($showDesktop)
    <div class="vacation-filters__desktop {{ $variant === 'sidebar' ? '' : 'd-none d-md-block' }}">
        <div class="{{ $variant === 'sidebar' ? 'vacation-filters__sidebar-stack' : 'row g-3 align-items-end' }}">
            @if($countries->isNotEmpty())
                <div class="{{ $variant === 'sidebar' ? 'vacation-filters__field' : 'col-md-3' }}">
                    <label class="form-label">{{ __('vacations.filter_country') }}</label>
                    <select name="country" class="form-select form-select-sm" onchange="this.form.submit()">
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
                    <select name="species" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">{{ __('vacations.select') }}</option>
                        @foreach($speciesOptions as $species)
                            <option value="{{ $species }}" @selected(($filter->species ?? '') === $species)>{{ $species }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div class="{{ $variant === 'sidebar' ? 'vacation-filters__field' : 'col-md-3' }}">
                <label class="form-label">{{ __('vacations.filter_sort') }}</label>
                <select name="sortby" class="form-select form-select-sm" onchange="this.form.submit()">
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

@if($showMobileToolbar)
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

                <button type="submit" class="btn btn-orange w-100">@lang('message.Search')</button>
            </form>
        </div>
    </div>
@endif
