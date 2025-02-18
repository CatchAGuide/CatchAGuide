@extends('layouts.app-v2-1')

@section('title', translate(substr($title, 0, -3)))
@section('description', translate($title))

@section('header_title', ((ucwords(isset($place)) ? translate('Alle Guidings bei ') . $place : translate('Alle Guidings'))))
@section('header_sub_title', '')

@section('css_after')
<style>
    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 15px;
        width: 100%;
    }

    .page-header {
        /*margin-top: -60px;*/
        background: #f8f9fa;
    }

    .page-header-bg-overly,
    .pager-header-bg {
        display: none;
    }

    .floating-search-container {
        position: relative;
        margin-top: -30px;
        z-index: 100;
    }

    .carousel.slide img {
        height: 250px;
        object-fit: cover;
        width: 100%;
        background: black;
    }

    .form-custom-input {
        border: 1px solid #dee2e6;
        border-radius: 4px;
        padding: 8px 12px;
        width: 100%;
    }

    .form-custom-input:focus {
        border-color: #e8604c;
        outline: none;
        box-shadow: 0 0 0 2px rgba(232,96,76,0.25);
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

    .carousel-item {
        aspect-ratio: 4/3;
        background-color: #f8f9fa;
    }

    .carousel-item-next, .carousel-item-prev, .carousel-item.active {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .form-custom-input{
    border: 1px solid #d4d5d6;
    border-radius: 5px;
    padding: 8px 10px;
    width:100%;
    }
    .form-control:focus{
       box-shadow: none;
    }
    .form-custom-input:focus-visible{
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

    .sort-row .form-select{
        width: auto;
    }

    @media only screen and (max-width: 450px) {
        #map-placeholder a.btn {
            top: 35%;
            left: 30%;
        }

        .page-header {
            margin-top: -60px!important;
        }
    }

    @media only screen and (min-width: 451px) and (max-width: 766px) {
        #map-placeholder a.btn {
            top: 40%;
            left: 35%;
        }

        .page-header {
            margin-top: -60px!important;
        }
    }
    @media only screen and (min-width: 767px) and (max-width: 991px) {
        #map-placeholder a.btn {
            top: 45%;
            left: 45%;
        }

        .page-header {
            margin-top: -30px!important;
        }
    }
    @media only screen and (min-width: 992px) {
        .page-header {
            margin-top: -30px!important;
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
    #map-placeholder {
        position: relative;
        width:100%;
        height: 200px;
        background-image: url({{ url('') }}/assets/images/map-bg.png);
    }
    #map-placeholder a.btn {
        position: absolute;
        top: calc(50% - 19px);
        right: calc(50% - 81px);
    }
    #guidings-menu-search {
        position: absolute;
        top: 133px;
        z-index: 3;
    }
    #guidings-result {
        line-height: 14px;
    }
    #offcanvasBottomSearch {
        height: 90%!important;
    }
    .pac-container {
        z-index: 2000;
    }
    .cag-btn {
        background-color: #E8604C !important;
        color: #fff !important;
        border: 2px solid #E8604C !important;
    }
    .cag-btn-inverted {
        background-color: #313041 !important;
        color: #fff !important;
        border: 2px solid #313041 !important;
    }
    .mobile-selection-sfm {
        position: sticky;
        z-index: 10;
        top: 0;
        background-color: #fff;
        padding-top: 15px;
        padding-left: 15px;
        padding-right: 15px;
    }
    .guiding-item-price {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
    }

    .guiding-item-price h5 {
        margin: 0;
        white-space: nowrap;
        font-size: clamp(14px, 2vw, 18px);  /* Responsive font size between 14px and 18px */
    }

    .guiding-item-price span {
        display: inline-block;
        padding: 4px 8px;
    }

    .tours-list {
        position: relative;
        padding: 30px 0;  /* Reduced default padding */
    }

    @media only screen and (max-width: 767px) {
        .tours-list {
            padding: 15px 0;  /* Even smaller padding for mobile */
        }
    }

    .inclusions-price {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 10px;
        margin-top: 10px;
    }

    .guidings-inclusions-container {
        flex: 1;
        min-width: 0; /* Prevents flex item from overflowing */
    }

    .guidings-included {
        font-size: 14px;
    }

    .guidings-included strong {
        display: block;
    }

    .inclusions-list {
        display: flex;
        flex-wrap: wrap;
        max-width: 100%;
    }

    .inclusion-item {
        white-space: nowrap;
        padding: 2px 8px;
        border-radius: 4px;
        display: inline-flex;
        align-items: center;
    }

    .inclusion-item i {
        font-size: 10px;
        margin-right: 4px;
        /* color: #E8604C; */
    }

    .guiding-item-price {
        text-align: right;
        min-width: fit-content;
        padding-left: 10px;
    }

    @media (max-width: 767px) {
        .inclusions-price {
            flex-direction: column;
        }
        
        .guiding-item-price {
            width: 100%;
            text-align: left;
            padding-left: 0;
        }

        .inclusion-item {
            font-size: 13px; /* Larger font size for mobile */
            padding: 3px 10px;
        }
        
        .guidings-included strong {
            font-size: 14px;
        }
    }

    .guidings-item-title {
        margin-bottom: 10px;
    }

    .guidings-item-title h5 {
        font-size: clamp(18px, 2vw, 22px);
        margin-bottom: 5px;
    }

    .guidings-item-title span {
        display: block;
        font-size: 15px;
        color: #444;
        max-width: 100%;
    }

    /* Only apply truncation on desktop */
    @media (min-width: 768px) {
        .guidings-item-title span.truncate {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    }

    .guidings-item-title i {
        font-size: 13px;
        margin-right: 4px;
        color: #666;
    }

    .inclusion-item {
        font-size: 15px;
        white-space: nowrap;
        padding: 2px 8px;
        border-radius: 4px;
        display: inline-flex;
        align-items: center;
    }

    .guiding-item-price h5 {
        margin: 0;
        white-space: nowrap;
        font-size: clamp(14px, 2vw, 18px);  /* Responsive font size between 14px and 18px */
    }

    /* Mobile specific styles */
    @media (max-width: 767px) {
        .guidings-item-title h5 {
            font-size: 18px;
            margin-bottom: 0
        }
        
        .guidings-item-title span {
            font-size: 15px; /* Keeping font size readable on mobile */
        }

        .inclusion-item {
            font-size: 15px; /* Larger font size for mobile */
            padding: 3px 10px; /* Slightly larger padding for better touch targets */
        }
        
        .guiding-item-price h5 {
            font-size: 18px; /* Fixed size for better readability on mobile */
        }
        
        .guidings-included strong {
            font-size: 15px;
        }
    }
</style>

@endsection
@section('custom_style')
@include('layouts.schema.listings')
@endsection
@section('content')
{{-- <div class="container"> --}}
    {{-- <section class="page-header">
        <div class="page-header__bottom">
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
    </section> --}}

    <!--Tours List Start-->
    <section class="tours-list">
        <div class="container">
            <div class="row">
                <!-- Mobile Sorting -->
                <div class="col-12 col-sm-4 col-md-12 d-flex mb-3 d-block d-sm-none mobile-selection-sfm">
                    <div class="d-grid gap-2 w-100">
                        <div class="btn-group" role="group" aria-label="Button group with nested dropdown">
                            <div class="btn-group border rounded-start cag-btn-inverted" role="group" style=" width:30%;">
                                <button type="button" class="btn dropdown-toggle text-white" data-bs-toggle="dropdown" aria-expanded="false"><i class="fa fa-arrow-down-arrow-up me-1"></i>@lang('message.sortby')</button>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="{{ route('guidings.index') }}?sortby=newest">@lang('message.newest')</a></li>
                                    <li><a class="dropdown-item" href="{{ route('guidings.index') }}?sortby=price-asc">@lang('message.lowprice')</a></li>
                                    <li><a class="dropdown-item" href="{{ route('guidings.index') }}?sortby=short-duration">@lang('message.shortduration')</a></li>
                                    <li><a class="dropdown-item" href="{{ route('guidings.index') }}?sortby=long-duration">@lang('message.longduration')</a></li>
                                </ul>

                                @foreach(request()->except('sortby') as $key => $value)
                                    @if(is_array($value))
                                        @foreach($value as $arrayValue)
                                            <input type="hidden" name="{{ $key }}[]" value="{{ $arrayValue }}">
                                        @endforeach
                                    @else
                                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                    @endif
                                @endforeach
                            </div>
                            <a class="btn border-start cag-btn-inverted" data-bs-toggle="offcanvas" data-bs-target="#offcanvasBottomSearch" aria-controls="offcanvasBottomSearch" href="javascript:void(0)" style="border-left: 1px solid #ccc!important; z-index: 2; width:30%;">
                                <i class="fa fa-filter me-1"></i>@lang('message.filter') 
                                @if($guidings->count() > 0)
                                    @if(request()->has('radius') || request()->has('num_guests') || request()->has('target_fish') || request()->has('water') || request()->has('fishing_type') || request()->has('price_range'))
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="guiding-filter-counter"></span>
                                    @endif
                                @endif
                            </a>
                            <a class="btn border cag-btn-inverted" data-bs-target="#mapModal" data-bs-toggle="modal" href="javascript:void(0)" style=" border-left: 2px solid #ccc!important; width:40%;"><i class="fa fa-map-marker-alt me-2"></i>@lang('destination.show_on_map')</a>
                        </div>
                    </div>
                </div>

                <!-- Desktop Filter -->
                <div id="filterCard" class="col-sm-12 col-lg-3">        
                    <div class="card mb-2 d-none d-sm-block">
                        <div id="map-placeholder">
                            <a class="btn btn-primary" data-bs-target="#mapModal" data-bs-toggle="modal" href="javascript:void(0)">@lang('destination.show_on_map')</a>
                        </div>
                    </div>
                    <div class="card d-block d-none d-sm-block mb-1">
                        <div class="card-header">
                            @lang('message.sortby'):
                        </div>
                        <div class="card-body border-bottom">
                            <form id="form-sortby-2" action="{{route('guidings.index')}}" method="get">
                                <select class="form-select form-select-sm" name="sortby" id="sortby-2">
                                    <option value="" disabled selected>@lang('message.choose')...</option>
                                    <option value="newest" {{request()->get('sortby') ? request()->get('sortby') == 'newest' ? 'selected' : '' : '' }}>@lang('message.newest')</option>
                                    <option value="price-asc" {{request()->get('sortby') ? request()->get('sortby') == 'price-asc' ? 'selected' : '' : '' }}>@lang('message.lowprice')</option>
                                    <option value="short-duration" {{request()->get('sortby') ? request()->get('sortby') == 'short-duration' ? 'selected' : '' : '' }}>@lang('message.shortduration')</option>
                                    <option value="long-duration" {{request()->get('sortby') ? request()->get('sortby') == 'long-duration' ? 'selected' : '' : '' }}>@lang('message.longduration')</option>
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
                    </div>
                    <div class="card d-block d-none d-sm-block">
                        <div class="card-header">
                            @lang('destination.filter_by'):
                        </div>
                        <div class="card-body border-bottom">
                            <form id="filterContainer" action="{{route('guidings.index')}}" method="get" class="shadow-sm px-4 py-2">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="input-group my-1">
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
                                 
                                    <div class="col-12">
                                        <div class="form-group my-1 d-flex align-items-center border-bottom">
                                            <div class="px-2 select2-icon">
                                                <img src="{{asset('assets/images/icons/fish.png')}}" height="20" width="20" alt="" />
                                            </div>
                                           
                                            <select class="form-control form-select border-0 rounded-0" id="target_fish" name="target_fish[]" style="width:100%">
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="col-12">
                                        <div class="form-group my-1 d-flex align-items-center border-bottom">
                                            <div class="px-2 select2-icon">
                                                <img src="{{asset('assets/images/icons/water-waves.png')}}" height="20" width="20" alt="" />
                                            </div>
                                            <select class="form-control form-select border-0  rounded-0" id="water" name="water[]" style="width:100%">
                                    
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group my-1 d-flex align-items-center border-bottom ">
                                            <div class="px-2 select2-icon">
                                                <img src="{{asset('assets/images/icons/fishing.png')}}" height="20" width="20" alt="" />
                                            </div>
                                            <select class="form-control form-select border-0 rounded-0" id="methods" name="methods[]" style="width:100%">
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-12 mb-2">
                                        <div class="input-group my-1">
                                            <div class="input-group-prepend border-0 border-bottom ">
                                                <span class="d-flex align-items-center px-2 h-100">
                                                    <i class="fa fa-euro-sign"></i>
                                                </span>
                                            </div>
                                            <select id="price_range" class="form-control form-select border-0 border-bottom rounded-0 custom-select" name="price_range">
                                                <option selected disabled hidden>Price per Person</option>
                                                <option value="" >@lang('message.choose')...</option>
                                                <option value="1-50" {{ request()->get('price_range') ? request()->get('price_range') == '1-200' ? 'selected' : null : null }}>1 - 50 p.P.</option>
                                                <option value="51-100" {{ request()->get('price_range') ? request()->get('price_range') == '201-400' ? 'selected' : null : null }}>51 - 100 p.P.</option>
                                                <option value="101-150" {{ request()->get('price_range') ? request()->get('price_range') == '401-600' ? 'selected' : null : null }}>101 - 150 p.P.</option>
                                                <option value="151-200" {{ request()->get('price_range') ? request()->get('price_range') == '601-800' ? 'selected' : null : null }}>151 - 200 p.P.</option>
                                                <option value="201-250" {{ request()->get('price_range') ? request()->get('price_range') == '801-1000' ? 'selected' : null : null }}>201 - 250 p.P.</option>
                                                <option value="350" {{ request()->get('price_range') ? request()->get('price_range') == '1001' ? 'selected' : null : null }}>350 and more</option>
                                            </select>
                                          </div>
                                    </div>
                                    <div class="col-sm-12 col-lg-12 ps-md-0">
                                        <button class="btn btn-sm theme-primary btn-theme-new w-100 h-100" >@lang('message.Search')</button>    
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-sm-12 col-lg-9">
                    <!-- Add search message display -->
                    @if(!empty($searchMessage))
                        <div class="alert alert-info mb-3" role="alert">
                            {{ $searchMessage }}
                        </div>
                    @endif

                    <!-- column-reverse-row-normal -->
                    @if(count($guidings))
                    <div class="row">
                        <div class="col-lg-12 col-sm-12">
                            <div class="tours-list__right">
                                <div class="tours-list__inner">
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
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    @if(count($otherguidings) && ( request()->placeLat != null || request()->placelat != "" && request()->placeLng != null || request()->placelng != ""))
                    
                    <hr>
                    <div class="my-0 section-title">
                        <h2 class="h4 text-dark fw-bolder">{{translate('Additional Fishing Tour close to') }} {{ request()->placeLat != null || request()->placelat != "" && request()->placeLng != null || request()->placelng != "" ? request()->place : '' }}</h2> 
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
                                    {!! $guidings->links('vendor.pagination.default') !!}
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

            </div>

     
        </div>

    </section>
    <!--Tours List End-->

    <!-- Modal -->
    @foreach($guidings as $guiding)
        @include('pages.guidings.content.guidingModal')
    @endforeach


    <div class="modal show" id="mapModal" tabindex="-1" aria-labelledby="mapModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" style="max-width: 100%; width: 96%; height:100%;">
            <div class="modal-content" style="height:90%;">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="mapModalLabel">Map</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div id="map" class="modal-body"></div>
            </div>
        </div>
    </div>
    <!-- Endmodal -->

    <br>

    <div class="offcanvas offcanvas-bottom" tabindex="-1" id="offcanvasBottomSearch" aria-labelledby="offcanvasBottomLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasBottomLabel">Filter</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body small">
            <form id="filterContainerOffCanvass" action="{{route('guidings.index')}}" method="get" class="px-4 py-2">
                <div class="row">
                    <div class="col-12">
                        <div class="form-group my-1">
                            <div class="input-group">
                                <div class="input-group-prepend border-0 border-bottom ">
                                    <span class=" d-flex align-items-center px-2 h-100"><i class="fas fa-map-marker-alt"></i></span>
                                </div>
                                <input id="searchPlace2" name="place" type="text" value="{{ request()->get('place') ? request()->get('place') : null }} @if(empty(request()->get('place')) && !empty(request()->get('country'))) {{request()->get('country')}} @endif" class="form-control border-0 border-bottom rounded-0" placeholder="@lang('message.enter-location')"  autocomplete="on">
                            </div>
                          <input type="hidden" id="LocationLat2" value="{{ request()->get('placeLat') ? request()->get('placeLat') : null }}" name="placeLat"/>
                          <input type="hidden" id="LocationLng2" value="{{ request()->get('placeLng') ? request()->get('placeLng') : null }}" name="placeLng"/>
                          <input type="hidden" id="LocationCity2" value="{{ request()->get('city') ? request()->get('city') : null }}" name="city"/>
                          <input type="hidden" id="LocationCountry2" value="{{ request()->get('country') ? request()->get('country') : null }}" name="country"/>
                        </div>
                    </div>
                    {{-- <div class="col-12">
                        <div class="input-group my-1">
                            <div class="input-group-prepend border-0 border-bottom ">
                                <span class="d-flex align-items-center px-2 h-100">
                                    <i class="fa fa-compass"></i>
                                </span>
                            </div>
                            <select id="radiusOffCanvass" class="form-control form-select border-0 border-bottom rounded-0 custom-select" name="radius">
                                <option selected disabled hidden>Radius</option>
                                <option>@lang('message.choose')...</option>
                                <option value="50" {{ request()->get('radius') ? request()->get('radius') == 50 ? 'selected' : null : null }}>50 km</option>
                                <option value="100" {{ request()->get('radius') ? request()->get('radius') == 100 ? 'selected' : null : null }}>100 km</option>
                                <option value="150" {{ request()->get('radius') ? request()->get('radius') == 150 ? 'selected' : null : null }}>150 km</option>
                                <option value="250" {{ request()->get('radius') ? request()->get('radius') == 250 ? 'selected' : null : null }}>250 km</option>
                                <option value="500" {{ request()->get('radius') ? request()->get('radius') == 500 ? 'selected' : null : null }}>500 km</option>
                            </select>
                          </div>
                    </div> --}}
                    <div class="col-12">
                        <div class="input-group my-1">
                            <div class="input-group-prepend border-0 border-bottom ">
                                <span class="d-flex align-items-center px-2 h-100">
                                    <i class="fas fa-user"></i>
                                </span>
                            </div>
                            <select id="num-guestsOffCanvass" class="form-control form-select  border-0 border-bottom rounded-0 custom-select" name="num_guests">
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
                 
                    <div class="col-12">
                        <div class="form-group my-1 d-flex align-items-center border-bottom">
                            <div class="px-2 select2-icon">
                                <img src="{{asset('assets/images/icons/fish.png')}}" height="20" width="20" alt="" />
                            </div>
                           
                            <select class="form-control form-select border-0 rounded-0" id="target_fishOffCanvass" name="target_fish[]" style="width:100%">
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <div class="form-group my-1 d-flex align-items-center border-bottom">
                            <div class="px-2 select2-icon">
                                <img src="{{asset('assets/images/icons/water-waves.png')}}" height="20" width="20" alt="" />
                            </div>
                            <select class="form-control form-select border-0  rounded-0" id="waterOffCanvass" name="water[]" style="width:100%">
                    
                            </select>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group my-1 d-flex align-items-center border-bottom ">
                            <div class="px-2 select2-icon">
                                <img src="{{asset('assets/images/icons/fishing.png')}}" height="20" width="20" alt="" />
                            </div>
                            <select class="form-control form-select border-0 rounded-0" id="methodsOffCanvass" name="methods[]" style="width:100%">
                            </select>
                        </div>
                    </div>
                    <div class="col-12 mb-2">
                        <div class="input-group my-1">
                            <div class="input-group-prepend border-0 border-bottom ">
                                <span class="d-flex align-items-center px-2 h-100">
                                    <i class="fa fa-euro-sign"></i>
                                </span>
                            </div>
                            <select id="price_rangeOffCanvass" class="form-control form-select border-0 border-bottom rounded-0 custom-select" name="price_range">
                                <option selected disabled hidden>Price per Person</option>
                                <option value="" >@lang('message.choose')...</option>
                                <option value="1-50" {{ request()->get('price_range') ? request()->get('price_range') == '1-200' ? 'selected' : null : null }}>1 - 50 p.P.</option>
                                <option value="51-100" {{ request()->get('price_range') ? request()->get('price_range') == '201-400' ? 'selected' : null : null }}>51 - 100 p.P.</option>
                                <option value="101-150" {{ request()->get('price_range') ? request()->get('price_range') == '401-600' ? 'selected' : null : null }}>101 - 150 p.P.</option>
                                <option value="151-200" {{ request()->get('price_range') ? request()->get('price_range') == '601-800' ? 'selected' : null : null }}>151 - 200 p.P.</option>
                                <option value="201-250" {{ request()->get('price_range') ? request()->get('price_range') == '801-1000' ? 'selected' : null : null }}>201 - 250 p.P.</option>
                                <option value="350" {{ request()->get('price_range') ? request()->get('price_range') == '1001' ? 'selected' : null : null }}>350 and more</option>
                            </select>
                          </div>
                    </div>
                    <div class="col-sm-12 col-lg-12 ps-md-0">
                        <button class="btn btn-sm theme-primary btn-theme-new w-100 h-100" >@lang('message.Search')</button>    
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('js_after')

<script>
    $('#sortby, #sortby-2').on('change', function() {
        const urlParams = new URLSearchParams(window.location.search);
        urlParams.set('sortby', $(this).val());
        
        const newUrl = `${window.location.pathname}?${urlParams.toString()}`;
        window.location.href = newUrl;
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
$(function(){
    $('#toggleFilterBtn').click(function(){
        $('#filterCard').toggle();
    });
});
</script>

<script>
    initializeSelect2();

function initializeSelect2() {
    var selectTarget = $('#target_fish, #target_fishOffCanvass');
    var selectWater = $('#water, #waterOffCanvass');
    var selectMethod = $('#methods, #methodsOffCanvass');

    // Target Fish
    selectTarget.append(new Option('@lang('message.choose')...', '', true, true));
    @foreach($alltargets as $target)
        var targetname = '{{$target->name}}';
        @if(app()->getLocale() == 'en')
            targetname = '{{$target->name_en}}';
        @endif
        selectTarget.append(new Option(targetname, '{{ $target->id }}', false, false));
    @endforeach

    // Water Types
    selectWater.append(new Option('@lang('message.choose')...', '', true, true));
    @foreach($guiding_waters as $water)
        var watername = '{{$water->name}}';
        @if(app()->getLocale() == 'en')
            watername = '{{$water->name_en}}';
        @endif
        selectWater.append(new Option(watername, '{{ $water->id }}', false, false));
    @endforeach

    // Fishing Methods
    selectMethod.append(new Option('@lang('message.choose')...', '', true, true));
    @foreach($guiding_methods as $method)
        var methodname = '{{$method->name}}';
        @if(app()->getLocale() == 'en')
            methodname = '{{$method->name_en}}';
        @endif
        selectMethod.append(new Option(methodname, '{{ $method->id }}', false, false));
    @endforeach

    // Initialize Select2 and set values
    $("#target_fish, #target_fishOffCanvass").select2({
        multiple: true,
        placeholder: '@lang('message.target-fish')',
        width: 'resolve',
    }).val(@json(request()->get('target_fish', [])))
      .trigger('change');

    $("#water, #waterOffCanvass").select2({
        multiple: true,
        placeholder: '@lang('message.body-type')',
        width: 'resolve'
    }).val(@json(request()->get('water', [])))
      .trigger('change');

    $("#methods, #methodsOffCanvass").select2({
        multiple: true,
        placeholder: '@lang('message.fishing-technique')',
        width: 'resolve'
    }).val(@json(request()->get('methods', [])))
      .trigger('change');

    // Set other form values if they exist
    @if(request()->get('price_range'))
        $('#price_range, #price_rangeOffCanvass').val('{{ request()->get('price_range') }}');
    @endif

    @if(request()->get('num_guests'))
        $('#num-guests, #num-guestsOffCanvass').val('{{ request()->get('num_guests') }}');
    @endif
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

        @php
            $lat = isset($guidings[0]) ? $guidings[0]->lat : 51.165691;
            $lng = isset($guidings[0]) ? $guidings[0]->lng : 10.451526;
        @endphp

        const position =  { lat: {{request()->get('placeLat') ? request()->get('placeLat') : $lat }} , lng: {{request()->get('placeLng') ? request()->get('placeLng') : $lng }} }; 
        const { Map, InfoWindow } = await google.maps.importLibrary("maps");
        const { AdvancedMarkerElement, PinElement } = await google.maps.importLibrary("marker");

        const map = new Map(document.getElementById("map"), {
            zoom: 5,
            center: position,
            styles: mapStyle,
            mapId: "DEMO_MAP_ID",
            mapTypeControl: false,
            streetViewControl: false,
        });

        const marker = new AdvancedMarkerElement({
            map: map,
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

        const markerCluster = new MarkerClusterer({ markers, map, mapStyle });
        // Add click event listeners to individual markers inside the cluster
        google.maps.event.addListener(markerCluster, 'clusterclick', function(cluster) {
            map.setZoom(map.getZoom() + 2);
            map.setCenter(cluster.getCenter());
        });
    }

    window.addEventListener('load', function() {
        var placeLatitude = '{{ request()->get('placeLat') }}'; // Replace with the actual value from the request
        var placeLongitude = '{{ request()->get('placeLng') }}'; // Replace with the actual value from the request

        if (placeLatitude && placeLongitude) {
            // The place latitude and longitude are present, so set the values in the form fields
            document.getElementById('placeLat').value = placeLatitude;
            document.getElementById('placeLng').value = placeLongitude;
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
    }

</script>
<script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>
@endsection