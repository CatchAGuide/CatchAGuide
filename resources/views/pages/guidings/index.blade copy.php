@extends('layouts.app')

@section('title', __('message.guidings_meta_title'))
@section('description',__('message.guidings_meta_description'))
@section('css_after')
{{-- <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" /> --}}

    <style>
        .fixedmap {
            position: fixed;
            right: 0px;
            bottom: 10%;
            height: 70%;
        }
        a:hover {
            color: black;
        }
        .page-header-bg-overly {
            background-color: rgba(0,0,0,0);
        }
        .pager-header-bg {
            filter: none !important;
        }

        .carousel .carousel-control-next, .carousel .carousel-control-prev {
            top: 50%;
            transform: translateY(-50%);
        }

        .carousel.slide {
            max-height: 265px;
        }

        .carousel .carousel-control-next {
            right: 0;
        }

        .carousel .carousel-control-prev {
            left: 0;
        }

        .carousel-item {
            min-height: 50px;
        }
        .carousel .carousel-control-next, .carousel .carousel-control-prev {
            padding: 3px;
            width: 24px;
        }

        .carousel-item-next, .carousel-item-prev, .carousel-item.active {
            display: flex;
        }

        .carousel-control-prev-icon,
        .carousel-control-next-icon {
            width: 10px;
            height: 10px;
        }

        .carousel .carousel-control-next, .carousel .carousel-control-prev {
            padding: 3px;
            width: 24px;
        }
        .form-custom-input{
        /* border: solid #e8604c 1px; */
        border: 1px solid #d4d5d6;
        border-radius: 5px;
        padding: 8px 10px;
        width:100%;
        }
        .form-control:focus{
            /* border: solid #e8604c 1px !important; */
           box-shadow: none;
        }
        .form-custom-input:focus-visible{
            /* border: solid #e8604c 1px !important; */
            border:0;
            outline:solid #e8604c 1px !important;
        }
        li.select2-selection__choice{
            background-color: #E8604C !important;
            color: #fff !important;
            border: 0 !important;
            font-size:14px;
            vertical-align: middle !important;
            margin-top:0 !important;
         
        }
        button.select2-selection__choice__remove{
            border: 0 !important;
            color: #fff !important;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover, .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:focus{
            background:none;
        }
        span.select2-selection.select2-selection--multiple{
        border: 1px solid #d4d5d6;
        border-radius: 5px;
        padding: 7px 10px;
        }
        .select2-selection--multiple:before {
        content: "";
        position: absolute;
        right: 7px;
        top: 42%;
        border-top: 5px solid #888;
        border-left: 4px solid transparent;
        border-right: 4px solid transparent;
        }

        #toggleFilterBtn{
            display:none;
        }
        .sort-row .form-select{
            width: auto;
        }

        @media only screen and (max-width: 600px) {
            #toggleFilterBtn{
                display:block;
            }
            #filterContainer{
                display:none;
            }
            
        }
        
      

    
    </style>

@endsection

