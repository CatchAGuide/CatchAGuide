<div>
    {{-- Be like water. --}}

        @foreach($guidings as $guiding)
        <div class="row m-0 mb-2">
            <div class="col-md-12">
                <div class="row p-2 border shadow-sm bg-white rounded">
                    <div class="col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 mt-1">
                        <div id="carouselExampleControls-{{$guiding->id}}" class="carousel slide" data-bs-ride="carousel" data-bs-interval="false">
                            <div class="carousel-inner">
                                @if(count(get_galleries_image_link($guiding)))
                                    @foreach(get_galleries_image_link($guiding) as $index => $gallery_image_link)
                                        <div class="carousel-item @if($index == 0) active @endif">
                                            <img  class="d-block w-100" src="{{$gallery_image_link}}">
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
                    <div class="col-12 col-sm-12 col-md-6 col-lg-6 col-xl-6 col-xxl-6 mt-1">
                        <h5 class="fw-bolder text-truncate"><a class="text-dark" href="{{ route('guidings.show',[$guiding->id,$guiding->slug]) }}">{{translate($guiding->title)}}</a></h5>
                        <div class="ratings mr-2 color-primary my-1" style="font-size:0.80rem">
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
                        </div>
                        <span class="text-center" style="font-size:1rem;color:rgb(28, 28, 28)"><i class="fas fa-map-marker-alt me-2"></i>{{ translate($guiding->location) }}</span>
                        <div class="row mt-2">
                            <div class="col-6 col-sm-6 col-md-6">
                                <div class="d-flex align-items-center">
                                    <div>
                                        <img src="{{asset('assets/images/icons/fish.png')}}" height="20" width="20" alt="" />
                                    </div>
                                    <div class="mx-2">
                                        <div class="tours-list__content__trait__text" style="font-size:0.75rem">
                                            @php
                                            $guidingTargets = $guiding->guidingTargets->pluck('name')->toArray();
                                            if(app()->getLocale() == 'en'){
                                                $guidingTargets =  $guiding->guidingTargets->pluck('name_en')->toArray();
                                            }
                                            @endphp

                                            @if(!empty($guidingTargets))
                                                {{ implode(', ', $guidingTargets) }}
                                            @else
                                            {{ translate($guiding->threeTargets()) }}
                                            {{$guiding->target_fish_sonstiges ? " & " . translate($guiding->target_fish_sonstiges) : ""}}
                                            @endif
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-sm-6 col-md-6">
                                <div class="d-flex align-items-center">
                                    <div>
                                        <img src="{{asset('assets/images/icons/water-waves.png')}}" height="20" width="20" alt="" />
                                    </div>
                                    <div class="mx-2">
                                        <div class="tours-list__content__trait__text" style="font-size:0.75rem">
                                            @php
                                            $guidingWaters = $guiding->guidingWaters->pluck('name')->toArray();
                                            if(app()->getLocale() == 'en'){
                                                $guidingWaters =  $guiding->guidingWaters->pluck('name_en')->toArray();
                                            }
                                            @endphp

                                            @if(!empty($guidingWaters))
                                                {{ implode(', ', $guidingWaters) }}
                                            @else
                                            {{ translate($guiding->threeWaters()) }}
                                            {{$guiding->water_sonstiges ? " & " . translate($guiding->water_sonstiges) : ""}}
                                            @endif
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-sm-6 col-md-6">
                                <div class="d-flex align-items-center">
                                    <div>
                                        <img src="{{asset('assets/images/icons/fishing-tool.png')}}" height="20" width="20" alt="" />
                                    </div>
                                    <div class="mx-2">
                                        <div class="tours-list__content__trait__text" style="font-size:0.75rem">
                                            @php
                                            $fishingtype = null;
                                            if($guiding->fishingTypes){
                                                if(app()->getLocale() == 'en'){
                                                    $fishingtype = $guiding->fishingTypes->name_en;
                                                }else{
                                                   $fishingtype =  $guiding->fishingTypes->name;
                                                }
                                            }

                                            @endphp

                                            @if($fishingtype) {{$fishingtype}}  @else {{$guiding->fishing_type}}@endif
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-sm-6 col-md-6">
                                <div class="d-flex align-items-center">
                                    <div>
                                        <img src="{{asset('assets/images/icons/fishing.png')}}" height="20" width="20" alt="" />
                                    </div>
                                    <div class="mx-2">
                                        <div class="tours-list__content__trait__text" style="font-size:0.75rem">
                                            @php
                                            $guidingMethods = $guiding->guidingMethods->pluck('name')->toArray();
                                            if(app()->getLocale() == 'en'){
                                                $guidingMethods =  $guiding->guidingMethods->pluck('name_en')->toArray();
                                            }
                                            @endphp

                                            @if(!empty($guidingMethods))
                                                {{ implode(', ', $guidingMethods) }}
                                            @else
                                            {{ $guiding->threeMethods() }}
                                            {{$guiding->methods_sonstiges && $guiding->threeMethods() > 0 ? " & " . translate($guiding->methods_sonstiges) : null}}
                                            @endif
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-sm-6 col-md-6">
                                <div class="d-flex align-items-center">
                                    <div>
                                        <img src="{{asset('assets/images/icons/fishing-man.png')}}" height="20" width="20" alt="" />
                                    </div>
                                    <div class="mx-2">
                                        <div class="tours-list__content__trait__text" style="font-size:0.75rem">
                                            @php
                                            $whereFishing = null;
                                            if($guiding->fishingFrom){
                                                if(app()->getLocale() == 'en'){
                                                    $whereFishing = $guiding->fishingFrom->name_en;
                                                }else{
                                                   $whereFishing =  $guiding->fishingFrom->name;
                                                }
                                            }

                                            @endphp
                                            @if($whereFishing) {{$whereFishing}} @else {{$guiding->fishing_from}} @endif
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-sm-6 col-md-6">
                                <div class="d-flex align-items-center mt-2">
                                    <div class="icon-small">
                                        <span class="icon-user"></span>
                                    </div>
                                    <div class="mx-2" style="font-size:0.75rem">
                                    {{ $guiding->max_guests }} @if($guiding->max_guests != 1) {{translate('Personen')}} @else {{translate('Person')}} @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-sm-6 col-md-6">
                                <div class="d-flex align-items-center">
                                    <div>
                                        <img src="{{asset('assets/images/icons/clock.svg')}}" height="20" width="20" alt="" />
                                    </div>
                                    <div class="mx-2" style="font-size:0.75rem">
                                        {{ $guiding->duration }} @if($guiding->duration != 1) {{translate('Stunden')}} @else {{translate('Stunde')}} @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mt-3">
                                    @if($guiding->user->profil_image)
                                    <img class="center-block rounded-circle"
                                    src="{{asset('images/'. $guiding->user->profil_image)}}" alt="" width="20"
                                    height="20">
                                    @else
                                        <img class="center-block rounded-circle"
                                            src="{{asset('images/placeholder_guide.jpg')}}" alt="" width="20"
                                            height="20">
                                    @endif
                                    <span class="color-primary" style="font-size:1rem">{{$guiding->user->firstname}}</span>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="col-12 col-sm-12 col-md-2 col-lg-3 col-xl-2 col-xxl-2  mt-3">
                        <div class="text-center">
                            <h5 class="mr-1 color-primary fw-bold text-center">@lang('message.from') {{$guiding->price}}€</h4>
                        </div>
                        <div class="d-flex flex-column mt-4">
                            <a class="btn theme-primary btn-theme-new btn-sm" href="{{ route('guidings.show',[$guiding->id,$guiding->slug]) }}">Details</a>
                            <a class="btn btn-sm mt-2   {{ (auth()->check() ? (auth()->user()->isWishItem($guiding->id) ? 'btn-danger' : 'btn-outline-theme ') : 'btn-outline-theme') }}" href="{{ route('wishlist.add-or-remove', $guiding->id) }}">
                                {{ (auth()->check() ? (auth()->user()->isWishItem($guiding->id) ? 'Added to Favorites' : 'Add to Favorites') : 'Add to Favorites') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach

        @if($guidings->hasMorePages())
            <div class="d-flex justify-content-center align-items-center">
                <button wire:loading.remove class="btn theme-primary" wire:click="loadMore()" wire:loading.class="hidden" wire:loading.attr="disabled">
                    View more
                </button>
                <div wire:loading.delay>
                    <div class="d-flex align-items-center">
                        <div>
                            Please wait...
                        </div>
                        <span class="mx-2" id="cover-spin"></span>
                    </div>
                </div>


            </div>
        @endif

</div>
