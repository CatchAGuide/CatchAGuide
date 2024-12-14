@extends('layouts.app')

@section('title', substr($title, 0, -3))
@section('description', $title)
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

        .carousel.slide img {
            /* max-height: 265px; */
            object-fit: cover;
            background: black;
            height: 300px;
            /* min-height: 160px; */
            /* height:228px; */
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
        #radius{
            background: url("data:image/svg+xml,<svg height='10px' width='10px' viewBox='0 0 16 16' fill='%23808080' xmlns='http://www.w3.org/2000/svg'><path d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/></svg>") no-repeat;
            background-position: right 0.3rem center !important;
        }
        #num-guests{
            background: url("data:image/svg+xml,<svg height='10px' width='10px' viewBox='0 0 16 16' fill='%23808080' xmlns='http://www.w3.org/2000/svg'><path d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/></svg>") no-repeat;
            background-position: right 0.3rem center !important;
        }
        .custom-select:has(option:disabled:checked[hidden]) {
        color: gray;
        }
        .custom-select option{
            color:black;
        }

        .btn-outline-theme{
        color: #E8604C;
        border-color: #E8604C;
        }
        .new-bg{
            background:#313041;
        }

        
      

    
    </style>

@endsection
@section('custom_style')
@include('layouts.schema.listings')
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
                    <h1 class="h2">{{ucwords(isset($place) ? translate('Alle Guidings bei ') . $place : translate('Alle Guidings') )}}</h1>
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
                
                @if($agent->ismobile())
                
                <div class="col-12 map__wrap" id="wrap">
                    <div>
                        <a class="p-3 mb-2 btn new-bg btn-theme-new text-center w-100 my-3" href="{{ route('guidings.request') }}">@lang('request.btnsearch')</a>
                    <div>
                    <div id="map">
                    </div>
                </div>
                @endif
                
                <div class="col-md-12">
                    
                    <form id="filterContainer" action="{{route('guidings.index')}}" method="get" class="shadow-sm px-4 py-2">
                        <div class="row">
                            <div class="col-6 col-sm-6 col-md-4">
                                <div class="form-group my-1">
                                    {{-- <label for="place">@lang('message.location')</label> --}}
                                    <div class="input-group">
                                        <div class="input-group-prepend border-0 border-bottom ">
                                            <span class=" d-flex align-items-center px-2 h-100"><i class="fas fa-map-marker-alt"></i></span>
                                        </div>
                                        <input  id="searchPlace" name="place" type="text" value="{{ request()->get('place') ? request()->get('place') : null }} @if(empty(request()->get('place')) && !empty(request()->get('country'))) {{request()->get('country')}} @endif" class="form-control border-0 border-bottom rounded-0" placeholder="@lang('message.enter-location')"  autocomplete="on">
                                    </div>
                                  <input type="hidden" id="placeLat" value="{{ request()->get('placeLat') ? request()->get('placeLat') : null }}" name="placeLat"/>
                                  <input type="hidden" id="placeLng" value="{{ request()->get('placeLng') ? request()->get('placeLng') : null }}" name="placeLng"/>
                                </div>
                            </div>
                            <div class="col-6 col-sm-6 col-md-4">
                                <div class="input-group my-1">
                                    {{-- <label for="radius">Radius</label> --}}
                                    <div class="input-group-prepend border-0 border-bottom ">
                                        <span class="d-flex align-items-center px-2 h-100">
                                            <i class="fa fa-compass"></i>
                                        </span>
                                    </div>
                                    <select id="radius" class="form-control form-select border-0 border-bottom rounded-0 custom-select" name="radius">
                                        <option selected disabled hidden>Radius</option>
                                        <option value="" >@lang('message.choose')...</option>
                                        <option value="50" {{ request()->get('radius') ? request()->get('radius') == 50 ? 'selected' : null : null }}>50 km</option>
                                        <option value="100" {{ request()->get('radius') ? request()->get('radius') == 100 ? 'selected' : null : null }}>100 km</option>
                                        <option value="150" {{ request()->get('radius') ? request()->get('radius') == 150 ? 'selected' : null : null }}>150 km</option>
                                        <option value="250" {{ request()->get('radius') ? request()->get('radius') == 250 ? 'selected' : null : null }}>250 km</option>
                                        <option value="500" {{ request()->get('radius') ? request()->get('radius') == 500 ? 'selected' : null : null }}>500 km</option>
                                    </select>
                                  </div>
                            </div>
                            <div class="col-12 col-sm-12 col-md-4">
                                <div class="input-group my-1">
                                    {{-- <label for="radius">@lang('message.number-of-guests')</label> --}}
                                    <div class="input-group-prepend border-0 border-bottom ">
                                        <span class="d-flex align-items-center px-2 h-100">
                                            <i class="fas fa-user"></i>
                                        </span>
                                    </div>
                                    <select id="num-guests" class="form-control form-select  border-0 border-bottom rounded-0 custom-select" name="num_guests">
                                        <option value="" disabled selected hidden>@lang('message.number-of-guests')</option>
                                        <option value="">@lang('message.choose')...</option>
                                        <option value="1" {{ request()->get('num_guests') ? request()->get('num_guests') == 1 ? 'selected' : null : null }}>1</option>
                                        <option value="2" {{ request()->get('num_guests') ? request()->get('num_guests') == 2 ? 'selected' : null : null }}>2</option>
                                        <option value="3" {{ request()->get('num_guests') ? request()->get('num_guests') == 3 ? 'selected' : null : null }}>3</option>
                                        <option value="4" {{ request()->get('num_guests') ? request()->get('num_guests') == 4 ? 'selected' : null : null }}>4</option>
                                        <option value="5" {{ request()->get('num_guests') ? request()->get('num_guests') == 5 ? 'selected' : null : null }}>5</option>
                                    </select>
                                </div>
                            </div>
                         
                            <div class="col-md-3">
                                <div class="form-group my-1 d-flex align-items-center border-bottom">
                                    {{-- <label for="target_fish">@lang('message.target-fish')</label> --}}
                                    <div class="px-2 select2-icon">
                                        <img src="{{asset('assets/images/icons/fish.png')}}" height="20" width="20" alt="" />
                                    </div>
                                   
                                    <select class="form-control form-select border-0 rounded-0" id="target_fish" name="target_fish[]" style="width:100%">
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group my-1 d-flex align-items-center border-bottom">
                                    {{-- <label for="water">@lang('message.body-type')</label> --}}
                                    <div class="px-2 select2-icon">
                                        <img src="{{asset('assets/images/icons/water-waves.png')}}" height="20" width="20" alt="" />
                                    </div>
                                    <select class="form-control form-select border-0  rounded-0" id="water" name="water[]" style="width:100%">
                            
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group my-1 d-flex align-items-center border-bottom ">
                                    {{-- <label for="methods">@lang('message.fishing-technique')</label> --}}
                                    <div class="px-2 select2-icon">
                                        <img src="{{asset('assets/images/icons/fishing.png')}}" height="20" width="20" alt="" />
                                    </div>
                                    <select class="form-control form-select border-0 rounded-0" id="methods" name="methods[]" style="width:100%">
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-1 ps-md-0">
                                <button class="btn btn-sm theme-primary btn-theme-new w-100 h-100" style="font-size:0.75rem">@lang('message.Search')</button>    
                            </div>
                            
                        </div>
        
                    </form>
                </div>

                <div class="col-6 col-sm-4 col-md-12 d-flex align-items-center my-2">
                    <div class="d-flex justify-content-start">
                        <button  id="toggleFilterBtn" class="btn outline-none"><span class="fw-bold text-decoration-underline">Filters</span><i class="fa fa-filter color-primary" aria-hidden="true"></i></button>
                    </div>
                </div>
                @if(count($guidings) > 0)
                <div class="col-6 col-sm-6 col-md-6 col-lg-6 col-xl-6 col-xxl-4 my-2 mt-md-5">
                    <form id="form-sortby" action="{{route('guidings.index')}}" method="get">
                        <div class="row-sort">
                            <div class="d-flex flex-sm-row flex-column align-items-sm-center align-items-stretch my-2">
                                <div class="d-flex align-items-center">
                                    <label class="fs-sm me-2 pe-1 text-nowrap" for="sortby"><i class="fi-arrows-sort text-muted mt-n1 me-2"></i>@lang('message.sortby')</label>
                                    <select class="form-select form-select-sm" name="sortby" id="sortby">
                                        <option value="" disabled selected>@lang('message.choose')...</option>
                                        <option value="newest" {{request()->get('sortby') ? request()->get('sortby') == 'newest' ? 'selected' : '' : '' }}>@lang('message.newest')</option>
                                        <option value="price-asc" {{request()->get('sortby') ? request()->get('sortby') == 'price-asc' ? 'selected' : '' : '' }}>@lang('message.lowprice')</option>
                                        <option value="short-duration" {{request()->get('sortby') ? request()->get('sortby') == 'short-duration' ? 'selected' : '' : '' }}>@lang('message.shortduration')</option>
                                        <option value="long-duration" {{request()->get('sortby') ? request()->get('sortby') == 'long-duration' ? 'selected' : '' : '' }}>@lang('message.longduration')</option>
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
                <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6 col-xxl-4 my-2 mt-md-5">
                    <div class="d-flex justify-content-center justify-content-md-end ">
                        {!! $guidings->links('vendor.pagination.default') !!}
                    </div>  
                  
                </div>
                <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-6 col-xxl-4 align-self-end">
                    <div class="d-flex justify-content-center justify-content-md-end ">
                        <a class="p-3 mb-2 btn new-bg btn-theme-new text-center w-100 my-3" href="{{ route('guidings.request') }}">@lang('request.btnsearch')</a>
                    </div>
                </div>
                @endif

                <div class="col-12">
                    <!-- column-reverse-row-normal -->
                    <div class="row column-reverse-row-normal">
                  
                        @if(count($guidings))
               
                        <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12 col-xxl-8">
                            <div class="tours-list__right">
                                <div class="tours-list__inner">
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
                                    {!! $guidings->links('vendor.pagination.default') !!}

                                </div>
                            </div>
                        </div>
                        
                        @if(!$agent->ismobile())
                            
                            <div class="col-xxl-4 col-lg-12 map__wrap" id="wrap">
                                <div id="map">
                                </div>
                            </div>
                        @endif
                      
                    @else
                    
                    @if(!$agent->ismobile())
                        <div class="col-12">
                            <div class="alert alert-danger" role="alert">
                                <b>{{ translate( 'Es scheint so, als gäbe es noch keine Guidings nach deinen Suchkriterien' ) }}</b>
                            </div>
                            <div class="my-2">
                                <h4>@lang('message.find-otherguide')</h4>
                            </div>
                        </div>
                    @endif
     
                        @include('pages.guidings.partials.list',['guidings' => $otherguidings])

                        @if($agent->ismobile())
                        <div class="col-12">
                            <div class="alert alert-danger" role="alert">
                                <b>{{ translate( 'Es scheint so, als gäbe es noch keine Guidings nach deinen Suchkriterien' ) }}</b>
                            </div>
                            <div class="my-2">
                                <h4>@lang('message.find-otherguide')</h4>
                            </div>
                        </div>
                        @endif
                        @if(!$agent->ismobile())
                            <div class="col-xxl-4 col-lg-6 map__wrap" id="wrap">
                                <div id="map">
                                </div>
                            </div>
                        @endif

                    @endif
    
                    </div>
                </div>

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