@section('content')
    <section class="page-header">
        <div class="page-header__top">
            <div class="page-header-bg"
                 style="background-image: url({{asset('assets/images/allguidings.jpg')}})">
            </div>
            <div class="page-header-bg-overly"></div>
            <div class="container">
                <div class="page-header__top-inner">
                    <h2>{{ucwords(isset($place) ? translate('Alle Guidings bei ') . $place : translate('Alle Guidings') )}}</h2>
                </div>
            </div>
        </div>
        <div class="page-header__bottom">
            <div class="container">
                <div class="page-header__bottom-inner">
                    <ul class="thm-breadcrumb list-unstyled">
                        <li><a href="{{ route('welcome') }}">@lang('message.home')</a></li>
                        <li><span>&#183;</span></li>
                        <li class="active">
                            {{ucwords( isset($place) ? translate('Alle Guidings bei ') . $place : translate('Alle Guidings'))}}
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!--Tours List Start-->
    <section class="tours-list" style="padding-top: 20px;">

        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <form id="filterContainer" action="{{route('guidings.index')}}" method="get">
                        <div class="row shadow-sm rounded m-0">
                            <div class="col-md-10 p-3">
                                <div class="row">
                            <div class="col-md-6">
                                <div class="form-group my-1">
                                    <label for="place">@lang('message.location')</label>
                                  <input  id="searchPlace" name="place" type="text" value="{{ request()->get('place') ? request()->get('place') : null }}" class="form-control form-custom-input" placeholder="@lang('message.enter-location')"  autocomplete="on">
                                  <input type="hidden" id="placeLat" value="{{ request()->get('placeLat') ? request()->get('placeLat') : null }}" name="placeLat"/>
                                  <input type="hidden" id="placeLng" value="{{ request()->get('placeLng') ? request()->get('placeLng') : null }}" name="placeLng"/>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group my-1">
                                          <label for="radius">Radius</label>
                                          <select id="radius" class="form-control form-custom-input form-select" name="radius">
                                              <option value="" disabled selected>@lang('message.choose')...</option>
                                              <option value="50" {{ request()->get('radius') ? request()->get('radius') == 50 ? 'selected' : null : null }}>50 miles</option>
                                              <option value="100" {{ request()->get('radius') ? request()->get('radius') == 100 ? 'selected' : null : null }}>100 miles</option>
                                              <option value="150" {{ request()->get('radius') ? request()->get('radius') == 150 ? 'selected' : null : null }}>150 miles</option>
                                              <option value="250" {{ request()->get('radius') ? request()->get('radius') == 250 ? 'selected' : null : null }}>250 miles</option>
                                              <option value="500" {{ request()->get('radius') ? request()->get('radius') == 500 ? 'selected' : null : null }}>500 miles</option>
                                          </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group my-1">
                                            <label for="radius">Number of guests</label>
                                            <select id="radius" class="form-control form-custom-input form-select" name="num_guests">
                                                <option value="" disabled selected>@lang('message.choose')...</option>
                                                <option value="1" {{ request()->get('num_guests') ? request()->get('num_guests') == 1 ? 'selected' : null : null }}>1</option>
                                                <option value="2" {{ request()->get('num_guests') ? request()->get('num_guests') == 2 ? 'selected' : null : null }}>2</option>
                                                <option value="3" {{ request()->get('num_guests') ? request()->get('num_guests') == 3 ? 'selected' : null : null }}>3</option>
                                                <option value="4" {{ request()->get('num_guests') ? request()->get('num_guests') == 4 ? 'selected' : null : null }}>4</option>
                                                <option value="5" {{ request()->get('num_guests') ? request()->get('num_guests') == 5 ? 'selected' : null : null }}>5</option>
                                            </select>
                                          </div>
                                    </div>
                                </div>
                            </div>
                         
                                    <div class="col-md-4">
                                        <div class="form-group my-1">
                                            <label for="target_fish">@lang('message.target-fish')</label>
                                            <select class="form-control form-custom-input form-select" id="target_fish" name="target_fish[]" style="width:100%">
        
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4">
                                        <div class="form-group my-1">
                                            <label for="water">@lang('message.body-type')</label>
                                            <select class="form-control form-select" id="water" name="water[]" style="width:100%">
                                    
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group my-1">
                                            <label for="methods">@lang('message.fishing-technique')</label>
                                            <select class="form-control form-select" id="methods" name="methods[]" style="width:100%">
                                           
                                            </select>
                                        </div>
                                    </div>
                                    
                                </div>
        
                            </div>
                            <div class="col-md-2 p-0">
                                <button class="btn btn-sm theme-primary w-100 h-100" style="font-size:1rem">@lang('message.Search')</button>    
                            </div>
        
                        </div>
        
                    </form>
                </div>
                <div class="col-6 col-sm-4 d-flex align-items-center my-2">
                    <div class="d-flex justify-content-start">
                        <button  id="toggleFilterBtn" class="btn outline-none"><span class="fw-bold text-decoration-underline">Filters</span><i class="fa fa-filter color-primary" aria-hidden="true"></i></button>
                    </div>
                </div>
                <div class="col-6 col-sm-4 my-2">
                    <form id="form-sortby" action="{{route('guidings.index')}}" method="get">
                        <div class="row-sort">
                            <div class="d-flex flex-sm-row flex-column align-items-sm-center align-items-stretch my-2 justify-content-end">
                                <div class="d-flex align-items-center justify-content-end">
                                    <label class="fs-sm me-2 pe-1 text-nowrap" for="sortby"><i class="fi-arrows-sort text-muted mt-n1 me-2"></i>Sort by:</label>
                                    <select class="form-select form-select-sm" name="sortby" id="sortby">
                                        <option value="" disabled selected>@lang('message.choose')...</option>
                                        <option value="newest" {{request()->get('sortby') ? request()->get('sortby') == 'newest' ? 'selected' : '' : '' }}>Newest</option>
                                        <option value="price-asc" {{request()->get('sortby') ? request()->get('sortby') == 'price-asc' ? 'selected' : '' : '' }}>Low to High Price</option>
                                        <option value="price-desc" {{request()->get('sortby') ? request()->get('sortby') == 'price-desc' ? 'selected' : '' : '' }}>High to Low Price</option>
                                    </select>
                                </div>
                            </div>
                        </div>

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
            </div>
        {!! $guidings->links('vendor.pagination.default') !!}
            <div class="row column-reverse-row-normal">
                @if(count($guidings) > 0)
                

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
                                                    {{-- @if($guiding->galleries->isEmpty())
                                                        <img class=""
                                                             src="{{asset('images/' . $guiding->thumbnail_path)}}"
                                                             style="width: 100%; object-fit: cover;">
                                                    @else
                                                        <div id="carouselExampleControls-{{$guiding->id}}" class="carousel slide" data-bs-ride="carousel" data-bs-interval="false">
                                                            <div class="carousel-inner">
                                                                @foreach(app('guiding')->getImagesUrl($guiding) as $limgKey => $image)
                                                                    <div class="carousel-item">
                                                                        <img src="{{$image}}" class="d-block w-100" alt="..." style="object-fit: cover">
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                            @if($guiding->galleries->count() > 1)
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
                                                    @endif --}}
                                                </a>

                                                <div class="tours-list__icon">
                                                    <a href="{{ route('wishlist.add-or-remove', $guiding->id) }}">
                                                        <i class="fa fa-heart {{ (auth()->check() ? (auth()->user()->isWishItem($guiding->id) ? 'text-danger' : '') : '') }}"></i>
                                                    </a>
                                                </div>

                                                <a class="tours-list__content" title="Guide mit Slug {{ $guiding->slug }} aufmachen" href="{{ route('guidings.show', [$guiding->id,$guiding->slug]) }}" >
                                                    <span>{{ translate($guiding->location) }}</span>
                                                    <div class="tours-list__body">
                                                        <h3 class="tours-list__title">{{ translate( $guiding->title ) }} {{$guiding->columns_with_value_count}}</h3>

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
                                                                    {{ translate( 'Gästeanzahl:' )  }} {{ $guiding->max_guests }} @if($guiding->max_guests != 1) {{translate('Personen')}} @else {{translate('Person')}} @endif
                                                                </div>
                                                            </div>

                                                            <div class="tours-list__content__trait">
                                                                <img src="{{asset('assets/images/icons/clock.svg')}}" height="20" width="20" alt="" />
                                                                <div class="tours-list__content__trait__text">Dauer: {{ $guiding->duration }} @if($guiding->duration != 1) {{translate('Stunden')}} @else {{translate('Stunde')}} @endif</div>
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
                                                            <p class="tours-list__rate" style="text-align: right;"><span>ab {{$guiding->price}} €</span></p>
                                                        </div>
                                                    
                                                    </div>
                                                </a>
                                            

                                        </div>
                                        <!--Tours List Single-->
                                    @endforeach
                                    {!! $guidings->links('vendor.pagination.default') !!}

                            </div>
                        </div>
                    </div>
                    <div class="col-xxl-4 col-lg-6 map__wrap" id="wrap">
                        <div id="map">
                            <br>
                            <button id="load-map" class="btn btn-primary form-control">{{ translate('Die Cookies akzeptieren und die Karte laden') }}</button>
                        </div>
                    </div>
                @else
                <div id="map">
                    <br>
                    <button id="load-map" class="btn btn-primary form-control">{{ translate('Die Cookies akzeptieren und die Karte laden') }}</button>
                </div>
                    <div class="alert alert-danger" role="alert">
                        <b>{{ translate( 'Es scheint so, als gäbe es noch keine Guidings nach deinen Suchkriterien' ) }}</b>
                    </div>
                @endif

            </div>
        </div>

    </section>
    <!--Tours List End-->

    <!-- Modal -->
    @foreach($guidings as $guiding)
        @include('pages.guidings.content.guidingModal')
    @endforeach


    <!-- Endmodal -->

    <br>
