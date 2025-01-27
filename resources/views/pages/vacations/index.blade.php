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
        .dimg-fluid {
            width: 100%!important;
        }
    }
    #map-placeholder {
        width:100%;
        height: 200px;
        background-image: url({{ url('') }}/assets/images/map-bg.png);
        text-align: center;
    }    
    #offcanvasBottomSearch {
        height: 90%!important;
    }

    .btn-outline-theme {
        color: #E8604C!important;
        border-color: #E8604C!important;
    }
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
    <div class="container" id="vacations-category">
        <div class="row">
            <div class="col-12">
                <div id="page-main-intro" class="mb-3">
                    <div class="page-main-intro-text mb-1">{!! translate(nl2br($row_data->introduction)) !!}</div>
                    <p class="see-more text-center"><a href="#" class="btn btn-primary btn-sm read-more-btn">@lang('vacations.read_more')</a></p>
                </div>
                <h5 class="mb-2">{{ translate('Vacations in ' . translate($row_data->name)) }}</h5>
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
                                    @if($vacations_total > 0)
                                        @if(request()->has('radius') || request()->has('num_guests') || request()->has('target_fish') || request()->has('water') || request()->has('fishing_type') || request()->has('price_range'))
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="guiding-filter-counter">{{ $vacations->count() }}</span>
                                        @endif
                                    @endif
                                </a>
                                <a class="btn border cag-btn-inverted" data-bs-target="#mapModal" data-bs-toggle="modal" href="javascript:void(0)" style=" border-left: 2px solid #ccc!important; width:40%;"><i class="fa fa-map-marker-alt me-2"></i>@lang('vacations.show_on_map')</a>

                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 col-lg-3">
                        <div class="card mb-2 d-none d-sm-block">
                            <div id="map-placeholder">
                                <button class="btn btn-primary read-more-btn" data-bs-target="#mapModal" data-bs-toggle="modal">@lang('vacations.show_on_map')</button>
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
                                @lang('vacations.filter_by'):
                            </div>
                            <div class="card-body border-bottom">
                                <form method="get" action="{{ url()->current() }}">
                                    <div class="filter-group">
                                        <div class="filter-icon">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <select class="form-control filter-select" id="num_guests" name="num_guests">
                                            <option disabled selected hidden>-- @lang('vacations.select') --</option>
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
                                            <i class="fa fa-euro-sign"></i>
                                        </div>
                                        <select class="form-control filter-select" id="price_range" name="price_range">
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
                                    <button class="btn btn-sm theme-primary btn-theme-new w-100" type="submit">@lang('vacations.search')</button>
                                </form> 
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 col-lg-9 country-listing-item">
                        @foreach($vacations as $vacation)
                        <div class="row m-0 mb-2 guiding-list-item">
                            <div class="tours-list__right col-md-12">
                                <div class="row p-2 border shadow-sm bg-white rounded">
                                    <div class="col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4 col-xxl-4 mt-1 p-0">
                                        <div id="carouselExampleControls-{{$vacation->id}}" class="carousel slide" data-bs-ride="carousel" data-bs-interval="false">
                                            <div class="carousel-inner">
                                                @if(count(get_galleries_image_link($vacation, 1)))
                                                    @foreach(get_galleries_image_link($vacation, 1) as $index => $gallery_image_link)
                                                        <div class="carousel-item @if($index == 0) active @endif">
                                                            <img  class="carousel-image" src="{{asset($gallery_image_link)}}">
                                                        </div>
                                                    @endforeach
                                                @endif
                                            </div>

                                            @if(count(get_galleries_image_link($vacation, 1)) > 1)
                                                <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleControls-{{$vacation->id}}" data-bs-slide="prev">
                                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                                    <span class="visually-hidden">Previous</span>
                                                </button>
                                                <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleControls-{{$vacation->id}}" data-bs-slide="next">
                                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                                    <span class="visually-hidden">Next</span>
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="guiding-item-desc col-12 col-sm-12 col-md-8 col-lg-8 col-xl-8 col-xxl-8 p-2 p-md-3 mt-md-1">
                                    <a href="{{ route('vacations.show', [$vacation->id, $vacation->slug]) }}">
                                        <div class="guidings-item">
                                            <div class="guidings-item-title">
                                                @if(!$agent->ismobile())
                                                <h5 class="fw-bolder text-truncate">{{translate($vacation->title)}}</h5>
                                                @endif
                                                @if($agent->ismobile())
                                                    <h5 class="fw-bolder text-truncate">{{ \Str::limit(translate($vacation->title), 45) }}</h5>
                                                @endif
                                                <span class="text-center"><i class="fas fa-map-marker-alt me-2"></i>{{ $vacation->location }} </span>                                      
                                            </div>
                                            <div class="inclusions-price">
                                            <div class="guiding-item-price">
                                                <h5 class="mr-1 fw-bold text-end"><span class="p-1">@lang('message.from') {{$vacation->getLowestPrice()}}€ p.P.</span></h5>
                                                <div class="d-none d-flex flex-column mt-4">
                                                </div>
                                            </div>
                                        </div>  
                                            {{-- @if ($vacation->user->average_rating())
                                            <div class="guidings-item-ratings">
                                            <div class="ratings-score">
                                                    <span class="text-warning">★</span>
                                                    <span>{{$guiding->user->average_rating()}} </span>
                                                </div>
                                            </div>
                                            @endif --}}
                                        </div>
                                        <div class="vacations-item-row">
                                            <div class="vacations-item-row-top">
                                            </div>
                                                <div class="vacations-info-container"> 
                                                    <span class="fw-bold">{{translate('Boat Available')}}:</span>
                                                    <span class="text-regular">{{ count($vacation->boats) > 0 ? translate('Available') : translate('Unavailable') }}</span>
                                                </div>
                                                <div class="vacations-info-container"> 
                                                    <span class="fw-bold">{{translate('Distance to the water')}}:</span>
                                                    <div class="">
                                                        {{ $vacation->water_distance }}
                                                    </div>
                                                </div>
                                            <div class="vacations-info-container"> 
                                                <span class="fw-bold">{{translate('Target Fish')}}:</span>
                                                <div class="d-flex">
                                                    @php
                                                        $target_fish = json_decode($vacation->target_fish);
                                                    @endphp
                                                    <ul class="list-unstyled mb-0 d-flex">
                                                        {{ translate(\Str::limit(implode(', ', $target_fish), limit:50 )) }}
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>  
                                    </a>
                                </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                        {!! $vacations->links('vendor.pagination.default') !!}
                    </div>
                </div>

                <div class="mb-3">{!! $row_data->content !!}</div>

                @if($row_data->fish_avail_title != '' && $row_data->fish_avail_intro != '')
                    <h2 class="mb-2 mt-5">{{ $row_data->fish_avail_title }}</h2>
                    <p>{!! $row_data->fish_avail_intro !!}</p>
                    @if($fish_chart->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered " id="fish_chart_table">
                            <thead>
                                <tr>
                                    <th width="28%">@lang('vacations.fish')</th>
                                    <th width="6%" class="text-center">Jan</th>
                                    <th width="6%" class="text-center">Feb</th>
                                    <th width="6%" class="text-center">Mar</th>
                                    <th width="6%" class="text-center">Apr</th>
                                    <th width="6%" class="text-center">May</th>
                                    <th width="6%" class="text-center">Jun</th>
                                    <th width="6%" class="text-center">Jul</th>
                                    <th width="6%" class="text-center">Aug</th>
                                    <th width="6%" class="text-center">Sep</th>
                                    <th width="6%" class="text-center">Oct</th>
                                    <th width="6%" class="text-center">Nov</th>
                                    <th width="6%" class="text-center">Dec</th>
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
                        <h2>{{ $row_data->size_limit_title }}</h2>
                        <p>{!! $row_data->size_limit_intro !!}</p>
                        @if(!empty($fish_size_limit))
                        <table class="table table-bordered table-striped" id="fish_size_limit_table">
                            <thead>
                                <tr>
                                    <th width="20%">@lang('vacations.fish')</th>
                                    <th width="80%">{{ translate('Size Limit') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                            @if(!empty($fish_size_limit))
                                @foreach($fish_size_limit as $row)
                                <tr>
                                    <td>{{ $row->fish }}</td>
                                    <td>{{ $row->data }}</td>
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
                        <h2>{{ $row_data->time_limit_title }}</h2>
                        <p>{!! $row_data->time_limit_intro !!}</p>
                        @if(!empty($fish_time_limit))
                        <table class="table table-bordered table-striped" id="fish_time_limit_table">
                            <thead>
                                <tr>
                                    <th width="20%">@lang('vacations.fish')</th>
                                    <th width="80%">{{ translate('Time Limit') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                            @if(!empty($fish_time_limit))
                                @foreach($fish_time_limit as $row)
                                <tr>
                                    <td>{{ $row->fish }}</td>
                                    <td>{{ $row->data }}</td>
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
                <h2 class="mb-3 mt-5">{{ $row_data->faq_title }}</h2>
                    <div class="accordion mb-5" id="faq">
                        @foreach($faq as $row)
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq{{ $row->id }}" aria-expanded="true" aria-controls="faq{{ $row->id }}">{{ $row->question }}</button>
                                </h2>
                                <div class="accordion-collapse collapse" id="faq{{ $row->id }}" data-bs-parent="#faq">
                                    <div class="accordion-body ">{{ $row->answer }}</div>
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
            <h5 class="offcanvas-title" id="offcanvasBottomLabel">Filter</h5>
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
                            <select id="num-guestsOffCanvass" class="form-control form-select border-0 border-bottom rounded-0 custom-select" name="num_guests">
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
    $('#sortby').on('change',function(){
        $('#form-sortby').submit();
    });
    
    $(function() {
        var word_char_count_allowed = $(window).width() <= 768 ? 300 : 1200;
        var content = $('#page-main-intro .page-main-intro-text');
        var seeMoreBtn = $('.see-more');
        var fullText = content.html();
        var textLength = content.text().length;
        var ellipsis = "...";
        var moreText = '<a href="#" class="btn btn-primary btn-sm read-more-btn">@lang('vacations.read_more')</a>';
        var lessText = '<a href="#" class="btn btn-primary btn-sm read-more-btn">@lang('vacations.read_less')</a>';

        if (textLength > word_char_count_allowed) {
            content.html('<div class="content-wrapper">' + fullText + '</div>');
            var wrapper = content.find('.content-wrapper');
            
            wrapper.hide();
            content.append('<div class="truncated-content">' + 
                fullText.substring(0, word_char_count_allowed) + 
                '<span class="more-ellipsis">' + ellipsis + '</span>' +
            '</div>');
            
            seeMoreBtn.show();
            
            seeMoreBtn.find('a').click(function(e) {
                e.preventDefault();
                if ($(this).hasClass('less')) {
                    $(this).removeClass('less');
                    $(this).html(moreText);
                    content.find('.truncated-content').show();
                    wrapper.hide();
                } else {
                    $(this).addClass('less');
                    $(this).html(lessText);
                    content.find('.truncated-content').hide();
                    wrapper.show();
                }
            });
        } else {
            seeMoreBtn.hide();
        }

        $(window).resize(function() {
            word_char_count_allowed = $(window).width() <= 768 ? 300 : 1200;
            if (textLength > word_char_count_allowed && content.find('.truncated-content').length) {
                content.find('.truncated-content').html(
                    fullText.substring(0, word_char_count_allowed) + 
                    '<span class="more-ellipsis">' + ellipsis + '</span>'
                );
                seeMoreBtn.show();
            } else {
                seeMoreBtn.hide();
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
        $lat = isset($vacations[0]) ? $vacations[0]->lat : 51.165691;
        $lng = isset($vacations[0]) ? $vacations[0]->lng : 10.451526;
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

    @if($allVacations->isEmpty())
        @include('pages.vacations.partials.maps',['vacations' => $othervacations])
    @else
        @include('pages.vacations.partials.maps',['vacations' => $allVacations])
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