<!-- <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBiGuDOg_5yhHeoRz-7bIkc9T1egi1fA7Q&libraries=places,geocoding"></script>
<script src="https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/markerclusterer.js"></script> -->
<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAP_API_KEY') }}&libraries=places,geocoding"></script>
<script>(g=>{var h,a,k,p="The Google Maps JavaScript API",c="google",l="importLibrary",q="__ib__",m=document,b=window;b=b[c]||(b[c]={});var d=b.maps||(b.maps={}),r=new Set,e=new URLSearchParams,u=()=>h||(h=new Promise(async(f,n)=>{await (a=m.createElement("script"));e.set("libraries",[...r]+"");for(k in g)e.set(k.replace(/[A-Z]/g,t=>"_"+t[0].toLowerCase()),g[k]);e.set("callback",c+".maps."+q);a.src=`https://maps.${c}apis.com/maps/api/js?`+e;d[q]=f;a.onerror=()=>h=n(Error(p+" could not load."));a.nonce=m.querySelector("script[nonce]")?.nonce||"";m.head.append(a)}));d[l]?console.warn(p+" only loads once. Ignoring:",g):d[l]=(f,...n)=>r.add(f)&&u().then(()=>d[l](f,...n))})
    ({key: "{{ env('GOOGLE_MAP_API_KEY') }}", v: "weekly"});
