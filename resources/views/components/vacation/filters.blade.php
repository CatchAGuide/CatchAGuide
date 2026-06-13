@props([

    'filter',

    'tripsTotal' => null,

    'campsTotal' => null,

    'speciesOptions' => collect(),

    'countries' => collect(),

    'showPillarToggles' => true,

    'action' => null,

])



@php
    use Illuminate\Support\Collection;

    $action = $action ?? url()->current();
    $total = ($tripsTotal ?? 0) + ($campsTotal ?? 0);
    $query = request()->except(['page', 'pillar']);
    $speciesOptions = $speciesOptions instanceof Collection ? $speciesOptions : collect($speciesOptions ?? []);
    $countries = $countries instanceof Collection ? $countries : collect($countries ?? []);
    $activePillar = $filter->pillar ?? 'all';
@endphp



<form method="get" action="{{ $action }}" class="vacation-filters" id="vacation-filters-form">

    @foreach($query as $key => $value)

        @if(is_array($value))

            @foreach($value as $v)

                <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">

            @endforeach

        @elseif($key !== 'species' && $key !== 'duration' && $key !== 'country' && $key !== 'sortby')

            <input type="hidden" name="{{ $key }}" value="{{ $value }}">

        @endif

    @endforeach



    @if($showPillarToggles && $tripsTotal !== null && $campsTotal !== null)

        <div class="vacation-filters__pillar-group" role="group" data-analytics-vacation-pillar-filter>

            <a href="{{ $action }}?{{ http_build_query(array_merge($query, ['pillar' => 'all'])) }}"

               class="vacation-filters__pillar-btn vacation-filters__pillar-btn--all {{ $activePillar === 'all' ? 'is-active' : '' }}">

                {{ __('vacations.filter_show_all') }} ({{ $total }})

            </a>

            <a href="{{ $action }}?{{ http_build_query(array_merge($query, ['pillar' => 'trips'])) }}"

               class="vacation-filters__pillar-btn vacation-filters__pillar-btn--trips {{ $activePillar === 'trips' ? 'is-active' : '' }}">

                {{ __('vacations.filter_trips_only') }} ({{ $tripsTotal }})

            </a>

            <a href="{{ $action }}?{{ http_build_query(array_merge($query, ['pillar' => 'camps'])) }}"

               class="vacation-filters__pillar-btn vacation-filters__pillar-btn--camps {{ $activePillar === 'camps' ? 'is-active' : '' }}">

                {{ __('vacations.filter_camps_only') }} ({{ $campsTotal }})

            </a>

        </div>

    @endif



    <div class="row g-3 align-items-end">

        @if($countries->isNotEmpty())

            <div class="col-md-3">

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

            <div class="col-md-3">

                <label class="form-label">{{ __('vacations.filter_species') }}</label>

                <select name="species" class="form-select form-select-sm" onchange="this.form.submit()">

                    <option value="">{{ __('vacations.select') }}</option>

                    @foreach($speciesOptions as $species)

                        <option value="{{ $species }}" @selected(($filter->species ?? '') === $species)>{{ $species }}</option>

                    @endforeach

                </select>

            </div>

        @endif



        <div class="col-md-3">

            <label class="form-label">{{ __('vacations.filter_sort') }}</label>

            <select name="sortby" class="form-select form-select-sm" onchange="this.form.submit()">

                <option value="">{{ __('message.newest') }}</option>

                <option value="price-asc" @selected(($filter->sortBy ?? '') === 'price-asc')>@lang('message.lowprice')</option>

                <option value="price-desc" @selected(($filter->sortBy ?? '') === 'price-desc')>{{ __('trips.catalog_sort_price_desc') }}</option>

            </select>

        </div>

    </div>

</form>