@endsection

@section('js_after')


<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY', 'AIzaSyBiGuDOg_5yhHeoRz-7bIkc9T1egi1fA7Q') }}&libraries=places,geocoding"></script>
<script src="https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/markerclusterer.js"></script>

<script>
    $('#sortby').on('change',function(){
        $('#form-sortby').submit();
    });
</script>
<script>
// Get the toggle button and filter container elements
var toggleBtn = document.getElementById('toggleFilterBtn');
var filterContainer = document.getElementById('filterContainer');

// Add click event listener to the toggle button
toggleBtn.addEventListener('click', function() {
    // Toggle the visibility of the filter container
    filterContainer.classList.toggle('d-block');
});
</script>

<script>
    initializeSelect2();

function initializeSelect2() {

    var selectTarget = $('#target_fish');
    var selectWater = $('#water');
    var selectMethod = $('#methods');

    $("#target_fish").select2({

        multiple: true,
        width: 'resolve' // need to override the changed default

    });

    @foreach($alltargets as $target)
    var targetOption = new Option('{{ $target->name }}', '{{ $target->id }}');
    selectTarget.append(targetOption);

    @if(request()->get('target_fish'))
        @if(in_array($target->id, request()->get('target_fish')))
        $(targetOption).prop('selected', true);
        @endif
    @endif


    @endforeach
  
    // Trigger change event to update Select2 display
    selectTarget.trigger('change');






    $("#water").select2({
        multiple: true,
        width: 'resolve' // need to override the changed default
    });

    @foreach($allwaters as $water)
    var waterOption = new Option('{{ $water->name }}', '{{ $water->id }}');
    selectWater.append(waterOption);

    @if(request()->get('water'))
        @if(in_array($water->id, request()->get('water')))
        $(waterOption).prop('selected', true);
        @endif
    @endif


    @endforeach
  
    // Trigger change event to update Select2 display
    selectWater.trigger('change');





    $("#methods").select2({
        multiple: true,
        width: 'resolve' // need to override the changed default
    });

    @foreach($allmethods as $method)
    var methodOption = new Option('{{ $method->name }}', '{{ $method->id }}');
    selectMethod.append(methodOption);

    
    @if(request()->get('methods'))
        @if(in_array($method->id, request()->get('methods')))
        $(methodOption).prop('selected', true);
        @endif
    @endif


    @endforeach
  
    // Trigger change event to update Select2 display
    selectMethod.trigger('change');




}



