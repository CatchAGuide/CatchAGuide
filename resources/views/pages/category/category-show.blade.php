@extends('layouts.app-v2')

@section('title', $row_data->language->title)
@section('description', $row_data->language->introduction)
@section('header_title', $row_data->language->title)
@section('header_sub_title', $row_data->language->sub_title)

@section('share_tags')
    <meta property="og:title" content="{{$row_data->source->name}}" />
    <meta property="og:description" content="{{$row_data->language->introduction ?? ""}}" />
    
    @if(isset($row_data->thumbnail_path) && file_exists(public_path(str_replace(asset(''), '', asset($row_data->thumbnail_path)))))
        <meta property="og:image" content="{{asset($row_data->thumbnail_path)}}"/>
    @endif
@endsection

@section('custom_style')
<style>
    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 15px;
        width: 100%;
    }

    .carousel.slide img {
        height: 250px;
        object-fit: cover;
        width: 100%;
        background: black;
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

    /* Filter styles */
    .card {
        border-radius: 4px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        margin-bottom: 15px;
    }

    .card-header {
        background-color: #f8f9fa;
        padding: 12px 15px;
        font-weight: 600;
        border-bottom: 1px solid rgba(0,0,0,0.125);
    }

    .card-body {
        padding: 15px;
    }

    .filter-group {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
        position: relative;
    }

    .filter-icon {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        z-index: 1;
        color: #666;
    }

    .filter-select {
        padding-left: 35px !important;
        width: 100%;
        height: 38px;
        border-radius: 4px;
        border: 1px solid #ced4da;
    }

    .btn-theme-new {
        background-color: #E8604C;
        color: white;
        border: none;
        padding: 8px 15px;
        border-radius: 4px;
        font-weight: 500;
        transition: background-color 0.2s;
    }

    .btn-theme-new:hover {
        background-color: #d4503c;
    }

    /* Guiding item styles */
    .guiding-item-price {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
    }

    .guiding-item-price h5 {
        margin: 0;
        white-space: nowrap;
        font-size: clamp(14px, 2vw, 18px);
    }

    .guiding-item-price span {
        display: inline-block;
        padding: 4px 8px;
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
        min-width: 0;
    }

    .guidings-included {
        font-size: 14px;
    }

    .guidings-included strong {
        display: block;
        margin-bottom: 5px;
    }

    .inclusions-list {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        max-width: 100%;
    }

    .inclusion-item {
        white-space: nowrap;
        padding: 2px 8px;
        border-radius: 4px;
        display: inline-flex;
        align-items: center;
        font-size: 13px;
        background-color: #f8f9fa;
    }

    .inclusion-item i {
        font-size: 10px;
        margin-right: 4px;
        color: #E8604C;
    }

    .guiding-item-price {
        text-align: right;
        min-width: fit-content;
        padding-left: 10px;
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

    .guidings-item-title i {
        font-size: 13px;
        margin-right: 4px;
        color: #666;
    }

    .guidings-item-icon {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 10px;
    }

    .guidings-icon-container {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 14px;
        color: #555;
        margin-right: 15px;
    }

    .guidings-icon-container img {
        margin-right: 8px;
    }

    .ave-reviews-row {
        display: flex;
        align-items: center;
        gap: 5px;
        margin-bottom: 10px;
    }

    .ratings-score {
        background-color: #E8604C;
        color: white;
        padding: 2px 8px;
        border-radius: 4px;
        font-weight: bold;
        margin-right: 5px;
    }

    .no-reviews {
        color: #777;
        font-size: 14px;
        margin-bottom: 10px;
    }

    /* Map placeholder */
    #map-placeholder {
        position: relative;
        height: 200px;
        background-color: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
    }

    #map-placeholder button {
        background-color: #E8604C;
        color: white;
        border: none;
        padding: 8px 15px;
        border-radius: 4px;
    }

    /* Mobile styles */
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
            font-size: 13px;
            padding: 3px 10px;
        }
        
        .guidings-included strong {
            font-size: 14px;
        }

        .guidings-item-title h5 {
            font-size: 18px;
            margin-bottom: 0;
        }
        
        .guidings-item-title span {
            font-size: 15px;
        }

        .inclusion-item {
            font-size: 15px;
            padding: 3px 10px;
        }
        
        .guiding-item-price h5 {
            font-size: 18px;
        }
        
        .guidings-included strong {
            font-size: 15px;
        }

        .mobile-selection-sfm {
            margin-bottom: 15px;
        }

        .mobile-selection-sfm .btn-group {
            width: 100%;
        }
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

    /* Filter sidebar specific styles */
    .filter-form {
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .filter-form select {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #ced4da;
        border-radius: 4px;
        background-color: white;
    }

    .filter-form button {
        margin-top: 10px;
    }

    /* Fix for select2 */
    .select2-container {
        width: 100% !important;
    }

    /* Additional styling to match the image */
    .guiding-list-item {
        border: 1px solid #e5e5e5;
        border-radius: 8px;
        overflow: hidden;
        margin-bottom: 20px;
        background-color: #fff;
    }
    
    .carousel-item img {
        height: 250px;
        object-fit: cover;
        width: 100%;
    }
    
    .guidings-item-title {
        display: flex;
        flex-direction: column;
        margin-bottom: 10px;
    }
    
    .guidings-item-title h5 {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 5px;
        color: #333;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    
    .guidings-item-title span {
        color: #666;
        font-size: 14px;
    }
    
    .no-reviews-text {
        color: #666;
        font-size: 14px;
        margin-bottom: 15px;
    }
    
    .tour-info-row {
        display: flex;
        align-items: center;
        margin-bottom: 8px;
    }
    
    .tour-info-row img {
        margin-right: 10px;
    }
    
    .tour-info-text {
        color: #555;
        font-size: 14px;
    }
    
    .whats-included-title {
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 5px;
    }
    
    .inclusion-item {
        display: inline-block;
        margin-right: 10px;
        font-size: 13px;
    }
    
    .inclusion-item i {
        color: #E8604C;
        margin-right: 3px;
    }
    
    .price-display {
        text-align: right;
        font-size: 16px;
        font-weight: 600;
    }
</style>
@endsection

@section('content')
    <div class="container" id="destination">
        <div class="container">
            <section class="page-header">
                <div class="page-header__bottom breadcrumb-container guiding">
                    <div class="page-header__bottom-inner">
                        <ul class="thm-breadcrumb list-unstyled">
                            <li><a href="{{ route('welcome') }}">@lang('message.home')</a></li>
                            <li><span><i class="fas fa-solid fa-chevron-right"></i></span></li>
                            <li><a href="{{ route('target-fish.index') }}">Target Fish</a></li>
                            <li><span><i class="fas fa-solid fa-chevron-right"></i></span></li>
                            <li class="active">{{ $row_data->source->name }}</li>
                        </ul>
                    </div>
                </div>
            </section>
        </div>

        <div class="container">
            <div class="col-12">
                <div id="page-main-intro" class="mb-3">
                    <div class="page-main-intro-text mb-1">{!! $row_data->language->introduction !!}</div>
                    <p class="see-more text-center"><a href="#" class="btn btn-primary btn-sm read-more-btn">@lang('destination.read_more')</a></p>
                </div>
                <h5 class="mb-2">{{ $row_data->source->name }}</h5>
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
                                    @if($category_total > 0)
                                        @if(request()->has('radius') || request()->has('num_guests') || request()->has('target_fish') || request()->has('water') || request()->has('fishing_type') || request()->has('price_range'))
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="guiding-filter-counter">{{ $category_total }}</span>
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
                                        <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sortby' => 'newest']) }}">@lang('message.newest')</a></li>
                                        <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sortby' => 'price-asc']) }}">@lang('message.lowprice')</a></li>
                                        <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sortby' => 'short-duration']) }}">@lang('message.shortduration')</a></li>
                                        <li><a class="dropdown-item" href="{{ request()->fullUrlWithQuery(['sortby' => 'long-duration']) }}">@lang('message.longduration')</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="card d-block d-none d-sm-block">
                            <div class="card-header">
                                @lang('destination.filter_by'):
                            </div>
                            <div class="card-body border-bottom">
                                <form method="get" action="{{ url()->current() }}" class="filter-form">
                                    @if(request()->has('sortby'))
                                        <input type="hidden" name="sortby" value="{{ request()->get('sortby') }}">
                                    @endif
                                    <div class="filter-group">
                                        <div class="filter-icon">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <select class="form-control filter-select" id="num_guests" name="num_guests">
                                            <option disabled selected hidden>-- @lang('destination.select') --</option>
                                            <option value="">@lang('message.choose')...</option>
                                            <option value="1">1</option>
                                            <option value="2">2</option>
                                            <option value="3">3</option>
                                            <option value="4">4</option>
                                            <option value="5">5</option>
                                        </select>
                                    </div>

                                    <div class="filter-group">
                                        <div class="filter-icon">
                                            <img src="{{asset('assets/images/icons/fish.png')}}" height="20" width="20" alt="" />
                                        </div>
                                        <select class="form-control filter-select" id="target_fish" name="target_fish[]" multiple></select>
                                    </div>
                                    <div class="filter-group">
                                        <div class="filter-icon">
                                            <img src="{{asset('assets/images/icons/water-waves.png')}}" height="20" width="20" alt="" />
                                        </div>
                                        <select class="form-control filter-select" id="water" name="water[]" multiple></select>
                                    </div>
                                    <div class="filter-group">
                                        <div class="filter-icon">
                                            <img src="{{asset('assets/images/icons/fishing.png')}}" height="20" width="20" alt="" />
                                        </div>
                                        <select class="form-control filter-select" id="methods" name="methods[]" multiple></select>
                                    </div>
                                    <div class="filter-group">
                                        <div class="filter-icon">
                                            <i class="fa fa-euro-sign"></i>
                                        </div>
                                        <select class="form-control filter-select" id="price_range" name="price_range">
                                            <option selected disabled hidden>{{ translate('Price per Person') }}</option>
                                            <option value="">@lang('message.choose')...</option>
                                            <option value="1-50">1 - 50 p.P.</option>
                                            <option value="51-100">51 - 100 p.P.</option>
                                            <option value="101-150">101 - 150 p.P.</option>
                                            <option value="151-200">151 - 200 p.P.</option>
                                            <option value="201-250">201 - 250 p.P.</option>
                                            <option value="350">350 and more</option>
                                        </select>
                                    </div>
                                    <button class="btn btn-sm theme-primary btn-theme-new w-100" type="submit">@lang('destination.search')</button>
                                </form> 
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 col-lg-9 country-listing-item">
                        @foreach($guides as $guiding)
                        <div class="row mb-2 guiding-list-item">
                            <div class="col-md-12">
                                <div class="row p-2 border shadow-sm bg-white rounded">
                                    <div class="col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 mt-1 p-0">
                                        <div id="carouselExampleControls-{{$guiding->id}}" class="carousel slide" data-bs-ride="carousel" data-bs-interval="false">
                                            <div class="carousel-inner">
                                                @if(count(get_galleries_image_link($guiding)))
                                                    @foreach(get_galleries_image_link($guiding) as $index => $gallery_image_link)
                                                        <div class="carousel-item @if($index == 0) active @endif">
                                                            <img class="d-block" src="{{asset($gallery_image_link)}}">
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
                                    <div class="col-12 col-sm-12 col-md-8 col-lg-8 col-xl-8 col-xxl-8 p-2 px-md-3 pt-md-2">
                                        <a href="{{ route('guidings.show', ['id' => $guiding->id, 'slug' => $guiding->slug, 'from_destination' => true, 'destination_id' => $row_data->id]) }}">
                                            <div class="row">
                                                <div class="d-flex justify-content-between align-items-start col-9">
                                                    <div class="guidings-item-title">
                                                        <h5>{{ Str::limit(translate($guiding->title), 50) }}</h5>
                                                        <span><i class="fas fa-map-marker-alt me-2"></i>{{ $guiding->location }}</span>
                                                    </div>
                                                </div>
                                                
                                                <div class="no-reviews-text col-3">
                                                    @if ($guiding->user->average_rating())
                                                        <div class="d-flex align-items-center">
                                                            <div class="ratings-score me-2">
                                                                <span class="rating-value">{{number_format($guiding->user->average_rating(), 1)}}</span>
                                                            </div>
                                                            <span>({{$guiding->user->reviews->count()}} reviews)</span>
                                                        </div>
                                                    @else
                                                        <span>No reviews yet</span>
                                                    @endif
                                                </div>
                                            </div>
                                            
                                            <div class="tour-info-row">
                                                <img src="{{asset('assets/images/icons/clock-new.svg')}}" height="20" width="20" alt="" />
                                                <div class="tour-info-text">{{$guiding->duration}} {{ $guiding->duration_type == 'multi_day' ? __('guidings.days') : __('guidings.hours') }}</div>
                                                
                                                <img src="{{asset('assets/images/icons/user-new.svg')}}" height="20" width="20" alt="" class="ms-4" />
                                                <div class="tour-info-text">{{ $guiding->max_guests }} @if($guiding->max_guests != 1) {{translate('Persons')}} @else {{translate('Person')}} @endif</div>
                                            </div>
                                            
                                            <div class="tour-info-row">
                                                <img src="{{asset('assets/images/icons/fish-new.svg')}}" height="20" width="20" alt="" />
                                                <div class="tour-info-text">
                                                    @php
                                                    $guidingTargets = collect($guiding->getTargetFishNames())->pluck('name')->toArray();
                                                    @endphp
                                                    
                                                    @if(!empty($guidingTargets))
                                                        {{ Str::limit(implode(', ', $guidingTargets), 60) }}
                                                    @endif
                                                </div>
                                            </div>
                                            
                                            <div class="tour-info-row">
                                                <img src="{{asset('assets/images/icons/fishing-tool-new.svg')}}" height="20" width="20" alt="" />
                                                <div class="tour-info-text">
                                                    {{$guiding->is_boat ? ($guiding->boatType && $guiding->boatType->name !== null ? $guiding->boatType->name : __('guidings.boat')) : __('guidings.shore')}}
                                                </div>
                                            </div>
                                            
                                            <div class="d-flex justify-content-between align-items-end">
                                                <div class="whats-included">
                                                    <div class="whats-included-title">What's Included</div>
                                                    <div>
                                                        @php
                                                            $inclussions = $guiding->getInclusionNames();
                                                            $maxToShow = 3;
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
                                                <div class="price-display">
                                                    From {{$guiding->getLowestPrice()}}€ p.P.
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                        {!! $guides->links('vendor.pagination.default') !!}
                    </div>
                </div>

                <div class="mb-3">{!! $row_data->language->content !!}</div>

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
                @if($row_data->language->faq_title != '' && $row_data->faq->count() > 0)
                <h2 class="mb-3 mt-5">{{ translate($row_data->language->faq_title) }}</h2>
                    <div class="accordion mb-5" id="faq">
                        @foreach($row_data->faq as $row)
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

    <div class="offcanvas offcanvas-bottom" tabindex="-1" id="offcanvasBottomSearch" aria-labelledby="offcanvasBottomLabel">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title" id="offcanvasBottomLabel">{{ translate('Filter') }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body small">
            <form id="filterContainerOffCanvass" action="{{ url()->current() }}" method="get" class="px-4 py-2">
                @if(request()->has('sortby'))
                    <input type="hidden" name="sortby" value="{{ request()->get('sortby') }}">
                @endif
                <div class="row">
                    <div class="col-12">
                        <div class="input-group my-1">
                            <div class="input-group-prepend border-0 border-bottom">
                                <span class="d-flex align-items-center px-2 h-100">
                                    <i class="fas fa-user"></i>
                                </span>
                            </div>
                            <select id="num-guestsOffCanvass" class="form-control form-select border-0 border-bottom rounded-0" name="num_guests">
                                <option value="">@lang('message.choose')...</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                            </select>
                        </div>
                    </div>
                 
                    <div class="col-12">
                        <div class="form-group my-1 d-flex align-items-center border-bottom">
                            <div class="px-2 select2-icon">
                                <img src="{{asset('assets/images/icons/fish.png')}}" height="20" width="20" alt="" />
                            </div>
                           
                            <select class="form-control form-select border-0 rounded-0" id="target_fishOffCanvass" name="target_fish[]" multiple></select>
                        </div>
                    </div>
                    
                    <div class="col-12">
                        <div class="form-group my-1 d-flex align-items-center border-bottom">
                            <div class="px-2 select2-icon">
                                <img src="{{asset('assets/images/icons/water-waves.png')}}" height="20" width="20" alt="" />
                            </div>
                            <select class="form-control form-select border-0  rounded-0" id="waterOffCanvass" name="water[]" multiple></select>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group my-1 d-flex align-items-center border-bottom ">
                            <div class="px-2 select2-icon">
                                <img src="{{asset('assets/images/icons/fishing.png')}}" height="20" width="20" alt="" />
                            </div>
                            <select class="form-control form-select border-0 rounded-0" id="methodsOffCanvass" name="methods[]" multiple></select>
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
                                <option value="1-50">1 - 50 p.P.</option>
                                <option value="51-100">51 - 100 p.P.</option>
                                <option value="101-150">101 - 150 p.P.</option>
                                <option value="151-200">151 - 200 p.P.</option>
                                <option value="201-250">201 - 250 p.P.</option>
                                <option value="350">350 and more</option>
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
if (toggleBtn) {
    toggleBtn.addEventListener('click', function() {
        filterContainer.classList.toggle('d-block');
    });
}
</script>

<script>
    initializeSelect2();

function initializeSelect2() {
    var selectTarget = $('#target_fish, #target_fishOffCanvass');
    var selectWater = $('#water, #waterOffCanvass');
    var selectMethod = $('#methods, #methodsOffCanvass');
    var selectPrice = $('#price_range, #price_rangeOffCanvass');
    var selectGuests = $('#num_guests, #num-guestsOffCanvass');

    // Clear all select2 instances first
    selectTarget.empty();
    selectWater.empty();
    selectMethod.empty();

    // Initialize select2 instances
    selectWater.select2({
        multiple: true,
        placeholder: '@lang('message.body-type')',
        width: 'resolve'
    });

    selectTarget.select2({
        multiple: true,
        placeholder: '@lang('message.target-fish')',
        width: 'resolve'
    });

    selectMethod.select2({
        multiple: true,
        placeholder: '{{translate('fishing type')}}',
        width: 'resolve'
    });

    // Get unique selected values from URL parameters
    var selectedFish = @json(array_unique(request()->get('target_fish') ?? []));
    var selectedWater = @json(array_unique(request()->get('water') ?? []));
    var selectedMethods = @json(array_unique(request()->get('methods') ?? []));

    // Add target fish options
    @foreach($alltargets as $fish)
        var fishname = '{{$fish->name}}';
        @if(app()->getLocale() == 'en')
            fishname = '{{$fish->name_en}}';
        @endif
        var fishOption = new Option(fishname, '{{ $fish->id }}', 
            selectedFish.includes('{{ $fish->id }}'),
            selectedFish.includes('{{ $fish->id }}')
        );
        selectTarget.append(fishOption);
    @endforeach

    // Add water options
    @foreach($allwaters as $water)
        var watername = '{{$water->name}}';
        @if(app()->getLocale() == 'en')
            watername = '{{$water->name_en}}';
        @endif
        var waterOption = new Option(watername, '{{ $water->id }}',
            selectedWater.includes('{{ $water->id }}'),
            selectedWater.includes('{{ $water->id }}')
        );
        selectWater.append(waterOption);
    @endforeach

    // Add fishing method options
    @foreach($allfishingfrom as $method)
        var methodname = '{{$method->name}}';
        @if(app()->getLocale() == 'en')
            methodname = '{{$method->name_en}}';
        @endif
        var methodOption = new Option(methodname, '{{ $method->id }}',
            selectedMethods.includes('{{ $method->id }}'),
            selectedMethods.includes('{{ $method->id }}')
        );
        selectMethod.append(methodOption);
    @endforeach

    // Set non-select2 values
    if ('{{ request()->get('num_guests') }}') {
        selectGuests.val('{{ request()->get('num_guests') }}');
    }
    
    if ('{{ request()->get('price_range') }}') {
        selectPrice.val('{{ request()->get('price_range') }}');
    }

    // Trigger final change events
    selectTarget.trigger('change');
    selectWater.trigger('change');
    selectMethod.trigger('change');
}

// Add form submit handler to clean up parameters
$('.filter-form, #filterContainerOffCanvass').on('submit', function(e) {
    e.preventDefault();
    
    var formData = new FormData(this);
    var params = new URLSearchParams();
    var seenValues = new Map();

    // Clean and add parameters without duplicates
    for (var pair of formData.entries()) {
        var key = pair[0];
        var value = pair[1];

        if (key.endsWith('[]')) {
            // Handle array parameters
            if (!seenValues.has(key)) {
                seenValues.set(key, new Set());
            }
            if (!seenValues.get(key).has(value) && value) {
                seenValues.get(key).add(value);
                params.append(key, value);
            }
        } else if (value) {
            params.append(key, value);
        }
    }

    window.location.href = `${window.location.pathname}?${params.toString()}`;
});

$(document).ready(function() {
    initializeSelect2();
});
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
        $lat = isset($guides[0]) ? $guides[0]->lat : 51.165691;
        $lng = isset($guides[0]) ? $guides[0]->lng : 10.451526;
    @endphp
    const position = { 
        lat: {{request()->get('placeLat') ? request()->get('placeLat') : $lat }},
        lng: {{request()->get('placeLng') ? request()->get('placeLng') : $lng }} 
    };
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
    var placeLatitude = '{{ request()->get('placeLat') }}';
    var placeLongitude = '{{ request()->get('placeLng') }}';

    if (placeLatitude && placeLongitude) {
        document.getElementById('placeLat').value = placeLatitude;
        document.getElementById('placeLng').value = placeLongitude;
    }
});       
</script>


    <script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>

@endsection
