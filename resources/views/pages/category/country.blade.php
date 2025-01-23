@extends('layouts.app-v2')

@section('title', translate($row_data->name))
@section('description', translate($row_data->title))
@section('header_title', translate($row_data->title))
@section('header_sub_title', translate($row_data->sub_title))

@section('custom_style')
<style>
    #destination{
        max-width: 1600px;
    }
    .guiding-item-desc a:hover {
        color: #000!important;
    }
    #page-main-intro {
        /*white-space: nowrap;*/
        /*overflow: hidden;
        text-overflow: ellipsis;
        height: 190px;
        width: 100%;*/
        /*display: -webkit-box;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
        -webkit-line-clamp: 9; 
        height: 13.5em; 
        max-height: 13.5em; */
    }
    #carousel-regions,
    #carousel-cities {
        min-height: 301.6px;
    }
    #carousel-regions .dimg-fluid,
    #carousel-cities .dimg-fluid {
        min-height: 301.6px;
    }
    /* #destination,
    #destination a
     {
        font-size: 14px;
    } */
    .country-listing-item p {
        font-size: 12px;
    }
    .country-listing-item-rating p {
        line-height: 12px;
    }
    #destination-form input,
    #destination-form select {
        padding-left: 30px;
    }

    @media (max-width: 767px) {
        #carousel-regions .carousel-inner .carousel-item > div {
            display: none;
        }
        #carousel-regions .carousel-inner .carousel-item > div:first-child {
            display: block;
        }
        .dimg-fluid {
            width: 100%!important;
        }
    }

    #carousel-regions .carousel-inner .carousel-item.active,
    #carousel-regions .carousel-inner .carousel-item-next,
    #carousel-regions .carousel-inner .carousel-item-prev,
    #carousel-cities .carousel-inner .carousel-item.active,
    #carousel-cities .carousel-inner .carousel-item-next,
    #carousel-cities .carousel-inner .carousel-item-prev {
        display: flex;
    }

    /* medium and up screens */
    @media (min-width: 768px) {
        #carousel-regions .carousel-inner .carousel-item img,
        #carousel-cities .carousel-inner .carousel-item img {
            margin-right: 2px;
        }
        
        #carousel-regions .carousel-inner .carousel-item-end.active,
        #carousel-regions .carousel-inner .carousel-item-next,
        #carousel-cities .carousel-inner .carousel-item-end.active,
        #carousel-cities .carousel-inner .carousel-item-next {
          transform: translateX(25%);
        }
        
        #carousel-regions .carousel-inner .carousel-item-start.active, 
        #carousel-regions .carousel-inner .carousel-item-prev,
        #carousel-cities .carousel-inner .carousel-item-start.active, 
        #carousel-cities .carousel-inner .carousel-item-prev {
          transform: translateX(-25%);
        }
    }

    #carousel-regions .carousel-inner .carousel-item-end,
    #carousel-regions .carousel-inner .carousel-item-start,
    #carousel-cities .carousel-inner .carousel-item-end,
    #carousel-cities .carousel-inner .carousel-item-start { 
      transform: translateX(0);
    }

    #map-placeholder {
        width:100%;
        height: 200px;
        background-image: url({{ url('') }}/assets/images/map-bg.png);
        text-align: center;
        padding-top: 40%;
    }
    #map-placeholder button {
        position: absolute;
        top: 44%;
        left: 37%;
    }

    /*.country-listing-item .carousel-inner {
        height: 256px;
    }*/

    /* .country-listing-item .carousel-control-prev,
    .country-listing-item .carousel-control-next {
        width: 30px!important;
        height: 30px!important;
    }

    .country-listing-item .carousel-item,
    .country-listing-item .carousel-item img {
        width: 256px!important;
        height: 300px!important;
        object-fit: cover;
    } */
     
    #offcanvasBottomSearch {
        height: 90%!important;
    }

    .btn-outline-theme {
        color: #E8604C!important;
        border-color: #E8604C!important;
    }
    /*.select2-container--default {
        padding-left: 25px !important;
    }
    .select2-search__field {
        border-bottom: 1px solid #ccc!important;
    }*/
    #num-guests {
        background: url("data:image/svg+xml,<svg height='10px' width='10px' viewBox='0 0 16 16' fill='%23808080' xmlns='http://www.w3.org/2000/svg'><path d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/></svg>") no-repeat;
        background-position: right 0.3rem center !important;
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
    /*.see-more {
        display: inline-block;
        color: blue;
        cursor: pointer;
        text-decoration: underline;
    }*/
    #fish_size_limit_table th:first-child, 
    #fish_size_limit_table td:first-child,
    #fish_time_limit_table th:first-child, 
    #fish_time_limit_table td:first-child
    {
        /*background-color: #fad4b9;*/
    }
    @media (min-width: 400px) {
        #fish_chart_table th:first-child, 
        #fish_chart_table td:first-child
        {
            position:sticky;
            left:0px;
            background-color: #fff;
            min-width: 156px !important;
        }
    }
    .card-img-overlay h5 {
        position: absolute;
        bottom: 20px;
        left: 20px;
        color: #fff;
    }
    .read-more-btn {
        background-color: #E8604C !important;
        color: #fff !important;
        border: 2px solid #E8604C !important;
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
    .dimg-fluid {
        width: 300px;
        height:300px;
    }
    .filter-select {
        background: url("data:image/svg+xml,<svg height='10px' width='10px' viewBox='0 0 16 16' fill='%23808080' xmlns='http://www.w3.org/2000/svg'><path d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/></svg>") no-repeat;
        background-position: right 0.3rem center !important;
        padding-left: 30px !important;
        border: 0;
        border-bottom: 1px solid #ccc;
    }

    .filter-group {
        position: relative;
        margin-bottom: 1rem;
    }

    .filter-icon {
        position: absolute;
        left: 8px;
        top: 50%;
        transform: translateY(-50%);
        z-index: 1;
        color: #808080;
    }

    /* Override Select2 styles to match */
    .select2-container--default .select2-selection--single,
    .select2-container--default .select2-selection--multiple {
        border: 0 !important;
        border-bottom: 1px solid #ccc !important;
        border-radius: 0 !important;
        padding-left: 30px !important;
    }
</style>
@endsection

@section('content')
    <!--News One Start-->
    <div class="container" id="destination">
        <div class="row">
            <div class="col-12">
                <div id="page-main-intro" class="mb-3">
                    <div class="page-main-intro-text mb-1">{!! translate(nl2br($row_data->introduction)) !!}</div>
                    <p class="see-more text-center"><a href="#" class="btn btn-primary btn-sm read-more-btn">@lang('destination.read_more')</a></p>
                </div>
                @php
                $region_count = $regions->count();
                $city_count = $cities->count();
                $region_counter = 0;
                $city_counter = 0;
                @endphp

                @if($region_count > 0)
                    <h5 class="mb-2">@lang('destination.all_region')</h5>
                    <div id="carousel-regions" class="owl-carousel owl-theme mb-4">
                        @foreach($regions as $region)
                            <div class="item">
                                <div class="col-sm-12">
                                    <a href="{{ route('destination.country', ['country' => $region->country_slug, 'region' => $region->slug]) }}">
                                        <div class="card">
                                            <div class="card-img">
                                                <img src="{{ $region->getThumbnailPath() }}" class="dimg-fluid" alt="Image Not Available">
                                            </div>
                                            <div class="card-img-overlay">
                                                <h5>{{ translate($region->name) }}</h5>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
                @if($city_count > 0)
                <h5 class="mb-2">@lang('destination.all_cities')</h5>
                <div id="carousel-cities" class="owl-carousel owl-theme mb-4">
                    @foreach($cities as $city)
                        <div class="item">
                            <div class="col-sm-12 col-lgs-3">
                                <a href="{{ route('destination.country', ['country' => $city->country_slug, 'region' => $city->region_slug, 'city' => $city->slug]) }}">
                                    <div class="card">
                                        <div class="card-img">
                                            <img src="{{ $city->getThumbnailPath() }}" class="dimg-fluid" alt="Image Not Available">
                                        </div>
                                        <div class="card-img-overlay">
                                            <h5>{{ translate($city->name) }}</h5>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
                @endif
                <h5 class="mb-2">{{ translate('Fishing tours in ' . $row_data->name) }}</h5>
                <div class="row mb-5">
                    <div class="col-12 col-sm-4 col-md-12 d-flex mb-3 d-block d-sm-none mobile-selection-sfm">
                        <div class="d-grid gap-2 w-100">
                            <div class="btn-group" role="group" aria-label="Button group with nested dropdown">
                                <div class="btn-group border rounded-start cag-btn-inverted" role="group" style=" width:30%;">
                                    <button type="button" class="btn dropdown-toggle text-white" data-bs-toggle="dropdown" aria-expanded="false"><i class="fa fa-arrow-down-arrow-up me-1"></i>@lang('message.sortby')</button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ url()->current() }}?sortby=newest">@lang('message.newest')</a></li>
                                        <li><a class="dropdown-item" href="{{ url()->current() }}?sortby=price-asc">@lang('message.lowprice')</a></li>
                                        <li><a class="dropdown-item" href="{{ url()->current() }}?sortby=short-duration">@lang('message.shortduration')</a></li>
                                        <li><a class="dropdown-item" href="{{ url()->current() }}?sortby=long-duration">@lang('message.longduration')</a></li>
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
                                    @if($guidings_total > 0)
                                        @if(request()->has('radius') || request()->has('num_guests') || request()->has('target_fish') || request()->has('water') || request()->has('fishing_type') || request()->has('price_range'))
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="guiding-filter-counter">{{ $guidings->count() }}</span>
                                        @endif
                                    @endif
                                </a>
                                <a class="btn border cag-btn-inverted" data-bs-target="#mapModal" data-bs-toggle="modal" href="javascript:void(0)" style=" border-left: 2px solid #ccc!important; width:40%;"><i class="fa fa-map-marker-alt me-2"></i>@lang('destination.show_on_map')</a>

                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 col-lg-3">
                        <div class="card mb-2 d-none d-sm-block">
                            <div id="map-placeholder">
                                <button class="btn btn-primary read-more-btn" data-bs-target="#mapModal" data-bs-toggle="modal">@lang('destination.show_on_map')</button>
                            </div>
                        </div>
                        <div class="card d-block d-none d-sm-block mb-1">
                            <div class="card-header">
                                @lang('message.sortby'):
                            </div>
                            <div class="card-body border-bottom">
                                <div class="btn-group w-100">
                                    <button type="button" class="btn dropdown-toggle text-dark" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fa fa-arrow-down-arrow-up me-1"></i>
                                        @if(request()->get('sortby') == 'newest')
                                            @lang('message.newest')
                                        @elseif(request()->get('sortby') == 'price-asc') 
                                            @lang('message.lowprice')
                                        @elseif(request()->get('sortby') == 'short-duration')
                                            @lang('message.shortduration') 
                                        @elseif(request()->get('sortby') == 'long-duration')
                                            @lang('message.longduration')
                                        @else
                                            @lang('message.choose')...
                                        @endif
                                    </button>
                                    <ul class="dropdown-menu w-100">
                                        <li><a class="dropdown-item" href="{{ url()->current() }}?sortby=newest">@lang('message.newest')</a></li>
                                        <li><a class="dropdown-item" href="{{ url()->current() }}?sortby=price-asc">@lang('message.lowprice')</a></li>
                                        <li><a class="dropdown-item" href="{{ url()->current() }}?sortby=short-duration">@lang('message.shortduration')</a></li>
                                        <li><a class="dropdown-item" href="{{ url()->current() }}?sortby=long-duration">@lang('message.longduration')</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="card d-block d-none d-sm-block">
                            <div class="card-header">
                                @lang('destination.filter_by'):
                            </div>
                            <div class="card-body border-bottom">
                                <form method="get" action="{{ url()->current() }}">
                                    <div class="filter-group">
                                        <div class="filter-icon">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <select class="form-control filter-select" id="num_guests" name="num_guests">
                                            <option disabled selected hidden>-- @lang('destination.select') --</option>
                                            <option value="">@lang('message.choose')...</option>
                                            <option value="1" {{ request()->get('num_guests') ? request()->get('num_guests') == 1 ? 'selected' : null : null }}>1</option>
                                            <option value="2" {{ request()->get('num_guests') ? request()->get('num_guests') == 2 ? 'selected' : null : null }}>2</option>
                                            <option value="3" {{ request()->get('num_guests') ? request()->get('num_guests') == 3 ? 'selected' : null : null }}>3</option>
                                            <option value="4" {{ request()->get('num_guests') ? request()->get('num_guests') == 4 ? 'selected' : null : null }}>4</option>
                                            <option value="5" {{ request()->get('num_guests') ? request()->get('num_guests') == 5 ? 'selected' : null : null }}>5</option>
                                        </select>
                                    </div>

                                    <div class="filter-group">
                                        <div class="filter-icon">
                                            <img src="{{asset('assets/images/icons/fish.png')}}" height="20" width="20" alt="" />
                                        </div>
                                        <select class="form-control filter-select" id="target_fish" name="target_fish[]"></select>
                                    </div>
                                    <div class="filter-group">
                                        <div class="filter-icon">
                                            <img src="{{asset('assets/images/icons/water-waves.png')}}" height="20" width="20" alt="" />
                                        </div>
                                        <select class="form-control filter-select" id="water" name="water[]"></select>
                                    </div>
                                    <div class="filter-group">
                                        <div class="filter-icon">
                                            <img src="{{asset('assets/images/icons/fishing.png')}}" height="20" width="20" alt="" />
                                        </div>
                                        <select class="form-control filter-select" id="methods" name="methods[]"></select>
                                    </div>
                                    <div class="filter-group">
                                        <div class="filter-icon">
                                            <i class="fa fa-euro-sign"></i>
                                        </div>
                                        <select class="form-control filter-select" id="price_range" name="price_range">
                                            <option selected disabled hidden>{{ translate('Price per Person') }}</option>
                                            <option value="" >@lang('message.choose')...</option>
                                            <option value="1-50" {{ request()->get('price_range') ? request()->get('price_range') == '1-200' ? 'selected' : null : null }}>1 - 50 p.P.</option>
                                            <option value="51-100" {{ request()->get('price_range') ? request()->get('price_range') == '201-400' ? 'selected' : null : null }}>51 - 100 p.P.</option>
                                            <option value="101-150" {{ request()->get('price_range') ? request()->get('price_range') == '401-600' ? 'selected' : null : null }}>101 - 150 p.P.</option>
                                            <option value="151-200" {{ request()->get('price_range') ? request()->get('price_range') == '601-800' ? 'selected' : null : null }}>151 - 200 p.P.</option>
                                            <option value="201-250" {{ request()->get('price_range') ? request()->get('price_range') == '801-1000' ? 'selected' : null : null }}>201 - 250 p.P.</option>
                                            <option value="350" {{ request()->get('price_range') ? request()->get('price_range') == '1001' ? 'selected' : null : null }}>350 and more</option>
                                        </select>
                                    </div>
                                    <button class="btn btn-sm theme-primary btn-theme-new w-100" type="submit">@lang('destination.search')</button>
                                </form> 
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 col-lg-9 country-listing-item">
                        @foreach($guidings as $guiding)
                        <div class="row m-0 mb-2 guiding-list-item">
                            <div class="tours-list__right col-md-12">
                                <div class="row p-2 border shadow-sm bg-white rounded">
                                    <div class="col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 mt-1 p-0">
                                        <div id="carouselExampleControls-{{$guiding->id}}" class="carousel slide" data-bs-ride="carousel" data-bs-interval="false">
                                            <div class="carousel-inner">
                                                @if(count(get_galleries_image_link($guiding)))
                                                    @foreach(get_galleries_image_link($guiding) as $index => $gallery_image_link)
                                                        <div class="carousel-item @if($index == 0) active @endif">
                                                            <img  class="carousel-image" src="{{asset($gallery_image_link)}}">
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
                                    <div class="guiding-item-desc col-12 col-sm-12 col-md-8 col-lg-8 col-xl-8 col-xxl-8 p-2 p-md-3 mt-md-1">
                                    <a href="{{ route('guidings.show', [$guiding->id, $guiding->slug]) }}">
                                            <div class="guidings-item">
                                                <div class="guidings-item-title">
                                                @if(!$agent->ismobile())
                                                <h5 class="fw-bolder text-truncate">{{translate($guiding->title)}}</h5>
                                                @endif
                                                @if($agent->ismobile())
                                                    <h5 class="fw-bolder text-truncate">{{ translate(Str::limit($guiding->title, 45)) }}</h5>
                                                @endif
                                                    <span class="text-center"><i class="fas fa-map-marker-alt me-2"></i>{{ translate($guiding->location) }} </span>                                      
                                                </div>
                                                @if ($guiding->user->average_rating())
                                                <div class="guidings-item-ratings">
                                                <div class="ratings-score">
                                                        <span class="text-warning">★</span>
                                                        <span>{{$guiding->user->average_rating()}} </span>
                                                    </div>
                                                </div>
                                                @endif
                                            </div>
                                            <div class="guidings-item-icon">
                                                <div class="guidings-icon-container"> 
                                                            <img src="{{asset('assets/images/icons/clock-new.svg')}}" height="20" width="20" alt="" />
                                                        <div class="">
                                                            {{ $guiding->duration }} @if($guiding->duration != 1) {{translate('Stunden')}} @else {{translate('Stunde')}} @endif
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
                                                                    {{ translate(implode(', ', $guidingTargets)) }}
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
                                                                    $inclusions = $guiding->getInclusionNames();
                                                                    $maxToShow = 3; // Maximum number of inclusions to display
                                                                @endphp

                                                                @foreach ($inclusions as $index => $inclusion)
                                                                    @if ($index < $maxToShow)
                                                                        <span class="inclusion-item"><i class="fa fa-check"></i>{{ translate($inclusion['name']) }}</span>
                                                                    @endif
                                                                @endforeach

                                                                @if (count($inclusions) > $maxToShow)
                                                                    <span class="inclusion-item">+{{ count($inclusions) - $maxToShow }} more</span>
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

                <div class="mb-3">{!! translate($row_data->content) !!}</div>

                @if($row_data->fish_avail_title != '' && $row_data->fish_avail_intro != '')
                    <h2 class="mb-2 mt-5">{{ translate($row_data->fish_avail_title) }}</h2>
                    <p>{!! translate($row_data->fish_avail_intro) !!}</p>
                    @if($fish_chart->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered " id="fish_chart_table">
                            <thead>
                                <tr>
                                    <th width="28%">@lang('destination.fish')</th>
                                    <th width="6%" class="text-center">{{ translate('Jan') }}</th>
                                    <th width="6%" class="text-center">{{ translate('Feb') }}</th>
                                    <th width="6%" class="text-center">{{ translate('Mar') }}</th>
                                    <th width="6%" class="text-center">{{ translate('Apr') }}</th>
                                    <th width="6%" class="text-center">{{ translate('May') }}</th>
                                    <th width="6%" class="text-center">{{ translate('Jun') }}</th>
                                    <th width="6%" class="text-center">{{ translate('Jul') }}</th>
                                    <th width="6%" class="text-center">{{ translate('Aug') }}</th>
                                    <th width="6%" class="text-center">{{ translate('Sep') }}</th>
                                    <th width="6%" class="text-center">{{ translate('Oct') }}</th>
                                    <th width="6%" class="text-center">{{ translate('Nov') }}</th>
                                    <th width="6%" class="text-center">{{ translate('Dec') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($fish_chart as $row)
                                <tr>
                                    <td>{{ $row->fish }}</td>
                                    <td class="text-center" style="background-color: {{ $row->bg_color($row->jan) }}"></td>
                                    <td class="text-center" style="background-color: {{ $row->bg_color($row->feb) }}"></td>
                                    <td class="text-center" style="background-color: {{ $row->bg_color($row->mar) }}"></td>
                                    <td class="text-center" style="background-color: {{ $row->bg_color($row->apr) }}"></td>
                                    <td class="text-center" style="background-color: {{ $row->bg_color($row->may) }}"></td>
                                    <td class="text-center" style="background-color: {{ $row->bg_color($row->jun) }}"></td>
                                    <td class="text-center" style="background-color: {{ $row->bg_color($row->jul) }}"></td>
                                    <td class="text-center" style="background-color: {{ $row->bg_color($row->aug) }}"></td>
                                    <td class="text-center" style="background-color: {{ $row->bg_color($row->sep) }}"></td>
                                    <td class="text-center" style="background-color: {{ $row->bg_color($row->oct) }}"></td>
                                    <td class="text-center" style="background-color: {{ $row->bg_color($row->nov) }}"></td>
                                    <td class="text-center" style="background-color: {{ $row->bg_color($row->dec) }}"></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif
                @endif

                <div class="row">
                    @if($row_data->size_limit_title != '' && $row_data->size_limit_intro != '')
                    <div class="col-sm-12 col-md-12 col-lg-12 mt-5">
                        <h2>{{ translate($row_data->size_limit_title) }}</h2>
                        <p>{!! translate($row_data->size_limit_intro) !!}</p>
                        @if(!empty($fish_size_limit))
                        <table class="table table-bordered table-striped" id="fish_size_limit_table">
                            <thead>
                                <tr>
                                    <th width="20%">@lang('destination.fish')</th>
                                    <th width="80%">{{ translate('Size Limit') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                            @if(!empty($fish_size_limit))
                                @foreach($fish_size_limit as $row)
                                <tr>
                                    <td>{{ translate($row->fish) }}</td>
                                    <td>{{ translate($row->data) }}</td>
                                </tr>
                                @endforeach
                            @endif
                            </tbody>
                        </table>
                        @endif
                    </div>
                    @endif
                    @if($row_data->time_limit_title != '' && $row_data->time_limit_intro != '')
                    <div class="col-sm-12 col-md-12 col-lg-12 mt-5">
                        <h2>{{ translate($row_data->time_limit_title) }}</h2>
                        <p>{!! translate($row_data->time_limit_intro) !!}</p>
                        @if(!empty($fish_time_limit))
                        <table class="table table-bordered table-striped" id="fish_time_limit_table">
                            <thead>
                                <tr>
                                    <th width="20%">@lang('destination.fish')</th>
                                    <th width="80%">{{ translate('Time Limit') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                            @if(!empty($fish_time_limit))
                                @foreach($fish_time_limit as $row)
                                <tr>
                                    <td>{{ translate($row->fish) }}</td>
                                    <td>{{ translate($row->data) }}</td>
                                </tr>
                                @endforeach
                            @endif
                            </tbody>
                        </table>
                        @endif
                    </div>
                    @endif
                </div>
                @if($row_data->faq_title != '' && $faq->count() > 0)
                <h2 class="mb-3 mt-5">{{ translate($row_data->faq_title) }}</h2>
                    <div class="accordion mb-5" id="faq">
                        @foreach($faq as $row)
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq{{ $row->id }}" aria-expanded="true" aria-controls="faq{{ $row->id }}">{{ translate($row->question) }}</button>
                                </h2>
                                <div class="accordion-collapse collapse" id="faq{{ $row->id }}" data-bs-parent="#faq">
                                    <div class="accordion-body ">{{ translate($row->answer) }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
    <!--News One End-->

    <div class="modal show" id="mapModal" tabindex="-1" aria-labelledby="mapModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" style="width:90%!important; max-width: 100%; height:90%;">
            <div class="modal-content" style="height:100%;">
                <div id="map" class="modal-body"></div>
            </div>
        </div>
    </div>

    <div class="offcanvas offcanvas-bottom" tabindex="-1" id="offcanvasBottomSearch" aria-labelledby="offcanvasBottomLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasBottomLabel">{{ translate('Filter') }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body small">
            <form id="filterContainerOffCanvass" action="{{ url()->current() }}" method="get" class="px-4 py-2">
                <div class="row">
                    <div class="col-12">
                        <div class="input-group my-1">
                            <div class="input-group-prepend border-0 border-bottom ">
                                <span class="d-flex align-items-center px-2 h-100">
                                    <i class="fas fa-user"></i>
                                </span>
                            </div>
                            <select id="num-guestsOffCanvass" class="form-control form-select  border-0 border-bottom rounded-0 custom-select" name="num_guests">
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
                                <option selected disabled hidden>{{ translate('Price per Person') }}</option>
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
    $('#sortby').on('change',function(){
        $('#form-sortby').submit();
    });
    
    $(document).ready(function(){
    $('#carousel-regions').owlCarousel({
        loop: false,
        margin: 10,
        nav: true,
        navText: ['<', '>'],
        autoplay: true,
        responsive: {
            0: {
                items: 1
            },
            600: {
                items: 2
            },
            1000: {
                items: 4
            }
        }
    });

    $('#carousel-cities').owlCarousel({
        loop: false,            // Infinite looping
        margin: 10,            // Space between items
        nav: true,             // Show next/prev buttons
        dots: true,            // Show pagination dots
        autoplay: true,        // Enable auto-play
        navText: ['<', '>'],
        responsive: {
            0: {
                items: 1   // Show 1 item on small screens
            },
            600: {
                items: 2   // Show 2 items on medium screens
            },
            1000: {
                items: 4   // Show 4 items on large screens
            }
        }
    });
});




    let itemsCollapseCities = document.querySelectorAll('#carousel-cities .carousel-item');
    itemsCollapseCities.forEach((el) => {
        const minPerSlide = (itemsCollapseCities.length >= 4) ? 4 : itemsCollapseCities.length;
        let next = el.nextElementSibling
        for (var i=1; i<minPerSlide; i++) {
            if (!next) {
                next = itemsCollapseCities[0]
            }
            let cloneChild = next.cloneNode(true)
            el.appendChild(cloneChild.children[0])
            next = next.nextElementSibling
        }
    });
    
    $(function() {
        var word_char_count_allowed = $(window).width() <= 768 ? 300 : 1200;  // Adjust character count based on screen size
        var page_main_intro = $('.page-main-intro-text');
        var page_main_intro_text = page_main_intro.html();
        var page_main_intro_count = page_main_intro.text().length;
        var ellipsis = "..."; 
        var moreText = '<a href="#" class="btn btn-primary btn-sm read-more-btn">@lang('destination.read_more')</a>';
        var lessText = '<a href="#" class="btn btn-primary btn-sm read-more-btn">@lang('destination.read_less')</a>';

        var visible_text = page_main_intro_text.substring(0, word_char_count_allowed);
        var hidden_text = page_main_intro_text.substring(word_char_count_allowed);

        if (page_main_intro_count >= word_char_count_allowed) {
            $('.page-main-intro-text').html(visible_text + '<span class="more-ellipsis">' + ellipsis + '</span><span class="more-text" style="display:none;">' + hidden_text + '</span>');
            //$('.more-text').show();
            $('.see-more').click(function(e) {
                e.preventDefault();
                var textContainer = $(this).prev('.page-main-intro-text'); // Get the content element

                if ($(this).hasClass('less')) {
                    $(this).removeClass('less');
                    $(this).html(moreText);
                    textContainer.find('.more-text').hide();
                    textContainer.find('.more-ellipsis').show();
                } else {
                    $(this).addClass('less');
                    $(this).html(lessText);
                    textContainer.find('.more-text').show();
                    textContainer.find('.more-ellipsis').hide();
                }
            });
        } else {
            $('.see-more').hide();
        }

        // Re-adjust the text length if window is resized
        $(window).resize(function() {
            word_char_count_allowed = $(window).width() <= 768 ? 300 : 1200;
            visible_text = page_main_intro_text.substring(0, word_char_count_allowed);
            hidden_text = page_main_intro_text.substring(word_char_count_allowed);

            if (page_main_intro_count >= word_char_count_allowed) {
                $('.page-main-intro-text').html(visible_text + '<span class="more-ellipsis">' + ellipsis + '</span><span class="more-text" style="display:none;">' + hidden_text + '</span>');
            }
        });
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

    var selectTarget = $('#target_fish, #target_fishOffCanvass');
    var selectWater = $('#water, #waterOffCanvass');
    var selectMethod = $('#methods, #methodsOffCanvass');

    selectTarget.select2({
        multiple: true,
        placeholder: '@lang('message.target-fish')',
        width: 'resolve', // need to override the changed default
    });
  
    @if(request()->get('price_range'))
        $('#price_range, #price_rangeOffCanvass').val('{{ request()->get('price_range') }}');
    @endif

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


    selectWater.select2({
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



    selectMethod.select2({
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
        mapTypeControl: false,
        streetViewControl: false,
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
    return null;
    // var geocoder = new google.maps.Geocoder();
    // var latlng = new google.maps.LatLng(lat, lng);
    // geocoder.geocode({'latLng': latlng}, function (results, status) {
    //     if (status === google.maps.GeocoderStatus.OK) {
    //         if (results[0]) {
    //             document.getElementById('searchPlace').value = results[0].formatted_address;
    //         } else {
    //             console.log('No results found');
    //             return null;
    //         }
    //     } else {
    //         console.log('Geocoder failed due to: ' + status);
    //         return null;
    //     }
    // });
}

       
</script>


    <script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>

@endsection