</script>


<script>
     initializeMap();


     function initializeMap() {
    const map = new google.maps.Map(document.getElementById("map"), {
        zoom: 5,
        center: { lat: {{request()->get('placeLat') ? request()->get('placeLat') : 51.165691 }} , lng: {{request()->get('placeLng') ? request()->get('placeLng') : 10.451526 }} },
    });

    @if(request()->has('placeLat') && request()->has('placeLng') && !empty(request()->get('placeLat')) && !empty(request()->get('placeLng')) )
    

        const circleOptions = {
            strokeColor: '#e8604c',
            strokeOpacity: 0.8,
            strokeWeight: 2,
            fillColor: '#e8604c',
            fillOpacity: 0.35,
            map: map,
            center: {lat: {{request()->get('placeLat')}}, lng: {{request()->get('placeLng')}}},
           radius: {{$radius}} * 1609.34, // Convert radius to meters
        };
        circle = new google.maps.Circle(circleOptions);

     
    @endif

    const markers = [];
    const infowindows = [];

    @foreach($allGuidings as $guiding)
    @if(!empty($guiding->lat) && !empty($guiding->lng))
    const location{{$guiding->id}} = { lat: {{$guiding->lat}}, lng: {{$guiding->lng}} };

    const marker{{$guiding->id}} = new google.maps.Marker({
        position: location{{$guiding->id}},
        map: map,
    });

    markers.push(marker{{$guiding->id}});

    const infowindow{{$guiding->id}} = new google.maps.InfoWindow({
    content: `
        <div class="card p-0 border-0" style="width: 200px;">
            <div class="card-body border-0 p-0">
                <h5 class="card-title" style="font-size: 14px;">{{$guiding->title}}</h5>
                <div class="d-flex align-items-center my-1">
                    <div>
                        <img src="{{asset('assets/images/icons/fish.png')}}" height="20" width="20" alt="" />
                    </div>
                    <div class="mx-1">         
                    <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                        @php
                        $guidingTargets = $guiding->guidingTargets->pluck('name')->toArray();
                        @endphp
                        
                        @if(!empty($guidingTargets))
                            {{ implode(', ', $guidingTargets) }}
                        @else
                        {{ $guiding->threeTargets()}}
                        {{$guiding->target_fish_sonstiges ? " & " . $guiding->target_fish_sonstiges : ""}}
                        @endif
                    </p>
                    </div>
                </div>
                <div class="d-flex align-items-center my-1">
                    <div>
                        <img src="{{asset('assets/images/icons/water-waves.png')}}" height="20" width="20" alt="" />
                    </div>
                    <div class="mx-1">         
                    <p class="card-text text-truncate" style="font-size: 12px;max-width: 150px;">
                        @php
                        $guidingWaters = $guiding->guidingWaters->pluck('name')->toArray();
                        @endphp
                        
                        @if(!empty($guidingWaters))
                            {{ implode(', ', $guidingWaters) }}
                        @else
                        {{ $guiding->threeWaters() }}
                        {{$guiding->water_sonstiges ? " & " . $guiding->water_sonstiges : ""}}
                        @endif
                    </p>
                    </div>
                </div>
                <div class="text-center mt-2">
                    <a class="theme-primary text-center my-2" href="{{route('guidings.show',[$guiding->id,$guiding->slug])}}" style="padding:3px 7px;">ab €{{$guiding->price}}</a>
                </div>

            </div>
        </div>
    `
    });


    infowindows.push(infowindow{{$guiding->id}});

    marker{{$guiding->id}}.addListener("click", () => {
        infowindows.forEach((infowindow) => {
            infowindow.close();
        });
        infowindow{{$guiding->id}}.open(map, marker{{$guiding->id}});
    });
    @endif
    @endforeach

    const markerCluster = new MarkerClusterer(map, markers, {
        imagePath: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m',
    });

    google.maps.event.addListener(markerCluster, 'clusterclick', function(cluster) {
        map.fitBounds(cluster.getBounds());
        // Optionally, you can add additional logic to expand the cluster and show individual markers
        // based on your requirements
    });
}



