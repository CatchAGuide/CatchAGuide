@php
    $destinationRoute = $destination_route ?? 'destination.country';
    $showGeoCarousels = $show_geo_carousels ?? true;
    $showOffersCatalog = $show_offers_catalog ?? true;
    $useToursHeroSearch = request()->routeIs('guidings.destination');
    $useCategoryHeroHeader = request()->routeIs('destination.country') || $useToursHeroSearch;
    $geoFilters = is_array($row_data->filters ?? null) ? $row_data->filters : [];
    $currentCountrySlug = $destination_type === 'country' ? $row_data->slug : ($row_data->country->slug ?? null);
    $heroBreadcrumbs = $useToursHeroSearch
        ? array_values(array_filter([
            ['label' => __('homepage.filter-fishing-near-me'), 'url' => route('guidings.index')],
            in_array($destination_type, ['region', 'city'], true) && $row_data->country
                ? ['label' => $row_data->country->name, 'url' => route('guidings.destination', ['country' => $row_data->country->slug])]
                : null,
            $destination_type === 'city' && $row_data->region && $row_data->country
                ? ['label' => $row_data->region->name, 'url' => route('guidings.destination', [
                    'country' => $row_data->country->slug,
                    'region' => $row_data->region->slug,
                ])]
                : null,
            ['label' => $row_data->name, 'url' => null],
        ]))
        : [
            ['label' => __('destination.breadcrumb'), 'url' => route('destination')],
            ['label' => $row_data->name, 'url' => null],
        ];
@endphp
@extends('layouts.app-v2')

@section('title', $row_data->title)
@section('description', $row_data->sub_title)
@section('header_title', $row_data->title)
@section('header_sub_title', $row_data->sub_title)
@section('description', $row_data->sub_title)

@section('share_tags')
    <meta property="og:title" content="{{$row_data->title}}" />
    <meta property="og:description" content="{{$row_data->introduction ?? ""}}" />
    
    @if(isset($row_data->thumbnail_path) && media_path_usable($row_data->thumbnail_path))
        <meta property="og:image" content="{{ media_url($row_data->thumbnail_path) }}"/>
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
    #page-main-intro {
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

    @media (min-width: 768px) {
        .country-content-fix {
            margin-top: 15px !important; /* Ensure this margin is applied */
        }
    }

    #map-placeholder {
        width: 100%;
        height: 200px;
        background-image: url({{ url('') }}/assets/images/map-bg.png);
        background-size: cover;
        background-position: center;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    #map-placeholder button {
        position: static;
        margin: 0;
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

    .slider-label {
        position: absolute;
        top: -25px; /* Adjust as needed */
        transform: translateX(-50%);
        background-color: white;
        padding: 2px 5px;
        border-radius: 3px;
        font-size: 12px;
        color: black;
        white-space: nowrap;
    }

    #radius {
        background: url("data:image/svg+xml,<svg height='10px' width='10px' viewBox='0 0 16 16' fill='%23808080' xmlns='http://www.w3.org/2000/svg'><path d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/></svg>") no-repeat;
        background-position: right 0.3rem center !important;
    }

    .custom-select:has(option:disabled:checked[hidden]) {
        color: gray;
    }
    .custom-select option {
        color: black;
    }

    .form-custom-input {
        border: 1px solid #d4d5d6;
        border-radius: 5px;
        padding: 8px 10px;
        width: 100%;
    }
    .form-control:focus {
        box-shadow: none;
    }
    .form-custom-input:focus-visible {
        border: 0;
        outline: solid #e8604c 1px !important;
    }

    #guidings-menu-search {
        position: absolute;
        top: 133px;
        z-index: 3;
    }
    #guidings-result {
        line-height: 14px;
    }
    .pac-container {
        z-index: 2000;
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
    }

    .carousel-image {
        height: 250px;
        object-fit: cover;
        width: 100%;
        background: black;
    }

    #filter-loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 255, 255, 0.7);
        z-index: 9999;
        display: flex;
        justify-content: center;
        align-items: center;
        transition: opacity 0.3s ease;
    }
    
    .listings-container.loading {
        opacity: 0.5;
        pointer-events: none;
    }

    .sort-row .form-select {
        width: auto;
    }

    .active-filters {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        margin-top: 10px;
    }

    .filter-sort-container {
        display: flex;
        flex-direction: column;
        margin-bottom: 15px;
    }

    .ave-reviews-row {
        display: flex;
        gap: 8px;
        align-items: center;
        justify-content: flex-end;
        position: absolute;
        right: 0px;
        top: 0;  /* Position at the top, aligned with the title */
        .ratings-score{
            background-color: #313041;
            color: #fff;
            font-weight: bold;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            border-radius: 8px 8px 0 8px;
            font-size: 12px;
            .rating-value{
                color: #fff;
                min-width: unset;
                font-size: 14px;
            }
            .rating-label{
                color: #fff;
            }
        }
    }

    .ratings-score {
        background-color: #E8604C;
        color: white;
        padding: 2px 5px;
        border-radius: 3px;
        font-weight: bold;
    }

    .no-reviews {
        width: 100%;
        display: flex;
        align-items: center;
        text-align: right;
        span{
            font-size: 14px;
            width: 100%;
        }
    }

    /* Adjust the mobile layout */
    @media (max-width: 767px) {
        .ave-reviews-row {
            position: absolute;
            top: 0;
            right: 0;
            width: auto;
            display: block;
            flex-direction: column;
            align-items: flex-end;
        }
        
        .ratings-score {
            margin-left: auto; /* Push to the right */
        }
        
        .guidings-item-title {
            padding-right: 50px;  /* Make room for the rating */
        }
    }
    
    /* Make sure the parent container has proper positioning */
    .guidings-item {
        position: relative;
    }
</style>
@endsection

@stack('guidingListingStyles')

@section('content')
    @if($useCategoryHeroHeader)
        <div class="category-hero-page" data-category-hero-page>
        @include('pages.category.partials.hero-header', [
            'listingTitle' => $row_data->title,
            'listingSubtitle' => $row_data->sub_title,
            'searchAction' => listing_search_action(),
            'placeValue' => $geoFilters['place'] ?? null,
            'placeLat' => $geoFilters['placeLat'] ?? null,
            'placeLng' => $geoFilters['placeLng'] ?? null,
            'placeCity' => $geoFilters['city'] ?? null,
            'placeCountry' => $geoFilters['country'] ?? null,
            'placeRegion' => $geoFilters['region'] ?? null,
            'breadcrumbItems' => $heroBreadcrumbs,
        ])
    @endif
    <div class="country-content-fix">
        <div class="container" id="destination">
            @unless($useCategoryHeroHeader)
            <div class="container">
                <section class="page-header">
                    <div class="page-header__bottom breadcrumb-container guiding">
                        <div class="page-header__bottom-inner">
                            <ul class="thm-breadcrumb list-unstyled">
                                <li><a href="{{ route('welcome') }}">@lang('message.home')</a></li>
                                <li><span><i class="fas fa-solid fa-chevron-right"></i></span></li>
                                @if($destination_type == 'country')
                                        <li class="active">{{ __('category.fishing_destinations_in')}} {{ $row_data->name }}</li>
                                    
                                    @elseif($destination_type == 'region')
                                        @if($row_data->country)
                                        <li><a href="{{ route($destinationRoute, ['country' => $row_data->country->slug]) }}">
                                            {{ __('category.fishing_destinations_in')}} {{ $row_data->country->name }}
                                        </a></li>
                                        <li><span><i class="fas fa-solid fa-chevron-right"></i></span></li>
                                        @endif
                                        <li class="active">{{ __('category.fishing_destinations_in')}} {{ $row_data->name }}</li>
                                    
                                    @elseif($destination_type == 'city')
                                        @if($row_data->country)
                                        <li><a href="{{ route($destinationRoute, ['country' => $row_data->country->slug]) }}">
                                            {{ __('category.fishing_destinations_in')}} {{ $row_data->country->name }}
                                        </a></li>
                                        <li><span><i class="fas fa-solid fa-chevron-right"></i></span></li>
                                        @endif
                                        @if($row_data->region && $row_data->country)
                                        <li><a href="{{ route($destinationRoute, ['country' => $row_data->country->slug, 'region' => $row_data->region->slug]) }}">
                                            {{ __('category.fishing_destinations_in')}} {{ $row_data->region->name }}
                                        </a></li>
                                        <li><span><i class="fas fa-solid fa-chevron-right"></i></span></li>
                                        @endif
                                        <li class="active">{{ __('category.fishing_destinations_in')}} {{ $row_data->name }}</li>
                                    @endif
                            </ul>
                        </div>
                    </div>
                </section>
            </div>
            @endunless
            <div class="container {{ $useCategoryHeroHeader ? 'category-hero-page__body offers-page-header__anim' : '' }}" @if($useCategoryHeroHeader) style="--offers-anim-i: 4" @endif>
                <div class="col-12">
                    <div id="page-main-intro" class="cag-dest-intro">
                        <div class="page-main-intro-text mb-1">{!! translate(nl2br($row_data->introduction ?? '')) !!}</div>
                        <p class="see-more text-center"><a href="#" class="btn btn-primary btn-sm read-more-btn">@lang('destination.read_more')</a></p>
                    </div>
                    @if($showGeoCarousels)
                    @php
                    $regionItems = $regions
                        ->filter(fn ($region) => $region->country)
                        ->map(fn ($region) => [
                            'url' => route($destinationRoute, [
                                'country' => $region->country->slug,
                                'region' => $region->slug,
                            ]),
                            'name' => $region->name,
                            'thumbnail' => $region->getThumbnailPath(),
                        ])
                        ->values();
                    $cityItems = $cities
                        ->filter(fn ($city) => $city->country && $city->region)
                        ->when($destination_type === 'city', fn ($collection) => $collection->reject(
                            fn ($city) => (int) $city->id === (int) $row_data->id
                        ))
                        ->map(fn ($city) => [
                            'url' => route($destinationRoute, [
                                'country' => $city->country->slug,
                                'region' => $city->region->slug,
                                'city' => $city->slug,
                            ]),
                            'name' => $city->name,
                            'thumbnail' => $city->getThumbnailPath(),
                        ])
                        ->values();
                    @endphp

                    @if($destination_type === 'country')
                        @include('pages.category.partials.geo-rail', [
                            'railKey' => 'regions',
                            'title' => __('destination.all_region'),
                            'subtitle' => __('destination.regions_subtitle'),
                            'items' => $regionItems,
                        ])
                    @endif

                    @if(in_array($destination_type, ['country', 'region', 'city'], true))
                        @include('pages.category.partials.geo-rail', [
                            'railKey' => 'cities',
                            'title' => __('destination.all_cities'),
                            'subtitle' => __('destination.cities_subtitle'),
                            'items' => $cityItems,
                        ])
                    @endif
                    @endif
                    @if($showOffersCatalog)
                    {{-- <h5 class="cag-dest-listings-title">{{ translate('Fishing tours in ' . $row_data->name) }}</h5> --}}
                    <div class="offers-catalog-page mb-5">
                        <x-offers.catalog-listing
                            :vm="$vm"
                            :show-faq="false"
                            analytics-page="destination-offers-catalog"
                            :region-redirect-options="$useToursHeroSearch ? ($countryOptions ?? collect()) : collect()"
                            :region-redirect-current="$currentCountrySlug"
                            :region-redirect-all-url="$useToursHeroSearch ? route('guidings.index') : null"
                        />
                    </div>
                    @else
                    <div class="cag-home cag-home--embed cag-dest-offers-wrap mb-5">
                        @include('pages.home.partials.mixed-offers-rail')
                    </div>
                    @endif

                    <div class="mb-3">{!! clean_html(translate($row_data->content ?? '')) !!}</div>

                    @if($row_data->fish_avail_title != '' && $row_data->fish_avail_intro != '')
                        <h2 class="mb-2 mt-5">{{ translate($row_data->fish_avail_title) }}</h2>
                        <p>{!! clean_html(translate($row_data->fish_avail_intro)) !!}</p>
                        @if($fish_chart->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered " id="fish_chart_table">
                                <thead>
                                    <tr>
                                        <th width="28%">@lang('destination.fish')</th>
                                        <th width="6%" class="text-center">{{ __('category.month_jan') }}</th>
                                        <th width="6%" class="text-center">{{ __('category.month_feb') }}</th>
                                        <th width="6%" class="text-center">{{ __('category.month_mar') }}</th>
                                        <th width="6%" class="text-center">{{ __('category.month_apr') }}</th>
                                        <th width="6%" class="text-center">{{ __('category.month_may') }}</th>
                                        <th width="6%" class="text-center">{{ __('category.month_jun') }}</th>
                                        <th width="6%" class="text-center">{{ __('category.month_jul') }}</th>
                                        <th width="6%" class="text-center">{{ __('category.month_aug') }}</th>
                                        <th width="6%" class="text-center">{{ __('category.month_sep') }}</th>
                                        <th width="6%" class="text-center">{{ __('category.month_oct') }}</th>
                                        <th width="6%" class="text-center">{{ __('category.month_nov') }}</th>
                                        <th width="6%" class="text-center">{{ __('category.month_dec') }}</th>
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
                            <p>{!! clean_html(translate($row_data->size_limit_intro)) !!}</p>
                            @if(!empty($fish_size_limit))
                            <table class="table table-bordered table-striped" id="fish_size_limit_table">
                                <thead>
                                    <tr>
                                        <th width="20%">@lang('destination.fish')</th>
                                        <th width="80%">{{ __('category.size_limit') }}</th>
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
                            <p>{!! clean_html(translate($row_data->time_limit_intro)) !!}</p>
                            @if(!empty($fish_time_limit))
                            <table class="table table-bordered table-striped" id="fish_time_limit_table">
                                <thead>
                                    <tr>
                                        <th width="20%">@lang('destination.fish')</th>
                                        <th width="80%">{{ __('category.time_limit') }}</th>
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
                                        <div class="accordion-body ">{!! clean_html(translate($row->answer)) !!}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @if($useCategoryHeroHeader)
        </div>
    @endif
@endsection

@section('js_after')
@if($useCategoryHeroHeader)
@include('layouts.partials.category-hero-header-script')
@endif
@if($showOffersCatalog)
@include('components.offers.partials.gallery-script')
@else
@include('pages.category.partials.destination-offers-script')
@endif
@if($showGeoCarousels)
@include('pages.home.partials.species-spotlight-script')
@endif
<script>
    $(function() {
        var word_char_count_allowed = $(window).width() <= 768 ? 300 : 1200;
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
            $('.see-more').click(function(e) {
                e.preventDefault();
                var textContainer = $(this).prev('.page-main-intro-text');

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
@endsection
