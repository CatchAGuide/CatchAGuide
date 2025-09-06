@if(count($guidings))
    <div class="filter-sort-container">
        {{-- Sort By Dropdown --}}
        @if(!$agent->ismobile())
        <div class="d-flex align-items-center">
            <span class="me-2">@lang('message.sortby'):</span>
            <form action="{{route('guidings.index')}}" method="get" style="margin-bottom: 0;">
                <select class="form-select form-select-sm" name="sortby" id="sortby-2" style="width: auto;">
                    <option value="" disabled selected>@lang('message.choose')...</option>
                    <option value="newest" {{request()->get('sortby') == 'newest' ? 'selected' : '' }}>@lang('message.newest')</option>
                    <option value="price-asc" {{request()->get('sortby') == 'price-asc' ? 'selected' : '' }}>@lang('message.lowprice')</option>
                    {{-- <option value="price-desc" {{request()->get('sortby') == 'price-desc' ? 'selected' : '' }}>@lang('message.highprice')</option> --}}
                    <option value="short-duration" {{request()->get('sortby') == 'short-duration' ? 'selected' : '' }}>@lang('message.shortduration')</option>
                    <option value="long-duration" {{request()->get('sortby') == 'long-duration' ? 'selected' : '' }}>@lang('message.longduration')</option>
                </select>
                @foreach(request()->except('sortby') as $key => $value)
                    @if(is_array($value))
                        @foreach($value as $arrayValue)
                            <input type="hidden" name="{{ $key }}[]" value="{{ $arrayValue }}">
                        @endforeach
                    @else
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endif
                @endforeach
            </form>
        </div>
        @endif

        {{-- Active Filters --}}
        <div class="active-filters">
            @if(request()->has('target_fish'))
                @foreach(request()->get('target_fish') as $fishId)
                    @php
                        $fish = $targetFishOptions->firstWhere('id', $fishId);
                    @endphp
                    @if($fish)
                        <span class="badge bg-light text-dark border">
                            {{ app()->getLocale() == 'en' ? $fish->name_en : $fish->name }}
                            <button type="button" class="btn-close ms-2" data-filter-type="target_fish" data-filter-id="{{ $fishId }}"></button>
                        </span>
                    @endif
                @endforeach
            @endif

            @if(request()->has('methods'))
                @foreach(request()->get('methods') as $methodId)
                    @php
                        $method = $methodOptions->firstWhere('id', $methodId);
                    @endphp
                    @if($method)
                        <span class="badge bg-light text-dark border">
                            {{ app()->getLocale() == 'en' ? $method->name_en : $method->name }}
                            <button type="button" class="btn-close ms-2" data-filter-type="methods" data-filter-id="{{ $methodId }}"></button>
                        </span>
                    @endif
                @endforeach
            @endif

            @if(request()->has('water'))
                @foreach(request()->get('water') as $waterId)
                    @php
                        $water = $waterTypeOptions->firstWhere('id', $waterId);
                    @endphp
                    @if($water)
                        <span class="badge bg-light text-dark border">
                            {{ app()->getLocale() == 'en' ? $water->name_en : $water->name }}
                            <button type="button" class="btn-close ms-2" data-filter-type="water" data-filter-id="{{ $waterId }}"></button>
                        </span>
                    @endif
                @endforeach
            @endif

            {{-- Duration Type Filters --}}
            @if(request()->has('duration_types'))
                @foreach(request()->get('duration_types') as $durationType)
                    <span class="badge bg-light text-dark border">
                        @if($durationType == 'half_day')
                            @lang('guidings.half_day')
                        @elseif($durationType == 'full_day')
                            @lang('guidings.full_day')
                        @elseif($durationType == 'multi_day')
                            @lang('guidings.multi_day')
                        @endif
                        <button type="button" class="btn-close ms-2" data-filter-type="duration_types" data-filter-id="{{ $durationType }}"></button>
                    </span>
                @endforeach
            @endif

            {{-- Number of Persons Filter --}}
            @if(request()->has('num_persons'))
                @php
                    $numPersons = request()->get('num_persons');
                @endphp
                <span class="badge bg-light text-dark border">
                    {{ $numPersons }} {{ $numPersons == 1 ? __('message.person') : __('message.persons') }}
                    <button type="button" class="btn-close ms-2" data-filter-type="num_persons" data-filter-id="{{ $numPersons }}"></button>
                </span>
            @endif

            {{-- Price Range Filter --}}
            @php
                $priceMin = request()->get('price_min');
                $priceMax = request()->get('price_max');
                $defaultMinPrice = 50;
                $defaultMaxPrice = isset($overallMaxPrice) ? $overallMaxPrice : 1000;
                $showPriceMin = isset($priceMin) && $priceMin != $defaultMinPrice;
                $showPriceMax = isset($priceMax) && $priceMax != $defaultMaxPrice;
            @endphp
            @if($showPriceMin || $showPriceMax)
                <span class="badge bg-light text-dark border">
                    @if($showPriceMin && $showPriceMax)
                        Price from €{{ $priceMin }} to €{{ $priceMax }}
                    @elseif($showPriceMin)
                        Price from €{{ $priceMin }}
                    @elseif($showPriceMax)
                        Price up to €{{ $priceMax }}
                    @endif
                    <button type="button" class="btn-close ms-2" data-filter-type="price_range" data-filter-id="price_range"></button>
                </span>
            @endif
        </div>
    </div>

    @include('pages.guidings.partials.guiding-card', ['guidings' => $guidings])
    {!! $guidings->links('vendor.pagination.default') !!}
@endif

@if(count($otherguidings) && ( request()->placeLat != null || request()->placelat != "" && request()->placeLng != null || request()->placelng != ""))
<hr>
<div class="my-0 section-title">
    <h2 class="h4 text-dark fw-bolder">{{ request()->placeLat != null || request()->placelat != "" && request()->placeLng != null || request()->placelng != "" ? translate('Additional Fishing Tour close to') . ' ' . request()->place : translate('Additional Fishing Tour') }}</h2> 
</div>
<br>
<div class="row">
    <div class="col-lg-12 col-sm-12">
        <div class="tours-list__right">
            <div class="tours-list__inner">
                @include('pages.guidings.partials.guiding-card', ['guidings' => $otherguidings])
            </div>
        </div>
    </div>
</div>
@endif 