function initialize() {
    var input = document.getElementById('searchPlace');
    var autocomplete = new google.maps.places.Autocomplete(input);
    google.maps.event.addListener(autocomplete, 'place_changed', function () {
        var place = autocomplete.getPlace();
        document.getElementById('placeLat').value = place.geometry.location.lat();
        document.getElementById('placeLng').value = place.geometry.location.lng();
    });
}

window.addEventListener('load', initialize);

window.addEventListener('load', function() {
    var placeLatitude = '{{ request()->get('placeLat') }}'; // Replace with the actual value from the request
    var placeLongitude = '{{ request()->get('placeLng') }}'; // Replace with the actual value from the request

    if (placeLatitude && placeLongitude) {
        // The place latitude and longitude are present, so set the values in the form fields
        document.getElementById('placeLat').value = placeLatitude;
        document.getElementById('placeLng').value = placeLongitude;
    } else {
        // The place latitude and longitude are not present, so execute the geolocation function
        getLocation();
    }
});

function getLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(showPosition);
    } else {
        console.log('Geolocation is not supported by this browser.');
    }
}

function showPosition(position) {
    var lat = position.coords.latitude;
    var lng = position.coords.longitude;
    document.getElementById('placeLat').value = lat;
    document.getElementById('placeLng').value = lng;
    
    codeLatLng(lat, lng);
}

function codeLatLng(lat, lng) {
    var geocoder = new google.maps.Geocoder();
    var latlng = new google.maps.LatLng(lat, lng);
    geocoder.geocode({'latLng': latlng}, function (results, status) {
        if (status === google.maps.GeocoderStatus.OK) {
            if (results[0]) {
                document.getElementById('searchPlace').value = results[0].formatted_address;
            } else {
                console.log('No results found');
                return null;
            }
        } else {
            console.log('Geocoder failed due to: ' + status);
            return null;
        }
    });
}

       
</script>


    <script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>

@endsection

