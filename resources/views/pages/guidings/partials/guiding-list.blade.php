@if(count($guidings))
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
                                <div class="guidings-item-ratings">
                                    <div class="ratings-score">
                                        <span class="text-warning">★</span>
                                        <span>{{$guiding->user->average_rating()}} </span>
                                        /5 ({{ $guiding->user->received_ratings->count() }} review/s)
                                    </div>
                                </div>
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
                                        <div class="guidings-item-ratings">
                                        <div class="ratings-score">
                                                <span class="text-warning">★</span>
                                                <span>{{$otherguide->user->average_rating()}} </span>
                                                /5 ({{ $otherguide->user->received_ratings->count() }} review/s)
                                            </div>
                                        </div>
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
                                                        $otherguideTargets = collect($otherguide->getTargetFishNames())->pluck('name')->toArray();
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