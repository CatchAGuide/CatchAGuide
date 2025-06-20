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

    @foreach($guidings as $guiding)
    <div class="row m-0 mb-2 guiding-list-item">
        <div class="col-md-12">
            <div class="row p-2 border shadow-sm bg-white rounded">
                <div class="col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 mt-1 p-0">
                    <div id="carouselExampleControls-{{$guiding->id}}" class="carousel slide" data-bs-ride="carousel" data-bs-interval="false">
                        <div class="carousel-inner">
                            @if(count(get_galleries_image_link($guiding)))
                                @foreach(get_galleries_image_link($guiding) as $index => $gallery_image_link)
                                    <div class="carousel-item @if($index == 0) active @endif">
                                        <img  class="d-block" src="{{asset($gallery_image_link)}}">
                                    </div>
                                @endforeach
                            @endif
                        </div>

                        @if(count(get_galleries_image_link($guiding)) > 1)
                            <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleControls-{{$guiding->id}}" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Previous</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleControls-{{$guiding->id}}" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Next</span>
                            </button>
                        @endif
                    </div>
                </div>
                <div class="guiding-item-desc col-12 col-sm-12 col-md-8 col-lg-8 col-xl-8 col-xxl-8 p-2 px-md-3 pt-md-2">
                    <a href="{{ route('guidings.show', [$guiding->id, $guiding->slug])}}">
                        <div class="guidings-item">
                            <div class="guidings-item-title">
                                <h5 class="fw-bolder text-truncate">{{ Str::limit(translate($guiding->title), 70) }}</h5>
                                <span class="truncate"><i class="fas fa-map-marker-alt me-2"></i>{{ $guiding->location }}</span>                                      
                            </div>
                            @if ($guiding->user->average_rating())
                                <div class="ave-reviews-row">
                                    <div class="ratings-score">
                                        <span class="rating-value">{{number_format($guiding->user->average_rating(), 1)}}</span>
                                    </div> 
                                    <span class="mb-1">
                                        {{-- ({{$guiding->user->received_ratings->count()}} reviews) --}}
                                        ({{$guiding->user->reviews->count()}} reviews)
                                    </span>
                                </div>
                            @else
                                <div class="no-reviews"><span>@lang('guidings.no_reviews')</span></div>
                            @endif
                        </div>
                        <div class="guidings-item-icon">
                            <div class="guidings-icon-container"> 
                                <img src="{{asset('assets/images/icons/clock-new.svg')}}" height="20" width="20" alt="" />
                                <div class="">
                                    {{$guiding->duration}} {{ $guiding->duration_type == 'multi_day' ? __('guidings.days') : __('guidings.hours') }}
                                </div>
                            </div>
                            <div class="guidings-icon-container"> 
                                <img src="{{asset('assets/images/icons/user-new.svg')}}" height="20" width="20" alt="" />
                                <div class="">
                                    {{ $guiding->max_guests }} @if($guiding->max_guests != 1) {{translate('Personen')}} @else {{translate('Person')}} @endif
                                </div>
                            </div>
                            <div class="guidings-icon-container"> 
                                <img src="{{asset('assets/images/icons/fish-new.svg')}}" height="20" width="20" alt="" />
                                <div class="">
                                    <div class="tours-list__content__trait__text" >
                                        @php
                                        $guidingTargets = collect($guiding->getTargetFishNames())->pluck('name')->toArray();
                                        @endphp
                                        
                                        @if(!empty($guidingTargets))
                                            {{ implode(', ', $guidingTargets) }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="guidings-icon-container">
                                <img src="{{asset('assets/images/icons/fishing-tool-new.svg')}}" height="20" width="20" alt="" />
                                <div class="">
                                    <div class="tours-list__content__trait__text" >
                                        {{$guiding->is_boat ? ($guiding->boatType && $guiding->boatType->name !== null ? $guiding->boatType->name : __('guidings.boat')) : __('guidings.shore')}}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="inclusions-price">
                            <div class="guidings-inclusions-container">
                                @if(!empty($guiding->getInclusionNames()))
                                <div class="guidings-included">
                                    <strong>@lang('guidings.Whats_Included')</strong>
                                    <div class="inclusions-list">
                                        @php
                                            $inclussions = $guiding->getInclusionNames();
                                            $maxToShow = 3; // Maximum number of inclusions to display
                                        @endphp

                                        @foreach ($inclussions as $index => $inclussion)
                                            @if ($index < $maxToShow)
                                                <span class="inclusion-item"><i class="fa fa-check"></i>{{ $inclussion['name'] }}</span>
                                            @endif
                                        @endforeach

                                        @if (count($inclussions) > $maxToShow)
                                            <span class="inclusion-item">+{{ count($inclussions) - $maxToShow }} more</span>
                                        @endif
                                    </div>
                                </div>
                                @endif
                            </div>
                            <div class="guiding-item-price">
                                <h5 class="mr-1 fw-bold text-end"><span class="p-1">@lang('message.from') {{$guiding->getLowestPrice()}}€ p.P.</span></h5>
                                <div class="d-none d-flex flex-column mt-4">
                                </div>
                            </div>
                        </div>    
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endforeach
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
                @foreach($otherguidings as $otherguide)
                <div class="row m-0 mb-2 guiding-list-item">
                    <div class="col-md-12">
                        <div class="row p-2 border shadow-sm bg-white rounded">
                            <div class="col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 mt-1 p-0">
                                <div id="carouselExampleControls-{{$otherguide->id}}" class="carousel slide" data-bs-ride="carousel" data-bs-interval="false">
                                    <div class="carousel-inner">
                                        @if(count(get_galleries_image_link($otherguide)))
                                            @foreach(get_galleries_image_link($otherguide) as $index => $gallery_image_link)
                                                <div class="carousel-item @if($index == 0) active @endif">
                                                    <img  class="d-block" src="{{asset($gallery_image_link)}}">
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>

                                    @if(count(get_galleries_image_link($otherguide)) > 1)
                                        <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleControls-{{$otherguide->id}}" data-bs-slide="prev">
                                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Previous</span>
                                        </button>
                                        <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleControls-{{$otherguide->id}}" data-bs-slide="next">
                                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                            <span class="visually-hidden">Next</span>
                                        </button>
                                    @endif
                                </div>
                        
                            </div>
                            <div class="guiding-item-desc col-12 col-sm-12 col-md-8 col-lg-8 col-xl-8 col-xxl-8 p-2 px-md-3 pt-md-2">
                                <a href="{{ route('guidings.show', [$otherguide->id, $otherguide->slug])}}">
                                    <div class="guidings-item">
                                        <div class="guidings-item-title">
                                        <h5 class="fw-bolder text-truncate">{{ Str::limit(translate($otherguide->title), 70) }}</h5>
                                        <span class="truncate"><i class="fas fa-map-marker-alt me-2"></i>{{ $otherguide->location }}</span>                                      
                                        </div>
                                        @if ($otherguide->user->average_rating())
                                        <div class="ave-reviews-row">
                                            <div class="ratings-score">
                                                <span class="rating-value">{{number_format($otherguide->user->average_rating(), 1)}}</span>
                                            </div> 
                                            <span class="mb-1">
                                                {{-- ({{$otherguide->user->received_ratings->count()}} reviews) --}}
                                                ({{$otherguide->user->reviews->count()}} reviews)
                                            </span>
                                        </div>
                                        @else
                                            <div class="no-reviews"><span>@lang('guidings.no_reviews')</span></div>
                                        @endif
                                    </div>
                                    <div class="guidings-item-icon">
                                        <div class="guidings-icon-container"> 
                                                    <img src="{{asset('assets/images/icons/clock-new.svg')}}" height="20" width="20" alt="" />
                                                <div class="">
                                                    {{$otherguide->duration}} {{ $otherguide->duration_type == 'multi_day' ? __('guidings.days') : __('guidings.hours') }}
                                                </div>
                                        </div>
                                        <div class="guidings-icon-container"> 
                                                <img src="{{asset('assets/images/icons/user-new.svg')}}" height="20" width="20" alt="" />
                                                <div class="">
                                                {{ $otherguide->max_guests }} @if($otherguide->max_guests != 1) {{translate('Personen')}} @else {{translate('Person')}} @endif
                                                </div>
                                        </div>
                                        <div class="guidings-icon-container"> 
                                                    <img src="{{asset('assets/images/icons/fish-new.svg')}}" height="20" width="20" alt="" />
                                                <div class="">
                                                    <div class="tours-list__content__trait__text" >
                                                        @php
                                                        $otherguideTargets = collect($otherguide->cached_target_fish_names ?? $otherguide->getTargetFishNames($targetsMap ?? null))->pluck('name')->toArray();
                                                        @endphp
                                                        
                                                        @if(!empty($otherguideTargets))
                                                            {{ implode(', ', $otherguideTargets) }}
                                                        @endif
                                                    </div>
                                                
                                                </div>
                                        </div>
                                        <div class="guidings-icon-container">
                                                    <img src="{{asset('assets/images/icons/fishing-tool-new.svg')}}" height="20" width="20" alt="" />
                                                <div class="">
                                                    <div class="tours-list__content__trait__text" >
                                                    {{$otherguide->is_boat ? ($otherguide->boatType && $otherguide->boatType->name !== null ? $otherguide->boatType->name : __('guidings.boat')) : __('guidings.shore')}}
                                                    </div>
                                                
                                                </div>
                                        </div>
                                    </div>
                                    <div class="inclusions-price">
                                        <div class="guidings-inclusions-container">
                                            @if(!empty($otherguide->getInclusionNames()))
                                            <div class="guidings-included">
                                                <strong>@lang('guidings.Whats_Included')</strong>
                                                <div class="inclusions-list">
                                                    @php
                                                        $inclussions = $otherguide->getInclusionNames();
                                                        $maxToShow = 3; // Maximum number of inclusions to display
                                                    @endphp

                                                    @foreach ($inclussions as $index => $inclussion)
                                                        @if ($index < $maxToShow)
                                                            <span class="inclusion-item"><i class="fa fa-check"></i>{{ $inclussion['name'] }}</span>
                                                        @endif
                                                    @endforeach

                                                    @if (count($inclussions) > $maxToShow)
                                                        <span class="inclusion-item">+{{ count($inclussions) - $maxToShow }} more</span>
                                                    @endif
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                        <div class="guiding-item-price">
                                            <h5 class="mr-1 fw-bold text-end"><span class="p-1">@lang('message.from') {{$otherguide->getLowestPrice()}}€ p.P.</span></h5>
                                            <div class="d-none d-flex flex-column mt-4">
                                            </div>
                                        </div>
                                    </div>    
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif 