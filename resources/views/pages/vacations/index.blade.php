@extends('layouts.app-v2')

@section('title', $row_data->title)
@section('description', $row_data->introduction)
@section('header_title', $row_data->title)
@section('header_sub_title', $row_data->sub_title)

@section('share_tags')
    <meta property="og:title" content="{{$row_data->title}}" />
    <meta property="og:description" content="{{$row_data->introduction ?? ""}}" />
    <meta name="description" content="{{$row_data->sub_title ?? $row_data->introduction}}">
    
    @if(isset($row_data->thumbnail_path) && file_exists(public_path(str_replace(asset(''), '', asset($row_data->thumbnail_path)))))
        <meta property="og:image" content="{{asset($row_data->thumbnail_path)}}"/>
    @endif
@endsection

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
    .show-more-maps {
        background-color: var(--thm-black) !important;
        color: #fff !important;
        border: 2px solid !important;
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
    
    .guiding-item-price h5 {
        margin: 0;
        white-space: nowrap;
    }
    
    /* Fix layout for vacations listing */
    .country-listing-item {
        padding-left: 15px;
    }
    
    @media (max-width: 991px) {
        .country-listing-item {
            padding-left: 0;
            margin-top: 20px;
        }
    }
    
    /* Ensure proper spacing for vacation cards */
    .guiding-list-item {
        margin-bottom: 20px !important;
    }
    
    /* Ensure map placeholder button is centered - override SCSS */
    #vacations-category #map-placeholder {
        position: relative !important;
    }
    
    #vacations-category #map-placeholder button {
        position: absolute !important;
        top: 50% !important;
        left: 50% !important;
        transform: translate(-50%, -50%) !important;
        width: auto !important;
        margin: 0 !important;
    }
</style>
@endsection

@section('content')
<div class="container">
        <section class="page-header">
            <div class="page-header__bottom breadcrumb-container guiding">
                <div class="page-header__bottom-inner">
                    <ul class="thm-breadcrumb list-unstyled">
                        <li><a href="{{ route('welcome') }}">@lang('message.home')</a></li>
                        <li><span><i class="fas fa-solid fa-chevron-right"></i></span></li>
                        <li><a href="{{ route('vacations.index') }}">{{ translate('Fishing Vacations')}}</a></li>
                        <li><span><i class="fas fa-solid fa-chevron-right"></i></span></li>
                        <li class="active">{{ translate('Vacations in ' . $row_data->name) }}</li>
                    </ul>
                </div>
            </div>
        </section>
    </div>
    <div class="container">
        {{-- <section class="page-header">
            <div class="page-header__bottom">
                <div class="container">
                    <div class="page-header__bottom-inner">
                        <ul class="thm-breadcrumb list-unstyled">
                            <li><a href="{{ route('welcome') }}">@lang('message.home')</a></li>
                            <li><span>&#183;</span></li>
                            <li><a href="{{ route('vacations.index') }}">{{ translate('Fishing Vacations')}}</a></li>
                            <li><span>&#183;</span></li>
                            <li class="active">
                                {{ translate('Vacations in ' . $row_data->name) }}
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </section> --}}
    </div>
    <!--News One Start-->
    <div class="container" id="vacations-category">
        <div class="row">
            <div class="col-12">
                <div id="page-main-intro" class="mb-3">
                    <div class="page-main-intro-text mb-1">{!! translate(nl2br($row_data->introduction)) !!}</div>
                    <p class="see-more text-center"><a href="#" class="btn btn-primary btn-sm read-more-btn">@lang('vacations.read_more')</a></p>
                </div>
                <h5 class="mb-2">{{ translate('Vacations in ' . $row_data->name) }}</h5>
                <div class="row mb-5">
                    {{-- Mobile sorting and filter section - commented out --}}
                    <div class="col-12 col-sm-4 col-md-12 d-flex mb-3 d-block d-sm-none mobile-selection-sfm">
                        <div class="d-grid gap-2 w-100">
                            <div class="btn-group" role="group" aria-label="Button group with nested dropdown">
                                {{-- <div class="btn-group border rounded-start cag-btn-inverted" role="group" style=" width:30%;">
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
                                </a> --}}
                                <a class="btn border cag-btn-inverted" data-bs-target="#mapModal" data-bs-toggle="modal" href="javascript:void(0)" style=" border-left: 2px solid #ccc!important; width:40%;"><i class="fa fa-map-marker-alt me-2"></i>@lang('vacations.show_on_map')</a>

                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 col-lg-3">
                        <div class="card mb-2 d-none d-sm-block">
                            <div id="map-placeholder">
                                <button class="btn btn-primary show-more-maps" data-bs-target="#mapModal" data-bs-toggle="modal">@lang('vacations.show_on_map')</button>
                            </div>
                        </div>
                        {{-- Sorting section - commented out --}}
                        {{-- <div class="card d-block d-none d-sm-block mb-1">
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
                        </div> --}}
                        {{-- Filter section - commented out --}}
                        {{-- <div class="card d-block d-none d-sm-block">
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
                        </div> --}}
                    </div>
                    <div class="col-sm-12 col-lg-9 country-listing-item">
                        @foreach($vacations as $vacation)
                        @php
                            $gallery_images = get_galleries_image_link($vacation, 0);
                            $gallery_images_full = array_map(function($img) {
                                return asset($img);
                            }, $gallery_images);
                            $gallery_count = count($gallery_images);
                            $target_fish_raw = $vacation->target_fish ?? [];
                            $target_fish = is_array($target_fish_raw) ? $target_fish_raw : (is_string($target_fish_raw) ? array_filter(explode(',', $target_fish_raw)) : []);
                            $target_fish_count = count($target_fish);
                            $has_boat = count($vacation->rentalBoats ?? []) > 0;
                            $has_guide = count($vacation->guidings ?? []) > 0;
                        @endphp
                        <div class="vacation-list-card guiding-list-item">
                            <div class="vacation-list-card__inner border shadow-sm bg-white rounded overflow-hidden">
                                {{-- Image on top (mobile) / left (desktop) --}}
                                <div class="vacation-list-card__media">
                                    <div class="vacation-card__gallery" data-vacation-gallery="{{ $vacation->id }}" data-gallery-images='@json($gallery_images_full)'>
                                        @if($gallery_count > 0)
                                            <img
                                                src="{{ asset($gallery_images[0]) }}"
                                                alt="{{ translate($vacation->title) }}"
                                                data-vacation-gallery-image
                                                data-vacation-open-modal
                                                class="vacation-list-card__img"
                                            />
                                            @if($gallery_count > 1)
                                                <button type="button" aria-label="{{ __('Previous image') }}" class="vacation-gallery__nav-btn vacation-gallery__nav-btn--prev" data-vacation-prev-image>‹</button>
                                                <button type="button" aria-label="{{ __('Next image') }}" class="vacation-gallery__nav-btn vacation-gallery__nav-btn--next" data-vacation-next-image>›</button>
                                                <div class="vacation-gallery__counter" data-vacation-image-counter>1/{{ $gallery_count }}</div>
                                            @endif
                                        @else
                                            <img src="{{ asset('images/placeholder_guide.jpg') }}" alt="{{ translate($vacation->title) }}" class="vacation-list-card__img" />
                                        @endif
                                    </div>
                                </div>
                                <div class="vacation-list-card__body">
                                    <a href="{{ route('vacations.show', [$vacation->slug]) }}" class="vacation-list-card__link" onclick="event.preventDefault(); document.getElementById('store-destination-{{ $vacation->id }}').submit();">
                                        <h3 class="vacation-list-card__title">{{ \Str::limit(translate($vacation->title), 65) }}</h3>
                                        <p class="vacation-list-card__location"><i class="fas fa-map-marker-alt me-2"></i>{{ $vacation->location }}</p>
                                        {{-- Target fish tags (oval, light gray) - ALL visible on one scrollable line --}}
                                        <div class="vacation-list-card__tags vacation-target-fish-container">
                                            @foreach($target_fish as $index => $fish)
                                                <span class="vacation-list-card__tag">{{ is_array($fish) ? ($fish['name'] ?? '') : (string) $fish }}</span>
                                            @endforeach
                                        </div>
                                        {{-- Boat / Guide feature boxes --}}
                                        <div class="vacation-list-card__features">
                                            <span class="vacation-list-card__feature vacation-list-card__feature--boat">
                                                @if($has_boat)
                                                    <i class="fas fa-ship" aria-hidden="true"></i>
                                                    <span>@lang('vacations.modern_boat')</span>
                                                @else
                                                    <i class="fas fa-home" aria-hidden="true"></i>
                                                    <span>@lang('vacations.cozy_house')</span>
                                                @endif
                                            </span>
                                            <span class="vacation-list-card__feature vacation-list-card__feature--guide">
                                                @if($has_guide)
                                                    <i class="fas fa-fish" aria-hidden="true"></i>
                                                    <span>@lang('vacations.pro_guide')</span>
                                                @else
                                                    <i class="fas fa-star" aria-hidden="true"></i>
                                                    <span>@lang('vacations.top_fishing')</span>
                                                @endif
                                            </span>
                                        </div>
                                        {{-- Camp amenities (included features) --}}
                                        <div class="vacations-amenities-container">
                                        <ul class="vacation-list-card__amenities">
                                            @php $facilities_display = $vacation->facilities ?? collect(); $facilities_count = is_countable($facilities_display) ? count($facilities_display) : 0; @endphp
                                            @foreach($facilities_display->take(3) as $index => $facility)
                                                <li class="vacation-list-card__amenity">
                                                    <i class="fa fa-check text-success" aria-hidden="true"></i>
                                                    <span>{{ is_object($facility) ? ($facility->name ?? '') : ($facility['name'] ?? '') }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                        </div>
                                    </a>
                                    {{-- Footer: price + Book Now + info (price = lowest of Accommodation OR Package only; hide if null) --}}
                                    <div class="vacation-list-card__footer">
                                        @php $displayPrice = $vacation->getLowestAccommodationOrOfferPrice(); @endphp
                                        @if($displayPrice !== null)
                                        <div class="vacation-list-card__price-block">
                                            <span class="vacation-list-card__price-label">@lang('vacations.per_day_label')</span>
                                            <span class="vacation-list-card__price">€{{ two($displayPrice) }}</span>
                                        </div>
                                        @endif
                                        <a href="{{ route('vacations.show', [$vacation->slug]) }}" class="vacation-list-card__btn-book" onclick="event.preventDefault(); document.getElementById('store-destination-{{ $vacation->id }}').submit();">@lang('vacations.book_now')</a>
                                        <a href="{{ route('vacations.show', [$vacation->slug]) }}" class="vacation-list-card__btn-info" onclick="event.preventDefault(); document.getElementById('store-destination-{{ $vacation->id }}').submit();" aria-label="@lang('vacations.details')"><i class="fas fa-info"></i></a>
                                    </div>
                                    <form id="store-destination-{{ $vacation->id }}" action="{{ route('vacations.show', [$vacation->slug]) }}" method="GET" style="display: none;">
                                        @php session(['vacation_destination_id' => $row_data->id]); @endphp
                                    </form>
                                </div>
                            </div>
                            {{-- Gallery modal --}}
                            <div class="vacation-gallery-modal" data-vacation-modal="{{ $vacation->id }}">
                                <div class="vacation-gallery-modal__content">
                                    <button class="vacation-gallery-modal__close">&times;</button>
                                    <button class="vacation-gallery-modal__prev">&#10094;</button>
                                    <button class="vacation-gallery-modal__next">&#10095;</button>
                                    <img class="vacation-gallery-modal__image" src="" alt="{{ translate($vacation->title) }}">
                                    <div class="vacation-gallery-modal__counter">
                                        <span class="vacation-gallery-modal__current">1</span> / <span class="vacation-gallery-modal__total">{{ $gallery_count }}</span>
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
    // Use centralized GoogleMapsManager
    const MapsManager = window.GoogleMapsManager;
    let map;
    const markers = [];
    const infowindows = [];
    const uniqueCoordinates = [];
    let isDuplicateCoordinate;
    let markerCluster;

    // Initialize map
    MapsManager.waitForGoogleMaps(async function() {
        @php
            $lat = isset($vacations[0]) ? $vacations[0]->latitude : 51.165691;
            $lng = isset($vacations[0]) ? $vacations[0]->longitude : 10.451526;
        @endphp
        const position = { lat: {{request()->get('placeLat') ? request()->get('placeLat') : $lat }}, lng: {{request()->get('placeLng') ? request()->get('placeLng') : $lng }} };
        
        // Initialize map using centralized manager
        map = await MapsManager.initMap("map", {
            zoom: 5,
            center: position,
            mapId: "{{ config('services.google_maps.map_id', 'DEMO_MAP_ID') }}",
            mapTypeControl: false,
            streetViewControl: false
        });

        // Create placeholder marker
        await MapsManager.createMarker({ map: map });
        
        @php
            $grayIds = collect($vacations->items())->pluck('id')->toArray();
        @endphp
        @include('pages.vacations.partials.maps',[
            'vacations' => $vacations ?? [],
            'grayIds' => $grayIds ?? [],
        ])
    
        function getRandomOffset() {
          return (Math.random() - 0.5) * 0.0080;
        }
    
        // Create marker cluster using centralized manager
        if (markers.length > 0) {
            markerCluster = MapsManager.createMarkerClusterer({ markers, map });
            if (markerCluster) {
                google.maps.event.addListener(markerCluster, 'clusterclick', function(cluster) {
                    map.setZoom(map.getZoom() + 2);
                    map.setCenter(cluster.getCenter());
                });
            }
        }
    });
    
    // Initialize Places Autocomplete using centralized manager
    function initialize() {
        MapsManager.waitForGoogleMaps(function() {
            MapsManager.initAutocomplete('searchPlace', function(place) {
                const locationData = MapsManager.extractLocationData(place);
                const latInput = document.getElementById('placeLat');
                const lngInput = document.getElementById('placeLng');
                if (latInput) latInput.value = locationData.lat;
                if (lngInput) lngInput.value = locationData.lng;
            });
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

    // Get the toggle button and filter container elements
    var toggleBtn = document.getElementById('toggleFilterBtn');
    var filterContainer = document.getElementById('filterContainer');

    // Add click event listener to the toggle button
    if(toggleBtn){
        toggleBtn.addEventListener('click', function() {
            // Toggle the visibility of the filter container
            filterContainer.classList.toggle('d-block');
        });
    }

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
    
    // Vacation Gallery Navigation
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('[data-vacation-gallery]').forEach(function(gallery) {
            const galleryId = gallery.getAttribute('data-vacation-gallery');
            const images = JSON.parse(gallery.getAttribute('data-gallery-images') || '[]');
            const imageEl = gallery.querySelector('[data-vacation-gallery-image]');
            const prevBtn = gallery.querySelector('[data-vacation-prev-image]');
            const nextBtn = gallery.querySelector('[data-vacation-next-image]');
            const counter = gallery.querySelector('[data-vacation-image-counter]');
            const modal = document.querySelector(`[data-vacation-modal="${galleryId}"]`);
            const modalImage = modal ? modal.querySelector('.vacation-gallery-modal__image') : null;
            const modalPrev = modal ? modal.querySelector('.vacation-gallery-modal__prev') : null;
            const modalNext = modal ? modal.querySelector('.vacation-gallery-modal__next') : null;
            const modalClose = modal ? modal.querySelector('.vacation-gallery-modal__close') : null;
            const modalCurrent = modal ? modal.querySelector('.vacation-gallery-modal__current') : null;
            const modalTotal = modal ? modal.querySelector('.vacation-gallery-modal__total') : null;
            
            if (images.length === 0) return;
            
            let currentIndex = 0;
            
            function updateImage(index) {
                if (index < 0) index = images.length - 1;
                if (index >= images.length) index = 0;
                currentIndex = index;
                
                if (imageEl) {
                    imageEl.src = images[currentIndex];
                }
                if (counter) {
                    counter.textContent = (currentIndex + 1) + '/' + images.length;
                }
                if (modalImage) {
                    modalImage.src = images[currentIndex];
                }
                if (modalCurrent) {
                    modalCurrent.textContent = currentIndex + 1;
                }
            }
            
            if (prevBtn) {
                prevBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    updateImage(currentIndex - 1);
                });
            }
            
            if (nextBtn) {
                nextBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    updateImage(currentIndex + 1);
                });
            }
            
            if (imageEl && modal) {
                imageEl.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    if (modal) {
                        modal.classList.add('show');
                        updateImage(currentIndex);
                    }
                });
            }
            
            if (modalPrev) {
                modalPrev.addEventListener('click', function() {
                    updateImage(currentIndex - 1);
                });
            }
            
            if (modalNext) {
                modalNext.addEventListener('click', function() {
                    updateImage(currentIndex + 1);
                });
            }
            
            if (modalClose) {
                modalClose.addEventListener('click', function() {
                    if (modal) {
                        modal.classList.remove('show');
                    }
                });
            }
            
            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        modal.classList.remove('show');
                    }
                });
            }
        });
    });
    
    // Handle See More/Less for Target Fish and Amenities
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.vacation-see-more-btn').forEach(function(button) {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                var target = this.getAttribute('data-target');
                var isExpanded = this.getAttribute('data-expanded') === 'true';
                var container = this.closest('.vacation-target-fish-container, .vacations-amenities-container');
                
                if (!container) return;
                
                // Find all hidden items in this container
                var hiddenItems = container.querySelectorAll('.vacation-item-hidden');
                
                if (isExpanded) {
                    // Collapse - hide items
                    hiddenItems.forEach(function(item) {
                        item.classList.remove('show');
                    });
                    this.setAttribute('data-expanded', 'false');
                    this.querySelector('.see-more-text').style.display = 'inline';
                    this.querySelector('.see-less-text').style.display = 'none';
                } else {
                    // Expand - show items
                    hiddenItems.forEach(function(item) {
                        item.classList.add('show');
                    });
                    this.setAttribute('data-expanded', 'true');
                    this.querySelector('.see-more-text').style.display = 'none';
                    this.querySelector('.see-less-text').style.display = 'inline';
                }
            });
        });
    });
</script>
@endsection
