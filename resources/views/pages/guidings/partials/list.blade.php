<div class="col-xxl-8 col-lg-6">
    <div class="tours-list__right">
        <div class="tours-list__inner">
            @foreach($guidings as $guiding)

                <!--Tours List Single-->
                    <div class="tours-list__single" style="color: black; display: flex;" >
                            <a class="tours-list__img" title="Guide mit Slug {{ $guiding->slug }} aufmachen" href="{{ route('guidings.show',[$guiding->id,$guiding->slug]) }}">
                                <div id="carouselExampleControls-{{$guiding->id}}" class="carousel slide" data-bs-ride="carousel" data-bs-interval="false">
                                    <div class="carousel-inner">
                                        @foreach(app('guiding')->getImagesUrl($guiding) as $limgKey => $limg)
                                            <div class="carousel-item  @if($limgKey == 'image_0') active @endif ">
                                                <img  class="d-block w-100" src="{{$limg}}">
                                            </div>
                                        @endforeach
                                    </div>

                                    @if(count(app('guiding')->getImagesUrl($guiding)) > 1)
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
                            </a>

                            <div class="tours-list__icon">
                                <a href="{{ route('wishlist.add-or-remove', $guiding->id) }}">
                                    <i class="fa fa-heart {{ (auth()->check() ? (auth()->user()->isWishItem($guiding->id) ? 'text-danger' : '') : '') }}"></i>
                                </a>
                            </div>

                            <a class="tours-list__content" title="Guide mit Slug {{ $guiding->slug }} aufmachen" href="{{ route('guidings.show', [$guiding->id,$guiding->slug]) }}" >
                                <span>{{ translate($guiding->location) }}</span>
                                <div class="tours-list__body">
                                    <h3 class="tours-list__title">{{ translate( $guiding->title ) }}</h3>

                                    <div class="tours-list__content__traits">

                                        <div class="tours-list__content__trait">
                                            <img src="{{asset('assets/images/icons/fish.png')}}" height="20" width="20" alt="" />
                                            <div class="tours-list__content__trait__text">
                                                @php
                                                $guidingTargets = $guiding->guidingTargets->pluck('name')->toArray();
                                                @endphp
                                                
                                                @if(!empty($guidingTargets))
                                                    {{ implode(', ', $guidingTargets) }}
                                                @else
                                                {{ translate($guiding->threeTargets()) }}
                                                {{$guiding->target_fish_sonstiges ? " & " . translate($guiding->target_fish_sonstiges) : ""}}
                                                @endif
                                            </div>
                                        </div>
                                
                        
                                        <div class="tours-list__content__trait">
                                            <img src="{{asset('assets/images/icons/water-waves.png')}}" height="20" width="20" alt="" />
                                            <div class="tours-list__content__trait__text">
                                                @php
                                                $guidingWaters = $guiding->guidingWaters->pluck('name')->toArray();
                                                @endphp
                                                
                                                @if(!empty($guidingWaters))
                                                    {{ implode(', ', $guidingWaters) }}
                                                @else
                                                {{ translate($guiding->threeWaters()) }}
                                                {{$guiding->water_sonstiges ? " & " . translate($guiding->water_sonstiges) : ""}}
                                                @endif
                                            </div>
                                        </div>
                                
                                        <div class="tours-list__content__trait">
                                            <img src="{{asset('assets/images/icons/fishing-tool.png')}}" height="20" width="20" alt="" />
                                            <div class="tours-list__content__trait__text">
                                                @if($guiding->fishingTypes){{ $guiding->fishingTypes->name}} @else {{$guiding->fishing_type}}@endif
                                            </div>
                                        </div>

                                        <div class="tours-list__content__trait">
                                            <img src="{{asset('assets/images/icons/fishing.png')}}" height="20" width="20" alt="" />
                                            <div class="tours-list__content__trait__text">
                                                @php
                                                $guidingMethods = $guiding->guidingMethods->pluck('name')->toArray();
                                                @endphp
                                                
                                                @if(!empty($guidingMethods))
                                                    {{ implode(', ', $guidingMethods) }}
                                                @else
                                                {{ $guiding->threeMethods() }}
                                                {{$guiding->methods_sonstiges && $guiding->threeMethods() > 0 ? " & " . translate($guiding->methods_sonstiges) : null}}
                                                @endif
                                            </div>
                                        </div>
                                    
                                        <div class="tours-list__content__trait">
                                            <img src="{{asset('assets/images/icons/fishing-man.png')}}" height="20" width="20" alt="" />
                                            <div class="tours-list__content__trait__text"> 
                                                {{-- {{ translate($guiding->fishing_from) }} --}}
                                                @if($guiding->fishingFrom){{ $guiding->fishingFrom->name}} @else {{$guiding->fishing_from}} @endif
                                            </div>
                                        </div>

                                        <div class="tours-list__content__trait">
                                            <div class="icon-small" style="font-size: 1.25rem;">
                                                <span class="icon-user"></span>
                                            </div>
                                            <div class="tours-list__content__trait__text">
                                            {{ $guiding->max_guests }} @if($guiding->max_guests != 1) {{translate('Personen')}} @else {{translate('Person')}} @endif
                                            </div>
                                        </div>

                                        <div class="tours-list__content__trait">
                                            <img src="{{asset('assets/images/icons/clock.svg')}}" height="20" width="20" alt="" />
                                            <div class="tours-list__content__trait__text">{{ $guiding->duration }} @if($guiding->duration != 1) {{translate('Stunden')}} @else {{translate('Stunde')}} @endif</div>
                                        </div>

                                    </div>

                                    <div class="tours-list__rates">
                                        <p class="tours-list__rate">
                                            {{$guiding->user->firstname}}
                                            @if(count($guiding->user->received_ratings) > 0)

                                                @switch(two($guiding->user->average_rating()))
                                                    @case(two($guiding->user->average_rating()) >= 5)
                                                        - {{one($guiding->user->average_rating())}}
                                                        <i class="fa fa-star"></i>
                                                        <i class="fa fa-star"></i>
                                                        <i class="fa fa-star"></i>
                                                        <i class="fa fa-star"></i>
                                                        <i class="fa fa-star"></i>
                                                        @break
                                                    @case(two($guiding->user->average_rating()) >= 4.5)
                                                        - {{one($guiding->user->average_rating())}}
                                                        <i class="fa fa-star"></i>
                                                        <i class="fa fa-star"></i>
                                                        <i class="fa fa-star"></i>
                                                        <i class="fa fa-star"></i>
                                                        <i class="fa fa-star-half"></i>
                                                        @break
                                                    @case(two($guiding->user->average_rating()) >= 4)
                                                        - {{one($guiding->user->average_rating())}}
                                                        <i class="fa fa-star"></i>
                                                        <i class="fa fa-star"></i>
                                                        <i class="fa fa-star"></i>
                                                        <i class="fa fa-star"></i>
                                                        @break
                                                    @case(two($guiding->user->average_rating()) >= 3.5)
                                                        - {{one($guiding->user->average_rating())}}
                                                        <i class="fa fa-star"></i>
                                                        <i class="fa fa-star"></i>
                                                        <i class="fa fa-star"></i>
                                                        <i class="fa fa-star-half"></i>
                                                        @break
                                                    @case(two($guiding->user->average_rating()) >= 3)
                                                        - {{one($guiding->user->average_rating())}}
                                                        <i class="fa fa-star"></i>
                                                        <i class="fa fa-star"></i>
                                                        <i class="fa fa-star"></i>
                                                        @break
                                                    @case(two($guiding->user->average_rating()) >= 2.5)
                                                        - {{one($guiding->user->average_rating())}}
                                                        <i class="fa fa-star"></i>
                                                        <i class="fa fa-star"></i>
                                                        <i class="fa fa-star-half"></i>
                                                        @break
                                                    @case(two($guiding->user->average_rating()) >= 2)
                                                        - {{one($guiding->user->average_rating())}}
                                                        <i class="fa fa-star"></i>
                                                        <i class="fa fa-star"></i>
                                                        @break
                                                    @case(two($guiding->user->average_rating()) >= 1.5)
                                                        - {{one($guiding->user->average_rating())}}
                                                        <i class="fa fa-star"></i>
                                                        <i class="fa fa-star-half"></i>
                                                        @break
                                                    @case(two($guiding->user->average_rating()) >= 1)
                                                        - {{one($guiding->user->average_rating())}}
                                                        <i class="fa fa-star"></i>
                                                        @break
                                                    @default
                                                        - {{one($guiding->user->average_rating())}}
                                                        <i class="fa fa-star"></i>
                                                @endswitch

                                                @if(count($guiding->user->received_ratings) >= 2) 
                                                    ({{count($guiding->user->received_ratings)}} Bewertungen)
                                                @else 
                                                    ({{count($guiding->user->received_ratings)}} Bewertung)
                                                @endif

                                            @endif                                                  
                                        </p>
                                        <p class="tours-list__rate" style="text-align: right;"><span>@lang('message.from') {{$guiding->price}}€</span></p>
                                    </div>
                                
                                </div>
                            </a>
                        

                    </div>
                    <!--Tours List Single-->
                @endforeach
        </div>
    </div>
</div>