</script>
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
        placeholder: '@lang('message.target-fish')',
        width: 'resolve', // need to override the changed default
    });

    @foreach($alltargets as $target)
    var targetname = '{{$target->name}}';

    @if(app()->getLocale() == 'en')
    targetname = '{{$target->name_en}}'
    @endif

    var targetOption = new Option(targetname, '{{ $target->id }}');

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
        placeholder: '@lang('message.body-type')',
        width: 'resolve' // need to override the changed default
    });

    @foreach($allwaters as $water)
    var watername = '{{$water->name}}';

    @if(app()->getLocale() == 'en')
    watername = '{{$water->name_en}}'
    @endif

    var waterOption = new Option(watername, '{{ $water->id }}');
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
        placeholder: '@lang('message.fishing-technique')',
        width: 'resolve' // need to override the changed default
    });

    @foreach($allmethods as $method)
    var methodname = '{{$method->name}}';

    @if(app()->getLocale() == 'en')
    methodname = '{{$method->name_en}}'
    @endif

    var methodOption = new Option(methodname, '{{ $method->id }}');
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


<script type="module">
    import { MarkerClusterer } from "https://cdn.skypack.dev/@googlemaps/markerclusterer@2.3.1";
     initializeMap();


     async function initializeMap() {

        var mapStyle = [
          {
            featureType: "poi",
            elementType: "labels",
            stylers: [
              {
                visibility: "off",
              },
            ],
          },
          {
            featureType: "transit",
            elementType: "labels",
            stylers: [
              {
                visibility: "off",
              },
            ],
          },
          {
            featureType: "road",
            elementType: "labels.icon",
            stylers: [
              {
                visibility: "off",
              },
            ],
          },
          {
            featureType: "road",
            elementType: "labels.text",
            stylers: [
              {
                visibility: "on",
              },
            ],
          },
          {
            featureType: "road",
            elementType: "labels.text.fill",
            stylers: [
              {
                visibility: "on",
              },
            ],
          },
          {
            featureType: "administrative.locality",
            elementType: "labels",
            stylers: [
              {
                visibility: "on",
              },
            ],
          },
        ];

    //const { Map } = await google.maps.importLibrary("maps");

    @php
        $lat = isset($guidings[0]) ? $guidings[0]->lat : 51.165691;
        $lng = isset($guidings[0]) ? $guidings[0]->lng : 10.451526;
    @endphp
    const position = { lat: {{request()->get('placeLat') ? request()->get('placeLat') : $lat }} , lng: {{request()->get('placeLng') ? request()->get('placeLng') : $lng }} };
    const { Map, InfoWindow } = await google.maps.importLibrary("maps");
    const { AdvancedMarkerElement, PinElement } = await google.maps.importLibrary("marker");

    //map = new google.maps.Map(document.getElementById("map"), {
    const map = new Map(document.getElementById("map"), {
        zoom: 5,
        center: position,
        styles: mapStyle,
        mapId: "DEMO_MAP_ID",
    });

    // The marker, positioned at Uluru
    const marker = new AdvancedMarkerElement({
        map: map,
        position: position,
    });



    const markers = [];
    const infowindows = [];
    const uniqueCoordinates = [];
    let isDuplicateCoordinate;  

    @if($allGuidings->isEmpty())
        @include('pages.guidings.partials.maps',['guidings' => $otherguidings])
    @else
        @include('pages.guidings.partials.maps',['guidings' => $allGuidings])
    @endif


    function getRandomOffset() {
      // Generate a random value between -0.00005 and 0.00005 (adjust the range as needed)
      return (Math.random() - 0.5) * 0.0080;
    }


    /* 
    // Create the MarkerClusterer
    const markerCluster = new MarkerClusterer(map, markers, {
        imagePath: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m',
        maxZoom: 12,
    });
    */
   
    const markerCluster = new MarkerClusterer({ markers, map, mapStyle });
    // Add click event listeners to individual markers inside the cluster
    google.maps.event.addListener(markerCluster, 'clusterclick', function(cluster) {
        // You can control the zoom level here
        // For example, zoom in by 2 levels when clicking on a cluster
        map.setZoom(map.getZoom() + 2);
        map.setCenter(cluster.getCenter());
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
        // getLocation();
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

